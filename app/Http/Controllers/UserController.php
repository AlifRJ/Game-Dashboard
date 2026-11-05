<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $request->only(['search', 'trashed']);

        return Inertia::render('User/Index', [
            'filters' => $filters,
            'users'   => User::query()
                ->filter($filters)
                ->latest()
                ->paginate(10)
                ->withQueryString(),
        ]);
    }

    public function show(User $user): Response
    {   
        return Inertia::render('User/Details', [
            'user' => $user
        ]);
    }

    public function create(): Response
    {   
        return Inertia::render('User/Form');
    }

    public function store(CreateUserRequest $request): RedirectResponse
    {   
        $validated = $request->validated();

        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return to_route('user.index')->with('message', 'User Added Successfully');
    }

    public function edit(User $user): Response
    {   
        return Inertia::render('User/Form', [
            'user' => $user
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {   
        $validated = $request->validated();

        // Hash the password if provided, or remove it so it isn't overwritten
        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return to_route('user.index')->with('message', 'User Updated Successfully');
    }

    public function destroy(User $user)
    {
        // Prevent the logged-in user from deleting their own account
        if (auth()->id() === $user->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return to_route('user.index')->with('message', 'User Deleted Successfully');
    }

    public function restore(User $user): RedirectResponse
    {
        $user->restore();

        return to_route('user.index')->with('message', 'User Restored Successfully');
    }

    public function forceDelete(User $user): RedirectResponse
    {
        $user->forceDelete();

        return to_route('user.index')->with('message', 'User Permanently Deleted');
    }
}
