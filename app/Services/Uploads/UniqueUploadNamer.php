<?php

namespace App\Services\Uploads;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UniqueUploadNamer
{
    public function makePathForUploadedFile(
        string $disk,
        string $directory,
        string $dataType,
        UploadedFile $file,
        string $fallbackExt = 'bin'
    ): string {
        $ext = $this->detectExtension($file, $fallbackExt);
        return $this->makePath($disk, $directory, $dataType, $ext);
    }

    public function makePath(
        string $disk,
        string $directory,
        string $dataType,
        ?string $extension = null
    ): string {
        $dir = trim($directory, '/');
        $type = $this->normalizeDataType($dataType);
        $ext = $this->normalizeExtension($extension);
        $dateCode = now()->format('Ymd');

        // Retry in case a generated name already exists (e.g. cache reset).
        for ($attempt = 0; $attempt < 8; $attempt++) {
            $seq = $this->nextSequence($type, $dateCode);
            $filename = $this->buildFilename($type, $dateCode, $seq, $ext);
            $path = "{$dir}/{$filename}";
            if (!Storage::disk($disk)->exists($path)) {
                return $path;
            }
        }

        $seq = $this->nextSequence($type, $dateCode);
        $filename = $this->buildFilename($type, $dateCode, $seq, $ext, Str::lower(Str::random(4)));
        return "{$dir}/{$filename}";
    }

    private function buildFilename(string $type, string $dateCode, int $seq, string $ext, ?string $suffix = null): string
    {
        $seqCode = str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
        $name = "{$type}_{$dateCode}_{$seqCode}";
        if ($suffix !== null && $suffix !== '') {
            $name .= "_{$suffix}";
        }
        return $ext !== '' ? "{$name}.{$ext}" : $name;
    }

    private function nextSequence(string $type, string $dateCode): int
    {
        $key = "upload_seq:{$type}:{$dateCode}";
        $ttl = now()->addDays(3);

        Cache::add($key, 0, $ttl);
        $next = (int) Cache::increment($key);

        if ($next < 1) {
            Cache::put($key, 1, $ttl);
            return 1;
        }

        return $next;
    }

    private function detectExtension(UploadedFile $file, string $fallbackExt): string
    {
        $raw = $file->getClientOriginalExtension()
            ?: $file->extension()
            ?: $fallbackExt;

        return $this->normalizeExtension($raw);
    }

    private function normalizeDataType(string $value): string
    {
        $normalized = Str::slug(Str::lower(trim($value)), '_');
        if ($normalized === '') {
            $normalized = 'file';
        }
        if (preg_match('/^[0-9]/', $normalized) === 1) {
            $normalized = 'f_'.$normalized;
        }
        return Str::limit($normalized, 42, '');
    }

    private function normalizeExtension(?string $value): string
    {
        $ext = Str::lower(trim((string) $value));
        $ext = ltrim($ext, '.');
        $ext = preg_replace('/[^a-z0-9]+/', '', $ext) ?: '';

        if ($ext === '') {
            return 'bin';
        }

        return Str::limit($ext, 10, '');
    }
}

