# Personal Website

Personal website built with PHP, SQLite, and Caddy — includes portfolio, blog/contact, admin panel, and GitHub project showcase.

## Features

- **Portfolio** — skills, projects (GitHub repos), experience timeline, FAQs
- **Contact form** — anonymous mode support, email via PHPMailer/SMTP
- **Admin panel** — manage skills, projects, messages, FAQs, analytics
- **GitHub integration** — auto-fetch repos with cache, exclusions, sort by newest
- **Dark mode** — persists in localStorage, respects `prefers-color-scheme`
- **Analytics** — lightweight page-view tracking (SQLite)
- **Security** — password-only admin login (ADMIN_PASSWORD env), CSP locked to `'self'`

## Requirements

- PHP 8.0+
- SQLite (pdo_sqlite)
- Caddy (or any PHP-capable server)
- Composer dependencies: `phpmailer/phpmailer`

## Setup

```bash
# 1. Clone & install
composer install

# 2. Configure environment
cp .env.example .env   # fill in ADMIN_PASSWORD, SMTP_*, etc.

# 3. Init database
php includes/init_db.php

# 4. Run
caddy run               # or php -S localhost:8000
```

## Project Structure

```
├── admin/              # Admin panel pages
├── admin-assets/       # Admin CSS/JS
├── assets/             # Fonts, icons, images, site.css
├── includes/           # PHP libs (auth, config, db, github, mail)
├── partials/           # Header, footer, nav
├── cache/              # GitHub repo cache (gitignored)
├── data/               # SQLite database (gitignored)
├── index.php           # Public homepage
├── error.php           # Error pages (403/404/500)
├── check.php           # System health check
└── sitemap.xml         # SEO sitemap
```
