<?php

namespace Tests\Feature;

use App\Mail\FeedbackReplyMail;
use App\Models\FeedbackMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class FeedbackFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_submit_feedback_with_default_baru_status(): void
    {
        $this->seed();

        $user = User::query()->where('email', 'mahasiswa@demo.test')->firstOrFail();
        $this->actingAs($user);

        $response = $this->post(route('feedback.store'), [
            'name' => 'Nama Palsu',
            'email' => 'palsu@example.com',
            'category' => FeedbackMessage::CATEGORY_KOMPLAIN,
            'phone' => '081234567890',
            'message' => 'Saya ingin menyampaikan komplain resmi karena proses layanan terlalu lama.',
            'website' => '',
        ]);

        $response->assertRedirect(route('feedback.create'));

        $this->assertDatabaseHas('feedback_messages', [
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'category' => FeedbackMessage::CATEGORY_KOMPLAIN,
            'status' => FeedbackMessage::STATUS_BARU,
        ]);
    }

    public function test_admin_reply_updates_feedback_and_sends_email(): void
    {
        $this->seed();
        Mail::fake();

        $admin = User::query()->where('email', 'superadmin@demo.test')->firstOrFail();
        $feedback = FeedbackMessage::query()->create([
            'user_id' => null,
            'name' => 'Pengirim Demo',
            'email' => 'pengirim@example.test',
            'phone' => '081111111111',
            'category' => FeedbackMessage::CATEGORY_SARAN,
            'message' => 'Terima kasih atas layanannya, mohon ditingkatkan waktu responsnya.',
            'status' => FeedbackMessage::STATUS_BARU,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.feedback.update', $feedback), [
                'status' => FeedbackMessage::STATUS_DIPROSES,
                'admin_reply' => 'Terima kasih atas sarannya. Kami sudah menindaklanjuti.',
            ])
            ->assertRedirect();

        $feedback->refresh();

        $this->assertSame(FeedbackMessage::STATUS_DIPROSES, $feedback->status);
        $this->assertSame('Terima kasih atas sarannya. Kami sudah menindaklanjuti.', $feedback->admin_reply);
        $this->assertSame($admin->id, $feedback->replied_by);
        $this->assertNotNull($feedback->replied_at);

        Mail::assertSent(FeedbackReplyMail::class, function (FeedbackReplyMail $mail) use ($feedback): bool {
            $rendered = $mail->render();

            return $mail->hasTo($feedback->email)
                && (int) $mail->feedback->id === (int) $feedback->id
                && Str::contains($rendered, 'Pesan Anda sebelumnya')
                && Str::contains($rendered, e($feedback->message))
                && Str::contains($rendered, e((string) $feedback->admin_reply));
        });
    }
}
