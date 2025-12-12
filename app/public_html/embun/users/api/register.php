<?php
require_once __DIR__ . '/config.php';
session_start();    

$register_message = "";

// Check database connection
if (!isset($pdo) || $pdo === null) {
    die("Database connection failed");
}

// Jika sudah login, arahkan ke login.html
if (isset($_SESSION["is_login"])) {
    header("location: ../login/login.html");
    exit;
}

if (isset($_POST["register"])) {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);

    // Validasi input kosong
    if (empty($username) || empty($password)) {
        $register_message = "Username dan password tidak boleh kosong!";
    } else {
        try {
            // Gunakan prepared statement dengan PDO
            $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            
            if ($stmt->execute([$username, $password])) {
                $register_message = "Daftar akun berhasil, silakan login.";
            } else {
                $register_message = "Daftar akun gagal, silakan coba lagi.";
            }
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // Duplicate entry error code
                $register_message = "Username sudah terdaftar, silakan coba lagi.";
            } else {
                $register_message = "Terjadi kesalahan: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register - Kafe Embun</title>
  <link rel="stylesheet" href="../login/style_register.css">
</head>
<body>
  <div class="register-container">
    <h3>Register Here</h3>
    <i><?= htmlspecialchars($register_message) ?></i>
    <form action="register.php" method="POST">
      <input type="text" placeholder="Masukkan username" name="username" required>
      <input type="password" placeholder="Masukkan password" name="password" required>
      <button type="submit" name="register">Register Now</button>
    </form>
    <a href="../login/login.html">Kembali ke Login</a>
  </div>
</body>
</html>
