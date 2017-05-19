<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Transformers\UserTransformer;
use App\User;
use Dingo\Api\Http\Request;
use JWTAuth;

class AuthController extends ApiController
{
    public function register(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required',
        ]);

        $user = new User($request->input());
        $user->password = bcrypt($request->password);
        $user->save();

        return $this->response->item($user, new UserTransformer)
                              ->meta('token', JWTAuth::fromUser($user))
                              ->statusCode(201);
    }

    public function login(Request $request)
    {
        $this->validate($request, ['email' => 'required|email', 'password' => 'required']);

        if ($token = JWTAuth::attempt($request->only('email', 'password'))) {
            $user = JWTAuth::toUser($token);

            return $this->response->item($user, new UserTransformer)->meta('token', $token);
        }

        $this->response->errorUnauthorized('Invalid credentials!');
    }

    public function refresh()
    {
        $token = JWTAuth::refresh(JWTAuth::getToken());

        return $this->response->array(compact('token'));
    }

    public function me()
    {
        return $this->response->item($this->user, new UserTransformer);
    }
}