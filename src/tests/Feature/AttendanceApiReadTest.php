<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\AttendanceRecord;

class AttendanceApiReadTest extends TestCase
{
    use RefreshDatabase;

    public function test_勤怠一覧がJSONで取得できる()
    {
        $user = User::factory()->create(['role' => User::ROLE_STAFF]);

        AttendanceRecord::create([
            'user_id' => $user->id,
            'date' => today(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => 'finished',
        ]);
        AttendanceRecord::create([
            'user_id' => $user->id,
            'date' => today()->subDay(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => 'finished',
        ]);
        AttendanceRecord::create([
            'user_id' => $user->id,
            'date' => today()->subDays(2),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => 'finished',
        ]);

        $response = $this->getJson('/api/v1/attendance-records');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data',
            'meta' => ['current_page', 'last_page', 'per_page', 'total'],
        ]);
    }

    public function test_勤怠詳細がJSONで取得できる()
    {
        $user = User::factory()->create(['role' => User::ROLE_STAFF]);

        $attendance = AttendanceRecord::create([
            'user_id' => $user->id,
            'date' => today(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => 'finished',
        ]);

        $response = $this->getJson("/api/v1/attendance-records/{$attendance->id}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => ['id', 'user', 'breaks', 'applications'],
        ]);
    }

    public function test_存在しないIDでは404とエラーJSONが返る()
    {
        $response = $this->getJson('/api/v1/attendance-records/99999');

        $response->assertStatus(404);
        $response->assertJson([
            'error' => '勤怠情報が見つかりませんでした。',
        ]);
    }
}
