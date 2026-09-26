<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

/**
 * Recuperación de contraseña exclusivamente desde la consola del servidor.
 * Fitter no expone un flujo público de "olvidé mi contraseña".
 */
class ResetUserPasswordCommand extends Command {
    protected $signature = 'fitter:user:password
                            {--email= : Correo electrónico de la cuenta}
                            {--keep-sessions : No revocar los tokens activos del usuario}';

    protected $description = 'Restablece la contraseña de una cuenta de Fitter y cierra sus sesiones activas';

    public function handle(): int {
        $email = strtolower(trim((string) ($this->option('email') ?: $this->ask('Correo electrónico'))));

        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("No existe ninguna cuenta con el correo {$email}.");

            return self::FAILURE;
        }

        // La contraseña siempre se pide de forma oculta para que no quede en el historial del shell
        $password = $this->secret('Nueva contraseña (mínimo 12 caracteres, mayúsculas, minúsculas y números)');
        $confirmation = $this->secret('Confirmar nueva contraseña');

        $validator = Validator::make(
            [
                'password' => $password,
                'password_confirmation' => $confirmation,
            ],
            [
                'password' => ['required', 'confirmed', Password::defaults()],
            ]
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $user->forceFill([
            'password' => Hash::make($password),
        ])->save();

        $this->info("Contraseña actualizada para {$user->email}.");

        // Por seguridad se cierran todas las sesiones: si la contraseña se filtró, los tokens también pudieron hacerlo
        if (!$this->option('keep-sessions')) {
            $revoked = $user->tokens()->delete();
            $this->info("Sesiones cerradas en todos los dispositivos ({$revoked} tokens revocados).");
        }

        return self::SUCCESS;
    }
}
