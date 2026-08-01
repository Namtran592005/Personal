# Nam Trần — Personal Website (v2)

Website cá nhân phong cách Apple, viết bằng PHP thuần + SQLite — không cần Composer, không cần cài đặt phức tạp, chạy được ngay trên Caddy / Apache / Nginx có hỗ trợ PHP. Trang chủ là one-page, kèm các trang phụ: chính sách quyền riêng tư, điều khoản sử dụng và bản đồ trang web.

Bản `v2` là thiết kế lại hoàn toàn từ `master`, kèm quản trị nội dung và bảng giá dịch vụ.

## Tính năng

- **One-page** responsive: Hero, Kỹ năng, Dự án, FAQ, Bảng giá, Liên hệ
- **Trang phụ**: `privacy.php` (chính sách riêng tư), `terms.php` (điều khoản), `sitemap.php` (bản đồ trang web) — nav tự rút gọn chỉ còn "Trang chủ"; nội dung 2 trang pháp lý **chỉnh sửa được** trong Admin → Pages
- **Xáo trộn dự án** bằng GSAP Flip — hiệu ứng bay chéo mượt, có nút bật/tắt, tự tắt trên mobile
- **Dark mode** mặc định, đồng bộ qua `localStorage`, nút toggle trên toàn site (kể cả trang lỗi/check)
- **Cuộn mượt** (`scroll-behavior: smooth`) toàn bộ trang công khai, không áp dụng cho admin
- **Admin quản trị** đầy đủ: hồ sơ, kỹ năng, dự án, FAQ, **bảng giá**, tin nhắn liên hệ, thống kê truy cập
- **Bảng giá dịch vụ** CRUD linh hoạt: giá, gói "phổ biến", badge, tính năng, ẩn/hiện, thứ tự
- **Form liên hệ** gửi mail SMTP (Gmail App Password) + lưu vào DB, chế độ **gửi ẩn danh** thu gọn, thông báo **toast glass**
- **Bảo mật đăng nhập**: khóa 5 lần sai trong 15 phút theo IP + token CSRF; trang đăng nhập admin nền **liquid glass** (blur + saturate) trên video nền
- **Trang pháp lý** viết bằng **plain-text có cú pháp nhẹ** (`##`, `###`, `-`, `**đậm**`, `[link](url)`) — hiển thị đẹp như HTML nhưng dễ chỉnh trong Admin → Pages, hỗ trợ placeholder `{name}`/`{email}`
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
- Quyền ghi vào thư mục `data/` và `cache/`

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
- Bảo mật: khóa tạm thời sau 5 lần đăng nhập sai trong 15 phút (theo IP) + token CSRF
- Các trang chính:
  - `admin/dashboard.php` — thống kê
  - `admin/profile.php` — hồ sơ cá nhân
  - `admin/skills.php` — kỹ năng
  - `admin/projects.php` — dự án
  - `admin/pricing.php` — quản lý bảng giá
  - `admin/faqs.php` — câu hỏi thường gặp
  - `admin/messages.php` — tin nhắn liên hệ
  - `admin/analytics.php` — thống kê truy cập (biểu đồ line theo ngày)
  - `admin/pages.php` — chỉnh sửa nội dung trang Chính sách & Điều khoản
  - `admin/settings.php` — cấu hình + Export JSON + Reset

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
├── includes/              # config, auth, github, mail, track, legal_defaults...
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
