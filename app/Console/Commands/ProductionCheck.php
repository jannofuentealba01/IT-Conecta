<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class ProductionCheck extends Command
{
    protected $signature = 'app:production-check {--skip-database : Omite únicamente la conexión a la base de datos}';

    protected $description = 'Comprueba la configuración esencial antes de abrir IT Conecta en producción';

    public function handle(): int
    {
        $mailer = config('mail.default');
        $smtpHost = config('mail.mailers.smtp.host');

        $checks = [
            ['Entorno', app()->environment('production'), 'APP_ENV debe ser production'],
            ['Depuración', ! config('app.debug'), 'APP_DEBUG debe ser false'],
            ['URL segura', str_starts_with((string) config('app.url'), 'https://'), 'APP_URL debe comenzar con https://'],
            ['Clave de aplicación', filled(config('app.key')), 'APP_KEY debe estar generada'],
            ['Cookie segura', config('session.secure') === true, 'SESSION_SECURE_COOKIE debe ser true'],
            [
                'Correo real',
                ! in_array($mailer, ['log', 'array'], true)
                    && ($mailer !== 'smtp' || filled($smtpHost)),
                'Configura un servicio de correo real y sus datos de conexión',
            ],
            ['Permisos storage', is_writable(storage_path()), 'storage debe permitir escritura'],
            ['Permisos cache', is_writable(base_path('bootstrap/cache')), 'bootstrap/cache debe permitir escritura'],
        ];

        if (! $this->option('skip-database')) {
            try {
                DB::connection()->getPdo();
                $checks[] = ['Base de datos', true, 'Conexión disponible'];
            } catch (Throwable) {
                $checks[] = ['Base de datos', false, 'No fue posible conectar con la base configurada'];
            }
        }

        $this->table(
            ['Comprobación', 'Estado', 'Detalle'],
            array_map(fn (array $check): array => [
                $check[0],
                $check[1] ? 'OK' : 'FALTA',
                $check[1] ? 'Correcto' : $check[2],
            ], $checks)
        );

        if (collect($checks)->contains(fn (array $check): bool => ! $check[1])) {
            $this->error('IT Conecta todavía no está lista para abrirse en producción.');

            return self::FAILURE;
        }

        $this->info('IT Conecta cumple las comprobaciones esenciales de producción.');

        return self::SUCCESS;
    }
}
