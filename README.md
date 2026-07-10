# ⚡ TechHub Website — PHP + MySQL Edition

A fully functional, branded 10-page website with a **PHP backend** and **MySQL database**
for real form submissions, dynamic blog posts, newsletter subscribers, and an admin dashboard.

---

## 📁 Complete File Structure

```
techhub/
│
├── index.html                  ← Homepage
├── 404.html                    ← Custom 404 error page
├── 500.html                    ← Custom 500 error page
├── .htaccess                   ← Apache security, routing, caching rules
├── setup.php                   ← One-time environment checker (DELETE after use)
│
├── css/
│   └── style.css               ← External stylesheet (all 10 pages)
│
├── js/
│   └── main.js                 ← External JavaScript with PHP API integration
│
├── pages/                      ← All 9 inner HTML pages
│   ├── about.html
│   ├── services.html
│   ├── products.html
│   ├── pricing.html
│   ├── blog.html               ← Loads posts dynamically from MySQL
│   ├── team.html
│   ├── contact.html            ← Submits to contact.php via AJAX
│   ├── faq.html
│   └── careers.html
│
├── contact.php                 ← Handles contact form → saves to DB + sends email
├── newsletter.php              ← Handles newsletter subscribe → saves to DB
├── unsubscribe.php             ← Handles unsubscribe via token link
│
├── config/
│   ├── db.php                  ← MySQL PDO connection (edit credentials here)
│   └── mailer.php              ← Email helper functions
│
├── api/
│   ├── blog.php                ← GET blog posts from MySQL (JSON API)
│   ├── team.php                ← GET team members from MySQL (JSON API)
│   └── jobs.php                ← GET job listings from MySQL (JSON API)
│
├── admin/
│   └── index.php               ← Admin dashboard (view submissions, subscribers)
│
└── database/
    └── schema.sql              ← Full MySQL schema + seed data (run this first)
```

---

## 🚀 Setup Instructions (XAMPP — Local)

### Step 1 — Install XAMPP
Download from [apachefriends.org](https://www.apachefriends.org) and install.
Start **Apache** and **MySQL** from the XAMPP Control Panel.

### Step 2 — Copy files
Place the entire `techhub/` folder inside:
```
C:\xampp\htdocs\techhub\          (Windows)
/Applications/XAMPP/htdocs/techhub/  (macOS)
```

### Step 3 — Create the database
1. Open **phpMyAdmin**: http://localhost/phpmyadmin
2. Click **New** → name it `techhub_db` → click **Create**
3. Click the `techhub_db` database → click **Import**
4. Choose `database/schema.sql` → click **Go**

### Step 4 — Configure database credentials
Edit `config/db.php` and update:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'techhub_db');
define('DB_USER', 'root');        // default XAMPP user
define('DB_PASS', '');            // default XAMPP password (blank)
```

### Step 5 — Run the setup checker
Open: http://localhost/techhub/setup.php

All checks should show **PASS**. If anything fails, follow the instructions shown.

### Step 6 — Open the website
http://localhost/techhub/index.html

### Step 7 — Access the admin panel
http://localhost/techhub/admin/
Password: `techhub2025!`  *(change this in admin/index.php before going live)*

---

## 🌐 Setup Instructions (Live Hosting / cPanel)

### Step 1 — Upload files
Upload all files to your `public_html/` folder (or a subdirectory) via FTP or cPanel File Manager.

### Step 2 — Create MySQL database
In **cPanel → MySQL Databases**:
1. Create a new database, e.g. `youruser_techhub`
2. Create a MySQL user with a strong password
3. Add the user to the database with **All Privileges**

### Step 3 — Import schema
In **phpMyAdmin** → select your database → **Import** → upload `database/schema.sql`

### Step 4 — Update config
Edit `config/db.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'youruser_techhub');
define('DB_USER', 'youruser_dbuser');
define('DB_PASS', 'yourStrongPassword');
```

Edit `config/mailer.php`:
```php
define('ADMIN_EMAIL', 'your@email.com');
define('FROM_EMAIL',  'no-reply@yourdomain.com');
define('SITE_URL',    'https://www.yourdomain.com');
```

### Step 5 — Verify & delete setup file
Visit: `https://yourdomain.com/setup.php`
Once all checks pass → **delete setup.php from the server**.

---

## 🗄️ Database Tables

| Table | Purpose |
|-------|---------|
| `contact_submissions` | Stores all contact form enquiries |
| `newsletter_subscribers` | Stores newsletter email addresses |
| `blog_posts` | Stores blog articles (dynamic) |
| `team_members` | Stores team member profiles |
| `job_listings` | Stores open job positions |

---

## 🔌 PHP API Endpoints

| Endpoint | Method | Description |
|----------|--------|-------------|
| `contact.php` | POST | Submit contact form |
| `newsletter.php` | POST | Subscribe to newsletter |
| `unsubscribe.php?token=XXX` | GET | Unsubscribe from newsletter |
| `api/blog.php` | GET | Get all published blog posts |
| `api/blog.php?category=Cloud` | GET | Filter posts by category |
| `api/blog.php?slug=post-slug` | GET | Get single post |
| `api/team.php` | GET | Get all active team members |
| `api/team.php?department=Leadership` | GET | Filter by department |
| `api/jobs.php` | GET | Get all open job listings |

---

## 🛡️ Admin Dashboard

**URL:** `/admin/`
**Default password:** `techhub2025!`

**Features:**
- View all contact form submissions with status (New / Read / Replied / Closed)
- See newsletter subscriber count and recent sign-ups
- Dashboard stats: total enquiries, new enquiries, subscribers, blog posts, open jobs

> ⚠️ Change the admin password in `admin/index.php` before deploying to production.
> For production, replace the simple password check with proper session-based authentication.

---

## 💰 Pricing Currency

All prices are displayed in **Pakistani Rupees (PKR / ₨)**:

| Plan | Monthly | Annual |
|------|---------|--------|
| Starter | ₨13,900 | ₨11,100 |
| Growth | ₨41,900 | ₨33,500 |
| Enterprise | Custom | Custom |

---

## ✅ Requirements

| Requirement | Minimum |
|-------------|---------|
| PHP | 7.4+ (8.x recommended) |
| MySQL | 5.7+ or MariaDB 10.3+ |
| Apache | 2.4+ with mod_rewrite enabled |
| PHP Extensions | `pdo_mysql`, `mbstring` |

> Works perfectly on **XAMPP** (local) and any shared hosting with **cPanel + phpMyAdmin**.

---

## 🔒 Security Features

- All user inputs sanitised with `htmlspecialchars()` and `filter_var()`
- Database queries use **PDO prepared statements** (prevents SQL injection)
- `config/` and `database/` directories blocked via `.htaccess`
- Security headers set via `.htaccess` (X-Frame-Options, XSS Protection)
- HTTPS redirect ready (uncomment in `.htaccess`)
- Unsubscribe uses random 64-character tokens (not guessable IDs)

---

## 🔧 Troubleshooting

| Problem | Solution |
|---------|---------|
| Blank page on contact form | Check PHP error log; verify `config/db.php` credentials |
| "Database connection failed" | Confirm MySQL is running and credentials are correct |
| Emails not sending | Configure SMTP via PHPMailer for reliable delivery |
| Blog posts not loading | Check browser console for API errors; verify `api/blog.php` path |
| Admin page not loading | Ensure session support is enabled on your server |

---

*© 2025 TechHub Ltd. All rights reserved.*
