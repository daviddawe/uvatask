<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$file = __DIR__ . '/../data/invoices.json';
$invoices = json_decode(file_get_contents($file), true);
if (!is_array($invoices)) {
    $invoices = [];
}

$data = json_decode(file_get_contents('php://input'), true);
if (!$data || !isset($data['customer'], $data['amount'], $data['currency'], $data['due_date'])) {
    respond(['error' => 'Missing required fields'], 400);
}

if (count($invoices) === 0) {
    $newId = 1;
} else {
    $newId = max(array_column($invoices, 'id')) + 1;
}

$newInvoice = [
    'id' => $newId,
    'customer' => $data['customer'],
    'amount' => $data['amount'],
    'currency' => $data['currency'],
    'status' => 'pending',
    'due_date' => $data['due_date'],
    'updated_at' => gmdate('Y-m-d\TH:i:s\Z'),
    'deleted_at' => null
];

$invoices[] = $newInvoice;
file_put_contents($file, json_encode($invoices, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
respond($newInvoice, 201);

function respond($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data);
    exit;
}