<?php

namespace App\Core;

/**
 * Base Controller
 */
class Controller
{
    /**
     * Render view with optional layout
     *
     * @param string $view   Path relatif (contoh: 'student/home')
     * @param array  $data   Data ke view
     * @param string|null $layout  'student' | 'admin' | null
     */
    protected function view(string $view, array $data = [], ?string $layout = null): void
    {
        extract($data);

        $viewPath = __DIR__ . '/../Views/' . $view . '.php';

        if (!file_exists($viewPath)) {
            http_response_code(404);
            echo "View not found: {$view}";
            return;
        }

        // Auto-detect layout dari path view
        if ($layout === null) {
            if (str_starts_with($view, 'admin/') || $view === 'admin/dashboard') {
                $layout = 'admin';
            } elseif (str_starts_with($view, 'auth/')) {
                $layout = null; // auth punya HTML sendiri
            } else {
                $layout = 'student';
            }
        }

        if ($layout) {
            ob_start();
            require $viewPath;
            $content = ob_get_clean();
            require __DIR__ . '/../Views/layouts/' . $layout . '.php';
        } else {
            require $viewPath;
        }
    }

    protected function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }

    protected function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function validateCsrf(): bool
    {
        $token = $_POST['_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        return Session::validateCsrfToken($token);
    }

    protected function input(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $default;
    }

    protected function query(string $key, mixed $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }

    protected function backWithError(string $message): void
    {
        Session::flash('error', $message);
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        $this->redirect($referer);
    }

    protected function redirectWithSuccess(string $url, string $message): void
    {
        Session::flash('success', $message);
        $this->redirect($url);
    }
}
