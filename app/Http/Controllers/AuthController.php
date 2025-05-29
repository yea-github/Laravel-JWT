<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use App\Models\User;

class AuthController extends Controller
{

    public function _construct(){
        $this->middleware(['auth:api', ['except'=> ['login', 'register']]]);
    }

    public function register(Request $request){

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }

        $user = User::create(array_merge(
            $validator->validated(),
            ['password'=> bcrypt($request->password)]
        ));

        return response()->json([
            'message'=> 'User successfully registered',
            'user'=> $user
        ], 201);

        /*
        $user = User::create([
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'password' => Hash::make($request->get('password')),
        ]);
        */

        /*
        $token = JWTAuth::fromUser($user);

        return response()->json(compact('user','token'), 201);
        */
    }

    public function login(Request $request){

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(), 422);
        }

        if(!$token=auth()->attempt($validator->validated())){
            return response()->json(['error' => 'Unauthorized', 401]);
        }



        /*
        if(!Auth::attempt($validator->validated()){
            return response()->json(['error' => 'Unauthorized'], 401)
        })
            */

        return $this->createNewToken($token);

    }

    public function createNewToken($token){
        return response()->json([
            'access_token'=> $token,
            'token_type'=> 'Bearer',
            'expires_in'=> auth()->factory()->getTTL() * 60,
            'user'=> auth()->user()
        ]);
    }

    public function profile(){
        return response()->json(auth()->user());
    }

    public function logout(Request $request){
        auth()->logout();
        return response()->json([
            'message'=> 'User successfully signed out'
        ]);
    }
}
