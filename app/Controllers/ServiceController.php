<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Models\Service;

final class ServiceController extends Controller
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

    public function index(Request $request): Response
    {
        $keyword = $request->input('q');
        $keyword = is_string($keyword) ? trim($keyword) : '';
        $categoryId = filter_var($request->input('category'), FILTER_VALIDATE_INT);
        if ($categoryId === false || $categoryId < 1) {
            $categoryId = null;
        }

        $model = new Service(Database::connection($this->databaseConfig));
        $categories = $model->listActiveCategories();
        $activeCategory = null;

        if ($categoryId !== null) {
            foreach ($categories as $category) {
                if ((int) $category['id'] === $categoryId) {
                    $activeCategory = $category;
                    break;
                }
            }

            if ($activeCategory === null) {
                $categoryId = null;
            }
        }

        return $this->view('services/index', [
            'categories' => $categories,
            'services' => $model->searchActive($keyword, $categoryId),
            'keyword' => $keyword,
            'categoryId' => $categoryId,
            'activeCategory' => $activeCategory,
            'garage' => $this->garage,
            'userRole' => $_SESSION['user_role'] ?? null,
            'userName' => $_SESSION['user_name'] ?? null,
        ]);
    }

    /**
     * @param array<string, string> $parameters
     */
    public function show(Request $request, array $parameters): Response
    {
        $serviceId = filter_var($parameters['id'] ?? null, FILTER_VALIDATE_INT);
        if ($serviceId === false || $serviceId < 1) {
            return Response::notFound();
        }

        $model = new Service(Database::connection($this->databaseConfig));
        $service = $model->findActiveById($serviceId);
        if ($service === null) {
            return Response::notFound();
        }

        $contentMap = require dirname(__DIR__, 2) . '/config/service_content.php';
        $content = is_array($contentMap[$service['name']] ?? null) ? $contentMap[$service['name']] : [];

        return $this->view('services/show', [
            'service' => $service,
            'relatedServices' => $model->relatedActive((int) $service['category_id'], $serviceId, 3),
            'content' => $content,
            'garage' => $this->garage,
            'userRole' => $_SESSION['user_role'] ?? null,
            'userName' => $_SESSION['user_name'] ?? null,
        ]);
    }
}
