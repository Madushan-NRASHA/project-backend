<?php
namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
   public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
                'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $validator->validated();
            $imagePaths = [];

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $imageName = uniqid() . '.' . $image->getClientOriginalExtension();
                    $path = $image->storeAs('images', $imageName, 'public');
                    $imagePaths[] = $path;
                }
            }

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'user_type' => 'user',
                'user_theme' => 0,
                'Profile_Pic' => implode(',', $imagePaths),
                'password' => Hash::make($data['password']),
            ]);

            // Passport token creation
            $token = $user->createToken('auth_token')->accessToken;

            return response()->json([
                'success' => true,
                'message' => 'User registered successfully',
                'user' => $user,
                'token' => $token,
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Registration error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Registration failed. Please try again.',
            ], 500);
        }
    }

public function login(Request $request)
{
    $validator = Validator::make($request->all(), [
        'email' => 'required|email',
        'password' => 'required|string',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'errors' => $validator->errors()
        ], 422);
    }

    try {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        // Sanctum token creation on login
        $token = $user->createToken('flutter-app-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'user_type' => $user->user_type,
                'user_theme' => $user->user_theme,
                'Profile_Pic' => $user->Profile_Pic,
            ],
            'token' => $token,
        ]);
    } catch (\Exception $e) {
        \Log::error('Login error: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Login failed. Please try again.',
        ], 500);
    }
}
    public function logout(Request $request)
    {
        // Revoke token for the authenticated user
        $request->user()->token()->revoke();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }
    public function updateTheme(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id',
                'user_theme' => 'required|integer|in:0,1',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 400);
            }

            $user = User::find($request->user_id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            $user->user_theme = $request->user_theme;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Theme updated successfully',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'user_theme' => $user->user_theme,
                    'Profile_Pic' => $user->Profile_Pic,
                ]
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Theme update error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update theme.',
            ], 500);
        }
    }

  

    public function updateProfile(Request $request)
    {
        try {
            $user = auth()->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255',
                'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $user->id,
                'password' => 'sometimes|required|string|min:8',
                'password_confirmation' => 'sometimes|required_with:password|string|same:password',
                'user_theme' => 'sometimes|integer',
                'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $validator->validated();
            
            if ($request->hasFile('images')) {
                $imagePaths = [];
                foreach ($request->file('images') as $image) {
                    $imageName = uniqid() . '.' . $image->getClientOriginalExtension();
                    $path = $image->storeAs('images', $imageName, 'public');
                    $imagePaths[] = $path;
                }
                $data['Profile_Pic'] = implode(',', $imagePaths);
            }

            if (isset($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }

            unset($data['password_confirmation']);
            $user->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'user' => $user->fresh(),
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Profile update error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Profile update failed. Please try again.',
            ], 500);
        }
    }

   
}