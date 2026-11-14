#!/usr/bin/env python3
import argparse
import re
import subprocess
import sys
from dataclasses import dataclass
from pathlib import Path

DEFAULT_ROOT = Path(__file__).resolve().parent

JS_EXTENSIONS = {".ts", ".tsx", ".js", ".jsx", ".mjs", ".cjs"}
IGNORED_DIR_NAMES = {".git", "vendor", "node_modules", "storage", ".idea", ".vscode", ".phpunit.cache", "dist"}
IGNORED_PATH_PREFIXES = (
    "bootstrap/cache/",
    "public/build/",
    "resources/js/components/ui/",
    "resources/js/actions/",
    "resources/js/routes/",
    "resources/js/wayfinder/",
    "docs/",
)
IGNORED_SUFFIXES = (".d.ts", ".min.js", ".min.css")
STRICT_TYPES_PREFIXES = ("src/", "app/", "tests/", "database/")
ALLOWED_ENV_KEYS = {
    "APP_KEY",
    "APP_ENV",
    "APP_DEBUG",
    "DB_CONNECTION",
    "DB_HOST",
    "DB_PORT",
    "DB_DATABASE",
    "DB_USERNAME",
    "DB_PASSWORD",
    "DB_SOCKET",
}
CI_MARKERS = (
    ".github/workflows",
    ".gitlab-ci.yml",
    ".circleci",
    "azure-pipelines.yml",
    "Jenkinsfile",
    "bitbucket-pipelines.yml",
)
MAX_FILE_LINES = 400
REGEX_PREVIOUS_CHARS = set("(,=:[!&|?{};>")
REGEX_KEYWORDS = {"return", "typeof", "case", "in", "of", "void", "delete"}

RULES = {
    "KOMENTAR": "Tidak boleh ada komentar apa pun (//, #, /* */, {{-- --}}, <!-- -->) pada kode proyek.",
    "STRICT_TYPES": "File PHP di src/, app/, tests/, database/ wajib memiliki declare(strict_types=1);",
    "DOMAIN_MURNI": "Lapisan Domain tidak boleh bergantung pada Illuminate, App, Application, atau Infrastructure.",
    "APPLICATION_MURNI": "Lapisan Application tidak boleh memakai Eloquent, HTTP, App, atau Infrastructure.",
    "DELIVERY_TIPIS": "app/Http dan app/Console tidak boleh memakai Eloquent, fasad DB, atau Infrastructure.",
    "ELOQUENT_DI_INFRA": "Model Eloquent hanya boleh ada di src/<Context>/Infrastructure.",
    "FUNGSI_DILARANG": "Dilarang dd, dump, var_dump, print_r, ray, console.log, serta alert/confirm/prompt bawaan browser.",
    "ENV_HANYA_DB": "Variabel lingkungan hanya APP_KEY dan DB_*; pengaturan lain disimpan di database dan diatur dari dashboard admin.",
    "IKON_REACT_ICONS": "Ikon hanya dari react-icons; lucide-react, heroicons, dan sejenisnya dilarang.",
    "MODAL_BUKAN_TOAST": "Notifikasi memakai AlertDialog modal di tengah; toast/sonner dilarang.",
    "FONT_ROBOTO": "Font hanya Roboto yang di-host sendiri; tanpa Google Fonts, Bunny Fonts, atau font lain.",
    "TANPA_CI": "Proyek tidak memakai CI; berkas konfigurasi CI dilarang ada.",
    "LOG_RAHASIA": "Log tidak boleh memuat password.",
    "UKURAN_BERKAS": f"Peringatan bila berkas lebih dari {MAX_FILE_LINES} baris.",
}


@dataclass(frozen=True)
class Temuan:
    path: str
    line: int
    rule: str
    message: str
    level: str = "error"


def language_of(path):
    name = path.name
    if name.endswith(".blade.php"):
        return "blade"
    if path.suffix == ".php":
        return "php"
    if path.suffix in JS_EXTENSIONS:
        return "js"
    if path.suffix == ".css":
        return "css"
    return None


def is_ignored(rel):
    if any(rel.startswith(prefix) for prefix in IGNORED_PATH_PREFIXES):
        return True
    if rel.endswith(IGNORED_SUFFIXES):
        return True
    return any(part in IGNORED_DIR_NAMES for part in rel.split("/")[:-1])


def skip_string(text, start, quote, multiline):
    i = start + 1
    n = len(text)
    while i < n:
        c = text[i]
        if c == "\\":
            i += 2
            continue
        if c == quote:
            return i + 1
        if c == "\n" and not multiline:
            return i
        i += 1
    return n


def regex_allowed(text, index):
    j = index - 1
    while j >= 0 and text[j] in " \t\r\n":
        j -= 1
    if j < 0:
        return True
    if text[j] in REGEX_PREVIOUS_CHARS:
        return True
    match = re.search(r"[A-Za-z_$]+$", text[: j + 1])
    return bool(match and match.group(0) in REGEX_KEYWORDS)


def skip_regex(text, start):
    i = start + 1
    n = len(text)
    in_class = False
    while i < n:
        c = text[i]
        if c == "\n":
            return 0
        if c == "\\":
            i += 2
            continue
        if c == "[":
            in_class = True
        elif c == "]":
            in_class = False
        elif c == "/" and not in_class:
            i += 1
            while i < n and text[i].isalpha():
                i += 1
            return i
        i += 1
    return 0


def line_end(text, index):
    end = text.find("\n", index)
    return len(text) if end == -1 else end


def scan_code_comments(text, language):
    spans = []
    length = len(text)
    stack = [["code", 0]]
    i = 0
    while i < length:
        mode = stack[-1]
        ch = text[i]
        nxt = text[i + 1] if i + 1 < length else ""
        if mode[0] == "tpl":
            if ch == "\\":
                i += 2
            elif ch == "`":
                stack.pop()
                i += 1
            elif ch == "$" and nxt == "{":
                stack.append(["expr", 0])
                i += 2
            else:
                i += 1
            continue
        if mode[0] == "expr":
            if ch == "{":
                mode[1] += 1
            elif ch == "}":
                if mode[1] == 0:
                    stack.pop()
                    i += 1
                    continue
                mode[1] -= 1
        if ch in "'\"":
            i = skip_string(text, i, ch, language == "php")
            continue
        if ch == "`" and language == "js":
            stack.append(["tpl", 0])
            i += 1
            continue
        if ch == "/" and nxt == "*":
            end = text.find("*/", i + 2)
            end = length if end == -1 else end + 2
            spans.append((i, end))
            i = end
            continue
        if language in ("php", "js") and ch == "/" and nxt == "/":
            if language == "js" and i > 0 and text[i - 1] == ":":
                i += 2
                continue
            end = line_end(text, i)
            spans.append((i, end))
            i = end
            continue
        if language == "php" and ch == "#":
            if nxt == "[":
                i += 1
                continue
            if i == 0 and nxt == "!":
                i = line_end(text, i)
                continue
            end = line_end(text, i)
            spans.append((i, end))
            i = end
            continue
        if language == "js" and ch == "/" and regex_allowed(text, i):
            end = skip_regex(text, i)
            if end:
                i = end
                continue
        i += 1
    return spans


def comment_spans(text, language):
    if language == "blade":
        pattern = re.compile(r"\{\{--.*?--\}\}|<!--.*?-->", re.S)
        return [(m.start(), m.end()) for m in pattern.finditer(text)]
    return scan_code_comments(text, language)


def line_of(text, offset):
    return text.count("\n", 0, offset) + 1


def strip_comments(text, language):
    spans = comment_spans(text, language)
    if not spans:
        return text
    widened = []
    for start, end in spans:
        before = start
        while before > 0 and text[before - 1] in " \t":
            before -= 1
        after = end
        while after < len(text) and text[after] in " \t":
            after += 1
        if before > 0 and text[before - 1] == "{" and after < len(text) and text[after] == "}":
            start, end = before - 1, after + 1
        begin = text.rfind("\n", 0, start) + 1
        finish = line_end(text, end)
        if text[begin:start].strip() == "" and text[end:finish].strip() == "":
            widened.append((begin, min(finish + 1, len(text))))
        else:
            widened.append((start, end))
    result = text
    for start, end in reversed(widened):
        result = result[:start] + result[end:]
    return re.sub(r"[ \t]+$", "", result, flags=re.M)


def check_comments(rel, text, language):
    return [
        Temuan(rel, line_of(text, start), "KOMENTAR", "Komentar tidak diperbolehkan.")
        for start, _ in comment_spans(text, language)
    ]


USE_PATTERN = re.compile(r"^\s*use\s+([^;\s]+)", re.M)
ENV_CALL_PATTERN = re.compile(r"""\benv\(\s*['"]([A-Z0-9_]+)['"]""")
PHP_DEBUG_PATTERN = re.compile(r"(?<![\w>:$])(dd|dump|var_dump|print_r|ray)\s*\(")
PHP_ENV_PATTERN = re.compile(r"(?<![\w>:$])env\s*\(")
JS_CONSOLE_PATTERN = re.compile(r"\bconsole\.(log|debug|info|trace)\s*\(")
JS_NATIVE_DIALOG_PATTERN = re.compile(r"(?<![\w.$])(alert|confirm|prompt)\s*\(|\bwindow\.(alert|confirm|prompt)\b")
ICON_PATTERN = re.compile(
    r"""from\s+['"](lucide-react|@heroicons/[^'"]*|@radix-ui/react-icons|@tabler/icons[^'"]*|react-feather|@phosphor-icons/[^'"]*)['"]"""
)
TOAST_PATTERN = re.compile(
    r"""from\s+['"](sonner|react-toastify|react-hot-toast|@/components/ui/(?:sonner|toast|toaster))['"]|\buseToast\b|<Toaster\b"""
)
FONT_HOST_PATTERN = re.compile(r"fonts\.(googleapis\.com|gstatic\.com|bunny\.net)")
LOG_SECRET_PATTERN = re.compile(r"Log::\w+\([^\n]*password", re.I)
EXTENDS_MODEL_PATTERN = re.compile(r"\bextends\s+(Model|Authenticatable|Pivot)\b")


def match_findings(rel, text, pattern, rule, message, level="error"):
    return [
        Temuan(rel, line_of(text, m.start()), rule, message, level)
        for m in pattern.finditer(text)
    ]


def layer_of(rel):
    match = re.match(r"src/[^/]+/(Domain|Application|Infrastructure)/", rel)
    return match.group(1) if match else None


def check_php(rel, text):
    findings = []
    layer = layer_of(rel)
    uses = [(m.group(1), line_of(text, m.start())) for m in USE_PATTERN.finditer(text)]
    if rel.startswith(STRICT_TYPES_PREFIXES) and "declare(strict_types=1)" not in text:
        findings.append(Temuan(rel, 1, "STRICT_TYPES", "Tambahkan declare(strict_types=1);"))
    for name, line in uses:
        if layer == "Domain" and (
            name.startswith(("Illuminate\\", "Laravel\\", "App\\")) or "\\Infrastructure\\" in name or "\\Application\\" in name
        ):
            findings.append(Temuan(rel, line, "DOMAIN_MURNI", f"Domain tidak boleh memakai {name}."))
        if layer == "Application" and (
            name.startswith(("Illuminate\\Database\\Eloquent", "Illuminate\\Http", "App\\"))
            or "\\Infrastructure\\" in name
        ):
            findings.append(Temuan(rel, line, "APPLICATION_MURNI", f"Application tidak boleh memakai {name}."))
        if rel.startswith(("app/Http/", "app/Console/")) and (
            name.startswith("Illuminate\\Database\\Eloquent")
            or name == "Illuminate\\Support\\Facades\\DB"
            or "\\Infrastructure\\" in name
        ):
            findings.append(Temuan(rel, line, "DELIVERY_TIPIS", f"Delivery tidak boleh memakai {name}."))
    if rel.startswith("app/Models/"):
        findings.append(Temuan(rel, 1, "ELOQUENT_DI_INFRA", "Pindahkan model ke src/<Context>/Infrastructure."))
    elif layer != "Infrastructure":
        findings += match_findings(rel, text, EXTENDS_MODEL_PATTERN, "ELOQUENT_DI_INFRA", "Model Eloquent hanya di Infrastructure.")
    findings += match_findings(rel, text, PHP_DEBUG_PATTERN, "FUNGSI_DILARANG", "Hapus fungsi debug.")
    findings += match_findings(rel, text, LOG_SECRET_PATTERN, "LOG_RAHASIA", "Jangan mencatat password ke log.")
    if rel.startswith("config/"):
        for m in ENV_CALL_PATTERN.finditer(text):
            if m.group(1) not in ALLOWED_ENV_KEYS:
                findings.append(
                    Temuan(rel, line_of(text, m.start()), "ENV_HANYA_DB", f"{m.group(1)} tidak boleh dari .env; simpan di pengaturan database.")
                )
    else:
        findings += match_findings(rel, text, PHP_ENV_PATTERN, "ENV_HANYA_DB", "env() hanya boleh di config/.")
    return findings


def check_js(rel, text):
    findings = []
    findings += match_findings(rel, text, JS_CONSOLE_PATTERN, "FUNGSI_DILARANG", "Hapus console.*.")
    findings += match_findings(rel, text, JS_NATIVE_DIALOG_PATTERN, "FUNGSI_DILARANG", "Pakai AlertDialog, bukan dialog bawaan browser.")
    findings += match_findings(rel, text, ICON_PATTERN, "IKON_REACT_ICONS", "Pakai react-icons.")
    findings += match_findings(rel, text, TOAST_PATTERN, "MODAL_BUKAN_TOAST", "Pakai AlertDialog modal, bukan toast.")
    return findings


def check_file(root, path):
    rel = path.relative_to(root).as_posix()
    if is_ignored(rel):
        return []
    language = language_of(path)
    if language is None:
        return []
    text = path.read_text(encoding="utf-8", errors="replace")
    findings = check_comments(rel, text, language)
    if language == "php":
        findings += check_php(rel, text)
    if language == "js":
        findings += check_js(rel, text)
    if language in ("js", "css", "blade"):
        findings += match_findings(rel, text, FONT_HOST_PATTERN, "FONT_ROBOTO", "Host font eksternal dilarang; host sendiri Roboto.")
    if language in ("php", "js") and text.count("\n") + 1 > MAX_FILE_LINES:
        findings.append(Temuan(rel, 1, "UKURAN_BERKAS", f"Berkas lebih dari {MAX_FILE_LINES} baris; pecah.", "warn"))
    return findings


def check_project(root):
    findings = []
    for marker in CI_MARKERS:
        if (root / marker).exists():
            findings.append(Temuan(marker, 1, "TANPA_CI", "Hapus berkas konfigurasi CI."))
    env_example = root / ".env.example"
    if env_example.exists():
        for number, raw in enumerate(env_example.read_text(encoding="utf-8").splitlines(), 1):
            match = re.match(r"^\s*([A-Z][A-Z0-9_]*)\s*=", raw)
            if match and match.group(1) not in ALLOWED_ENV_KEYS:
                findings.append(
                    Temuan(".env.example", number, "ENV_HANYA_DB", f"{match.group(1)} tidak boleh ada di .env.")
                )
    css_dir = root / "resources" / "css"
    if css_dir.is_dir():
        css_files = list(css_dir.rglob("*.css"))
        if css_files and not any("Roboto" in f.read_text(encoding="utf-8", errors="replace") for f in css_files):
            findings.append(Temuan("resources/css", 1, "FONT_ROBOTO", "Roboto belum dipakai pada CSS."))
    return findings


def collect_files(root, targets, staged):
    if staged:
        output = subprocess.run(
            ["git", "diff", "--cached", "--name-only", "--diff-filter=ACM"],
            cwd=root,
            capture_output=True,
            text=True,
            check=True,
        ).stdout.split()
        return [root / name for name in output if (root / name).is_file()]
    if targets:
        files = []
        for target in targets:
            path = (root / target).resolve() if not Path(target).is_absolute() else Path(target)
            files += [path] if path.is_file() else sorted(p for p in path.rglob("*") if p.is_file())
        return files
    return sorted(p for p in root.rglob("*") if p.is_file())


def run_cleanup(root, files):
    cleaned = 0
    for path in files:
        rel = path.relative_to(root).as_posix()
        language = language_of(path)
        if language is None or is_ignored(rel):
            continue
        original = path.read_text(encoding="utf-8", errors="replace")
        updated = strip_comments(original, language)
        if updated != original:
            path.write_text(updated, encoding="utf-8")
            cleaned += 1
            print(f"dibersihkan: {rel}")
    print(f"{cleaned} berkas dibersihkan dari komentar.")
    return 0


def print_report(findings):
    errors = [f for f in findings if f.level == "error"]
    warnings = [f for f in findings if f.level == "warn"]
    for f in sorted(findings, key=lambda x: (x.path, x.line, x.rule)):
        tag = "GALAT" if f.level == "error" else "PERINGATAN"
        print(f"{f.path}:{f.line}: [{f.rule}] {tag}: {f.message}")
    print(f"\nRingkasan: {len(errors)} pelanggaran, {len(warnings)} peringatan.")
    return errors, warnings


def main(argv=None):
    parser = argparse.ArgumentParser(description="Pemeriksa konvensi kode SICASN.")
    parser.add_argument("paths", nargs="*", help="Berkas atau folder yang diperiksa (default: seluruh proyek).")
    parser.add_argument("--root", default=str(DEFAULT_ROOT), help="Akar proyek.")
    parser.add_argument("--staged", action="store_true", help="Periksa hanya berkas yang di-stage git.")
    parser.add_argument("--ketat", action="store_true", help="Anggap peringatan sebagai pelanggaran.")
    parser.add_argument("--bersihkan", action="store_true", help="Hapus semua komentar dari berkas kode proyek.")
    parser.add_argument("--aturan", action="store_true", help="Tampilkan daftar aturan lalu keluar.")
    args = parser.parse_args(argv)

    if args.aturan:
        for name, description in RULES.items():
            print(f"{name:20s} {description}")
        return 0

    root = Path(args.root).resolve()
    files = collect_files(root, args.paths, args.staged)

    if args.bersihkan:
        return run_cleanup(root, files)

    findings = []
    for path in files:
        findings += check_file(root, path)
    if not args.paths and not args.staged:
        findings += check_project(root)

    errors, warnings = print_report(findings)
    if errors or (args.ketat and warnings):
        return 1
    print("Semua konvensi terpenuhi.")
    return 0


if __name__ == "__main__":
    sys.exit(main())
