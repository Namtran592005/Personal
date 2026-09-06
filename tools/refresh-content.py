#!/usr/bin/env python3
"""Re-export dữ liệu từ bản PHP (../Personal) sang bản tĩnh này.

Đọc:
  ../Personal/data/app.sqlite  -> profile, faqs, pages (privacy/terms VI+EN)
  ../Personal/cache/github_repos_*.json -> snapshot fallback cho Projects

Ghi:
  public/data/content.json
  public/data/projects-fallback.json
  (sau đó tự inject lại các đoạn legal vào privacy.html / terms.html
   tại vị trí giữa <div class="lang-block"...> và <span class="updated">)

Chạy:  python tools/refresh-content.py
"""
import html
import io
import json
import os
import re
import sqlite3
import sys

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
SRC = os.path.join(os.path.dirname(ROOT), "Personal")
DB = os.path.join(SRC, "data", "app.sqlite")
CACHE_DIR = os.path.join(SRC, "cache")
PUBLIC = os.path.join(ROOT, "public")


def legal_inline(s):
    def repl_link(m):
        url = m.group(2)
        scheme = url.split(":")[0].lower() if ":" in url else ""
        if scheme and scheme not in ("http", "https", "mailto", "tel"):
            return m.group(0)
        return '<a href="' + url + '">' + m.group(1) + "</a>"

    s = re.sub(r"\[([^\]]+)\]\(([^)\s]+)\)", repl_link, s)
    s = re.sub(r"\*\*([^*]+)\*\*", r"<strong>\1</strong>", s)
    return s


def render_legal(text):
    text = html.escape(text)
    lines = re.split(r"\r?\n", text)
    out, lst, first = [], [], True

    def flush():
        if lst:
            out.append("<ul>" + "".join("<li>" + legal_inline(li) + "</li>" for li in lst) + "</ul>")
            lst.clear()

    for line in lines:
        line = line.strip()
        if line == "":
            flush()
            continue
        m = re.match(r"^###\s+(.*)$", line)
        if m:
            flush()
            out.append("<h4>" + legal_inline(m.group(1)) + "</h4>")
            continue
        m = re.match(r"^##\s+(.*)$", line)
        if m:
            flush()
            out.append("<h3>" + legal_inline(m.group(1)) + "</h3>")
            continue
        m = re.match(r"^-\s+(.*)$", line)
        if m:
            lst.append(m.group(1))
            continue
        flush()
        out.append(("<p class=\"intro\">" if first else "<p>") + legal_inline(line) + "</p>")
        first = False
    flush()
    return "".join(out)


def main():
    if not os.path.exists(DB):
        sys.exit("Không tìm thấy DB: " + DB)
    con = sqlite3.connect(DB)
    con.row_factory = sqlite3.Row

    profile = dict(con.execute("SELECT * FROM profile WHERE id = 1").fetchone())
    faqs = [
        {"q": r["question"], "a": r["answer"]}
        for r in con.execute("SELECT * FROM faqs WHERE visible = 1 ORDER BY sort_order ASC, id DESC")
    ]
    pages = {r["key"]: dict(r) for r in con.execute("SELECT * FROM pages")}

    content = {
        "profile": {k: profile.get(k, "") for k in (
            "name", "title", "bio", "email", "phone", "location", "avatar",
            "social_github", "social_linkedin", "social_twitter", "social_dribbble",
            "social_facebook", "social_instagram", "social_threads", "social_tiktok")},
        "faqs": faqs,
        "github_user": "namtran592005",
    }
    with io.open(os.path.join(PUBLIC, "data", "content.json"), "w", encoding="utf-8") as f:
        json.dump(content, f, ensure_ascii=False, indent=2)

    gh_files = [f for f in os.listdir(CACHE_DIR) if f.startswith("github_repos_") and f.endswith(".json")]
    if gh_files:
        with io.open(os.path.join(CACHE_DIR, gh_files[0]), encoding="utf-8") as f:
            repos = json.load(f)
        with io.open(os.path.join(PUBLIC, "data", "projects-fallback.json"), "w", encoding="utf-8") as f:
            json.dump(repos, f, ensure_ascii=False, indent=1)
        print("projects fallback:", len(repos), "repos")
    else:
        print("Cảnh báo: không thấy cache GitHub, giữ nguyên projects-fallback.json cũ")

    for page, vikey, enkey in (("privacy.html", "privacy", "privacy_en"),
                               ("terms.html", "terms", "terms_en")):
        path = os.path.join(PUBLIC, page)
        with io.open(path, encoding="utf-8") as f:
            doc = f.read()

        def build(key):
            body = render_legal(pages[key]["content"])
            body = body.replace("{name}", html.escape(profile.get("name", "")))
            body = body.replace("{email}", html.escape(profile.get("email", "")))
            return body

        # Thay toàn bộ ruột của từng lang-block (giữ lại <span class="updated">).
        # Whitespace chuẩn hóa cố định để chạy lại nhiều lần không phình file.
        doc = re.sub(r'(<div class="lang-block" data-lang-block="vi">\s*).*?\s*(<span class="updated">)',
                     lambda m: m.group(1) + build(vikey) + "\n                    " + m.group(2),
                     doc, count=1, flags=re.S)
        doc = re.sub(r'(<div class="lang-block" data-lang-block="en" hidden>\s*).*?\s*(<span class="updated">)',
                     lambda m: m.group(1) + build(enkey) + "\n                    " + m.group(2),
                     doc, count=1, flags=re.S)
        with io.open(path, "w", encoding="utf-8") as f:
            f.write(doc)
        print(page, "legal refreshed")

    print("OK - faqs:", len(faqs))


if __name__ == "__main__":
    main()
