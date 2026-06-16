<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        return response()->json([
            'data' => User::query()
                ->select('id', 'name', 'email', 'deleted_at')
                ->orderBy('name')
                ->withTrashed()
                ->get(),
        ]);
    }

    public function show(int $user)
    {
        $user = User::withTrashed()->findOrFail($user);

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'deleted_at' => $user->deleted_at,
            'roles' => $user->roles->pluck('name'),
        ]);
    }

    public function destroy(int $user)
    {
        User::findOrFail($user)->delete();

        return response()->json([
            'success' => true,
        ]);
    }

    public function restore(int $user)
    {
        User::withTrashed()->findOrFail($user)->restore();

        return response()->json([
            'success' => true,
        ]);
    }

    public function updateRoles(Request $request, User $user)
    {
        if (! auth()->user()->can('manage users')) {
            abort(403);
        }

        $roles = collect($request->input('roles', []));

        if (! $roles->contains('user')) {
            $roles->push('user');
        }

        if (
            $user->hasRole('admin') &&
            auth()->id() !== $user->id &&
            ! $roles->contains('admin')
        ) {
            abort(403, 'Cannot remove admin role from another admin');
        }

        $user->syncRoles($roles->unique()->values());

        return response()->json([
            'roles' => $user->roles->pluck('name'),
        ]);
    }
}