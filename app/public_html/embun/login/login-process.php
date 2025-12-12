<?php
// login-process.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

ob_start();
header('Content-Type: application/json; charset=utf-8');

// Aktifkan error untuk debugging (matikan di production jika perlu)
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../users/api/config.php';

// Pastikan koneksi database valid
if (!isset($pdo) || $pdo === null) {
    if (ob_get_length()) ob_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Koneksi database gagal.']);
    exit;
}

// Hanya izinkan metode POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if (ob_get_length()) ob_clean();
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Metode request tidak valid. Gunakan POST.']);
    exit;
}

// Ambil data input
$username = isset($_POST['username']) ? trim((string)$_POST['username']) : '';
$password = isset($_POST['password']) ? (string)$_POST['password'] : '';

if ($username === '' || $password === '') {
    if (ob_get_length()) ob_clean();
    echo json_encode(['success' => false, 'error' => 'Username dan password harus diisi!']);
    exit;
}

// Helper: check whether stored value looks like a password hash
function looks_like_hash($s) {
    if (!is_string($s) || $s === '') return false;
    // bcrypt variants or argon
    return preg_match('/^\$(2y|2a|2b)\$|^\$argon2/', $s) === 1;
}

try {
    // Ambil data user
    $stmt = $pdo->prepare('SELECT id, username, password, role FROM users WHERE username = :username LIMIT 1');
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        if (ob_get_length()) ob_clean();
        echo json_encode(['success' => false, 'error' => 'Username atau password salah.']);
        exit;
    }

    $stored = (string)($user['password'] ?? '');
    $ok = false;

    if (looks_like_hash($stored)) {
        // Normal flow: DB has hashed password
        if (password_verify($password, $stored)) {
            $ok = true;
            // Jika perlu rehash (algoritma/cost berubah), update hash tanpa menyentuh updated_at
            if (password_needs_rehash($stored, PASSWORD_DEFAULT)) {
                $newhash = password_hash($password, PASSWORD_DEFAULT);
                $upd = $pdo->prepare('UPDATE users SET password = :ph WHERE id = :id');
                $upd->execute([':ph' => $newhash, ':id' => $user['id']]);
            }
        }
    } else {
        // Legacy: stored value not hashed (plain-text)
        // Gunakan hash_equals untuk timing-attack safe comparison
        if (hash_equals($stored, $password)) {
            $ok = true;
            // Migrasi: replace plain text with secure hash immediately
            $newhash = password_hash($password, PASSWORD_DEFAULT);
            $upd = $pdo->prepare('UPDATE users SET password = :ph WHERE id = :id');
            $upd->execute([':ph' => $newhash, ':id' => $user['id']]);
        }
    }

    if (!$ok) {
        if (ob_get_length()) ob_clean();
        echo json_encode(['success' => false, 'error' => 'Username atau password salah.']);
        exit;
    }

    // Sukses login: set session, regenerate id
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'] ?? 'user';

    // Tentukan redirect berdasarkan role (ubah sesuai kebutuhan)
    $redirectPage = ($user['role'] === 'admin') ? '../admin/panels/admin.php' : '../users/panels/Index.php';

    if (ob_get_length()) ob_clean();
    echo json_encode([
        'success' => true,
        'message' => 'Login berhasil.',
        'redirect' => $redirectPage
    ]);
    exit;

} catch (Throwable $e) {
    if (ob_get_length()) ob_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Terjadi kesalahan server.',
        'debug' => $e->getMessage()
    ]);
    exit;
}
