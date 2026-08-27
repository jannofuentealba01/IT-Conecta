<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccountExperienceTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_navigation_exposes_one_profile_link_and_one_logout_action(): void
    {
        $teacher = User::factory()->create();

        $response = $this->actingAs($teacher)->get(route('teacher.dashboard'));

        $response->assertOk()->assertSee('Mi perfil');
        $this->assertSame(1, substr_count($response->getContent(), 'href="'.route('profile.edit').'"'));
        $this->assertSame(1, substr_count($response->getContent(), 'action="'.route('logout').'"'));
    }

    public function test_admin_navigation_uses_the_same_account_actions_without_duplicate_logout(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);

        $response = $this->actingAs($admin)->get(route('admin.teachers.index'));

        $response->assertOk()
            ->assertSee('Mi perfil')
            ->assertSee('Administración');
        $this->assertSame(1, substr_count($response->getContent(), 'href="'.route('profile.edit').'"'));
        $this->assertSame(1, substr_count($response->getContent(), 'action="'.route('logout').'"'));
    }

    public function test_shared_profile_returns_each_role_to_its_own_area(): void
    {
        $teacher = User::factory()->create();
        $admin = User::factory()->create(['rol' => 'admin']);

        $this->actingAs($teacher)->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('Cuenta · Área docente')
            ->assertSee('← Volver a área docente')
            ->assertSee('href="'.route('teacher.dashboard').'"', false);

        $this->actingAs($admin)->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('Cuenta · Administración')
            ->assertSee('← Volver a administración')
            ->assertSee('href="'.route('admin.teachers.index').'"', false);
    }

    public function test_admin_and_teacher_areas_remain_separated(): void
    {
        $teacher = User::factory()->create();
        $admin = User::factory()->create(['rol' => 'admin']);

        $this->actingAs($admin)
            ->get(route('teacher.dashboard'))
            ->assertForbidden();

        $this->actingAs($teacher)
            ->get(route('admin.teachers.index'))
            ->assertForbidden();
    }

    public function test_login_ignores_an_intended_url_from_the_other_role(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);

        $response = $this->withSession(['url.intended' => route('teacher.dashboard')])
            ->post(route('login'), [
                'email' => $admin->email,
                'password' => 'password',
            ]);

        $response->assertRedirect(route('admin.teachers.index', absolute: false));
        $response->assertSessionMissing('url.intended');
    }

    public function test_admin_logout_invalidates_the_authenticated_session(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);

        $this->actingAs($admin)
            ->post(route('logout'))
            ->assertRedirect('/');

        $this->assertGuest();
    }
}
