<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

// Cek apakah sudah login & role-nya admin
if (!isset($_SESSION['username']) || ($_SESSION['role'] ?? '') !== 'admin') {
    echo json_encode(['authenticated' => false]);
    exit;
}

// Jika valid, kirim data user
echo json_encode([
    'authenticated' => true,
    'username' => $_SESSION['username'],
    'role' => $_SESSION['role']
]);
