<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;

class TranslateController extends Controller
{
    public function translate(Request $request)
    {
        $data = $request->validate([
            // Accept rich HTML from Tiptap (About ULT) in addition to short text fields.
            'text' => ['required', 'string', 'max:12000'],
            'from' => ['nullable', Rule::in(['id'])],
            'to' => ['nullable', Rule::in(['en'])],
        ]);

        $text = trim((string) ($data['text'] ?? ''));
        if ($text === '') {
            return response()->json(['translated' => '']);
        }

        $from = $data['from'] ?? 'id';
        $to = $data['to'] ?? 'en';

        $cacheKey = 'translate:' . $from . ':' . $to . ':' . md5($text);

        $cached = Cache::get($cacheKey);
        if (is_string($cached) && $cached !== '') {
            return response()->json(['translated' => $cached]);
        }

        $translated = (function () use ($text, $from, $to) {
            $res = Http::timeout(6)
                ->retry(1, 150)
                ->get('https://translate.googleapis.com/translate_a/single', [
                    'client' => 'gtx',
                    'sl' => $from,
                    'tl' => $to,
                    'dt' => 't',
                    'q' => $text,
                ]);

            if (!$res->ok()) {
                return '';
            }

            $json = $res->json();
            if (!is_array($json) || !isset($json[0]) || !is_array($json[0])) {
                return '';
            }

            $out = '';
            foreach ($json[0] as $chunk) {
                if (is_array($chunk) && isset($chunk[0]) && is_string($chunk[0])) {
                    $out .= $chunk[0];
                }
            }

            return trim($out);
        })();

        if (is_string($translated) && $translated !== '') {
            Cache::put($cacheKey, $translated, now()->addDays(7));
        }

        return response()->json(['translated' => $translated]);
    }
}
