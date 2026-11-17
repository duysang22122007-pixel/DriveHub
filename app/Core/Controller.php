<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

abstract class Controller
{
    /**
     * @param array<string, mixed> $data
     */
    protected function view(string $view, array $data = [], int $status = 200): Response
    {
        $viewPath = dirname(__DIR__) . '/Views/' . $view . '.php';
        if (!is_file($viewPath)) {
            throw new RuntimeException('View not found: ' . $view);
        }

        extract($data, EXTR_SKIP);
        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        if ($content === false) {
            throw new RuntimeException('Unable to render view.');
        }

        $clearSubmittedDraft = ($_SESSION['_clear_form_draft'] ?? false) === true;
        unset($_SESSION['_clear_form_draft']);
        $draftConfig = '<script>window.DriveHubFormDraftScope = '
            . json_encode((string) ($_SESSION['user_id'] ?? 'guest'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)
            . ';window.DriveHubClearSubmittedDraft = '
            . ($clearSubmittedDraft ? 'true' : 'false')
            . ';</script><script src="' . e(url('assets/form-drafts.js')) . '"></script>';
        $content = preg_replace('/<head\b[^>]*>/i', '$0' . $draftConfig, $content, 1, $headCount);
        if (!is_string($content) || $headCount !== 1) {
            throw new RuntimeException('Unable to load form draft support for the rendered view.');
        }

        return Response::html($content, $status);
    }
}
