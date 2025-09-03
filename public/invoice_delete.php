<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$id = isset($_GET['id']) ? intval($_GET['id']) : null;
if (!$id) {
    respond(['error' => 'Missing invoice id'], 400);
}

$file = __DIR__ . '/../data/invoices.json';
$invoices = json_decode(file_get_contents($file), true);

$found = false;
foreach ($invoices as &$inv) {
    if ($inv['id'] == $id && (!isset($inv['deleted_at']) || $inv['deleted_at'] === null)) {
        $found = true;
        $inv['deleted_at'] = gmdate('Y-m-d\TH:i:s\Z');
        file_put_contents($file, json_encode($invoices, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        respond(['success' => true]);
    }
}
unset($inv);

if (!$found) {
    respond(['error' => 'Invoice not found'], 404);
}

function respond($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data);
    exit;
}