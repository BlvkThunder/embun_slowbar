<?php
require_once '../api/config.php';
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
$csrf = $_SESSION['csrf_token'];

function opt_label($k) {
    return match($k) {
        'sugar' => 'Sugar',
        'ice' => 'Ice',
        'add_sugar' => 'Add sugar',
        'notes' => 'Catatan',
        default => ucfirst(str_replace('_',' ',$k))
    };
}

function safe_json_decode($s) {
    if (empty($s)) return null;
    $j = json_decode($s, true);
    return is_array($j) ? $j : null;
}

/* ===== status filter (from GET) ===== */
$filter_status = isset($_GET['status']) ? trim($_GET['status']) : '';
$allowed_statuses = ['paid','settlement','pending','challenge','failed','cancelled','expired','deny']; // for sanity (not strict)
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Manajemen Order | Admin Embun Slowbar</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background: #fafafa; }
    .table-wrap { background: #fff; padding: 18px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }

    .item-options { font-size: 13px; color: #555; margin-top:6px; }
    .small-muted { font-size:13px; color:#666; }
    pre.json-raw { max-height: 320px; overflow:auto; background:#f8f9fa; padding:12px; border-radius:6px; }

    /* Table (desktop) */
    .orders-table { width: 100%; border-collapse: collapse; font-size:14px; }
    .orders-table th, .orders-table td { vertical-align: middle; padding: 10px; border-top: 1px solid #eef2f1; }

    .col-id { min-width: 56px; width:56px; }
    .col-orderid { min-width: 170px; max-width: 220px; }
    .col-name { min-width: 140px; max-width: 200px; }
    .col-email { min-width: 150px; max-width: 260px; }
    .col-item { min-width: 260px; max-width: 600px; } /* allow item column big but bounded */

    .item-summary { display:inline-block; max-width: 320px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; vertical-align: middle; }

    .nested-table th, .nested-table td { padding:6px; font-size:13px; white-space: normal; }

    /* mobile cards (hidden on desktop) */
    .mobile-cards { display:none; gap:12px; }
    .order-card { background:#fff; border-radius:10px; padding:12px; box-shadow:0 6px 18px rgba(0,0,0,0.04); border:1px solid #eef4ee; margin-bottom:12px; }
    .order-card .meta { font-size:13px; color:#555; margin-bottom:8px; }
    .order-card .actions { display:flex; gap:8px; margin-top:10px; flex-wrap:wrap; }

    /* responsive switching: hide table on small screens, show cards */
    @media (max-width: 920px) {
      .orders-table { display:none; }
      .mobile-cards { display:block; }
      .table-wrap { padding: 12px; }
    }

    .wa-link { text-decoration:none; color:#0a66c2; font-weight:600; }
    .wa-link:hover { text-decoration:underline; }
  </style>
</head>
<body>
<div class="container mt-4">
 <div class="d-flex align-items-center justify-content-between mb-3">
    <h1 class="mb-0">📦 Daftar Order</h1>

    <div class="d-flex gap-2">
        <!-- 🔥 Tombol menuju Orders History -->
        <a href="../history/orders_history.php" class="btn btn-warning">
            🕑 Order History
        </a>

        <!-- tombol lama tetap ada -->
        <a href="../panels/admin.php" class="btn btn-outline-secondary">
            ← Kembali ke admin.php
        </a>
    </div>
  </div>

  <div class="mb-3 d-flex gap-2 align-items-center">
    <a href="export_orders.php" class="btn btn-sm btn-outline-success">Export Semua CSV</a>
    <a href="export_orders.php?status=pending" class="btn btn-sm btn-outline-success">Export Pending</a>
    <a href="export_orders.php?today=1" class="btn btn-sm btn-outline-success">Export Hari Ini</a>

    <form method="get" class="d-inline-block me-2">
      <input class="form-control form-control-sm d-inline-block" style="width:auto" type="date" name="from" /> -
      <input class="form-control form-control-sm d-inline-block" style="width:auto" type="date" name="to" />
      <button class="btn btn-sm btn-outline-success" type="submit" formaction="export_orders.php">Export by date</button>
    </form>

    <!-- status filter form -->
    <form method="get" class="d-inline-block ms-auto">
      <select name="status" class="form-select form-select-sm" style="width:170px;display:inline-block">
        <option value="">-- Filter Status (semua) --</option>
        <option value="pending" <?= $filter_status === 'pending' ? 'selected' : '' ?>>Pending</option>
        <option value="paid" <?= $filter_status === 'paid' ? 'selected' : '' ?>>Paid</option>
        <option value="settlement" <?= $filter_status === 'settlement' ? 'selected' : '' ?>>Settlement</option>
        <option value="cancelled" <?= $filter_status === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
        <option value="failed" <?= $filter_status === 'failed' ? 'selected' : '' ?>>Failed</option>
      </select>
      <button class="btn btn-sm btn-outline-primary" type="submit">Terapkan</button>
      <?php if ($filter_status !== ''): ?>
        <a href="<?= strtok($_SERVER["REQUEST_URI"],'?') ?>" class="btn btn-sm btn-outline-secondary">Reset</a>
      <?php endif; ?>
    </form>
  </div>

  <?php if (!empty($_GET['msg'])): ?>
    <div class="alert alert-info"><?= htmlspecialchars($_GET['msg']) ?></div>
  <?php endif; ?>

  <div class="table-wrap">
    <?php
    /* ==== build query with optional status filter ==== */
    $params = [];
    $sql = "SELECT * FROM orders";
    if ($filter_status !== '') {
      $sql .= " WHERE status = ?";
      $params[] = $filter_status;
    }
    $sql .= " ORDER BY id DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <!-- Desktop table -->
    <div class="table-responsive">
      <table class="table table-bordered table-hover align-middle orders-table mb-0">
        <thead class="table-dark">
          <tr>
            <th class="col-id">ID</th>
            <th class="col-orderid">Order ID</th>
            <th class="col-name">Nama</th>
            <th class="col-email">Email / WA</th>
            <th class="col-item">Item (klik "Lihat" untuk detail)</th>
            <th>Status</th>
            <th>Pembayaran</th>
            <th>Waktu</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($orders as $row):
          $status_color = match(strtolower($row['status'] ?? '')) {
            'paid','settlement' => 'success',
            'pending','challenge' => 'warning',
            'failed','cancelled','expired','deny' => 'danger',
            default => 'secondary'
          };

          // decode items
          $items = safe_json_decode($row['order_items_json'] ?? ($row['order_items'] ?? '')) ;
          if ($items === null && !empty($row['cart_json'])) {
              $tmp = json_decode($row['cart_json'], true);
              if (is_array($tmp)) $items = $tmp;
          }

          // prepare summary (plain text for message and html for display)
          $summary_plain = '-';
          $summary_html = '-';
          if (is_array($items) && count($items) > 0) {
            $parts_plain = [];
            foreach ($items as $it) {
              $parts_plain[] = ($it['name'] ?? ('Item '.($it['id'] ?? '?'))) . (isset($it['quantity']) ? ' x'.$it['quantity'] : '');
            }
            $summary_plain = implode(', ', $parts_plain);
            $first = $items[0]['name'] ?? ('Item '.($items[0]['id'] ?? '?'));
            $count = count($items);
            $summary_html = htmlspecialchars($first) . ($count > 1 ? " <small class='text-muted'>+ " . ($count-1) . " lainnya</small>" : '');
          } else {
            $summary_plain = $row['item_name'] ?? '-';
            $summary_html = htmlspecialchars($row['item_name'] ?? '-');
          }

          // message for WA confirm
          $confirm_msg = "Halo, apakah benar " . ($row['customer_name'] ?: 'Tamu') . " memesan: " . $summary_plain . " ?";
          $collapseIdT = 'itemsCollapseT' . htmlspecialchars($row['id']);
        ?>
          <tr>
            <td class="col-id"><?= htmlspecialchars($row['id']) ?></td>
            <td class="col-orderid"><?= htmlspecialchars($row['order_id']) ?></td>
            <td class="col-name"><?= htmlspecialchars($row['customer_name']) ?></td>
            <td class="col-email">
              <?= htmlspecialchars($row['email'] ?? '-') ?>
              <?php if (!empty($row['wa_number'])): 
                // display clickable WA link that triggers JS confirmation and opens wa.me
                $wa_display = htmlspecialchars($row['wa_number']);
              ?>
                <div class="small-muted mt-1">
                  <a href="#" class="wa-link" data-wa="<?= htmlspecialchars($row['wa_number']) ?>" data-msg="<?= htmlspecialchars($confirm_msg) ?>">
                    WA: <?= $wa_display ?>
                  </a>
                </div>
              <?php endif; ?>
            </td>

            <td class="col-item">
              <div>
                <span class="item-summary"><?= $summary_html ?></span>
                <?php if (is_array($items) && count($items) > 0): ?>
                  <button class="btn btn-sm btn-outline-primary ms-2" type="button" data-bs-toggle="collapse" data-bs-target="#<?= $collapseIdT ?>" aria-expanded="false" aria-controls="<?= $collapseIdT ?>">
                    Lihat
                  </button>
                <?php endif; ?>
              </div>

              <?php if (is_array($items) && count($items) > 0): ?>
                <div class="collapse mt-2" id="<?= $collapseIdT ?>">
                  <div class="card card-body p-2">
                    <table class="table nested-table mb-0">
                      <thead>
                        <tr>
                          <th>Nama</th>
                          <th class="text-end">Harga</th>
                          <th class="text-end">Qty</th>
                          <th class="text-end">Subtotal</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php
                        $calc_total = 0;
                        foreach ($items as $it):
                          $iname = $it['name'] ?? ('Item ' . ($it['id'] ?? ''));
                          $iprice = isset($it['price']) ? (int)$it['price'] : 0;
                          $extra = isset($it['extra_price']) ? (int)$it['extra_price'] : 0;
                          $iqty = isset($it['quantity']) ? (int)$it['quantity'] : 1;
                          $row_sub = ($iprice + $extra) * $iqty;
                          $calc_total += $row_sub;
                        ?>
                        <tr>
                          <td style="vertical-align:middle;">
                            <strong><?= htmlspecialchars($iname) ?></strong>
                            <?php if (!empty($it['options']) && is_array($it['options'])): ?>
                              <div class="item-options">
                                <?php foreach ($it['options'] as $ok => $ov):
                                  $val = ($ok === 'add_sugar') ? (($ov) ? 'Ya' : 'Tidak') : (is_bool($ov) ? ($ov ? 'Ya' : 'Tidak') : (string)$ov);
                                ?>
                                  <div><small><strong><?= htmlspecialchars(opt_label($ok)) ?>:</strong> <?= htmlspecialchars($val) ?></small></div>
                                <?php endforeach; ?>
                              </div>
                            <?php endif; ?>
                          </td>
                          <td class="text-end"><?= number_format($iprice + $extra, 0, ',', '.') ?></td>
                          <td class="text-end"><?= number_format($iqty, 0, ',', '.') ?></td>
                          <td class="text-end"><?= number_format($row_sub, 0, ',', '.') ?></td>
                        </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                    <div class="small-muted mt-2">Subtotal terhitung: Rp <?= number_format($calc_total,0,',','.') ?></div>
                  </div>
                </div>
              <?php endif; ?>
            </td>

            <td><span class="badge bg-<?= $status_color ?>"><?= htmlspecialchars($row['status']) ?></span></td>
            <td><?= htmlspecialchars($row['payment_type'] ?? '-') ?></td>
            <td><?= htmlspecialchars($row['transaction_time'] ?? $row['created_at'] ?? '-') ?></td>

            <td class="d-flex gap-2">
              <a class="btn btn-sm btn-primary" href="sync_status.php?order_id=<?= urlencode($row['order_id']) ?>">Status</a>
              <a class="btn btn-sm btn-outline-primary" href="print_order.php?order_id=<?= urlencode($row['order_id']) ?>" target="_blank">Cetak</a>

              <form action="delete_order.php" method="post" onsubmit="return confirm('Hapus order ini?')" class="m-0">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                <input type="hidden" name="order_id" value="<?= htmlspecialchars($row['order_id']) ?>">
                <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
              </form>
            </td>
          </tr>

          <!-- Modal: JSON Raw (kept) -->
          <div class="modal fade" id="jsonModal<?= htmlspecialchars($row['id']) ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title">Raw Order JSON — <?= htmlspecialchars($row['order_id']) ?></h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <h6>order_items_json</h6>
                  <pre class="json-raw"><?= htmlspecialchars($row['order_items_json'] ?? '') ?></pre>

                  <h6 class="mt-3">snap_response</h6>
                  <pre class="json-raw"><?= htmlspecialchars($row['snap_response'] ?? '') ?></pre>

                  <h6 class="mt-3">Semua kolom (ringkasan)</h6>
                  <pre class="json-raw"><?= htmlspecialchars(json_encode($row, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
              </div>
            </div>
          </div>

        <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- Mobile cards container (render once) -->
    <div class="mobile-cards mt-3">
      <?php foreach ($orders as $row2):
        $items2 = safe_json_decode($row2['order_items_json'] ?? ($row2['order_items'] ?? ''));
        if ($items2 === null && !empty($row2['cart_json'])) {
          $tmp2 = json_decode($row2['cart_json'], true);
          if (is_array($tmp2)) $items2 = $tmp2;
        }
        $summary2 = '-';
        $summary_plain2 = '-';
        if (is_array($items2) && count($items2) > 0) {
          $parts2 = [];
          foreach ($items2 as $it2) $parts2[] = ($it2['name'] ?? ('Item '.($it2['id'] ?? '?'))) . (isset($it2['quantity']) ? ' x'.$it2['quantity'] : '');
          $summary_plain2 = implode(', ', $parts2);
          $first2 = $items2[0]['name'] ?? ('Item '.($items2[0]['id'] ?? '?'));
          $count2 = count($items2);
          $summary2 = htmlspecialchars($first2) . ($count2 > 1 ? " <small class='text-muted'>+ " . ($count2-1) . " lainnya</small>" : '');
        } else {
          $summary_plain2 = $row2['item_name'] ?? '-';
          $summary2 = htmlspecialchars($row2['item_name'] ?? '-');
        }
        $collapseIdC2 = 'itemsCollapseC' . htmlspecialchars($row2['id']);
        $confirm_msg2 = "Halo, apakah benar " . ($row2['customer_name'] ?: 'Tamu') . " memesan: " . $summary_plain2 . " ?";
      ?>
        <div class="order-card">
          <div style="display:flex;justify-content:space-between;align-items:center">
            <div>
              <div style="font-weight:700"><?= htmlspecialchars($row2['order_id']) ?></div>
              <div class="meta"><?= htmlspecialchars($row2['customer_name']) ?> • <?= htmlspecialchars($row2['email'] ?? '-') ?></div>
            </div>
            <div class="small-muted">ID <?= htmlspecialchars($row2['id']) ?></div>
          </div>

          <div style="margin-top:8px">
            <div><strong>Items:</strong> <?= $summary2 ?></div>
            <?php if (is_array($items2) && count($items2) > 0): ?>
              <div class="mt-2">
                <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#<?= $collapseIdC2 ?>" aria-expanded="false" aria-controls="<?= $collapseIdC2 ?>">Lihat detail</button>
              </div>

              <div class="collapse mt-2" id="<?= $collapseIdC2 ?>">
                <div class="card card-body p-2">
                  <table class="table nested-table mb-0">
                    <thead><tr><th>Nama</th><th class="text-end">Harga</th><th class="text-end">Qty</th></tr></thead>
                    <tbody>
                      <?php foreach ($items2 as $it2):
                        $iname2 = $it2['name'] ?? ('Item ' . ($it2['id'] ?? ''));
                        $iprice2 = isset($it2['price']) ? (int)$it2['price'] : 0;
                        $extra2 = isset($it2['extra_price']) ? (int)$it2['extra_price'] : 0;
                        $iqty2 = isset($it2['quantity']) ? (int)$it2['quantity'] : 1;
                      ?>
                        <tr>
                          <td style="vertical-align:middle"><strong><?= htmlspecialchars($iname2) ?></strong>
                            <?php if (!empty($it2['options']) && is_array($it2['options'])): ?>
                              <div class="item-options">
                                <?php foreach ($it2['options'] as $ok2 => $ov2):
                                  $val2 = ($ok2 === 'add_sugar') ? (($ov2) ? 'Ya' : 'Tidak') : (is_bool($ov2) ? ($ov2 ? 'Ya' : 'Tidak') : (string)$ov2);
                                ?>
                                  <div><small><strong><?= htmlspecialchars(opt_label($ok2)) ?>:</strong> <?= htmlspecialchars($val2) ?></small></div>
                                <?php endforeach; ?>
                              </div>
                            <?php endif; ?>
                          </td>
                          <td class="text-end"><?= number_format($iprice2 + $extra2,0,',','.') ?></td>
                          <td class="text-end"><?= number_format($iqty2,0,',','.') ?></td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            <?php endif; ?>
          </div>

          <div class="actions" style="margin-top:10px">
            <a class="btn btn-sm btn-primary" href="sync_status.php?order_id=<?= urlencode($row2['order_id']) ?>">Status</a>

            <?php if (!empty($row2['wa_number'])): ?>
              <a href="#" class="btn btn-sm btn-outline-success wa-link" data-wa="<?= htmlspecialchars($row2['wa_number']) ?>" data-msg="<?= htmlspecialchars($confirm_msg2) ?>">Konfirmasi via WA</a>
            <?php endif; ?>

            <form action="delete_order.php" method="post" onsubmit="return confirm('Hapus order ini?')" class="m-0">
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
              <input type="hidden" name="order_id" value="<?= htmlspecialchars($row2['order_id']) ?>">
              <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
            </form>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
/* WA confirmation click handling:
   - reads data-wa and data-msg
   - shows confirm() with message
   - when confirmed, opens wa.me link in new tab with encoded message
*/
document.addEventListener('DOMContentLoaded', function(){
  document.querySelectorAll('.wa-link').forEach(function(el){
    el.addEventListener('click', function(e){
      e.preventDefault();
      const wa = el.dataset.wa || '';
      let msg = el.dataset.msg || '';
      if (!wa) {
        alert('Nomor WA tidak tersedia.');
        return;
      }
      // show confirm dialog with message (as plain text)
      const ok = confirm(msg + "\n\nKirim konfirmasi via WhatsApp?");
      if (!ok) return;

      // normalize phone number: remove spaces, parentheses, pluses except leading +
      let phone = wa.replace(/[^0-9+]/g, '');

      // If phone starts with 0, try to convert to +62 when it's Indonesian local (optional)
      if (phone.startsWith('0')) {
        // you may want to change this logic depending on your expected number format
        phone = phone.replace(/^0+/, '');
        phone = '62' + phone;
      } else if (phone.startsWith('+')) {
        phone = phone.replace(/^\+/, '');
      }

      const encoded = encodeURIComponent(msg);
      const href = 'https://wa.me/' + phone + '?text=' + encoded;
      window.open(href, '_blank');
    });
  });
});
</script>

</body>
</html>
