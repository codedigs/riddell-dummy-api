<?php

namespace App\Http\Controllers;

use App\User;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $email = $request->header("PHP_AUTH_USER");
        $password = $request->header("PHP_AUTH_PW");

        if ($user = User::findBy('email', $email)->first())
        {
            if (password_verify($password, $user->password))
            {
                $app_config = config('app');
                $jwt_config = config('jwt');

                if (!is_null($user->access_token))
                {
                    try {
                        $decoded = JWT::decode($user->access_token, $app_config['key'], [$jwt_config['algorithm']]);

                        return response()->json([
                            'success' => true,
                            'access_token' => $user->access_token
                        ]);
                    } catch (ExpiredException $e) {
                        goto generateNewApiTokenKey;
                    }
                }

                // generate new api tokn
                generateNewApiTokenKey:

                $now = time();

                $token = [
                    'iat' => $now,
                    'exp' => $now + $jwt_config['lifespan'],

                    'payload' => compact('name', 'email')
                ];

                $access_token = JWT::encode($token, $app_config['key'], $jwt_config['algorithm']);

                if ($user->saveAccessToken($access_token))
                {
                    return response()->json([
                        'success' => true,
                        'access_token' => $access_token
                    ]);
                }

                \Log::error("Error: saveAccessToken method in User model not working properly.");
            }
        }

        return response()->json([
            'success' => false,
            'message' => "Invalid email and password."
        ]);
    }
}
