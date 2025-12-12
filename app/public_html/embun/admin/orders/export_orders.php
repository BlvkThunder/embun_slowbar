<?php
// export_orders.php
require_once '../api/config.php'; // sesuaikan path jika perlu

// Simple auth check? (opsional) — pastikan hanya admin mengakses
// if (!isAdmin()) { http_response_code(403); exit; }

$from = $_GET['from'] ?? null;
$to   = $_GET['to']   ?? null;
$status = $_GET['status'] ?? null;

$q = "SELECT * FROM orders WHERE 1=1";
$params = [];

if (!empty($status)) {
  $q .= " AND status = ?";
  $params[] = $status;
}
if (!empty($from)) {
  $q .= " AND DATE(created_at) >= ?";
  $params[] = $from;
}
if (!empty($to)) {
  $q .= " AND DATE(created_at) <= ?";
  $params[] = $to;
}
$q .= " ORDER BY id DESC";

$stmt = $pdo->prepare($q);
$stmt->execute($params);

// set CSV headers
$filename = 'embun_orders_' . date('Ymd_His') . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);
$output = fopen('php://output', 'w');

// BOM untuk Excel (optional)
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// CSV header row
fputcsv($output, ['id','order_id','customer_name','email','wa_number','item_summary','items_detail','amount','status','payment_type','transaction_time','created_at']);

// helper untuk decode items to readable text
function items_to_text($items_json, $cart_json_fallback) {
    $items = null;
    if (!empty($items_json)) $items = json_decode($items_json, true);
    if ($items === null && !empty($cart_json_fallback)) $items = json_decode($cart_json_fallback, true);
    if (!is_array($items)) return '';
    $parts = [];
    foreach ($items as $it) {
        $name = $it['name'] ?? ($it['id'] ?? 'Item');
        $qty  = isset($it['quantity']) ? ' x'.$it['quantity'] : '';
        $opt_text = '';
        if (!empty($it['options']) && is_array($it['options'])) {
            $opts = [];
            foreach ($it['options'] as $k=>$v) {
                if ($k === 'add_sugar') $v = ($v ? 'Yes' : 'No');
                $opts[] = $k.':'.$v;
            }
            if ($opts) $opt_text = ' (' . implode(';', $opts) . ')';
        }
        $parts[] = $name . $qty . $opt_text;
    }
    return implode(' | ', $parts);
}

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $summary = $row['item_name'] ?? '';
    // build detailed items text
    $items_text = items_to_text($row['order_items_json'] ?? null, $row['cart_json'] ?? null);
    fputcsv($output, [
        $row['id'],
        $row['order_id'],
        $row['customer_name'],
        $row['email'],
        $row['wa_number'] ?? '',
        $summary,
        $items_text,
        (int)$row['amount'],
        $row['status'] ?? '',
        $row['payment_type'] ?? '',
        $row['transaction_time'] ?? '',
        $row['created_at'] ?? ''
    ]);
}

fclose($output);
exit;
