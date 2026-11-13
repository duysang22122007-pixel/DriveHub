<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Response;
use App\Models\Service;

final class HomeController extends Controller
{
    /**
     * @param array<string, string|int> $databaseConfig
     * @param array<string, string|null> $garage
     */
    public function __construct(
        private readonly array $databaseConfig,
        private readonly array $garage
    ) {
    }

    public function index(): Response
    {
        $services = (new Service(Database::connection($this->databaseConfig)))->listFeatured(6);

        return $this->view('home', [
            'services' => $services,
            'garage' => $this->garage,
            'userRole' => $_SESSION['user_role'] ?? null,
            'userName' => $_SESSION['user_name'] ?? null,
        ]);
    }
}
