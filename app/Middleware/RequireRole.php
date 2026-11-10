<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;

final class RequireRole implements MiddlewareInterface
{
    public function __construct(private readonly string $role)
    {
    }

    public function handle(Request $request, callable $next): Response
    {
        if (($_SESSION['user_role'] ?? null) !== $this->role) {
            return Response::forbidden();
        }

        return $next($request);
    }
}
