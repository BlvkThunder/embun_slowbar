<?php
session_start();
require_once __DIR__ . '/../api/config.php';

// Proteksi admin (sesuaikan dengan sistemmu)
if (!isset($_SESSION['username']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ../../../../login/login.html');
    exit;
}

/**
 * Auto-clean: hapus history yang lebih lama dari 14 hari
 */
try {
    $pdo->exec("DELETE FROM orders_history WHERE action_at < DATE_SUB(NOW(), INTERVAL 14 DAY)");
} catch (PDOException $e) {
    error_log('Cleanup orders_history 14 days error: '.$e->getMessage());
}

// Filter sederhana
$order_id_filter = trim($_GET['order_id'] ?? '');
$status_filter   = trim($_GET['status']   ?? '');
$action_filter   = trim($_GET['action']   ?? '');

// Build query
$sql    = "SELECT * FROM orders_history WHERE 1=1";
$params = [];

if ($order_id_filter !== '') {
    $sql .= " AND order_id LIKE :order_id";
    $params[':order_id'] = "%{$order_id_filter}%";
}
if ($status_filter !== '') {
    $sql .= " AND status = :status";
    $params[':status'] = $status_filter;
}
if ($action_filter !== '') {
    $sql .= " AND action = :action";
    $params[':action'] = $action_filter;
}

$sql .= " ORDER BY action_at DESC, id DESC LIMIT 200";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// daftar status & action untuk dropdown
$status_options = ['','pending','paid','settlement','failed','expired','cancel','deny'];
$action_options = ['','created','capture','settlement','pending','cancel','expire','deny','sync_status'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Orders History - Admin Embun Slowbar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f6f7fb;
            font-size: 14px;
        }
        .page-wrapper {
            max-width: 1200px;
            margin: 24px auto;
        }
        .card-main {
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(15, 23, 42, .08);
        }
        .card-header {
            border-bottom: 1px solid #e2e8f0;
        }
        .badge-status {
            padding: 3px 8px;
            border-radius: 999px;
            font-size: 11px;
            letter-spacing: .3px;
        }
        .badge-status-paid       { background:#d1fae5; color:#065f46; }
        .badge-status-pending    { background:#fef9c3; color:#854d0e; }
        .badge-status-failed     { background:#fee2e2; color:#991b1b; }
        .badge-status-other      { background:#e0f2fe; color:#1d4ed8; }

        .filter-chip {
            font-size: 13px;
        }
        .table thead th {
            white-space: nowrap;
            background:#0f172a;
            color:#f9fafb;
            border-color:#0f172a;
        }
        .table tbody tr:nth-child(even) {
            background:#f9fafb;
        }
        .table tbody tr:hover {
            background:#e5f0ff;
        }
        .text-small {
            font-size: 12px;
            color:#64748b;
        }
        @media (max-width: 992px) {
            .page-wrapper { margin: 12px; }
        }
    </style>
</head>
<body>
<div class="page-wrapper">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="mb-0">🕑 Orders History</h3>
            <div class="text-small mt-1">
                Riwayat perubahan status dan aktivitas order (tersimpan 14 hari terakhir, maks. 200 entri).
            </div>
        </div>
        <a href="../orders/orders.php" class="btn btn-outline-secondary btn-sm">
            &larr; Kembali ke Daftar Order
        </a>
    </div>

    <div class="card card-main">
        <div class="card-header bg-white">
            <form class="row gy-2 gx-2 align-items-end" method="get">
                <div class="col-md-3">
                    <label class="form-label mb-1">Order ID</label>
                    <input type="text"
                           name="order_id"
                           value="<?= htmlspecialchars($order_id_filter) ?>"
                           class="form-control form-control-sm"
                           placeholder="Cari ORDER ID...">
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-1">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Semua status</option>
                        <?php foreach ($status_options as $st): ?>
                            <?php if ($st === '') continue; ?>
                            <option value="<?= htmlspecialchars($st) ?>"
                                <?= $status_filter === $st ? 'selected' : '' ?>>
                                <?= htmlspecialchars(ucfirst($st)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-1">Action (event)</label>
                    <select name="action" class="form-select form-select-sm">
                        <option value="">Semua action</option>
                        <?php foreach ($action_options as $ac): ?>
                            <?php if ($ac === '') continue; ?>
                            <option value="<?= htmlspecialchars($ac) ?>"
                                <?= $action_filter === $ac ? 'selected' : '' ?>>
                                <?= htmlspecialchars(ucfirst($ac)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 text-md-end">
                    <button type="submit" class="btn btn-primary btn-sm me-1">
                        Filter
                    </button>
                    <a href="orders_history.php" class="btn btn-outline-secondary btn-sm">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead>
                    <tr>
                        <th class="text-center" style="width:40px;">#</th>
                        <th style="width:150px;">Action Time</th>
                        <th style="width:110px;">Action</th>
                        <th style="width:90px;" class="text-center">Aksi</th>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th style="width:110px;" class="text-end">Amount</th>
                        <th style="width:100px;">Status</th>
                        <th style="width:120px;">Payment Type</th>
                        <th style="width:130px;">WA</th>
                        <th style="width:120px;">Action By</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($rows)): ?>
                        <tr>
                            <td colspan="10" class="text-center py-4 text-muted">
                                Belum ada data history (atau data lebih lama dari 14 hari sudah dihapus).
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($rows as $i => $r): ?>
                            <?php
                            $status = $r['status'] ?? '';
                            $badgeClass = 'badge-status-other';
                            if ($status === 'paid' || $status === 'settlement') {
                                $badgeClass = 'badge-status-paid';
                            } elseif ($status === 'pending' || $status === 'challenge') {
                                $badgeClass = 'badge-status-pending';
                            } elseif ($status === 'failed' || $status === 'expired' || $status === 'cancel') {
                                $badgeClass = 'badge-status-failed';
                            }

                            $amount = (int)($r['amount'] ?? 0);
                            ?>
                            <tr>
                                <td class="text-center"><?= $i + 1 ?></td>
                                <td><?= htmlspecialchars($r['action_at'] ?? '') ?></td>
                                <td>
                                    <span class="badge bg-light text-dark border filter-chip">
                                        <?= htmlspecialchars($r['action'] ?? '') ?>
                                    </span>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($r['order_id'] ?? '') ?></strong><br>
                                    <span class="text-small text-muted">
                                        DB ID: <?= htmlspecialchars($r['order_db_id'] ?? '-') ?>
                                    </span>
                                </td>
                                <td>
                                    <?= htmlspecialchars($r['customer_name'] ?? '') ?><br>
                                    <span class="text-small text-muted">
                                        <?= htmlspecialchars($r['email'] ?? '') ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    Rp <?= number_format($amount, 0, ',', '.') ?>
                                </td>
                                <td>
                                    <span class="badge badge-status <?= $badgeClass ?>">
                                        <?= htmlspecialchars($status ?: '-') ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($r['payment_type'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($r['wa_number'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($r['action_by'] ?? '-') ?></td>
                                <td class="text-center">
                                    <a href="sync_status.php?order_id=<?= urlencode($r['order_id']) ?>"
                                    class="btn btn-sm btn-primary">
                                        Status
                                    </a>
                                </td>

                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if (!empty($rows)): ?>
                <div class="px-3 py-2 border-top text-small text-muted">
                    Menampilkan maksimal <strong>200</strong> entri terbaru (hanya 14 hari terakhir yang disimpan).
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>
</body>
</html>
