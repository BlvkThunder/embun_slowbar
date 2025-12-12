<?php
// admin_profile.php
session_start();
require_once '../api/config.php'; // pastikan file config.php berada di lokasi yang sama dan mendefinisikan $pdo

// hanya boleh admin
if (!isset($_SESSION['username']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: login.html');
    exit;
}

// buat CSRF token jika belum ada
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['csrf_token'];

// ambil data admin (ringan)
$sessionUsername = $_SESSION['username'];
$stmt = $pdo->prepare("SELECT id, username, password, avatar_path FROM users WHERE username = :u AND role = 'admin' LIMIT 1");
$stmt->execute([':u' => $sessionUsername]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$admin) {
    // jika tidak ditemukan, logout atau tampilkan error
    echo "Admin tidak ditemukan. Silakan login kembali.";
    exit;
}

// helper: cek apakah string tampak seperti password hash
function looks_like_hash($s) {
    if (!is_string($s) || $s === '') return false;
    // bcrypt: $2y$ or $2a$ or $2b$, argon: $argon2i$ $argon2id$
    return preg_match('/^\$(2y|2a|2b)\$|^\$argon2/', $s) === 1;
}

// helper: verifikasi password (mendukung hash dan plain-text fallback)
function verify_password_mixed($inputPlain, $stored) {
    if (looks_like_hash($stored)) {
        return password_verify($inputPlain, $stored);
    } else {
        // fallback: bandingkan plain text (untuk DB lama yang menyimpan password tanpa hash)
        return hash_equals((string)$stored, (string)$inputPlain);
    }
}

$message = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // cek CSRF
    $post_csrf = $_POST['csrf_token'] ?? '';
    if (empty($post_csrf) || !hash_equals($csrf, $post_csrf)) {
        $errors[] = 'CSRF token tidak valid.';
    } else {
        // ambil input
        $newUsername = trim((string)($_POST['username'] ?? ''));
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';

        if ($newUsername === '') {
            $errors[] = 'Username tidak boleh kosong.';
        } else {
            try {
                // ambil latest password dari DB (fresh)
                $stmt = $pdo->prepare("SELECT id, username, password, avatar_path FROM users WHERE id = :id LIMIT 1");
                $stmt->execute([':id' => $admin['id']]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$row) throw new Exception('Admin tidak ditemukan (fresh).');

                $dbPassword = $row['password'];

                // if username or password change, require current password verification
                $sensitive = ($newUsername !== $row['username']) || ($newPassword !== '');
                if ($sensitive) {
                    if ($currentPassword === '') throw new Exception('Masukkan current password untuk melakukan perubahan username/password.');
                    if (!verify_password_mixed($currentPassword, $dbPassword)) {
                        throw new Exception('Current password salah.');
                    }
                }

                $updates = [];
                $params = [':id' => $admin['id']];

                // username uniqueness
                if ($newUsername !== $row['username']) {
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = :u AND id != :id");
                    $stmt->execute([':u' => $newUsername, ':id' => $admin['id']]);
                    if ($stmt->fetchColumn() > 0) throw new Exception('Username sudah digunakan.');
                    $updates[] = "username = :username";
                    $params[':username'] = $newUsername;
                }

                // password change
                if ($newPassword !== '') {
                    if (strlen($newPassword) < 6) throw new Exception('Password baru minimal 6 karakter.');
                    $hash = password_hash($newPassword, PASSWORD_DEFAULT);
                    $updates[] = "password = :ph";
                    $params[':ph'] = $hash;
                }

                // avatar upload
                if (!empty($_FILES['avatar']['tmp_name'])) {
                    $file = $_FILES['avatar'];
                    if ($file['error'] !== UPLOAD_ERR_OK) {
                        throw new Exception('Kesalahan saat upload file (error code: ' . intval($file['error']) . ').');
                    }
                    if ($file['size'] > 5 * 1024 * 1024) throw new Exception('Ukuran gambar maksimal 5MB.');

                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mime = finfo_file($finfo, $file['tmp_name']);
                    finfo_close($finfo);
                    $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
                    if (!isset($allowed[$mime])) throw new Exception('Tipe file tidak didukung (gunakan jpg/png/webp).');

                    $ext = $allowed[$mime];
                    $uploadDir = __DIR__ . '/uploads/admins';
                    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

                    // buat nama file unik
                    $filename = 'admin_' . $admin['id'] . '_' . time() . '.' . $ext;
                    $dest = $uploadDir . '/' . $filename;
                    if (!move_uploaded_file($file['tmp_name'], $dest)) {
                        throw new Exception('Gagal menyimpan file upload.');
                    }

                    // hapus file lama jika ada
                    if (!empty($row['avatar_path'])) {
                        $old = __DIR__ . '/' . ltrim($row['avatar_path'], '/');
                        if (file_exists($old) && is_file($old)) @unlink($old);
                    }

                    // simpan path relatif
                    $relpath = 'uploads/admins/' . $filename;
                    $updates[] = "avatar_path = :avatar";
                    $params[':avatar'] = $relpath;
                }

                if (!empty($updates)) {
                    // PERHATIAN: pastikan kolom updated_at ada di tabel users,
                    // atau jika tidak mau pakai updated_at, edit baris SQL ini.
                    $sql = 'UPDATE users SET ' . implode(', ', $updates) . ', updated_at = NOW() WHERE id = :id';
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($params);

                    // update session username jika diubah
                    if (isset($params[':username'])) {
                        $_SESSION['username'] = $params[':username'];
                        $sessionUsername = $_SESSION['username'];
                    }

                    // refresh admin data untuk tampilan
                    $stmt = $pdo->prepare("SELECT id, username, avatar_path FROM users WHERE id = :id LIMIT 1");
                    $stmt->execute([':id' => $admin['id']]);
                    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

                    $message = 'Profil berhasil diperbarui.';
                } else {
                    $message = 'Tidak ada perubahan yang disimpan.';
                }

            } catch (Exception $ex) {
                $errors[] = $ex->getMessage();
            }
        }
    }
}

// untuk tampilan avatar: jika tidak ada, gunakan inline SVG data-uri (cepat tanpa koneksi eksternal)
function avatar_url_or_placeholder($avatar_path) {
    if (!empty($avatar_path) && file_exists(__DIR__ . '/' . ltrim($avatar_path, '/'))) {
        return $avatar_path;
    }
    // inline SVG placeholder (data URI)
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="120" height="120"><rect width="100%" height="100%" fill="#e6e6e6"/><text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="20" fill="#9b9b9b">ADMIN</text></svg>';
    $data = 'data:image/svg+xml;utf8,' . rawurlencode($svg);
    return $data;
}

$avatar_for_view = avatar_url_or_placeholder($admin['avatar_path'] ?? null);

// output HTML (single-page)
?><!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Edit Profil Admin — Admin Panel</title>
<link rel="stylesheet" href="../panels/style.css">
<style>
/* Theme variables */
:root{
    --primary-color: #718a5d;
    --secondary-color: #8fbc8f;
    --accent-color: #f5deb3;
    --light-color: #f9f9f9;
    --dark-color: #2c3e50;
    --text-color: #333;
    --muted: #758089;
    --card-shadow: 0 8px 30px rgba(15,15,15,0.06);
}

/* Reset / base */
*{box-sizing:border-box}
body{
    font-family: Inter, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
    background: linear-gradient(180deg, rgba(113,138,93,0.06) 0%, rgba(255,255,255,0.02) 100%), var(--light-color);
    color: var(--text-color);
    margin: 0;
    padding: 28px;
    -webkit-font-smoothing:antialiased;
}

/* Card */
.card{
    max-width: 820px;
    margin: 28px auto;
    background: linear-gradient(180deg,#ffffff,#fbfbfb);
    border-radius: 14px;
    padding: 26px;
    box-shadow: var(--card-shadow);
    border: 1px solid rgba(16,24,40,0.04);
}

/* Header */
h1{margin:0;font-size:20px;color:var(--dark-color)}
.lead{color:var(--muted);margin-top:8px;margin-bottom:18px}

/* Layout */
.row{display:flex;gap:24px;align-items:flex-start;flex-wrap:wrap}
.col-left{width:160px;flex:0 0 160px}
.col-right{flex:1;min-width:260px}

/* Avatar */
.avatar{width:128px;height:128px;border-radius:16px;overflow:hidden;border:6px solid var(--light-color);background:linear-gradient(180deg,var(--accent-color),#fff);box-shadow:0 6px 18px rgba(0,0,0,0.06);display:inline-block}
.avatar img{width:100%;height:100%;object-fit:cover;display:block}

/* Inputs */
label{display:block;font-weight:700;color:var(--dark-color);margin-top:12px}
.input, input[type="text"], input[type="password"], input[type="file"]{
    width:100%;
    padding:10px 12px;
    border-radius:10px;
    border:1px solid rgba(16,24,40,0.06);
    background: #fff;
    font-size:14px;
    color:var(--text-color);
}

/* Small helper text */
.small{font-size:13px;color:var(--muted);margin-top:8px}

/* Buttons */
.form-actions{margin-top:18px;display:flex;gap:10px;flex-wrap:wrap}
.btn{
    background: linear-gradient(180deg,var(--primary-color),var(--secondary-color));
    color: #fff;
    padding:10px 14px;
    border-radius:10px;
    border:none;
    cursor:pointer;
    font-weight:700;
    letter-spacing:0.2px;
    box-shadow: 0 6px 18px rgba(113,138,93,0.12);
}
.btn.secondary{
    background: #fff;
    color:var(--dark-color);
    border:1px solid rgba(16,24,40,0.06);
    box-shadow:none;
}

/* Messages */
.msg{padding:12px;border-radius:10px;margin-bottom:12px}
.msg.ok{background: rgba(121, 210, 164, 0.12); color: #0f6b3a; border:1px solid rgba(121,210,164,0.25)}
.msg.err{background: rgba(255, 200, 200, 0.12); color: #8b1a1a; border:1px solid rgba(255,120,120,0.18)}

/* password toggle */
.pw-toggle{
    margin-left:8px;background: #fff;border:1px solid rgba(16,24,40,0.06);
    padding:8px;border-radius:8px;cursor:pointer;font-size:14px;
}

/* Responsive */
@media (max-width: 760px){
    .row{flex-direction:column}
    .col-left{width:100%;flex:0 0 auto;display:flex;justify-content:center}
    .avatar{width:120px;height:120px}
}

/* subtle focus */
.input:focus{outline:none;box-shadow:0 6px 20px rgba(113,138,93,0.08);border-color: rgba(113,138,93,0.2)}
</style>
</head>
<body>
<div class="card">
    <h1>Edit Profil Admin</h1>
    <div class="lead">Ubah username, password, atau foto profil Anda. (Perubahan username/password memerlukan current password untuk verifikasi.)</div>

    <?php if (!empty($message)): ?>
        <div class="msg ok"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <?php foreach ($errors as $err): ?>
            <div class="msg err"><?= htmlspecialchars($err) ?></div>
        <?php endforeach; ?>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" id="profile-form" novalidate>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">

        <div class="row">
            <div class="col-left">
                <div class="avatar" id="avatar-box" title="Klik untuk lihat/ganti">
                    <img id="avatar-img" src="<?= htmlspecialchars($avatar_for_view) ?>" alt="Avatar">
                </div>
                <div class="small">Ukuran maks 5MB. Format: jpg / png / webp.</div>
                <label for="avatar" style="margin-top:12px">Ganti Foto</label>
                <input type="file" name="avatar" id="avatar" accept="image/png,image/jpeg,image/webp">
            </div>
            <div class="col-right">
                <label for="username">Username</label>
                <input class="input" type="text" id="username" name="username" value="<?= htmlspecialchars($admin['username']) ?>" required>

                <div style="display:flex;gap:12px;margin-top:12px;align-items:center">
                    <div style="flex:1">
                        <label for="current_password">Current Password</label>
                        <div style="display:flex;gap:8px;align-items:center">
                            <input class="input" type="password" id="current_password" name="current_password" placeholder="Masukkan password sekarang (diperlukan untuk perubahan sensitif)">
                            <button type="button" class="pw-toggle" data-target="current_password" title="Tampilkan / sembunyikan">👁</button>
                        </div>
                    </div>
                    <div style="width:240px">
                        <label for="new_password">New Password</label>
                        <div style="display:flex;gap:8px;align-items:center">
                            <input class="input" type="password" id="new_password" name="new_password" placeholder="Biarkan kosong jika tidak ingin ganti">
                            <button type="button" class="pw-toggle" data-target="new_password" title="Tampilkan / sembunyikan">👁</button>
                        </div>
                    </div>
                </div>

                <div class="small" style="margin-top:10px">Jika password di database masih tersimpan dalam plain text (legacy), sistem akan mendukung verifikasi plain-text (hanya untuk kompatibilitas). Saat mengganti password, password baru akan disimpan dalam bentuk hash yang aman.</div>

                <div class="form-actions" style="margin-top:18px">
                    <button class="btn" type="submit">Simpan Perubahan</button>
                    <a href="../panels/admin.php" class="btn secondary" style="text-decoration:none;display:inline-block;padding:10px 14px;">Kembali</a>
                </div>

            </div>
        </div>
    </form>
</div>

<script>
// show/hide password toggles
document.querySelectorAll('.pw-toggle').forEach(function(btn){
    btn.addEventListener('click', function(){
        var targetId = btn.getAttribute('data-target');
        var input = document.getElementById(targetId);
        if (!input) return;
        if (input.type === 'password') {
            input.type = 'text';
            btn.textContent = '🙈';
        } else {
            input.type = 'password';
            btn.textContent = '👁';
        }
    });
});

// preview avatar client-side
document.getElementById('avatar').addEventListener('change', function(e){
    var f = this.files && this.files[0];
    if (!f) return;
    if (!f.type.startsWith('image/')) { alert('Pilih file gambar.'); return; }
    var reader = new FileReader();
    reader.onload = function(ev){
        var img = document.getElementById('avatar-img');
        img.src = ev.target.result;
    };
    reader.readAsDataURL(f);
});

// small client-side UX: confirm before submit sensitive change
document.getElementById('profile-form').addEventListener('submit', function(e){
    var usernameInput = document.getElementById('username').value.trim();
    var currentUsername = <?= json_encode($admin['username']) ?>;
    var newPassword = document.getElementById('new_password').value;
    if (usernameInput !== currentUsername) {
        if (!confirm('Anda akan mengubah username dari \"' + currentUsername + '\" menjadi \"' + usernameInput + '\". Lanjutkan?')) {
            e.preventDefault();
            return false;
        }
    }
    if (newPassword && newPassword.length > 0) {
        if (newPassword.length < 6) {
            alert('Password baru minimal 6 karakter.');
            e.preventDefault();
            return false;
        }
        if (!confirm('Anda akan mengganti password akun admin. Lanjutkan?')) {
            e.preventDefault();
            return false;
        }
    }
});
</script>
</body>
</html>
