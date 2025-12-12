# ☕ Embun Slowbar

A full-stack café web application featuring coffee ordering, library management, boardgame catalog, and room reservations.

![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?logo=mysql&logoColor=white)
![Docker](https://img.shields.io/badge/Docker-Ready-2496ED?logo=docker&logoColor=white)

---

## 📖 Documentation

For comprehensive project documentation, please refer to **[knowledge.md](./knowledge.md)** which includes:

- Complete project structure
- Database schema details
- API endpoints reference
- Docker configuration
- Deployment commands
- And more...

---

## 🚀 Quick Start

### Option 1: Using Docker (Recommended)

```bash
# Clone the repository
git clone <repository-url>
cd Embun

# Copy environment file
cp .env.example .env

# Start all services
docker-compose up -d

# Access the application
# Main App: http://localhost:8080
# phpMyAdmin: http://localhost:8888
```

### Option 2: Using XAMPP (Local Development)

If you prefer to run this project on **XAMPP localhost**, follow these steps:

#### Step 1: Copy Application Files

1. Navigate to the `/app` folder in this project
2. Copy the entire `public_html/embun` folder
3. Paste it into your XAMPP's `htdocs` directory

```
C:\xampp\htdocs\
└── embun/          ← Place the embun folder here
```

#### Step 2: Import Database

1. Open **phpMyAdmin** at `http://localhost/phpmyadmin`
2. Create a new database named `embun_slowbar`
3. Import the SQL file:
   - Use `db/init.sql` for fresh installation
   - Or use `embun_slowbar.sql` (database backup)

#### Step 3: Configure Database Connection

Edit the database configuration in `/embun/config/database.php` if needed:

```php
// Default XAMPP settings (usually works out of the box)
$host = 'localhost';
$dbname = 'embun_slowbar';
$username = 'root';
$password = '';  // Empty for default XAMPP
```

#### Step 4: Access the Application

Open your browser and navigate to:
- **Application**: `http://localhost/embun/`
- **Admin Panel**: `http://localhost/embun/admin/panels/`

---

## 🔑 Default Login Credentials

| Username | Password | Role |
|----------|----------|------|
| admin | admin | Admin |
| user | user | User |

---

## 🛠️ Tech Stack

- **Backend**: PHP 8.2
- **Database**: MySQL 8.0
- **Web Server**: Apache
- **Payment Gateway**: Midtrans (Sandbox)
- **Containerization**: Docker & Docker Compose

---

## 📁 Project Structure

```
Embun/
├── app/
│   └── public_html/
│       └── embun/          # Main application (copy this for XAMPP)
├── db/
│   └── init.sql            # Database schema & seed data
├── docker/                 # Docker configuration files
├── docker-compose.yml      # Docker orchestration
├── .env.example            # Environment template
├── knowledge.md            # Detailed documentation
└── README.md               # This file
```

---

## 📝 License

This project is for educational and demonstration purposes.

---

*For more details, see [knowledge.md](./knowledge.md)*
