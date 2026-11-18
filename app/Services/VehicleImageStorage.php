<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Request;
use PDO;
use RuntimeException;

final class VehicleImageStorage
{
    private const MAX_SIZE = 3145728;
    private const UPLOAD_PREFIX = 'assets/uploads/vehicles/';

    /**
     * Store a validated vehicle image and return its public-relative path.
     */
    public function store(Request $request): ?string
    {
        $file = $request->file('image');
        if ($file === null) {
            return null;
        }
        if (!is_array($file)) {
            throw new \InvalidArgumentException('Tệp hình ảnh không hợp lệ.');
        }

        $error = $file['error'] ?? UPLOAD_ERR_NO_FILE;
        if ($error === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if ($error !== UPLOAD_ERR_OK) {
            $message = in_array($error, [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)
                ? 'Hình ảnh phải có dung lượng tối đa 3 MB.'
                : 'Không thể nhận tệp hình ảnh đã tải lên.';
            throw new \InvalidArgumentException($message);
        }

        $temporaryPath = $file['tmp_name'] ?? null;
        $filename = $file['name'] ?? null;
        $reportedSize = $file['size'] ?? null;
        if (
            !is_string($temporaryPath)
            || !is_uploaded_file($temporaryPath)
            || !is_string($filename)
            || !is_int($reportedSize)
            || $reportedSize < 1
            || $reportedSize > self::MAX_SIZE
        ) {
            throw new \InvalidArgumentException('Hình ảnh phải có dung lượng tối đa 3 MB.');
        }

        $actualSize = filesize($temporaryPath);
        if ($actualSize === false || $actualSize !== $reportedSize || $actualSize < 1 || $actualSize > self::MAX_SIZE) {
            throw new \InvalidArgumentException('Dung lượng hình ảnh không hợp lệ hoặc vượt quá 3 MB.');
        }

        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $allowedTypes = [
            'jpg' => ['mime' => 'image/jpeg', 'stored_extension' => 'jpg'],
            'jpeg' => ['mime' => 'image/jpeg', 'stored_extension' => 'jpg'],
            'png' => ['mime' => 'image/png', 'stored_extension' => 'png'],
            'webp' => ['mime' => 'image/webp', 'stored_extension' => 'webp'],
        ];
        if (!isset($allowedTypes[$extension])) {
            throw new \InvalidArgumentException('Chỉ chấp nhận ảnh JPG, JPEG, PNG hoặc WEBP.');
        }

        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($temporaryPath);
        $image = getimagesize($temporaryPath);
        if (
            !is_string($mime)
            || $mime !== $allowedTypes[$extension]['mime']
            || $image === false
            || ($image['mime'] ?? null) !== $mime
            || $image[0] * $image[1] > 20000000
        ) {
            throw new \InvalidArgumentException('Tệp tải lên không phải ảnh JPG, JPEG, PNG hoặc WEBP hợp lệ.');
        }

        $directory = dirname(__DIR__, 2) . '/public/' . self::UPLOAD_PREFIX;
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new RuntimeException('Không thể tạo thư mục lưu ảnh phương tiện.');
        }

        do {
            $storedName = bin2hex(random_bytes(16)) . '.' . $allowedTypes[$extension]['stored_extension'];
            $destination = $directory . $storedName;
        } while (file_exists($destination));

        if (!move_uploaded_file($temporaryPath, $destination)) {
            throw new RuntimeException('Không thể lưu ảnh phương tiện đã tải lên.');
        }

        return self::UPLOAD_PREFIX . $storedName;
    }

    public function publicUrl(?string $path): ?string
    {
        if (!is_string($path) || $path === '' || str_contains($path, '..') || str_contains($path, '\\')) {
            return null;
        }

        if (str_starts_with($path, self::UPLOAD_PREFIX)) {
            $name = substr($path, strlen(self::UPLOAD_PREFIX));
            if (preg_match('/\A[a-f0-9]{32}\.(?:jpg|png|webp)\z/', $name) !== 1) {
                return null;
            }
        } elseif (!str_starts_with($path, 'assets/images/')) {
            return null;
        }

        $publicRoot = dirname(__DIR__, 2) . '/public/';
        if (!is_file($publicRoot . $path)) {
            return null;
        }

        return url($path);
    }

    /**
     * Delete a managed upload only after no vehicle references it.
     */
    public function deleteIfUnused(PDO $pdo, ?string $path): bool
    {
        $absolutePath = $this->managedAbsolutePath($path);
        if ($absolutePath === null) {
            return true;
        }

        $statement = $pdo->prepare('SELECT COUNT(*) FROM vehicles WHERE image_path = :path');
        $statement->execute(['path' => $path]);
        if ((int) $statement->fetchColumn() > 0) {
            return true;
        }

        if (!file_exists($absolutePath)) {
            return true;
        }

        return unlink($absolutePath);
    }

    private function managedAbsolutePath(?string $path): ?string
    {
        if (
            !is_string($path)
            || preg_match(
                '#\A' . preg_quote(self::UPLOAD_PREFIX, '#') . '[a-f0-9]{32}\.(?:jpg|png|webp)\z#',
                $path
            ) !== 1
        ) {
            return null;
        }

        $directory = dirname(__DIR__, 2) . '/public/' . self::UPLOAD_PREFIX;
        $realDirectory = realpath($directory);
        $absolutePath = $directory . substr($path, strlen(self::UPLOAD_PREFIX));
        $realPath = realpath($absolutePath);
        if (
            $realDirectory === false
            || $realPath === false
            || !is_file($realPath)
            || is_link($absolutePath)
            || !str_starts_with($realPath, $realDirectory . DIRECTORY_SEPARATOR)
        ) {
            return null;
        }

        return $realPath;
    }
}
