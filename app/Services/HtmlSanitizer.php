<?php

namespace App\Services;

use HTMLPurifier;
use HTMLPurifier_Config;
use Illuminate\Support\Facades\File;

class HtmlSanitizer
{
    private HTMLPurifier $purifier;

    public function __construct()
    {
        $config = HTMLPurifier_Config::createDefault();
        // HTMLPurifier (bundled) only supports specific doctypes. We keep HTML4 Transitional and
        // extend the definition to allow modern embed attributes safely.
        $config->set('HTML.Doctype', 'HTML 4.01 Transitional');
        $config->set('HTML.DefinitionID', 'ult_custom_html4');
        $config->set('HTML.DefinitionRev', 2);
        $cacheDir = storage_path('app/htmlpurifier');
        File::ensureDirectoryExists($cacheDir);
        $config->set('Cache.SerializerPath', $cacheDir);

        // Allowlist tags/attrs - anti XSS
        $config->set('HTML.Allowed', implode(',', [
            'p,b,strong,i,em,u,s,br,hr,blockquote,code,pre',
            'h1,h2,h3,h4,h5,h6',
            'ul,ol,li',
            'a[href|title|target|rel]',
            'img[src|alt|title|class|width|height]',
            'iframe[src|class|width|height|allow|allowfullscreen|frameborder]',
            'span[class]',
            'div[class|data-youtube-video]',
        ]));

        $config->set('HTML.SafeIframe', true);
        $config->set('URI.SafeIframeRegexp', '%^https?://(www\\.)?(youtube\\.com/embed/|www\\.youtube-nocookie\\.com/embed/)%');

        $config->set('Attr.AllowedFrameTargets', ['_blank']);
        $config->set('AutoFormat.RemoveEmpty', true);

        // Extend iframe attributes for modern embeds (YouTube).
        if ($def = $config->maybeGetRawHTMLDefinition()) {
            $def->addAttribute('iframe', 'allow', 'Text');
            $def->addAttribute('iframe', 'allowfullscreen', 'Bool');
            $def->addAttribute('iframe', 'frameborder', 'Text');
            $def->addAttribute('iframe', 'class', 'Text');
            $def->addAttribute('div', 'data-youtube-video', 'Text');
        }

        $this->purifier = new HTMLPurifier($config);
    }

    public function clean(?string $html): string
    {
        return $this->purifier->purify($html ?? '');
    }
}
