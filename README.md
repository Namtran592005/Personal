# Personal Static — bản HTML/CSS/JS thuần của web Personal

Bản tĩnh 100% (không PHP, không SQLite, không admin) để deploy lên
**Cloudflare Workers (Static Assets)** hoặc **Cloudflare Pages**.
Nguồn giao diện + dữ liệu: `../Personal` (bản PHP + SQLite).

## Demo local

```bash
cd public
python -m http.server 8080
# mở http://localhost:8080
```

## Cấu trúc

```
Personal-node/
├── wrangler.toml          # Workers Static Assets: serve ./public
├── public/                # thư mục deploy
│   ├── index.html         # trang chủ (VI mặc định, chuyển EN bằng JS, không reload)
│   ├── privacy.html       # chính sách (cả 2 ngôn ngữ trong 1 file)
│   ├── terms.html         # điều khoản (cả 2 ngôn ngữ trong 1 file)
│   ├── sitemap.html       # bản đồ trang
│   ├── 404.html           # trang lỗi (Workers tự dùng khi not_found_handling = none)
│   ├── sitemap.xml        # URL tuyệt đối theo domain tranvohoangnam.id.vn
│   ├── robots.txt         # TODO: thay domain trong dòng Sitemap:
│   ├── _headers           # security headers + cache (Workers Static Assets tự hiểu)
│   ├── assets/            # css (8 module), js, icons phosphor, font inter, video, favicon
│   │   └── js/app.js      # i18n VI/EN + tải Projects live từ GitHub API (mới của bản tĩnh)
│   ├── media/avt.png      # ảnh đại diện
│   └── data/              # content.json (profile+faqs) + projects-fallback.json
└── tools/refresh-content.py  # đồng bộ lại dữ liệu từ ../Personal
```

## Khác biệt so với bản PHP

| Bản PHP | Bản tĩnh này |
|---|---|
| `?lang=` + cookie + AJAX reload từng phần | 1 file, JS đổi VI/EN tức thì, lưu `localStorage.lang` |
| Projects từ cache server 30 phút | `fetch` live `api.github.com` từ trình duyệt, cache `localStorage` 30 phút, fallback `data/projects-fallback.json` khi API lỗi/rate-limit |
| Analytics, admin, SQLite | Bỏ hẳn (Workers không chạy PHP/SQLite). Muốn thống kê: bật **Cloudflare Web Analytics** (free, 1 click) |
| Sections Skills / Experience / Pricing | Đang trống trong DB nên ẩn (giống bản PHP khi `count == 0`); khi nào nhập liệu ở bản PHP thì chạy `refresh-content.py` rồi bổ sung section theo mẫu |
| Form liên hệ | Bản PHP cũng đã bỏ form, chỉ còn mailto + mạng xã hội — giữ nguyên |

## Deploy lên Cloudflare Workers

```bash
npm i -g wrangler
wrangler login
wrangler deploy
```

Xong sẽ có URL `https://personal-static.<subdomain>.workers.dev`.
Gắn domain riêng: Dashboard → Workers → `personal-static` → Settings → Domains & Routes.

> Workers Static Assets tự chuẩn hóa pretty URL: `/privacy.html` ↔ `/privacy`
> đều mở được (không cần file `_redirects` — đã xóa vì rule rewrite `.html`
> gây redirect loop với cơ chế này).

Hoặc deploy bằng **Cloudflare Pages**: tạo project → Upload assets → kéo thả thư mục `public/`.

Domain chính: `https://tranvohoangnam.id.vn` (sitemap.xml + robots.txt đã trỏ đúng).

## Đồng bộ dữ liệu mới từ bản PHP

Sửa profile/FAQ/pages ở trang admin của bản PHP xong:

```bash
python tools/refresh-content.py
```

Script đọc `../Personal/data/app.sqlite` + cache GitHub, ghi lại
`content.json`, `projects-fallback.json` và ruột legal của `privacy.html`/`terms.html`.
Riêng hero tên/title/FAQ text cứng trong `index.html` — sửa tay cho khớp nếu đổi profile.
