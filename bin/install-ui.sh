#!/usr/bin/env bash
# ─────────────────────────────────────────────────────────────────────────────
# install-ui.sh — wire dcplibrary/ui into a sibling DCPL package
#
# Usage:
#   ./bin/install-ui.sh ~/Sites/notices
#   ./bin/install-ui.sh              # prompts for target
#
# What it does:
#   1. Finds *ServiceProvider.php in <target>/src/
#   2. Appends resolveDcplUiViewsPath(), registerDcplUiCssRoute(),
#      and shareDcplUiCssVersion() methods
#   3. Injects dcpl view-namespace call after the first loadViewsFrom() line
#      (falls back to start of boot() if none found)
#   4. Injects $this->registerDcplUiCssRoute() into registerRoutes() or boot()
#   5. Injects $this->shareDcplUiCssVersion() near the end of boot()
#   6. Patches tailwind.config.js with vendor scan path + DCPL colour tokens
# ─────────────────────────────────────────────────────────────────────────────
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
UI_ROOT="$(cd "${SCRIPT_DIR}/.." && pwd)"

# ── Colours ───────────────────────────────────────────────────────────────────
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
CYAN='\033[0;36m'
RESET='\033[0m'

ok()   { echo -e "  ${GREEN}✓${RESET}  $*"; }
skip() { echo -e "  ${YELLOW}skip${RESET} $*"; }
warn() { echo -e "  ${YELLOW}⚠${RESET}   $*"; }
err()  { echo -e "  ${RED}✗${RESET}  $*"; }

# ── Target directory ──────────────────────────────────────────────────────────
TARGET="${1:-}"

if [[ -z "$TARGET" ]]; then
  echo -e "\n  ${CYAN}dcplibrary/ui installer${RESET}\n"
  read -rp "  Target package directory (e.g. ~/Sites/notices): " TARGET
fi

TARGET="${TARGET/#\~/$HOME}"
TARGET="$(cd "$TARGET" && pwd)"

if [[ ! -d "$TARGET" ]]; then
  err "Directory not found: $TARGET"
  exit 1
fi

echo -e "\n  ${CYAN}dcplibrary/ui installer${RESET}"
echo -e "  Target: ${TARGET}\n"

# ── Locate ServiceProvider ────────────────────────────────────────────────────
PROVIDER="$(find "${TARGET}/src" -maxdepth 2 -name '*ServiceProvider.php' 2>/dev/null | head -1)"

if [[ -z "$PROVIDER" ]]; then
  err "No *ServiceProvider.php found in ${TARGET}/src"
  exit 1
fi

echo -e "  Provider: ${PROVIDER}\n"

# ── Python helper — all PHP file patching done here ──────────────────────────
python3 - "$PROVIDER" "$UI_ROOT" <<'PYEOF'
import sys, re

provider_path = sys.argv[1]
ui_root       = sys.argv[2]

source = open(provider_path).read()

ALREADY_MARKER = 'resolveDcplUiViewsPath'

if ALREADY_MARKER in source:
    print("  \033[1;33mskip\033[0m Methods already present in ServiceProvider.")
    sys.exit(0)

# ── Helper methods to append ──────────────────────────────────────────────────
METHODS = r"""
    // ── dcplibrary/ui integration ─────────────────────────────────────────────
    //
    // Added by dcplibrary-ui/bin/install-ui.sh

    /**
     * Resolve the dcplibrary/ui views directory.
     * Checks Composer vendor first, then the local sibling directory.
     */
    protected function resolveDcplUiViewsPath(): ?string
    {
        $candidates = [
            base_path('vendor/dcplibrary/ui/resources/views'),
            __DIR__ . '/../../dcplibrary-ui/resources/views',
        ];

        foreach ($candidates as $path) {
            if (is_dir($path)) {
                return realpath($path);
            }
        }

        return null;
    }

    /**
     * Register GET /dcpl/ui/css (named dcpl.ui.css) if not already registered.
     * No-op when UiServiceProvider has already registered the route via Composer.
     */
    protected function registerDcplUiCssRoute(): void
    {
        if (\Illuminate\Support\Facades\Route::has('dcpl.ui.css')) {
            return;
        }

        $candidates = [
            base_path('vendor/dcplibrary/ui/resources/dist/dcpl.css'),
            __DIR__ . '/../../dcplibrary-ui/resources/dist/dcpl.css',
        ];

        foreach ($candidates as $css) {
            if (file_exists($css)) {
                \Illuminate\Support\Facades\Route::get('dcpl/ui/css', static function () use ($css) {
                    return response()->file($css, [
                        'Content-Type'  => 'text/css',
                        'Cache-Control' => 'public, max-age=31536000',
                    ]);
                })->name('dcpl.ui.css');
                return;
            }
        }
    }

    /**
     * Share the dcpl.css asset version hash for cache-busting.
     * Use in layouts: href="{{ route('dcpl.ui.css') }}?v={{ $dcplUiCssVersion ?? 'dev' }}"
     */
    protected function shareDcplUiCssVersion(): void
    {
        $candidates = [
            base_path('vendor/dcplibrary/ui/resources/dist/dcpl.css'),
            __DIR__ . '/../../dcplibrary-ui/resources/dist/dcpl.css',
        ];

        foreach ($candidates as $css) {
            if (file_exists($css)) {
                \Illuminate\Support\Facades\View::share(
                    'dcplUiCssVersion',
                    substr(md5_file($css), 0, 8)
                );
                return;
            }
        }
    }
"""

# Append before the final closing brace of the file.
last_brace = source.rfind('}')
if last_brace == -1:
    print("  \033[0;31m✗\033[0m  Could not find closing brace — file may be malformed.")
    sys.exit(1)

source = source[:last_brace] + METHODS + "}\n"
print("  \033[0;32m+\033[0m  Appended resolveDcplUiViewsPath(), registerDcplUiCssRoute(), shareDcplUiCssVersion()")

# ── Shared: insert a line after a given source line ──────────────────────────
def insert_after_line(src, search, new_line):
    """Find the first line containing `search` and append `new_line` after it."""
    lines = src.splitlines(keepends=True)
    for idx, ln in enumerate(lines):
        if search in ln:
            lines.insert(idx + 1, new_line + '\n')
            return ''.join(lines)
    return src  # not found — return unchanged

# ── Shared: insert a line before the closing brace of a named method ─────────
def inject_before_method_close(src, method_name, new_line):
    """
    Walk the source character-by-character to find method_name's closing brace,
    then insert new_line before it, preserving the brace's original indentation.
    """
    pos = src.find(f'function {method_name}')
    if pos == -1:
        return src
    depth, open_pos = 0, -1
    for i in range(pos, len(src)):
        if src[i] == '{':
            if depth == 0:
                open_pos = i
            depth += 1
        elif src[i] == '}':
            depth -= 1
            if depth == 0 and open_pos != -1:
                # Find the start of the line that holds this closing brace
                # so we can preserve its indentation.
                line_start = src.rfind('\n', 0, i) + 1
                indent     = src[line_start:i]   # whitespace before '}'
                return src[:line_start] + new_line + '\n' + indent + src[i:]
    return src

# ── Inject dcpl view-namespace registration ───────────────────────────────────
#
# Strategy (in priority order):
#  1. If registerViews() exists, inject right after its opening brace.
#  2. Otherwise, inject after the first loadViewsFrom() call anywhere in the file.
#  3. Otherwise, inject at the start of boot().

VIEWS_BLOCK = """\
        $dcplViews = $this->resolveDcplUiViewsPath();
        if ($dcplViews) {
            $this->loadViewsFrom($dcplViews, 'dcpl');
            \\Illuminate\\Support\\Facades\\Blade::anonymousComponentNamespace('dcpl::components', 'dcpl');
        }"""

def inject_views(src):
    # Strategy 1 — registerViews() method exists
    pattern = r'(function\s+registerViews\s*\([^)]*\)[^{]*\{)'
    patched = re.sub(pattern,
                     lambda m: m.group(0) + '\n' + VIEWS_BLOCK,
                     src, count=1, flags=re.DOTALL)
    if patched != src:
        return patched, 'registerViews()'

    # Strategy 2 — inject after the first loadViewsFrom() call
    patched = insert_after_line(src, 'loadViewsFrom(', VIEWS_BLOCK)
    if patched != src:
        return patched, 'after loadViewsFrom()'

    # Strategy 3 — inject at start of boot()
    pattern = r'(function\s+boot\s*\([^)]*\)[^{]*\{)'
    patched = re.sub(pattern,
                     lambda m: m.group(0) + '\n' + VIEWS_BLOCK,
                     src, count=1, flags=re.DOTALL)
    if patched != src:
        return patched, 'start of boot()'

    return src, None

patched, location = inject_views(source)
if location:
    source = patched
    print(f"  \033[0;32m+\033[0m  Injected dcpl view-namespace registration ({location})")
else:
    print("  \033[1;33m⚠\033[0m   Could not auto-inject dcpl view namespace — add manually:")
    print("      $dcplViews = $this->resolveDcplUiViewsPath();")
    print("      if ($dcplViews) {")
    print("          $this->loadViewsFrom($dcplViews, 'dcpl');")
    print("          Blade::anonymousComponentNamespace('dcpl::components', 'dcpl');")
    print("      }")

# ── Inject $this->registerDcplUiCssRoute() ───────────────────────────────────
CSS_CALL = '        $this->registerDcplUiCssRoute();'

target_method = 'registerRoutes' if 'function registerRoutes' in source else 'boot'
patched = inject_before_method_close(source, target_method, CSS_CALL)
if patched != source:
    source = patched
    print(f"  \033[0;32m+\033[0m  Injected registerDcplUiCssRoute() into {target_method}()")
else:
    print(f"  \033[1;33m⚠\033[0m   Could not inject into {target_method}() — add manually:")
    print("      $this->registerDcplUiCssRoute();")

# ── Inject $this->shareDcplUiCssVersion() into boot() ────────────────────────
CSS_VERSION_CALL = '        $this->shareDcplUiCssVersion();'

patched = inject_before_method_close(source, 'boot', CSS_VERSION_CALL)
if patched != source:
    source = patched
    print("  \033[0;32m+\033[0m  Injected shareDcplUiCssVersion() into boot()")
else:
    print("  \033[1;33m⚠\033[0m   Could not inject shareDcplUiCssVersion() — add manually to boot():")
    print("      $this->shareDcplUiCssVersion();")

open(provider_path, 'w').write(source)
print("  \033[0;32m✓\033[0m  Wrote patched ServiceProvider.")
PYEOF

# ── Tailwind config ───────────────────────────────────────────────────────────
echo ""

TAILWIND=""
for candidate in \
  "${TARGET}/tailwind.config.js" \
  "${TARGET}/../tailwind.config.js"
do
  if [[ -f "$candidate" ]]; then
    TAILWIND="$(realpath "$candidate")"
    break
  fi
done

if [[ -z "$TAILWIND" ]]; then
  warn "tailwind.config.js not found. Add these manually:"
  echo ""
  echo "    content: ["
  echo "      'vendor/dcplibrary/ui/resources/views/**/*.blade.php',"
  echo "    ]"
  echo ""
  echo "    theme: { extend: { colors: {"
  echo "      'dcpl-blue':   '#0075A3',"
  echo "      'dcpl-orange': '#F47920',"
  echo "      'dcpl-green':  '#8BC53F',"
  echo "      'dcpl-purple': '#8B5CF6',"
  echo "      'dcpl-gold':   '#F4B942',"
  echo "      'dcpl-text':   '#4d6375',"
  echo "    }}}"
  echo ""
else
  echo -e "  Tailwind: ${TAILWIND}\n"

  python3 - "$TAILWIND" <<'PYEOF'
import sys, re

path   = sys.argv[1]
source = open(path).read()
changed = False

# ── Vendor scan path ──────────────────────────────────────────────────────────
SCAN = "'vendor/dcplibrary/ui/resources/views/**/*.blade.php'"

if 'dcplibrary/ui' not in source:
    def add_scan(m):
        inner = m.group(1).rstrip()
        if inner and not inner.rstrip().endswith(','):
            inner += ','
        return 'content: [' + inner + '\n        ' + SCAN + ',\n    ]'
    patched = re.sub(r'content\s*:\s*\[([^\]]*)\]', add_scan, source, count=1, flags=re.DOTALL)
    if patched and 'dcplibrary/ui' in patched:
        source  = patched
        changed = True
        print("  \033[0;32m+\033[0m  Added vendor scan path to content[]")
    else:
        print("  \033[1;33m⚠\033[0m   Could not patch content[] — add manually:")
        print(f"      {SCAN}")
else:
    print("  \033[1;33mskip\033[0m Vendor path already in tailwind.config.js")

# ── DCPL colour tokens ────────────────────────────────────────────────────────
COLOR_TOKENS = """
            'dcpl-blue':   '#0075A3',
            'dcpl-orange': '#F47920',
            'dcpl-green':  '#8BC53F',
            'dcpl-purple': '#8B5CF6',
            'dcpl-gold':   '#F4B942',
            'dcpl-text':   '#4d6375',"""

if 'dcpl-blue' not in source:
    if 'extend:' in source:
        patched = re.sub(
            r'(extend\s*:\s*\{)',
            lambda m: m.group(0) + '\n        colors: {' + COLOR_TOKENS + '\n        },',
            source, count=1
        )
        if patched and 'dcpl-blue' in patched:
            source  = patched
            changed = True
            print("  \033[0;32m+\033[0m  Added DCPL colour tokens to theme.extend.colors")
        else:
            print("  \033[1;33m⚠\033[0m   Could not patch theme.extend.colors — add manually.")
    else:
        print("  \033[1;33m⚠\033[0m   No extend: block found — add DCPL colour tokens manually.")
else:
    print("  \033[1;33mskip\033[0m DCPL colour tokens already present")

if changed:
    open(path, 'w').write(source)
    print("  \033[0;32m✓\033[0m  Wrote patched tailwind.config.js")
PYEOF

fi

echo ""
echo -e "  ${GREEN}✓${RESET}  dcplibrary/ui integration complete."
echo ""
