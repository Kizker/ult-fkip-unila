<?php

namespace App\Console\Commands;

use App\Services\Documents\DocumentAssemblerService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class WarmDocumentConverter extends Command
{
    public const READY_KEY = 'doc.preview.warmup.ready';
    public const DISPATCH_LOCK_KEY = 'doc.preview.warmup.dispatching';

    protected $signature = 'ult:warm-document-converter';

    protected $description = 'Warm up DOCX to PDF conversion so first user requests do not hit LibreOffice initialization.';

    public function handle(DocumentAssemblerService $assembler): int
    {
        $this->components->info('Warming up DOCX to PDF converter...');

        $ok = $assembler->warmUpPdfConverter();
        if (!$ok) {
            Cache::forget(self::READY_KEY);
            Cache::forget(self::DISPATCH_LOCK_KEY);
            $this->components->error('Warm-up failed. Check laravel.log for soffice output.');
            return self::FAILURE;
        }

        Cache::put(self::READY_KEY, now()->toDateTimeString(), now()->addHours(12));
        Cache::forget(self::DISPATCH_LOCK_KEY);
        $this->components->info('Warm-up completed successfully.');
        return self::SUCCESS;
    }
}
