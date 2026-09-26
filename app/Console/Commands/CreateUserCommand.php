<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

/**
 * Alta de cuentas exclusivamente desde la consola del servidor.
 * Fitter no expone un endpoint público de registro: solo quien tiene
 * acceso al servidor (Docker / SSH) puede crear usuarios.
 */
class CreateUserCommand extends Command {
    protected $signature = 'fitter:user:create
                            {--name= : Nombre visible del usuario}
                            {--email= : Correo electrónico (identificador de login)}';

    protected $description = 'Crea una cuenta de usuario de Fitter (no existe registro público)';

    public function handle(): int {
        $name = $this->option('name') ?: $this->ask('Nombre');
        $email = strtolower(trim((string) ($this->option('email') ?: $this->ask('Correo electrónico'))));

        // La contraseña siempre se pide de forma oculta para que no quede en el historial del shell
        $password = $this->secret('Contraseña (mínimo 12 caracteres, mayúsculas, minúsculas y números)');
        $confirmation = $this->secret('Confirmar contraseña');

        $validator = Validator::make(
            [
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'password_confirmation' => $confirmation,
            ],
            [
                'name' => ['required', 'string', 'max:100'],
                'email' => ['required', 'string', 'email', 'max:150', 'unique:users,email'],
                'password' => ['required', 'confirmed', Password::defaults()],
            ]
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
        ]);

        $this->info("Usuario creado correctamente: {$user->email} ({$user->id})");

        return self::SUCCESS;
    }
}
