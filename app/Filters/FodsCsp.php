<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

// Security headers for every response (Config\Filters globals 'before').
// Sent with PHP's header() at the start of the request, not added to the CI
// response afterwards, so they also reach responses that stop early: the FPDF
// printouts, CSV exports and file downloads end with exit before the 'after'
// filters would run.
// Content-Security-Policy: scripts and styles only from FODS itself and the CDNs
// the templates load. 'unsafe-inline' stays because the pages use inline <script>
// data blocks, the save responses are <script> blocks, and some views use onclick=.
class FodsCsp implements FilterInterface
{
    const CDNS = 'https://code.jquery.com https://cdn.datatables.net https://cdn.jsdelivr.net https://cdnjs.cloudflare.com';

    public function before(RequestInterface $request, $arguments = null)
    {
        if (headers_sent()) {
            return null;
        }

        $policy = [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' " . self::CDNS,
            "style-src 'self' 'unsafe-inline' " . self::CDNS . " https://fonts.googleapis.com",
            "font-src 'self' data: https://fonts.gstatic.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com",
            "img-src 'self' data: blob: " . self::CDNS,
            "connect-src 'self' https://api.iconify.design https://api.simplesvg.com https://api.unisvg.com",
            "frame-src 'self' blob:",
            "worker-src 'self' blob:",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'self'",
        ];

        // the same set CodeIgniter's SecureHeaders filter sends, plus the CSP
        $headers = [
            'X-Frame-Options' => 'SAMEORIGIN',
            'X-Content-Type-Options' => 'nosniff',
            'X-Download-Options' => 'noopen',
            'X-Permitted-Cross-Domain-Policies' => 'none',
            'Referrer-Policy' => 'same-origin',
            'Content-Security-Policy' => implode('; ', $policy),
        ];
        foreach ($headers as $name => $value) {
            header($name . ': ' . $value, true);
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
