<?php

namespace App\Http\Controllers;

use App\Models\Score;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ScoreController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('Score', [
            'filters' => $request->only(['search', 'trashed']),
            'scores' => Score::query()
                ->when($request->input('search'), function ($query, $search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('id', 'like', "%{$search}%")
                        ->orWhere('user_id', 'like', "%{$search}%")
                        ->orWhere('category', 'like', "%{$search}%")
                        ->orWhere('difficulty', 'like', "%{$search}%");
                    });
                })
                ->when($request->input('trashed'), function ($query, $trashed) {
                    if ($trashed === 'only') $query->onlyTrashed();
                    if ($trashed === 'with') $query->withTrashed();
                })
                ->latest()
                ->paginate(10)
                ->withQueryString(),
        ]);
    }
}
