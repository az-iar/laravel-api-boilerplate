<?php

namespace App\Http\Controllers;

use App\Http\Transformers\UserTransformer;
use App\User;
use Dingo\Api\Http\Request;
use JWTAuth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $this->validate($request, ['name' => 'required', 'email' => 'required|email|unique:users', 'password' => 'required']);

        $user = new User($request->only('name', 'email', 'password'));
        $user->save();

        return response()->json([
            'data' => $user->transform(new UserTransformer),
            'token' => JWTAuth::fromUser($user)
        ])->setStatusCode(201);
    }

    public function login(Request $request)
    {
        $this->validate($request, ['email' => 'required|email', 'password' => 'required']);

        if ($token = JWTAuth::attempt($request->only('email', 'password'))) {
            $user = User::whereEmail($request->email)->first();

            return response()->json([
                'data' => $user->transform(new UserTransformer),
                'token' => $token
            ]);
        }

        $this->response->errorUnauthorized('Invalid credentials!');
    }

    public function refresh()
    {
        $current_token = JWTAuth::getToken();
        $token = JWTAuth::refresh($current_token);

        return response()->json(compact('token'));
    }

    public function me()
    {
        $user = $this->user;

        return response()->json([
            'data' => $user->transform(),
        ]);
    }
}