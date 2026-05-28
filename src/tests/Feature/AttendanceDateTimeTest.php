<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceDateTimeTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_current_date_and_time_are_displayed_on_attendance_page(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-01 08:00:00'));

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        /** @var User $user */
$user = User::factory()->create([
    'email_verified_at' => now(),
]);
        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('2026年6月1日');
        $response->assertSee('08:00');
    }
}