<?php

$invoiceId = $argv[1] ?? 1;

$apiUrl = "http://localhost:8000/api/invoices/{$invoiceId}/pay";

$options = [
    'http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/json\r\n"
    ]
];
$context = stream_context_create($options);
$response = file_get_contents($apiUrl, false, $context);

echo "Response:\n";
echo $response . "\n";

$data = json_decode($response, true);

if (isset($data['id']) && $data['status'] === 'paid') {
    echo "✅ Pay operation test passed.\n";
} else {
    echo "❌ Pay operation test failed.\n";
}