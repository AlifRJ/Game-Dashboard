<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Services\UserApiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request, UserApiService $gameApi): Response
    {
        $filters = $request->only(['search', 'trashed']);

        $result = $gameApi->getPaginatedUsers(
            filters: $filters,
            perPage: $request->integer('per_page', 15),
            forceRefresh: $request->boolean('refresh'),
        );

        return Inertia::render('User/Index', [
            'users'   => $result['users']->appends($filters),
            'filters' => $filters,
            'error'   => $result['error'],
        ]);
    }

    public function show(Request $request, string $id, UserApiService $gameApi): Response
    {
        abort_unless(Str::isUuid($id), 404);

        $result = $gameApi->getUserById(
            id: $id,
            forceRefresh: $request->boolean('refresh'),
        );

        if ($result['status'] === 404) {
            abort(404, 'User not found.');
        }

        return Inertia::render('User/Details', [
            'user'  => $result['user'],
            'error' => $result['error'],
        ]);
    }

    public function create(): Response
    {   
        return Inertia::render('User/Form');
    }

    public function store(CreateUserRequest $request, UserApiService $gameApi): RedirectResponse
    {   
        $validated = $request->validated();

        $result = $gameApi->createUser([
            'name'     => $validated['name'],
            'username' => $validated['username'],
            'email'    => $validated['email'],
            'password' => $validated['password'],
        ]);

        if ($result['user'] === null) {
            $errors = !empty($result['errors'])
                ? $result['errors']
                : ['email' => $result['message']];

            throw ValidationException::withMessages($errors);
        }

        return to_route('user.index')->with('message', 'User Added Successfully');
    }

    public function edit(string $id, UserApiService $gameApi): Response
    {   
        abort_unless(Str::isUuid($id), 404);

        $result = $gameApi->getUserById($id, forceRefresh: true);

        if ($result['status'] === 404 || $result['user'] === null) {
            abort(404, 'User not found.');
        }
        
        return Inertia::render('User/Form', [
            'user' => $result['user'],
        ]);
    }

    public function update(UpdateUserRequest $request, string $id, UserApiService $gameApi): RedirectResponse
    {   
        abort_unless(Str::isUuid($id), 404);

        $validated = $request->validated();

        $payload = [
            'name'     => $validated['name'],
            'username' => $validated['username'],
            'email'    => $validated['email'],
        ];

        // Only send password if the admin actually typed a new one
        if (!empty($validated['password'])) {
            $payload['password'] = $validated['password'];
        }

        $result = $gameApi->updateUser($id, $payload);

        if ($result['status'] === 404) {
            abort(404, 'User not found.');
        }

        if ($result['user'] === null) {
            $errors = !empty($result['errors'])
                ? $result['errors']
                : ['email' => $result['message']];

            throw ValidationException::withMessages($errors);
        }

        return to_route('user.index')->with('message', 'User Updated Successfully');
    }

    public function destroy(string $id, UserApiService $gameApi): RedirectResponse
    {
        abort_unless(Str::isUuid($id), 404);

        $result = $gameApi->deleteUser($id);

        if ($result['status'] === 404) {
            abort(404, 'User not found.');
        }

        if (!$result['success']) {
            return back()->with('error', $result['message']);
        }

        return to_route('user.index')->with('message', 'User Deleted Successfully');
    }

    public function restore(string $id, UserApiService $gameApi): RedirectResponse
    {
        abort_unless(Str::isUuid($id), 404);

        $result = $gameApi->restoreUser($id);

        if ($result['status'] === 404) {
            abort(404, 'User not found.');
        }

        if (!$result['success']) {
            return back()->with('error', $result['message']);
        }

        return to_route('user.index')->with('message', 'User Restored Successfully');
    }

    public function forceDelete(string $id, UserApiService $gameApi): RedirectResponse
    {
        abort_unless(Str::isUuid($id), 404);

        $result = $gameApi->forceDeleteUser($id);

        if ($result['status'] === 404) {
            abort(404, 'User not found.');
        }

        if (!$result['success']) {
            return back()->with('error', $result['message']);
        }

        return to_route('user.index')->with('message', 'User Permanently Deleted');
    }
}
