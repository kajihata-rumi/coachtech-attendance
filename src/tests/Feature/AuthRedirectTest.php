<?php

namespace Tests\Feature;

use Tests\TestCase;

class AuthRedirectTest extends TestCase
{
    public function test_guest_user_is_redirected_from_attendance_page(): void
    {
        $response = $this->get('/attendance');

        $response->assertRedirect('/login');
    }

    public function test_guest_user_is_redirected_from_attendance_list_page(): void
    {
        $response = $this->get('/attendance/list');

        $response->assertRedirect('/login');
    }

    public function test_guest_admin_is_redirected_from_admin_attendance_page(): void
    {
        $response = $this->get('/admin/attendance/list');

        $response->assertRedirect('/admin/login');
    }

    public function test_guest_admin_is_redirected_from_admin_staff_list_page(): void
    {
        $response = $this->get('/admin/staff/list');

        $response->assertRedirect('/admin/login');
    }
}