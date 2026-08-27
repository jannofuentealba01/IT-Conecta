<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateAdmin extends Command
{
    protected $signature = 'app:create-admin';

    protected $description = 'Crea de forma segura la cuenta administradora de IT Conecta';

    public function handle(): int
    {
        $name = trim((string) $this->ask('Nombre del administrador'));
        $email = mb_strtolower(trim((string) $this->ask('Correo del administrador')));
        $password = (string) $this->secret('Contraseña segura (mínimo 12 caracteres)');
        $confirmation = (string) $this->secret('Repite la contraseña');

        if (! hash_equals($password, $confirmation)) {
            $this->error('Las contraseñas no coinciden. No se creó ninguna cuenta.');

            return self::FAILURE;
        }

        $validator = Validator::make(
            compact('name', 'email', 'password'),
            [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255'],
                'password' => ['required', 'string', 'min:12'],
            ]
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $existing = User::where('email', $email)->first();
        if ($existing && $existing->rol !== 'admin') {
            $this->error('Ese correo ya pertenece a una cuenta que no es administradora.');

            return self::FAILURE;
        }

        if ($existing && ! $this->confirm('La cuenta administradora ya existe. ¿Deseas actualizar su nombre y contraseña?')) {
            $this->warn('No se realizaron cambios.');

            return self::SUCCESS;
        }

        $admin = $existing ?? new User;
        $admin->forceFill([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'rol' => 'admin',
            'approval_status' => 'approved',
            'approved_at' => now(),
            'approved_by' => null,
            'email_verified_at' => now(),
        ])->save();

        $this->info($existing
            ? 'Cuenta administradora actualizada correctamente.'
            : 'Cuenta administradora creada correctamente.');

        return self::SUCCESS;
    }
}
