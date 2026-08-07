<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthRegistrationTest extends TestCase
{
    use RefreshDatabase;

    private function validParams(array $overrides = []): array
    {
        return array_merge([
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ], $overrides);
    }

    public function test_名前が未入力の場合バリデーションメッセージが表示される()
    {
        $response = $this->post(route('register'), $this->validParams(['name' => '']));

        $response->assertSessionHasErrors([
            'name' => 'お名前を入力してください',
        ]);
    }

    public function test_メールアドレスが未入力の場合バリデーションメッセージが表示される()
    {
        $response = $this->post(route('register'), $this->validParams(['email' => '']));

        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください',
        ]);
    }

    public function test_パスワードが8文字未満の場合バリデーションメッセージが表示される()
    {
        $response = $this->post(route('register'), $this->validParams([
            'password' => 'short1',
            'password_confirmation' => 'short1',
        ]));

        $response->assertSessionHasErrors([
            'password' => 'パスワードは8文字以上で入力してください',
        ]);
    }

    public function test_パスワードが一致しない場合バリデーションメッセージが表示される()
    {
        $response = $this->post(route('register'), $this->validParams([
            'password' => 'password123',
            'password_confirmation' => 'differentpass',
        ]));

        $response->assertSessionHasErrors([
            'password' => 'パスワードと一致しません',
        ]);
    }

    public function test_パスワードが未入力の場合バリデーションメッセージが表示される()
    {
        $response = $this->post(route('register'), $this->validParams([
            'password' => '',
            'password_confirmation' => '',
        ]));

        $response->assertSessionHasErrors([
            'password' => 'パスワードを入力してください',
        ]);
    }

    public function test_フォームに内容が入力されていた場合データが正常に保存される()
    {
        $response = $this->post(route('register'), $this->validParams());

        $this->assertDatabaseHas('users', [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
        ]);
    }
}
