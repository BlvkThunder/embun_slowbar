<?php
// admin/reservations/loan/create_loan.php
require_once '../../api/config.php';
date_default_timezone_set('Asia/Jakarta');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "Method not allowed";
    exit;
}

$reservation_id = isset($_POST['reservation_id']) ? (int)$_POST['reservation_id'] : 0;

if ($reservation_id <= 0) {
    echo "Reservasi tidak valid";
    exit;
}

try {
    // Ambil data reservasi
    $stmt = $pdo->prepare("
        SELECT id, user_id, book_id, date, created_at
        FROM reservations
        WHERE id = :id
          AND book_id IS NOT NULL
    ");
    $stmt->execute([':id' => $reservation_id]);
    $res = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$res) {
        echo "Reservasi tidak ditemukan atau tidak ada buku.";
        exit;
    }

    // Cek apakah sudah pernah dibuat loan untuk reservasi ini
    $stmt = $pdo->prepare("
        SELECT COUNT(*) AS total 
        FROM loans 
        WHERE reservation_id = :rid
    ");
    $stmt->execute([':rid' => $reservation_id]);
    $already = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];

    if ($already > 0) {
        // Sudah ada loan -> tidak perlu buat lagi
        header('Location: loan_overview.php');
        exit;
    }

    $book_id       = (int)$res['book_id'];
    $borrower_name = $res['user_id'] ?: null;

    // tanggal pinjam: pakai date, kalau kosong pakai tanggal created_at
    $borrow_date = $res['date'];
    if (!$borrow_date && !empty($res['created_at'])) {
        $borrow_date = substr($res['created_at'], 0, 10); // YYYY-MM-DD
    }
    if (!$borrow_date) {
        $borrow_date = date('Y-m-d');
    }

    // due date = 7 hari dari tanggal pinjam
    $stmt = $pdo->prepare("
        INSERT INTO loans (reservation_id, book_id, borrower_name, borrow_date, due_date)
        VALUES (:rid, :bid, :name, :bdate, DATE_ADD(:bdate, INTERVAL 7 DAY))
    ");
    $stmt->execute([
        ':rid'  => $reservation_id,
        ':bid'  => $book_id,
        ':name' => $borrower_name,
        ':bdate'=> $borrow_date
    ]);

    header('Location: loan_overview.php');
    exit;
} catch (PDOException $e) {
    echo "Error: " . htmlspecialchars($e->getMessage());
    exit;
}
