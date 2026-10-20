<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class SettingsController extends Controller
{
    /**
     * Met à jour le profil de l'utilisateur connecté.
     * Le mot de passe n'est modifié que si l'ancien est fourni et correct.
     */
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'lastname' => ['required', 'string', 'max:100'],
            'username' => [
                'required',
                'string',
                'max:25',
                'regex:/^[A-Za-z0-9._-]+$/',
                Rule::unique('users', 'username')->ignore($user->id),
            ],
            'current_password' => ['nullable', 'required_with:password', 'current_password:sanctum'],
            'password' => ['nullable', 'confirmed', Password::min(8)],
        ]);

        $user->name = $data['name'];
        $user->lastname = $data['lastname'];
        $user->username = $data['username'];

        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        return response()->json($user->load('pack', 'configuration'));
    }
}
