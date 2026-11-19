<?php

namespace App\Http\Controllers;

use App\Services\ScoreApiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ScoreController extends Controller
{
    public function index(Request $request, ScoreApiService $gameApi): Response
    {
        $filters = $request->only(['search', 'trashed']);

        $result = $gameApi->getPaginatedScores(
            filters: $filters,
            perPage: $request->integer('per_page', 15),
            forceRefresh: $request->boolean('refresh'),
        );

        return Inertia::render('Score/Index', [
            'scores'   => $result['scores']->appends($filters),
            'filters' => $filters,
            'error'   => $result['error'],
        ]);
    }

    public function show(Request $request, string $id, ScoreApiService $gameApi): Response
    {
        abort_unless(Str::isUuid($id), 404);

        $result = $gameApi->getScoreById(
            id: $id,
            forceRefresh: $request->boolean('refresh'),
        );

        if ($result['status'] === 404) {
            abort(404, 'Score not found.');
        }

        return Inertia::render('Score/Details', [
            'score'  => $result['score'],
            'error' => $result['error'],
        ]);
    }

    public function destroy(string $id, ScoreApiService $gameApi): RedirectResponse
    {
        abort_unless(Str::isUuid($id), 404);

        $result = $gameApi->deleteScore($id);

        if ($result['status'] === 404) {
            abort(404, 'Score not found.');
        }

        if (!$result['success']) {
            return back()->with('error', $result['message']);
        }

        return to_route('score.index')->with('message', 'Score Deleted Successfully');
    }

    public function restore(string $id, ScoreApiService $gameApi): RedirectResponse
    {
        abort_unless(Str::isUuid($id), 404);

        $result = $gameApi->restoreScore($id);

        if ($result['status'] === 404) {
            abort(404, 'Score not found.');
        }

        if (!$result['success']) {
            return back()->with('error', $result['message']);
        }

        return to_route('score.index')->with('message', 'Score Restored Successfully');
    }

    public function forceDelete(string $id, ScoreApiService $gameApi): RedirectResponse
    {
        abort_unless(Str::isUuid($id), 404);

        $result = $gameApi->forceDeleteScore($id);

        if ($result['status'] === 404) {
            abort(404, 'Score not found.');
        }

        if (!$result['success']) {
            return back()->with('error', $result['message']);
        }

        return to_route('score.index')->with('message', 'Score Permanently Deleted');
    }
}
