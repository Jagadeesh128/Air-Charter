# ✈️ Air Charter Back-End

## 📌 Project Overview

**Air-Charter** is a Symfony-based backend system for managing air charter bookings. It supports both admin and end-user workflows, providing APIs, authentication, and a scalable architecture.

* 🔗 Repository: [https://github.com/Jagadeesh128/Air-Charter](https://github.com/Jagadeesh128/Air-Charter)
* 📄 License: MIT

---

## 🚀 Features

* Aircraft, Route, User, and Booking management
* REST APIs using API Platform
* JWT-based authentication
* Admin dashboard (EasyAdmin)
* Image upload support
* Doctrine ORM with migrations
* Dockerized setup + traditional setup support

---

## 📁 Project Structure

```
src/              # Business logic (controllers, entities)
public/           # Entry point (index.php)
config/           # Configuration files
migrations/       # DB migrations
templates/        # Twig templates
translations/     # Language files
docker/           # Docker-related configs
compose.yaml      # Docker services
Dockerfile        # App container build
```

---

## 🐳 Docker Setup (Recommended)

### Prerequisites

* Docker
* Docker Compose

### 1. Clone Repository

```bash
git clone https://github.com/Jagadeesh128/Air-Charter.git
cd Air-Charter
```

### 2. Start Containers

```bash
docker compose up -d --build
```

### 3. Enter App Container

```bash
docker compose exec app bash
```

### 4. Install Dependencies

```bash
composer install
```

### 5. Setup Database

```bash
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load  # optional
```

### 6. Import Sample Data (Optional)

```bash
mysql -u symfonyuser -p air_charter_db < aircraft.sql
mysql -u symfonyuser -p air_charter_db < routes.sql
mysql -u symfonyuser -p air_charter_db < user.sql
```

### 7. Access Application

```
http://localhost:8000
```

---

## 🖥️ Local Setup (Without Docker)

### Requirements

* PHP 8.2+
* Apache (mod_rewrite enabled)
* MySQL 8+

### Steps

```bash
git clone https://github.com/Jagadeesh128/Air-Charter.git
cd Air-Charter
composer install
```

### Configure Environment

Create `.env.local`:

```env
DATABASE_URL="mysql://user:password@127.0.0.1:3306/air_charter_db"
```

### Setup Database

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load
```

### Apache Config

Ensure DocumentRoot points to `public/` and enable rewrite:

```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

---

## 🔧 Useful Commands

```bash
# Clear cache
php bin/console cache:clear

# Run SQL test
php bin/console doctrine:query:sql "SELECT 1"

# View logs
docker compose logs -f
```

---

## ⚠️ Troubleshooting

| Issue               | Solution                            |
| ------------------- | ----------------------------------- |
| DB connection error | Check DATABASE_URL and DB container |
| 404/403 errors      | Verify Apache config + mod_rewrite  |
| Migration issues    | Re-run migrations                   |
| Docker issues       | `docker compose down && up --build` |

---

## 📌 Notes

* Use `.env.local` for local overrides
* Do not commit sensitive credentials
* Rebuild Docker after config changes

---

## 🤝 Contribution

1. Fork repo
2. Create feature branch
3. Commit changes
4. Open PR

---

## 👤 Author

Jagadeesh
[https://github.com/Jagadeesh128](https://github.com/Jagadeesh128)
