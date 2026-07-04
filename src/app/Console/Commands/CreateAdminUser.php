<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateAdminUser extends Command
{
    protected $signature = 'aegis:create-admin {--email=} {--name=} {--password=}';

    protected $description = 'Crear un usuario administrador para el panel de Aegis Filter';

    public function handle(): int
    {
        $email = $this->option('email') ?: $this->ask('Email del administrador');
        $name = $this->option('name') ?: $this->ask('Nombre del administrador');
        $password = $this->option('password') ?: $this->secret('Contraseña (mínimo 8 caracteres)');

        $validator = Validator::make(
            ['email' => $email, 'name' => $name, 'password' => $password],
            [
                'email' => ['required', 'email', 'unique:users,email'],
                'name' => ['required', 'string', 'max:255'],
                'password' => ['required', 'string', 'min:8'],
            ]
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
        ]);

        $this->info("Administrador '{$email}' creado correctamente.");

        return self::SUCCESS;
    }
}
