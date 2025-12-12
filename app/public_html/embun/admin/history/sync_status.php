<?php
// admin/history/sync_status.php
session_start();
require_once __DIR__ . '/../api/config.php';

// Proteksi admin
if (!isset($_SESSION['username']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ../../../../login/login.html');
    exit;
}

$order_id = $_GET['order_id'] ?? '';
if ($order_id === '') {
    header('Location: orders_history.php?msg=' . urlencode('order_id kosong'));
    exit;
}

$serverKey = trim($MIDTRANS_SERVER_KEY ?? '');
if ($serverKey === '') {
    header('Location: orders_history.php?msg=' . urlencode('Server key Midtrans belum diset.'));
    exit;
}

$auth    = base64_encode($serverKey . ':');
$baseApi = !empty($MIDTRANS_IS_PRODUCTION) ? 'https://api.midtrans.com'
                                           : 'https://api.sandbox.midtrans.com';
$url     = $baseApi . '/v2/' . rawurlencode($order_id) . '/status';

// ====== Panggil Midtrans /v2/{order_id}/status ======
$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => [
        'Accept: application/json',
        'Authorization: Basic ' . $auth
    ],
    CURLOPT_TIMEOUT        => 20
]);

$res  = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$err  = curl_error($ch);
curl_close($ch);

if ($err || $code < 200 || $code >= 300) {
    $msg = "Gagal sync ($code): " . ($err ?: $res);
    header('Location: orders_history.php?msg=' . urlencode($msg));
    exit;
}

$data = json_decode($res, true);

// Ambil field penting dari Midtrans
$transaction_status = $data['transaction_status'] ?? 'pending';
$payment_type       = $data['payment_type']       ?? null;
$transaction_time   = $data['transaction_time']   ?? null;
$fraud_status       = $data['fraud_status']       ?? null;

// ====== Mapping ke status di tabel orders ======
$new_status = 'pending';
if ($transaction_status === 'capture') {
    $new_status = ($fraud_status === 'challenge') ? 'challenge' : 'paid';
} elseif ($transaction_status === 'settlement') {
    $new_status = 'paid';
} elseif ($transaction_status === 'pending') {
    $new_status = 'pending';
} elseif (in_array($transaction_status, ['deny','cancel','expire'], true)) {
    $new_status = ($transaction_status === 'expire') ? 'expired' : 'failed';
}

try {
    $pdo->beginTransaction();

    // 1) Update tabel orders
    $stm = $pdo->prepare("
        UPDATE orders
        SET status = ?, payment_type = ?, transaction_time = ?
        WHERE order_id = ?
    ");
    $stm->execute([$new_status, $payment_type, $transaction_time, $order_id]);

    // 2) Ambil snapshot orders setelah update
    $stmtSel = $pdo->prepare("SELECT * FROM orders WHERE order_id = ?");
    $stmtSel->execute([$order_id]);
    $orderRow = $stmtSel->fetch(PDO::FETCH_ASSOC);

    if ($orderRow) {
        // 3) Simpan ke orders_history (action = sync_status)
        $stmtHist = $pdo->prepare("
            INSERT INTO orders_history
                (order_db_id, order_id, customer_name, email, item_name, amount, status,
                 payment_type, transaction_time, va_numbers, snap_token, created_at,
                 order_items_json, snap_response, wa_number, action, action_by, action_at)
            VALUES
                (:order_db_id, :order_id, :customer_name, :email, :item_name, :amount, :status,
                 :payment_type, :transaction_time, :va_numbers, :snap_token, :created_at,
                 :order_items_json, :snap_response, :wa_number, :action, :action_by, NOW())
        ");

        $stmtHist->execute([
            ':order_db_id'      => $orderRow['id']               ?? null,
            ':order_id'         => $orderRow['order_id']         ?? null,
            ':customer_name'    => $orderRow['customer_name']    ?? null,
            ':email'            => $orderRow['email']            ?? null,
            ':item_name'        => $orderRow['item_name']        ?? null,
            ':amount'           => $orderRow['amount']           ?? null,
            ':status'           => $orderRow['status']           ?? $new_status,
            ':payment_type'     => $orderRow['payment_type']     ?? $payment_type,
            ':transaction_time' => $orderRow['transaction_time'] ?? $transaction_time,
            ':va_numbers'       => $orderRow['va_numbers']       ?? null,
            ':snap_token'       => $orderRow['snap_token']       ?? null,
            ':created_at'       => $orderRow['created_at']       ?? null,
            ':order_items_json' => $orderRow['order_items_json'] ?? null,
            ':snap_response'    => $orderRow['snap_response']    ?? null,
            ':wa_number'        => $orderRow['wa_number']        ?? null,
            ':action'           => 'sync_status',
            ':action_by'        => $_SESSION['username']         ?? 'admin',
        ]);
    }

    $pdo->commit();

    $msg = "Sync sukses: $order_id → $new_status";
    header('Location: orders_history.php?msg=' . urlencode($msg));
    exit;

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('sync_status (history) error: '.$e->getMessage());
    header('Location: orders_history.php?msg=' . urlencode('Gagal sync (DB error).'));
    exit;
}
