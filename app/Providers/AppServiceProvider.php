<?php

namespace App\Providers;

use App\Console\Commands\GenerateDummyDocx;
use App\Console\Commands\WarmDocumentConverter;
use App\Services\Documents\DocxPlaceholderExtractor;
use App\Services\Documents\DocumentAssemblerService;
use App\Services\Documents\DocumentGateService;
use App\Services\Documents\DocumentRequestInitializer;
use App\Services\Documents\DocumentServiceSetupService;
use App\Services\Documents\DocumentSignerService;
use App\Services\Documents\ServiceDocumentReadinessChecker;
use App\Services\AuditLogger;
use App\Services\DocumentNumberService;
use App\Services\HtmlSanitizer;
use App\Services\RequestWorkflowService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(HtmlSanitizer::class);
        $this->app->singleton(AuditLogger::class);
        $this->app->singleton(DocumentNumberService::class);
        $this->app->singleton(RequestWorkflowService::class);

        // Document module services
        $this->app->singleton(DocxPlaceholderExtractor::class);
        $this->app->singleton(ServiceDocumentReadinessChecker::class);
        $this->app->singleton(DocumentServiceSetupService::class);
        $this->app->singleton(DocumentRequestInitializer::class);
        $this->app->singleton(DocumentGateService::class);
        $this->app->singleton(DocumentSignerService::class);
        $this->app->singleton(DocumentAssemblerService::class);
    }

    public function boot(): void
    {
        // Rate limiting (security-by-default)
        RateLimiter::for('auth', fn (Request $request) =>
            Limit::perMinute(10)->by(($request->ip()).'|'.strtolower((string) $request->input('email')))
        );

        RateLimiter::for('uploads', fn (Request $request) =>
            Limit::perMinute(20)->by($request->user()?->id ?: $request->ip())
        );

        RateLimiter::for('downloads', fn (Request $request) =>
            Limit::perMinute(30)->by($request->user()?->id ?: $request->ip())
        );

        RateLimiter::for('status-change', fn (Request $request) =>
            Limit::perMinute(30)->by($request->user()?->id ?: $request->ip())
        );

        RateLimiter::for('approvals', fn (Request $request) =>
            Limit::perMinute(30)->by($request->user()?->id ?: $request->ip())
        );

        // CMS autosave should be generous but still bounded to prevent abuse.
        RateLimiter::for('cms-autosave', fn (Request $request) =>
            Limit::perMinute(60)->by($request->user()?->id ?: $request->ip())
        );

        // Short form-field translation helper (admin-only).
        RateLimiter::for('translate', fn (Request $request) =>
            Limit::perMinute(60)->by($request->user()?->id ?: $request->ip())
        );

        // Public feedback form (auth-only): keep tighter limit against abuse.
        RateLimiter::for('feedback-submit', fn (Request $request) =>
            Limit::perMinute(3)->by(($request->user()?->id ?: 'guest') . '|' . $request->ip())
        );

        // Custom Artisan commands
        $this->commands([
            GenerateDummyDocx::class,
            WarmDocumentConverter::class,
        ]);

    }
}
