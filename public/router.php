<?php

$requestUri  = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestMethod = $_SERVER['REQUEST_METHOD'];

if (strpos($requestUri, '/api/') === 0) {
    switch (true) {
        // List invoices with filters and pagination
        case $requestMethod === 'GET' && $requestUri === '/api/invoices':
            require __DIR__ . '/invoices.php';
            exit;

        // Create a new invoice (stretch)
        case $requestMethod === 'POST' && $requestUri === '/api/invoices':
            require __DIR__ . '/invoices_create.php';
            exit;

        // Read one invoice (stretch)
        case $requestMethod === 'GET' && preg_match('#^/api/invoices/(\d+)$#', $requestUri, $m):
            $_GET['id'] = $m[1];
            require __DIR__ . '/invoice_show.php';
            exit;

        // Mark as paid (idempotent)
        case $requestMethod === 'POST' && preg_match('#^/api/invoices/(\d+)/pay$#', $requestUri, $m):
            $_GET['id'] = $m[1];
            require __DIR__ . '/invoice_pay.php';
            exit;

        // Update invoice (stretch)
        case ($requestMethod === 'PUT' || $requestMethod === 'PATCH') && preg_match('#^/api/invoices/(\d+)$#', $requestUri, $m):
            $_GET['id'] = $m[1];
            require __DIR__ . '/invoice_update.php';
            exit;

        // Delete (soft) invoice (stretch)
        case $requestMethod === 'DELETE' && preg_match('#^/api/invoices/(\d+)$#', $requestUri, $m):
            $_GET['id'] = $m[1];
            require __DIR__ . '/invoice_delete.php';
            exit;
    }

    http_response_code(404);
    echo json_encode(['error' => 'API route not found']);
    exit;
}

// --- NON-API REQUESTS ---

$file = __DIR__ . $requestUri;
if (is_file($file)) {
    return false;
}

require __DIR__ . '/index.html';