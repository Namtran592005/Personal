# Nam Trần — Personal Website

Website cá nhân phong cách Apple, viết bằng **PHP thuần + SQLite** — không Composer, không framework, không build step, chạy được ngay trên Caddy / Apache / Nginx có PHP 8.0+. Trang chủ là one-page portfolio + CMS mini với admin đầy đủ.

## Nhánh

- **`main`** — nhánh chính (mặc định): thiết kế lại hoàn toàn, đang phát triển.
- **`legacy`** — bản gốc cũ, giữ làm archive (không phát triển nữa).

## Tính năng

### Frontend
- **One-page** responsive: Hero, Kỹ năng, Kinh nghiệm, Dự án, FAQ, Bảng giá, Liên hệ
- **Đa ngôn ngữ** Việt / English cho toàn bộ trang công khai — chuyển bằng nút **VI | EN** trên nav (lưu cookie `lang` 365 ngày)
- **Smooth language switch**: chuyển ngôn ngữ **không reload** (AJAX + `DOMParser`), bật/tắt được trong Admin → Settings; khi tắt thì switcher là navigation thường
- **Xáo trộn dự án** bằng GSAP Flip — hiệu ứng bay chéo mượt, có nút bật/tắt, chạy được cả trên mobile (bật/tắt trong Admin → Settings → *Shuffle Effect on Mobile*)
- **Dark mode** mặc định, đồng bộ qua `localStorage`, nút toggle trên toàn site
- **Cuộn mượt** (`scroll-behavior: smooth`) toàn bộ trang công khai, không áp dụng cho admin
- **Trang trí line-art**: dấu chấm hỏi vàng (FAQ), hình hoa nền
- **Nút gọi nhanh** FAB glass (bấm tự gọi số từ profile) + **nút trở lên đầu trang** — bật/tắt trong Admin → Settings
- **Assets module hóa**: CSS chia 8 module (`assets/css/`), JS chia theo chức năng (`assets/js/`), cache-bust bằng `filemtime()`
- **Portable**: dùng `BASE_PATH` tự tính từ thư mục gốc — bỏ vào vị trí bất kỳ cũng chạy
- Toàn bộ thư viện (GSAP, Phosphor icons, font Inter) lưu local, không phụ thuộc CDN

### Trang phụ
- `privacy.php` / `terms.php` — nội dung chỉnh sửa được trong Admin → Pages, viết bằng **plain-text có cú pháp nhẹ** (`##`, `###`, `-`, `**đậm**`, `[link](url)`, placeholder `{name}`/`{email}`), có bản riêng cho từng ngôn ngữ
- `sitemap.php` + `sitemap.xml` — bản đồ trang web sinh động theo `BASE_PATH`
- `check.php` — kiểm tra hệ thống (PHP version, extension, DB, settings, helpers, HTTP)
- `error.php` — trang lỗi 404/403/500 thiết kế riêng
- Nav ở các trang phụ tự rút gọn chỉ còn "Trang chủ"

### Admin
- Hồ sơ, kỹ năng, kinh nghiệm, dự án, FAQ, **bảng giá**, thống kê truy cập, trang pháp lý
- **Upload ảnh đại diện** trong Admin → Profile (PNG/JPG/WEBP, ≤2MB, validate MIME, lưu `media/`)
- **Đổi video nền** hero & trang login admin trong Admin → Videos (MP4/WEBM/OGG, ≤20MB, có nút Reset về mặc định)
- **Bảng giá dịch vụ** CRUD: giá, gói "phổ biến", badge, tính năng, ẩn/hiện, thứ tự
- **Sidebar giữ vị trí cuộn** khi điều hướng giữa các trang (lưu `sessionStorage`)

### Bảo mật
- **2FA (TOTP)** Google Authenticator / Authy / 1Password — bật/tắt trong Admin → Security, đăng nhập 2 bước
- **Khóa đăng nhập**: 5 lần sai / 15 phút theo IP + token CSRF trên mọi form POST admin
- **Bảo mật phiên**: tự đăng xuất sau 30 phút không hoạt động, gắn với IP + fingerprint, regenerate session id khi đăng nhập
- **Giới hạn tần suất toàn cục**: chặn HTTP 429 khi một IP vượt 100 request/60 giây (miễn trừ admin đã đăng nhập)
- Trang đăng nhập admin nền **liquid glass** trên video nền

### Thống kê
- Lượt xem, trình duyệt, ngôn ngữ, **thời gian xem trang** (tổng & trung bình/khách, đo bằng `visibilitychange` + `sendBeacon`, chỉ tính khi tab hiển thị, cap 3600s)
- Chart line theo ngày (Chart.js local) + bảng Visitors phân trang

### Hệ thống
- **Schema tập trung** (`includes/schema.php`) — DDL, hằng số (`DB_VERSION`, cache TTL, giới hạn beacon, khóa đăng nhập) dùng chung
- **Migration tự động** theo `db_version` (`includes/migrations.php`) — chạy một lần, idempotent, dựng cấu trúc DB ban đầu (nội dung để trống, người dùng tự nhập qua admin)
- **Lấy repo GitHub** tự động (cache 30 phút theo từng username, fallback cache cũ khi API lỗi, nút Clear Cache trong Admin → Settings)
- **Kiểm tra tự động** (`tools/`): `lint.php` quét syntax toàn bộ PHP, `smoke.php` kiểm tra DB/tables/settings/helpers + HTTP theo `--url`; GitHub Actions chạy trên mỗi push `main`

## Yêu cầu

- PHP **8.0+** với các extension: `pdo_sqlite`, `mbstring`, `session`, `fileinfo`, `json`
- Web server bất kỳ (Caddy / Apache / Nginx / `php -S`)
- **Quyền ghi vào thư mục `data/`, `cache/` và `media/`** (để upload ảnh đại diện)

## Cài đặt

```bash
# 1. Clone repo (nhánh mặc định `main`)
git clone https://github.com/Namtran592005/Personal.git
cd Personal

# 2. Trỏ web root tới thư mục này rồi mở trình duyệt
```

- Database `data/app.sqlite` và bảng dữ liệu sẽ **tự tạo lần đầu** chạy (không kèm nội dung mẫu — để trống cho người dùng nhập qua admin).
- Thư mục `data/`, `cache/` đã được gitignore — không nên commit.
- Muốn test nhanh: `php -S localhost:8080` rồi mở `http://localhost:8080`.

## Quản trị

- Đăng nhập: `http://your-site/admin/login.php`
- **Lần đầu chạy / quên hay bị lộ mật khẩu**: upload `admin/setup.php` lên server → mở `http://your-site/admin/setup.php` → nhập mật khẩu admin + **Database key** (bỏ trống để tự sinh) → **rồi xoá file `admin/setup.php` khỏi server**. Nếu đã có tài khoản admin, phải nhập **Database key hiện tại** (từ `includes/db-key.php`) làm bằng chứng sở hữu trước khi đặt lại mật khẩu; để trống New database key thì giữ nguyên key cũ. Mật khẩu được lưu **chỉ dạng bcrypt hash** trong bảng `users` (KHÔNG dùng `.env`/file cấu hình).
- **Database key (mã hoá dữ liệu nhạy cảm)**: `admin/setup.php` lưu key vào `includes/db-key.php` (file PHP — trình duyệt không tải được vì nó được thực thi chứ không serve nội dung; đã gitignore). Key dùng để mã hoá giá trị nhạy cảm trong DB (2FA secret) bằng AES-256-GCM (`includes/crypto.php`), chỉ fingerprint SHA-256 của key lưu trong bảng `settings`. **Hãy sao lưu key** — mất key thì dữ liệu đã mã hoá không đọc được.
- **Chặn tải DB qua web (Caddy/Nginx — `.htaccess` vô dụng)**: đưa DB ra ngoài web root bằng code, không cần sửa cấu hình webserver. Copy `includes/config-local.php.example` → `includes/config-local.php` rồi sửa `$DATA_DIR_OVERRIDE` thành đường dẫn tuyệt đối PHP-writable nằm **ngoài** thư mục web (vd `/var/www/private/personal-data`). Lần chạy đầu, DB cũ ở `data/app.sqlite` được tự copy sang. Nếu đường dẫn không dùng được, app tự fallback về `data/` để site không chết. `check.php` sẽ báo FAIL nếu DB còn nằm trong web root.
- Bảo mật: khóa tạm thời sau 5 lần sai trong 15 phút (theo IP) + giới hạn rate login (10 POST/60s/IP) + token CSRF; **2FA (TOTP)** tùy chọn qua Admin → Security (secret được mã hoá trong DB); phiên hết hạn sau 30 phút không hoạt động; cookie `Secure` + `HttpOnly` + `SameSite=Strict` (yêu cầu HTTPS)
- Các trang chính:
  - `admin/dashboard.php` — thống kê
  - `admin/profile.php` — hồ sơ cá nhân + ảnh đại diện
  - `admin/skills.php` — kỹ năng
  - `admin/experiences.php` — kinh nghiệm (timeline)
  - `admin/projects.php` — dự án
  - `admin/pricing.php` — bảng giá dịch vụ
  - `admin/faqs.php` — câu hỏi thường gặp
  - `admin/analytics.php` — thống kê truy cập
  - `admin/pages.php` — nội dung Chính sách & Điều khoản (VI + EN)
  - `admin/videos.php` — video nền hero & trang login
  - `admin/security.php` — 2FA (TOTP) & cấu hình phiên
  - `admin/settings.php` — feature toggles (gồm shuffle trên mobile) + ngôn ngữ mặc định + Clear GitHub cache + Export JSON + Reset
  - `admin/password.php` — đổi mật khẩu admin

## Cấu trúc thư mục

```
├── index.php              # Trang chủ
├── privacy.php            # Chính sách quyền riêng tư
├── terms.php              # Điều khoản sử dụng
├── sitemap.php            # Bản đồ trang web
├── error.php              # Trang lỗi 404/403/500
├── check.php              # Kiểm tra hệ thống
├── sitemap.xml            # Sitemap (PHP, sinh XML)
├── admin/                 # Khu vực quản trị
├── admin-assets/admin.css
├── includes/              # config, schema, migrations, auth, functions (facade: helpers + legal),
│                          # github, track, lang, i18n, totp, rate-limit, legal_defaults
├── tools/                 # lint.php, smoke.php — kiểm tra tự động
├── partials/              # header, nav, hero, projects, pricing, footer...
├── assets/
│   ├── css/               # base, decor, nav, hero, sections, layout, components, theme
│   ├── js/                # darkmode, analytics, ui, lang-switch, shuffle, main, gsap/Flip/chart
│   ├── icons/phosphor/    # icon font (local)
│   ├── fonts/inter/       # font Inter (local)
│   └── video/             # bg.mp4 & hero.mp4 (video nền, local)
├── media/avt.png          # Ảnh đại diện; video nền upload lưu media/videos/ (gitignore)
├── data/                  # SQLite (gitignore)
└── cache/                 # Cache GitHub (gitignore)
```
## Kiểm tra

```bash
php tools/lint.php                          # quét syntax toàn bộ .php
php tools/smoke.php                         # kiểm tra DB/tables/settings/helpers
php tools/smoke.php --url http://localhost:8080   # + hit các trang live
```

## Ghi chú

- Mọi tham số hiển thị section (Kỹ năng, Dự án, FAQ, Bảng giá, Liên hệ) đều bật/tắt được trong Admin → Settings.
- Nếu đổi tên/URL web, mọi đường dẫn tự động tính qua `BASE_PATH`, không cần sửa code.
- Nhánh `legacy` là bản gốc cũ của dự án, chỉ đọc.
