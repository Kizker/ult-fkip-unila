<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Services\AuditLogger;
use App\Services\HtmlSanitizer;
use Illuminate\Http\Request;

class SiteSettingsController extends Controller
{
    public function __construct(
        private readonly HtmlSanitizer $sanitizer,
        private readonly AuditLogger $audit
    ) {}

    public function edit()
    {
        $settings = SiteSetting::query()->pluck('value','key')->all();

        return view('admin.cms.settings_edit', [
            'settings' => $settings,
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            // About ULT (HTML, sanitized)
            'about_ult_html_id' => ['nullable','string'],
            'about_ult_html_en' => ['nullable','string'],
        ]);

        $pairs = [
            'about_ult_html_id' => $this->sanitizer->clean($data['about_ult_html_id'] ?? ''),
            'about_ult_html_en' => $this->sanitizer->clean($data['about_ult_html_en'] ?? ''),
        ];

        foreach ($pairs as $key => $val) {
            SiteSetting::updateOrCreate(['key' => $key], ['value' => $val]);
        }

        $this->audit->log('site_settings.updated', 'site_settings', 'bulk', array_filter($pairs, fn($v) => $v !== null));

        return redirect()->route('admin.cms.settings.edit')->with('status', __('app.saved'));
    }
}
