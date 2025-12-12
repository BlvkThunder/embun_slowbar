<?php
session_start();
require_once __DIR__ . '/../api/config.php';

// Proteksi admin
if (!isset($_SESSION['username']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ../../../../login/login.html');
    exit;
}

/**
 * Auto-clean: hapus history yang lebih lama dari 14 hari
 */
try {
    $pdo->exec("DELETE FROM reservations_history WHERE action_at < DATE_SUB(NOW(), INTERVAL 14 DAY)");
} catch (PDOException $e) {
    error_log('Cleanup reservations_history 14 days error: '.$e->getMessage());
}

// filter GET
$user_filter   = trim($_GET['user_id'] ?? '');
$room_filter   = trim($_GET['room']    ?? '');
$action_filter = trim($_GET['action']  ?? '');

// query
$sql = "SELECT * FROM reservations_history WHERE 1=1";
$params = [];

if ($user_filter !== '') {
    $sql .= " AND user_id LIKE :user_id";
    $params[':user_id'] = "%{$user_filter}%";
}
if ($room_filter !== '') {
    $sql .= " AND room = :room";
    $params[':room'] = $room_filter;
}
if ($action_filter !== '') {
    $sql .= " AND action = :action";
    $params[':action'] = $action_filter;
}

$sql .= " ORDER BY action_at DESC, id DESC LIMIT 200";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// filter action: cuma created & deleted
$action_options = ['', 'created', 'deleted'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Reservations History - Admin Embun Slowbar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #f6f7fb;
            font-size: 14px;
        }
        .page-wrapper {
            max-width: 1100px;
            margin: 20px auto;
        }
        .card-main {
            border-radius: 16px;
            box-shadow: 0 6px 20px rgba(15,23,42,.08);
        }
        .card-header {
            padding: 10px 16px;
            border-bottom: 1px solid #e2e8f0;
        }
        .card-body {
            padding: 10px 16px 12px;
        }
        .table thead th {
            white-space: nowrap;
            background:#0f172a;
            color:#f9fafb;
            border-color:#0f172a;
            font-size: 12px;
        }
        .table tbody tr:nth-child(even) {
            background:#f9fafb;
        }
        .table tbody tr:hover {
            background:#e5f0ff;
        }
        .badge-action {
            padding: 3px 7px;
            border-radius: 999px;
            font-size: 11px;
        }

        .badge-created  { background:#d1fae5; color:#065f46; }
        .badge-updated  { background:#fef9c3; color:#854d0e; }
        .badge-deleted  { background:#fee2e2; color:#991b1b; }
        .badge-canceled { background:#ffe4e6; color:#b91c1c; }
        .badge-other    { background:#e0f2fe; color:#1d4ed8; }

        .badge-type {
            padding: 2px 8px;
            border-radius: 999px;
            font-size: 11px;
        }
        .badge-type-room   { background:#dcfce7; color:#166534; }
        .badge-type-book   { background:#e0f2fe; color:#1d4ed8; }
        .badge-type-both   { background:#fef3c7; color:#92400e; }

        .text-small { font-size: 12px; color:#64748b; }

        .filter-label { font-size: 13px; margin-bottom: 2px; }

        .filter-btns .btn {
            font-size: 12px;
            padding: 4px 10px;
        }

        @media (max-width: 992px) {
            .page-wrapper { margin: 12px; }
            .card-body { padding: 10px; }
        }
    </style>
</head>

<body>

<div class="page-wrapper">

    <!-- header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">📘 Reservations History</h4>
            <div class="text-small mt-1">
                Riwayat semua aktifitas reservasi (tersimpan 14 hari terakhir, max 200 data).
            </div>
        </div>

        <a href="../reservations/reservations_admin.php" class="btn btn-outline-secondary btn-sm">
            &larr; Kembali ke Daftar Reservasi
        </a>
    </div>

    <!-- card -->
    <div class="card card-main">
        <div class="card-header bg-white">

            <form class="row gy-2 gx-2 align-items-end" method="get">
                
                <div class="col-md-4">
                    <label class="filter-label">User ID</label>
                    <input type="text"
                        name="user_id"
                        value="<?= htmlspecialchars($user_filter) ?>"
                        class="form-control form-control-sm"
                        placeholder="Cari user ID...">
                </div>

                <div class="col-md-4">
                    <label class="filter-label">Room</label>
                    <input type="text"
                        name="room"
                        value="<?= htmlspecialchars($room_filter) ?>"
                        class="form-control form-control-sm"
                        placeholder="AC / Meeting / dll">
                </div>

                <div class="col-md-3">
                    <label class="filter-label">Action</label>
                    <select name="action" class="form-select form-select-sm">
                        <option value="">Semua action</option>
                        <?php foreach ($action_options as $ac): ?>
                            <?php if ($ac === '') continue; ?>
                            <option value="<?= $ac ?>" <?= $action_filter === $ac ? 'selected' : '' ?>>
                                <?= ucfirst($ac) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-1 text-md-end">
                    <button class="btn btn-primary btn-sm me-1" type="submit">Filter</button>
                    <a href="reservations_history.php" class="btn btn-outline-secondary btn-sm">Reset</a>
                </div>

            </form>

        </div>

        <div class="card-body">

            <!-- Filter tipe (Semua / Ruangan / Buku / Ruangan+Buku) -->
            <div class="d-flex align-items-center mb-2">
                <span class="text-small me-2">Tipe:</span>
                <div class="btn-group filter-btns" role="group" aria-label="Filter tipe reservasi">
                    <button type="button" class="btn btn-outline-primary btn-sm active" data-type="all">Semua</button>
                    <button type="button" class="btn btn-outline-primary btn-sm" data-type="room">Ruangan</button>
                    <button type="button" class="btn btn-outline-primary btn-sm" data-type="book">Buku</button>
                    <button type="button" class="btn btn-outline-primary btn-sm" data-type="both">Ruangan + Buku</button>
                </div>
            </div>

            <div class="table-responsive">
                <table id="historyTable" class="table table-sm mb-0">
                    <thead>
                    <tr>
                        <th class="text-center" style="width:40px;">#</th>
                        <th style="width:160px">Action Time</th>
                        <th style="width:110px">Action</th>
                        <th style="width:120px">Tipe</th>
                        <th style="width:220px">Reservation</th>
                        <th>Detail</th>
                        <th style="width:140px">WA</th>
                        <th style="width:120px">Action By</th>
                    </tr>
                    </thead>

                    <tbody>
                    <?php if (empty($rows)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                Belum ada data history (atau data lebih lama dari 14 hari sudah dihapus).
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($rows as $i => $r): ?>
                            <?php
                                // badge action
                                $badge = 'badge-other';
                                if ($r['action'] === 'created')  $badge = 'badge-created';
                                if ($r['action'] === 'updated')  $badge = 'badge-updated';
                                if ($r['action'] === 'deleted')  $badge = 'badge-deleted';
                                if ($r['action'] === 'canceled') $badge = 'badge-canceled';

                                // deteksi tipe (room/book/both)
                                $hasBook = !is_null($r['book_id']) && $r['book_id'] !== '';
                                $hasRoom = (
                                    !empty($r['room']) ||
                                    !empty($r['date']) ||
                                    !empty($r['time']) ||
                                    !empty($r['people']) ||
                                    !empty($r['duration'])
                                );

                                if ($hasBook && $hasRoom) {
                                    $type = 'both';
                                    $typeBadge = '<span class="badge-type badge-type-both">ROOM + BOOK</span>';
                                } elseif ($hasBook) {
                                    $type = 'book';
                                    $typeBadge = '<span class="badge-type badge-type-book">BOOK</span>';
                                } else {
                                    $type = 'room';
                                    $typeBadge = '<span class="badge-type badge-type-room">ROOM</span>';
                                }

                                $durationLabel = $r['duration'] !== null && $r['duration'] !== ''
                                    ? $r['duration'].' jam'
                                    : '-';
                            ?>
                            <tr data-type="<?= htmlspecialchars($type) ?>">
                                <td class="text-center"><?= $i+1 ?></td>

                                <td>
                                    <?= htmlspecialchars($r['action_at']) ?><br>
                                    <span class="text-small text-muted">
                                        ID: <?= htmlspecialchars($r['id']) ?>
                                    </span>
                                </td>

                                <td>
                                    <span class="badge-action <?= $badge ?>">
                                        <?= htmlspecialchars($r['action']) ?>
                                    </span>
                                </td>

                                <td class="text-center">
                                    <?= $typeBadge ?>
                                </td>

                                <td>
                                    <strong>Reservation #<?= htmlspecialchars($r['reservation_id']) ?></strong><br>
                                    <span class="text-small">
                                        User: <?= htmlspecialchars($r['user_id']) ?>
                                        <?php if ($r['book_id'] !== null && $r['book_id'] !== ''): ?>
                                            · Book ID: <?= htmlspecialchars($r['book_id']) ?>
                                        <?php endif; ?>
                                    </span>
                                </td>

                                <td>
                                    <?php if (!empty($r['room'])): ?>
                                        <span class="text-small d-block">
                                            <strong>Room:</strong> <?= htmlspecialchars($r['room']) ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if (!empty($r['people'])): ?>
                                        <span class="text-small d-block">
                                            <strong>People:</strong> <?= htmlspecialchars($r['people']) ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if (!empty($r['date'])): ?>
                                        <span class="text-small d-block">
                                            <strong>Date:</strong> <?= htmlspecialchars($r['date']) ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if (!empty($r['time'])): ?>
                                        <span class="text-small d-block">
                                            <strong>Time:</strong> <?= htmlspecialchars($r['time']) ?>
                                        </span>
                                    <?php endif; ?>
                                    <span class="text-small d-block">
                                        <strong>Duration:</strong> <?= htmlspecialchars($durationLabel) ?>
                                    </span>
                                </td>

                                <td>
                                    <?php if (!empty($r['whatsapp'])): ?>
                                        <span class="d-block"><?= htmlspecialchars($r['whatsapp']) ?></span>
                                    <?php else: ?>
                                        <span class="text-small text-muted">-</span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($r['action_by'] ?: '-') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>

                </table>
            </div>

            <?php if (!empty($rows)): ?>
                <div class="px-1 pt-2 text-small text-muted">
                    Menampilkan maksimal <strong>200</strong> data terbaru (hanya 14 hari terakhir yang disimpan).
                </div>
            <?php endif; ?>
        </div>

    </div>

</div>

<script>
// filter tipe (Semua / Ruangan / Buku / Ruangan + Buku) pakai JS
(function(){
    const rows    = Array.from(document.querySelectorAll('#historyTable tbody tr'));
    const buttons = document.querySelectorAll('.filter-btns .btn');
    let currentType = 'all';

    function applyFilter() {
        rows.forEach(row => {
            const t = row.getAttribute('data-type') || 'room';
            row.style.display = (currentType === 'all' || t === currentType) ? '' : 'none';
        });
    }

    buttons.forEach(btn => {
        btn.addEventListener('click', () => {
            buttons.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            currentType = btn.getAttribute('data-type');
            applyFilter();
        });
    });
})();
</script>

</body>
</html>
