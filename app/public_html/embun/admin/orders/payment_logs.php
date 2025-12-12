<?php
require_once '../api/config.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Payment Logs | Admin Embun Slowbar</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <style>
    body { background: #fafafa; }
    .table-wrap { background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    pre { white-space: pre-wrap; word-wrap: break-word; }
  </style>
</head>
<body>
<div class="container mt-4">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h1 class="mb-0">💰 Payment Logs</h1>
    <a href="../panels/admin.php" class="btn btn-outline-secondary">← Kembali ke admin.php</a>
  </div>

  <div class="table-wrap">
    <table class="table table-bordered table-hover align-middle">
      <thead class="table-dark">
        <tr>
          <th>#</th>
          <th>Order ID</th>
          <th>Waktu</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $stmt = $pdo->query("SELECT * FROM payment_logs ORDER BY id DESC");
        foreach ($stmt as $row):
        ?>
        <tr>
          <td><?= htmlspecialchars($row['id']) ?></td>
          <td><?= htmlspecialchars($row['order_id']) ?></td>
          <td><?= htmlspecialchars($row['created_at']) ?></td>
          <td>
            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#logModal<?= $row['id'] ?>">
              Lihat Detail
            </button>
          </td>
        </tr>

        <!-- Modal -->
        <div class="modal fade" id="logModal<?= $row['id'] ?>" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">
                  Log #<?= htmlspecialchars($row['id']) ?> — <?= htmlspecialchars($row['order_id']) ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body">
                <pre><?= htmlspecialchars(json_encode(json_decode($row['raw_payload'], true), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) ?></pre>
              </div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>
