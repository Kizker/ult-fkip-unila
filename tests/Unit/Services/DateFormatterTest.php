<?php

namespace Tests\Unit\Services;

use App\Services\Documents\DateFormatter;
use PHPUnit\Framework\TestCase;

class DateFormatterTest extends TestCase
{
    public function test_format_date_to_doc_id(): void
    {
        $this->assertSame('12 Januari 2026', DateFormatter::formatDateToDoc('2026-01-12', 'id'));
    }
}

