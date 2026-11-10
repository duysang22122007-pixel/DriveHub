<?php

declare(strict_types=1);

namespace App\Core;

final class Request
{
    private string $method;
    private string $path;

    /**
     * @param array<string, mixed> $query
     * @param array<string, mixed> $body
     * @param array<string, string> $server
     */
    public function __construct(
        string $method,
        string $path,
        private array $query = [],
        private array $body = [],
        private array $server = []
    ) {
        $this->method = $method;
        $this->path = $path;
    }

    public static function fromGlobals(): self
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $uriPath = parse_url($uri, PHP_URL_PATH);
        $path = is_string($uriPath) ? $uriPath : '/';

        $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        $basePath = rtrim(str_replace('\\', '/', dirname($scriptName)), '/.');
        if (
            $basePath !== ''
            && $basePath !== '/'
            && ($path === $basePath || str_starts_with($path, $basePath . '/'))
        ) {
            $path = substr($path, strlen($basePath));
        }

        $path = '/' . trim($path, '/');
        if ($path === '//') {
            $path = '/';
        }

        return new self($method, $path, $_GET, $_POST, $_SERVER);
    }

    public function method(): string
    {
        return $this->method;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $this->query[$key] ?? $default;
    }

    /**
     * @return array<string, mixed>
     */
    public function body(): array
    {
        return $this->body;
    }

    public function server(string $key, ?string $default = null): ?string
    {
        $value = $this->server[$key] ?? $default;

        return is_string($value) ? $value : $default;
    }
}
