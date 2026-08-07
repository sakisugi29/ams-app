<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\AttendanceRecord;

class AttendanceApiWriteTest extends TestCase
{
    use RefreshDatabase;

    public function test_勤怠が作成される()
    {
        $user = User::factory()->create(['role' => User::ROLE_STAFF]);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/attendance-records', [
            'date' => today()->format('Y-m-d'),
            'clock_in' => '09:00:00',
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('attendance_records', [
            'user_id' => $user->id,
            'date' => today()->format('Y-m-d'),
        ]);
    }

    public function test_バリデーションエラー時に422と日本語エラーメッセージが返る()
    {
        $user = User::factory()->create(['role' => User::ROLE_STAFF]);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/attendance-records', [
            'date' => '',
            'clock_in' => '',
        ]);

        $response->assertStatus(422);
        $response->assertJsonFragment([
            'date' => ['勤怠日は必須です。'],
        ]);
    }

    public function test_勤怠が更新される()
    {
        $user = User::factory()->create(['role' => User::ROLE_STAFF]);
        $attendance = AttendanceRecord::create([
            'user_id' => $user->id,
            'date' => today(),
            'clock_in' => '09:00:00',
            'status' => 'finished',
        ]);

        $response = $this->actingAs($user, 'sanctum')->putJson("/api/v1/attendance-records/{$attendance->id}", [
            'date' => today()->format('Y-m-d'),
            'clock_in' => '10:00:00',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('attendance_records', [
            'id' => $attendance->id,
            'clock_in' => '10:00:00',
        ]);
    }

    public function test_存在しないIDに対してPUTを実行すると404が返る()
    {
        $user = User::factory()->create(['role' => User::ROLE_STAFF]);

        $response = $this->actingAs($user, 'sanctum')->putJson('/api/v1/attendance-records/99999', [
            'date' => today()->format('Y-m-d'),
            'clock_in' => '09:00:00',
        ]);

        $response->assertStatus(404);
    }

    public function test_勤怠が削除される()
    {
        $user = User::factory()->create(['role' => User::ROLE_STAFF]);
        $attendance = AttendanceRecord::create([
            'user_id' => $user->id,
            'date' => today(),
            'clock_in' => '09:00:00',
            'status' => 'finished',
        ]);

        $response = $this->actingAs($user, 'sanctum')->deleteJson("/api/v1/attendance-records/{$attendance->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('attendance_records', [
            'id' => $attendance->id,
        ]);
    }

    public function test_存在しないIDに対してDELETEを実行すると404が返る()
    {
        $user = User::factory()->create(['role' => User::ROLE_STAFF]);

        $response = $this->actingAs($user, 'sanctum')->deleteJson('/api/v1/attendance-records/99999');

        $response->assertStatus(404);
    }
}
