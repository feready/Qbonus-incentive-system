<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminMiddleware
{
    /**
     * Memastikan hanya Super Admin yang dapat mengakses halaman.
     */
    public function handle(
        Request $request,
        Closure $next
    ): Response {
        abort_unless(
            $request->user()?->isSuperAdmin(),
            403,
            'Maaf, halaman ini hanya dapat diakses oleh Super Admin.'
        );

        return $next($request);
    }
}
