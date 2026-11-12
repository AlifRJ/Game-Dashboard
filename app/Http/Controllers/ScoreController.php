<?php

namespace App\Http\Controllers;

use App\Models\Score;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ScoreController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $request->only(['search', 'trashed']);

        return Inertia::render('Score/Index', [
            'filters' => $filters,
            'scores'  => Score::query()
                ->with(['user' => fn ($q) => $q->chaperone()])
                ->filter($filters)
                ->latest()
                ->paginate(10)
                ->withQueryString(),
        ]);
    }

    public function show(Score $score): Response
    {   
        return Inertia::render('Score/Details', [
            'score' => $score
        ]);
    }

    public function destroy(Score $score): RedirectResponse
    {
        $score->delete();

        return to_route('score.index')->with('message', 'Score Deleted Successfully');
    }

    public function restore(Score $score): RedirectResponse
    {
        $score->restore();

        return to_route('score.index')->with('message', 'Score Restored Successfully');
    }

    public function forceDelete(Score $score): RedirectResponse
    {
        $score->forceDelete();

        return to_route('score.index')->with('message', 'Score Permanently Deleted');
    }
}
