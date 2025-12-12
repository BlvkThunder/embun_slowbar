<?php
// admin/sync_status.php
require_once '../api/config.php';

$order_id = $_GET['order_id'] ?? '';
if ($order_id === '') {
  header('Location: orders.php?msg=' . urlencode('order_id kosong')); exit;
}

$serverKey = trim($MIDTRANS_SERVER_KEY);
$auth      = base64_encode($serverKey . ':');
$baseApi   = $MIDTRANS_IS_PRODUCTION ? 'https://api.midtrans.com' : 'https://api.sandbox.midtrans.com';
$url       = $baseApi . '/v2/' . rawurlencode($order_id) . '/status';

$ch = curl_init($url);
curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_HTTPHEADER => [
    'Accept: application/json',
    'Authorization: Basic ' . $auth
  ],
  CURLOPT_TIMEOUT => 20
]);
$res  = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$err  = curl_error($ch);
curl_close($ch);

if ($err || $code < 200 || $code >= 300) {
  header('Location: orders.php?msg=' . urlencode("Gagal sync ($code): " . ($err ?: $res))); exit;
}

$data = json_decode($res, true);
$transaction_status = $data['transaction_status'] ?? 'pending';
$payment_type       = $data['payment_type'] ?? null;
$transaction_time   = $data['transaction_time'] ?? null;
$fraud_status       = $data['fraud_status'] ?? null;

// Mapping status ke kolom 'status' di DB
$new_status = 'pending';
if ($transaction_status === 'capture') {
  $new_status = ($fraud_status === 'challenge') ? 'challenge' : 'paid';
} elseif ($transaction_status === 'settlement') {
  $new_status = 'paid';
} elseif ($transaction_status === 'pending') {
  $new_status = 'pending';
} elseif (in_array($transaction_status, ['deny','cancel','expire'])) {
  $new_status = ($transaction_status === 'expire') ? 'expired' : 'failed';
}

$stm = $pdo->prepare("UPDATE orders SET status=?, payment_type=?, transaction_time=? WHERE order_id=?");
$stm->execute([$new_status, $payment_type, $transaction_time, $order_id]);

header('Location: orders.php?msg=' . urlencode("Sync sukses: $order_id → $new_status"));
