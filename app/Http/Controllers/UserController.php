<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('User', [
            'filters' => $request->only(['search', 'trashed']),
            'users' => User::query()
                ->when($request->input('search'), function ($query, $search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('id', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%");
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
