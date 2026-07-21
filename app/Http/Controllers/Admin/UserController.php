<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class UserController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:manage-users'),
        ];
    }

    public function index(Request $request)
    {
        $users = User::with('roles')
            ->when($request->input('search'), function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString()
            ->through(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_active' => $user->is_active,
                'roles' => $user->roles->pluck('name'),
                'created_at' => $user->created_at,
            ]);

        return Inertia::render('admin/users/index', [
            'users' => $users,
            'filters' => $request->only('search'),
        ]);
    }

    public function create()
    {
        return Inertia::render('admin/users/create', [
            'roles' => Role::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', Password::defaults()],
            'role' => ['required', 'string', 'exists:roles,name'],
            'is_active' => ['boolean'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'is_active' => $data['is_active'] ?? true,
        ]);

        $user->assignRole($data['role']);

        activity()
            ->causedBy(Auth::user())
            ->performedOn($user)
            ->withProperties(['role' => $data['role']])
            ->log('user_created');

        return redirect()
            ->route('admin.users.index')
            ->with('success', "User \"{$user->name}\" created successfully.");
    }

    public function edit(User $user)
    {
        return Inertia::render('admin/users/edit', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_active' => $user->is_active,
                'roles' => $user->roles->pluck('name'),
            ],
            'roles' => Role::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', Password::defaults()],
            'role' => ['required', 'string', 'exists:roles,name'],
            'is_active' => ['boolean'],
        ]);

        $old = $user->only(['name', 'email', 'is_active']) + ['role' => $user->roles->pluck('name')->first()];

        $updateData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'is_active' => $data['is_active'] ?? $user->is_active,
        ];

        if (! empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        $user->update($updateData);
        $user->syncRoles([$data['role']]);

        activity()
            ->causedBy(Auth::user())
            ->performedOn($user)
            ->withProperties([
                'old' => $old,
                'new' => $user->only(['name', 'email', 'is_active']) + ['role' => $data['role']],
            ])
            ->log('user_updated');

        return redirect()
            ->route('admin.users.index')
            ->with('success', "User \"{$user->name}\" updated successfully.");
    }

    public function destroy(User $user)
    {
        if ($user->id === Auth::id()) {
            return redirect()
                ->route('admin.users.index')
                ->with('error', 'You cannot deactivate your own account.');
        }

        $user->update(['is_active' => false]);

        activity()
            ->causedBy(Auth::user())
            ->performedOn($user)
            ->log('user_deactivated');

        return redirect()
            ->route('admin.users.index')
            ->with('success', "User \"{$user->name}\" has been deactivated.");
    }
}
