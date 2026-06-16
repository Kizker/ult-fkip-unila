<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFeedbackMessageRequest;
use App\Models\FeedbackMessage;
use App\Models\HeroBanner;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;

class FeedbackController extends Controller
{
    public function __construct(
        private readonly AuditLogger $audit,
    ) {
    }

    public function create()
    {
        $heroBanner = HeroBanner::query()
            ->where('is_active', true)
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->first();

        return view('public.feedback.create', compact('heroBanner'));
    }

    public function store(StoreFeedbackMessageRequest $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user !== null, 403);

        $data = $request->validated();

        $feedback = FeedbackMessage::create([
            'user_id' => $user->id,
            'name' => (string) $user->name,
            'email' => strtolower((string) $user->email),
            'phone' => $data['phone'] ?? null,
            'category' => (string) $data['category'],
            'message' => (string) $data['message'],
            'status' => FeedbackMessage::STATUS_BARU,
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
        ]);

        $this->audit->log(
            'feedback.submitted',
            'feedback_messages',
            (string) $feedback->id,
            [
                'category' => $feedback->category,
                'status' => $feedback->status,
            ],
            $request,
            $user
        );

        return redirect()
            ->route('feedback.create')
            ->with('status', 'Kritik/saran berhasil dikirim. Terima kasih atas masukannya.');
    }
}
