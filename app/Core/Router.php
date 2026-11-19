<?php

namespace App\Core;

/**
 * Simple Router
 * 
 * Routing berbasis method dan path.
 * Mendukung parameter dinamis seperti /project/{id}
 */
class Router
{
    private array $routes = [];
    private array $middlewares = [];

    /**
     * Daftarkan route GET
     */
    public function get(string $path, array|string $handler, array $middleware = []): void
    {
        $this->addRoute('GET', $path, $handler, $middleware);
    }

    /**
     * Daftarkan route POST
     */
    public function post(string $path, array|string $handler, array $middleware = []): void
    {
        $this->addRoute('POST', $path, $handler, $middleware);
    }

    /**
     * Daftarkan route
     */
    private function addRoute(string $method, string $path, array|string $handler, array $middleware): void
    {
        $this->routes[] = [
            'method'     => $method,
            'path'       => $path,
            'handler'    => $handler,
            'middleware' => $middleware,
        ];
    }

    /**
     * Jalankan router
     */
    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri = rtrim($uri, '/') ?: '/';

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            // Konversi path menjadi regex (support {param})
            $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '([^/]+)', $route['path']);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches); // buang full match

                // Jalankan middleware
                foreach ($route['middleware'] as $mw) {
                    $this->runMiddleware($mw);
                }

                // Jalankan handler
                $this->runHandler($route['handler'], $matches);
                return;
            }
        }

        // 404 Not Found
        http_response_code(404);
        echo '<h1>404 - Halaman Tidak Ditemukan</h1>';
        echo '<p>URI: ' . htmlspecialchars($uri) . '</p>';
        echo '<a href="/">Kembali ke Beranda</a>';
    }

    /**
     * Jalankan middleware
     */
    private function runMiddleware(string $middleware): void
    {
        match ($middleware) {
            'auth'    => Auth::requireLogin(),
            'admin'   => Auth::requireAdmin(),
            'student' => Auth::requireStudent(),
            'guest'   => $this->requireGuest(),
            default   => null,
        };
    }

    /**
     * Middleware: hanya untuk user yang belum login
     */
    private function requireGuest(): void
    {
        if (Auth::check()) {
            Auth::redirectByRole();
        }
    }

    /**
     * Jalankan controller/action
     */
    private function runHandler(array|string $handler, array $params): void
    {
        if (is_string($handler)) {
            // Format: "Controller@method"
            [$controllerName, $method] = explode('@', $handler);
        } else {
            // Format: [Controller::class, 'method']
            [$controllerName, $method] = $handler;
        }

        // Jika belum fully qualified, tambahkan namespace
        if (!str_contains($controllerName, '\\')) {
            $controllerName = 'App\\Controllers\\' . $controllerName;
        }

        if (!class_exists($controllerName)) {
            throw new \Exception("Controller not found: {$controllerName}");
        }

        $controller = new $controllerName();

        if (!method_exists($controller, $method)) {
            throw new \Exception("Method not found: {$controllerName}@{$method}");
        }

        call_user_func_array([$controller, $method], $params);
    }
}
