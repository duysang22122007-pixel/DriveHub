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
            if (preg_match('#\A/admin/customers/\d+/detail/?\z#', $request->path()) === 1) {
                return Response::json(
                    json_encode(['error' => 'Bạn không có quyền xem thông tin khách hàng.'], JSON_THROW_ON_ERROR),
                    403
                );
            }

            return Response::forbidden();
        }

        return $next($request);
    }
}
