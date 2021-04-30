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
    /**
     * @OA\Post(
     *      path="/api/user/signup",
     *      tags={"Authentication"},
     *      summary="Register",
     *      operationId="register",
     *      @OA\Parameter(
     *          name="name",
     *          in="query",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="email",
     *          in="query",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="password",
     *          in="query",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="password_confirmation",
     *          in="query",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="response",
     *                  type="string"
     *              ),
     *          ),
     *      ),
     *      @OA\Response(
     *          response="422",
     *          description="Validation error",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="errors",
     *                  type="array",
     *                  @OA\Items(),
     *              ),
     *          ),
     *      ),
     *     @OA\Response(
     *          response="500",
     *          description="Something went wrong",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="response",
     *                  type="string"
     *              ),
     *          ),
     *      )
     *)
     **/

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

    /**
     * @OA\Post(
     *      path="/api/user/login",
     *      tags={"Authentication"},
     *      summary="Login",
     *      @OA\Parameter(
     *          name="email",
     *          in="query",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="password",
     *          in="query",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="password_confirmation",
     *          in="query",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="user_id",
     *                  type="integer"
     *              ),
     *              @OA\Property(
     *                  property="token",
     *                  type="string"
     *              ),
     *          ),
     *      ),
     *      @OA\Response(
     *          response="422",
     *          description="Validation error",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="errors",
     *                  type="array",
     *                  @OA\Items(),
     *              ),
     *          ),
     *      ),
     *      @OA\Response(
     *          response="401",
     *          description="Wrong credentials",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="response",
     *                  type="string",
     *              ),
     *          ),
     *      ),
     *      @OA\Response(
     *          response="404",
     *          description="User doesn't exist",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="response",
     *                  type="string",
     *              ),
     *          ),
     *      ),
     *)
     **/

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
                $response = ["response" => "Password mismatch"];

                return response()->json($response, 401);
            }
        } else {
            $response = ["response" =>'User does not exist'];

            return response()->json($response, 404);
        }
    }
}
