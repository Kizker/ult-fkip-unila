<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        $headers = config('security.headers');
        $csp = (string) ($headers['content_security_policy'] ?? '');
        $xFrameOptions = (string) ($headers['x_frame_options'] ?? 'DENY');

        // Allow same-origin iframe for in-app PDF preview panels.
        if ($request->routeIs('staff.assemble.output_inline') || $request->routeIs('services.document_preview')) {
            $xFrameOptions = 'SAMEORIGIN';
            $csp = $this->replaceFrameAncestorsDirective($csp, "'self'");
        }

        $response->headers->set('X-Content-Type-Options', $headers['x_content_type_options']);
        $response->headers->set('Referrer-Policy', $headers['referrer_policy']);
        $response->headers->set('X-Frame-Options', $xFrameOptions);
        $response->headers->set('Permissions-Policy', $headers['permissions_policy']);
        $response->headers->set('Content-Security-Policy', $csp);

        $hsts = $headers['hsts'] ?? ['enabled' => false];
        if (!empty($hsts['enabled']) && $request->isSecure()) {
            $value = 'max-age='.$hsts['max_age'];
            if (!empty($hsts['include_subdomains'])) $value .= '; includeSubDomains';
            if (!empty($hsts['preload'])) $value .= '; preload';
            $response->headers->set('Strict-Transport-Security', $value);
        }

        // Keep HTML pages revalidated so post-deploy/data updates are visible immediately.
        if ($request->isMethod('GET') && $this->isHtmlResponse($response)) {
            $response->headers->set('Cache-Control', 'max-age=0, must-revalidate, no-cache, private');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
        }

        // Avoid stale CSRF pages from browser/proxy cache on auth-sensitive routes.
        if ($request->is('login', 'register', 'forgot-password', 'reset-password/*', 'email/verify*')) {
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
        }

        return $response;
    }

    private function replaceFrameAncestorsDirective(string $csp, string $value): string
    {
        $parts = array_values(array_filter(array_map('trim', explode(';', $csp)), fn ($v) => $v !== ''));
        if (empty($parts)) {
            return "frame-ancestors {$value}";
        }

        $out = [];
        $replaced = false;
        foreach ($parts as $part) {
            if (stripos($part, 'frame-ancestors') === 0) {
                $out[] = "frame-ancestors {$value}";
                $replaced = true;
                continue;
            }
            $out[] = $part;
        }

        if (!$replaced) {
            $out[] = "frame-ancestors {$value}";
        }

        return implode('; ', $out);
    }

    private function isHtmlResponse(Response $response): bool
    {
        $contentType = strtolower((string) $response->headers->get('Content-Type', ''));
        return str_contains($contentType, 'text/html');
    }
}
