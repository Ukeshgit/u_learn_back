<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;

class UserController extends Controller
{
    /**
     * Register a new user
     */
    public function createUser(Request $request)
    {
        try {
            $validateUser = Validator::make($request->all(), [
                'avatar' => 'required',
                'type' => 'required',
                'open_id' => 'required',
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required'
            ]);

            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validateUser->errors()
                ], 422);
            }

            $validated = $validateUser->validated();

            // Check if user exists based on type and open_id (e.g., for social login)
            $map = [
                'email'   => $validated['email'],      // check by email
                'type'    => $validated['type'],       // optionally keep this
                'open_id' => $validated['open_id']     // optionally keep this
            ];
            
            $existingUser = User::where($map)->first();
        

            if (!$existingUser) {
                // Create new user
                $validated['token'] = md5(uniqid() . rand(10000, 99999));
                $validated['created_at'] = Carbon::now();
                $validated['password'] = Hash::make($validated['password']);

                // Insert and retrieve ID
                $userID = User::insertGetId($validated);
                $userInfo = User::find($userID);

                // Generate personal access token
                $accessToken = $userInfo->createToken(uniqid())->plainTextToken;
                $userInfo->access_token = $accessToken;

                // Save token in DB
                User::where('id', $userID)->update(['access_token' => $accessToken]);

                return response()->json([
                    'status' => true,
                    'message' => 'User created successfully',
                    'user' => $userInfo
                ], 201);
            }

            // If user already exists (e.g., already logged in before with same social login)
            $accessToken = $existingUser->createToken(uniqid())->plainTextToken;
            $existingUser->access_token = $accessToken;
            User:where('open_id','=', validated['open_id'])->update(['access_token' => $accessToken]);


            return response()->json([
                'status' => true,
                'message' => 'User already exists, returning existing user info',
                'user' => $existingUser,
                'token' => $accessToken
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Login the user
     */
    public function loginUser(Request $request)
    {
        try {
            $validateUser = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required'
            ]);

            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validateUser->errors()
                ], 422);
            }

            if (!Auth::attempt($request->only('email', 'password'))) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid email or password'
                ], 401);
            }

            $user = User::where('email', $request->email)->first();

            return response()->json([
                'status' => true,
                'message' => 'User logged in successfully',
                'user' => $user,
                'token' => $user->createToken("API TOKEN")->plainTextToken
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $th->getMessage()
            ], 500);
        }
    }
}
