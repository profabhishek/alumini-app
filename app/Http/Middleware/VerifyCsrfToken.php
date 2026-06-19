<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        // No exemptions — all POST routes are CSRF protected.
        // /chat/mark-offline was previously exempt but the JS already sends
        // X-CSRF-TOKEN in headers (and _token in the sendBeacon Blob), so
        // the exemption was unnecessary.
    ];
}
