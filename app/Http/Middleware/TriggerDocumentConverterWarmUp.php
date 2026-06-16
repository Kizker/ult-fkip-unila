<?php

namespace App\Http\Middleware;

use App\Console\Commands\WarmDocumentConverter;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Symfony\Component\HttpFoundation\Response;

class TriggerDocumentConverterWarmUp
{
    public function handle(Request $request, Closure $next): Response
    {
        $this->dispatchWarmUpIfNeeded();

        return $next($request);
    }

    private function dispatchWarmUpIfNeeded(): void
    {
        try {
            if (app()->runningInConsole()) {
                return;
            }

            if (Cache::has(WarmDocumentConverter::READY_KEY)) {
                return;
            }

            if (!Cache::add(WarmDocumentConverter::DISPATCH_LOCK_KEY, 1, now()->addMinutes(5))) {
                return;
            }

            Process::path(base_path())
                ->quietly()
                ->start([
                    PHP_BINARY,
                    'artisan',
                    'ult:warm-document-converter',
                ]);
        } catch (\Throwable $e) {
            Cache::forget(WarmDocumentConverter::DISPATCH_LOCK_KEY);

            Log::warning('doc.preview.warmup_dispatch_failed', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
