<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

require_once "../../admin/api/config.php"; // koneksi ke $pdo

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'error'   => 'Invalid request method'
    ]);
    exit;
}

// ambil data dari form
$user_id  = trim($_POST['user_id']   ?? '');
$book_id  = !empty($_POST['book_id']) ? (int)$_POST['book_id'] : null;
$notes    = trim($_POST['notes']     ?? '');
$whatsapp = trim($_POST['whatsapp']  ?? '');

// validasi dasar
if ($user_id === '' || empty($book_id)) {
    echo json_encode([
        'success' => false,
        'error'   => 'User dan Buku harus diisi.'
    ]);
    exit;
}

if ($whatsapp === '') {
    echo json_encode([
        'success' => false,
        'error'   => 'Nomor WhatsApp harus diisi.'
    ]);
    exit;
}

// opsional: normalisasi nomor WA (0... -> 62..., hapus spasi/dash)
$wa = preg_replace('/\D+/', '', $whatsapp);   // hanya angka
if (strpos($wa, '0') === 0) {
    // 08xxxx -> 628xxxx
    $wa = '62' . substr($wa, 1);
}

// kalau kamu pakai session user/admin, boleh ambil dari sana
// session_start();  // kalau perlu
$action_by = 'user'; // atau $_SESSION['username'] ?? 'user';

try {
    // Mulai transaksi: reservations + reservations_history harus sama-sama berhasil
    $pdo->beginTransaction();

    // ========== 1) INSERT ke tabel utama reservations ==========
    $stmt = $pdo->prepare("
        INSERT INTO reservations (user_id, book_id, whatsapp, notes, created_at)
        VALUES (:user_id, :book_id, :whatsapp, :notes, NOW())
    ");

    $stmt->bindParam(':user_id',  $user_id, PDO::PARAM_STR);
    $stmt->bindParam(':book_id',  $book_id, PDO::PARAM_INT);
    $stmt->bindParam(':whatsapp', $wa,      PDO::PARAM_STR);
    $stmt->bindParam(':notes',    $notes,   PDO::PARAM_STR);

    $stmt->execute();

    // Ambil id reservasi yang baru dibuat
    $reservation_id = $pdo->lastInsertId();

    // ========== 2) INSERT ke tabel history ==========
    $stmtHist = $pdo->prepare("
        INSERT INTO reservations_history
            (reservation_id, user_id, book_id, whatsapp, notes, created_at, action, action_by)
        VALUES
            (:reservation_id, :user_id, :book_id, :whatsapp, :notes, NOW(), :action, :action_by)
    ");

    $action = 'created';

    $stmtHist->execute([
        ':reservation_id' => $reservation_id,
        ':user_id'        => $user_id,
        ':book_id'        => $book_id,
        ':whatsapp'       => $wa,
        ':notes'          => $notes,
        ':action'         => $action,
        ':action_by'      => $action_by
    ]);

    // Semua sukses -> commit
    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Reservasi buku berhasil disimpan dan history tercatat!'
    ]);
} catch (PDOException $e) {
    // kalau ada error, rollback semua
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    echo json_encode([
        'success' => false,
        'error'   => 'Database error: ' . $e->getMessage()
    ]);
}
