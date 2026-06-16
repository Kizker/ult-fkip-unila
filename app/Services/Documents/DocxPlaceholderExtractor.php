<?php

namespace App\Services\Documents;

use Illuminate\Support\Facades\Storage;
use ZipArchive;

class DocxPlaceholderExtractor
{
    /**
     * Extract unique placeholder keys from a .docx stored on a disk.
     * Looks in main document + headers + footers.
     *
     * Placeholders format: {{PLACEHOLDER_KEY}}
     *
     * @return array<int, string> normalized unique keys (A-Z0-9_)
     */
    public function extractFromStoredDocx(string $disk, string $storedPath): array
    {
        $tmpDir = storage_path('app/tmp/docx');
        if (!is_dir($tmpDir)) {
            @mkdir($tmpDir, 0775, true);
        }

        $tmpFile = $tmpDir.'/'.uniqid('tpl_', true).'.docx';
        $stream = Storage::disk($disk)->readStream($storedPath);
        if (!is_resource($stream)) {
            throw new \RuntimeException('Template file not found.');
        }

        $out = fopen($tmpFile, 'wb');
        if ($out === false) {
            if (is_resource($stream)) fclose($stream);
            throw new \RuntimeException('Failed to create temp file.');
        }
        stream_copy_to_stream($stream, $out);
        if (is_resource($stream)) fclose($stream);
        if (is_resource($out)) fclose($out);

        $zip = new ZipArchive();
        $ok = $zip->open($tmpFile);
        if ($ok !== true) {
            @unlink($tmpFile);
            throw new \RuntimeException('Invalid DOCX (zip) file.');
        }

        $keys = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (!is_string($name)) continue;

            if (!preg_match('#^word/(document|header\\d+|footer\\d+)\\.xml$#', $name)) continue;

            $xml = $zip->getFromIndex($i);
            if (!is_string($xml) || $xml === '') continue;

            // Extract from concatenated text nodes (handles runs split by tags loosely)
            $text = preg_replace('/<[^>]+>/', '', $xml) ?? '';
            if ($text === '') continue;

            if (preg_match_all('/\\{\\{\\s*([^\\}\\s]+)\\s*\\}\\}/', $text, $m)) {
                foreach ($m[1] as $raw) {
                    $key = PlaceholderKeyNormalizer::normalize($raw);
                    if ($key) $keys[$key] = true;
                }
            }
        }

        $zip->close();
        @unlink($tmpFile);

        $unique = array_keys($keys);
        sort($unique);
        return $unique;
    }
}

