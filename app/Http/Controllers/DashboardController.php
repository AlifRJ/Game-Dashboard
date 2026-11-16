<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Throwable;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $baseUrl         = config('services.game_api.url');
        $apiKey          = config('services.game_api.key');
        $isApiConfigured = filled($baseUrl) && filled($apiKey);
        $analytics       = null;

        if ($isApiConfigured) {
            $forceRefresh = $request->boolean('refresh');
            $analytics    = $forceRefresh ? null : Cache::get('dashboard_analytics');

            if ($analytics === null) {
                $apiUrl = rtrim($baseUrl, '/') . '/analytics';

                try {
                    $response = Http::withHeaders([
                        'X-Dashboard-Token' => $apiKey,
                        'Accept'            => 'application/json',
                    ])->timeout(10)->get($apiUrl);

                    if ($response->successful()) {
                        $analytics = $response->json();
                        Cache::put('dashboard_analytics', $analytics, now()->addMinutes(5));
                    } else {
                        Log::warning('Game analytics API returned an error', [
                            'status' => $response->status(),
                            'body'   => $response->body(),
                        ]);
                    }
                } catch (Throwable $err) {
                    Log::error('Game analytics API connection failed: ' . $err->getMessage());
                }
            }
        }

        return Inertia::render('Dashboard', [
            'canLogin'        => Route::has('login'),
            'canRegister'     => Route::has('register'),
            'isApiConfigured' => $isApiConfigured,
            'analytics'       => $analytics,
        ]);
    }
}
