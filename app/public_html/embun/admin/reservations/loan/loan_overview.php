<?php
// admin/reservations/loan/loan_overview.php
require_once '../../api/config.php';
date_default_timezone_set('Asia/Jakarta');

// Ambil semua loan (histori + yang masih aktif)
$sql = "
    SELECT
        l.id AS loan_id,
        b.title AS book_title,
        l.borrower_name,
        l.borrow_date,
        l.due_date,
        l.return_date,
        CASE 
            WHEN l.return_date IS NULL AND CURDATE() > l.due_date
                THEN DATEDIFF(CURDATE(), l.due_date)
            WHEN l.return_date IS NOT NULL AND l.return_date > l.due_date
                THEN DATEDIFF(l.return_date, l.due_date)
            ELSE 0
        END AS days_late,
        CASE 
            WHEN l.return_date IS NULL AND CURDATE() > l.due_date
                THEN DATEDIFF(CURDATE(), l.due_date) * 1000
            ELSE l.final_fine
        END AS current_fine
    FROM loans l
    JOIN books b ON b.id = l.book_id
    ORDER BY l.borrow_date DESC, l.id DESC
";

$stmt = $pdo->query($sql);
$loans = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Loan Overview | Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <style>
        body { background:#f6f8fb; font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
        .container-narrow { max-width: 1100px; }
        .btn-back {
            display:inline-block;background:#0d6efd;color:#fff;border-radius:10px;
            padding:10px 16px;text-decoration:none;font-weight:600;
            box-shadow:0 4px 12px rgba(13,110,253,.35);transition:.25s;
        }
        .btn-back:hover { background:#0b5ed7; transform: translateY(-2px); color:#fff; }
        .badge-status { font-size: .75rem; }
    </style>
</head>
<body>
<div class="container container-narrow py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="../reservations_admin.php" class="btn-back">
            ← Kembali ke Reservasi
        </a>
    </div>

    <div class="card">
        <div class="card-header bg-white">
            <h4 class="mb-0">Histori Peminjaman Buku (Loans)</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr class="text-center">
                            <th>#</th>
                            <th>Judul Buku</th>
                            <th>Nama Peminjam</th>
                            <th>Tgl Pinjam</th>
                            <th>Jatuh Tempo</th>
                            <th>Tgl Kembali</th>
                            <th>Status</th>
                            <th>Telat (hari)</th>
                            <th>Denda (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$loans): ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    Belum ada peminjaman buku.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $no = 1; foreach ($loans as $loan): ?>
                                <?php
                                    $isReturned = !empty($loan['return_date']);
                                    $isLate     = $loan['days_late'] > 0;
                                ?>
                                <tr class="text-center">
                                    <td><?= $no++; ?></td>
                                    <td class="text-start"><?= htmlspecialchars($loan['book_title']); ?></td>
                                    <td class="text-start"><?= htmlspecialchars($loan['borrower_name'] ?? '-'); ?></td>
                                    <td><?= htmlspecialchars($loan['borrow_date']); ?></td>
                                    <td><?= htmlspecialchars($loan['due_date']); ?></td>
                                    <td><?= $loan['return_date'] ? htmlspecialchars($loan['return_date']) : '–'; ?></td>
                                    <td>
                                        <?php if ($isReturned): ?>
                                            <span class="badge bg-success badge-status">Sudah Kembali</span>
                                        <?php else: ?>
                                            <?php if ($isLate): ?>
                                                <span class="badge bg-danger badge-status">Terlambat</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning text-dark badge-status">Dipinjam</span>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= (int)$loan['days_late']; ?></td>
                                    <td>Rp <?= number_format($loan['current_fine'], 0, ',', '.'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>
