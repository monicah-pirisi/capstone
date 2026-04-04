# Samburu EWS — Early Warning System Recommender & Educative Platform

A PHP 8+ web platform that combines NDMA scientific data, KMD seasonal forecasts, and Samburu indigenous knowledge into actionable, multi-channel drought early warnings for pastoralist communities.

---

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.0+ (no frameworks) |
| Database | MySQL 5.7+ / MariaDB 10.3+ |
| Frontend | HTML5, CSS3 (custom properties), Vanilla JavaScript |
| Charts | Chart.js 4.x (CDN) |
| Fonts | Inter (Google Fonts) |

---

## Quick Start (Local Development)

### 1. Clone / download

```bash
cd ~/Downloads    # or wherever you want
# the project is in capstone_web/
```

### 2. Create the database

```bash
mysql -u root -p -e "CREATE DATABASE samburu_ews CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p samburu_ews < migrations/schema.sql
mysql -u root -p samburu_ews < migrations/seed.sql   # optional demo data
```

### 3. Update config

Edit `app/config/config.php` with your local MySQL credentials:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'samburu_ews');
define('DB_USER', 'root');
define('DB_PASS', '');
```

### 4. Run the PHP built-in server

```bash
cd public
php -S localhost:8000
```

Open **http://localhost:8000** in your browser.

> **Admin login:** visit `/admin.php` — default password is `admin123` (change in production).

---

## Project Structure

```
capstone_web/
├── app/
│   ├── config/
│   │   └── config.php            # DB creds, base_url(), constants
│   ├── data/                     # JSON data files (editable)
│   │   ├── interviews.json       # n=384 interview findings
│   │   ├── ndma_latest.json      # NDMA drought bulletin
│   │   ├── kmd_summary.json      # KMD seasonal forecast
│   │   ├── indigenous_indicators.json
│   │   ├── barriers.json         # 6 communication barriers
│   │   ├── recommendations.json  # 5 evidence-based recs
│   │   ├── stakeholders.json     # 5 stakeholder groups
│   │   └── channels_content.json # Message templates
│   ├── partials/
│   │   ├── header.php
│   │   ├── nav.php
│   │   └── footer.php
│   └── services/
│       ├── Auth.php              # Session admin auth
│       ├── Csrf.php              # CSRF token management
│       ├── DataRepository.php    # Safe JSON file reader
│       ├── Db.php                # PDO singleton
│       ├── RiskEngine.php        # Weighted risk scorer
│       └── Validator.php         # Input validation
├── public/                       # Document root
│   ├── api/
│   │   ├── findings-data.php     # GET — chart data
│   │   ├── current-alert-data.php# GET — risk assessment
│   │   └── contact-submit.php    # POST — form handler
│   ├── assets/
│   │   ├── css/styles.css        # Design system
│   │   └── js/
│   │       ├── main.js           # Nav toggle, toasts
│   │       ├── findings.js       # Chart.js dashboard
│   │       ├── currentAlert.js   # Alert page logic
│   │       └── ussdSimulator.js  # USSD menu engine
│   ├── .htaccess
│   ├── index.php
│   ├── problem.php
│   ├── solution.php
│   ├── findings.php
│   ├── current-alert.php
│   ├── stakeholders.php
│   ├── channels.php
│   ├── prototype.php
│   ├── resources.php
│   ├── ussd-simulator.php
│   ├── contact.php
│   ├── admin.php
│   └── logout.php
├── migrations/
│   ├── schema.sql
│   └── seed.sql
├── docs/
│   └── DEPLOY_CPANEL.md
└── README.md
```

---

## Key Features

- **Risk Engine** — 6-indicator weighted scoring (NDVI, rainfall, livestock, water, food security, indigenous) → 5 alert levels
- **Findings Dashboard** — Interactive Chart.js charts from community interview data (n=384)
- **Multi-Channel Dissemination** — Auto-generated WhatsApp, Facebook, radio (30s/60s), and USSD templates
- **USSD Simulator** — Phone-like UI with bilingual menu (English / Samburu)
- **Indigenous Knowledge** — 8 traditional indicators with drought/good-season signals
- **Stakeholder Profiles** — 5 groups with per-phase response actions
- **Contact System** — CSRF-protected form with honeypot, validation, and admin dashboard

---

## Deploying to cPanel

See [docs/DEPLOY_CPANEL.md](docs/DEPLOY_CPANEL.md) for the full guide. Summary:

1. Create a MySQL database in cPanel and import `schema.sql`
2. Upload `app/` folder and contents of `public/` into your site root (`public_html/your-folder/`)
3. Update `app/config/config.php` with your DB credentials and a new admin password hash
4. Set PHP version to 8.0+ in cPanel MultiPHP Manager
5. Visit your site URL to verify

---

## Security Notes

- Change `ADMIN_PASSWORD_HASH` in `config.php` before deploying
- Never commit real database credentials
- The `.htaccess` blocks access to `app/`, `migrations/`, and `docs/`
- CSRF tokens protect all form submissions
- Honeypot field filters bot submissions
- PDO prepared statements prevent SQL injection

---

## License

This project was built as a capstone research platform for Samburu County drought early-warning systems.
