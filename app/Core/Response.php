<?php

declare(strict_types=1);

namespace App\Core;

final class Response
{
    private string $content;
    private int $status;
    private array $headers;

    /**
     * @param array<string, string> $headers
     */
    public function __construct(
        string $content = '',
        int $status = 200,
        array $headers = ['Content-Type' => 'text/html; charset=UTF-8']
    ) {
        $this->content = $content;
        $this->status = $status;
        $this->headers = $headers;
    }

    public static function html(string $content, int $status = 200): self
    {
        return new self($content, $status);
    }

    public static function json(string $content, int $status = 200): self
    {
        return new self($content, $status, ['Content-Type' => 'application/json; charset=UTF-8']);
    }

    public static function notFound(): self
    {
        return self::html('<h1>404 - Not Found</h1>', 404);
    }

    public static function forbidden(): self
    {
        return self::html('<h1>403 - Forbidden</h1>', 403);
    }

    public static function csrfRejected(): self
    {
        return self::html('<h1>419 - Page Expired</h1><p>Vui lòng tải lại trang và thử lại.</p>', 419);
    }

    public static function redirect(string $location, int $status = 302): self
    {
        return new self('', $status, ['Location' => $location]);
    }

    /** @param list<string> $allowedMethods */
    public static function methodNotAllowed(array $allowedMethods): self
    {
        return new self(
            '<h1>405 - Method Not Allowed</h1>',
            405,
            [
                'Content-Type' => 'text/html; charset=UTF-8',
                'Allow' => implode(', ', $allowedMethods),
            ]
        );
    }

    public function send(): void
    {
        http_response_code($this->status);
        foreach ($this->headers as $name => $value) {
            header($name . ': ' . $value);
        }

        echo $this->content;
    }
}
