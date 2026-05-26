<?php

namespace Tests\Feature;

use Tests\TestCase;

class AuthTest extends TestCase
{
    public function test_user_login_page_can_be_displayed(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_user_register_page_can_be_displayed(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_admin_login_page_can_be_displayed(): void
    {
        $response = $this->get('/admin/login');

        $response->assertStatus(200);
    }
}