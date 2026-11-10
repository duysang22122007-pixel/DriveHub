<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Models\User;

final class GuestOnly implements MiddlewareInterface
{
    /**
     * @param array<string, string|int> $databaseConfig
     */
    public function __construct(private readonly array $databaseConfig)
    {
    }

    public function handle(Request $request, callable $next): Response
    {
        $userId = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);
        if ($userId === false || $userId === null || $userId < 1) {
            return $next($request);
        }

        $user = (new User(Database::connection($this->databaseConfig)))->findPublicById($userId);
        if ($user === null || $user['status'] !== 'active') {
            unset($_SESSION['user_id'], $_SESSION['user_role'], $_SESSION['user_name']);

            return $next($request);
        }

        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_name'] = $user['full_name'];

        return Response::redirect(url('dashboard'));
    }
}
