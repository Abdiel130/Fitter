<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Support\Api;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController {
    /**
     * Iniciar sesión en la API (Android / Móvil).
     * Emite un par de tokens: access_token (30 días) y refresh_token (180 días).
     */
    public function login(Request $request): JsonResponse {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return Api::response()->unauthorized(
                message: 'Las credenciales proporcionadas son incorrectas.',
                errors: [
                    'email' => ['Credenciales inválidas. Verifica tu correo y contraseña.'],
                ]
            );
        }

        $deviceName = $validated['device_name'] ?? 'Android Device';

        // 1. Access Token: Para sincronización y operaciones habituales (30 días)
        $accessToken = $user->createToken(
            $deviceName . ' (Access)',
            ['access-api'],
            now()->addDays(30)
        );

        // 2. Refresh Token: Para renovación biométrica tras expiración (180 días)
        $refreshToken = $user->createToken(
            $deviceName . ' (Refresh)',
            ['issue-token'],
            now()->addDays(180)
        );

        return Api::response()->success(
            message: 'Inicio de sesión exitoso.',
            data: [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'current_routine' => $user->current_routine,
                    'active_sequence_index' => $user->active_sequence_index,
                    'created_at' => $user->created_at,
                ],
                'tokens' => [
                    'token_type' => 'Bearer',
                    'access_token' => $accessToken->plainTextToken,
                    'access_token_expires_at' => $accessToken->accessToken->expires_at?->toIso8601String(),
                    'refresh_token' => $refreshToken->plainTextToken,
                    'refresh_token_expires_at' => $refreshToken->accessToken->expires_at?->toIso8601String(),
                ],
            ]
        );
    }

    /**
     * Renovar el token mediante Refresh Token desbloqueado por Biometría Android.
     * Aplica rotación estricta: destruye el refresh token usado y emite un nuevo par.
     */
    public function refresh(Request $request): JsonResponse {
        $user = $request->user();
        $currentToken = $user->currentAccessToken();

        // Validar que el token presentado tenga explícitamente el permiso 'issue-token'
        if (!$currentToken || !$user->tokenCan('issue-token')) {
            return Api::response()->forbidden(
                message: 'No autorizado. Se requiere un Refresh Token válido para esta operación.'
            );
        }

        // Obtener el identificador del dispositivo
        $rawName = $currentToken->name ?? 'Android Device';
        $deviceName = trim(str_replace('(Refresh)', '', $rawName));
        if (empty($deviceName)) {
            $deviceName = 'Android Device';
        }

        // ROTACIÓN DE TOKENS: Destruir inmediatamente el refresh token presentado
        $currentToken->delete();

        // Emitir nuevo par de tokens
        $newAccessToken = $user->createToken(
            $deviceName . ' (Access)',
            ['access-api'],
            now()->addDays(30)
        );

        $newRefreshToken = $user->createToken(
            $deviceName . ' (Refresh)',
            ['issue-token'],
            now()->addDays(180)
        );

        return Api::response()->success(
            message: 'Tokens renovados exitosamente.',
            data: [
                'tokens' => [
                    'token_type' => 'Bearer',
                    'access_token' => $newAccessToken->plainTextToken,
                    'access_token_expires_at' => $newAccessToken->accessToken->expires_at?->toIso8601String(),
                    'refresh_token' => $newRefreshToken->plainTextToken,
                    'refresh_token_expires_at' => $newRefreshToken->accessToken->expires_at?->toIso8601String(),
                ],
            ]
        );
    }

    /**
     * Cierre de sesión seguro.
     * Revoca los tokens asociados al dispositivo actual (o todos si se solicita).
     */
    public function logout(Request $request): JsonResponse {
        $user = $request->user();
        $currentToken = $user->currentAccessToken();

        if ($currentToken) {
            $baseName = trim(str_replace(['(Access)', '(Refresh)'], '', $currentToken->name));

            // Si se pasa 'all_devices' => true, revoca todo. Sino, solo el dispositivo actual
            if ($request->boolean('all_devices')) {
                $user->tokens()->delete();
                $message = 'Se cerraron todas las sesiones en todos los dispositivos.';
            } else {
                $user->tokens()->where('name', 'like', "%{$baseName}%")->delete();
                $message = 'Sesión cerrada correctamente en este dispositivo.';
            }
        } else {
            $message = 'Sesión cerrada correctamente.';
        }

        return Api::response()->success(message: $message);
    }

    /**
     * Obtener el perfil y datos del usuario autenticado actual.
     */
    public function me(Request $request): JsonResponse {
        $user = $request->user();

        return Api::response()->success(
            data: [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'current_routine' => $user->current_routine,
                    'active_sequence_index' => $user->active_sequence_index,
                    'created_at' => $user->created_at,
                    'current_routine_details' => $user->currentRoutine,
                ],
            ]
        );
    }
}
