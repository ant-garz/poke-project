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

    public function show($id)
    {
        $user = User::withTrashed()->findOrFail($id);

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'deleted_at' => $user->deleted_at,
            'roles' => $user->roles->pluck('name'),
        ]);
    }

    public function destroy(User $user)
    {
        $user->delete();

        return response()->json(['success' => true]);
    }

    public function restore(User $user)
    {
        $user->restore();

        return response()->json(['success' => true]);
    }
}