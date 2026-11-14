<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Throwable;

class DashboardController extends Controller
{
    public function index() {
        $baseUrl = config('services.game_api.url');
        $apiUrl = "{$baseUrl}/analytics";
        $apiKey = config('services.game_api.key');
        $analytics = null;
        
        try {
            $response = Http::withHeaders([
                'X-Dashboard-Token' => $apiKey,
                'Accept'            => 'application/json',
            ])->timeout(10)->get($apiUrl);

            if ($response->successful()) {
                $analytics = $response->json();
            } else {
                Log::warning('Game analytics API returned an error', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
            }
        } catch (Throwable $err) {
            Log::error('Game analytics API connection failed: ' . $err->getMessage());
        }

        return Inertia::render('Dashboard', [
            'canLogin'    => Route::has('login'),
            'canRegister' => Route::has('register'),
            'analytics'   => $analytics,
        ]);
    }
}
