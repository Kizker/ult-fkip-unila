<?php

namespace App\Providers;

use App\Models\Attachment;
use App\Models\DocumentNumberFormat;
use App\Models\LetterNumberFormat;
use App\Models\Request as UltRequest;
use App\Models\RequestOutput;
use App\Policies\AttachmentPolicy;
use App\Policies\DocumentNumberFormatPolicy;
use App\Policies\LetterNumberFormatPolicy;
use App\Policies\RequestPolicy;
use App\Policies\RequestOutputPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        UltRequest::class => RequestPolicy::class,
        Attachment::class => AttachmentPolicy::class,
        DocumentNumberFormat::class => DocumentNumberFormatPolicy::class,
        LetterNumberFormat::class => LetterNumberFormatPolicy::class,
        RequestOutput::class => RequestOutputPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        // Superadmin bypass for all authorization checks (useful on live if RBAC seed isn't re-run yet).
        Gate::before(function ($user, $ability) {
            if (!method_exists($user, 'hasRole')) return null;
            return $user->hasRole('Superadmin') ? true : null;
        });

        // Optional gates can be defined here.
        Gate::define('admin-access', function ($user) {
            return $user->canAny([
                'requests.view_any',
                'requests.view_unit',
                'requests.review_ult',
                'services.manage',
                'cms.manage',
                'site_settings.manage',
                'academics.manage',
                'document_numbers.manage_formats',
                'letter_numbers.manage_formats',
                'users.manage',
                'audit_logs.view',
                'feedbacks.manage',
            ]);
        });
    }
}
