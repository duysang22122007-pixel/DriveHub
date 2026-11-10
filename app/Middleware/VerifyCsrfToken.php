<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;

final class VerifyCsrfToken implements MiddlewareInterface
{
    public function handle(Request $request, callable $next): Response
    {
        $body = $request->body();
        if ($request->method() !== 'POST' || !csrf_token_is_valid($body['_token'] ?? null)) {
            return Response::csrfRejected();
        }

        return $next($request);
    }
}
