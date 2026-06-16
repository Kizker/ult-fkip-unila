<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuditLogger;
use App\Support\VerificationEmailDispatcher;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationController extends Controller
{
    private function dashboardRouteFor(?\App\Models\User $user): string
    {
        if (!$user) return route('home');

        $adminPerms = [
            'requests.view_any',
            'requests.view_unit',
            'requests.review_ult',
            'requests.process_unit',
            'approvals.unit.sign',
            'approvals.faculty.sign',
            'document_numbers.issue',
            'services.manage',
            'cms.manage',
            'site_settings.manage',
            'academics.manage',
            'users.manage',
            'audit_logs.view',
            'doc_services.manage',
            'doc_services.publish',
            'doc_templates.upload',
            'doc_placeholders.manage',
            'doc_signers.manage',
            'feedbacks.manage',
            'doc_requests.gate',
            'doc_requests.assemble',
        ];

        if ($user->can('requests.view_own')) return route('student.dashboard');
        if ($user->canAny($adminPerms)) return route('admin.dashboard');
        if ($user->can('doc_signoffs.decide')) return route('signer.requests.inbox');

        return route('home');
    }

    public function notice()
    {
        return view('auth.verify-email');
    }

    public function verify(EmailVerificationRequest $request, AuditLogger $audit): RedirectResponse
    {
        $request->fulfill();
        $audit->log('auth.email_verified', 'users', (string) $request->user()->id, [], $request);

        return redirect()->intended($this->dashboardRouteFor($request->user()));
    }

    public function send(Request $request, AuditLogger $audit, VerificationEmailDispatcher $verificationMail): RedirectResponse
    {
        $result = $verificationMail->send($request->user());
        $audit->log('auth.email_verification_sent', 'users', (string) $request->user()->id, [
            'sent' => (bool) ($result['sent'] ?? false),
            'error' => $result['raw_error'] ?? null,
        ], $request);

        if (!(bool) ($result['sent'] ?? false)) {
            return back()->with('error', $result['error'] ?? 'Email verifikasi gagal dikirim.');
        }

        return back()->with('status', __('verification-link-sent'));
    }
}
