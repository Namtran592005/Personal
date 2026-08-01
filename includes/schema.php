<?php
// Centralized schema definitions: DDL, seed defaults, and schema version.
// Shared by config.php (table bootstrap), migrations.php (one-time runs),
// and tools/smoke.php (verification).

const DB_VERSION = 2;

const GITHUB_CACHE_TTL = 1800;   // seconds (30 min)
const GITHUB_MAX_REPOS = 50;
const BEACON_MAX_SECONDS = 3600;
const LOGIN_MAX_ATTEMPTS = 5;
const LOGIN_LOCK_MINUTES = 15;

function schemaTables(): array {
    return [
        'users' => "CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            password_hash TEXT NOT NULL,
            created_at TEXT DEFAULT (datetime('now'))
        )",

        'profile' => "CREATE TABLE IF NOT EXISTS profile (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL DEFAULT '',
            title TEXT NOT NULL DEFAULT '',
            bio TEXT,
            email TEXT NOT NULL DEFAULT '',
            phone TEXT NOT NULL DEFAULT '',
            location TEXT NOT NULL DEFAULT '',
            social_github TEXT DEFAULT '',
            social_linkedin TEXT DEFAULT '',
            social_twitter TEXT DEFAULT '',
            social_dribbble TEXT DEFAULT '',
            social_facebook TEXT DEFAULT '',
            social_instagram TEXT DEFAULT '',
            social_threads TEXT DEFAULT '',
            social_tiktok TEXT DEFAULT '',
            updated_at TEXT DEFAULT (datetime('now'))
        )",

        'skills' => "CREATE TABLE IF NOT EXISTS skills (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            category TEXT NOT NULL,
            icon TEXT NOT NULL DEFAULT 'ph-code',
            description TEXT,
            tags TEXT,
            sort_order INTEGER DEFAULT 0,
            visible INTEGER DEFAULT 1,
            created_at TEXT DEFAULT (datetime('now'))
        )",

        'projects' => "CREATE TABLE IF NOT EXISTS projects (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            description TEXT,
            tech_stack TEXT,
            github_url TEXT DEFAULT '',
            live_url TEXT DEFAULT '',
            image TEXT DEFAULT '',
            sort_order INTEGER DEFAULT 0,
            visible INTEGER DEFAULT 1,
            created_at TEXT DEFAULT (datetime('now'))
        )",

        'experiences' => "CREATE TABLE IF NOT EXISTS experiences (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            company TEXT NOT NULL,
            location TEXT DEFAULT '',
            start_date TEXT,
            end_date TEXT,
            description TEXT,
            sort_order INTEGER DEFAULT 0,
            visible INTEGER DEFAULT 1,
            created_at TEXT DEFAULT (datetime('now'))
        )",

        'messages' => "CREATE TABLE IF NOT EXISTS messages (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT NOT NULL,
            subject TEXT DEFAULT '',
            message TEXT NOT NULL,
            is_read INTEGER DEFAULT 0,
            is_anonymous INTEGER DEFAULT 0,
            created_at TEXT DEFAULT (datetime('now'))
        )",

        'faqs' => "CREATE TABLE IF NOT EXISTS faqs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            question TEXT NOT NULL,
            answer TEXT NOT NULL,
            sort_order INTEGER DEFAULT 0,
            visible INTEGER DEFAULT 1,
            created_at TEXT DEFAULT (datetime('now'))
        )",

        'pricing_plans' => "CREATE TABLE IF NOT EXISTS pricing_plans (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            price TEXT NOT NULL DEFAULT '',
            price_note TEXT DEFAULT '',
            badge TEXT DEFAULT '',
            features TEXT DEFAULT '',
            button_text TEXT DEFAULT 'Bắt đầu ngay',
            popular INTEGER DEFAULT 0,
            sort_order INTEGER DEFAULT 0,
            visible INTEGER DEFAULT 1,
            created_at TEXT DEFAULT (datetime('now'))
        )",

        'analytics' => "CREATE TABLE IF NOT EXISTS analytics (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            ip TEXT UNIQUE NOT NULL DEFAULT '',
            user_agent TEXT DEFAULT '',
            referrer TEXT DEFAULT '',
            screen_w INTEGER DEFAULT 0,
            screen_h INTEGER DEFAULT 0,
            language TEXT DEFAULT '',
            country TEXT DEFAULT '',
            city TEXT DEFAULT '',
            visits INTEGER DEFAULT 1,
            pages TEXT DEFAULT '',
            time_spent INTEGER DEFAULT 0,
            first_seen TEXT DEFAULT (datetime('now')),
            last_seen TEXT DEFAULT (datetime('now'))
        )",

        'settings' => "CREATE TABLE IF NOT EXISTS settings (
            key TEXT PRIMARY KEY NOT NULL,
            value TEXT NOT NULL DEFAULT ''
        )",

        'login_attempts' => "CREATE TABLE IF NOT EXISTS login_attempts (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            ip TEXT NOT NULL,
            attempted_at TEXT DEFAULT (datetime('now'))
        )",

        'pages' => "CREATE TABLE IF NOT EXISTS pages (
            key TEXT PRIMARY KEY NOT NULL,
            title TEXT NOT NULL DEFAULT '',
            content TEXT NOT NULL DEFAULT '',
            updated_at TEXT DEFAULT (datetime('now'))
        )",
    ];
}

function schemaSettingsDefaults(): array {
    return [
        'show_skills' => '1', 'show_projects' => '1', 'show_pricing' => '1', 'show_faq' => '1',
        'show_contact' => '1', 'show_experience' => '1',
        'enable_analytics' => '1', 'enable_contact_form' => '1', 'github_username' => 'namtran592005',
        'show_back_top' => '1', 'show_call_fab' => '1',
    ];
}
