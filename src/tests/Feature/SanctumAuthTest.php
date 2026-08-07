<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\AttendanceRecord;

class SanctumAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_未認証時に書き込み系APIで401が返る()
    {
        $user = User::factory()->create(['role' => User::ROLE_STAFF]);
        $attendance = AttendanceRecord::create([
            'user_id' => $user->id,
            'date' => today(),
            'clock_in' => '09:00:00',
            'status' => 'finished',
        ]);

        // 認証せずにPOST
        $this->postJson('/api/v1/attendance-records', [
            'date' => today()->format('Y-m-d'),
            'clock_in' => '09:00:00',
        ])->assertStatus(401)
          ->assertJson(['message' => 'Unauthenticated.']);

        // 認証せずにPUT
        $this->putJson("/api/v1/attendance-records/{$attendance->id}", [
            'date' => today()->format('Y-m-d'),
            'clock_in' => '10:00:00',
        ])->assertStatus(401);

        // 認証せずにDELETE
        $this->deleteJson("/api/v1/attendance-records/{$attendance->id}")
            ->assertStatus(401);
    }

    public function test_認証済みユーザーは自分の勤怠を更新削除できる()
    {
        $user = User::factory()->create(['role' => User::ROLE_STAFF]);
        $attendance = AttendanceRecord::create([
            'user_id' => $user->id,
            'date' => today(),
            'clock_in' => '09:00:00',
            'status' => 'finished',
        ]);

        $this->actingAs($user, 'sanctum')->putJson("/api/v1/attendance-records/{$attendance->id}", [
            'date' => today()->format('Y-m-d'),
            'clock_in' => '10:00:00',
        ])->assertStatus(200);

        $this->actingAs($user, 'sanctum')->deleteJson("/api/v1/attendance-records/{$attendance->id}")
            ->assertStatus(204);
    }

    public function test_他ユーザーの勤怠を更新削除しようとすると403が返る()
    {
        $owner = User::factory()->create(['role' => User::ROLE_STAFF]);
        $otherUser = User::factory()->create(['role' => User::ROLE_STAFF]);

        $attendance = AttendanceRecord::create([
            'user_id' => $owner->id,
            'date' => today(),
            'clock_in' => '09:00:00',
            'status' => 'finished',
        ]);

        $this->actingAs($otherUser, 'sanctum')->putJson("/api/v1/attendance-records/{$attendance->id}", [
            'date' => today()->format('Y-m-d'),
            'clock_in' => '10:00:00',
        ])->assertStatus(403);

        $this->actingAs($otherUser, 'sanctum')->deleteJson("/api/v1/attendance-records/{$attendance->id}")
            ->assertStatus(403);
    }
}
