<?php

namespace App\Http\Controllers;

use App\User;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\SignatureInvalidException;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        // $app_config = config('app');
        // $jwt_config = config('jwt');
        // $token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VyX2VtYWlsIjoidGVzdEBhZGFtcy5jb20iLCJwbF9jYXJ0X2lkIjoiMDEyYjYxODIxMWQ3IiwiaXRlbXMiOlt7ImxpbmVfaXRlbV9pZCI6IjAwMDA0OTAwNDhfNzIxYmMiLCJjdXRfaWQiOjkzfSx7ImxpbmVfaXRlbV9pZCI6IjAwMDA0OTAwNDhfNzdhYmMiLCJjdXRfaWQiOjkzfSx7ImxpbmVfaXRlbV9pZCI6IjAwMDA0OTAwNDhfNTMzN2QiLCJjdXRfaWQiOjk0fV0sImV4cCI6MTU2Njk5OTA1Nn0.SDlCx1v8oJlyHDUDG01f25vR4wFV13PtloNm2An7dyo";

        // try {
        //     $decoded = JWT::decode($token, "aKKjfi38nf9jfJL83Kk93lm383fn3Kj3mfnbx", [$jwt_config['algorithm']]);
        // } catch (SignatureInvalidException $e) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => $e->getMessage()
        //     ]);
        // }

        // return response()->json($decoded);

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
