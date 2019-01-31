<?php

namespace App\Http\Middleware;

use Closure;
use Request; 
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;

class VerifyCsrfToken extends BaseVerifier
{
    /**
     * Indicates whether the XSRF-TOKEN cookie should be set on the response.
     *
     * @var bool
     */
    protected $addHttpCookie = true;

    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */ 
    protected $except = [
        //
    ];

    public function handle($request, Closure $next)  
    {
        return $next($request);
    }
}
