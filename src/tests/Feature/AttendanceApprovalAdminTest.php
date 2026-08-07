<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\AttendanceRecord;

class AttendanceApprovalAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_承認待ちの修正申請が全て表示されている()
    {
        $admin = User::factory()->create(['role'=>User::ROLE_ADMIN]);
        $user1 = User::factory()->create(['role' => User::ROLE_STAFF]);
        $user2 = User::factory()->create(['role' => User::ROLE_STAFF]);


        $attendance1 = AttendanceRecord::create([
            'user_id' => $user1->id,
            'date' => today(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => 'finished',
        ]);

        $attendance1->correctionRequests()->create(['status' => '承認待ち']);

        $attendance2 = AttendanceRecord::create([
            'user_id' => $user2->id,
            'date' => today(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => 'finished',
        ]);

        $attendance2->correctionRequests()->create(['status' => '承認待ち']);

        $response = $this->actingAs($admin)->get(route('application.index',['tab' => 'pending']));

        $response->assertStatus(200);
        $response->assertSee($user1->name);
        $response->assertSee($user2->name);
        $response->assertSee(today()->format('Y/m/d'));
    }

    public function test_承認済みの修正申請が全て表示されている()
    {
        $admin = User::factory()->create(['role'=>User::ROLE_ADMIN]);
        $user1 = User::factory()->create(['role' => User::ROLE_STAFF]);
        $user2 = User::factory()->create(['role' => User::ROLE_STAFF]);

        $attendance1 = AttendanceRecord::create([
            'user_id' => $user1->id,
            'date' => today(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => 'finished',
        ]);

        $attendance1->correctionRequests()->create(['status' => '承認済み']);

        $attendance2 = AttendanceRecord::create([
            'user_id' => $user2->id,
            'date' => today(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => 'finished',
        ]);

        $attendance2->correctionRequests()->create(['status' => '承認済み']);

        $response = $this->actingAs($admin)->get(route('application.index',['tab' => 'approved']));

        $response->assertStatus(200);
        $response->assertSee($user1->name);
        $response->assertSee($user2->name);
        $response->assertSee(today()->format('Y/m/d'));
    }

    public function test_修正申請の内容が正しく表示されている()
    {
        $admin = User::factory()->create(['role'=>User::ROLE_ADMIN]);
        $user = User::factory()->create(['role' => User::ROLE_STAFF]);

        $attendance = AttendanceRecord::create([
            'user_id' => $user->id,
            'date' => today(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => 'finished',
        ]);

        $correctionRequest = $attendance->correctionRequests()->create([
            'status' => '承認待ち',
        ]);

        $correctionRequest->details()->create([
            'field_name' => 'comment',
            'before_value' => null,
            'after_value' => '修正理由のテスト',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.application-detail',['attendance_correct_request_id' => $correctionRequest->id]));

        $response->assertStatus(200);
        $response->assertSee($user->name);
        $response->assertSee('修正理由のテスト');
    }

    public function test_修正申請の承認処理が正しく行われている()
    {
        $admin = User::factory()->create(['role'=>User::ROLE_ADMIN]);
        $user = User::factory()->create(['role' => User::ROLE_STAFF]);

        $attendance = AttendanceRecord::create([
            'user_id' => $user->id,
            'date' => today(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => 'finished',
        ]);

        $correctionRequest = $attendance->correctionRequests()->create([
            'status' => '承認待ち',
        ]);

        $response = $this->actingAs($admin)->patch(route('admin.application-update', ['attendance_correct_request_id' => $correctionRequest->id]));

        $response->assertStatus(200);

        $this->assertDatabaseHas('correction_requests', [
            'id' => $correctionRequest->id,
            'status' => '承認済み',
        ]);
    }
}
