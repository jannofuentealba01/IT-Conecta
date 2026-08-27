<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProductionBootstrapSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_test_admin_is_not_seeded_in_production(): void
    {
        $this->app->detectEnvironment(fn (): string => 'production');

        app(UserSeeder::class)->run();

        $this->assertDatabaseMissing('users', ['email' => 'admin@test.com']);
    }

    public function test_secure_admin_can_be_created_with_interactive_command(): void
    {
        $this->artisan('app:create-admin')
            ->expectsQuestion('Nombre del administrador', 'Administración IT Conecta')
            ->expectsQuestion('Correo del administrador', 'admin@itconecta.cl')
            ->expectsQuestion('Contraseña segura (mínimo 12 caracteres)', 'ClaveSegura-2026!')
            ->expectsQuestion('Repite la contraseña', 'ClaveSegura-2026!')
            ->expectsOutput('Cuenta administradora creada correctamente.')
            ->assertSuccessful();

        $admin = User::where('email', 'admin@itconecta.cl')->firstOrFail();

        $this->assertSame('admin', $admin->rol);
        $this->assertSame('approved', $admin->approval_status);
        $this->assertNotNull($admin->email_verified_at);
        $this->assertTrue(Hash::check('ClaveSegura-2026!', $admin->password));
    }

    public function test_admin_command_rejects_mismatched_passwords(): void
    {
        $this->artisan('app:create-admin')
            ->expectsQuestion('Nombre del administrador', 'Administración')
            ->expectsQuestion('Correo del administrador', 'admin@itconecta.cl')
            ->expectsQuestion('Contraseña segura (mínimo 12 caracteres)', 'ClaveSegura-2026!')
            ->expectsQuestion('Repite la contraseña', 'UnaClaveDiferente!')
            ->expectsOutput('Las contraseñas no coinciden. No se creó ninguna cuenta.')
            ->assertFailed();

        $this->assertDatabaseMissing('users', ['email' => 'admin@itconecta.cl']);
    }

    public function test_production_check_passes_with_secure_configuration(): void
    {
        $this->app->detectEnvironment(fn (): string => 'production');
        config([
            'app.debug' => false,
            'app.url' => 'https://itconecta.cl',
            'app.key' => 'base64:'.base64_encode(random_bytes(32)),
            'session.secure' => true,
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => 'smtp.example.com',
        ]);

        $this->artisan('app:production-check --skip-database')
            ->expectsOutput('IT Conecta cumple las comprobaciones esenciales de producción.')
            ->assertSuccessful();
    }
}
