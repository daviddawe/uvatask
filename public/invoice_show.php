<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$id = isset($_GET['id']) ? intval($_GET['id']) : null;
if (!$id) {
    respond(['error' => 'Missing invoice id'], 400);
}

$invoices = json_decode(file_get_contents(__DIR__ . '/../data/invoices.json'), true);
$invoice = null;
foreach ($invoices as $inv) {
    if ($inv['id'] == $id && (!isset($inv['deleted_at']) || $inv['deleted_at'] === null)) {
        $invoice = $inv;
        break;
    }
}

if (!$invoice) {
    respond(['error' => 'Invoice not found'], 404);
}

respond($invoice);

function respond($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data);
    exit;
}