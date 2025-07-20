## Air-Charter Back-End

## Project Overview

**Air-Charter** is a comprehensive Symfony web application for managing air charter bookings, tailored for administrative and end-user workflows. It combines PHP (Symfony), MySQL, Apache (or PHP built-in server), and integrates best practices for modular web application development and Dockerized deployment.

- **GitHub Repository:** [Air-Charter on GitHub](https://github.com/Jagadeesh128/Air-Charter)
- **License:** MIT

---

## Features

- Aircraft, Route, User, and Booking management
- API Platform support for RESTful web services
- JWT-based user authentication
- Admin dashboard (EasyAdmin)
- Image uploads for aircraft/public assets
- Doctrine ORM with migrations
- Configurable via environment variables
- Ready for Docker build or classic Apache/PHP environment

---

## Directory Structure Overview

| Directory/File                     | Purpose                                                 |
|-------------------------------------|--------------------------------------------------------|
| `src/`                             | Application source code (entities, controllers, logic) |
| `public/`                          | Web root (entry: `index.php`, static assets)           |
| `bin/`                             | Symfony CLI tools, console                             |
| `config/`                          | Symfony and bundle configuration files                 |
| `migrations/`                      | Doctrine migration scripts                             |
| `templates/`                       | Twig templates (UI)                                    |
| `translations/`                    | App language files                                     |
| `compose.yaml`, `Dockerfile`        | Docker configuration                                   |
| `apache/000-default.conf`           | Custom Apache vhost config (Docker)                    |
| `aircraft.sql`, `user.sql`, `routes.sql` | Example table export files                      |

---

## Running the Project (Development)

### Option 1: Dockerized Environment (Recommended for Consistency)

**Prerequisites:**
- Docker and Docker Compose installed on your machine.

**1. Clone the Repository:**
git clone https://github.com/Jagadeesh128/Air-Charter.git
cd Air-Charter

text

**2. Build and Start the Containers:**
docker compose up --build

text
> This will build a custom PHP 8.2 + Apache image (with PDO MySQL and mod_rewrite), start a MySQL 8.0 database container, and apply your custom Apache config (`public/` as DocumentRoot).

**3. Access the Web App:**
- Visit: [http://localhost:8000](http://localhost:8000)

**4. One-Time Command Setup:**
Enter the app container:
docker compose exec app bash

text
Install dependencies and set up the database:
composer install
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load # Optional: load sample data

text
Import demo data (if applicable):
mysql -u symfonyuser -p air_charter_db < /var/www/html/aircraft.sql
mysql -u symfonyuser -p air_charter_db < /var/www/html/routes.sql
mysql -u symfonyuser -p air_charter_db < /var/www/html/user.sql

text
*(Enter your DB user's password when prompted.)*

**5. Stopping/Restarting:**
- Stop: `docker compose down`
- See logs: `docker compose logs -f`

---

### Option 2: Classic Apache2/PHP Host (Non-Docker, e.g. XAMPP/LAMP/WAMP)

**Prerequisites:**
- Apache2 (with mod_rewrite)
- PHP 8.2+ (with PDO MySQL extension)
- MySQL 8.0+

**1. Clone the Repository:**
git clone https://github.com/Jagadeesh128/Air-Charter.git
cd Air-Charter

text

**2. Configure your Apache VirtualHost:**
- DocumentRoot must point to `<project-root>/public`
- Example (`/etc/apache2/sites-available/air-charter.conf`):

    ```
    <VirtualHost *:80>
        DocumentRoot /path/to/Air-Charter/public
        <Directory /path/to/Air-Charter/public>
            AllowOverride All
            Require all granted
            Options -Indexes +FollowSymLinks
        </Directory>
        ErrorLog ${APACHE_LOG_DIR}/error.log
        CustomLog ${APACHE_LOG_DIR}/access.log combined
    </VirtualHost>
    ```

- Enable the site and `mod_rewrite`, and restart Apache:
    ```
    sudo a2enmod rewrite
    sudo a2ensite air-charter
    sudo systemctl reload apache2
    ```

**3. Set Up Environment Variables**
Copy `.env` to `.env.local` and update the `DATABASE_URL` for your local DB:
DATABASE_URL="mysql://your_user:your_password@127.0.0.1:3306/air_charter_db"

text

**4. Install Dependencies and Initialize**
composer install
php bin/console doctrine:database:create # Only if database doesn't exist
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load # Optional

text
Import demo tables if desired:
mysql -u your_user -p air_charter_db < aircraft.sql
mysql -u your_user -p air_charter_db < routes.sql
mysql -u your_user -p air_charter_db < user.sql

text

**5. Open Your App**
- Visit: [http://localhost](http://localhost)

---

## Common Issues and Tips

| Problem                                               | Solution                                                                                       |
|-------------------------------------------------------|------------------------------------------------------------------------------------------------|
| Cannot resolve DB host (`getaddrinfo ... failed`)     | Make sure `DATABASE_URL` host matches your Docker Compose DB service name or `127.0.0.1` locally|
| 403, 404 errors on `/api` or `/admin/login`           | Ensure correct `DocumentRoot` and `<Directory>` with `AllowOverride All`, `mod_rewrite` enabled|
| SQL batch insert `field doesn't have a default value` | Add required fields to data, update table defaults in SQL if needed                            |
| Apache config lost after Docker restart               | Use `COPY` in Dockerfile from `apache/000-default.conf`, don't edit config inside the container |
| New migration/entity changes                          | After `php bin/console make:migration`, always run `doctrine:migrations:migrate`               |

---

## FAQ

**Q: Do I need to run migrations every time I start the project?**  
A: No. Only run after schema/entity changes or when resetting/initializing a fresh DB.

**Q: Where do I edit my Apache config for the Docker build?**  
A: On your host: `apache/000-default.conf`. Changes go into Docker on rebuild.

**Q: Can I use the project outside of Docker?**  
A: Yes, just configure Apache/PHP/MySQL as above.

---

## Credits and Contribution

- Original author: [Jagadeesh128](https://github.com/Jagadeesh128)
- Contributions welcome via Pull Requests or Issues on GitHub

---
