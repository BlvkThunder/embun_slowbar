<?php
// admin_delete_reservasi.php
require_once '../api/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: text/html; charset=utf-8');

// Cek CSRF
$csrfPost = $_POST['csrf_token'] ?? '';
$csrfSess = $_SESSION['csrf_token'] ?? '';

if (!$csrfPost || !$csrfSess || !hash_equals($csrfSess, $csrfPost)) {
    die('CSRF token tidak valid.');
}

// Ambil IDs (bisa banyak atau satu)
$idsRaw = trim($_POST['ids'] ?? '');
if ($idsRaw === '') {
    header('Location: reservations_admin.php');
    exit;
}

$ids = array_values(
    array_filter(
        array_map(function($v){
            return ctype_digit(trim($v)) ? (int)$v : null;
        }, explode(',', $idsRaw)),
        fn($v) => !is_null($v)
    )
);

if (empty($ids)) {
    header('Location: reservations_admin.php');
    exit;
}

try {
    $pdo->beginTransaction();

    // 1) Ambil data dari reservations sebelum dihapus
    $in = implode(',', array_fill(0, count($ids), '?'));
    $stmtSel = $pdo->prepare("SELECT * FROM reservations WHERE id IN ($in)");
    $stmtSel->execute($ids);
    $reservations = $stmtSel->fetchAll(PDO::FETCH_ASSOC);

    // 2) Insert history action = deleted
    if ($reservations) {

        $stmtHist = $pdo->prepare("
            INSERT INTO reservations_history
                (reservation_id, user_id, whatsapp, book_id, notes, room, people, date, time, duration, created_at, action, action_by, action_at)
            VALUES
                (:reservation_id, :user_id, :whatsapp, :book_id, :notes, :room, :people, :date, :time, :duration, :created_at, 'deleted', :action_by, NOW())
        ");

        $action_by = $_SESSION['username'] ?? 'admin';

        foreach ($reservations as $r) {
            // Insert DELETE record
            $stmtHist->execute([
                ':reservation_id' => $r['id'],
                ':user_id'        => $r['user_id'],
                ':whatsapp'       => $r['whatsapp'],
                ':book_id'        => $r['book_id'],
                ':notes'          => $r['notes'],
                ':room'           => $r['room'],
                ':people'         => $r['people'],
                ':date'           => $r['date'],
                ':time'           => $r['time'],
                ':duration'       => $r['duration'],
                ':created_at'     => $r['created_at'],
                ':action_by'      => $action_by
            ]);

            // 3) Hapus semua history lama yang bukan deleted
            $stmtCleanup = $pdo->prepare("
                DELETE FROM reservations_history
                WHERE reservation_id = :id
                AND action != 'deleted'
            ");
            $stmtCleanup->execute([':id' => $r['id']]);
        }
    }

    // 4) Hapus data utama
    $stmtDel = $pdo->prepare("DELETE FROM reservations WHERE id IN ($in)");
    $stmtDel->execute($ids);

    $pdo->commit();

    header('Location: reservations_admin.php');
    exit;

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Delete reservations error: '.$e->getMessage());
    echo 'Gagal menghapus data. <a href="reservations_admin.php">Kembali</a>';
}
