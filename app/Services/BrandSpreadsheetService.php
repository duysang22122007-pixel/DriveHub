<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Validator;
use InvalidArgumentException;
use PDO;
use RuntimeException;
use SimpleXMLElement;
use Throwable;
use ZipArchive;

final class BrandSpreadsheetService
{
    private const MAX_FILE_BYTES = 5 * 1024 * 1024;
    private const MAX_ROWS = 2000;

    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * Đọc file Excel (.xlsx) hoặc CSV tải lên và tự động thêm mới / cập nhật vào bảng vehicle_brands.
     *
     * @param array<string, mixed> $file
     * @return array{created: int, updated: int, unchanged: int, skipped: int, errors: list<string>}
     */
    public function importUploadedFile(array $file): array
    {
        $error = $file['error'] ?? UPLOAD_ERR_NO_FILE;
        if ($error === UPLOAD_ERR_NO_FILE) {
            throw new InvalidArgumentException('Vui lòng chọn tệp Excel (.xlsx) hoặc CSV (.csv) để tải lên.');
        }
        if (
            $error !== UPLOAD_ERR_OK
            || !is_string($file['tmp_name'] ?? null)
            || !is_uploaded_file($file['tmp_name'])
            || !is_int($file['size'] ?? null)
            || $file['size'] < 1
            || $file['size'] > self::MAX_FILE_BYTES
        ) {
            throw new InvalidArgumentException('Tệp tải lên không hợp lệ hoặc vượt quá dung lượng 5 MB.');
        }

        $originalName = is_string($file['name'] ?? null) ? $file['name'] : '';
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if ($extension === 'xlsx') {
            $rawRows = $this->parseXlsx($file['tmp_name']);
        } elseif ($extension === 'csv' || $extension === 'txt') {
            $rawRows = $this->parseCsv($file['tmp_name']);
        } elseif ($extension === 'xls') {
            throw new InvalidArgumentException('Định dạng .xls cũ chưa được hỗ trợ trực tiếp. Vui lòng lưu lại dưới dạng .xlsx hoặc .csv trong Excel rồi tải lên.');
        } else {
            throw new InvalidArgumentException('Chỉ hỗ trợ định dạng bảng Excel (.xlsx) hoặc CSV (.csv).');
        }

        if ($rawRows === []) {
            throw new InvalidArgumentException('Bảng Excel không có dữ liệu.');
        }

        $parsedRows = $this->normalizeTableRows($rawRows);
        if ($parsedRows === []) {
            throw new InvalidArgumentException('Không tìm thấy dòng hãng xe hợp lệ trong bảng tải lên.');
        }

        return $this->upsertBrands($parsedRows);
    }

    /**
     * Tạo tệp .xlsx mẫu hoặc xuất toàn bộ danh sách hãng xe hiện tại ra .xlsx.
     *
     * @param list<array{name: string, status: int|string, logo: ?string}> $brands
     */
    public function buildXlsxBinary(array $brands): string
    {
        if (!class_exists(ZipArchive::class)) {
            throw new RuntimeException('Máy chủ chưa bật PHP extension ZipArchive.');
        }

        $rows = [
            ['Tên hãng xe', 'Trạng thái (1 = Hoạt động, 0 = Ngừng)', 'Logo (tùy chọn)'],
        ];
        if ($brands === []) {
            $rows[] = ['Toyota', '1', ''];
            $rows[] = ['Hyundai', '1', ''];
            $rows[] = ['Kia', '1', ''];
            $rows[] = ['VinFast', '1', ''];
        } else {
            foreach ($brands as $brand) {
                $rows[] = [
                    (string) $brand['name'],
                    (int) $brand['status'] === 1 ? '1' : '0',
                    (string) ($brand['logo'] ?? ''),
                ];
            }
        }

        $sheetRowsXml = '';
        foreach ($rows as $rowIndex => $cols) {
            $rNum = $rowIndex + 1;
            $cellsXml = '';
            foreach ($cols as $colIndex => $value) {
                $colRef = $this->columnLetter($colIndex) . $rNum;
                $escaped = htmlspecialchars((string) $value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
                $cellsXml .= '<c r="' . $colRef . '" t="inlineStr"><is><t>' . $escaped . '</t></is></c>';
            }
            $sheetRowsXml .= '<row r="' . $rNum . '">' . $cellsXml . '</row>';
        }

        $sheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<sheetData>' . $sheetRowsXml . '</sheetData>'
            . '</worksheet>';

        $contentTypesXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '</Types>';

        $relsXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>';

        $workbookXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" '
            . 'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets><sheet name="HangXe" sheetId="1" r:id="rId1"/></sheets>'
            . '</workbook>';

        $workbookRelsXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '</Relationships>';

        $tmpFile = tempnam(sys_get_temp_dir(), 'dh_brand_xlsx_');
        if ($tmpFile === false) {
            throw new RuntimeException('Không thể tạo tệp tạm để xuất Excel.');
        }

        try {
            $zip = new ZipArchive();
            if ($zip->open($tmpFile, ZipArchive::OVERWRITE | ZipArchive::CREATE) !== true) {
                throw new RuntimeException('Không thể khởi tạo gói Excel (.xlsx).');
            }
            $zip->addFromString('[Content_Types].xml', $contentTypesXml);
            $zip->addFromString('_rels/.rels', $relsXml);
            $zip->addFromString('xl/workbook.xml', $workbookXml);
            $zip->addFromString('xl/_rels/workbook.xml.rels', $workbookRelsXml);
            $zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);
            $zip->close();

            $binary = file_get_contents($tmpFile);
            if ($binary === false) {
                throw new RuntimeException('Không thể đọc dữ liệu Excel vừa tạo.');
            }

            return $binary;
        } finally {
            @unlink($tmpFile);
        }
    }

    /**
     * @return list<list<string>>
     */
    private function parseXlsx(string $path): array
    {
        if (!class_exists(ZipArchive::class)) {
            throw new InvalidArgumentException('Máy chủ chưa bật ZipArchive để đọc file .xlsx. Hãy thử định dạng .csv.');
        }

        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            throw new InvalidArgumentException('Không thể mở tệp .xlsx. Vui lòng kiểm tra tệp có bị hỏng hoặc đặt mật khẩu hay không.');
        }

        try {
            $sharedStrings = [];
            $sharedXml = $zip->getFromName('xl/sharedStrings.xml');
            if (is_string($sharedXml) && $sharedXml !== '') {
                $ss = @simplexml_load_string($sharedXml, SimpleXMLElement::class, LIBXML_NONET | LIBXML_NOERROR);
                if ($ss instanceof SimpleXMLElement) {
                    foreach ($ss->si as $si) {
                        if (isset($si->t)) {
                            $sharedStrings[] = (string) $si->t;
                        } else {
                            $text = '';
                            foreach ($si->r as $run) {
                                $text .= (string) ($run->t ?? '');
                            }
                            $sharedStrings[] = $text;
                        }
                    }
                }
            }

            $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
            if (!is_string($sheetXml) || $sheetXml === '') {
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $name = $zip->getNameIndex($i);
                    if (is_string($name) && preg_match('#\Axl/worksheets/sheet\d+\.xml\z#i', $name)) {
                        $sheetXml = $zip->getFromIndex($i);
                        break;
                    }
                }
            }

            if (!is_string($sheetXml) || $sheetXml === '') {
                throw new InvalidArgumentException('Không tìm thấy trang tính hợp lệ trong tệp .xlsx.');
            }

            $xml = @simplexml_load_string($sheetXml, SimpleXMLElement::class, LIBXML_NONET | LIBXML_NOERROR);
            if (!$xml instanceof SimpleXMLElement || !isset($xml->sheetData->row)) {
                throw new InvalidArgumentException('Cấu trúc dữ liệu bên trong tệp .xlsx không hợp lệ.');
            }

            $rows = [];
            foreach ($xml->sheetData->row as $rowNode) {
                $rowCells = [];
                $maxCol = -1;
                foreach ($rowNode->c as $cell) {
                    $ref = (string) ($cell['r'] ?? '');
                    $colIdx = $ref !== '' ? $this->columnIndexFromRef($ref) : ($maxCol + 1);
                    $maxCol = max($maxCol, $colIdx);
                    $type = (string) ($cell['t'] ?? '');

                    if ($type === 's') {
                        $idx = (int) ((string) ($cell->v ?? 0));
                        $val = $sharedStrings[$idx] ?? '';
                    } elseif ($type === 'inlineStr') {
                        if (isset($cell->is->t)) {
                            $val = (string) $cell->is->t;
                        } else {
                            $val = '';
                            foreach ($cell->is->r ?? [] as $run) {
                                $val .= (string) ($run->t ?? '');
                            }
                        }
                    } else {
                        $val = (string) ($cell->v ?? '');
                    }

                    $rowCells[$colIdx] = trim($val);
                }

                if ($maxCol >= 0) {
                    $normalized = [];
                    for ($c = 0; $c <= $maxCol; $c++) {
                        $normalized[] = $rowCells[$c] ?? '';
                    }
                    $rows[] = $normalized;
                }

                if (count($rows) >= self::MAX_ROWS) {
                    break;
                }
            }

            return $rows;
        } finally {
            $zip->close();
        }
    }

    /**
     * @return list<list<string>>
     */
    private function parseCsv(string $path): array
    {
        $content = file_get_contents($path);
        if (!is_string($content) || trim($content) === '') {
            return [];
        }

        if (str_starts_with($content, "\xEF\xBB\xBF")) {
            $content = substr($content, 3);
        } elseif (!mb_check_encoding($content, 'UTF-8')) {
            $converted = @iconv('Windows-1258', 'UTF-8//IGNORE', $content);
            if (is_string($converted) && $converted !== '') {
                $content = $converted;
            }
        }

        $firstLine = strtok($content, "\r\n") ?: '';
        $delimiter = substr_count($firstLine, ';') > substr_count($firstLine, ',') ? ';' : ',';

        $rows = [];
        $handle = fopen('php://temp', 'r+');
        if ($handle === false) {
            return [];
        }
        fwrite($handle, $content);
        rewind($handle);

        while (($cols = fgetcsv($handle, 0, $delimiter)) !== false) {
            if (!is_array($cols)) {
                continue;
            }
            $rows[] = array_map(static fn (mixed $v): string => trim((string) $v), $cols);
            if (count($rows) >= self::MAX_ROWS) {
                break;
            }
        }
        fclose($handle);

        return $rows;
    }

    /**
     * @param list<list<string>> $rawRows
     * @return list<array{name: string, status: ?int, logo: ?string, row_number: int}>
     */
    private function normalizeTableRows(array $rawRows): array
    {
        $firstNonEmptyIndex = null;
        foreach ($rawRows as $idx => $row) {
            if (implode('', $row) !== '') {
                $firstNonEmptyIndex = $idx;
                break;
            }
        }
        if ($firstNonEmptyIndex === null) {
            return [];
        }

        $headerRow = $rawRows[$firstNonEmptyIndex];
        $nameCol = null;
        $statusCol = null;
        $logoCol = null;

        foreach ($headerRow as $colIdx => $cell) {
            $key = $this->normalizeHeader($cell);
            if ($nameCol === null && in_array($key, ['ten hang xe', 'ten hang', 'hang xe', 'name', 'brand', 'brand_name', 'make', 'make_name'], true)) {
                $nameCol = $colIdx;
            } elseif ($statusCol === null && (str_contains($key, 'trang thai') || $key === 'status' || $key === 'active')) {
                $statusCol = $colIdx;
            } elseif ($logoCol === null && ($key === 'logo' || str_contains($key, 'logo') || $key === 'hinh anh')) {
                $logoCol = $colIdx;
            }
        }

        $hasHeader = ($nameCol !== null);
        if (!$hasHeader) {
            $nameCol = 0;
            $statusCol = count($headerRow) >= 2 ? 1 : null;
            $logoCol = count($headerRow) >= 3 ? 2 : null;
            $startIdx = $firstNonEmptyIndex;
        } else {
            $startIdx = $firstNonEmptyIndex + 1;
        }

        $items = [];
        $rowCount = count($rawRows);
        for ($i = $startIdx; $i < $rowCount; $i++) {
            $row = $rawRows[$i];
            if (implode('', $row) === '') {
                continue;
            }

            $name = trim(preg_replace('/\s+/u', ' ', (string) ($row[$nameCol] ?? '')) ?? '');
            $statusRaw = $statusCol !== null ? trim((string) ($row[$statusCol] ?? '')) : '';
            $logoRaw = $logoCol !== null ? trim((string) ($row[$logoCol] ?? '')) : '';

            $items[] = [
                'name' => $name,
                'status' => $this->parseStatusValue($statusRaw),
                'logo' => $logoRaw !== '' ? $logoRaw : null,
                'row_number' => $i + 1,
            ];
        }

        return $items;
    }

    /**
     * @param list<array{name: string, status: ?int, logo: ?string, row_number: int}> $rows
     * @return array{created: int, updated: int, unchanged: int, skipped: int, errors: list<string>}
     */
    private function upsertBrands(array $rows): array
    {
        $created = 0;
        $updated = 0;
        $unchanged = 0;
        $skipped = 0;
        $errors = [];

        $this->pdo->beginTransaction();
        try {
            $existingRows = $this->pdo->query(
                'SELECT id, name, logo, status FROM vehicle_brands'
            )->fetchAll(PDO::FETCH_ASSOC);

            $byLowerName = [];
            foreach ($existingRows as $existing) {
                $key = mb_strtolower(trim((string) $existing['name']), 'UTF-8');
                $byLowerName[$key] = $existing;
            }

            $insertStmt = $this->pdo->prepare(
                'INSERT INTO vehicle_brands (name, logo, status) VALUES (:name, :logo, :status)'
            );
            $updateStmt = $this->pdo->prepare(
                'UPDATE vehicle_brands SET name = :name, logo = :logo, status = :status WHERE id = :id'
            );

            foreach ($rows as $row) {
                $name = $row['name'];
                if ($name === '' || !Validator::stringLength($name, 1, 100)) {
                    $skipped++;
                    $errors[] = 'Dòng ' . $row['row_number'] . ': Tên hãng xe trống hoặc vượt quá 100 ký tự.';
                    continue;
                }

                $key = mb_strtolower($name, 'UTF-8');
                if (isset($byLowerName[$key])) {
                    $existing = $byLowerName[$key];
                    $newStatus = $row['status'] ?? (int) $existing['status'];
                    $newLogo = $row['logo'] ?? $existing['logo'];

                    $isSame = ((string) $existing['name'] === $name)
                        && ((int) $existing['status'] === $newStatus)
                        && ((string) ($existing['logo'] ?? '') === (string) ($newLogo ?? ''));

                    if ($isSame) {
                        $unchanged++;
                        continue;
                    }

                    $updateStmt->execute([
                        'name' => $name,
                        'logo' => $newLogo,
                        'status' => $newStatus,
                        'id' => (int) $existing['id'],
                    ]);
                    $byLowerName[$key] = [
                        'id' => (int) $existing['id'],
                        'name' => $name,
                        'logo' => $newLogo,
                        'status' => $newStatus,
                    ];
                    $updated++;
                } else {
                    $newStatus = $row['status'] ?? 1;
                    $insertStmt->execute([
                        'name' => $name,
                        'logo' => $row['logo'],
                        'status' => $newStatus,
                    ]);
                    $newId = (int) $this->pdo->lastInsertId();
                    $byLowerName[$key] = [
                        'id' => $newId,
                        'name' => $name,
                        'logo' => $row['logo'],
                        'status' => $newStatus,
                    ];
                    $created++;
                }
            }

            $this->pdo->commit();

            return [
                'created' => $created,
                'updated' => $updated,
                'unchanged' => $unchanged,
                'skipped' => $skipped,
                'errors' => array_slice($errors, 0, 10),
            ];
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    private function parseStatusValue(string $raw): ?int
    {
        if ($raw === '') {
            return null;
        }
        $normalized = $this->normalizeHeader($raw);
        if (in_array($normalized, ['1', 'active', 'hoat dong', 'dang hoat dong', 'co', 'yes', 'true', 'on', 'mo'], true)) {
            return 1;
        }
        if (in_array($normalized, ['0', 'inactive', 'ngung', 'ngung hoat dong', 'khong', 'khoa', 'tat', 'no', 'false', 'off'], true)) {
            return 0;
        }

        return null;
    }

    private function normalizeHeader(string $value): string
    {
        $lower = mb_strtolower(trim($value), 'UTF-8');
        $map = [
            'à' => 'a', 'á' => 'a', 'ả' => 'a', 'ã' => 'a', 'ạ' => 'a',
            'ă' => 'a', 'ằ' => 'a', 'ắ' => 'a', 'ẳ' => 'a', 'ẵ' => 'a', 'ặ' => 'a',
            'â' => 'a', 'ầ' => 'a', 'ấ' => 'a', 'ẩ' => 'a', 'ẫ' => 'a', 'ậ' => 'a',
            'đ' => 'd',
            'è' => 'e', 'é' => 'e', 'ẻ' => 'e', 'ẽ' => 'e', 'ẹ' => 'e',
            'ê' => 'e', 'ề' => 'e', 'ế' => 'e', 'ể' => 'e', 'ễ' => 'e', 'ệ' => 'e',
            'ì' => 'i', 'í' => 'i', 'ỉ' => 'i', 'ĩ' => 'i', 'ị' => 'i',
            'ò' => 'o', 'ó' => 'o', 'ỏ' => 'o', 'õ' => 'o', 'ọ' => 'o',
            'ô' => 'o', 'ồ' => 'o', 'ố' => 'o', 'ổ' => 'o', 'ỗ' => 'o', 'ộ' => 'o',
            'ơ' => 'o', 'ờ' => 'o', 'ớ' => 'o', 'ở' => 'o', 'ỡ' => 'o', 'ợ' => 'o',
            'ù' => 'u', 'ú' => 'u', 'ủ' => 'u', 'ũ' => 'u', 'ụ' => 'u',
            'ư' => 'u', 'ừ' => 'u', 'ứ' => 'u', 'ử' => 'u', 'ữ' => 'u', 'ự' => 'u',
            'ỳ' => 'y', 'ý' => 'y', 'ỷ' => 'y', 'ỹ' => 'y', 'ỵ' => 'y',
        ];
        $ascii = strtr($lower, $map);
        $ascii = preg_replace('/\([^)]*\)/', ' ', $ascii) ?? $ascii;
        $ascii = preg_replace('/[^a-z0-9_]+/', ' ', $ascii) ?? $ascii;

        return trim($ascii);
    }

    private function columnIndexFromRef(string $ref): int
    {
        if (!preg_match('/\A([A-Z]+)/i', $ref, $matches)) {
            return 0;
        }
        $letters = strtoupper($matches[1]);
        $index = 0;
        $len = strlen($letters);
        for ($i = 0; $i < $len; $i++) {
            $index = $index * 26 + (ord($letters[$i]) - ord('A') + 1);
        }

        return max(0, $index - 1);
    }

    private function columnLetter(int $index): string
    {
        $letter = '';
        $n = $index + 1;
        while ($n > 0) {
            $rem = ($n - 1) % 26;
            $letter = chr(65 + $rem) . $letter;
            $n = intdiv($n - 1, 26);
        }

        return $letter;
    }
}
