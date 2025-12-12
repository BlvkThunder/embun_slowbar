<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// amankan akses session
$user_id   = $_SESSION['user_id']  ?? '';
$user_name = $_SESSION['username'] ?? 'Guest';

// helper sederhana
function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// base url dinamis (http://localhost/embun) -> aman kalau dipindah folder
$scheme    = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host      = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptDir = rtrim(str_replace('\\','/', dirname($_SERVER['SCRIPT_NAME'])), '/');
$baseUrl   = rtrim("$scheme://$host$scriptDir", '/');

// normalisasi path gambar dari DB ataupun hardcode:
// - jika sudah absolute (http...): pakai apa adanya
// - jika host-relative (diawali '/'): pakai apa adanya
// - jika relatif ('uploads/...'): prefix dengan $baseUrl
function asset_url(string $val, string $baseUrl): string {
    if (preg_match('#^https?://#i', $val)) return $val;
    if (strlen($val) && $val[0] === '/') return $val;
    return $baseUrl . '/' . ltrim($val, '/');
}

// Jika belum login, redirect ke halaman login
if (!isset($_SESSION['username'])) {
    header('Location: ../../login/login.html');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kafe Embun - Tempat Nyaman untuk Bersantai</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ========= CSS VARIABLES ========= */
:root {
    --primary-color: #718a5d;
    --secondary-color: #8fbc8f;
    --accent-color: #f5deb3;
    --light-color: #f9f9f9;
    --dark-color: #2c3e50;
    --text-color: #333;
    --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    --transition: all 0.3s ease;
    --body-bg: var(--light-color);
    --card-bg: #ffffff;
}

/* ========= DARK MODE OVERRIDE ========= */
body.dark {
    --primary-color: #a3c985;
    --secondary-color: #6a9f6a;
    --accent-color: #caa56a;
    --light-color: #111827;
    --dark-color: #020617;
    --text-color: #e5e7eb;
    --body-bg: #020617;
    --card-bg: #111827;
}

/* ========= RESET & BASE ========= */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    line-height: 1.6;
    color: var(--text-color);
    background-color: var(--body-bg);
}

a {
    text-decoration: none;
    color: inherit;
}

ul {
    list-style: none;
}

.container {
    width: 90%;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 15px;
}

.section {
    padding: 80px 0;
}

.section-title {
    text-align: center;
    margin-bottom: 50px;
    position: relative;
}

.section-title h2 {
    font-size: 2.5rem;
    color: var(--primary-color);
    margin-bottom: 15px;
}

.section-title::after {
    content: '';
    position: absolute;
    bottom: -10px;
    left: 50%;
    transform: translateX(-50%);
    width: 80px;
    height: 3px;
    background-color: var(--secondary-color);
}

.btn {
    display: inline-block;
    padding: 12px 25px;
    background-color: var(--primary-color);
    color: white;
    border: none;
    border-radius: 30px;
    cursor: pointer;
    font-weight: 600;
    transition: var(--transition);
    text-align: center;
}

.btn:hover {
    background-color: var(--secondary-color);
    transform: translateY(-3px);
    box-shadow: var(--shadow);
}

/* ========= NAVBAR / HEADER ========= */
header {
    background-color: var(--card-bg);
    box-shadow: var(--shadow);
    position: fixed;
    width: 100%;
    top: 0;
    z-index: 1000;
}

.navbar {
    display: flex;
    align-items: center;
    padding: 15px 0;
    position: relative;
    max-width: 100%;
}

.logo {
    width: 150px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
}

.logo-image {
    height: 40px;
    width: auto;
    transition: var(--transition);
    /* Biar logo EMBUN lebih terang di navbar gelap */
    filter: brightness(2.5) contrast(1.45);
}


.nav-links {
    margin-left: auto;
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 25px;
}

.nav-links li {
    margin: 0;
}

.nav-links a {
    font-weight: 600;
    font-size: 0.95rem;
    transition: var(--transition);
    position: relative;
    padding: 5px 0;
}

.nav-links a.active {
    color: var(--primary-color);
    font-weight: 700;
}

.nav-links a.active::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 2px;
    background-color: var(--primary-color);
}

/* Theme toggle */
.theme-toggle {
    margin-left: 10px;
    border: none;
    background: transparent;
    cursor: pointer;
    font-size: 1.1rem;
    color: var(--primary-color);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 6px;
    border-radius: 999px;
    transition: var(--transition);
}

.theme-toggle:hover {
    background-color: rgba(0, 0, 0, 0.05);
}

/* Hamburger */
.hamburger {
    display: none;
    cursor: pointer;
    font-size: 1.5rem;
    color: var(--primary-color);
    margin-left: 10px;
}

/* ========= HERO ========= */
.hero {
    height: 100vh;
    background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)),
                url('https://images.unsplash.com/photo-1554118811-1e0d58224f24?ixlib=rb-4.0.3&auto=format&fit=crop&w=1470&q=80');
    background-size: cover;
    background-position: center;
    display: flex;
    align-items: center;
    color: white;
    text-align: center;
    padding-top: 80px;
    margin-top: -80px;
    position: relative;
    overflow: hidden;
}

.hero-content {
    max-width: 800px;
    margin: 0 auto;
}

.hero h1 {
    font-size: 3.5rem;
    margin-bottom: 20px;
}

.hero p {
    font-size: 1.2rem;
    margin-bottom: 30px;
}

/* ========= ABOUT ========= */
.about-content {
    display: flex;
    align-items: center;
    gap: 50px;
}

.about-text {
    flex: 1;
    text-align: justify;
}

.about-text h3 {
    font-size: 1.8rem;
    margin-bottom: 20px;
    color: var(--primary-color);
}

.about-text p {
    margin-bottom: 15px;
}

.about-image {
    flex: 1;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: var(--shadow);
}

.about-image img {
    width: 100%;
    height: auto;
    display: block;
    transition: var(--transition);
}

.about-image:hover img {
    transform: scale(1.05);
}

/* ========= LAZY LOADING ========= */
img.lazy {
    opacity: 0;
    transition: opacity 0.3s ease-in-out;
    filter: blur(5px);
    background-color: #f6f6f6;
    background-image:
        linear-gradient(45deg, #f0f0f0 25%, transparent 25%, transparent 75%, #f0f0f0 75%, #f0f0f0),
        linear-gradient(45deg, #f0f0f0 25%, transparent 25%, transparent 75%, #f0f0f0 75%, #f0f0f0);
    background-size: 20px 20px;
    background-position: 0 0, 10px 10px;
}

img.lazy.loaded {
    opacity: 1;
    filter: blur(0);
}

/* ========= BEST SELLER CAROUSEL ========= */
.best-seller {
    background-color: var(--light-color);
    padding-bottom: 30px;
    margin-bottom: 40px;
}

.carousel-container {
    position: relative;
    max-width: 750px;
    margin: 0 auto;
    overflow: hidden;
    border-radius: 15px;
    box-shadow: var(--shadow);
    background-color: var(--card-bg);
}

.carousel-slides {
    display: flex;
    transition: transform 0.5s ease-in-out;
}

.carousel-slide {
    min-width: 100%;
    padding: 20px;
    box-sizing: border-box;
}

.best-seller-card {
    background-color: var(--card-bg);
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    transition: var(--transition);
    display: flex;
    flex-direction: column;
    height: 100%;
}

.best-seller-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
}

.best-seller-image {
    height: 0;
    padding-bottom: 56.25%;
    overflow: hidden;
    position: relative;
}

.best-seller-image img {
    position: absolute;
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: var(--transition);
}

.best-seller-card:hover .best-seller-image img {
    transform: scale(1.05);
}

.best-seller-info {
    padding: 20px;
    flex-grow: 1;
    display: flex;
    flex-direction: column;
    position: relative;
}

.best-seller-info h3 {
    font-size: 1.4rem;
    margin-bottom: 10px;
    color: var(--primary-color);
}

.best-seller-info p {
    color: #666;
    margin-bottom: 15px;
    flex-grow: 1;
}

body.dark .best-seller-info p {
    color: #9ca3af;
}

.best-seller-price {
    font-size: 1.2rem;
    font-weight: 700;
    color: var(--primary-color);
    margin-bottom: 10px;
}

.best-seller-badge {
    display: inline-flex;
    align-items: center;
    background-color: var(--accent-color);
    color: var(--dark-color);
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
}

.best-seller-badge i {
    margin-right: 5px;
    color: #ffc107;
}

.carousel-btn {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background-color: rgba(255, 255, 255, 0.8);
    border: none;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: var(--transition);
    z-index: 10;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
}

.carousel-btn:hover {
    background-color: #ffffff;
    transform: translateY(-50%) scale(1.1);
}

.carousel-btn-prev {
    left: 15px;
}

.carousel-btn-next {
    right: 15px;
}

.carousel-indicators {
    display: flex;
    justify-content: center;
    margin-top: 5px;
    position: relative;
    padding-bottom: 20px;
}

.indicator {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background-color: #ddd;
    margin: 0 5px;
    cursor: pointer;
    transition: var(--transition);
}

.indicator.active {
    background-color: var(--primary-color);
    transform: scale(1.2);
}

/* ========= MENU ========= */
.menu {
    background-color: #f5f5f5;
}

body.dark .menu {
    background-color: #020617;
}

.menu-categories {
    display: flex;
    justify-content: center;
    margin-bottom: 40px;
    flex-wrap: wrap;
}

.category-btn {
    padding: 10px 20px;
    margin: 0 10px 10px;
    margin-top: 10px;
    background-color: var(--card-bg);
    border: 1px solid #ddd;
    border-radius: 30px;
    cursor: pointer;
    transition: var(--transition);
}

.category-btn.active,
.category-btn:hover {
    background-color: var(--primary-color);
    color: white;
}

.menu-items {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 30px;
    margin-top: 24px;      /* 👈 jarak dari kategori menu */
    padding-top: 4px;      /* sedikit napas biar nggak nempel */
}


.menu-item {
    background-color: var(--card-bg);
    border-radius: 10px;
    overflow: hidden;
    box-shadow: var(--shadow);
    transition: var(--transition);
}

.menu-item:hover {
    transform: translateY(-10px);
}

.menu-item-image {
    height: 0;
    padding-bottom: 56.25%;
    overflow: hidden;
    position: relative;
}

.menu-item-image img {
    position: absolute;
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: var(--transition);
}

.menu-item-content {
    padding: 20px;
}

.menu-item-title {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
}

.menu-item-title h3 {
    font-size: 1.2rem;
}

.menu-item-price {
    color: var(--primary-color);
    font-weight: 600;
}

.menu-checkout {
    text-align: center;
    margin-top: 60px;
}

.btn-checkout {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    background: linear-gradient(135deg, #3d9970, #2e7d57);
    color: #fff;
    font-size: 22px;
    font-weight: 700;
    padding: 18px 48px;
    border-radius: 12px;
    text-decoration: none;
    box-shadow: 0 6px 15px rgba(0,0,0,0.2);
    transition: all 0.3s ease;
}

.btn-checkout:hover {
    background: linear-gradient(135deg, #2e7d57, #3d9970);
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.25);
    text-decoration: none;
}

.btn-checkout i {
    font-size: 26px;
}

/* ========= LIBRARY ========= */
.library-filter {
    display: flex;
    justify-content: center;
    margin-bottom: 40px;
    flex-wrap: wrap;
}

.filter-btn {
    padding: 8px 15px;
    margin: 0 10px 10px;
    background-color: var(--card-bg);
    border: 1px solid #ddd;
    border-radius: 30px;
    cursor: pointer;
    transition: var(--transition);
}

.filter-btn.active,
.filter-btn:hover {
    background-color: var(--primary-color);
    color: white;
}

.books-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 30px;
}

.book-card {
    background-color: var(--card-bg);
    border-radius: 10px;
    overflow: hidden;
    box-shadow: var(--shadow);
    transition: var(--transition);
}

.book-card:hover {
    transform: translateY(-5px);
}

.book-cover {
    height: 0;
    padding-bottom: 133.33%;
    overflow: hidden;
    position: relative;
}

.book-cover img {
    position: absolute;
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: var(--transition);
}

.book-card:hover .book-cover img {
    transform: scale(1.05);
}

.book-info {
    padding: 20px;
}

.book-title {
    font-size: 1.1rem;
    margin-bottom: 5px;
}

.book-author {
    color: #666;
    font-size: 0.9rem;
    margin-bottom: 10px;
}

body.dark .book-author {
    color: #9ca3af;
}

.book-genre {
    display: inline-block;
    padding: 3px 8px;
    background-color: var(--accent-color);
    border-radius: 15px;
    font-size: 0.8rem;
}

/* ========= BOARDGAMES ========= */
.boardgames-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 30px;
}

.boardgame-card {
    background-color: var(--card-bg);
    border-radius: 10px;
    overflow: hidden;
    box-shadow: var(--shadow);
    transition: var(--transition);
}

.boardgame-card:hover {
    transform: translateY(-5px);
}

.boardgame-image {
    height: 0;
    padding-bottom: 75%;
    overflow: hidden;
    position: relative;
}

.boardgame-image img {
    position: absolute;
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: var(--transition);
}

.boardgame-card:hover .boardgame-image img {
    transform: scale(1.05);
}

.boardgame-info {
    padding: 20px;
}

.boardgame-title {
    font-size: 1.2rem;
    margin-bottom: 10px;
}

.boardgame-details {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
    font-size: 0.9rem;
    color: #666;
}

body.dark .boardgame-details {
    color: #9ca3af;
}

.boardgame-description {
    font-size: 0.9rem;
    margin-bottom: 15px;
}

/* ========= RESERVATION ========= */
.reservation {
    background-color: #f5f5f5;        /* light mode */
}

body.dark .reservation {
    background-color: #020617;        /* dark mode */
}

.reservation-content {
    display: flex;
    gap: 50px;
    flex-wrap: wrap;
    align-items: flex-start;          /* ⬅️ form nggak ikut “ketarik” setinggi kolom kiri */
}

/* ===== KARTU RUANGAN ===== */
.room-options {
    flex: 1;
    min-width: 300px;
    max-width: 600px;
}

.room-card {
    background-color: var(--card-bg, #0b1120);
    border-radius: 10px;
    overflow: hidden;
    box-shadow: var(--shadow);
    margin-bottom: 30px;
    transition: var(--transition);
}

body:not(.dark) .room-card {
    background-color: #ffffff;
}

.room-card:hover {
    transform: translateY(-5px);
}

.room-image {
    height: 0;
    padding-bottom: 56.25%; /* 16:9 */
    overflow: hidden;
    position: relative;
}

.room-image img {
    position: absolute;
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: var(--transition);
}

.room-card:hover .room-image img {
    transform: scale(1.05);
}

.room-info {
    padding: 20px;
}

.room-title {
    font-size: 1.3rem;
    margin-bottom: 10px;
    color: var(--primary-color);
}

.room-capacity {
    display: flex;
    align-items: center;
    margin-bottom: 10px;
    color: #666;
}

body.dark .room-capacity {
    color: #9ca3af;
}

.room-capacity i {
    margin-right: 5px;
}

.room-price {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--primary-color);
    margin-bottom: 15px;
}

/* ===== FORM RESERVASI ===== */
.reservation-form-container {
    background: #f9fafb;              /* light default */
    color: #111827;
    border-radius: 12px;
    padding: 24px 20px;
    box-shadow: var(--shadow);
    border: 1px solid #111827;        /* border hitam di light mode */

    max-width: 420px;
    width: 100%;
    align-self: flex-start;           /* ⬅️ tinggi mengikuti isi (dinamis) */
    box-sizing: border-box;
}

/* dark mode untuk box form */
body.dark .reservation-form-container {
    background: #020617;
    color: #e5e7eb;
    border-color: #f9fafb;            /* border putih di dark mode */
}

/* semua input/select di dalam form reservasi */
.reservation-form-container .form-control {
    width: 100%;
    padding: 12px 15px;
    border-radius: 6px;
    font-size: 1rem;
    transition: var(--transition);
    border: 1px solid #d1d5db;
    background-color: #ffffff;
    color: #111827;
    box-sizing: border-box;
}

/* dark mode input/select */
body.dark .reservation-form-container .form-control {
    background-color: #020617;
    color: #e5e7eb;
    border-color: #4b5563;
}

/* fokus di dalam form reservasi */
.reservation-form-container .form-control:focus {
    border-color: var(--accent-color, #a3e635);
    outline: none;
    box-shadow: 0 0 0 2px rgba(163, 230, 53, 0.3);
}

/* placeholder */
.reservation-form-container .form-control::placeholder {
    color: #9ca3af;
}

/* styling khusus select di form reservasi */
.reservation-form-container select.form-control {
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
}

/* opsi select – light mode */
body:not(.dark) .reservation-form-container select.form-control option {
    background-color: #ffffff;
    color: #111827;
}

/* opsi select – dark mode */
body.dark .reservation-form-container select.form-control option {
    background-color: #020617;
    color: #f9fafb;
}

/* grup & row */
.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
}

.form-row {
    display: flex;
    gap: 15px;
}

.form-row .form-group {
    flex: 1;
}
/* ==== Checkbox Sesi Reservasi ==== */
.session-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 4px;
}

.session-pill {
    display: inline-flex;
    align-items: center;
    padding: 6px 12px;
    border-radius: 999px;
    border: 1px solid #d1d5db;
    background-color: #ffffff;
    cursor: pointer;
    font-size: 0.9rem;
    transition: all 0.2s ease;
}

body.dark .session-pill {
    background-color: #020617;
    border-color: #4b5563;
}

.session-pill input[type="checkbox"] {
    margin-right: 6px;
}

.session-pill:hover {
    box-shadow: 0 0 0 1px rgba(113, 138, 93, 0.5);
    transform: translateY(-1px);
}

/* sesi yang sudah dibooking */
.session-pill.session-unavailable {
    opacity: 0.4;
    cursor: not-allowed;
    background-color: #e5e7eb;
}

body.dark .session-pill.session-unavailable {
    background-color: #374151;
}

.session-pill.session-unavailable input[type="checkbox"] {
    pointer-events: none;
}
.session-checkboxes {
    display: flex;
    flex-wrap: wrap;
    gap: 8px 16px;
}

.session-item {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 10px;
    border-radius: 6px;
    border: 1px solid #d1d5db;
    background-color: #ffffff;
    cursor: pointer;
    font-size: 0.9rem;
    transition: background-color 0.2s ease, transform 0.1s ease;
}

body.dark .session-item {
    background-color: #020617;
    border-color: #4b5563;
}

.session-item span {
    font-weight: 700;           /* ⬅️ yang available tebal */
}

.session-item input {
    cursor: pointer;
}

/* Hover untuk yang available */
.session-item:not(.disabled):hover {
    background-color: #e5f2ff;
    transform: translateY(-1px);
}


/* ========= FOOTER ========= */
footer {
    background-color: var(--dark-color);
    color: white;
    padding: 60px 0 30px;
}

.footer-content {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 40px;
    margin-bottom: 40px;
    align-items: start;
}

.footer-column {
    text-align: justify;
    font-weight: 400;
}

.footer-column h3 {
    font-size: 1.3rem;
    margin-bottom: 20px;
    position: relative;
    padding-bottom: 10px;
}

.footer-column h3::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 115px;
    height: 2px;
    background-color: var(--secondary-color);
}

.footer-links li {
    margin-bottom: 10px;
}

.footer-links a {
    transition: var(--transition);
}

.footer-links a:hover {
    color: var(--secondary-color);
    padding-left: 5px;
}

.contact-info li {
    display: flex;
    align-items: flex-start;
    margin-bottom: 15px;
}

.contact-info i {
    margin-right: 10px;
    color: var(--secondary-color);
    margin-top: 5px;
}

.social-links {
    display: flex;
    gap: 15px;
    margin-top: 20px;
}

.social-links a {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    background-color: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    transition: var(--transition);
}

.social-links a:hover {
    background-color: var(--secondary-color);
    transform: translateY(-3px);
}

.footer-logos {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 15px;
}

.footer-center-logo {
    height: 80px;
    width: auto;
    margin-bottom: 10px;
}

.footer-type-logo {
    height: 40px;
    width: auto;
}

.footer-column-center {
    background-color: #ffffff;
    border-radius: 15px;
    padding: 30px 20px;
    margin: -20px 10px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    text-align: center;
    position: relative;
    z-index: 1;
}

body.dark .footer-column-center {
    background-color: #020617;
}

.copyright {
    text-align: center;
    padding-top: 30px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    font-size: 0.9rem;
    color: #aaa;
}

/* ========= SPA SECTIONS ========= */
html, body {
    overflow-x: hidden;
    overflow-y: auto;
    max-width: 100%;
    width: 100%;
    height: 100%;
}

.main-container {
    position: relative;
    overflow-x: hidden;
    overflow-y: visible;
    width: 100%;
    min-height: 100vh;
    height: auto;
}

.content-section {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    min-height: 100vh;
    overflow-x: hidden;
    overflow-y: auto;
    transform: translateX(100%);
    transition: transform 0.5s ease-in-out;
    padding-top: 80px;
}

.content-section.active {
    transform: translateX(0);
    position: relative;
    z-index: 1;
    overflow-y: visible;
    height: auto;
}

.content-section:not(.active) {
    height: 0;
    overflow: hidden;
}

.content-section.prev {
    transform: translateX(-100%);
}

/* ========= RESPONSIVE ========= */
@media (max-width: 1200px) {
    .logo {
        width: 120px;
    }
    .logo-image {
        height: 30px;
    }
    .nav-links {
        font-size: 0.9rem;
        gap: 15px;
    }
    .nav-links a {
        white-space: nowrap;
    }
}

@media (max-width: 992px) {
    header {
        height: auto;
        min-height: 70px;
    }

    .navbar {
        flex-wrap: wrap;
        padding: 10px 0;
    }

    .nav-links {
        display: none;
        width: 100%;
        flex-direction: column;
        align-items: flex-start;
        background: var(--card-bg);
        padding: 10px 20px 15px;
        gap: 15px;
        margin: 10px 0 0 0;
        border-top: 1px solid #eee;
    }

    .nav-links.active {
        display: flex;
    }

    .hamburger {
        display: block;
        margin-left: 10px;
    }

    .hero h1 {
        font-size: 2.8rem;
    }

    .about-content,
    .reservation-content {
        flex-direction: column;
    }

    .about-image,
    .about-text {
        flex: none;
        width: 100%;
    }
}

@media (max-width: 768px) {
    .hero h1 {
        font-size: 2.2rem;
    }

    .section-title h2 {
        font-size: 2rem;
    }

    .content-section {
        padding-top: 70px;
    }

    .hero {
        padding-top: 70px;
        margin-top: -70px;
    }

    .menu-items,
    .books-grid,
    .boardgames-grid {
        grid-template-columns: 1fr;
    }

    .form-row {
        flex-direction: column;
        gap: 0;
    }

    .footer-column-center {
        margin: 0 auto 20px;
        max-width: 300px;
        grid-column: 1 / -1;
    }

    .footer-content {
        grid-template-columns: 1fr;
        text-align: center;
    }
}

@media (max-width: 576px) {
    .hero h1 {
        font-size: 1.8rem;
    }

    .logo-image {
        height: 25px;
    }

    .footer-center-logo {
        height: 50px;
    }

    .footer-type-logo {
        height: 25px;
    }
}

    </style>
</head>
<body>
<div class="main-container">
    <!-- Header -->
    <header>
        <div class="container">
            <nav class="navbar">
                <a href="#" class="logo" data-section="home">
                    <img src="../../uploads/website/TypeEmbun.png" alt="Embun Slowbar" class="logo-image">
                </a>

                <!-- Center logo kalau mau (bisa dihapus kalau tidak dipakai)
                <div class="header-center">
                    <img src="../../uploads/website/LogoEmbun.png" alt="Embun Logo" class="header-center-logo">
                </div>
                -->

                <ul class="nav-links">
                    <li><a href="#" data-section="home">Home</a></li>
                    <li><a href="#" data-section="about">About</a></li>
                    <li><a href="#" data-section="menu">Menu</a></li>
                    <li><a href="#" data-section="books">Books</a></li>
                    <li><a href="#" data-section="boardgames">Boardgames</a></li>
                    <li><a href="#" data-section="reservation">Reservasi</a></li>
                    <li><a href="../../login/login.html">Logout</a></li>
                    <li><a href="../checkout/checkout.php">Keranjang Embun ☕</a></li>
                </ul>

                <!-- Toggle Light / Dark Mode -->
                <button type="button" id="theme-toggle" class="theme-toggle" aria-label="Toggle dark mode">
                    <i class="fas fa-moon"></i>
                </button>

                <div class="hamburger">
                    <i class="fas fa-bars"></i>
                </div>
            </nav>
        </div>
    </header>

    <!-- Home Section -->
    <section id="home-section" class="content-section active">
        <div class="hero">
            <div class="container">
                <div class="hero-content">
                    <h1>Embun Slowbar</h1>
                    <p>Best budget friendly cafe in Araya</p>
                    <a href="#" class="btn" data-section="reservation">Reservasi Sekarang</a>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about-section" class="content-section">
        <div class="section">
            <div class="container">
                <div class="section-title">
                    <h2>Tentang Kafe Embun</h2>
                </div>
                <div class="about-content">
                    <div class="about-text">
                        <h3>Ruang Nyaman untuk Setiap Momen</h3>
                        <p>Kafe Embun hadir sebagai tempat berkumpul yang hangat dan nyaman bagi para pecinta kopi, buku, dan boardgame. Dengan konsep perpustakaan kafe, kami menyediakan lingkungan yang tenang untuk membaca dan bekerja, serta ruang yang menyenangkan untuk bersosialisasi.</p>
                        <p>Kami menyajikan berbagai pilihan kopi spesialti dari berbagai daerah di Indonesia, dipadukan dengan menu makanan ringan yang lezat. Perpustakaan kami memiliki koleksi buku yang beragam, dari fiksi hingga non-fiksi, yang dapat dinikmati selama Anda berada di kafe.</p>
                        <p>Untuk hiburan, kami menyediakan berbagai boardgame yang dapat dimainkan bersama teman atau keluarga. Kami juga memiliki ruang khusus di lantai dua dan tiga yang dapat dipesan untuk acara privat, meeting, atau gathering.</p>
                    </div>
                    <div class="about-image">
                        <img data-src="https://images.unsplash.com/photo-1506744038136-46273834b3fb?ixlib=rb-4.0.3&auto=format&fit=crop&w=1470&q=80" 
                             alt="Tentang Kafe Embun">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Menu Section -->
    <section id="menu-section" class="content-section">
        <div class="section menu">
            <div class="container">
                <div class="section-title">
                    <h2>Menu Kami</h2>
                </div>

                <div class="best-seller best-seller-in-menu">
                    <div class="section-title">
                        <h3>Menu Best Seller</h3>
                    </div>
                    <div class="carousel-container">
                        <div class="carousel-slides" id="best-seller-slides"></div>
                        <button class="carousel-btn carousel-btn-prev">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <button class="carousel-btn carousel-btn-next">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                        <div class="carousel-indicators" id="carousel-indicators"></div>
                    </div>
                </div>

                <div class="menu-categories" id="menu-categories"></div>
                <div class="menu-items" id="menu-items"></div>

                <div class="menu-checkout">
                    <a href="../checkout/checkout.php" class="btn-checkout">
                        <i class="fas fa-shopping-cart"></i> Lanjut ke Pembayaran
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Books Section -->
    <section id="books-section" class="content-section">
        <div class="section">
            <div class="container">
                <div class="section-title">
                    <h2>Perpustakaan</h2>
                </div>
                <div class="library-filter" id="library-filter"></div>
                <div class="books-grid" id="books-grid"></div>
            </div>
        </div>
    </section>

    <!-- Boardgames Section -->
    <section id="boardgames-section" class="content-section">
        <div class="section">
            <div class="container">
                <div class="section-title">
                    <h2>Boardgame Collection</h2>
                </div>
                <div class="boardgames-grid" id="boardgames-grid"></div>
            </div>
        </div>
    </section>

    <!-- Reservation Section -->
    <section id="reservation-section" class="content-section">
        <div class="section reservation">
            <div class="container">
                <div class="section-title">
                    <h2>Reservasi Ruangan & Buku</h2>
                </div>
                <div class="reservation-content">
                    <div class="room-options">
                        <!-- Akan di-replace oleh renderRooms() dari DB -->
                        <div class="room-card">
                            <div class="room-image">
                                <img data-src="https://images.unsplash.com/photo-1497366216548-37526070297c?ixlib=rb-4.0.3&auto=format&fit=crop&w=1469&q=80" 
                                     alt="Ruangan Lantai 2" 
                                     class="lazy">
                            </div>
                            <div class="room-info">
                                <h3 class="room-title">Lantai 2 (Mini Library)</h3>
                                <div class="room-capacity">
                                    <i class="fas fa-users"></i>
                                    <span>Kapasitas: 8 orang</span>
                                </div>
                                <p>Ruangan nyaman dengan suasana tenang, cocok untuk meeting kecil, belajar kelompok, atau berkumpul dengan teman serta membaca buku.</p>
                                <div class="room-price">Terdapat fasilitas AC & printer</div>
                            </div>
                        </div>
                        <div class="room-card">
                            <div class="room-image">
                                <img data-src="https://images.unsplash.com/photo-1497366811353-6870744d04b2?ixlib=rb-4.0.3&auto=format&fit=crop&w=1469&q=80" 
                                     alt="Ruangan Lantai 3" 
                                     class="lazy">
                            </div>                        
                            <div class="room-info">
                                <h3 class="room-title">Lantai 3 (Board Game)</h3>
                                <div class="room-capacity">
                                    <i class="fas fa-users"></i>
                                    <span>Kapasitas: 4 orang</span>
                                </div>
                                <p>Ruangan nyaman dengan suasana cozy, tersedia berbagai boardgame yang dapat dipinjam secara gratis</p>
                                <div class="room-price">Terdapat fasilitas AC</div>                           
                            </div>
                        </div>
                    </div>

                    <!-- Form Reservasi -->
                    <div class="reservation-form-container">
                        <h3>Form Reservasi</h3>

                        <form id="reservation-form">
                          <div class="form-group">
                              <label for="reservation_type">Jenis Reservasi</label>
                              <select id="reservation_type" name="reservation_type" class="form-control" required>
                                  <option value="">Pilih Jenis Reservasi</option>
                                  <option value="ruangan">Reservasi Ruangan</option>
                                  <option value="buku">Reservasi Buku</option>
                                  <option value="both">Reservasi Ruangan + Buku</option>
                              </select>
                          </div>

                          <!-- Nama -->
                          <div class="form-group">
                              <label for="user_id">Nama Lengkap</label>
                              <input type="text"
                                    id="user_id"
                                    name="user_id"
                                    class="form-control"
                                    value="<?= e($user_name) ?>"
                                    required>
                          </div>

                          <!-- Nomor WA -->
                          <div class="form-group">
                              <label for="whatsapp">Nomor WhatsApp</label>
                              <input type="tel"
                                    id="whatsapp"
                                    name="whatsapp"
                                    class="form-control"
                                    placeholder="Contoh: 0895xxxx atau 6289xxxx"
                                    required>
                          </div>

                          <!-- RUANGAN -->
                          <div id="form-ruangan" style="display:none;">
                              <div class="form-row">
                                  <div class="form-group">
                                      <label for="room">Ruangan</label>
                                      <select id="room" name="room" class="form-control">
                                          <option value="">Pilih Ruangan</option>
                                      </select>
                                  </div>
                                  <div class="form-group">
                                      <label for="people">Jumlah Orang</label>
                                      <input type="number" id="people" name="people" class="form-control" min="1">
                                  </div>
                              </div>

                              <div class="form-group">
                                  <label for="date">Tanggal</label>
                                  <input type="date" id="date" name="date" class="form-control">
                              </div>

                              <!-- Hidden field untuk dikirim ke server -->
                              <input type="hidden" id="time" name="time">
                              <input type="hidden" id="duration" name="duration">

                              <div class="form-group">
                                  <label>Sesi Waktu (maksimal 3 sesi)</label>
                                  <div id="sessionCheckboxes" class="session-checkboxes">
                                      <label class="session-item">
                                          <input type="checkbox" class="session-input" value="10:00-12:00" data-start="10:00">
                                          <span>10:00 - 12:00</span>
                                      </label>
                                      <label class="session-item">
                                          <input type="checkbox" class="session-input" value="12:00-14:00" data-start="12:00">
                                          <span>12:00 - 14:00</span>
                                      </label>
                                      <label class="session-item">
                                          <input type="checkbox" class="session-input" value="14:00-16:00" data-start="14:00">
                                          <span>14:00 - 16:00</span>
                                      </label>
                                      <label class="session-item">
                                          <input type="checkbox" class="session-input" value="16:00-18:00" data-start="16:00">
                                          <span>16:00 - 18:00</span>
                                      </label>
                                      <label class="session-item">
                                          <input type="checkbox" class="session-input" value="18:00-20:00" data-start="18:00">
                                          <span>18:00 - 20:00</span>
                                      </label>
                                      <label class="session-item">
                                          <input type="checkbox" class="session-input" value="20:00-22:00" data-start="20:00">
                                          <span>20:00 - 22:00</span>
                                      </label>
                                      <label class="session-item">
                                          <input type="checkbox" class="session-input" value="22:00-24:00" data-start="22:00">
                                          <span>22:00 - 24:00</span>
                                      </label>
                                  </div>
                                  <small class="text-muted" id="session-helper">
                                      Pilih minimal 1 sesi, maksimal 3 sesi. Sesi yang sudah lewat + 1 sesi setelah jam sekarang akan otomatis tidak bisa dipilih.
                                  </small>
                              </div>

                              <div class="form-group">
                                  <label for="duration">Durasi (jam)</label>
                                  <input type="number"
                                        id="duration"
                                        name="duration"
                                        class="form-control"
                                        readonly>
                              </div>

                              <div class="form-group">
                                  <label for="notes">Catatan Khusus</label>
                                  <textarea id="notes" name="notes" class="form-control" rows="4"></textarea>
                              </div>
                          </div>

                          <!-- BUKU -->
                          <div id="form-buku" style="display:none;">
                              <div class="form-group">
                                  <label for="category_id">Pilih Kategori Buku</label>
                                  <select id="category_id" name="category_id" class="form-control">
                                      <option value="">Pilih Kategori</option>
                                  </select>
                              </div>
                              <div class="form-group">
                                  <label for="book">Pilih Buku</label>
                                  <select id="book" name="book_id" class="form-control">
                                      <option value="">Pilih Buku</option>
                                  </select>
                              </div>
                          </div>

                          <button type="submit" class="btn" style="width:100%;">Kirim Reservasi</button>
                      </form>


                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<footer>
    <div class="container">
        <div class="footer-content">
            <div class="footer-column">
                <h3>Kafe Embun</h3>
                <p>Tempat yang sempurna untuk menikmati kopi berkualitas, membaca buku favorit, bermain boardgame, dan bersantai dengan teman-teman.</p>
            </div>
            <div class="footer-column footer-column-center">
                <div class="footer-logos">
                    <img src="../../uploads/website/LogoEmbun.png" alt="Embun Logo" class="footer-center-logo">
                    <img src="../../uploads/website/TypeEmbun.png" alt="Embun Slowbar" class="footer-type-logo">
                </div>
            </div>
            <div class="footer-column">
                <h3>Kontak Kami</h3>
                <ul class="contact-info">
                    <li>
                        <i class="fas fa-map-marker-alt"></i>
                        <span><a href="https://maps.app.goo.gl/pWuCtqHSwsNs5Jzy8" target="_blank">M-House D12, Genitri, Tirtomoyo, Kec. Pakis, Kabupaten Malang, Jawa Timur</a></span>
                    </li>
                    <li>
                        <i class="fas fa-phone"></i>
                        <span><a href="https://wa.me/62895339931433" target="_blank">+62 895-3399-31433</a></span>
                    </li>
                    <li>
                        <i class="fab fa-instagram"></i>
                        <span><a href="https://instagram.com/embunslowbar.mlg?" target="_blank">embunslowbar.mlg</a></span>
                    </li>
                    <li>
                        <i class="fas fa-clock"></i>
                        <span>Senin - Minggu: 10.00 - 00.00</span>
                    </li>
                </ul>
            </div>
        </div>
        <div class="copyright">
            <p>&copy;2025 Embun Slowbar. All rights reserved.</p>
        </div>
    </div>
</footer>

<script>
// ====== THEME (LIGHT / DARK MODE) ======
(function initTheme() {
  const saved = localStorage.getItem('theme');
  if (saved === 'dark') {
    document.body.classList.add('dark');
  }
})();

document.addEventListener('DOMContentLoaded', function() {
  const toggle = document.getElementById('theme-toggle');
  const icon   = toggle ? toggle.querySelector('i') : null;

  function syncIcon() {
    if (!icon) return;
    if (document.body.classList.contains('dark')) {
      icon.classList.remove('fa-moon');
      icon.classList.add('fa-sun');
    } else {
      icon.classList.remove('fa-sun');
      icon.classList.add('fa-moon');
    }
  }

  syncIcon();

  if (toggle) {
    toggle.addEventListener('click', () => {
      document.body.classList.toggle('dark');
      const mode = document.body.classList.contains('dark') ? 'dark' : 'light';
      localStorage.setItem('theme', mode);
      syncIcon();
    });
  }
});

// === Helpers URL Gambar ===
const ORIGIN = window.location.origin;
const APP_BASE = window.location.pathname.replace(/\/[^\/]*$/, '');

const PATHS = {
  menu:      'uploads/menu',
  books:     'uploads/books',
  boardgame: 'uploads/boardgames',
  website:   'uploads/website',
};

function normalizeAssetUrl(val) {
  if (!val) return '';
  if (/^https?:\/\//i.test(val)) return val;
  if (val[0] === '/') return val;
  return `${ORIGIN}${APP_BASE}/${val.replace(/^\/+/, '')}`;
}
function buildImagePath(raw, type) {
  if (!raw) return '';
  if (/^https?:\/\//i.test(raw) || raw[0] === '/' || raw.includes('/')) return raw;
  const base = PATHS[type] || PATHS.website;
  return `${base}/${raw}`;
}
function imgUrl(raw, type) {
  return normalizeAssetUrl(buildImagePath(raw, type));
}

// === Toast Helper ===
function showToast(message, type = 'info') {
  const existing = document.querySelector('.embun-toast');
  if (existing) existing.remove();

  const toast = document.createElement('div');
  toast.className = `embun-toast embun-toast-${type}`;
  toast.textContent = message;

  Object.assign(toast.style, {
    position: 'fixed',
    bottom: '20px',
    right: '20px',
    zIndex: 9999,
    padding: '10px 16px',
    borderRadius: '6px',
    backgroundColor:
      type === 'success' ? '#2e7d32' :
      type === 'error'   ? '#c62828' :
      type === 'warning' ? '#f9a825' :
                           '#333',
    color: '#fff',
    boxShadow: '0 2px 8px rgba(0,0,0,0.25)',
    opacity: '0',
    transform: 'translateY(10px)',
    transition: 'opacity .2s ease, transform .2s ease',
    fontSize: '14px'
  });

  document.body.appendChild(toast);
  requestAnimationFrame(() => {
    toast.style.opacity = '1';
    toast.style.transform = 'translateY(0)';
  });
  setTimeout(() => {
    toast.style.opacity = '0';
    toast.style.transform = 'translateY(10px)';
    setTimeout(() => toast.remove(), 200);
  }, 3000);
}

// === Global SPA State ===
let currentSection = 'home';
let sectionsData = {
  menu: { loaded: false },
  books: { loaded: false },
  boardgames: { loaded: false },
  rooms: { loaded: false }
};

// === Navbar / Mobile Menu ===
const hamburger = document.querySelector('.hamburger');
const navLinks = document.querySelector('.nav-links');

if (hamburger && navLinks) {
  hamburger.addEventListener('click', () => {
    navLinks.classList.toggle('active');
    const icon = hamburger.querySelector('i');
    if (navLinks.classList.contains('active')) {
      icon.classList.remove('fa-bars');
      icon.classList.add('fa-times');
    } else {
      icon.classList.remove('fa-times');
      icon.classList.add('fa-bars');
    }
  });

  document.querySelectorAll('.nav-links a').forEach(link => {
    link.addEventListener('click', () => {
      navLinks.classList.remove('active');
      const icon = hamburger.querySelector('i');
      icon.classList.remove('fa-times');
      icon.classList.add('fa-bars');
    });
  });

  document.addEventListener('click', (e) => {
    if (!document.querySelector('.navbar').contains(e.target) && navLinks.classList.contains('active')) {
      navLinks.classList.remove('active');
      const icon = hamburger.querySelector('i');
      icon.classList.remove('fa-times');
      icon.classList.add('fa-bars');
    }
  });
}

// === Show Section (SPA) ===
function showSection(sectionId) {
  document.querySelectorAll('.content-section').forEach(section => {
    section.classList.remove('active', 'prev');
    section.style.transform = 'translateX(100%)';
    section.style.position = 'absolute';
    section.style.zIndex = '0';
  });

  const targetSection = document.getElementById(`${sectionId}-section`);
  if (targetSection) {
    targetSection.classList.add('active');
    targetSection.style.transform = 'translateX(0)';
    targetSection.style.position = 'relative';
    targetSection.style.zIndex = '1';
    currentSection = sectionId;
    loadSectionData(sectionId);
  }
  updateActiveNav(sectionId);
  window.scrollTo(0, 0);
  document.body.style.overflow = 'hidden';
  setTimeout(() => {
    document.body.style.overflow = '';
    document.documentElement.style.overflow = '';
  }, 50);
}

function updateActiveNav(activeSection) {
  document.querySelectorAll('.nav-links a, .logo[data-section]').forEach(link => {
    if (link.getAttribute('data-section') === activeSection) {
      link.classList.add('active');
    } else {
      link.classList.remove('active');
    }
  });
}

// === Initial Load ===
document.addEventListener('DOMContentLoaded', function() {
  showSection('home');
  setTimeout(ensureActiveSectionVisible, 100);

  document.querySelectorAll('[data-section]').forEach(link => {
    link.addEventListener('click', function(e) {
      e.preventDefault();
      const sectionId = this.getAttribute('data-section');
      showSection(sectionId);
      if (navLinks && navLinks.classList.contains('active')) {
        navLinks.classList.remove('active');
        const icon = hamburger.querySelector('i');
        icon.classList.remove('fa-times');
        icon.classList.add('fa-bars');
      }
    });
  });

  loadWebsiteContent();
  loadInitialData();
  initLazyLoading();
});

async function loadInitialData() {
  try {
    await Promise.all([
      loadRoomData(),
      loadWebsiteContent()
    ]);
  } catch (err) {
    console.error('Error loading initial data:', err);
  }
}

function loadSectionData(sectionId) {
  switch(sectionId) {
    case 'menu':
      if (!sectionsData.menu.loaded) {
        loadMenuData();
        sectionsData.menu.loaded = true;
      }
      break;
    case 'books':
      if (!sectionsData.books.loaded) {
        loadBooksData();
        sectionsData.books.loaded = true;
      }
      break;
    case 'boardgames':
      if (!sectionsData.boardgames.loaded) {
        loadBoardgamesData();
        sectionsData.boardgames.loaded = true;
      }
      break;
    case 'reservation':
      if (!sectionsData.rooms.loaded) {
        loadRoomData();
        sectionsData.rooms.loaded = true;
      }
      break;
  }
}

// === Filter Menu & Books ===
document.addEventListener('click', function(e) {
  if (e.target.classList && e.target.classList.contains('category-btn')) {
    document.querySelectorAll('.category-btn').forEach(btn => btn.classList.remove('active'));
    e.target.classList.add('active');
    filterMenuItems(e.target.getAttribute('data-category'));
  }
  if (e.target.classList && e.target.classList.contains('filter-btn')) {
    document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
    e.target.classList.add('active');
    filterBooks(e.target.getAttribute('data-filter'));
  }
});

function filterMenuItems(category) {
  const menuItems = document.querySelectorAll('.menu-item');
  menuItems.forEach(item => {
    if (category === 'all' || item.getAttribute('data-category') === category) {
      item.style.display = 'block';
      setTimeout(() => {
        item.style.opacity = '1';
        item.style.transform = 'translateY(0)';
      }, 50);
    } else {
      item.style.opacity = '0';
      item.style.transform = 'translateY(20px)';
      setTimeout(() => { item.style.display = 'none'; }, 300);
    }
  });
}

function filterBooks(filter) {
  const bookCards = document.querySelectorAll('.book-card');
  bookCards.forEach(book => {
    if (filter === 'all' || book.getAttribute('data-category') === filter) {
      book.style.display = 'block';
      setTimeout(() => {
        book.style.opacity = '1';
        book.style.transform = 'translateY(0)';
      }, 50);
    } else {
      book.style.opacity = '0';
      book.style.transform = 'translateY(20px)';
      setTimeout(() => { book.style.display = 'none'; }, 300);
    }
  });
}

// === Lazy Loading Gambar ===
function initLazyLoading() {
  const lazyImages = [].slice.call(document.querySelectorAll('img[data-src]'));
  if ('IntersectionObserver' in window) {
    const lazyImageObserver = new IntersectionObserver(function(entries, observer) {
      entries.forEach(function(entry) {
        if (entry.isIntersecting) {
          const lazyImage = entry.target;
          lazyImage.src = lazyImage.dataset.src;
          lazyImage.classList.remove('lazy');
          lazyImageObserver.unobserve(lazyImage);
          lazyImage.onload = function() {
            lazyImage.classList.add('loaded');
          };
        }
      });
    });
    lazyImages.forEach(function(lazyImage) {
      lazyImageObserver.observe(lazyImage);
    });
  } else {
    lazyImages.forEach(function(lazyImage) {
      lazyImage.src = lazyImage.dataset.src;
    });
  }
}

// === Website Content (hero, about) ===
async function loadWebsiteContent() {
  try {
    const res = await fetch('../api/get_website_content.php');
    const data = await res.json();
    if (data.error) {
      console.error('Error loading website content:', data.error);
      return;
    }
    if (data.success && data.content) {
      renderWebsiteContent(data.content);
    }
  } catch (err) {
    console.error('Error loading website content:', err);
  }
}
function renderWebsiteContent(content) {
  const heroSection = document.querySelector('.hero');
  if (content.hero_background && heroSection) {
    heroSection.style.backgroundImage =
      `linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('${content.hero_background}')`;
  }
  const heroSubtitle = document.querySelector('.hero p');
  if (content.hero_subtitle && heroSubtitle) {
    heroSubtitle.textContent = content.hero_subtitle;
  }
  const aboutTitle = document.querySelector('.about-text h3');
  if (content.about_title && aboutTitle) {
    aboutTitle.textContent = content.about_title;
  }
  const aboutContent = document.querySelector('.about-text');
  if (content.about_content && aboutContent) {
    aboutContent.innerHTML = content.about_content;
  }
  const aboutImage = document.querySelector('.about-image img');
  if (content.about_image && aboutImage) {
    aboutImage.setAttribute('data-src', content.about_image);
    aboutImage.setAttribute('alt', 'Interior Kafe Embun - Gambar dinamis');
    initLazyLoading();
  }
}

// === Menu ===
async function loadMenuData() {
  try {
    const response = await fetch('../api/get_menu.php');
    const data = await response.json();
    if (data.error) {
      console.error('Error loading menu:', data.error);
      return;
    }
    renderBestSellers(data.best_sellers);
    renderMenuCategories(data.categories);
    renderMenuItems(data.menu_items);
    setTimeout(() => filterMenuItems('coffee'), 100);
  } catch (err) {
    console.error('Error loading menu data:', err);
  }
}
function renderBestSellers(bestSellers) {
  const carouselSlides = document.getElementById('best-seller-slides');
  const indicators = document.getElementById('carousel-indicators');
  if (!carouselSlides || !indicators) return;
  carouselSlides.innerHTML = '';
  indicators.innerHTML = '';

  if (!bestSellers || bestSellers.length === 0) {
    carouselSlides.innerHTML = '<div class="carousel-slide"><p>Tidak ada best seller</p></div>';
    return;
  }

  bestSellers.forEach((item, index) => {
    const slide = document.createElement('div');
    slide.className = 'carousel-slide';
    slide.innerHTML = `
      <div class="best-seller-card">
        <div class="best-seller-image">
          <img data-src="${item.image_url || 'https://images.unsplash.com/photo-1559925393-8be0ec4767c8?auto=format&fit=crop&w=500&q=80'}"
               alt="${item.name}" class="lazy">
        </div>
        <div class="best-seller-info">
          <span class="best-seller-badge"><i class="fas fa-star"></i> Best Seller</span>
          <h3>${item.name}</h3>
          <p>${item.description || ''}</p>
          <div class="best-seller-price">Rp ${parseInt(item.price, 10).toLocaleString('id-ID')}</div>
        </div>
      </div>`;
    carouselSlides.appendChild(slide);

    const indicator = document.createElement('div');
    indicator.className = `indicator ${index === 0 ? 'active' : ''}`;
    indicator.setAttribute('data-slide', index);
    indicators.appendChild(indicator);
  });

  initCarousel();
  initLazyLoading();
}
function renderMenuCategories(categories) {
  const categoriesContainer = document.getElementById('menu-categories');
  if (!categoriesContainer) return;
  categoriesContainer.innerHTML = '';

  const allButton = document.createElement('button');
  allButton.className = 'category-btn';
  allButton.setAttribute('data-category', 'all');
  allButton.textContent = 'All';
  categoriesContainer.appendChild(allButton);

  categories.forEach(category => {
    const button = document.createElement('button');
    button.className = 'category-btn';
    button.setAttribute('data-category', category.slug);
    button.textContent = category.name;
    if (category.slug === 'coffee') button.classList.add('active');
    categoriesContainer.appendChild(button);
  });
}
function renderMenuItems(menuItems) {
  const menuItemsContainer = document.getElementById('menu-items');
  if (!menuItemsContainer) return;
  menuItemsContainer.innerHTML = '';

  menuItems.forEach(item => {
    const menuItem = document.createElement('div');
    menuItem.className = 'menu-item';
    menuItem.setAttribute('data-category', item.category_slug);
    menuItem.style.opacity = '0';
    menuItem.style.transform = 'translateY(20px)';
    menuItem.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
    menuItem.innerHTML = `
      <div class="menu-item-image">
        <img data-src="${item.image_url || 'https://images.unsplash.com/photo-1559925393-8be0ec4767c8?auto=format&fit=crop&w=500&q=80'}"
             alt="${item.name}" class="lazy">
      </div>
      <div class="menu-item-content">
        <div class="menu-item-title">
          <h3>${item.name}</h3>
          <span class="menu-item-price">Rp ${parseInt(item.price, 10).toLocaleString('id-ID')}</span>
        </div>
        <p>${item.description || ''}</p>
      </div>`;
    menuItemsContainer.appendChild(menuItem);
  });
  initLazyLoading();
}

// === Books ===
async function loadBooksData() {
  try {
    const res = await fetch('../api/get_books.php');
    const data = await res.json();
    if (data.error) {
      console.error('Error loading books:', data.error);
      return;
    }
    renderBookFilters(data.categories);
    renderBooks(data.books);
    setTimeout(() => filterBooks('fiksi'), 100);
  } catch (err) {
    console.error('Error loading books data:', err);
  }
}
function renderBookFilters(categories) {
  const filterContainer = document.getElementById('library-filter');
  if (!filterContainer) return;
  filterContainer.innerHTML = '';

  const allButton = document.createElement('button');
  allButton.className = 'filter-btn';
  allButton.setAttribute('data-filter', 'all');
  allButton.textContent = 'All';
  filterContainer.appendChild(allButton);

  categories.forEach(category => {
    const button = document.createElement('button');
    button.className = 'filter-btn';
    button.setAttribute('data-filter', category.slug);
    button.textContent = category.name;
    if (category.slug === 'fiksi') button.classList.add('active');
    filterContainer.appendChild(button);
  });
}
function renderBooks(books) {
  const booksGrid = document.getElementById('books-grid');
  if (!booksGrid) return;
  booksGrid.innerHTML = '';

  books.forEach(book => {
    const bookCard = document.createElement('div');
    bookCard.className = 'book-card';
    bookCard.setAttribute('data-category', book.category_slug);
    bookCard.style.opacity = '0';
    bookCard.style.transform = 'translateY(20px)';
    bookCard.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
    bookCard.innerHTML = `
      <div class="book-cover">
        <img data-src="${book.cover_image || 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?auto=format&fit=crop&w=500&q=80'}"
             alt="${book.title}" class="lazy">
      </div>
      <div class="book-info">
        <h3 class="book-title">${book.title}</h3>
        <p class="book-author">${book.author || 'Unknown Author'}</p>
        <span class="book-genre">${book.category_name}</span>
      </div>`;
    booksGrid.appendChild(bookCard);
  });
  initLazyLoading();
}

// === Boardgames ===
async function loadBoardgamesData() {
  try {
    const res = await fetch('../api/get_boardgame.php');
    const boardgames = await res.json();
    if (boardgames.error) {
      console.error('Error loading boardgames:', boardgames.error);
      return;
    }
    renderBoardgames(boardgames);
  } catch (err) {
    console.error('Error loading boardgames data:', err);
  }
}
function renderBoardgames(boardgames) {
  const boardgamesGrid = document.getElementById('boardgames-grid');
  if (!boardgamesGrid) return;
  boardgamesGrid.innerHTML = '';

  boardgames.forEach(game => {
    const gameCard = document.createElement('div');
    gameCard.className = 'boardgame-card';
    gameCard.style.opacity = '0';
    gameCard.style.transform = 'translateY(20px)';
    gameCard.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
    gameCard.innerHTML = `
      <div class="boardgame-image">
        <img data-src="${game.image_url || 'https://images.unsplash.com/photo-1611371809842-7e89f81f44e0?auto=format&fit=crop&w=500&q=80'}"
             alt="${game.name}" class="lazy">
      </div>
      <div class="boardgame-info">
        <h3 class="boardgame-title">${game.name}</h3>
        <div class="boardgame-details">
          <span><i class="fas fa-users"></i> ${game.min_players}-${game.max_players} Players</span>
          <span><i class="fas fa-clock"></i> ${game.play_time} mins</span>
        </div>
        <p class="boardgame-description">${game.description || ''}</p>
      </div>`;
    boardgamesGrid.appendChild(gameCard);
  });

  setTimeout(() => {
    document.querySelectorAll('.boardgame-card').forEach((card, index) => {
      setTimeout(() => {
        card.style.opacity = '1';
        card.style.transform = 'translateY(0)';
      }, index * 100);
    });
  }, 100);

  initLazyLoading();
}

// === Reservasi (Ruangan & Buku) ===
document.addEventListener('DOMContentLoaded', function() {
  const reservationForm    = document.getElementById('reservation-form');
  if (!reservationForm) return;

  const dateInput          = document.getElementById('date');
  const durationInput      = document.getElementById('duration');   // hidden / input durasi (jam)
  const timeInput          = document.getElementById('time');       // hidden untuk kirim label sesi
  const categorySelect     = document.getElementById('category_id');
  const bookSelect         = document.getElementById('book');
  const typeSelect         = document.getElementById('reservation_type');
  const roomSelect         = document.getElementById('room');
  const waInput            = document.getElementById('whatsapp');
  const sessionCheckboxes  = Array.from(document.querySelectorAll('.session-input'));
  const sessionHelper      = document.getElementById('session-helper');

  // ====== Set min/max date (hari ini s/d +7 hari) ======
  function updateMinMaxDate() {
    if (!dateInput) return;
    const jakartaTime = new Date().toLocaleString('en-US', { timeZone: 'Asia/Jakarta' });
    const today = new Date(jakartaTime);
    const yyyy = today.getFullYear();
    const mm   = String(today.getMonth() + 1).padStart(2, '0');
    const dd   = String(today.getDate()).padStart(2, '0');
    const minStr = `${yyyy}-${mm}-${dd}`;

    const maxDate = new Date(today);
    maxDate.setDate(maxDate.getDate() + 7);
    const yyyy2 = maxDate.getFullYear();
    const mm2   = String(maxDate.getMonth() + 1).padStart(2, '0');
    const dd2   = String(maxDate.getDate()).padStart(2, '0');
    const maxStr = `${yyyy2}-${mm2}-${dd2}`;

    dateInput.min = minStr;
    dateInput.max = maxStr;

    // simpan tanggal hari ini global
    window.embunToday = minStr;
  }

  // ====== Reset sesi ke kondisi dasar ======
  function resetSessionsBase() {
    sessionCheckboxes.forEach(cb => {
      cb.disabled = false;
      cb.checked  = false;
      const item = cb.closest('.session-item');
      if (item) item.classList.remove('disabled');
    });
    if (durationInput) durationInput.value = '';
    if (timeInput)     timeInput.value     = '';
    if (sessionHelper) {
      sessionHelper.textContent =
        'Pilih minimal 1 sesi, maksimal 3 sesi. Sesi yang sudah lewat + 1 sesi setelah jam sekarang akan otomatis tidak bisa dipilih.';
    }
  }

  // ====== Disable sesi yang sudah "lewat" berdasarkan jam sekarang (WIB) ======
  function applyTimeBasedAvailability() {
    if (!dateInput || !dateInput.value) return;
    if (typeof window.embunToday !== 'string') return;

    const selectedDate = dateInput.value; // YYYY-MM-DD
    if (selectedDate !== window.embunToday) {
      // kalau bukan hari ini → nggak ada blokir berdasarkan jam sekarang
      return;
    }

    const nowJakarta   = new Date(new Date().toLocaleString('en-US', { timeZone: 'Asia/Jakarta' }));
    const nowMinutes   = nowJakarta.getHours() * 60 + nowJakarta.getMinutes();
    const cutoffMinutes = nowMinutes + 120; // minimal 1 sesi setelah jam sekarang (2 jam)

    sessionCheckboxes.forEach(cb => {
      const startStr = cb.dataset.start; // "10:00"
      if (!startStr) return;

      const [h, m] = startStr.split(':').map(Number);
      const startMinutes = h * 60 + m;

      if (startMinutes <= cutoffMinutes) {
        // sesi ini sudah lewat / terlalu dekat → disable & abu-abu
        cb.checked  = false;
        cb.disabled = true;
        const item = cb.closest('.session-item');
        if (item) item.classList.add('disabled');
      }
    });
  }

  // ====== Disable sesi yang sudah dibooking dari DB (get_unavailable_sessions.php) ======
  async function applyDbUnavailableSessions() {
    if (!roomSelect || !dateInput) return;

    const roomId = roomSelect.value;
    const dateVal = dateInput.value;
    if (!roomId || !dateVal) return;

    try {
      const resp = await fetch(
        `../api/get_unavailable_sessions.php?room_id=${encodeURIComponent(roomId)}&date=${encodeURIComponent(dateVal)}`
      );
      const data = await resp.json();
      if (!data.success) {
        console.error('Gagal load sesi unavailable:', data.error);
        return;
      }
      const unavailable = data.sessions || [];

      sessionCheckboxes.forEach(cb => {
        if (unavailable.includes(cb.value)) {
          cb.checked  = false;
          cb.disabled = true;
          const item = cb.closest('.session-item');
          if (item) item.classList.add('disabled');
        }
      });
    } catch (err) {
      console.error('Error fetch unavailable sessions:', err);
    }
  }

  // ====== Gabungan: reset → blokir jam sekarang → blokir dari DB ======
  async function refreshAllSessions() {
    resetSessionsBase();
    applyTimeBasedAvailability();
    await applyDbUnavailableSessions();
    recomputeSessions(); // setelah semua aturan terpakai
  }

  // ====== Validasi simple tanggal (range saja) ======
  function validateDate() {
    if (!dateInput || !dateInput.value) return true;
    const chosen = new Date(dateInput.value + 'T00:00:00');
    const min = new Date(dateInput.min + 'T00:00:00');
    const max = new Date(dateInput.max + 'T23:59:59');

    if (chosen < min) {
      showToast('Tanggal tidak boleh di masa lalu.', 'warning');
      return false;
    }
    if (chosen > max) {
      showToast('Tanggal hanya bisa dipilih maksimal 7 hari ke depan.', 'warning');
      return false;
    }
    return true;
  }

  if (dateInput) {
    dateInput.addEventListener('change', () => {
      if (!validateDate()) return;
      refreshAllSessions();
    });
  }

  if (roomSelect) {
    roomSelect.addEventListener('change', () => {
      refreshAllSessions();
    });
  }

  // ====== Hitung durasi + field time dari jumlah sesi (2 jam per sesi) ======
  function recomputeSessions(changedCb = null) {
    const selected = sessionCheckboxes.filter(cb => cb.checked);

    if (selected.length > 3 && changedCb) {
      // batalin checkbox yang baru dicentang kalau lebih dari 3
      changedCb.checked = false;
      showToast('Maksimal 3 sesi.', 'warning');
    }

    const finalSelected = sessionCheckboxes.filter(cb => cb.checked);
    const totalDuration = finalSelected.length * 2;

    if (durationInput) {
      durationInput.value = totalDuration || '';
    }
    if (timeInput) {
      // kirim ke server sebagai string: "10:00-12:00, 12:00-14:00, ..."
      timeInput.value = finalSelected.map(cb => cb.value).join(', ');
    }
    if (sessionHelper) {
      if (finalSelected.length === 0) {
        sessionHelper.textContent =
          'Pilih minimal 1 sesi, maksimal 3 sesi. Sesi yang sudah lewat + 1 sesi setelah jam sekarang akan otomatis tidak bisa dipilih.';
      } else {
        sessionHelper.textContent =
          `Dipilih ${finalSelected.length} sesi (total ${totalDuration} jam). Maksimal 3 sesi.`;
      }
    }
  }

  sessionCheckboxes.forEach(cb => {
    cb.addEventListener('change', () => {
      // jangan izinkan klik ke sesi yang disabled dari CSS
      if (cb.disabled) {
        cb.checked = false;
        return;
      }
      recomputeSessions(cb);
    });
  });

  // ====== Load kategori & buku (tetap) ======
  if (categorySelect && bookSelect) {
    fetch('../api/get_categories.php')
      .then(r => r.json())
      .then(data => {
        if (!Array.isArray(data)) return;
        data.forEach(cat => {
          const opt = document.createElement('option');
          opt.value = cat.id;
          opt.textContent = cat.name;
          categorySelect.appendChild(opt);
        });
      })
      .catch(err => console.error('Error load categories:', err));

    categorySelect.addEventListener('change', function() {
      const categoryId = this.value;
      bookSelect.innerHTML = '<option value="">Pilih Buku</option>';
      if (!categoryId) return;
      fetch(`../api/get_books_by_categories.php?category_id=${encodeURIComponent(categoryId)}`)
        .then(r => r.json())
        .then(data => {
          if (!Array.isArray(data)) return;
          data.forEach(book => {
            const opt = document.createElement('option');
            opt.value = book.id;
            opt.textContent = book.title;
            bookSelect.appendChild(opt);
          });
        })
        .catch(err => console.error('Error load books:', err));
    });
  }

  // ====== Quick select dari kartu ruangan ======
  document.addEventListener('click', function(e) {
    const btn = e.target.closest('.room-select-btn');
    if (!btn) return;
    const roomId   = btn.dataset.room;
    const roomName = btn.dataset.roomName;
    showSection('reservation');
    if (typeSelect) {
      typeSelect.value = 'ruangan';
      typeSelect.dispatchEvent(new Event('change', { bubbles: true }));
    }
    if (roomSelect) roomSelect.value = roomId;

    refreshAllSessions();

    const formContainer = document.querySelector('.reservation-form-container');
    if (formContainer) {
      formContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
    showToast(`Ruangan "${roomName}" dipilih. Silakan lengkapi detail reservasi.`, 'info');
  });

  // ====== Validasi & submit form ======
  reservationForm.addEventListener('submit', async (e) => {
    e.preventDefault();

    const formData = new FormData(reservationForm);
    const type = formData.get('reservation_type');

    if (!type) {
      showToast('Silakan pilih jenis reservasi.', 'warning');
      return;
    }

    if (!validateDate()) return;

    // minimal 1 sesi kalau ada reservasi ruangan
    if (type === 'ruangan' || type === 'both') {
      const selectedSessions = sessionCheckboxes.filter(cb => cb.checked);
      if (selectedSessions.length === 0) {
        showToast('Silakan pilih minimal 1 sesi ruangan.', 'warning');
        return;
      }
      // pastikan time & duration sudah terisi sesuai checkbox
      recomputeSessions();
    }

    // tentukan URL backend
    let url = '';
    if (type === 'ruangan') {
      url = '../reservation/proses_reservasi_ruangan.php';
    } else if (type === 'buku') {
      url = '../reservation/proses_reservasi_buku.php';
    } else if (type === 'both') {
      url = '../reservation/proses_reservasi_semua.php';
    } else {
      showToast('Jenis reservasi tidak valid.', 'error');
      return;
    }

    try {
      const resp = await fetch(url, { method: 'POST', body: formData });
      if (!resp.ok) throw new Error(`HTTP error! Status: ${resp.status}`);
      const result = await resp.json();

      if (!result.success) {
        showToast(result.error || result.message || 'Terjadi kesalahan saat menyimpan data.', 'error');
        return;
      }

      if (type === 'ruangan') {
        showToast('Reservasi ruangan berhasil disimpan!', 'success');
      } else if (type === 'buku') {
        showToast('Reservasi buku berhasil disimpan!', 'success');
      } else {
        showToast('Reservasi ruangan + buku berhasil disimpan!', 'success');
      }

      // reset form
      reservationForm.reset();
      if (bookSelect)     bookSelect.innerHTML = '<option value="">Pilih Buku</option>';
      if (categorySelect) categorySelect.value = '';
      updateMinMaxDate();
      resetSessionsBase();

      if (typeSelect) {
        typeSelect.value = '';
        typeSelect.dispatchEvent(new Event('change', { bubbles: true }));
      }

    } catch (err) {
      console.error('Fetch error:', err);
      showToast('Gagal menghubungi server. Cek konsol untuk detail.', 'error');
    }
  });

  // ====== Validasi input WA (hanya angka dan +) ======
  if (waInput) {
    waInput.addEventListener('input', function() {
      this.value = this.value.replace(/[^\d+]/g, '');
    });
  }

  // Inisialisasi awal
  updateMinMaxDate();
  refreshAllSessions();
});



// === Carousel ===
function initCarousel() {
  const carouselSlides = document.querySelector('.carousel-slides');
  const slides = document.querySelectorAll('.carousel-slide');
  const prevBtn = document.querySelector('.carousel-btn-prev');
  const nextBtn = document.querySelector('.carousel-btn-next');
  const indicators = document.querySelectorAll('.indicator');

  if (!carouselSlides || slides.length === 0) return;

  let currentSlide = 0;
  const totalSlides = slides.length;

  function updateCarousel() {
    carouselSlides.style.transform = `translateX(-${currentSlide * 100}%)`;
    indicators.forEach((ind, idx) => {
      if (idx === currentSlide) ind.classList.add('active');
      else ind.classList.remove('active');
    });
  }
  function nextSlide() {
    currentSlide = (currentSlide + 1) % totalSlides;
    updateCarousel();
  }
  function prevSlide() {
    currentSlide = (currentSlide - 1 + totalSlides) % totalSlides;
    updateCarousel();
  }

  if (nextBtn) {
    const newNextBtn = nextBtn.cloneNode(true);
    nextBtn.parentNode.replaceChild(newNextBtn, nextBtn);
    newNextBtn.addEventListener('click', nextSlide);
  }
  if (prevBtn) {
    const newPrevBtn = prevBtn.cloneNode(true);
    prevBtn.parentNode.replaceChild(newPrevBtn, prevBtn);
    newPrevBtn.addEventListener('click', prevSlide);
  }

  indicators.forEach(indicator => {
    indicator.addEventListener('click', () => {
      const idx = parseInt(indicator.getAttribute('data-slide'), 10);
      if (!Number.isNaN(idx)) {
        currentSlide = idx;
        updateCarousel();
      }
    });
  });
}

function ensureActiveSectionVisible() {
  const activeSection = document.querySelector('.content-section.active');
  if (activeSection) {
    activeSection.style.transform = 'translateX(0)';
    activeSection.style.position = 'relative';
    activeSection.style.zIndex = '1';
  }
}

// === Toggle Form Ruangan / Buku + Required ===
document.addEventListener('DOMContentLoaded', function () {
  const typeSelect  = document.getElementById('reservation_type');
  const formRuangan = document.getElementById('form-ruangan');
  const formBuku    = document.getElementById('form-buku');

  const ruanganFields = ['room', 'people', 'date', 'duration']; // ⬅️ HAPUS 'time'
  const bukuFields    = ['category_id', 'book'];

  function setRequired(fields, required) {
    fields.forEach(id => {
      const el = document.getElementById(id);
      if (!el) return;
      if (required) el.setAttribute('required', 'required');
      else el.removeAttribute('required');
    });
  }

  function handleTypeChange() {
    const type = typeSelect.value;
    if (type === 'ruangan') {
      formRuangan.style.display = 'block';
      formBuku.style.display    = 'none';
      setRequired(ruanganFields, true);
      setRequired(bukuFields, false);
    } else if (type === 'buku') {
      formRuangan.style.display = 'none';
      formBuku.style.display    = 'block';
      setRequired(ruanganFields, false);
      setRequired(bukuFields, true);
    } else if (type === 'both') {
      formRuangan.style.display = 'block';
      formBuku.style.display    = 'block';
      setRequired(ruanganFields, true);
      setRequired(bukuFields, true);
    } else {
      formRuangan.style.display = 'none';
      formBuku.style.display    = 'none';
      setRequired(ruanganFields, false);
      setRequired(bukuFields, false);
    }
  }

  if (typeSelect) {
    typeSelect.addEventListener('change', handleTypeChange);
    handleTypeChange();
  }
});


// === Rooms (dynamic dari DB) ===
async function loadRoomData() {
  try {
    const response = await fetch('../api/get_rooms.php');
    const data = await response.json();
    if (data.success && data.data) {
      renderRooms(data.data);
    }
  } catch (err) {
    console.error('Error loading room data:', err);
  }
}
function renderRooms(rooms) {
  const roomOptions = document.querySelector('.room-options');
  if (!roomOptions) return;
  roomOptions.innerHTML = '';

  const roomSelect = document.getElementById('room');
  if (roomSelect) {
    roomSelect.innerHTML = '';
    const defaultOption = new Option('Pilih Ruangan', '');
    roomSelect.appendChild(defaultOption);
    rooms.forEach(room => {
      const opt = new Option(`${room.name} (${room.capacity} orang)`, room.id);
      roomSelect.add(opt);
    });
  }

  rooms.forEach(room => {
    const roomCard = document.createElement('div');
    roomCard.className = 'room-card';
    roomCard.innerHTML = `
      <div class="room-image">
        <img src="${room.image || 'uploads/default-room.jpg'}" alt="${room.name}">
      </div>
      <div class="room-info">
        <h3>${room.name}</h3>
        <p class="capacity">Kapasitas: ${room.capacity} orang</p>
        <p class="description">${room.description}</p>
        <p class="facilities">${room.facilities}</p>
      </div>`;
    roomOptions.appendChild(roomCard);
  });
}

// ==== Hitung Durasi Otomatis dari Jumlah Sesi (2 jam per sesi) ====
document.addEventListener('DOMContentLoaded', function() {
    const timeSelect = document.getElementById('time');
    const durationInput = document.getElementById('duration');

    if (!timeSelect) return;

    timeSelect.addEventListener('change', function () {
        const selected = Array.from(timeSelect.selectedOptions);

        if (selected.length > 3) {
            // batasi max 3 sesi
            selected[selected.length - 1].selected = false;
            showToast('Maksimal 3 sesi!', 'warning');
            return;
        }

        const totalDuration = selected.length * 2; // 2 jam per sesi
        durationInput.value = totalDuration;
    });
});

// ==== Validasi sederhana nomor WA ====
document.getElementById('whatsapp').addEventListener('input', function() {
    this.value = this.value.replace(/[^\d+]/g, ''); // hanya angka & +
});

</script>
</body>
</html>
