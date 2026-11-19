<?php

namespace App\Services;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class ScoreApiService
{
    private const VERSION_KEY = 'dashboard_scores_version';

    public function getPaginatedScores(
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
            $this->flushScoresCache();
        }

        $version = Cache::get(self::VERSION_KEY, 1);
        $cacheKey = 'dashboard_scores_v' . $version . '_' . md5(serialize($queryParams));

        if (Cache::has($cacheKey)) {
            $cached = Cache::get($cacheKey);

            return [
                'scores' => $this->buildPaginator($cached['data'], $cached['total'], $perPage, $page),
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
                ->get('/scores/admin', $queryParams);

            if ($response->successful()) {
                $payload = $response->json() ?? [];
                $data = $payload['data'] ?? [];
                $total = (int) ($payload['total'] ?? count($data));

                Cache::put($cacheKey, [
                    'data'  => $data,
                    'total' => $total,
                ], now()->addMinutes(5));

                return [
                    'scores' => $this->buildPaginator($data, $total, $perPage, $page),
                    'error' => null,
                ];
            }

            Log::warning('Score API returned an error', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
        } catch (Throwable $e) {
            Log::error('Score API connection failed', [
                'exception' => $e->getMessage(),
            ]);
        }

        return [
            'scores' => $this->buildPaginator([], 0, $perPage, $page),
            'error' => 'Unable to fetch score data from the server.',
        ];
    }

    public function flushScoresCache(): void
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
     * Fetch a single score by UUID from the Go backend API.
     *
     * @return array{score: array<string, mixed>|null, status: int, error: string|null}
     */
    public function getScoreById(string $id, bool $forceRefresh = false): array
    {
        $version = Cache::get(self::VERSION_KEY, 1);
        $cacheKey = "dashboard_score_v{$version}_{$id}";

        if (!$forceRefresh && Cache::has($cacheKey)) {
            return [
                'score'   => Cache::get($cacheKey),
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
                ->get("scores/admin/{$id}");

            if ($response->successful()) {
                $payload = $response->json() ?? [];

                $score = isset($payload['data']) && is_array($payload['data'])
                    ? $payload['data']
                    : $payload;

                Cache::put($cacheKey, $score, now()->addMinutes(5));

                return [
                    'score'   => $score,
                    'status' => 200,
                    'error'  => null,
                ];
            }

            if ($response->status() === 404) {
                return [
                    'score'   => null,
                    'status' => 404,
                    'error'  => 'Score not found.',
                ];
            }

            Log::warning('Score API returned an error on show', [
                'score_id' => $id,
                'status'  => $response->status(),
                'body'    => $response->body(),
            ]);
        } catch (Throwable $e) {
            Log::error('Score API connection failed on show', [
                'score_id'   => $id,
                'exception' => $e->getMessage(),
            ]);
        }

        return [
            'score'   => null,
            'status' => 502,
            'error'  => 'Unable to fetch score details from the server.',
        ];
    }

    /**
     * Create a new score via the Go backend API.
     *
     * @param  array{score: int, category: string, difficulty: string}  $payload
     * @return array{score: array<string, mixed>|null, status: int, errors: array<string, string>, message: string|null}
     */
    public function createScore(array $payload): array
    {
        try {
            $response = Http::baseUrl(rtrim(config('services.game_api.url'), '/') . '/')
                ->withHeaders([
                    'X-Dashboard-Token' => config('services.game_api.key'),
                ])
                ->acceptJson()
                ->timeout(10)
                ->post('scores/admin', $payload);

            if ($response->successful()) {
                $this->flushScoresCache();

                $body = $response->json() ?? [];
                $score = isset($body['data']) && is_array($body['data'])
                    ? $body['data']
                    : $body;

                return [
                    'score'    => $score,
                    'status'  => $response->status(),
                    'errors'  => [],
                    'message' => null,
                ];
            }

            Log::warning('Score API failed to create score', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
        } catch (Throwable $e) {
            Log::error('Score API connection failed during createScore', [
                'exception' => $e->getMessage(),
            ]);
        }

        return [
            'score'    => null,
            'status'  => 502,
            'errors'  => [],
            'message' => 'Unable to create score on the server.',
        ];
    }

    /**
     * Update an existing score via the Go backend API.
     *
     * @param  string  $id
     * @param  array{name: int, category: string, difficulty: string}  $payload
     * @return array{score: array<string, mixed>|null, status: int, errors: array<string, string>, message: string|null}
     */
    public function updateScore(string $id, array $payload): array
    {
        try {
            $response = Http::baseUrl(rtrim(config('services.game_api.url'), '/') . '/')
                ->withHeaders([
                    'X-Dashboard-Token' => config('services.game_api.key'),
                ])
                ->acceptJson()
                ->timeout(10)
                ->put("scores/admin/{$id}", $payload);

            if ($response->successful()) {
                $this->flushScoresCache();

                $body = $response->json() ?? [];
                $score = isset($body['data']) && is_array($body['data'])
                    ? $body['data']
                    : $body;

                return [
                    'score'    => $score,
                    'status'  => $response->status(),
                    'errors'  => [],
                    'message' => null,
                ];
            }

            if ($response->status() === 404) {
                return [
                    'score'    => null,
                    'status'  => 404,
                    'errors'  => [],
                    'message' => 'Score not found.',
                ];
            }

            Log::warning('Score API failed to update score', [
                'score_id' => $id,
                'status'  => $response->status(),
                'body'    => $response->body(),
            ]);
        } catch (Throwable $e) {
            Log::error('Score API connection failed during updateScore', [
                'score_id'   => $id,
                'exception' => $e->getMessage(),
            ]);
        }

        return [
            'score'    => null,
            'status'  => 502,
            'errors'  => [],
            'message' => 'Unable to update score on the server.',
        ];
    }

    /**
     * Soft-delete a score via the Go backend API.
     *
     * @return array{success: bool, status: int, message: string|null}
     */
    public function deleteScore(string $id): array
    {
        return $this->sendScoreAction('delete', "scores/admin/{$id}", 'Unable to delete score.');
    }

    /**
     * Restore a soft-deleted score via the Go backend API.
     *
     * @return array{success: bool, status: int, message: string|null}
     */
    public function restoreScore(string $id): array
    {
        return $this->sendScoreAction('patch', "scores/admin/{$id}/restore", 'Unable to restore score.');
    }

    /**
     * Permanently delete a score via the Go backend API.
     *
     * @return array{success: bool, status: int, message: string|null}
     */
    public function forceDeleteScore(string $id): array
    {
        return $this->sendScoreAction('delete', "scores/admin/{$id}/permanent", 'Unable to permanently delete score.');
    }

    /**
     * Shared helper for state-mutating score actions (delete, restore, force-delete).
     *
     * @return array{success: bool, status: int, message: string|null}
     */
    private function sendScoreAction(string $method, string $endpoint, string $fallbackError): array
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
                $this->flushScoresCache();

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
                    'message' => 'Score not found.',
                ];
            }

            Log::warning("Score API failed on {$method} {$endpoint}", [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
        } catch (Throwable $e) {
            Log::error("Score API connection failed on {$method} {$endpoint}", [
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