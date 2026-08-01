# AGENTS.md

Hướng dẫn cho AI agent làm việc trong repo này. Đọc hết trước khi sửa code.

## Tổng quan dự án

Website cá nhân **PHP thuần + SQLite** (không Composer, không framework, không build step). One-page portfolio kiểu Apple + CMS mini với đầy đủ admin. Chạy trên Caddy/Apache/Nginx có PHP 8.0+.

- Nhánh chính: `v2` (thiết kế lại hoàn toàn). Nhánh `master` là bản gốc cũ.
- Repo: https://github.com/Namtran592005/Personal
- Deploy thực tế: Caddy, web root trỏ vào thư mục dự án.

## Kiến trúc lõi (đọc trước khi sửa bất kỳ thứ gì)

### Khởi động (`includes/config.php`)
Mọi request bắt đầu bằng `require 'includes/config.php'`. File này:
1. `require_once includes/schema.php` (DDL + hằng số tập trung)
2. Nạp password admin từ env / `.env`
3. Thiết lập session (`HttpOnly`, `SameSite=Strict`)
4. Mở SQLite tại `data/app.sqlite` (WAL mode)
5. Tạo toàn bộ bảng bằng `schemaTables()` (idempotent)
6. Đọc `db_version` từ bảng `settings`; nếu `< DB_VERSION` thì `require includes/migrations.php`
7. Nạp toàn bộ `settings` vào biến global `$settings`

**Quy ước global quan trọng**: sau khi require config, luôn có sẵn:
- `$pdo` — PDO object (hoặc `null`), `$dbAvailable` — bool
- `$settings` — mảng `key => value` từ bảng settings
- `BASE_PATH` — hằng số đường dẫn gốc (portable)

### Schema & hằng số (`includes/schema.php`)
- `schemaTables(): array` — DDL 12 bảng (một nguồn duy nhất).
- `schemaSettingsDefaults(): array` — defaults cho bảng settings.
- Hằng số: `DB_VERSION`, `GITHUB_CACHE_TTL` (1800), `GITHUB_MAX_REPOS` (50), `BEACON_MAX_SECONDS` (3600), `LOGIN_MAX_ATTEMPTS` (5), `LOGIN_LOCK_MINUTES` (15).

**LUẬT**: nếu thêm cột/bảng/const, sửa **schema.php** chứ KHÔNG sửa DDL trong config.php. Khi cần migrate DB cũ, bump `DB_VERSION` và thêm ALTER/seed vào `includes/migrations.php`.

### Migration (`includes/migrations.php`)
Chạy 1 lần khi `db_version < DB_VERSION`. Toàn bộ phải **idempotent** (chạy lại an toàn): ALTER bọc `try/catch`, seed dùng `INSERT OR IGNORE` hoặc kiểm tra `COUNT(*)`. Cuối cùng `INSERT OR REPLACE ... db_version`. Có guard `MIGRATIONS_RAN` để tránh chạy 2 lần trong 1 request.

### Auth (`includes/auth.php`)
- `requireLogin()` — chuyển hướng về `/admin/login.php` nếu chưa đăng nhập, gọi `enforceSessionSecurity()`.
- `isLoggedIn()` — kiểm tra `$_SESSION['user_id']`.
- CSRF: `generateCsrfToken()` / `validateCsrfToken()`. **Mọi form POST admin PHẢI có hidden `_csrf` và validate**.
- Brute force: `loginLockMinutes()` + `recordLoginAttempt()` + `clearLoginAttempts()` — khóa 5 lần sai/15 phút theo IP, chạy server-side mỗi request.
- Login chấp nhận password từ `.env` (rehash bcrypt tự động) hoặc hash trong bảng `users`.
- Đăng nhập 2 bước: `login($password)` chỉ verify → trả `?int`; nếu `twoFactorEnabled()` thì set `$_SESSION['2fa_user']` chuyển `admin/2fa.php`, nếu không gọi `completeLogin($userId)` (regenerate session id + set `last_activity`/`fingerprint`).
- Session hardening: `enforceSessionSecurity()` — idle timeout `SESSION_TIMEOUT_MINUTES` (30p) + fingerprint IP/UA, hủy session khi khớp sai.

### 2FA (TOTP, `includes/totp.php`)
- Thuần PHP, RFC 6238: `generateTotpSecret()`, `totpCode()`, `verifyTotp()` (±1 step), `totpProvisioningUri()`. Bật/tắt ở `admin/security.php` (generate secret → xác nhận code → enable).

### Rate limit toàn cục (`includes/rate-limit.php`)
- Hook trong `config.php` sau khi load settings: mỗi request (trừ admin đã đăng nhập) upsert `rate_limits`, quá `RATE_LIMIT_MAX` (100) / `RATE_LIMIT_WINDOW` (60s) theo IP → HTTP 429.

### i18n (`includes/lang.php` + `includes/i18n.php`)
- Phân giải: `?lang=vi|en` → cookie `lang` → `settings.default_lang` → `vi`. Switch ở nav dùng `langUrl()`.
- `t('key')` trả chuỗi theo `$LANG` từ `includes/i18n.php`. **KHÔNG dùng URL prefix `/en/`**.
- Toàn bộ trang công khai (index, partials, privacy, terms, sitemap, check, error) đã i18n. Admin không i18n (trừ login/2fa/security tiếng Anh).
- Trang pháp lý có 4 keys trong bảng `pages`: `privacy`/`terms`/`privacy_en`/`terms_en`, biên tập ở `admin/pages.php`.
- `error.php` và `check.php` tự detect lang inline (không phụ thuộc DB).

## Cấu trúc thư mục

```
├── index.php          # Trang chủ: nạp data từ DB + include partials theo settings
├── privacy.php        # Chính sách (nội dung từ bảng pages, seed legal_defaults)
├── terms.php          # Điều khoản (tương tự privacy)
├── sitemap.php        # Bản đồ trang web (HTML)
├── sitemap.xml        # Sitemap XML động
├── check.php          # Trang kiểm tra hệ thống
├── error.php          # Trang lỗi 404/403/500
├── admin/             # Khu vực quản trị (mỗi file 1 module CRUD)
├── admin-assets/admin.css
├── includes/          # config, schema, migrations, auth, functions, github, mail, track, legal_defaults, email-template
├── partials/          # header, nav, hero, skills, experiences, projects, faq, pricing, contact, footer
├── assets/            # site.css, js (gsap/Flip/chart), icons phosphor, fonts Inter, video (bg/hero)
├── media/avt.png      # Ảnh đại diện mặc định; avatar upload lưu media/avatar-*.png|jpg|webp
├── data/app.sqlite    # DB (gitignored, tự tạo lần đầu)
├── cache/             # Cache GitHub repos (gitignored)
├── tools/             # lint.php, smoke.php (test tự động)
└── .github/workflows/ # CI workflow (lint + smoke trên mỗi push)
```

## Quy ước code

- **Mọi output user-generated phải qua `h()`** (`htmlspecialchars`). Có helper `h()` trong `includes/functions.php`.
- **SQL luôn dùng prepared statement** — không nối biến vào SQL. Nếu phải nối tên cột/bảng, dùng whitelist mảng cứng (vd `admin/profile.php`).
- KHÔNG thêm comment khi viết code mới trừ khi thật cần thiết (mã hiện tại có một số comment tiếng Việt/Anh từ trước).
- Đường dẫn luôn dùng `BASE_PATH` (portable, không hardcode). CSS cache-bust qua `filemtime()` trong `partials/header.php`.
- **Tính portable (triển khai bất kỳ đâu, không phụ thuộc domain)**: toàn bộ link `href`/`src`/`action`/`fetch`/`header('Location')` PHẢI dùng `BASE_PATH` hoặc dạng tương đối (`?q=`, `#anchor`, `mailto:`, `tel:`, `../`). Không hardcode domain/đường dẫn tuyệt đối. `BASE_PATH` được chuẩn hóa forward-slash trong `config.php`, `error.php`, `check.php` (Windows/Linux đều chạy). URL tuyệt đối động (og:url, sitemap.xml, email template) dựng từ `$_SERVER['HTTPS']`+`HTTP_HOST`+`BASE_PATH`. Lưu ý: `$_SERVER['SCRIPT_NAME']` ĐÃ bao gồm `BASE_PATH` nên khi dùng nó không nối thêm `BASE_PATH` (xem `langUrl()` trong `includes/lang.php`).
- Error handling kiểu codebase: bọc `try { ... } catch (PDOException $e) {}` — DB có thể unavailable, page phải fallback mềm.
- Các trang admin: bắt đầu bằng 4 `require_once` (config, auth, functions) + `requireLogin()`, đặt `$page = '<tên>'` trước khi include sidebar để highlight menu.

## Module chính

### Settings / Feature toggles
Bảng `settings`, các key toggle: `show_skills`, `show_experience`, `show_projects`, `show_faq`, `show_pricing`, `show_contact`, `enable_analytics`, `enable_contact_form`, `show_back_top`, `show_call_fab`, `github_username`.
- Trang chủ check: `if (($settings['show_x'] ?? '1') === '1') include 'partials/x.php';`
- **Khi thêm section mới**: thêm toggle key vào `schemaSettingsDefaults()` (migrations), `admin/settings.php` (cả `$keys` và mảng `$toggles`), index.php, `partials/nav.php` (link), `sitemap.php` + `sitemap.xml` (nếu có).

### Analytics (thời gian xem trang)
- `includes/track.php` — nhận 2 loại request:
  - Image beacon (`?path=...&sw=&sh=&lang=`) → upsert 1 dòng/IP.
  - Duration beacon (`?duration=N`) → `UPDATE analytics SET time_spent += N, last_seen=now WHERE ip=?`.
- `partials/header.php` — JS gửi pixel lúc load + `sendBeacon` lúc `pagehide` (chỉ tính khi tab visible qua `visibilitychange`), cap `BEACON_MAX_SECONDS`.
- `admin/analytics.php` — thẻ thống kê + chart (Chart.js local) + bảng visitors phân trang (`vpage`, 20/trang). Cột `pages` hiển thị gọn `Nx /trang-đầu` kèm tooltip.

### Avatar đại diện
- Cột `avatar` trong bảng `profile` (`DB_VERSION=3`, ALTER idempotent trong migrations). Giá trị lưu đường dẫn tương đối như `media/avatar-<ts>-<rand>.<ext>`.
- Upload ở `admin/profile.php`: form `enctype=multipart/form-data`, validate MIME bằng `finfo` (png/jpeg/webp, ≤2MB), lưu bằng `move_uploaded_file` vào `media/`.
- Hiển thị: `partials/hero.php` + admin preview dùng `$profile['avatar'] ?: 'media/avt.png'` với `onerror` fallback về `media/avt.png`.

### Tin nhắn liên hệ
- `admin/messages.php` có phân trang (`page`, 15/trang) + lọc `status` (all/unread/read) + tìm `q` theo name/email/subject. Phải nhớ: biến `$page` dùng cho sidebar highlight — đừng dùng chung làm biến số trang.

### GitHub repos (`includes/github.php`)
`fetchGithubRepos($username, $max=GITHUB_MAX_REPOS, $exclude)` — cache JSON 30 phút trong `cache/`, fallback cache cũ nếu API lỗi. Projects trên trang chủ lấy từ đây (không phải bảng `projects`). Bảng `projects` chỉ dùng nếu bạn bật tự CRUD (hiện trang chủ dùng GitHub).

### Legal pages (privacy/terms)
- Nội dung DB (bảng `pages`), seed từ `includes/legal_defaults.php` (plain-text tiếng Việt).
- Render bởi `renderLegalText()` + `legalInline()` trong `includes/functions.php`. Cú pháp: `##`/`###` tiêu đề, `-` list, `**đậm**`, `[text](url)`, placeholder `{name}`/`{email}`.
- Migration tự động: nếu bản cũ chứa HTML (`<`) sẽ bị thay bằng plain-text seed.

### Mail (`includes/mail.php`)
SMTP tự viết bằng socket (fsockopen + STARTTLS), không dùng thư viện. Cấu hình từ `.env`: `SMTP_HOST/PORT/USER/PASS/FROM/FROM_NAME`. `sendMail()` trả `false` nếu không cấu hình.

### Contact form (`includes/contact-handler.php`)
POST JSON: lưu vào bảng `messages` + gửi mail SMTP. Chế độ ẩn danh (checkbox) bỏ trống name/email. `enable_contact_form` phải = 1.

## DB: 12 bảng
`users`, `profile`, `skills`, `projects`, `experiences`, `messages`, `faqs`, `pricing_plans`, `analytics`, `settings`, `login_attempts`, `pages`. Schema chi tiết ở `includes/schema.php`.

## Kiểm tra / Test

Môi trường máy dev KHÔNG có PHP CLI — **không chạy được `php -l` tại đây**. Với agent: khi sửa PHP, tự rà soát kỹ cú pháp bằng mắt (khớp ngoặc nhọn `<?php ... ?>`, dấu ngoặc, toán tử `?.`, match/case). Test thật chạy trên server:

```bash
php tools/lint.php                          # quét syntax toàn bộ .php
php tools/smoke.php                         # kiểm tra DB/tables/settings/helpers
php tools/smoke.php --url http://localhost:8080   # + hit các trang live
```

GitHub Actions chạy tự động (lint + smoke) trên mỗi push `v2`/`master`.

## Deploy

- Bản trên server tại `/var/www/html/Personal` — **không phải máy dev này**.
- Các file phụ thuộc chéo (`includes/schema.php`, `config.php`, `migrations.php`, `auth.php`, `functions.php`, `github.php`, `track.php`, `header.php`) phải deploy cùng lúc.
- Lần deploy đầu sau migration: server tự chạy migrations (vì `db_version` chưa có → chạy 1 lần).
- Sau khi deploy nên chạy `tools/lint.php` + `tools/smoke.php` trên server.

## Git

- Nhánh `v2`. Commit message tiếng Anh, phong cách: `feat: ...` / `fix: ...` / `docs: ...`.
- Chỉ commit khi được yêu cầu. Sau commit push lên `origin/v2`.
- `data/`, `cache/`, `.env` đã gitignore.
- Lưu ý: Git cảnh báo LF→CRLF (không ảnh hưởng).

## Lịch sử commit gần đây (mốc kiến trúc)
- `4aebcec` — schema tập trung (`includes/schema.php`), tools lint/smoke, CI workflow
- `bf8f3a0` — experiences CRUD + timeline, đổi mật khẩu admin, migration một lần, prepared statements
- `2be252c` — time-on-page analytics, cột Pages gọn
- `66c30ef` — admin UX: toggles 2 cột, form ẩn, View Site
- `4710ea1` — zoom màn hình lớn
- `8c666a9` — liquid glass login, legal pages plain-text, FAB, bg video
