<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTeacherApprovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_approve_a_pending_teacher(): void
    {
        $admin = User::factory()->create(['rol' => 'admin']);
        $teacher = User::factory()->create([
            'approval_status' => 'pending',
            'approved_at' => null,
        ]);

        $response = $this->actingAs($admin)
            ->post(route('admin.teachers.approve', $teacher));

        $response->assertRedirect();
        $teacher->refresh();
        $this->assertSame('approved', $teacher->approval_status);
        $this->assertSame($admin->id, $teacher->approved_by);
        $this->assertNotNull($teacher->approved_at);
    }

    public function test_teacher_can_not_approve_another_teacher(): void
    {
        $teacher = User::factory()->create();
        $pending = User::factory()->create([
            'approval_status' => 'pending',
            'approved_at' => null,
        ]);

        $this->actingAs($teacher)
            ->post(route('admin.teachers.approve', $pending))
            ->assertForbidden();

        $this->assertSame('pending', $pending->fresh()->approval_status);
    }
}
