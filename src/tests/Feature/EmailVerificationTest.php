<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registered_user_is_created_as_unverified(): void
    {
        Notification::fake();

        $this->post('/register', [
            'name' => 'テスト 太郎',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $user = User::where('email', 'test@example.com')->first();

        $this->assertNotNull($user);
        $this->assertNull($user->email_verified_at);

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_unverified_user_is_redirected_to_email_verification_notice(): void
    {
        $user = $this->createUnverifiedUser();

        $response = $this->actingAs($user)->get(route('attendance.index'));

        $response->assertRedirect(route('verification.notice'));

        $noticeResponse = $this->actingAs($user)->get(route('verification.notice'));

        $noticeResponse->assertStatus(200);
        $noticeResponse->assertSee('登録していただいたメールアドレスに認証メールを送付しました。');
        $noticeResponse->assertSee('メール認証を完了してください。');
        $noticeResponse->assertSee('認証はこちらから');
        $noticeResponse->assertSee('認証メールを再送する');
    }

    public function test_user_can_verify_email_with_valid_verification_link(): void
    {
        $user = $this->createUnverifiedUser();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => sha1($user->getEmailForVerification()),
            ]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        $response->assertStatus(302);

        $this->assertTrue($user->fresh()->hasVerifiedEmail());

        $attendanceResponse = $this->actingAs($user->fresh())->get(route('attendance.index'));

        $attendanceResponse->assertStatus(200);
    }

    public function test_verification_email_can_be_resent(): void
    {
        Notification::fake();

        $user = $this->createUnverifiedUser();

        $response = $this
            ->actingAs($user)
            ->post(route('verification.send'));

        $response->assertSessionHas('status', 'verification-link-sent');

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    private function createUnverifiedUser(): User
    {
        /** @var User $user */
        $user = User::factory()->create([
            'name' => 'テスト 太郎',
            'email' => 'test@example.com',
            'role' => 'user',
            'email_verified_at' => null,
        ]);

        return $user;
    }
}
