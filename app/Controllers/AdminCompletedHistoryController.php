<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Models\Invoice;
use App\Models\OperationHistory;
use App\Models\Review;
use DateTimeImmutable;

final class AdminCompletedHistoryController extends Controller
{
    private const PAGE_SIZE = 20;

    /**
     * @param array<string, string|int> $databaseConfig
     */
    public function __construct(private readonly array $databaseConfig)
    {
    }

    public function index(Request $request): Response
    {
        $keywordInput = $request->input('q', '');
        $keyword = is_string($keywordInput) ? trim($keywordInput) : '';
        if (mb_strlen($keyword, 'UTF-8') > 100) {
            $keyword = mb_substr($keyword, 0, 100, 'UTF-8');
        }

        $errors = [];
        $fromDate = is_string($request->input('from_date', '')) ? trim((string) $request->input('from_date', '')) : '';
        $toDate = is_string($request->input('to_date', '')) ? trim((string) $request->input('to_date', '')) : '';
        if ($fromDate !== '' && !$this->isValidDate($fromDate)) {
            $errors[] = 'Ngày bắt đầu lọc không hợp lệ.';
            $fromDate = '';
        }
        if ($toDate !== '' && !$this->isValidDate($toDate)) {
            $errors[] = 'Ngày kết thúc lọc không hợp lệ.';
            $toDate = '';
        }

        $paymentStatus = is_string($request->input('payment', '')) ? (string) $request->input('payment', '') : '';
        if (!in_array($paymentStatus, ['', 'paid', 'partial', 'unpaid'], true)) {
            $paymentStatus = '';
        }

        $reviewStatus = is_string($request->input('review', '')) ? (string) $request->input('review', '') : '';
        if (!in_array($reviewStatus, ['', 'reviewed', 'sent', 'not_reviewed'], true)) {
            $reviewStatus = '';
        }

        $pageInput = filter_var($request->input('page', 1), FILTER_VALIDATE_INT);
        $page = $pageInput === false || $pageInput < 1 ? 1 : $pageInput;

        $model = new OperationHistory(Database::connection($this->databaseConfig));
        $summary = $model->summary();
        $pageData = $model->adminPage(
            $keyword,
            $fromDate === '' ? null : $fromDate,
            $toDate === '' ? null : $toDate,
            $paymentStatus,
            $reviewStatus,
            $page,
            self::PAGE_SIZE
        );

        $pageCount = max(1, (int) ceil($pageData['total'] / self::PAGE_SIZE));
        if ($page > $pageCount) {
            $page = $pageCount;
            $pageData = $model->adminPage(
                $keyword,
                $fromDate === '' ? null : $fromDate,
                $toDate === '' ? null : $toDate,
                $paymentStatus,
                $reviewStatus,
                $page,
                self::PAGE_SIZE
            );
        }

        return $this->view('admin/completed-history/index', [
            'items' => $pageData['items'],
            'total' => $pageData['total'],
            'summary' => $summary,
            'page' => $page,
            'pageCount' => $pageCount,
            'q' => $keyword,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'paymentStatus' => $paymentStatus,
            'reviewStatus' => $reviewStatus,
            'invoiceStatusLabels' => Invoice::STATUS_LABELS,
            'errors' => $errors,
            'success' => flash('success'),
            'error' => flash('error'),
        ]);
    }

    /**
     * @param array<string, string> $parameters
     */
    public function toggleReviewVisibility(Request $request, array $parameters): Response
    {
        $reviewId = filter_var($parameters['id'] ?? null, FILTER_VALIDATE_INT);
        if ($reviewId === false || $reviewId < 1) {
            flash('error', 'Mã đánh giá không hợp lệ.');

            return Response::redirect(url('admin/completed-history'));
        }

        $next = (new Review(Database::connection($this->databaseConfig)))->toggleVisibility($reviewId);
        if ($next === null) {
            flash('error', 'Không tìm thấy đánh giá cần cập nhật.');
        } else {
            flash('success', $next === 'visible' ? 'Đã hiển thị đánh giá của khách hàng.' : 'Đã ẩn đánh giá khỏi hiển thị công khai.');
        }

        return Response::redirect(url('admin/completed-history'));
    }

    private function isValidDate(string $date): bool
    {
        if (!preg_match('/\A\d{4}-\d{2}-\d{2}\z/', $date)) {
            return false;
        }

        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
        $errors = DateTimeImmutable::getLastErrors();

        return $parsed !== false
            && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))
            && $parsed->format('Y-m-d') === $date;
    }
}
