<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_会員登録後認証メールが送信される()
    {
        Notification::fake();

        $response = $this->post(route('register'), [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $user = User::where('email', 'test@example.com')->first();

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_認証はこちらからボタンでメール認証サイトに遷移する()
    {
        $user = User::factory()->create(['email_verified_at' => null]);

        $response = $this->actingAs($user)->get(route('verification.notice'));

        $response->assertStatus(200);
        $response->assertSee('http://localhost:8025', false);
    }

    public function test_メール認証を完了すると勤怠登録画面に遷移する()
    {
        $user = User::factory()->create(['email_verified_at' => null, 'role' => User::ROLE_STAFF]);

        $verifyUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->actingAs($user)->get($verifyUrl);

        $response->assertRedirect(route('attendance.index'));
        $this->assertNotNull($user->fresh()->email_verified_at);
    }
}
