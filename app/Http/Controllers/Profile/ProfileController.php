<?php

namespace App\Http\Controllers\Profile;

use App\Support\Api;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController {
    /**
     * Obtener los datos básicos del perfil del usuario autenticado,
     * incluyendo el estado del asistente de configuración inicial.
     */
    public function show(Request $request): JsonResponse {
        return Api::response()->success(
            data: [
                'profile' => $request->user()->toProfileArray(),
            ]
        );
    }

    /**
     * Marcar (o desmarcar) el asistente de configuración inicial como completado.
     * Idempotente: marcar dos veces como completado conserva la fecha original.
     */
    public function updateSetup(Request $request): JsonResponse {
        $validated = $request->validate([
            'is_setup_completed' => ['required', 'boolean'],
        ]);

        $user = $request->user();
        $completed = (bool) $validated['is_setup_completed'];

        // Solo se escribe al cambiar de estado, así se conserva la fecha original
        if ($completed !== $user->isSetupCompleted()) {
            $user->forceFill([
                'setup_completed_at' => $completed ? now() : null,
            ])->save();
        }

        return Api::response()->success(
            message: $completed
                ? 'Configuración inicial marcada como completada.'
                : 'Configuración inicial reiniciada.',
            data: [
                'profile' => $user->toProfileArray(),
            ]
        );
    }
}
