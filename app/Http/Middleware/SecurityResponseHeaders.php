<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecurityResponseHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param Closure(Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse) $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if (! config('app.allow_embedding_in_iframe')) {
            $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        }
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'accelerometer=(), autoplay=(self), camera=(), cross-origin-isolated=(), display-capture=(), document-domain=(), encrypted-media=(), fullscreen=(), geolocation=(), gyroscope=(), keyboard-map=(), magnetometer=(), microphone=(), midi=(), payment=(), picture-in-picture=(), publickey-credentials-get=(), screen-wake-lock=(), sync-xhr=(), usb=(), xr-spatial-tracking=()');
        $response->headers->set('Content-Security-Policy', "default-src 'self'; script-src 'self' 'unsafe-inline' *.google.com *.gstatic.com; style-src 'self' 'unsafe-inline' fonts.googleapis.com; font-src fonts.gstatic.com data:; img-src 'self' data:; frame-src *.google.com *.gstatic.com");

        // Set by the webserver, not the response
        if (! headers_sent()) {
            header_remove('Server');
        }

        return $response;
    }
}
