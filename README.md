# Orion — Retail Management & Technical Service System

[![Website](https://img.shields.io/badge/Website-android--evolucion.42web.io-blue?style=flat-square&logo=googlechrome)](https://android-evolucion.42web.io)
[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D8.4-777BB4?style=flat-square&logo=php)](https://www.php.net/)
[![Flight PHP](https://img.shields.io/badge/Flight_PHP-3.19-f38b00?style=flat-square)](https://flightphp.com/)
[![Eloquent ORM](https://img.shields.io/badge/Eloquent-13.x-FF2D20?style=flat-square&logo=laravel)](https://laravel.com/)
[![Versión en Español](https://img.shields.io/badge/Idioma-Espa%C3%B1ol-informational?style=flat-square)](LEAME.md)

> 🌐 **Website / Live Demo:** [https://android-evolucion.42web.io](https://android-evolucion.42web.io)  
> 📖 **Language:** English | [Versión en español](LEAME.md)

A lightweight and modern web system designed for retail management, inventory control, sales invoicing, layaway plans, and mobile phone/spare parts technical repair services. Features multi-store branch support and real-time synchronization with Venezuela's official Central Bank (BCV) exchange rate.

---

## 🚀 Key Features

- **Multi-Store & Branch Management**:
  - Allows users to manage multiple store locations or branches independently from a single account.
  - Fast branch switcher located in the top navigation bar.
  - Independent tax ID (RIF), physical address, and contact numbers per store location.

- **Daily Exchange Rate & Official BCV Sync**:
  - Automated integration with external API to retrieve official Central Bank of Venezuela (BCV) exchange rates.
  - Strict middleware enforcement ensuring the daily exchange rate is reviewed and set before registering any sales or financial operations.
  - Custom daily exchange rate adjustments per user.
  - Built-in USD/VES quick currency converter.

- **Inventory & Product Catalog**:
  - Categorization into Phones (`phone`), Accessories (`accessory`), and Spare Parts (`spare_part`).
  - Serial tracking for mobile devices: **IMEI 1**, **IMEI 2**, and batch codes.
  - Multi-location stock allocation using store batches (`batches`).
  - Native USD base pricing with real-time conversion into local currency (VES).

- **Sales & Point of Sale (POS)**:
  - Multi-line shopping cart and checkout.
  - Split and combined payment methods supported:
    - USD Cash
    - Bolívares (VES) Cash
    - Debit/Credit Card (POS Terminal)
    - Bank Transfer / Mobile Payment (*Pago Móvil*)
  - Printable sales receipt / invoice generation (`invoice`).
  - Sale cancellation with automatic stock restoration.

- **Layaway Plans**:
  - Product reservation with initial down payments and subsequent installment tracking.
  - Real-time balance calculation, settlement status, and cancellation flow.

- **Technical Service & Repairs**:
  - Work order registration for device maintenance and repairs (brand, model, customer, intake condition, failure description, and estimated cost).
  - Installment payments and financial tracking for active repairs.
  - Technical intake and delivery printable receipt (`repair-receipt`).

- **Customer Management**:
  - Centralized customer directory recording national ID / Tax ID, phone numbers, email, and address.

- **User Interface & Experience**:
  - Clean and responsive dashboard layout.
  - Automatic **Dark Mode** and **Light Mode** powered by modern CSS `light-dark()`.
  - Non-intrusive floating toast notifications for user actions and feedback.
  - Zero heavy frontend dependencies (semantic HTML5, modern CSS, and lightweight Vanilla JS).

---

## 🛠️ Tech Stack

| Layer | Technology | Description |
| :--- | :--- | :--- |
| **Language** | PHP 8.4+ | Strict typing (`declare(strict_types=1);`), native attributes, constructor property promotion |
| **Router / Framework** | [Flight PHP](https://flightphp.com/) (^3.19) | Fast, lightweight, and extensible micro-framework for web routes and REST APIs |
| **ORM / Database Layer** | [Illuminate Database](https://laravel.com/) (^13.38) | Laravel Eloquent ORM for models, relationships, and automated database schema builders |
| **Authentication** | [Leaf Auth](https://leafphp.dev/) (^5.1) | Session-based authentication and secure password hashing with Bcrypt |
| **Localization** | [Leaf Lingo](https://leafphp.dev/) (^5.0) | Multi-language translation support |
| **HTTP Client** | [Guzzle](https://docs.guzzlephp.org/) (^8.3) | HTTP client for external BCV exchange rate API consumption and integration testing |
| **Environment Configuration** | [Symfony Dotenv](https://symfony.com/doc/current/components/dotenv.html) (^8.1) | Secure parsing and loading of `.env` configuration files |
| **Database** | SQLite (Default) / MySQL | Zero-config local SQLite database with out-of-the-box MariaDB/MySQL support |
| **Testing** | [PHPUnit](https://phpunit.de/) (^13.4) | Unit and HTTP feature integration test suite |

---

## 📁 Project Structure

```text
android-evolucion-ss-fp/
├── app/
│   ├── Http/
│   │   ├── Controllers/          # Resource controllers and single-action controllers (__invoke)
│   │   └── Middleware/           # Middlewares (Auth, BusinessSelected, ExchangeRate, etc.)
│   ├── Models/                   # Eloquent models (User, Business, Product, Sale, Repair...)
│   ├── Services/                 # External service layers (BcvExchangeRate, DailyExchangeRate)
│   ├── Support/                  # Value objects and helpers (PaymentMethod, PaymentDetails)
│   ├── Flash.php                 # Flash message helpers
│   ├── FlashKey.php              # Session flash keys
│   └── Migration.php             # Contract interface for database migrations
├── database/
│   ├── migrations/               # Database migrations executed on application boot
│   └── database.sqlite           # Local SQLite database file (auto-created on first run)
├── lang/
│   └── es.yml                    # Localization messages
├── resources/
│   ├── images/                   # Favicons and static image assets
│   ├── styles/
│   │   └── app.css               # Modern CSS with custom properties and theme tokens
│   └── views/                    # PHP templates and reusable components
│       ├── components/           # UI components (layout, nav, sidebar, toast, etc.)
│       ├── dashboard.php         # Main dashboard view
│       ├── sales.php             # Sales registration and history
│       ├── invoice.php           # Printable sales invoice
│       ├── products.php          # Inventory and product management
│       ├── repairs.php           # Technical repair service view
│       ├── repair-receipt.php    # Technical repair intake and delivery receipt
│       ├── layaways.php          # Layaway management
│       ├── clients.php           # Customer directory
│       ├── businesses.php        # Store locations and business management
│       └── calculator.php        # Exchange rate configuration and converter
├── routes/
│   ├── web.php                   # Web application endpoints and view controllers
│   └── api.php                   # REST API routes (e.g. BCV rate fetching)
├── tests/
│   ├── Feature/                  # HTTP feature integration tests using Guzzle
│   └── Unit/                     # Unit tests
├── .env.example                  # Environment configuration template
├── .htaccess                     # URL rewrite rules for Apache web servers
├── composer.json                 # Project dependencies and PSR-4 autoloading definitions
├── index.php                     # Application entry point and Dependency Injection container
├── LEAME.md                      # Spanish documentation
└── phpunit.xml.dist              # PHPUnit test runner configuration
```

---

## ⚙️ System Requirements

1. **PHP**: Version **8.4 or higher**.
2. **PHP Extensions**:
   - `pdo`
   - `pdo_sqlite` (or `pdo_mysql` when running MySQL/MariaDB)
   - `curl`
   - `mbstring`
   - `openssl`
3. **Composer**: Dependency manager for PHP.
4. **Web Server**: Apache (with `mod_rewrite` enabled) via XAMPP/WAMP/LAMP or the built-in PHP development server.

---

## 📥 Installation & Setup

### 1. Clone the repository
```bash
git clone https://github.com/fadrian06/android-evolucion-ss-fp.git
cd android-evolucion-ss-fp
```

### 2. Install PHP dependencies
Install packages via Composer:
```bash
composer install
```

### 3. Configure environment variables
Copy the `.env.example` file to `.env` (the application will also create this automatically if not present):
```bash
cp .env.example .env
```

Adjust your configuration values in `.env`:
```ini
APP_NAME=Orion

APP_LOCALE=es
LOCALES_STRATEGY=header

BCRYPT_ROUNDS=12

# Database configuration (SQLite by default)
DB_CONNECTION=sqlite
DB_CHARSET=utf8
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=database/database.sqlite
DB_USERNAME=root
DB_PASSWORD=

SESSION_LIFETIME=120

# API key for retrieving BCV exchange rates via DolarVzla
# Get your free API key at: https://dolarvzla.com/settings
DOLARVZLA_KEY=your_api_key_here
```

### 4. Database Initialization & Migraciones
No manual database setup commands are required:
- When starting the application, `index.php` checks for `database/database.sqlite` and creates it automatically if missing.
- All database migration files in `database/migrations/` run automatically on boot.

### 5. Running the Application

#### Option A: Built-in PHP Development Server (Quickest)
```bash
php -S localhost:8000 index.php
```
Open your browser and navigate to: `http://localhost:8000`

#### Option B: Apache / XAMPP
1. Move or clone the project directory into your web root (e.g., `C:\xampp\htdocs\android-evolucion-ss-fp`).
2. Verify that Apache's `mod_rewrite` module is enabled in `httpd.conf`.
3. Start the Apache server from the XAMPP Control Panel.
4. Access the application directly via `http://localhost/android-evolucion-ss-fp`. The `.htaccess` file will route incoming requests to `index.php`.

---

## 🧪 Running Automated Tests

Run the test suite using **PHPUnit**:

```bash
vendor/bin/phpunit
```

> [!NOTE]
> The functional feature tests (`tests/Feature/`) use Guzzle to perform live HTTP requests against `http://localhost`. Ensure your local web server is running and listening on port 80 before executing the feature suite.

---

## 🗺️ Main Application Routes

### Authentication & Access
- `GET /ingresar` — Login page.
- `POST /ingresar` — Authenticate user.
- `GET /registrarse` — Admin registration page.
- `POST /registrarse` — Register new user account.
- `GET /cerrar-sesion` — Logout session.

### Store & Branch Management
- `GET /negocios` — View registered business locations.
- `POST /negocios` — Register new location.
- `POST /negocios/@id` — Update location information.
- `GET /negocios/@id/seleccionar` — Select active store branch.
- `GET /negocios/@id/eliminar` — Delete location.

### Daily Operations & Exchange Rates
- `GET /` — Main overview dashboard.
- `GET /calculadora` — Currency converter and daily exchange rate setup.
- `POST /calculadora/cotizacion` — Save custom exchange rate for the day.
- `GET /api/bcv/exchange-rate` — REST API endpoint returning current BCV exchange rates.

### Products & Inventory
- `GET /productos` — Product catalog and branch stock inventory.
- `POST /productos` — Create product and add stock batches.
- `POST /productos/@id` — Update product details.
- `GET /productos/@id/eliminar` — Remove product.

### Customers
- `GET /clientes` — Customer directory.
- `POST /clientes` — Create new customer.
- `POST /clientes/@id` — Update customer profile.
- `GET /clientes/@id/eliminar` — Delete customer.

### Sales & POS Invoicing
- `GET /ventas` — Sales register and transactions list.
- `POST /ventas` — Process sale with split payment details.
- `GET /ventas/@id` — Display printable invoice receipt (`invoice`).
- `POST /ventas/@id/pagar` — Add subsequent payment to a sale.
- `POST /ventas/@id/anular` — Cancel sale and restore inventory stock.

### Layaway Plans
- `GET /apartados` — List active and historical layaway agreements.
- `POST /apartados` — Create layaway with initial deposit.
- `POST /apartados/@id/pagar` — Register installment payment.
- `POST /apartados/@id/cancelar` — Cancel layaway.

### Technical Service (Repairs)
- `GET /reparaciones` — Technical service work order list.
- `POST /reparaciones` — Create repair order.
- `GET /reparaciones/@id` — Display printable repair intake & delivery receipt (`repair-receipt`).
- `POST /reparaciones/@id/pagar` — Register payment towards a repair ticket.

---

## 🔒 Security & Best Practices

- **Password Security**: Passwords hashed with Bcrypt and configurable cost factor (`BCRYPT_ROUNDS`).
- **SQL Injection Prevention**: Parameterized and prepared queries managed through Eloquent ORM and PDO.
- **Exchange Rate Protection**: Middleware safeguards that prevent financial transactions when the day's exchange rate has not been confirmed.
- **Store-level Isolation**: Foreign key constraints enforce strict isolation of products, invoices, and stock batches per user and active business branch.

---

## 📄 License

Proprietary software developed for commercial and technical retail management. All rights reserved.
