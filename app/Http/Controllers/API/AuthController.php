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
            'password' => 'required|string|min:8',
            'password_confirmation' => 'required|string|same:password',
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

        // Handle file uploads
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imageName = uniqid() . '.' . $image->getClientOriginalExtension();
                $path = $image->storeAs('images', $imageName, 'public');
                $imagePaths[] = $path;
            }
        }

        // Create user
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'user_type' => 'user',
            'user_theme' => 0,
            'Profile_Pic' => implode(',', $imagePaths), // Fixed typo: was 'Porofile_Pic'
            'password' => Hash::make($data['password']),
        ]);

        // Create token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'User registered successfully',
            'user' => $user,
            'token' => $token,
        ], 201);

    } catch (\Exception $e) {
        // Log the error for debugging
        \Log::error('Registration error: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'Registration failed. Please try again.',
            'error' => $e->getMessage() // Remove this in production
        ], 500);
    }
}

   public function login(Request $request)
{
    $validator = Validator::make($request->all(), [
        'email'    => 'required|email',
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

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'user'    => [
                'id'          => $user->id,
                'name'        => $user->name,
                'email'       => $user->email,
                'user_type'   => $user->user_type,
                'user_theme'  => $user->user_theme,
                'Profile_Pic' => $user->Profile_Pic,
            ],
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Login failed: ' . $e->getMessage(),
        ], 500);
    }
}

    public function logout(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }
     public function updateTheme(Request $request)
    {
        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id',
                'user_theme' => 'required|integer|in:0,1', // 0 = light, 1 = dark
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 400);
            }

            // Find the user
            $user = User::find($request->user_id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            // Update the user's theme
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
            return response()->json([
                'success' => false,
                'message' => 'Server error occurred',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}