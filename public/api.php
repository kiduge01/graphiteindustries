<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
try {
    require __DIR__ . '/../app/bootstrap.php';
    security_headers();
    $action = $_GET['action'] ?? '';
    if (!is_string($action)) {
        fail('Invalid action.');
    }
    $method = $_SERVER['REQUEST_METHOD'];
    $data = [];
    if ($method === 'POST' && $action !== 'upload') {
        $raw = file_get_contents('php://input', false, null, 0, 100001);
        if (strlen($raw) > 100000) {
            fail('Request too large.', 413);
        }
        $data = json_decode($raw, true);
        if (!is_array($data)) {
            fail('Invalid request data.');
        }
    }
    $reads = ['health', 'public', 'me', 'dashboard', 'list', 'mediaItem', 'export'];
    if (!in_array($action, $reads, true) && $method !== 'POST') {
        fail('Use POST for this action.', 405);
    }
    if ($method === 'POST') {
        session_boot();
        if (!hash_equals($_SESSION['csrf'], $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '')) {
            fail('Your session expired. Refresh and try again.', 403);
        }
    }
    require ROOT . '/app/actions/public.php';
    if (in_array($action, ['me', 'login'], true)) {
        require ROOT . '/app/actions/auth.php';
    }
    $user = require_user();
    require ROOT . '/app/actions/auth.php';
    require ROOT . '/app/actions/dashboard.php';
    require ROOT . '/app/actions/content.php';
    require ROOT . '/app/actions/admin.php';
    fail('Unknown action.', 404);
}
catch (Throwable $e) {
    if (function_exists('db')) {
        try {
            if (db()->inTransaction()) {
                db()->rollBack();
            }
        }
        catch (Throwable $ignored) {
        }
    }
    error_log('Graphite application: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'The application is unavailable. Please check configuration or contact the administrator.']);
}
