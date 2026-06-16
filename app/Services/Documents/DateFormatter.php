<?php

namespace App\Services\Documents;

use Carbon\CarbonImmutable;

class DateFormatter
{
    /**
     * Input: YYYY-MM-DD
     * Output: "DD MMMM YYYY" (Bahasa Indonesia), contoh: 12 Januari 2026
     */
    public static function formatDateToDoc(string $dateYYYYMMDD, string $locale = 'id'): string
    {
        $d = CarbonImmutable::createFromFormat('Y-m-d', $dateYYYYMMDD);
        if (!$d) {
            throw new \InvalidArgumentException('Invalid date format, expected YYYY-MM-DD.');
        }

        return $d->locale($locale)->translatedFormat('d F Y');
    }
}

