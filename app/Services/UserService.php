<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserConfig;
use App\Notifications\UserCreatedNotification;
use Error;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function updateProfile(array $data, $userId)
    {
        return DB::transaction(function () use ($data, $userId) {
            try {
                $user = User::findOrFail($userId);
                $$data['password'] = Hash::make($data['password']);

                $user->fill($data);
                $user->save();
                if (is_array($data['profile_image']))
                    $data['profile_image'] = ($data['profile_image'][0]['content']);
                else
                    $data['profile_image'] = ($data['profile_image']);
                $config = UserConfig::where('user_id', $userId)->first();
                $config->fill($data);
                $config->save();
                DB::commit();
                return ['user' => $user];
            } catch (Exception $e) {
                DB::rollBack();
                throw new Error($e->getMessage());
            }
        });
    }
    public function register(array $data)
    {

        return DB::transaction(function () use ($data) {
            try {

                $data['password'] = Hash::make($data['password']);

                $user = new User($data);
                $user->save();
                $data['profile_image'] = ($data['profile_image'][0]['content']);

                $config = new UserConfig($data);
                $config->user_id = $user->id;
                $config->save();
                $token = $user->createToken(env('SECRET_TOKEN', 'test_token'));
                $user->notify(new UserCreatedNotification($user, $token->plainTextToken));
                DB::commit();
                return ['user' => $user, 'token' => $token->plainTextToken];
            } catch (Exception $e) {
                DB::rollBack();
                throw new Error($e->getMessage());
            }
        });
    }

    public function verifyEmail($userId, $token)
    {
        $user = User::findOrFail($userId);
        $user->email_verified_at = now();
        $user->save();
        return $user;
    }
}
