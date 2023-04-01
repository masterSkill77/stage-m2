<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\UnauthorizedException;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required',
            'password' => 'required'
        ]);
        $email = $request->input('email');
        $password = $request->input('password');
        if (Auth::attempt(['email' => $email, 'password' => $password])) {
            $user = User::where('email', $email)->first();
            $token = $user->createToken(env('SECRET_TOKEN', 'test_token'));

            return response()->json(['user' => $user, 'access_token' => $token->plainTextToken]);
        };
        return response()->json(["message" => "Unauthorized"], Response::HTTP_UNAUTHORIZED);
    }
}