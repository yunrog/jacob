<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $request->user()) {
            return redirect()->route('login');
        }

        abort_unless(in_array($request->user()->role, $roles, true), 403);

        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        Log::info('Request selesai', ['url' => $request->fullUrl()]);
    }
}
