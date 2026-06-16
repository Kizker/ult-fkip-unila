<?php

namespace App\Services\Documents;

class PlaceholderKeyNormalizer
{
    /**
     * Normalize placeholder keys to A-Z0-9_ (uppercase).
     * Returns null if the input is invalid.
     */
    public static function normalize(?string $raw): ?string
    {
        if ($raw === null) return null;

        $v = trim($raw);
        if ($v === '') return null;

        // Remove invisible characters that can leak from DOCX runs
        // and make visually-identical placeholder keys fail to match.
        $cleaned = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}\x{2060}\x{00AD}]/u', '', $v);
        if (is_string($cleaned)) {
            $v = $cleaned;
        }

        if (!preg_match('/^[A-Za-z0-9_]+$/', $v)) {
            return null;
        }

        return strtoupper($v);
    }
}
