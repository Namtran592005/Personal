# Nam Trần — Personal Website (v2)

Website cá nhân phong cách Apple, viết bằng PHP thuần + SQLite — không cần Composer, không cần cài đặt phức tạp, chạy được ngay trên Caddy / Apache / Nginx có hỗ trợ PHP. Trang chủ là one-page, kèm các trang phụ: chính sách quyền riêng tư, điều khoản sử dụng và bản đồ trang web.

Bản `v2` là thiết kế lại hoàn toàn từ `master`, kèm quản trị nội dung và bảng giá dịch vụ.

## Tính năng

- **One-page** responsive: Hero, Kỹ năng, Kinh nghiệm, Dự án, FAQ, Bảng giá, Liên hệ
- **Trang phụ**: `privacy.php` (chính sách riêng tư), `terms.php` (điều khoản), `sitemap.php` (bản đồ trang web) — nav tự rút gọn chỉ còn "Trang chủ"; nội dung 2 trang pháp lý **chỉnh sửa được** trong Admin → Pages
- **Xáo trộn dự án** bằng GSAP Flip — hiệu ứng bay chéo mượt, có nút bật/tắt, tự tắt trên mobile
- **Dark mode** mặc định, đồng bộ qua `localStorage`, nút toggle trên toàn site (kể cả trang lỗi/check)
- **Cuộn mượt** (`scroll-behavior: smooth`) toàn bộ trang công khai, không áp dụng cho admin
- **Admin quản trị** đầy đủ: hồ sơ, kỹ năng, kinh nghiệm, dự án, FAQ, **bảng giá**, tin nhắn liên hệ, thống kê truy cập
- **Migration tự động** theo `db_version` (`includes/migrations.php`) — chỉ chạy một lần khi schema cũ, seed dữ liệu mẫu lần đầu
- **Schema tập trung** (`includes/schema.php`) — DDL, hằng số (`DB_VERSION`, cache TTL, giới hạn beacon, khóa đăng nhập) dùng chung cho config, migration và test
- **Kiểm tra tự động** (`tools/`): `lint.php` quét syntax toàn bộ PHP (`php -l`), `smoke.php` kiểm tra DB/tables/settings/helpers + HTTP theo `--url`; có sẵn GitHub Actions workflow chạy trên mỗi push
- **Thống kê truy cập** đầy đủ: lượt xem, trình duyệt, ngôn ngữ, **thời gian xem trang** (tổng & trung bình/khách, đo bằng `visibilitychange` + `sendBeacon`, chỉ tính khi tab hiển thị); bảng Visitors **phân trang**
- **Upload ảnh đại diện** ngay trong Admin → Profile (PNG/JPG/WEBP, tối đa 2MB, tự validate MIME, lưu vào `media/`), hero hiển thị ảnh từ profile kèm fallback `media/avt.png`
- **Tin nhắn liên hệ** có **phân trang + lọc** theo trạng thái (All/Unread/Read) và tìm kiếm theo tên/email/chủ đề
- **Bảng giá dịch vụ** CRUD linh hoạt: giá, gói "phổ biến", badge, tính năng, ẩn/hiện, thứ tự
- **Form liên hệ** gửi mail SMTP (Gmail App Password) + lưu vào DB, chế độ **gửi ẩn danh** thu gọn, thông báo **toast glass**
- **Bảo mật đăng nhập**: khóa 5 lần sai trong 15 phút theo IP + token CSRF, **đổi mật khẩu** ngay trong Admin → Change Password; trang đăng nhập admin nền **liquid glass** (blur + saturate) trên video nền
- **2FA (TOTP)** qua Google Authenticator / Authy / 1Password — bật/tắt ngay trong Admin → Security, đăng nhập 2 bước (mật khẩu → mã 6 chữ số)
- **Bảo mật phiên**: tự đăng xuất sau 30 phút không hoạt động, phiên gắn với IP + trình duyệt (fingerprint), regenerate session id khi đăng nhập
- **Giới hạn tần suất toàn cục**: chặn tự động (HTTP 429) khi một IP vượt 100 request/60 giây, miễn trừ admin đã đăng nhập
- **Trang pháp lý** viết bằng **plain-text có cú pháp nhẹ** (`##`, `###`, `-`, `**đậm**`, `[link](url)`) — hiển thị đẹp như HTML nhưng dễ chỉnh trong Admin → Pages, hỗ trợ placeholder `{name}`/`{email}`
- **Đa ngôn ngữ** Việt / English cho toàn bộ trang công khai — chuyển ngôn ngữ bằng nút **VI | EN** trên nav (lưu vào cookie `lang` 365 ngày), nội dung trang pháp lý có bản riêng cho từng ngôn ngữ, chọn ngôn ngữ mặc định trong Admin → Settings
- **Nút gọi nhanh** FAB glass (icon điện thoại) — bấm tự gọi số từ profile
- **Nút trở lên đầu trang** và **nút gọi** đều bật/tắt được trong Admin → Settings
- **Trang trí line-art**: trái tim hồng có chữ "love you" (phần liên hệ), dấu chấm hỏi vàng (phần FAQ)
- **Lấy repo GitHub** tự động (cache 30 phút)
- **Trang kiểm tra hệ thống** `check.php` và trang **lỗi** `error.php` (404/403/500) thiết kế riêng
- **Sitemap** `sitemap.xml` sinh động theo `BASE_PATH`, kèm các mục trên trang chủ
- **Portable**: dùng `BASE_PATH` tự tính từ thư mục gốc — bỏ vào bất kỳ vị trí nào cũng chạy
- Toàn bộ thư viện (GSAP, Phosphor icons, font Inter) được lưu local, không phụ thuộc CDN

## Yêu cầu

- PHP **8.0+** với các extension: `pdo_sqlite`, `mbstring`, `session`, `fileinfo`, `json`
- Web server bất kỳ (Caddy / Apache / Nginx / `php -S`)
- **Quyền ghi vào thư mục `data/`, `cache/` và `media/`** (để upload ảnh đại diện)

## Cài đặt

```bash
# 1. Clone repo
git clone https://github.com/Namtran592005/Personal.git
cd Personal
git checkout v2

# 2. Tạo file cấu hình từ bản mẫu
cp .env.example .env
#   rồi sửa ADMIN_PASSWORD và các thông số SMTP trong .env

# 3. Trỏ web root tới thư mục này rồi mở trình duyệt
```

- Database `data/app.sqlite` và bảng dữ liệu sẽ **tự tạo lần đầu** chạy (kèm dữ liệu mẫu).
- Thư mục `data/`, `cache/`, file `.env` đã được gitignore — không nên commit.
- Muốn test nhanh: `php -S localhost:8080` rồi mở `http://localhost:8080`.

## Quản trị

- Đăng nhập: `http://your-site/admin/login.php`
- Mật khẩu lấy từ `ADMIN_PASSWORD` trong `.env`
- Bảo mật: khóa tạm thời sau 5 lần đăng nhập sai trong 15 phút (theo IP) + token CSRF; **2FA (TOTP)** tùy chọn qua Admin → Security; phiên hết hạn sau 30 phút không hoạt động
- Các trang chính:
  - `admin/dashboard.php` — thống kê
  - `admin/profile.php` — hồ sơ cá nhân
  - `admin/skills.php` — kỹ năng
  - `admin/experiences.php` — kinh nghiệm làm việc (timeline)
  - `admin/projects.php` — dự án
  - `admin/pricing.php` — quản lý bảng giá
  - `admin/faqs.php` — câu hỏi thường gặp
  - `admin/messages.php` — tin nhắn liên hệ (phân trang + lọc/tìm kiếm)
  - `admin/analytics.php` — thống kê truy cập (biểu đồ line theo ngày, bảng Visitors phân trang)
  - `admin/pages.php` — chỉnh sửa nội dung trang Chính sách & Điều khoản (VI + EN)
  - `admin/security.php` — 2FA (TOTP) & cấu hình phiên
  - `admin/settings.php` — cấu hình + ngôn ngữ mặc định + Export JSON + Reset
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
├── .env                   # Cấu hình nhạy cảm (đã gitignore)
├── .env.example           # Bản mẫu cấu hình
├── admin/                 # Khu vực quản trị
├── admin-assets/admin.css
├── includes/              # config, auth, github, mail, track, schema, migrations, legal_defaults...
├── tools/                 # lint.php, smoke.php — kiểm tra tự động
├── partials/              # header, nav, hero, projects, pricing, footer...
├── assets/
│   ├── js/                # gsap, Flip, chart (local)
│   ├── icons/phosphor/    # icon font (local)
│   ├── fonts/inter/       # font Inter (local)
│   └── video/             # bg.mp4 & hero.mp4 (video nền, local)
├── media/avt.png          # Ảnh đại diện
├── data/                  # SQLite (gitignore)
└── cache/                 # Cache GitHub (gitignore)
```

## Ghi chú

- Nhánh `master` là bản gốc của dự án; `v2` là bản thiết kế lại này.
- Mọi tham số hiển thị section (Kỹ năng, Dự án, FAQ, Bảng giá, Liên hệ) đều bật/tắt được trong Admin → Settings.
- Nếu đổi tên/URL web, mọi đường dẫn tự động tính qua `BASE_PATH`, không cần sửa code.
