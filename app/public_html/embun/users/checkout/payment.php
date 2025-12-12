<?php
// payment.php (cURL, multi item, cart_json) - patched version (save options + wa_number + extra_price)
require_once '../../admin/api/config.php';
header('Content-Type: application/json');

/**
 * Normalisasi harga agar tidak "nambah dua nol".
 */
function normalize_price($v) {
    if (is_numeric($v)) {
        return (int) round($v);
    }
    if (is_string($v)) {
        $s = trim($v);
        if (preg_match('/[.,]/', $s)) {
            $s = str_replace(',', '.', $s);
            $f = floatval($s);
            return (int) round($f);
        }
        $onlyDigits = preg_replace('/\D+/', '', $s);
        return $onlyDigits !== '' ? (int)$onlyDigits : 0;
    }
    return 0;
}

/** Simple logger untuk debugging (file) */
function embun_log($msg) {
    $path = sys_get_temp_dir() . '/embun_payment.log'; // /tmp atau system temp
    $time = date('Y-m-d H:i:s');
    error_log("[$time] $msg\n", 3, $path);
}

// ===== 1) Ambil & validasi input dasar =====
$customer_name = trim($_POST['customer_name'] ?? '');
$email         = trim($_POST['email'] ?? '');
$item_name_in  = trim($_POST['item_name'] ?? ''); // ringkasan dari frontend jika ada
$amount_form   = normalize_price($_POST['amount'] ?? 0); // diabaikan jika pakai cart_json
$cart_json_str = $_POST['cart_json'] ?? '[]';
$cart_items    = json_decode($cart_json_str, true);
$wa_number     = trim($_POST['wa_number'] ?? '');

// Validasi minimal
if ($customer_name === '' || $email === '') {
    echo json_encode(['success' => false, 'message' => 'Nama dan email wajib diisi']); exit;
}
if (!is_array($cart_items)) $cart_items = [];

// ===== 2) Susun item_details dari cart (jika ada) =====
// item_details untuk Midtrans (harus berisi price per unit yang sudah termasuk extra_price jika ada)
$item_details = [];
$grand_total  = 0;

if (!empty($cart_items)) {
    $i = 0;
    foreach ($cart_items as $it) {
        $i++;
        $id       = isset($it['id']) ? (string)$it['id'] : ('item-' . $i);
        $name     = isset($it['name']) ? (string)$it['name'] : ('Item ' . $i);
        $price    = normalize_price($it['price'] ?? 0);
        // extra_price optionally provided by frontend (mis. add_sugar)
        $extra    = isset($it['extra_price']) ? (int)$it['extra_price'] : 0;
        $quantity = (int)($it['quantity'] ?? 1);
        if ($quantity < 1) $quantity = 1;

        // Abaikan baris invalid
        if ($price <= 0 && $extra <= 0) continue;

        // price_for_midtrans adalah price + extra (per unit)
        $price_for_midtrans = $price + $extra;

        $item_details[] = [
            'id'       => $id,
            'price'    => $price_for_midtrans,
            'quantity' => $quantity,
            'name'     => $name
        ];

        // total tambah (price + extra) * qty
        $grand_total += ($price_for_midtrans * $quantity);
    }

    if ($grand_total <= 0) {
        echo json_encode(['success' => false, 'message' => 'Keranjang tidak valid (total 0)']); exit;
    }
} else {
    // Fallback: 1 item dari form lama (tidak pakai keranjang)
    if ($amount_form <= 0 && $item_name_in === '') {
        echo json_encode(['success' => false, 'message' => 'Item/Total tidak valid']); exit;
    }
    $fallback_name = $item_name_in !== '' ? $item_name_in : 'Keranjang Embun';
    $item_details[] = [
        'id'       => 'item-1',
        'price'    => ($amount_form > 0 ? $amount_form : 0),
        'quantity' => 1,
        'name'     => $fallback_name
    ];
    $grand_total = ($amount_form > 0 ? $amount_form : 0);
}

// Jika frontend tidak memberi item_name, buat ringkasan dari item_details
if ($item_name_in === '') {
    $countItems = count($item_details);
    if ($countItems === 1) {
        $item_name = $item_details[0]['name'];
    } else {
        // ambil 1-3 nama untuk ringkasan
        $names = array_map(function($it){ return $it['name']; }, array_slice($item_details, 0, 3));
        $item_name = implode(', ', $names) . ($countItems > 3 ? " +".($countItems-3)." lainnya" : "");
        $item_name = "Keranjang — " . $item_name;
    }
} else {
    $item_name = $item_name_in;
}

// ===== 3) Buat order_id & simpan "pending" ke DB =====
try {
    $order_id = 'EMBUN-' . date('YmdHis') . '-' . random_int(1000, 9999);
} catch (Exception $e) {
    $order_id = 'EMBUN-' . date('YmdHis') . '-' . mt_rand(1000, 9999);
}

// Insert order (simpan wa_number juga)
try {
    // pakai created_at dari PHP supaya bisa dipakai juga ke history
    $created_at = date('Y-m-d H:i:s');

    $stmt = $pdo->prepare("
        INSERT INTO orders 
            (order_id, customer_name, email, item_name, amount, status, wa_number, created_at)
        VALUES 
            (?, ?, ?, ?, ?, 'pending', ?, ?)
    ");
    $stmt->execute([
        $order_id,
        $customer_name,
        $email,
        $item_name,
        $grand_total,
        $wa_number ?: null,
        $created_at
    ]);

    // === HISTORY: simpan ke orders_history saat order dibuat ===
    $order_db_id = $pdo->lastInsertId();   // id PK dari tabel orders
    $action      = 'created';
    $action_by   = 'user'; // kalau pakai login admin/user bisa diganti $_SESSION['username']

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
        ':order_db_id'      => $order_db_id,
        ':order_id'         => $order_id,
        ':customer_name'    => $customer_name,
        ':email'            => $email,
        ':item_name'        => $item_name,
        ':amount'           => $grand_total,
        ':status'           => 'pending',
        ':payment_type'     => null,      // belum tahu, nanti di-update via callback
        ':transaction_time' => null,
        ':va_numbers'       => null,
        ':snap_token'       => null,      // belum ada sebelum panggil Snap
        ':created_at'       => $created_at,
        ':order_items_json' => null,      // nanti bisa catat lagi dengan action berbeda
        ':snap_response'    => null,
        ':wa_number'        => $wa_number ?: null,
        ':action'           => $action,
        ':action_by'        => $action_by,
    ]);

} catch (Exception $e) {
    embun_log("DB INSERT error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'DB error: ' . $e->getMessage()]); exit;
}

// Simpan RAW cart_json yang dikirim frontend agar admin melihat FULL options (non-fatal if column missing)
try {
    $raw_cart_json_to_save = json_encode($cart_items, JSON_UNESCAPED_UNICODE);
    $up = $pdo->prepare("UPDATE orders SET order_items_json = ? WHERE order_id = ?");
    $up->execute([$raw_cart_json_to_save, $order_id]);
} catch (Exception $e) {
    embun_log("DB UPDATE order_items_json failed (non-fatal): " . $e->getMessage());
}

// Juga simpan item_details (yang sudah disesuaikan untuk Midtrans) supaya ada ringkasan yang mudah dibaca
try {
    $items_json = json_encode($item_details, JSON_UNESCAPED_UNICODE);
    $up2 = $pdo->prepare("UPDATE orders SET item_details = ? WHERE order_id = ?");
    $up2->execute([$items_json, $order_id]);
} catch (Exception $e) {
    // kolom item_details opsional; log saja
    embun_log("DB UPDATE item_details failed (non-fatal): " . $e->getMessage());
}

// ===== 4) Siapkan payload Snap =====
$base_midtrans = (!empty($MIDTRANS_IS_PRODUCTION) && $MIDTRANS_IS_PRODUCTION) ? 'https://app.midtrans.com' : 'https://app.sandbox.midtrans.com';
$url = $base_midtrans . '/snap/v1/transactions';

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$finish_url = $scheme . '://' . $host . '/embun/thankyou.php?order_id=' . urlencode($order_id);

$payload = [
    'transaction_details' => [
        'order_id'     => $order_id,
        'gross_amount' => $grand_total
    ],
    'item_details' => $item_details,
    'customer_details' => [
        'first_name' => $customer_name,
        'email'      => $email
    ],
    'callbacks' => [
        'finish' => $finish_url
    ]
];

// ===== 5) Panggil Snap (cURL) =====
$serverKey = trim($MIDTRANS_SERVER_KEY ?? '');
$auth      = base64_encode($serverKey . ':');

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'Accept: application/json',
        'Authorization: Basic ' . $auth
    ],
    CURLOPT_POSTFIELDS     => json_encode($payload),
    CURLOPT_TIMEOUT        => 30
]);

$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error    = curl_error($ch);
curl_close($ch);

// Simpan respons mentah ke log (aman)
embun_log("Midtrans response for {$order_id}: HTTP {$httpcode}; error={$error}; resp=" . substr($response ?? '', 0, 2000));

// ===== 6) Tangani hasil =====
if ($error) {
    try { $pdo->prepare("UPDATE orders SET status='failed' WHERE order_id=?")->execute([$order_id]); } catch(Exception $e){}
    echo json_encode(['success' => false, 'message' => 'cURL error: ' . $error]); exit;
}

if ($httpcode === 401) {
    try {
        $pdo->prepare("UPDATE orders SET status='failed', snap_response=? WHERE order_id=?")->execute([substr($response ?? '',0,1000), $order_id]);
    } catch (Exception $e) {
        embun_log("Failed to save snap_response on 401: " . $e->getMessage());
    }

    $msg = 'HTTP 401 (Unauthorized). Periksa Midtrans Server Key, environment (sandbox/production), dan header Authorization "Basic base64(SERVER_KEY:)".';
    echo json_encode([
        'success' => false,
        'message' => $msg,
        'http_code' => 401,
        'response_sample' => substr($response ?? '', 0, 1000)
    ]);
    exit;
}

if ($httpcode < 200 || $httpcode >= 300) {
    try { $pdo->prepare("UPDATE orders SET status='failed' WHERE order_id=?")->execute([$order_id]); } catch(Exception $e){}
    echo json_encode(['success' => false, 'message' => 'HTTP ' . $httpcode, 'response' => $response]); exit;
}

$data = json_decode($response, true);
$snapToken = $data['token'] ?? null;

// Jika tidak ada token — anggap gagal
if (!$snapToken) {
    try { $pdo->prepare("UPDATE orders SET status='failed', snap_response=? WHERE order_id=?")->execute([substr($response ?? '',0,1000), $order_id]); } catch(Exception $e){}
    echo json_encode(['success' => false, 'message' => 'Snap token tidak diterima', 'response' => $response]); exit;
}

// Simpan snap_token + response ke DB (jika kolom ada)
try {
    $pdo->prepare("UPDATE orders SET snap_token=?, status='pending', snap_response=? WHERE order_id=?")
        ->execute([$snapToken, substr($response ?? '',0,2000), $order_id]);
} catch (Exception $e) {
    embun_log("Failed to save snap_token/snap_response: " . $e->getMessage());
}

echo json_encode([
    'success'   => true,
    'order_id'  => $order_id,
    'snapToken' => $snapToken,
    'finish_url' => $finish_url
]);
