<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('teacher.dashboard', absolute: false));
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }

    public function test_pending_teacher_can_not_authenticate(): void
    {
        $user = User::factory()->create([
            'approval_status' => 'pending',
            'approved_at' => null,
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_admin_is_sent_to_teacher_approval_panel(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);

        $response = $this->post('/login', [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($admin);
        $response->assertRedirect(route('admin.teachers.index', absolute: false));
    }

    public function test_teacher_login_clears_a_previous_student_session(): void
    {
        $teacher = User::factory()->create([
            'rol' => 'profesor',
            'approval_status' => 'approved',
        ]);

        $response = $this->withSession($this->studentSessionData())->post('/login', [
            'email' => $teacher->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($teacher);
        $response->assertRedirect(route('teacher.dashboard', absolute: false));
        $this->assertStudentSessionWasCleared($response);
    }

    public function test_admin_login_clears_a_previous_student_session(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);

        $response = $this->withSession($this->studentSessionData())->post('/login', [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($admin);
        $response->assertRedirect(route('admin.teachers.index', absolute: false));
        $this->assertStudentSessionWasCleared($response);
    }

    public function test_authenticated_account_identity_has_priority_over_stale_student_data(): void
    {
        $teacher = User::factory()->create([
            'name' => 'Profesor visible',
            'rol' => 'profesor',
            'approval_status' => 'approved',
        ]);

        $this->actingAs($teacher)
            ->withSession($this->studentSessionData())
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('Profesor visible')
            ->assertDontSee('Alumno anterior');
    }

    private function studentSessionData(): array
    {
        return [
            'participant_id' => 99,
            'participant_name' => 'Alumno anterior',
            'participant_course' => '4° Medio D',
            'room_id' => 10,
            'room_code' => '654321',
        ];
    }

    private function assertStudentSessionWasCleared($response): void
    {
        foreach (array_keys($this->studentSessionData()) as $key) {
            $response->assertSessionMissing($key);
        }
    }
}
