<?php

namespace App\Helpers;

class Response
{
    public static function success(array $data = [], string $message = 'Success'): void
    {
        self::send(['success' => true, 'message' => $message, 'data' => $data], 200);
    }

    public static function created(array $data = [], string $message = 'Resource created'): void
    {
        self::send(['success' => true, 'message' => $message, 'data' => $data], 201);
    }

    public static function error(string $message, int $statusCode = 400, array $errors = []): void
    {
        $response = ['success' => false, 'message' => $message];
        if (!empty($errors)) $response['errors'] = $errors;
        self::send($response, $statusCode);
    }

    public static function notFound(string $message = 'Resource not found'): void
    {
        self::send(['success' => false, 'message' => $message], 404);
    }

    public static function validationError(array $errors, string $message = 'Validation failed'): void
    {
        self::send(['success' => false, 'message' => $message, 'errors' => $errors], 422);
    }

    public static function serverError(string $message = 'Internal server error', array $debug = []): void
    {
        $response = ['success' => false, 'message' => $message];
        if (!empty($debug) && isset($_ENV['APP_ENV']) && $_ENV['APP_ENV'] === 'development') {
            $response['debug'] = $debug;
        }
        self::send($response, 500);
    }

    public static function json(array $data, int $statusCode = 200): void
    {
        self::send($data, $statusCode);
    }

    public static function paginated(array $items, int $total, int $page, int $perPage, string $message = 'Success'): void
    {
        $totalPages = ceil($total / $perPage);
        self::send([
            'success' => true,
            'message' => $message,
            'data' => $items,
            'pagination' => ['total' => $total, 'page' => $page, 'per_page' => $perPage, 'total_pages' => $totalPages]
        ], 200);
    }

    private static function send(array $data, int $statusCode = 200): void
    {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($statusCode);
        echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    }
}
