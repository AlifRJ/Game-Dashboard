<?php

namespace App\Services;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class GameApiService
{
    private const VERSION_KEY = 'dashboard_users_version';

    public function getPaginatedUsers(
        array $filters = [],
        int $perPage = 15,
        bool $forceRefresh = false
    ): array {
        $page = Paginator::resolveCurrentPage('page');
        $offset = ($page - 1) * $perPage;

        $queryParams = array_filter([
            'limit'   => $perPage,
            'offset'  => $offset,
            'search'  => $filters['search'] ?? null,
            'trashed' => $filters['trashed'] ?? null,
        ], fn ($value) => $value !== null && $value !== '');

        if ($forceRefresh) {
            $this->flushUsersCache();
        }

        $version = Cache::get(self::VERSION_KEY, 1);
        $cacheKey = 'dashboard_users_v' . $version . '_' . md5(serialize($queryParams));

        if (Cache::has($cacheKey)) {
            $cached = Cache::get($cacheKey);

            return [
                'users' => $this->buildPaginator($cached['data'], $cached['total'], $perPage, $page),
                'error' => null,
            ];
        }

        try {
            $response = Http::baseUrl(rtrim(config('services.game_api.url'), '/') . '/')
                ->withHeaders([
                    'X-Dashboard-Token' => config('services.game_api.key'),
                ])
                ->acceptJson()
                ->timeout(10)
                ->retry(2, 200, throw: false)
                ->get('/users/admin', $queryParams);

            if ($response->successful()) {
                $payload = $response->json() ?? [];
                $data = $payload['data'] ?? [];
                $total = (int) ($payload['total'] ?? count($data));

                Cache::put($cacheKey, [
                    'data'  => $data,
                    'total' => $total,
                ], now()->addMinutes(5));

                return [
                    'users' => $this->buildPaginator($data, $total, $perPage, $page),
                    'error' => null,
                ];
            }

            Log::warning('Game API returned an error', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
        } catch (Throwable $e) {
            Log::error('Game API connection failed', [
                'exception' => $e->getMessage(),
            ]);
        }

        return [
            'users' => $this->buildPaginator([], 0, $perPage, $page),
            'error' => 'Unable to fetch user data from the game server.',
        ];
    }

    public function flushUsersCache(): void
    {
        Cache::increment(self::VERSION_KEY);
    }

    private function buildPaginator(array $items, int $total, int $perPage, int $page): LengthAwarePaginator
    {
        return new LengthAwarePaginator(
            items: $items,
            total: $total,
            perPage: $perPage,
            currentPage: $page,
            options: [
                'path'     => Paginator::resolveCurrentPath(),
                'pageName' => 'page',
            ]
        );
    }

    /**
     * Fetch a single user by UUID from the Go backend API.
     *
     * @return array{user: array<string, mixed>|null, status: int, error: string|null}
     */
    public function getUserById(string $id, bool $forceRefresh = false): array
    {
        $version = Cache::get(self::VERSION_KEY, 1);
        $cacheKey = "dashboard_user_v{$version}_{$id}";

        if (!$forceRefresh && Cache::has($cacheKey)) {
            return [
                'user'   => Cache::get($cacheKey),
                'status' => 200,
                'error'  => null,
            ];
        }

        try {
            $response = Http::baseUrl(rtrim(config('services.game_api.url'), '/') . '/')
                ->withHeaders([
                    'X-Dashboard-Token' => config('services.game_api.key'),
                ])
                ->acceptJson()
                ->timeout(10)
                ->retry(2, 200, throw: false)
                ->get("users/admin/{$id}");

            if ($response->successful()) {
                $payload = $response->json() ?? [];
                // Handle either direct user object `{ "id": ... }` or wrapped `{ "data": { "id": ... } }`
                $user = isset($payload['data']) && is_array($payload['data'])
                    ? $payload['data']
                    : $payload;

                Cache::put($cacheKey, $user, now()->addMinutes(5));

                return [
                    'user'   => $user,
                    'status' => 200,
                    'error'  => null,
                ];
            }

            if ($response->status() === 404) {
                return [
                    'user'   => null,
                    'status' => 404,
                    'error'  => 'User not found.',
                ];
            }

            Log::warning('Game API returned an error on show', [
                'user_id' => $id,
                'status'  => $response->status(),
                'body'    => $response->body(),
            ]);
        } catch (Throwable $e) {
            Log::error('Game API connection failed on show', [
                'user_id'   => $id,
                'exception' => $e->getMessage(),
            ]);
        }

        return [
            'user'   => null,
            'status' => 502,
            'error'  => 'Unable to fetch user details from the game server.',
        ];
    }

    /**
     * Create a new user via the Go backend API.
     *
     * @param  array{name: string, username: string, email: string, password: string}  $payload
     * @return array{user: array<string, mixed>|null, status: int, errors: array<string, string>, message: string|null}
     */
    public function createUser(array $payload): array
    {
        try {
            // Verify whether your Go endpoint is `users/admin` or `users`
            $response = Http::baseUrl(rtrim(config('services.game_api.url'), '/') . '/')
                ->withHeaders([
                    'X-Dashboard-Token' => config('services.game_api.key'),
                ])
                ->acceptJson()
                ->timeout(10)
                ->post('users/admin', $payload);

            if ($response->successful()) {
                $this->flushUsersCache();

                $body = $response->json() ?? [];
                $user = isset($body['data']) && is_array($body['data'])
                    ? $body['data']
                    : $body;

                return [
                    'user'    => $user,
                    'status'  => $response->status(),
                    'errors'  => [],
                    'message' => null,
                ];
            }

            // Handle conflict / duplicate username or email from Go API (e.g., 409 or 422)
            if (in_array($response->status(), [409, 422], true)) {
                $body = $response->json() ?? [];

                return [
                    'user'    => null,
                    'status'  => $response->status(),
                    'errors'  => $body['errors'] ?? [],
                    'message' => $body['error'] ?? $body['message'] ?? 'Validation failed on the game server.',
                ];
            }

            Log::warning('Game API failed to create user', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
        } catch (Throwable $e) {
            Log::error('Game API connection failed during createUser', [
                'exception' => $e->getMessage(),
            ]);
        }

        return [
            'user'    => null,
            'status'  => 502,
            'errors'  => [],
            'message' => 'Unable to create user on the game server.',
        ];
    }

    /**
     * Update an existing user via the Go backend API.
     *
     * @param  string  $id
     * @param  array{name: string, username: string, email: string, password?: string}  $payload
     * @return array{user: array<string, mixed>|null, status: int, errors: array<string, string>, message: string|null}
     */
    public function updateUser(string $id, array $payload): array
    {
        try {
            // Use ->patch() if your Go router registers PATCH /v1/users/admin/{id}
            $response = Http::baseUrl(rtrim(config('services.game_api.url'), '/') . '/')
                ->withHeaders([
                    'X-Dashboard-Token' => config('services.game_api.key'),
                ])
                ->acceptJson()
                ->timeout(10)
                ->put("users/admin/{$id}", $payload);

            if ($response->successful()) {
                $this->flushUsersCache();

                $body = $response->json() ?? [];
                $user = isset($body['data']) && is_array($body['data'])
                    ? $body['data']
                    : $body;

                return [
                    'user'    => $user,
                    'status'  => $response->status(),
                    'errors'  => [],
                    'message' => null,
                ];
            }

            if ($response->status() === 404) {
                return [
                    'user'    => null,
                    'status'  => 404,
                    'errors'  => [],
                    'message' => 'User not found.',
                ];
            }

            // Map duplicate email/username or validation errors (409 / 422) back to form fields
            if (in_array($response->status(), [409, 422], true)) {
                $body = $response->json() ?? [];
                $errors = $body['errors'] ?? [];
                $rawError = strtolower($body['error'] ?? '');

                if (empty($errors) && $rawError !== '') {
                    if (str_contains($rawError, 'email')) {
                        $errors['email'] = $body['error'];
                    } elseif (str_contains($rawError, 'username')) {
                        $errors['username'] = $body['error'];
                    }
                }

                return [
                    'user'    => null,
                    'status'  => $response->status(),
                    'errors'  => $errors,
                    'message' => $body['error'] ?? $body['message'] ?? 'Validation failed on the game server.',
                ];
            }

            Log::warning('Game API failed to update user', [
                'user_id' => $id,
                'status'  => $response->status(),
                'body'    => $response->body(),
            ]);
        } catch (Throwable $e) {
            Log::error('Game API connection failed during updateUser', [
                'user_id'   => $id,
                'exception' => $e->getMessage(),
            ]);
        }

        return [
            'user'    => null,
            'status'  => 502,
            'errors'  => [],
            'message' => 'Unable to update user on the game server.',
        ];
    }

    /**
     * Soft-delete a user via the Go backend API.
     *
     * @return array{success: bool, status: int, message: string|null}
     */
    public function deleteUser(string $id): array
    {
        return $this->sendUserAction('delete', "users/admin/{$id}", 'Unable to delete user.');
    }

    /**
     * Restore a soft-deleted user via the Go backend API.
     *
     * @return array{success: bool, status: int, message: string|null}
     */
    public function restoreUser(string $id): array
    {
        return $this->sendUserAction('patch', "users/admin/{$id}/restore", 'Unable to restore user.');
    }

    /**
     * Permanently delete a user via the Go backend API.
     *
     * @return array{success: bool, status: int, message: string|null}
     */
    public function forceDeleteUser(string $id): array
    {
        // Match your Go router path (e.g. `users/admin/{$id}/force` or `users/admin/{$id}/permanent`)
        return $this->sendUserAction('delete', "users/admin/{$id}/permanent", 'Unable to permanently delete user.');
    }

    /**
     * Shared helper for state-mutating user actions (delete, restore, force-delete).
     *
     * @return array{success: bool, status: int, message: string|null}
     */
    private function sendUserAction(string $method, string $endpoint, string $fallbackError): array
    {
        try {
            $response = Http::baseUrl(rtrim(config('services.game_api.url'), '/') . '/')
                ->withHeaders([
                    'X-Dashboard-Token' => config('services.game_api.key'),
                ])
                ->acceptJson()
                ->timeout(10)
                ->{$method}($endpoint);

            if ($response->successful()) {
                $this->flushUsersCache();

                return [
                    'success' => true,
                    'status'  => $response->status(),
                    'message' => null,
                ];
            }

            if ($response->status() === 404) {
                return [
                    'success' => false,
                    'status'  => 404,
                    'message' => 'User not found.',
                ];
            }

            Log::warning("Game API failed on {$method} {$endpoint}", [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
        } catch (Throwable $e) {
            Log::error("Game API connection failed on {$method} {$endpoint}", [
                'exception' => $e->getMessage(),
            ]);
        }

        return [
            'success' => false,
            'status'  => 502,
            'message' => $fallbackError,
        ];
    }

    
}