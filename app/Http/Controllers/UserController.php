<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function signup(Request $request)
    {   
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed'
        ]);

        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()], 422);
        }

        $request['password']=Hash::make($request['password']);
        $request['remember_token'] = Str::random(10);
        if (User::create($request->toArray())) {
            return response()->json(["response" => "You have successfully signed up"], 200);
        } else {
            return response()->json(["response" => "Something went wrong while registration"], 500);
        }
        // $token = $user->createToken('Laravel Password Grant Client')->accessToken;
        // $response = ['token' => $token];
    }

    public function login(Request $request)
    {   
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()], 422);
        }

        $user = User::where('email', $request->email)->first();

        if ($user) {
            if (Hash::check($request->password, $user->password)) {
                $token = $user->createToken('Laravel Password Grant Client')->accessToken;
                $response = [
                                'user_id' => $user->id,
                                'token' => $token,
                            ];
                
                return response()->json($response, 200);
            } else {
                $response = ["message" => "Password mismatch"];

                return response()->json($response, 422);
            }
        } else {
            $response = ["message" =>'User does not exist'];

            return response()->json($response, 422);
        }
    }
}
