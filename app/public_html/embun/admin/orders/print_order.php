<?php
// print_order.php
require_once '../api/config.php';

$order_id = $_GET['order_id'] ?? '';
if (!$order_id) { echo "order_id missing"; exit; }

$stmt = $pdo->prepare("SELECT * FROM orders WHERE order_id = ? LIMIT 1");
$stmt->execute([$order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$order) { echo "Order not found"; exit; }

function safe_json_decode($s) {
    if (empty($s)) return null;
    $j = json_decode($s, true);
    return is_array($j) ? $j : null;
}

$items = safe_json_decode($order['order_items_json'] ?? ($order['cart_json'] ?? ''));
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Cetak Order <?= htmlspecialchars($order_id) ?></title>
  <style>
    body{font-family:Arial,Helvetica,sans-serif;color:#222;padding:18px}
    .wrap{max-width:760px;margin:0 auto}
    h1{font-size:18px;margin-bottom:6px}
    .meta{margin-bottom:12px}
    table{width:100%;border-collapse:collapse;margin-top:8px}
    th,td{border:1px solid #eee;padding:8px;text-align:left}
    .text-right{text-align:right}
    @media print {
      .no-print{display:none}
    }
  </style>
</head>
<body>
  <div class="wrap">
    <div style="display:flex;justify-content:space-between;align-items:center">
      <div>
        <h1>Order <?= htmlspecialchars($order['order_id']) ?></h1>
        <div class="meta">
          Nama: <?= htmlspecialchars($order['customer_name']) ?><br>
          Email: <?= htmlspecialchars($order['email']) ?><br>
          WA: <?= htmlspecialchars($order['wa_number'] ?? '-') ?><br>
          Status: <?= htmlspecialchars($order['status'] ?? '-') ?><br>
          Waktu: <?= htmlspecialchars($order['transaction_time'] ?? $order['created_at'] ?? '-') ?>
        </div>
      </div>
      <div class="no-print">
        <button onclick="window.print()">Cetak</button>
        <button onclick="window.close()">Tutup</button>
      </div>
    </div>

    <?php if (is_array($items) && count($items)>0): ?>
      <table>
        <thead><tr><th>Nama</th><th class="text-right">Harga</th><th class="text-right">Qty</th><th class="text-right">Subtotal</th></tr></thead>
        <tbody>
          <?php $total=0; foreach($items as $it): 
            $name = $it['name'] ?? ($it['id'] ?? 'Item');
            $price = isset($it['price']) ? (int)$it['price'] : 0;
            $extra = isset($it['extra_price']) ? (int)$it['extra_price'] : 0;
            $qty = isset($it['quantity']) ? (int)$it['quantity'] : 1;
            $sub = ($price + $extra) * $qty;
            $total += $sub;
          ?>
            <tr>
              <td>
                <strong><?= htmlspecialchars($name) ?></strong><br>
                <?php if (!empty($it['options']) && is_array($it['options'])): ?>
                  <small>
                  <?php foreach($it['options'] as $k=>$v){ 
                    $label = htmlspecialchars($k); $val = is_bool($v) ? ($v?'Ya':'Tidak') : htmlspecialchars((string)$v);
                    echo "<div>$label: $val</div>";
                  } ?>
                  </small>
                <?php endif; ?>
              </td>
              <td class="text-right"><?= number_format($price + $extra,0,',','.') ?></td>
              <td class="text-right"><?= number_format($qty,0,',','.') ?></td>
              <td class="text-right"><?= number_format($sub,0,',','.') ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr><th colspan="3" class="text-right">TOTAL</th><th class="text-right"><?= number_format($total,0,',','.') ?></th></tr>
        </tfoot>
      </table>
    <?php else: ?>
      <p>Tidak ada item (data tidak lengkap)</p>
    <?php endif; ?>

    <div style="margin-top:18px;font-size:13px;color:#666">
      Catatan: <?= htmlspecialchars($order['note'] ?? '-') ?>
    </div>
  </div>
</body>
</html>
