<?php
session_start();

require_once __DIR__ . '/../api/config.php';
// Jika belum login atau bukan admin, arahkan ke login.html
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../login/login.html');
    exit;
}

// --- avatar helper: ambil avatar admin untuk header ---
$admin_avatar_url = null;
if (isset($_SESSION['username'])) {
    try {
        $stmt = $pdo->prepare("SELECT avatar_path FROM users WHERE username = :u LIMIT 1");
        $stmt->execute([':u' => $_SESSION['username']]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($r && !empty($r['avatar_path']) && file_exists(__DIR__ . '/' . ltrim($r['avatar_path'], '/'))) {
            $admin_avatar_url = $r['avatar_path'];
        } else {
            // inline SVG placeholder (fast, no external request)
            $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="40" height="40"><rect width="100%" height="100%" fill="#e6e6e6"/><text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="10" fill="#9b9b9b">ADM</text></svg>';
            $admin_avatar_url = 'data:image/svg+xml;utf8,' . rawurlencode($svg);
        }
    } catch (Exception $ex) {
        // jika ada error DB, tetap gunakan placeholder inline
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="40" height="40"><rect width="100%" height="100%" fill="#e6e6e6"/><text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="10" fill="#9b9b9b">ADM</text></svg>';
        $admin_avatar_url = 'data:image/svg+xml;utf8,' . rawurlencode($svg);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Kafe Embun</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Loading Screen -->
    <div id="loading-screen" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; flex-direction: column; justify-content: center; align-items: center; z-index: 9999; color: white; font-family: Arial, sans-serif;">
        <div class="spinner" style="border: 4px solid rgba(255, 255, 255, 0.3); border-top: 4px solid white; border-radius: 50%; width: 50px; height: 50px; animation: spin 1s linear infinite; margin-bottom: 20px;"></div>
        <p>Memeriksa autentikasi...</p>
    </div>

    <!-- Main Admin Content (hidden initially) -->
    <div id="admin-content" style="display: none;">
        <!-- Header -->
        <header class="admin-header">
            <div class="container">
                <div class="header-content">
                    <h1><i class="fas fa-cog"></i> Admin Panel - Kafe Embun</h1>
                    <div class="admin-info">
                        <span id="admin-greeting">Halo, Admin!</span>
                        <a href="../../login/login.html" class="btn btn-logout">Logout</a>
                        <a href="../../users/panels/Index.php" class="btn btn-secondary">Lihat Website</a>
                        <a href="../reservations/reservations_admin.php" class="btn btn-logout">Lihat Reservasi</a>
                        <a href="../orders/orders.php" class="btn btn-secondary">📦 Lihat Order</a>
                        <a href="../profile/admin_profile.php" class="btn btn-logout">Profil Admin</a>
                    </div>
                </div>
            </div>
        </header>

        <!-- Navigation -->
        <nav class="admin-nav">
            <div class="container">
                <ul class="nav-tabs">
                    <li><a href="#menu" class="nav-tab active" data-tab="menu"><i class="fas fa-utensils"></i> Menu</a></li>
                    <li><a href="#books" class="nav-tab" data-tab="books"><i class="fas fa-book"></i> Buku</a></li>
                    <li><a href="#boardgames" class="nav-tab" data-tab="boardgames"><i class="fas fa-dice"></i> Boardgames</a></li>
                    <li><a href="#menu-categories" class="nav-tab" data-tab="menu-categories"><i class="fas fa-tags"></i> Kategori Menu</a></li>
                    <li><a href="#book-categories" class="nav-tab" data-tab="book-categories"><i class="fas fa-tag"></i> Kategori Buku</a></li>
                    <li><a href="#website-content" class="nav-tab" data-tab="website-content"><i class="fas fa-globe"></i> Website Content</a></li>
                    <li><a href="#rooms" class="nav-tab" data-tab="rooms"><i class="fas fa-door-open"></i> Rooms</a></li>
                </ul>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="admin-main">
            <div class="container">
                <!-- Menu Management -->
                <section id="menu-tab" class="tab-content active">
                    <div class="section-header">
                        <h2>Manajemen Menu</h2>
                        <button class="btn btn-primary" id="add-menu-btn"><i class="fas fa-plus"></i> Tambah Menu</button>
                    </div>
                    
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Kategori</th>
                                    <th>Harga</th>
                                    <th>Best Seller</th>
                                    <!-- <th>Urutan</th> -->
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="menu-table-body">
                                <tr><td colspan="6" style="text-align: center;">Memuat data...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <!-- Books Management -->
                <section id="books-tab" class="tab-content">
                    <div class="section-header">
                        <h2>Manajemen Buku</h2>
                        <button class="btn btn-primary" id="add-book-btn"><i class="fas fa-plus"></i> Tambah Buku</button>
                    </div>
                    
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Judul</th>
                                    <th>Penulis</th>
                                    <th>Kategori</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="books-table-body">
                                <tr><td colspan="4" style="text-align: center;">Memuat data...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <!-- Boardgames Management -->
                <section id="boardgames-tab" class="tab-content">
                    <div class="section-header">
                        <h2>Manajemen Boardgames</h2>
                        <button class="btn btn-primary" id="add-boardgame-btn"><i class="fas fa-plus"></i> Tambah Boardgame</button>
                    </div>
                    
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Min Player</th>
                                    <th>Max Player</th>
                                    <th>Waktu Main</th>
                                    <!-- <th>Urutan</th> -->
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="boardgames-table-body">
                                <tr><td colspan="6" style="text-align: center;">Memuat data...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </section>
                
                <!-- Menu Categories Management -->
                <section id="menu-categories-tab" class="tab-content">
                    <div class="section-header">
                        <h2>Manajemen Kategori Menu</h2>
                        <button class="btn btn-primary" id="add-menu-category-btn"><i class="fas fa-plus"></i> Tambah Kategori</button>
                    </div>
                    
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Slug</th>
                                    <!-- <th>Urutan Tampil</th> -->
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="menu-categories-table-body">
                                <tr><td colspan="4" style="text-align: center;">Memuat data...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <!-- Book Categories Management -->
                <section id="book-categories-tab" class="tab-content">
                    <div class="section-header">
                        <h2>Manajemen Kategori Buku</h2>
                        <button class="btn btn-primary" id="add-book-category-btn"><i class="fas fa-plus"></i> Tambah Kategori</button>
                    </div>
                    
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Slug</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="book-categories-table-body">
                                <tr><td colspan="3" style="text-align: center;">Memuat data...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </section>
                <!-- Website Content Management -->
                <section id="website-content-tab" class="tab-content">
                    <div class="section-header">
                        <h2>Website Content Management</h2>
                        <button class="btn btn-primary" id="add-website-content-btn"><i class="fas fa-edit"></i> Edit Website Content</button>
                    </div>
                    
                    <div style="background: white; padding: 30px; border-radius: 8px; box-shadow: var(--shadow);">
                        <h3 style="color: var(--primary-color); margin-bottom: 20px;">Current Website Content</h3>
                        
                        <!-- Hero Section -->
                        <div style="margin-bottom: 30px; padding-bottom: 20px; border-bottom: 1px solid #eee;">
                            <h4 style="color: var(--secondary-color); margin-bottom: 15px;">Hero Section</h4>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                                <div>
                                    <p><strong>Background Image:</strong></p>
                                    <div id="current-hero-bg" style="margin-top: 5px;">
                                        <span>Loading...</span>
                                    </div>
                                </div>
                                <div>
                                    <p><strong>Subtitle:</strong></p>
                                    <div id="current-hero-subtitle" style="margin-top: 5px; color: #666;">
                                        Loading...
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- About Section -->
                        <div>
                            <h4 style="color: var(--secondary-color); margin-bottom: 15px;">About Section</h4>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                                <div>
                                    <p><strong>Title:</strong></p>
                                    <div id="current-about-title" style="margin-top: 5px; color: #666;">
                                        Loading...
                                    </div>
                                    
                                    <p style="margin-top: 15px;"><strong>Image:</strong></p>
                                    <div id="current-about-image" style="margin-top: 5px;">
                                        Loading...
                                    </div>
                                </div>
                                <div>
                                    <p><strong>Content:</strong></p>
                                    <div id="current-about-content" style="margin-top: 5px; color: #666; line-height: 1.6;">
                                        Loading...
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                <!-- Room Management -->
                <section id="rooms-tab" class="tab-content">
                    <div class="section-header">
                        <h2>Rooms</h2>
                        <button id="add-room-btn" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add Room
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table id="rooms-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Capacity</th>
                                    <th>Description</th>
                                    <th>Facilities</th>
                                    <th>Image</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </section>


                <!-- Room Modal -->
                <div id="room-modal" class="modal">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h2>Add/Edit Room</h2>
                            <span class="close">&times;</span>
                        </div>
                        <form id="room-form">
                            <input type="hidden" id="room-id" name="id">
                            <input type="hidden" id="existing-room-image" name="existing_image">
                            
                            <div class="form-group">
                                <label for="room-name">Room Name</label>
                                <input type="text" id="room-name" name="name" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="room-capacity">Capacity</label>
                                <input type="number" id="room-capacity" name="capacity" required min="1">
                            </div>
                            
                            <div class="form-group">
                                <label for="room-description">Description</label>
                                <textarea id="room-description" name="description" required></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label for="room-facilities">Facilities</label>
                                <textarea id="room-facilities" name="facilities"></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label for="room-image">Image</label>
                                <input type="file" id="room-image" name="image" accept="image/*">
                                <div id="room-image-preview" class="image-preview"></div>
                            </div>
                            
                            <div class="form-actions">
                                <button type="button" id="cancel-room" class="btn btn-secondary">Cancel</button>
                                <button type="submit" class="btn btn-primary">Save</button>
                            </div>
                        </form>
                    </div>
                </div>                                
            </div>
        </main>
        
        <!-- Modals -->
        <!-- Menu Modal -->
        <div id="menu-modal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 id="menu-modal-title">Tambah Menu</h3>
                    <span class="close">&times;</span>
                </div>
                <form id="menu-form">
                    <input type="hidden" id="menu-id">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="menu-name">Nama Menu *</label>
                            <input type="text" id="menu-name" required>
                        </div>
                        <div class="form-group">
                            <label for="menu-category">Kategori *</label>
                            <select id="menu-category" required>
                                <option value="">Memuat kategori...</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="menu-description">Deskripsi</label>
                        <textarea id="menu-description" rows="3"></textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="menu-price">Harga *</label>
                            <input type="number" id="menu-price" min="0" step="100" required>
                        </div>
                        <!-- <div class="form-group">
                            <label for="menu-order">Urutan Tampil</label>
                            <input type="number" id="menu-order" min="0" value="0">
                        </div> -->
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="menu-image">Upload Gambar(16:9) Max: 5MB</label>
                            <input type="file" id="menu-image" accept="image/*">
                            <div id="menu-image-preview" style="margin-top: 10px; display: none;">
                                <img src="" alt="Preview" style="max-width: 200px; max-height: 150px; border-radius: 5px;">
                            </div>
                        </div>
                        <div class="form-group checkbox-group">
                            <label>
                                <input type="checkbox" id="menu-best-seller">
                                Best Seller
                            </label>
                        </div>
                    </div>
                    <div class="modal-actions">
                        <button type="button" class="btn btn-secondary" id="cancel-menu">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Book Modal -->
        <div id="book-modal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 id="book-modal-title">Tambah Buku</h3>
                    <span class="close">&times;</span>
                </div>
                <form id="book-form">
                    <input type="hidden" id="book-id">
                    <div class="form-group">
                        <label for="book-title">Judul Buku *</label>
                        <input type="text" id="book-title" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="book-author">Penulis</label>
                            <input type="text" id="book-author">
                        </div>
                        <div class="form-group">
                            <label for="book-category">Kategori *</label>
                            <select id="book-category" required>
                                <option value="">Memuat kategori...</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="book-cover">Upload Cover Buku(3:4) Max: 5MB</label>
                        <input type="file" id="book-cover" accept="image/*">
                        <div id="book-cover-preview" style="margin-top: 10px; display: none;">
                            <img src="" alt="Preview" style="max-width: 200px; max-height: 150px; border-radius: 5px;">
                        </div>
                    </div>
                    <div class="modal-actions">
                        <button type="button" class="btn btn-secondary" id="cancel-book">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Boardgame Modal -->
        <div id="boardgame-modal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 id="boardgame-modal-title">Tambah Boardgame</h3>
                    <span class="close">&times;</span>
                </div>
                <form id="boardgame-form">
                    <input type="hidden" id="boardgame-id">
                    <div class="form-group">
                        <label for="boardgame-name">Nama Boardgame *</label>
                        <input type="text" id="boardgame-name" required>
                    </div>
                    <div class="form-group">
                        <label for="boardgame-description">Deskripsi</label>
                        <textarea id="boardgame-description" rows="3"></textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="boardgame-min-players">Min Pemain</label>
                            <input type="number" id="boardgame-min-players" min="1" value="1">
                        </div>
                        <div class="form-group">
                            <label for="boardgame-max-players">Max Pemain</label>
                            <input type="number" id="boardgame-max-players" min="1" value="4">
                        </div>
                        <div class="form-group">
                            <label for="boardgame-play-time">Waktu Main (menit)</label>
                            <input type="number" id="boardgame-play-time" min="1" value="30">
                        </div>
                    </div>
                    <div class="form-row">
                        <!-- <div class="form-group">
                            <label for="boardgame-order">Urutan Tampil</label>
                            <input type="number" id="boardgame-order" min="0" value="0">
                        </div> -->
                        <div class="form-group">
                            <label for="boardgame-image">Upload Gambar(4:3) Max: 5MB</label>
                            <input type="file" id="boardgame-image" accept="image/*">
                            <div id="boardgame-image-preview" style="margin-top: 10px; display: none;">
                                <img src="" alt="Preview" style="max-width: 200px; max-height: 150px; border-radius: 5px;">
                            </div>
                        </div>
                    </div>
                    <div class="modal-actions">
                        <button type="button" class="btn btn-secondary" id="cancel-boardgame">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Menu Category Modal -->
        <div id="menu-category-modal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 id="menu-category-modal-title">Tambah Kategori Menu</h3>
                    <span class="close">&times;</span>
                </div>
                <form id="menu-category-form">
                    <input type="hidden" id="menu-category-id">
                    <input type="hidden" id="menu-category-type" value="menu">
                    <div class="form-group">
                        <label for="menu-category-name">Nama Kategori *</label>
                        <input type="text" id="menu-category-name" required>
                    </div>
                    <div class="form-group">
                        <label for="menu-category-slug">Slug *</label>
                        <input type="text" id="menu-category-slug" required>
                        <small style="color: #666;">Slug akan digunakan dalam URL. Contoh: coffee, tea, snacks</small>
                    </div>
                    <!-- <div class="form-group">
                        <label for="menu-category-order">Urutan Tampil</label>
                        <input type="number" id="menu-category-order" min="0" value="0">
                    </div> -->
                    <div class="modal-actions">
                        <button type="button" class="btn btn-secondary" id="cancel-menu-category">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>

        

        <!-- Book Category Modal -->
        <div id="book-category-modal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 id="book-category-modal-title">Tambah Kategori Buku</h3>
                    <span class="close">&times;</span>
                </div>
                <form id="book-category-form">
                    <input type="hidden" id="book-category-id">
                    <input type="hidden" id="book-category-type" value="book">
                    <div class="form-group">
                        <label for="book-category-name">Nama Kategori *</label>
                        <input type="text" id="book-category-name" required>
                    </div>
                    <div class="form-group">
                        <label for="book-category-slug">Slug *</label>
                        <input type="text" id="book-category-slug" required>
                        <small style="color: #666;">Slug akan digunakan dalam URL. Contoh: fiction, non-fiction</small>
                    </div>
                    <div class="modal-actions">
                        <button type="button" class="btn btn-secondary" id="cancel-book-category">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>

   <!-- Website Content Modal -->
        <div id="website-content-modal" class="modal">
            <div class="modal-content" style="max-width: 800px;">
                <div class="modal-header">
                    <h3>Edit Website Content</h3>
                    <span class="close">&times;</span>
                </div>
                <form id="website-content-form">
                    <!-- Hero Section -->
                    <div style="margin-bottom: 30px; padding-bottom: 20px; border-bottom: 1px solid #eee;">
                        <h4 style="color: var(--primary-color); margin-bottom: 20px; border-bottom: 2px solid var(--secondary-color); padding-bottom: 5px;">Hero Section</h4>
                        
                        <div class="form-group">
                            <label for="hero-background-file">Hero Background Image (16:9) Max: 5MB</label>
                            <input type="file" id="hero-background-file" accept="image/*">
                            <div id="hero-background-preview" style="margin-top: 10px; display: none;">
                                <img src="" alt="Preview" style="max-width: 100%; max-height: 300px; border-radius: 5px; border: 1px solid #ddd;">
                            </div>
                            <small style="color: #666;">Current: <span id="current-hero-bg-modal">Loading...</span></small>
                        </div>
                        
                        <div class="form-group">
                            <label for="hero-subtitle">Hero Subtitle</label>
                            <input type="text" id="hero-subtitle" placeholder="Best budget friendly cafe in Araya" style="width: 100%;">
                        </div>
                    </div>

                    <!-- About Section -->
                    <div style="margin-bottom: 20px;">
                        <h4 style="color: var(--primary-color); margin-bottom: 20px; border-bottom: 2px solid var(--secondary-color); padding-bottom: 5px;">About Section</h4>
                        
                        <div class="form-group">
                            <label for="about-title">About Title</label>
                            <input type="text" id="about-title" placeholder="Ruang Nyaman untuk Setiap Momen" style="width: 100%;">
                        </div>
                        
                        <div class="form-group">
                            <label for="about-content">About Content</label>
                            <textarea id="about-content" rows="6" placeholder="Deskripsi tentang kafe..." style="width: 100%; resize: vertical;"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="about-image-file">About Image (16:9) Max: 5MB</label>
                            <input type="file" id="about-image-file" accept="image/*">
                            <div id="about-image-preview" style="margin-top: 10px; display: none;">
                                <img src="" alt="Preview" style="max-width: 100%; max-height: 300px; border-radius: 5px; border: 1px solid #ddd;">
                            </div>
                            <small style="color: #666;">Current: <span id="current-about-image-modal">Loading...</span></small>
                        </div>
                    </div>
                    
                    <div class="modal-actions">
                        <button type="button" class="btn btn-secondary" onclick="admin.closeModals()">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Loading Spinner -->
        <div id="loading" class="loading-spinner" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); justify-content: center; align-items: center; z-index: 9999;">
            <div class="spinner" style="border: 4px solid rgba(255, 255, 255, 0.3); border-top: 4px solid white; border-radius: 50%; width: 50px; height: 50px; animation: spin 1s linear infinite;"></div>
        </div>
    </div>

    <script>
        // Authentication check
        async function checkAuthentication() {
            try {
                console.log('Checking authentication...');
                const response = await fetch('../api/auth-check.php', { credentials: 'same-origin' });

                const data = await response.json();
                
                console.log('Auth response:', data);
                
                if (data.authenticated) {
                    // Show admin content
                    document.getElementById('loading-screen').style.display = 'none';
                    document.getElementById('admin-content').style.display = 'block';
                    
                    // Update greeting if username is available
                    if (data.username) {
                        document.getElementById('admin-greeting').textContent = `Halo, ${data.username}!`;
                    }
                    
                    // Initialize admin panel
                    initializeAdminPanel();
                } else {
                    // Redirect to login page
                    console.log('Not authenticated, redirecting to login');
                    window.location.href = '../../login/login.html';
                }
            } catch (error) {
                console.error('Authentication check failed:', error);
                alert('Error checking authentication: ' + error.message);
                window.location.href = '../../login/login.html';
            }
        }

        // Initialize admin panel after authentication
        function initializeAdminPanel() {
            console.log('Initializing admin panel...');
            
            // Load the admin script
            const script = document.createElement('script');
            script.src = 'admin-script.js';
            script.onload = function() {
                console.log('Admin script loaded successfully');
                // Initialize admin panel
                if (typeof AdminPanel !== 'undefined') {
                    window.admin = new AdminPanel();
                    console.log('Admin panel initialized successfully');
                } else {
                    console.error('AdminPanel class not found');
                }
            };
            script.onerror = function() {
                console.error('Failed to load admin script');
                alert('Failed to load admin functionality. Please refresh the page.');
            };
            document.head.appendChild(script);
        }

        // Check authentication when page loads
        document.addEventListener('DOMContentLoaded', checkAuthentication);
    </script>

    <style>
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
<script src="admin_panel.js?v=2"></script>

</body>
</html>