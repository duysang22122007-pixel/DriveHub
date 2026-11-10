<?php

declare(strict_types=1);

namespace App\Core;

use App\Middleware\MiddlewareInterface;
use RuntimeException;

final class Router
{
    /**
     * @var list<array{
     *     method: string,
     *     path: string,
     *     handler: callable,
     *     middleware: list<MiddlewareInterface>
     * }>
     */
    private array $routes = [];

    /**
     * @param list<MiddlewareInterface> $middleware
     */
    public function get(string $path, callable $handler, array $middleware = []): void
    {
        $this->add('GET', $path, $handler, $middleware);
    }

    /**
     * @param list<MiddlewareInterface> $middleware
     */
    public function post(string $path, callable $handler, array $middleware = []): void
    {
        $this->add('POST', $path, $handler, $middleware);
    }

    public function dispatch(Request $request): Response
    {
        $allowedMethods = [];

        foreach ($this->routes as $route) {
            $parameters = $this->matchPath($route['path'], $request->path());
            if ($parameters === null) {
                continue;
            }

            $allowedMethods[] = $route['method'];
            if ($route['method'] !== $request->method()) {
                continue;
            }

            $next = static function (Request $currentRequest) use ($route, $parameters): Response {
                $response = ($route['handler'])($currentRequest, $parameters);
                if (!$response instanceof Response) {
                    throw new RuntimeException('Route handlers must return a Response.');
                }

                return $response;
            };

            foreach (array_reverse($route['middleware']) as $middleware) {
                $nextMiddleware = $next;
                $next = static fn (Request $currentRequest): Response => $middleware->handle($currentRequest, $nextMiddleware);
            }

            return $next($request);
        }

        if ($allowedMethods !== []) {
            return Response::methodNotAllowed(array_values(array_unique($allowedMethods)));
        }

        return Response::notFound();
    }

    /**
     * @param list<MiddlewareInterface> $middleware
     */
    private function add(string $method, string $path, callable $handler, array $middleware): void
    {
        if ($path === '' || $path[0] !== '/') {
            throw new RuntimeException('Route paths must start with /.');
        }

        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
            'middleware' => $middleware,
        ];
    }

    /**
     * @return array<string, string>|null
     */
    private function matchPath(string $routePath, string $requestPath): ?array
    {
        $names = [];
        $segments = explode('/', trim($routePath, '/'));
        $patternSegments = [];

        foreach ($segments as $segment) {
            if (preg_match('/\A\{([a-zA-Z_][a-zA-Z0-9_]*)\}\z/', $segment, $match)) {
                $names[] = $match[1];
                $patternSegments[] = '([^/]+)';
                continue;
            }

            $patternSegments[] = preg_quote($segment, '#');
        }

        $pattern = '#^/' . implode('/', $patternSegments) . '/?$#uD';
        if (!preg_match($pattern, $requestPath, $matches)) {
            return null;
        }

        $parameters = [];
        foreach ($names as $index => $name) {
            $parameters[$name] = rawurldecode($matches[$index + 1]);
        }

        return $parameters;
    }
}
