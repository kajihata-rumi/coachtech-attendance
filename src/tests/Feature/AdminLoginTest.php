<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_is_required_on_admin_login(): void
    {
        $response = $this->post('/admin/login', [
            'email' => '',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください',
        ]);
    }

    public function test_password_is_required_on_admin_login(): void
{
    $response = $this->post('/admin/login', [
        'email' => 'admin@example.com',
        'password' => '',
    ]);

    $response->assertSessionHasErrors([
        'password' => 'パスワードを入力してください',
    ]);
}

public function test_admin_login_fails_with_invalid_credentials(): void
{
    $response = $this->post('/admin/login', [
        'email' => 'not-admin@example.com',
        'password' => 'wrong-password',
    ]);

    $response->assertSessionHasErrors([
        'email' => 'ログイン情報が登録されていません',
    ]);
}
}