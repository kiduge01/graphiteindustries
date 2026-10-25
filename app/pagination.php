<?php
declare(strict_types=1);
function page_number(string $key = 'page'): int
{
    $value = $_GET[$key] ?? '1';
    if (!is_string($value) || !ctype_digit($value) || (int)$value < 1) {
        fail('Invalid page number.');
    }
    return min((int)$value, 1000000);
}
function collection(string $resource): array
{
    if (!in_array($resource, ['posts', 'media', 'enquiries', 'users'], true)) {
        fail('Unknown collection.', 404);
    }
    if ($resource === 'users') {
        require_user(true);
    }
    $where = [];
    $params = [];
    $search = $_GET['search'] ?? '';
    if (!is_string($search) || strlen($search) > 190) {
        fail('Invalid search.');
    }
    if ($search !== '') {
        $columns = match ($resource) {
            'posts' => ['title', 'summary'],
            'media' => ['name'],
            'users' => ['name', 'email'],
            'enquiries' => ['name', 'email', 'category', 'message'],
        }
        ;
        $where[] = '(' . implode(' OR ', array_map(fn($c) => "$c LIKE ?", $columns)) . ')';
        $params = array_fill(0, count($columns), '%' . $search . '%');
    }
    $status = $_GET['status'] ?? '';
    if (!is_string($status)) {
        fail('Invalid status.');
    }
    if ($status !== '' && in_array($resource, ['posts', 'enquiries'], true)) {
        $where[] = 'status=?';
        $params[] = $status;
    }
    if ($resource === 'media' && ($_GET['images'] ?? '') === '1') {
        $where[] = "type LIKE 'image/%'";
    }
    $clause = $where ? ' WHERE ' . implode(' AND ', $where) : '';
    $total = (int)query("SELECT COUNT(*) FROM $resource$clause", $params)->fetchColumn();
    $pages = max(1, (int)ceil($total / 20));
    $page = min(page_number(), $pages);
    $offset = ($page - 1) * 20;
    $fields = $resource === 'users' ? 'id,name,email,role,active' : '*';
    return [
        'items' => all("SELECT $fields FROM $resource$clause ORDER BY id DESC LIMIT 20 OFFSET $offset", $params),
        'page' => $page,
        'pages' => $pages,
        'total' => $total,
    ];
}
