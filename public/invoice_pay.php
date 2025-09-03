<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$id = isset($_GET['id']) ? intval($_GET['id']) : null;
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$auditFile = __DIR__ . '/../data/audit.log';

function audit($invoice_id, $outcome, $ip, $auditFile) {
    $entry = [
        'timestamp' => gmdate('c'),
        'invoice_id' => $invoice_id,
        'outcome' => $outcome,
        'ip' => $ip
    ];
    file_put_contents($auditFile, json_encode($entry) . PHP_EOL . PHP_EOL, FILE_APPEND);
}

if (!$id) {
    audit(null, 'invalid', $ip, $auditFile);
    respond(['error' => 'Missing invoice id'], 400);
}

$file = __DIR__ . '/../data/invoices.json';
$invoices = json_decode(file_get_contents($file), true);
$found = false;

foreach ($invoices as &$inv) {
    if ($inv['id'] == $id && (!isset($inv['deleted_at']) || $inv['deleted_at'] === null)) {
        $found = true;
        if ($inv['status'] === 'paid') {
            audit($id, 'already_paid', $ip, $auditFile);
            respond(['id' => $id, 'status' => 'paid', 'already_paid' => true, 'updated_at' => $inv['updated_at']]);
        }
        $inv['status'] = 'paid';
        $inv['updated_at'] = gmdate('Y-m-d\TH:i:s\Z');
        file_put_contents($file, json_encode($invoices, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        audit($id, 'paid', $ip, $auditFile);
        respond(['id' => $id, 'status' => 'paid', 'already_paid' => false, 'updated_at' => $inv['updated_at']]);
    }
}
unset($inv);

if (!$found) {
    audit($id, 'not_found', $ip, $auditFile);
    respond(['error' => 'Invoice not found'], 404);
}

function respond($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data);
    exit;
}