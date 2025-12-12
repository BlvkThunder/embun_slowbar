<?php
// admin/api/admin_api.php
// Handle all admin operations with file upload support

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// DEV: tampilkan error biar gampang debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Always JSON
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Config & DB
require_once __DIR__ . '/config.php';

if (!isset($pdo) || $pdo === null) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

// AUTH: only admin
if (!isset($_SESSION['username']) || ($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized - Please login first']);
    exit;
}

// Path helper
$scriptDir   = dirname($_SERVER['SCRIPT_NAME']);          // e.g. /andi/embun/admin/api
$webBase     = rtrim(str_replace('/api', '', $scriptDir), '/'); // e.g. /andi/embun/admin -> kita mau ke /andi/embun
$webBase     = rtrim(str_replace('/admin', '', $webBase), '/'); // hasil akhirnya: /andi/embun
$projectRoot = rtrim($_SERVER['DOCUMENT_ROOT'], DIRECTORY_SEPARATOR) . $webBase . '/';
$uploadBasePath = $projectRoot . 'uploads/';
$uploadBaseUrl  = $webBase . '/uploads/';

// Ensure upload dirs exist
$uploadDirs = [
    'menu/',
    'books/',
    'boardgames/',
    'website/',
    'rooms/',
];

foreach ($uploadDirs as $dir) {
    $fullPath = $uploadBasePath . $dir;
    if (!is_dir($fullPath)) {
        mkdir($fullPath, 0755, true);
        error_log("Created directory: $fullPath");
    }
}

/**
 * Handle image upload
 */
function handleFileUpload($file, $uploadType, $existingFile = '')
{
    global $uploadBasePath, $uploadBaseUrl;

    if (isset($file) && $file['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        $fileType = mime_content_type($file['tmp_name']);

        if (!in_array($fileType, $allowedTypes)) {
            throw new Exception('Hanya file gambar JPEG dan PNG yang diizinkan');
        }

        if ($file['size'] > 5 * 1024 * 1024) {
            throw new Exception('Ukuran file maksimal 5MB');
        }

        $imageInfo = getimagesize($file['tmp_name']);
        if (!$imageInfo) {
            throw new Exception('File bukan gambar yang valid');
        }

        $filename = str_replace(' ', '_', uniqid() . '_' . time() . '.jpg');
        $destination = $uploadBasePath . $uploadType . '/' . $filename;

        $dir = dirname($destination);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            $error = error_get_last();
            throw new Exception('Gagal mengupload file: ' . ($error['message'] ?? 'Unknown error'));
        }

        // Hapus file lama (kalau lokal)
        if (!empty($existingFile)) {
            // jika existing berupa URL: ubah dulu ke path file
            if (strpos($existingFile, $uploadBaseUrl) === 0) {
                $existingPath = $uploadBasePath . substr($existingFile, strlen($uploadBaseUrl));
            } else {
                $existingPath = $existingFile;
            }

            if (file_exists($existingPath)) {
                unlink($existingPath);
            }
        }

        return $uploadBaseUrl . $uploadType . '/' . $filename;
    } elseif (isset($file) && $file['error'] !== UPLOAD_ERR_NO_FILE) {
        throw new Exception('Error upload file: ' . $file['error']);
    }

    return $existingFile;
}

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'get_menu':             getMenu(); break;
        case 'save_menu':            saveMenu(); break;
        case 'delete_menu':          deleteMenu(); break;

        case 'get_book':             getBook(); break;
        case 'save_book':            saveBook(); break;
        case 'delete_book':          deleteBook(); break;

        case 'get_boardgame':        getBoardgame(); break;
        case 'save_boardgame':       saveBoardgame(); break;
        case 'delete_boardgame':     deleteBoardgame(); break;

        case 'get_category':         getCategory(); break;
        case 'save_category':        saveCategory(); break;
        case 'delete_category':      deleteCategory(); break;

        case 'get_website_content':  getWebsiteContent(); break;
        case 'save_website_content': saveWebsiteContent(); break;

        case 'get_rooms':            getRooms(); break;
        case 'get_room':             getRoom(); break;
        case 'save_room':            saveRoom(); break;
        case 'delete_room':          deleteRoom(); break;

        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Action tidak valid: ' . $action]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

/* =========================
 *  MENU
 * =======================*/

function getMenu()
{
    global $pdo;

    $id = $_GET['id'] ?? null;

    try {
        if ($id) {
            $stmt = $pdo->prepare("
                SELECT mi.*, mc.name AS category_name, mc.slug AS category_slug
                FROM menu_items mi
                LEFT JOIN menu_categories mc ON mi.category_id = mc.id
                WHERE mi.id = ?
            ");
            $stmt->execute([$id]);
            $menu = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($menu) {
                // jadikan 0/1 integer supaya konsisten
                $menu['is_best_seller'] = (int)$menu['is_best_seller'];

                echo json_encode(['success' => true, 'data' => $menu]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Menu tidak ditemukan']);
            }
        } else {
            $stmt = $pdo->query("
                SELECT mi.*, mc.name AS category_name, mc.slug AS category_slug
                FROM menu_items mi
                LEFT JOIN menu_categories mc ON mi.category_id = mc.id
            ");
            $menuItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($menuItems as &$item) {
                $item['is_best_seller'] = (int)$item['is_best_seller'];
            }
            unset($item);

            $stmt = $pdo->query("SELECT * FROM menu_categories");
            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success'    => true,
                'menu_items' => $menuItems,
                'categories' => $categories
            ]);
        }
    } catch (PDOException $e) {
        throw new Exception('Database error: ' . $e->getMessage());
    }
}

function saveMenu()
{
    global $pdo;

    try {
        $id            = $_POST['id'] ?? null;
        $name          = trim($_POST['name'] ?? '');
        $category_id   = $_POST['category_id'] ?? '';
        $description   = trim($_POST['description'] ?? '');
        $price         = $_POST['price'] ?? 0;
        $is_best_seller = isset($_POST['is_best_seller']) && $_POST['is_best_seller'] === '1' ? 1 : 0;

        if ($name === '') {
            throw new Exception('Nama menu harus diisi');
        }
        if ($category_id === '') {
            throw new Exception('Kategori harus dipilih');
        }
        if (empty($price) || $price <= 0) {
            throw new Exception('Harga harus diisi dan lebih dari 0');
        }

        $existingImage = '';
        if ($id) {
            $stmt = $pdo->prepare("SELECT image_url FROM menu_items WHERE id = ?");
            $stmt->execute([$id]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
            $existingImage = $existing['image_url'] ?? '';
        }

        $image_url = handleFileUpload($_FILES['menu_image'] ?? null, 'menu', $existingImage);

        if ($id) {
            $stmt = $pdo->prepare("
                UPDATE menu_items
                SET name = ?, category_id = ?, description = ?, price = ?, image_url = ?, is_best_seller = ?
                WHERE id = ?
            ");
            $ok = $stmt->execute([$name, $category_id, $description, $price, $image_url, $is_best_seller, $id]);

            if (!$ok) {
                throw new Exception('Gagal memperbarui menu');
            }

            echo json_encode(['success' => true, 'message' => 'Menu berhasil diperbarui']);
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO menu_items (name, category_id, description, price, image_url, is_best_seller)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $ok = $stmt->execute([$name, $category_id, $description, $price, $image_url, $is_best_seller]);

            if (!$ok) {
                throw new Exception('Gagal menambahkan menu');
            }

            echo json_encode(['success' => true, 'message' => 'Menu berhasil ditambahkan']);
        }
    } catch (PDOException $e) {
        throw new Exception('Database error: ' . $e->getMessage());
    }
}

function deleteMenu()
{
    global $pdo, $uploadBasePath, $uploadBaseUrl;

    try {
        $id = $_POST['id'] ?? null;
        if (!$id) {
            throw new Exception('ID menu tidak diberikan');
        }

        $stmt = $pdo->prepare("SELECT image_url FROM menu_items WHERE id = ?");
        $stmt->execute([$id]);
        $menu = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($menu && !empty($menu['image_url'])) {
            $imageUrl  = $menu['image_url'];
            if (strpos($imageUrl, $uploadBaseUrl) === 0) {
                $imagePath = $uploadBasePath . substr($imageUrl, strlen($uploadBaseUrl));
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
        }

        $stmt = $pdo->prepare("DELETE FROM menu_items WHERE id = ?");
        $ok = $stmt->execute([$id]);

        if ($ok && $stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Menu berhasil dihapus']);
        } else {
            throw new Exception('Menu tidak ditemukan atau gagal dihapus');
        }
    } catch (PDOException $e) {
        throw new Exception('Database error: ' . $e->getMessage());
    }
}

/* =========================
 *  BOOKS
 * =======================*/

function getBook()
{
    global $pdo;

    $id = $_GET['id'] ?? null;

    try {
        if ($id) {
            $stmt = $pdo->prepare("
                SELECT b.*, bc.name AS category_name
                FROM books b
                LEFT JOIN book_categories bc ON b.category_id = bc.id
                WHERE b.id = ?
            ");
            $stmt->execute([$id]);
            $book = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($book) {
                echo json_encode(['success' => true, 'data' => $book]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Buku tidak ditemukan']);
            }
        } else {
            $stmt = $pdo->query("
                SELECT b.*, bc.name AS category_name, bc.slug AS category_slug
                FROM books b
                LEFT JOIN book_categories bc ON b.category_id = bc.id
                ORDER BY b.title
            ");
            $books = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmt = $pdo->query("SELECT * FROM book_categories ORDER BY name");
            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success'    => true,
                'books'      => $books,
                'categories' => $categories
            ]);
        }
    } catch (PDOException $e) {
        throw new Exception('Database error: ' . $e->getMessage());
    }
}

function saveBook()
{
    global $pdo;

    try {
        $id          = $_POST['id'] ?? null;
        $title       = trim($_POST['title'] ?? '');
        $author      = trim($_POST['author'] ?? '');
        $category_id = $_POST['category_id'] ?? '';

        if ($title === '') {
            throw new Exception('Judul buku harus diisi');
        }
        if ($category_id === '') {
            throw new Exception('Kategori buku harus dipilih');
        }

        $existingImage = '';
        if ($id) {
            $stmt = $pdo->prepare("SELECT cover_image FROM books WHERE id = ?");
            $stmt->execute([$id]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
            $existingImage = $existing['cover_image'] ?? '';
        }

        $cover_image = handleFileUpload($_FILES['book_cover'] ?? null, 'books', $existingImage);

        if ($id) {
            $stmt = $pdo->prepare("
                UPDATE books
                SET title = ?, author = ?, category_id = ?, cover_image = ?
                WHERE id = ?
            ");
            $ok = $stmt->execute([$title, $author, $category_id, $cover_image, $id]);

            if (!$ok) {
                throw new Exception('Gagal memperbarui buku');
            }

            echo json_encode(['success' => true, 'message' => 'Buku berhasil diperbarui']);
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO books (title, author, category_id, cover_image)
                VALUES (?, ?, ?, ?)
            ");
            $ok = $stmt->execute([$title, $author, $category_id, $cover_image]);

            if (!$ok) {
                throw new Exception('Gagal menambahkan buku');
            }

            echo json_encode(['success' => true, 'message' => 'Buku berhasil ditambahkan']);
        }
    } catch (PDOException $e) {
        throw new Exception('Database error: ' . $e->getMessage());
    }
}

function deleteBook()
{
    global $pdo, $uploadBasePath, $uploadBaseUrl;

    try {
        $id = $_POST['id'] ?? null;
        if (!$id) {
            throw new Exception('ID buku tidak diberikan');
        }

        $stmt = $pdo->prepare("SELECT cover_image FROM books WHERE id = ?");
        $stmt->execute([$id]);
        $book = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($book && !empty($book['cover_image'])) {
            $imageUrl = $book['cover_image'];
            if (strpos($imageUrl, $uploadBaseUrl) === 0) {
                $imagePath = $uploadBasePath . substr($imageUrl, strlen($uploadBaseUrl));
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
        }

        $stmt = $pdo->prepare("DELETE FROM books WHERE id = ?");
        $ok = $stmt->execute([$id]);

        if ($ok && $stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Buku berhasil dihapus']);
        } else {
            throw new Exception('Buku tidak ditemukan atau gagal dihapus');
        }
    } catch (PDOException $e) {
        throw new Exception('Database error: ' . $e->getMessage());
    }
}

/* =========================
 *  BOARDGAMES
 * =======================*/

function getBoardgame()
{
    global $pdo;

    $id = $_GET['id'] ?? null;

    try {
        if ($id) {
            $stmt = $pdo->prepare("SELECT * FROM boardgames WHERE id = ?");
            $stmt->execute([$id]);
            $bg = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($bg) {
                echo json_encode(['success' => true, 'data' => $bg]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Boardgame tidak ditemukan']);
            }
        } else {
            $stmt = $pdo->query("SELECT * FROM boardgames");
            $games = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'boardgames' => $games]);
        }
    } catch (PDOException $e) {
        throw new Exception('Database error: ' . $e->getMessage());
    }
}

function saveBoardgame()
{
    global $pdo;

    try {
        $id          = $_POST['id'] ?? null;
        $name        = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $min_players = (int)($_POST['min_players'] ?? 1);
        $max_players = (int)($_POST['max_players'] ?? 4);
        $play_time   = (int)($_POST['play_time'] ?? 30);

        if ($name === '') {
            throw new Exception('Nama boardgame harus diisi');
        }
        if ($min_players > $max_players) {
            throw new Exception('Min pemain tidak boleh lebih besar dari max pemain');
        }

        $existingImage = '';
        if ($id) {
            $stmt = $pdo->prepare("SELECT image_url FROM boardgames WHERE id = ?");
            $stmt->execute([$id]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
            $existingImage = $existing['image_url'] ?? '';
        }

        $image_url = handleFileUpload($_FILES['boardgame_image'] ?? null, 'boardgames', $existingImage);

        if ($id) {
            $stmt = $pdo->prepare("
                UPDATE boardgames
                SET name = ?, description = ?, min_players = ?, max_players = ?, play_time = ?, image_url = ?
                WHERE id = ?
            ");
            $ok = $stmt->execute([$name, $description, $min_players, $max_players, $play_time, $image_url, $id]);

            if (!$ok) {
                throw new Exception('Gagal memperbarui boardgame');
            }

            echo json_encode(['success' => true, 'message' => 'Boardgame berhasil diperbarui']);
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO boardgames (name, description, min_players, max_players, play_time, image_url)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $ok = $stmt->execute([$name, $description, $min_players, $max_players, $play_time, $image_url]);

            if (!$ok) {
                throw new Exception('Gagal menambahkan boardgame');
            }

            echo json_encode(['success' => true, 'message' => 'Boardgame berhasil ditambahkan']);
        }
    } catch (PDOException $e) {
        throw new Exception('Database error: ' . $e->getMessage());
    }
}

function deleteBoardgame()
{
    global $pdo, $uploadBasePath, $uploadBaseUrl;

    try {
        $id = $_POST['id'] ?? null;
        if (!$id) {
            throw new Exception('ID boardgame tidak diberikan');
        }

        $stmt = $pdo->prepare("SELECT image_url FROM boardgames WHERE id = ?");
        $stmt->execute([$id]);
        $bg = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($bg && !empty($bg['image_url'])) {
            $imageUrl = $bg['image_url'];
            if (strpos($imageUrl, $uploadBaseUrl) === 0) {
                $imagePath = $uploadBasePath . substr($imageUrl, strlen($uploadBaseUrl));
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
        }

        $stmt = $pdo->prepare("DELETE FROM boardgames WHERE id = ?");
        $ok = $stmt->execute([$id]);

        if ($ok && $stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Boardgame berhasil dihapus']);
        } else {
            throw new Exception('Boardgame tidak ditemukan atau gagal dihapus');
        }
    } catch (PDOException $e) {
        throw new Exception('Database error: ' . $e->getMessage());
    }
}

/* =========================
 *  CATEGORIES
 * =======================*/

function getCategory()
{
    global $pdo;

    $id   = $_GET['id'] ?? null;
    $type = $_GET['type'] ?? '';

    if (!$id || !$type) {
        throw new Exception('ID dan tipe kategori harus diberikan');
    }

    $table = ($type === 'menu') ? 'menu_categories' : 'book_categories';

    $stmt = $pdo->prepare("SELECT * FROM $table WHERE id = ?");
    $stmt->execute([$id]);
    $cat = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($cat) {
        echo json_encode(['success' => true, 'data' => $cat]);
    } else {
        throw new Exception('Kategori tidak ditemukan');
    }
}

function saveCategory()
{
    global $pdo;

    $id   = $_POST['id'] ?? null;
    $type = $_POST['type'] ?? '';
    $name = trim($_POST['name'] ?? '');
    $slug = trim($_POST['slug'] ?? '');

    if ($name === '' || $slug === '' || $type === '') {
        throw new Exception('Nama, slug, dan tipe kategori harus diisi');
    }

    $table = ($type === 'menu') ? 'menu_categories' : 'book_categories';

    if ($id) {
        $stmt = $pdo->prepare("UPDATE $table SET name = ?, slug = ? WHERE id = ?");
        $stmt->execute([$name, $slug, $id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO $table (name, slug) VALUES (?, ?)");
        $stmt->execute([$name, $slug]);
    }

    echo json_encode(['success' => true]);
}

function deleteCategory()
{
    global $pdo;

    $id   = $_POST['id'] ?? null;
    $type = $_POST['type'] ?? '';

    if (!$id || !$type) {
        throw new Exception('ID dan tipe kategori harus diberikan');
    }

    $table = ($type === 'menu') ? 'menu_categories' : 'book_categories';

    $stmt = $pdo->prepare("DELETE FROM $table WHERE id = ?");
    $stmt->execute([$id]);

    echo json_encode(['success' => true]);
}

/* =========================
 *  WEBSITE CONTENT
 * =======================*/

function getWebsiteContent()
{
    global $pdo;

    try {
        $stmt = $pdo->query("SELECT * FROM website_content");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $content = [];
        foreach ($rows as $r) {
            $content[$r['content_key']] = [
                'id'    => $r['id'],
                'value' => $r['content_value'],
                'type'  => $r['content_type']
            ];
        }

        echo json_encode(['success' => true, 'content' => $content]);
    } catch (PDOException $e) {
        throw new Exception('Database error: ' . $e->getMessage());
    }
}

function saveWebsiteContent()
{
    global $pdo;

    try {
        $content_key   = $_POST['content_key'] ?? '';
        $content_value = $_POST['content_value'] ?? '';

        if ($content_key === '') {
            throw new Exception('Content key is required');
        }

        if (isset($_FILES['content_file']) && $_FILES['content_file']['error'] === UPLOAD_ERR_OK) {
            $existingValue = '';

            $stmt = $pdo->prepare("SELECT content_value FROM website_content WHERE content_key = ?");
            $stmt->execute([$content_key]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($existing) {
                $existingValue = $existing['content_value'];
            }

            $content_value = handleFileUpload($_FILES['content_file'], 'website', $existingValue);
        }

        $stmt = $pdo->prepare("
            INSERT INTO website_content (content_key, content_value, content_type)
            VALUES (?, ?, 'text')
            ON DUPLICATE KEY UPDATE content_value = VALUES(content_value), updated_at = CURRENT_TIMESTAMP
        ");

        $ok = $stmt->execute([$content_key, $content_value]);

        if (!$ok) {
            throw new Exception('Gagal menyimpan content');
        }

        echo json_encode(['success' => true, 'message' => 'Content berhasil disimpan']);
    } catch (Exception $e) {
        error_log("Error saving website content: " . $e->getMessage());
        throw $e;
    }
}

/* =========================
 *  ROOMS
 * =======================*/

function getRooms()
{
    global $pdo;

    try {
        $stmt = $pdo->query("SELECT * FROM rooms ORDER BY id");
        $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'data' => $rooms]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

function getRoom()
{
    global $pdo;

    $id = $_GET['id'] ?? null;
    if (!$id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Missing room id']);
        return;
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = ?");
        $stmt->execute([$id]);
        $room = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($room) {
            echo json_encode(['success' => true, 'data' => $room]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Room not found']);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

function saveRoom()
{
    global $pdo;

    try {
        $id          = $_POST['id'] ?? null;
        $name        = $_POST['name'] ?? '';
        $capacity    = (int)($_POST['capacity'] ?? 0);
        $description = $_POST['description'] ?? '';
        $facilities  = $_POST['facilities'] ?? '';
        $existingImg = $_POST['existing_image'] ?? '';

        if ($name === '' || $description === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Name and description are required']);
            return;
        }

        $imageUrl = handleFileUpload($_FILES['image'] ?? null, 'rooms', $existingImg);

        if ($id) {
            $stmt = $pdo->prepare("
                UPDATE rooms
                SET name = ?, capacity = ?, description = ?, facilities = ?, image = ?
                WHERE id = ?
            ");
            $stmt->execute([$name, $capacity, $description, $facilities, $imageUrl, $id]);
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO rooms (name, capacity, description, facilities, image)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$name, $capacity, $description, $facilities, $imageUrl]);
        }

        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

function deleteRoom()
{
    global $pdo, $uploadBasePath;

    try {
        $id = $_POST['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Missing room id']);
            return;
        }

        $stmt = $pdo->prepare("SELECT image FROM rooms WHERE id = ?");
        $stmt->execute([$id]);
        $room = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("DELETE FROM rooms WHERE id = ?");
        $stmt->execute([$id]);

        if ($room && $room['image']) {
            // room.image is assumed to be URL like /embun/uploads/rooms/xxx.jpg
            $imagePath = $projectRoot . ltrim($room['image'], '/');
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
