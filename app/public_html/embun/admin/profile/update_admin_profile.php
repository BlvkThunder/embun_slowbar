<?php
// update_admin_profile.php
session_start();
header('Content-Type: application/json; charset=utf-8');

// hanya admin
if (!isset($_SESSION['username']) || ($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    echo json_encode(['ok'=>false,'error'=>'Forbidden']);
    exit;
}

require_once __DIR__ . '../api/config.php'; // pastikan $pdo ada

// CSRF token check
$csrf = $_POST['csrf_token'] ?? '';
if (empty($_SESSION['csrf_token']) || $csrf !== $_SESSION['csrf_token']) {
    http_response_code(403);
    echo json_encode(['ok'=>false,'error'=>'Invalid CSRF token']);
    exit;
}

// form inputs
$sessionUsername = $_SESSION['username'];
$newUsername = trim($_POST['username'] ?? '');
$currentPassword = $_POST['current_password'] ?? '';
$newPassword = $_POST['new_password'] ?? '';

// basic validation
if ($newUsername === '') {
    echo json_encode(['ok'=>false,'error'=>'Username required']); exit;
}

try {
    // ambil record admin sekarang
    $stmt = $pdo->prepare('SELECT id, username, password FROM users WHERE username = :u AND role = :r LIMIT 1');
    $stmt->execute([':u' => $sessionUsername, ':r' => 'admin']);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$admin) {
        echo json_encode(['ok'=>false,'error'=>'Admin not found']); exit;
    }

    $updates = [];
    $params = [];

    // jika ganti username atau password, butuh current password verifikasi
    $sensitiveChange = ($newPassword !== '') || ($newUsername !== $admin['username']);
    if ($sensitiveChange) {
        if (empty($currentPassword)) {
            echo json_encode(['ok'=>false,'error'=>'Current password required to change username/password']); exit;
        }
        if (!password_verify($currentPassword, $admin['password'])) {
            echo json_encode(['ok'=>false,'error'=>'Current password incorrect']); exit;
        }
    }

    // username unique check jika berubah
    if ($newUsername !== $admin['username']) {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = :u AND id != :id');
        $stmt->execute([':u' => $newUsername, ':id' => $admin['id']]);
        if ($stmt->fetchColumn() > 0) {
            echo json_encode(['ok'=>false,'error'=>'Username already taken']); exit;
        }
        $updates[] = 'username = :newusername';
        $params[':newusername'] = $newUsername;
    }

    // password change
    if ($newPassword !== '') {
        if (strlen($newPassword) < 6) {
            echo json_encode(['ok'=>false,'error'=>'New password must be at least 6 characters']); exit;
        }
        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $updates[] = 'password = :ph';
        $params[':ph'] = $newHash;
    }

    // avatar upload handling
    $avatarSavedPath = null;
    if (isset($_FILES['avatar']) && is_uploaded_file($_FILES['avatar']['tmp_name'])) {
        $file = $_FILES['avatar'];
        if ($file['size'] > 5 * 1024 * 1024) {
            echo json_encode(['ok'=>false,'error'=>'Avatar too large (max 5MB)']); exit;
        }
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
        if (!isset($allowed[$mime])) {
            echo json_encode(['ok'=>false,'error'=>'Unsupported file type (allowed: jpg/png/webp)']); exit;
        }
        $ext = $allowed[$mime];

        $uploadDir = __DIR__ . '/uploads/admins';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $filename = 'admin_' . $admin['id'] . '_' . time() . '.' . $ext;
        $dest = $uploadDir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            echo json_encode(['ok'=>false,'error'=>'Failed to save avatar']); exit;
        }

        // delete old avatar if exists (optional)
        $stmt = $pdo->prepare('SELECT avatar_path FROM users WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $admin['id']]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!empty($row['avatar_path'])) {
            $old = __DIR__ . '/' . ltrim($row['avatar_path'], '/');
            if (file_exists($old) && is_file($old)) @unlink($old);
        }

        $avatarSavedPath = 'uploads/admins/' . $filename;
        $updates[] = 'avatar_path = :avatar';
        $params[':avatar'] = $avatarSavedPath;
    }

    if (empty($updates)) {
        echo json_encode(['ok'=>false,'error'=>'No changes detected']); exit;
    }

    $params[':id'] = $admin['id'];
    $sql = 'UPDATE users SET ' . implode(', ', $updates) . ', updated_at = NOW() WHERE id = :id';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    // if username changed, update session
    if (isset($params[':newusername'])) {
        $_SESSION['username'] = $params[':newusername'];
    }

    echo json_encode(['ok'=>true,'message'=>'Profile updated','avatar' => $avatarSavedPath]);
    exit;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok'=>false,'error'=>$e->getMessage()]);
    exit;
}
