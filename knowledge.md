# Embun Slowbar - Project Knowledge Base

> **Version**: DockerV2  
> **Last Updated**: December 12, 2025  
> **Docker Hub**: `ga502du/embun:DockerV2`

---

## 📋 Project Overview

**Embun Slowbar** is a full-stack web application for a cozy café that combines:
- ☕ **Coffee Ordering System** - Online menu and checkout with Midtrans payment integration
- 📚 **Library Management** - Book collection, borrowing, and categories
- 🎲 **Boardgame Catalog** - Display and management of available boardgames
- 🏠 **Room Reservations** - Private room booking system
- 👤 **User Management** - Authentication, roles (admin/user), and profiles

---

## 🏗️ Project Structure

```
Embun/
├── app/
│   └── public_html/           # Web root (cleaned & optimized)
│       ├── index.php          # Root redirect → /embun/
│       └── embun/             # Core application
│           ├── admin/         # Admin dashboard & APIs
│           │   ├── api/       # Admin API endpoints (5 files)
│           │   ├── orders/    # Order management (6 files)
│           │   ├── history/   # Order/Reservation history (3 files)
│           │   ├── reservations/  # Reservation management (4 files)
│           │   ├── panels/    # Admin UI panels (5 files)
│           │   └── profile/   # Admin profile management (3 files)
│           ├── users/         # User-facing features
│           │   ├── api/       # User API endpoints (11 files)
│           │   ├── checkout/  # Payment checkout (4 files)
│           │   ├── panels/    # User UI panels (3 files)
│           │   └── reservation/  # Room/book reservations (3 files)
│           ├── login/         # Authentication (5 files)
│           ├── config/        # Configuration
│           │   └── database.php  # Centralized DB config
│           └── uploads/       # User uploads
│               ├── boardgames/   # Boardgame images
│               ├── books/        # Book cover images
│               ├── menu/         # Menu item images
│               ├── rooms/        # Room images
│               └── website/      # Website content images
├── db/
│   └── init.sql               # Database schema & seed data (883 lines)
├── docker/
│   ├── mysql/                 # MySQL configuration
│   └── php/
│       └── Dockerfile         # Development Dockerfile
├── Dockerfile                 # Production Dockerfile (for Docker Hub)
├── docker-compose.yml         # Multi-service orchestration
├── .env                       # Environment variables
├── .env.example               # Environment template
├── embun_slowbar.sql          # Database backup (~54KB)
└── knowledge.md               # This file
```

### 📊 Project Statistics
- **Total PHP/JS/CSS Files**: ~76 files in `/app/public_html/`
- **Core App Components**: 57 files in `/embun/`
- **Upload Categories**: 5 directories (boardgames, books, menu, rooms, website)
- **Database Schema**: 883 lines with seed data

---

## 🐳 Docker Configuration

### Production Image (Docker Hub)

**Image**: `ga502du/embun:DockerV2`

```bash
# Pull the image
docker pull ga502du/embun:DockerV2

# Run standalone (requires external MySQL)
docker run -d -p 8080:80 \
  -e DB_HOST=your_mysql_host \
  -e DB_NAME=embun_slowbar \
  -e DB_USER=embun_user \
  -e DB_PASSWORD=your_password \
  -e MIDTRANS_SERVER_KEY=your_server_key \
  -e MIDTRANS_CLIENT_KEY=your_client_key \
  ga502du/embun:DockerV2
```

### Local Development (docker-compose)

```bash
# Start all services
docker-compose up -d

# Stop services
docker-compose down

# View logs
docker-compose logs -f
```

### Services Architecture

| Service | Container | Port | Description |
|---------|-----------|------|-------------|
| **app** | Embun | 8080:80 | PHP 8.2 + Apache webserver |
| **db** | Embun_db | 3307:3306 | MySQL 8.0 database |
| **phpmyadmin** | Embun_pma | 8888:80 | Database administration |

### Access URLs (Local Development)

| URL | Purpose |
|-----|---------|
| http://localhost:8080 | Main Application (auto-redirects to /embun/) |
| http://localhost:8080/embun/ | Login Page |
| http://localhost:8080/embun/admin/panels/ | Admin Dashboard |
| http://localhost:8080/embun/users/panels/ | User Dashboard |
| http://localhost:8888 | phpMyAdmin |

---

## 🔧 Environment Variables

Copy `.env.example` to `.env` and configure:

```env
# Database Configuration
DB_HOST=db                     # 'db' for Docker, 'localhost' for XAMPP
DB_NAME=embun_slowbar
DB_USER=embun_user
DB_PASSWORD=embun_password_123

# MySQL Root Password (Docker only)
MYSQL_ROOT_PASSWORD=root_password_123

# Midtrans Payment Gateway (Sandbox)
MIDTRANS_SERVER_KEY=Mid-server-98kpatTHxQfwBNveH0ltKckl
MIDTRANS_CLIENT_KEY=Mid-client-QkOUo2wGLK0eGGQ3
MIDTRANS_IS_PRODUCTION=false
```

---

## 🗄️ Database Schema

### Tables Overview (15 Tables)

| Table | Description |
|-------|-------------|
| `users` | User accounts with role-based access (admin/user) |
| `menu_items` | Café menu items (62 items across 9 categories) |
| `menu_categories` | Menu categorization (9 categories) |
| `orders` | Customer orders with payment status |
| `orders_history` | Audit trail for order changes |
| `order_item_options` | Order customization options |
| `option_types` | Available customization types (sugar, ice, etc.) |
| `payment_logs` | Payment gateway logs |
| `books` | Library book collection (127 books) |
| `book_categories` | Book categorization (14 categories) |
| `loans` | Book borrowing records (with fine tracking) |
| `boardgames` | Boardgame catalog (9 games) |
| `rooms` | Reservable private rooms (2 rooms) |
| `reservations` | Room/book reservations |
| `reservations_history` | Reservation audit trail |
| `website_content` | Dynamic website content (hero, about, etc.) |

### Menu Categories
| ID | Name | Slug |
|----|------|------|
| 1 | Black Coffee | coffee |
| 2 | Milky Coffee | milky-coffee |
| 3 | Tea | tea |
| 4 | Matcha | matcha |
| 5 | Squash | squash |
| 6 | Dairy Milk | dairy |
| 7 | Santapan | food |
| 8 | Kudapan | snack |
| 9 | Dessert | dessert |

### Book Categories
| ID | Name |
|----|------|
| 1 | Fiksi |
| 2 | Misteri |
| 3 | Komik dan Novel Grafis |
| 4 | Pengembangan Diri dan Psikologi |
| 5 | Sejarah dan Biografi |
| 6 | Sosial dan Politik |
| 7 | Finansial |
| 8 | Makanan dan Minuman |
| 9 | Fantasi |
| 10 | Seni |
| 11 | Edukasi |
| 12 | Komedi |
| 13 | Religius |
| 14 | Sastra |

### Key Relationships

```
users ──┬── orders (via customer info)
        ├── loans (via user_id)
        └── reservations (via user_id)

menu_items ── menu_categories (via category_id)
books ── book_categories (via category_id)
loans ── books (via book_id)
orders ── orders_history (1:many audit)
reservations ── reservations_history (1:many audit)
```

---

## 🔐 Database Connection

The application uses a centralized database configuration at:
`/embun/config/database.php`

### Features:
- **Environment Detection**: Automatically detects Docker vs XAMPP
- **Dual Connection Support**: PDO (primary) and MySQLi (backward compatibility)
- **Environment Variables**: Reads from ENV with fallback defaults
- **Error Handling**: Graceful error handling with environment-specific display

### Usage in PHP files:

```php
<?php
require_once __DIR__ . '/../config/database.php';

// Use PDO (recommended)
$pdo = getDbConnection();
if ($pdo) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
}

// Or use MySQLi (legacy support)
$mysqli = getMysqliConnection();

// Check connection status
if (isDbConnected()) {
    // Database is ready
}

// Get current environment
echo getEnvironment(); // 'docker' or 'development'
```

---

## 💳 Payment Integration

The application integrates with **Midtrans** payment gateway (Sandbox mode).

### Supported Payment Methods:
- QRIS
- Bank Transfer (Virtual Account)
- Other Midtrans-supported methods

### Order Status Flow:
```
pending → paid → (completed)
        ↘ expired
        ↘ failed
        ↘ cancelled
```

---

## 👥 User Roles

| Role | Access |
|------|--------|
| **admin** | Full access: manage orders, menu, books, rooms, users, website content |
| **user** | Limited access: view menu, place orders, make reservations, borrow books |

### Default Accounts (from seed data):

| Username | Role | Notes |
|----------|------|-------|
| admin | admin | Full admin access |
| Joanne | admin | Admin with avatar |
| user | user | Standard user |

---

## 📂 API Endpoints

### User APIs (`/embun/users/api/`)

| File | Purpose |
|------|---------|
| `config.php` | API configuration |
| `get_menu.php` | Fetch menu items and categories |
| `get_books.php` | Fetch book collection |
| `get_books_by_categories.php` | Fetch books filtered by category |
| `get_categories.php` | Fetch all categories |
| `get_boardgame.php` | Fetch boardgame catalog |
| `get_rooms.php` | Fetch available rooms |
| `get_unavailable_sessions.php` | Check room availability |
| `get_website_content.php` | Fetch dynamic website content |
| `process_reservation.php` | Handle reservation submissions |
| `register.php` | User registration |

### Admin APIs (`/embun/admin/api/`)

| File | Purpose |
|------|---------|
| `admin_api.php` | Main admin API (~27KB, comprehensive CRUD) |
| `auth-check.php` | Authentication verification |
| `config.php` | API configuration |
| `get_books_by_categories.php` | Fetch books with admin options |
| `get_categories.php` | Fetch categories with admin options |

### Admin Order Management (`/embun/admin/orders/`)
- `orders.php` - Order listing and management
- `delete_order.php` - Order deletion
- `export_orders.php` - Export orders to file
- `payment_logs.php` - View payment logs
- `print_order.php` - Print order receipt
- `sync_status.php` - Sync with Midtrans

---

## 🚀 Deployment Commands

### Build Docker Image
```bash
docker build -t ga502du/embun:DockerV2 .
```

### Push to Docker Hub
```bash
docker login
docker push ga502du/embun:DockerV2
```

### Run Production Container
```bash
docker run -d \
  --name embun-production \
  -p 80:80 \
  -e DB_HOST=production-mysql-host \
  -e DB_NAME=embun_slowbar \
  -e DB_USER=prod_user \
  -e DB_PASSWORD=secure_password \
  -e MIDTRANS_SERVER_KEY=production_key \
  -e MIDTRANS_CLIENT_KEY=production_key \
  -e MIDTRANS_IS_PRODUCTION=true \
  ga502du/embun:DockerV2
```

---

## 🛠️ Development Workflow

### Prerequisites
- Docker & Docker Compose
- (Optional) XAMPP for local PHP development without Docker

### Quick Start
```bash
# Clone and start
cd /path/to/Embun
cp .env.example .env
docker-compose up -d

# Wait for MySQL to be healthy (check logs)
docker-compose logs -f db

# Access the application
open http://localhost:8080
```

### Database Reset
```bash
# Remove volumes and restart
docker-compose down -v
docker-compose up -d
```

---

## 📝 Important Notes

1. **XAMPP Compatibility**: The project supports both Docker and XAMPP environments. When running on XAMPP, ensure MySQL uses `localhost` with root credentials.

2. **File Uploads**: User uploads are stored in `/embun/uploads/` subdirectories:
   - `/embun/uploads/menu/` - Menu item images
   - `/embun/uploads/books/` - Book cover images
   - `/embun/uploads/boardgames/` - Boardgame images
   - `/embun/uploads/rooms/` - Room images
   - `/embun/uploads/website/` - Website content images

3. **Payment Security**: Never commit production Midtrans keys. Use environment variables for sensitive data.

4. **Production Considerations**:
   - Change `MIDTRANS_IS_PRODUCTION=true` for live payments
   - Use proper SSL/TLS certificates
   - Configure proper database passwords
   - Set `display_errors = 0` in php.ini

---

## 🧹 Project Optimization History

### v1.1 (December 11, 2025):
Removed XAMPP-related files to optimize the project:

| Removed | Size/Count | Reason |
|---------|------------|--------|
| `dashboard/` | 142 files | XAMPP default dashboard (multilingual docs, FAQs) |
| `applications.html` | 159 bytes | XAMPP placeholder |
| `applications (Copy).html` | 3.6 KB | Duplicate backup file |
| `bitnami.css` | 177 bytes | Unused Bitnami styles |
| `index.html` | 159 bytes | Duplicate of index.php functionality |
| `favicon.ico` | 30 KB | XAMPP default favicon |
| `img/` | 2 files | XAMPP module images |
| `webalizer/` | Empty | XAMPP analytics tool directory |

**Result**: Reduced `public_html` from 206 items down to just 2 (embun folder + index.php).

---

## 📊 Docker Images Summary

| Image | Size | Purpose |
|-------|------|---------|
| `ga502du/embun:DockerV2` | ~890MB | Production PHP app |
| `mysql:8.0` | ~1.08GB | Database |
| `phpmyadmin/phpmyadmin:latest` | ~1.09GB | DB administration |
| `php:8.2-apache` | ~714MB | Base image |

---

## 🔗 Useful Links

- **Docker Hub**: [ga502du/embun](https://hub.docker.com/r/ga502du/embun)
- **Midtrans Dashboard**: [dashboard.midtrans.com](https://dashboard.midtrans.com)
- **Midtrans Sandbox**: [dashboard.sandbox.midtrans.com](https://dashboard.sandbox.midtrans.com)

---

## 📈 Content Statistics

### Seed Data Summary:
- **Menu Items**: 62 items across 9 categories
- **Books**: 127 books across 14 categories
- **Boardgames**: 9 games
- **Rooms**: 2 private rooms (Meeting Room, Cozy Corner)
- **Users**: 6 accounts (2 admin, 4 user)
- **Website Content**: 5 dynamic content entries

---

*This document serves as the primary knowledge base for the Embun Slowbar project. Keep it updated as the project evolves.*
