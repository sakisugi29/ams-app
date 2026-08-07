<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class AttendanceDateTimeTest extends TestCase
{
    use RefreshDatabase;

    public function test_現在の日時情報がUIと同じ形式で出力されている()
    {
        $user = User::factory()->create(['role' => User::ROLE_STAFF]);

        $this->travelTo(now()->setTime(10, 30, 0));

        $response = $this->actingAs($user)->get(route('attendance.index'));

        $response->assertStatus(200);
        $response->assertSee(now()->format('Y年n月j日'));
        $response->assertSee(now()->format('H:i'));
    }
}
