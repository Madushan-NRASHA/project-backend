<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class UserController extends Controller
{
    // Get all users
    public function index()
    {
        try {
            $users = User::all();
            return response()->json([
                'success' => true,
                'data' => $users
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving users: ' . $e->getMessage()
            ], 500);
        }
    }

    // Get user by ID
    public function show($id)
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $user
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving user: ' . $e->getMessage()
            ], 500);
        }
    }

    // Paginated users
    public function getUsersPaginated(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10);
            $users = User::paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $users->items(),
                'pagination' => [
                    'current_page' => $users->currentPage(),
                    'per_page' => $users->perPage(),
                    'total' => $users->total(),
                    'last_page' => $users->lastPage()
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // Search users
    public function search(Request $request)
    {
        $query = $request->input('query');

        if (!$query) {
            return response()->json(['success' => false, 'message' => 'Query is required'], 400);
        }

        $users = User::where('name', 'like', "%$query%")
                    ->orWhere('email', 'like', "%$query%")
                    ->get();

        return response()->json(['success' => true, 'data' => $users], 200);
    }

    // Create user
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users',
                'password' => 'required|string|min:8',
                'user_type' => 'in:user,admin'
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'user_type' => $request->user_type ?? 'user'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'data' => $user
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // Update user
    public function update(Request $request, $id)
    {
        try {
            $user = User::find($id);
            if (!$user) return response()->json(['success' => false, 'message' => 'User not found'], 404);

            $request->validate([
                'name' => 'string|max:255',
                'email' => 'email|unique:users,email,' . $id,
                'user_type' => 'in:user,admin'
            ]);

            $user->update($request->only(['name', 'email', 'user_type']));

            return response()->json(['success' => true, 'message' => 'User updated', 'data' => $user], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // Update only user_type
    public function updateUserType(Request $request, $id)
    {
        try {
            $user = User::find($id);
            if (!$user) return response()->json(['success' => false, 'message' => 'User not found'], 404);

            $request->validate(['user_type' => 'required|in:user,admin']);

            $user->user_type = $request->user_type;
            $user->save();

            return response()->json(['success' => true, 'message' => 'User type updated', 'data' => $user], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // Delete user
    public function destroy($id)
    {
        try {
            $user = User::find($id);
            if (!$user) return response()->json(['success' => false, 'message' => 'User not found'], 404);

            $user->delete();

            return response()->json(['success' => true, 'message' => 'User deleted'], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
