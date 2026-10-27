<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $users = User::select('id', 'name', 'username', 'email', 'role', 'email_verified_at', 'created_at', 'updated_at')
            ->orderBy('id', 'asc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $users
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'nullable|string|max:100|unique:users,username',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,kasir',
        ]);

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username ?? strtolower(str_replace(' ', '', $request->name)),
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'email_verified_at' => Carbon::now(), // Auto-verified when added by admin
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Akun berhasil dibuat.',
            'data' => $user
        ], 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'nullable|string|max:100|unique:users,username,' . $id,
            'email' => 'required|email|max:255|unique:users,email,' . $id,
            'password' => 'nullable|string|min:6',
            'role' => 'required|in:admin,kasir',
        ]);

        $user->name = $request->name;
        $user->username = $request->username ?? $user->username;
        $user->email = $request->email;
        $user->role = $request->role;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Akun berhasil diperbarui.',
            'data' => $user
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $user = User::findOrFail($id);

        // Prevent deleting the main admin
        if ($user->id === 1 || $user->role === 'admin' && User::where('role', 'admin')->count() <= 1) {
            return response()->json([
                'status' => 'error',
                'message' => 'Akun admin utama tidak dapat dihapus.'
            ], 422);
        }

        $user->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Akun berhasil dihapus.'
        ]);
    }
}
