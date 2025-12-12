<?php
require_once '../../admin/api/config.php';

// Ambil order_id dari URL
$order_id = $_GET['order_id'] ?? '';

if ($order_id === '') {
    die("<h2>Order ID tidak ditemukan.</h2>");
}

// Ambil data order dari database
$stmt = $pdo->prepare("SELECT * FROM orders WHERE order_id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    die("<h2>Data order tidak ditemukan.</h2>");
}

// Tentukan warna/status
$status = strtolower($order['status'] ?? 'pending');
$status_badge = match($status) {
    'paid', 'settlement' => ['success', 'Pembayaran Berhasil ☕'],
    'pending' => ['warning', 'Menunggu Pembayaran ⏳'],
    'failed', 'deny', 'cancelled', 'expired' => ['danger', 'Pembayaran Gagal ❌'],
    default => ['secondary', ucfirst($status)]
};

list($status_color, $status_text) = $status_badge;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terima Kasih | Embun Slowbar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f9fafb;
            font-family: "Poppins", sans-serif;
            color: #333;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .thankyou-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.1);
            padding: 40px;
            text-align: center;
            max-width: 520px;
            width: 90%;
        }
        .thankyou-icon {
            font-size: 70px;
            margin-bottom: 20px;
        }
        .status-success { color: #28a745; }
        .status-warning { color: #ffc107; }
        .status-danger  { color: #dc3545; }
        h1 {
            font-weight: 700;
            margin-bottom: 10px;
        }
        .order-detail {
            margin-top: 25px;
            text-align: left;
        }
        .order-detail dt {
            color: #555;
        }
        .btn-home {
            background-color: #3d9970;
            color: #fff;
            font-weight: 600;
            border-radius: 8px;
            padding: 12px 28px;
            margin-top: 25px;
            text-decoration: none;
            transition: background 0.3s ease;
        }
        .btn-home:hover {
            background-color: #2e7d57;
            color: #fff;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="thankyou-card">
        <?php if ($status_color === 'success'): ?>
            <div class="thankyou-icon status-success">✅</div>
        <?php elseif ($status_color === 'warning'): ?>
            <div class="thankyou-icon status-warning">⏳</div>
        <?php else: ?>
            <div class="thankyou-icon status-danger">❌</div>
        <?php endif; ?>

        <h1><?= htmlspecialchars($status_text) ?></h1>
        <p>Terima kasih, <b><?= htmlspecialchars($order['customer_name']) ?></b>!  
           Pesanan kamu telah kami terima.</p>

        <div class="order-detail">
            <dl class="row">
                <dt class="col-5">Nomor Order:</dt>
                <dd class="col-7"><?= htmlspecialchars($order['order_id']) ?></dd>

                <dt class="col-5">Item:</dt>
                <dd class="col-7"><?= htmlspecialchars($order['item_name']) ?></dd>

                <dt class="col-5">Total:</dt>
                <dd class="col-7">Rp <?= number_format((int)$order['amount'], 0, ',', '.') ?></dd>

                <dt class="col-5">Status:</dt>
                <dd class="col-7">
                    <span class="badge bg-<?= $status_color ?>">
                        <?= ucfirst($status) ?>
                    </span>
                </dd>

                <?php if (!empty($order['transaction_time'])): ?>
                <dt class="col-5">Waktu:</dt>
                <dd class="col-7"><?= htmlspecialchars($order['transaction_time']) ?></dd>
                <?php endif; ?>
            </dl>
        </div>

        <a href="../panels/Index.php" class="btn-home">← Kembali ke Beranda</a>
    </div>
</body>
</html>
