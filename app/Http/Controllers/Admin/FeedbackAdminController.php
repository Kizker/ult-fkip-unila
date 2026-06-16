<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateFeedbackMessageRequest;
use App\Mail\FeedbackReplyMail;
use App\Models\FeedbackMessage;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class FeedbackAdminController extends Controller
{
    public function __construct(
        private readonly AuditLogger $audit,
    ) {
    }

    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $status = strtoupper(trim((string) $request->query('status', '')));
        $category = strtoupper(trim((string) $request->query('category', '')));

        if (!in_array($status, FeedbackMessage::STATUSES, true)) {
            $status = '';
        }

        if (!in_array($category, FeedbackMessage::CATEGORIES, true)) {
            $category = '';
        }

        $items = FeedbackMessage::query()
            ->with(['user:id,name,email', 'repliedByUser:id,name'])
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($category !== '', fn ($query) => $query->where('category', $category))
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($where) use ($q) {
                    $where->where('name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%")
                        ->orWhere('phone', 'like', "%{$q}%")
                        ->orWhere('message', 'like', "%{$q}%");
                });
            })
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.feedback.index', compact('items', 'q', 'status', 'category'));
    }

    public function show(FeedbackMessage $feedback)
    {
        $feedback->loadMissing(['user:id,name,email', 'repliedByUser:id,name']);

        return view('admin.feedback.show', compact('feedback'));
    }

    public function update(UpdateFeedbackMessageRequest $request, FeedbackMessage $feedback)
    {
        $data = $request->validated();

        $newStatus = (string) $data['status'];
        $newReply = $data['admin_reply'] ?? null;
        $hadReply = filled($feedback->admin_reply);
        $replyChanged = filled($newReply) && trim((string) $newReply) !== trim((string) $feedback->admin_reply);

        $feedback->status = $newStatus;

        if (filled($newReply)) {
            $feedback->admin_reply = (string) $newReply;
            $feedback->replied_by = $request->user()?->id;
            $feedback->replied_at = now();
        }

        $feedback->save();

        $emailSent = false;
        $emailError = null;

        if ($replyChanged) {
            try {
                Mail::to($feedback->email)->send(new FeedbackReplyMail($feedback->fresh()));
                $emailSent = true;
            } catch (\Throwable $e) {
                $emailError = $e->getMessage();
            }
        }

        $this->audit->log(
            'feedback.updated',
            'feedback_messages',
            (string) $feedback->id,
            [
                'status' => $newStatus,
                'reply_added' => filled($newReply),
                'reply_changed' => $replyChanged,
                'had_reply_before' => $hadReply,
                'email_sent' => $emailSent,
                'email_error' => $emailError,
            ],
            $request
        );

        if ($replyChanged && !$emailSent) {
            return back()->with('status', 'Status tersimpan, tetapi email balasan gagal dikirim. Periksa konfigurasi mail dan coba lagi.');
        }

        return back()->with('status', 'Data kritik/saran berhasil diperbarui.');
    }
}
