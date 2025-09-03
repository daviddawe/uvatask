<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: no-store, must-revalidate');

$invoicesFile = '../data/invoices.json';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $invoices = json_decode(file_get_contents($invoicesFile), true);
    if (!is_array($invoices)) {
        http_response_code(200);
        echo json_encode([
            'data' => [],
            'page' => 1,
            'per_page' => 10,
            'total' => 0,
            'error' => 'Could not read invoices.json or file is malformed'
        ]);
        exit;
    }

    $status = isset($_GET['status']) && $_GET['status'] !== '' ? $_GET['status'] : null;
    $q = isset($_GET['q']) && $_GET['q'] !== '' ? strtolower($_GET['q']) : null;
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $perPage = isset($_GET['per_page']) ? max(1, intval($_GET['per_page'])) : 10;

    $filteredInvoices = array_filter($invoices, function($invoice) {
        return !isset($invoice['deleted_at']) || $invoice['deleted_at'] === null;
    });

    if ($status) {
        $filteredInvoices = array_filter($filteredInvoices, function($invoice) use ($status) {
            return isset($invoice['status']) && $invoice['status'] === $status;
        });
    }

    if ($q) {
        $filteredInvoices = array_filter($filteredInvoices, function($invoice) use ($q) {
            return isset($invoice['customer']) && strpos(strtolower($invoice['customer']), $q) !== false;
        });
    }

    $total = count($filteredInvoices);
    $filteredInvoices = array_values($filteredInvoices);
    $start = ($page - 1) * $perPage;
    $paged = array_slice($filteredInvoices, $start, $perPage);

    echo json_encode([
        'data' => $paged,
        'page' => $page,
        'per_page' => $perPage,
        'total' => $total
    ]);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method Not Allowed']);