# Deploying Samburu EWS to cPanel

Step-by-step guide for deploying to a cPanel shared-hosting environment.

---

## Prerequisites

- cPanel hosting with **PHP 8.0+** and **MySQL 5.7+** (or MariaDB 10.3+)
- An available **addon domain** or **subdomain** pointed to a folder
- FTP/SFTP access  **or**  cPanel File Manager

---

## 1. Create the MySQL Database

1. Log in to **cPanel → MySQL Databases**.
2. Create a new database, e.g. `youruser_samburu_ews`.
3. Create a new MySQL user with a strong password.
4. **Add the user to the database** with **All Privileges**.
5. Open **phpMyAdmin** and select the new database.
6. Click **Import → Choose File** → select `migrations/schema.sql` → **Go**.
7. *(Optional)* Import `migrations/seed.sql` for demo contact messages.

---

## 2. Upload Files

### Recommended structure

```
public_html/
└── samburu-ews/          ← your site folder (addon domain root)
    ├── app/              ← upload entire app/ folder here
    │   ├── config/
    │   ├── data/
    │   ├── partials/
    │   └── services/
    ├── api/              ← from public/api/
    ├── assets/           ← from public/assets/
    ├── .htaccess         ← from public/.htaccess
    ├── index.php         ← from public/index.php
    ├── findings.php
    ├── current-alert.php
    ├── (all other .php from public/)
    └── ...
```

> **Key point:** The contents of `public/` go into your site root.
> The `app/` folder sits **alongside** the public files (same level).

### Upload steps

1. **FTP/SFTP:** Upload the `app/` folder as-is.
2. Upload **all files from `public/`** into the site root.
3. The `migrations/` and `docs/` folders do **not** need uploading.

---

## 3. Update Database Credentials

Edit `app/config/config.php` on the server:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'youruser_samburu_ews');   // ← your cPanel DB name
define('DB_USER', 'youruser_dbuser');         // ← your cPanel DB user
define('DB_PASS', 'YOUR_STRONG_PASSWORD');    // ← your password
```

Also update the admin password hash:

```php
// Generate a hash locally:  php -r "echo password_hash('YourNewPassword', PASSWORD_BCRYPT);"
define('ADMIN_PASSWORD_HASH', '$2y$10$...your_hash_here...');
```

---

## 4. Set PHP Version

1. Go to **cPanel → MultiPHP Manager** (or **Select PHP Version**).
2. Select your domain / folder.
3. Set PHP version to **8.0**, **8.1**, or **8.2**.
4. Ensure these extensions are enabled: `pdo`, `pdo_mysql`, `json`, `mbstring`.

---

## 5. Verify

1. Visit `https://yourdomain.com/samburu-ews/` — you should see the home page.
2. Visit `https://yourdomain.com/samburu-ews/api/findings-data.php` — should return JSON.
3. Visit `https://yourdomain.com/samburu-ews/admin.php` — login with your admin password.
4. Test the contact form at `https://yourdomain.com/samburu-ews/contact.php`.

---

## Troubleshooting

| Issue | Fix |
|---|---|
| 500 Internal Server Error | Check PHP version ≥ 8.0; check `.htaccess` syntax |
| Database connection failed | Verify `config.php` credentials; ensure DB user has privileges |
| API returns empty | Confirm `app/data/*.json` files were uploaded |
| CSS/JS not loading | Verify `assets/` folder is in the site root |
| CSRF error on forms | Ensure PHP sessions are enabled in cPanel |

---

## Security Checklist

- [ ] Change `ADMIN_PASSWORD_HASH` to a strong bcrypt hash
- [ ] Set `DB_PASS` to a strong unique password
- [ ] Ensure `.htaccess` is active (Apache `AllowOverride All`)
- [ ] Remove `seed.sql` demo data from production database
- [ ] Set `APP_DEBUG` to `false` (or don't define it) in production
