<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\UserCreatedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function register(array $data)
    {
        $data['password'] = Hash::make($data['password']);
        $user = new User($data);
        $user->save();
        $token = $user->createToken(env('SECRET_TOKEN', 'test_token'));
        $user->notify(new UserCreatedNotification($user, $token->plainTextToken));
        return ['user' => $user, 'token' => $token->plainTextToken];
    }

    public function verifyEmail($userId, $token)
    {
        $user = User::findOrFail($userId);
        $user->email_verified_at = now();
        return $user;
    }
}
