<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\carbon;

class UserController extends Controller
{
    /**
     * Register a new user
     */
    public function createUser(Request $request)
    {
        try {
            $validateUser = Validator::make($request->all(), [
                'avatar'=>'required',
                'type'=>'required',
                'open_id'=>'required',
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                
                
            ]);

            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validateUser->errors()
                ], 422);
            }
            //validated will have all the user field values
            //we can save in the databases
            $validated=$validateUser->validated();
            $map=[];
            //Email,phone,google,facebook or apple
            $map['type']=$validated['type'];
            $map['open_id']=$validated['open_id'];
            $user=User::where($map)->first();
            //whether already login or not
            //Empty means doesnot exist
            //then save the data in the database for the first time
            if (empty($user->id)) {
                //this certain user is not ever present in the database
                //our job is to assign the user in the database
                
                //This token is like userid
                $validated['token']=md5(uniqid().rand(10000,99999));
                //user first time login
                $validated['created_at']=carbon::now();
                //returns the id of the user after saving in the database
                $userID=User::insertGetId($validated);//only returns the userId

                $userInfo=User::where('id',"=",$userID);//returns the whole information of the particular id of user 
                $accessToken=$userInfo->createToken(uniqid())->plainTextToken;
                $userInfo->access_token=$accessToken;
                
            return response()->json([
                'status' => true,
                'message' => 'User created successfully',
                'data'=>$userInfo,

            ], 201);

            }
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password)
            ]);

            return response()->json([
                'status' => true,
                'message' => 'User created successfully',
                'user' => $user,
                'token' => $user->createToken("API TOKEN")->plainTextToken
            ], 201);

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
