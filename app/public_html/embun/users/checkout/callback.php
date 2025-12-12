<?php
// callback.php (SDK)
require_once '../api/config.php'; // pastikan ini mengarah ke file config (mis: config.php)
require_once __DIR__ . '/vendor/autoload.php';

header("Content-Type: application/json");

\Midtrans\Config::$serverKey    = $MIDTRANS_SERVER_KEY;
\Midtrans\Config::$isProduction = $MIDTRANS_IS_PRODUCTION;

try {
    $notif = new \Midtrans\Notification(); // otomatis baca JSON body

    $transaction_status = $notif->transaction_status;
    $payment_type       = $notif->payment_type;
    $order_id           = $notif->order_id;
    $fraud_status       = $notif->fraud_status ?? null;
    $transaction_time   = $notif->transaction_time ?? null;
    $va_numbers         = $notif->va_numbers ?? null; // untuk VA

    // Simpan raw log
    $raw = file_get_contents('php://input');
    $stmt = $pdo->prepare("INSERT INTO payment_logs (order_id, raw_payload) VALUES (?, ?)");
    $stmt->execute([$order_id, $raw]);

    // Mapping status
    $new_status = 'pending';
    if ($transaction_status === 'capture') {
        $new_status = ($fraud_status === 'challenge') ? 'pending' : 'paid';
    } elseif ($transaction_status === 'settlement') {
        $new_status = 'paid';
    } elseif ($transaction_status === 'pending') {
        $new_status = 'pending';
    } elseif (in_array($transaction_status, ['deny','cancel','expire'])) {
        $new_status = ($transaction_status === 'expire') ? 'expired' : 'failed';
    }

    // === 1) Update orders dengan status terbaru ===
    $stmt2 = $pdo->prepare("
        UPDATE orders
        SET status = ?, payment_type = ?, transaction_time = ?, va_numbers = ?
        WHERE order_id = ?
    ");
    $stmt2->execute([
        $new_status,
        $payment_type,
        $transaction_time,
        $va_numbers ? json_encode($va_numbers) : null,
        $order_id
    ]);

    // === 2) Ambil data order terbaru untuk disimpan ke orders_history ===
    $stmtOrder = $pdo->prepare("SELECT * FROM orders WHERE order_id = ? LIMIT 1");
    $stmtOrder->execute([$order_id]);
    $order = $stmtOrder->fetch(PDO::FETCH_ASSOC);

    if ($order) {
        // Siapkan nilai untuk INSERT ke orders_history
        $stmtHist = $pdo->prepare("
            INSERT INTO orders_history
                (order_db_id, order_id, customer_name, email, item_name, amount, status,
                 payment_type, transaction_time, va_numbers, snap_token, created_at,
                 order_items_json, snap_response, wa_number, action, action_by)
            VALUES
                (:order_db_id, :order_id, :customer_name, :email, :item_name, :amount, :status,
                 :payment_type, :transaction_time, :va_numbers, :snap_token, :created_at,
                 :order_items_json, :snap_response, :wa_number, :action, :action_by)
        ");

        $stmtHist->execute([
            ':order_db_id'      => $order['id'],
            ':order_id'         => $order['order_id'],
            ':customer_name'    => $order['customer_name'],
            ':email'            => $order['email'],
            ':item_name'        => $order['item_name'],
            ':amount'           => $order['amount'],
            ':status'           => $order['status'],              // status terbaru (paid/pending/failed/etc)
            ':payment_type'     => $order['payment_type'],
            ':transaction_time' => $order['transaction_time'],
            ':va_numbers'       => $order['va_numbers'],
            ':snap_token'       => $order['snap_token'],
            ':created_at'       => $order['created_at'],
            ':order_items_json' => $order['order_items_json'] ?? null,
            ':snap_response'    => $order['snap_response'] ?? null,
            ':wa_number'        => $order['wa_number'] ?? null,
            // pakai transaction_status Midtrans sebagai "aksi" biar kelihatan event-nya: settlement/capture/cancel/expire/dll
            ':action'           => $transaction_status,
            ':action_by'        => 'midtrans-callback',
        ]);
    }

    echo json_encode(['ok' => true]);
} catch (\Exception $e) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
