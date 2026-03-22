<?php

namespace Dcpl\Ui\Console\Commands;

use Illuminate\Console\Command;

/**
 * php artisan dcpl:install
 *
 * Wires dcplibrary/ui into an existing Laravel package's ServiceProvider.
 *
 * What it does
 * ─────────────
 * 1. Locates (or asks for) the consuming ServiceProvider .php file.
 * 2. Appends the two required helper methods if not already present:
 *      – resolveDcplUiViewsPath()
 *      – registerDcplUiCssRoute()
 * 3. Injects calls to those methods into registerViews() and boot() / the
 *    route-registration method it can find — with a safe fallback that prints
 *    manual instructions when the AST heuristic is uncertain.
 * 4. Locates (or asks for) tailwind.config.js and patches it with the vendor
 *    scan path and DCPL colour token definitions.
 */
class InstallCommand extends Command
{
    protected $signature = 'dcpl:install-ui
                            {--provider= : Relative or absolute path to the consuming ServiceProvider}
                            {--tailwind= : Relative or absolute path to tailwind.config.js}
                            {--force    : Re-patch even if dcpl hooks are already present}';

    protected $description = 'Wire dcplibrary/ui into a consuming package\'s ServiceProvider and Tailwind config';

    // ── Boilerplate that gets appended to the ServiceProvider ──────────────────

    private const HELPER_METHODS = <<<'PHP'

    // ── dcplibrary/ui integration ─────────────────────────────────────────────
    //
    // These two methods were added automatically by `php artisan dcpl:install`.
    // They let the package work whether dcplibrary/ui is installed via Composer
    // (production) or lives as a sibling directory (local dev).

    /**
     * Resolve the dcplibrary/ui views directory, checking Composer vendor first
     * then a local sibling directory for development environments.
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
     * Register the GET /dcpl/ui/css route (named dcpl.ui.css) if not already
     * registered.  Skipped automatically when UiServiceProvider registers it
     * via Composer.
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
PHP;

    // ── Main ──────────────────────────────────────────────────────────────────

    public function handle(): int
    {
        $this->newLine();
        $this->line('  <fg=blue;options=bold>dcplibrary/ui installer</>');
        $this->newLine();

        // 1. ServiceProvider
        $providerPath = $this->resolveProviderPath();

        if (! $providerPath) {
            $this->error('Could not locate a ServiceProvider. Pass it with --provider=path/to/FooServiceProvider.php');
            return self::FAILURE;
        }

        $this->line("  <fg=gray>Provider:</> {$providerPath}");
        $this->newLine();

        $providerResult = $this->patchServiceProvider($providerPath);

        // 2. Tailwind
        $tailwindPath = $this->resolveTailwindPath();

        if ($tailwindPath) {
            $this->line("  <fg=gray>Tailwind:</> {$tailwindPath}");
            $this->newLine();
            $this->patchTailwindConfig($tailwindPath);
        } else {
            $this->warn('  tailwind.config.js not found automatically.');
            $this->printTailwindInstructions();
        }

        // 3. Summary
        $this->newLine();

        if ($providerResult === 'already_installed') {
            $this->line('  <fg=yellow>⚠</> dcpl hooks were already present — nothing changed. Use --force to re-patch.');
        } else {
            $this->line('  <fg=green>✓</> dcplibrary/ui integration complete.');
        }

        $this->newLine();

        return self::SUCCESS;
    }

    // ── ServiceProvider patching ──────────────────────────────────────────────

    private function resolveProviderPath(): ?string
    {
        // Explicit option wins.
        if ($opt = $this->option('provider')) {
            $path = $this->toAbsPath($opt);
            return file_exists($path) ? $path : null;
        }

        // Auto-detect: search common locations for *ServiceProvider.php
        $searchDirs = [
            base_path('app/Providers'),
            base_path('src'),
            base_path('../src'),          // package repo root pattern
        ];

        $candidates = [];

        foreach ($searchDirs as $dir) {
            if (! is_dir($dir)) {
                continue;
            }
            foreach (glob("{$dir}/*ServiceProvider.php") ?: [] as $file) {
                // Skip dcplibrary/ui's own provider.
                if (str_contains($file, 'UiServiceProvider')) {
                    continue;
                }
                $candidates[] = $file;
            }
        }

        // Recurse one level deeper — packages often live at src/
        foreach (glob(base_path('packages/*/src/*ServiceProvider.php')) ?: [] as $file) {
            $candidates[] = $file;
        }

        if (count($candidates) === 1) {
            return $candidates[0];
        }

        if (count($candidates) > 1) {
            return $this->choice(
                'Multiple ServiceProviders found. Which one should be patched?',
                $candidates
            );
        }

        // Interactive fallback.
        $entered = $this->ask('Enter the path to the consuming package\'s ServiceProvider');
        if ($entered) {
            $path = $this->toAbsPath($entered);
            return file_exists($path) ? $path : null;
        }

        return null;
    }

    private function patchServiceProvider(string $path): string
    {
        $source = file_get_contents($path);

        // ── Idempotency ────────────────────────────────────────────────────────
        $alreadyHasMethods = str_contains($source, 'resolveDcplUiViewsPath');

        if ($alreadyHasMethods && ! $this->option('force')) {
            $this->line('  <fg=yellow>skip</>  Methods already present in ServiceProvider.');
            return 'already_installed';
        }

        $modified = $source;

        // ── Step 1: Append helper methods ──────────────────────────────────────
        if (! $alreadyHasMethods) {
            // Insert just before the final closing brace of the class.
            $lastBrace = strrpos($modified, '}');
            if ($lastBrace === false) {
                $this->error('Could not find closing brace in ServiceProvider — file may be malformed.');
                return 'error';
            }

            $modified = substr($modified, 0, $lastBrace)
                . self::HELPER_METHODS
                . "\n}\n";

            $this->line('  <fg=green>+</>  Appended resolveDcplUiViewsPath() and registerDcplUiCssRoute()');
        }

        // ── Step 2: Inject registerViews() call ────────────────────────────────
        $viewsSnippet = <<<'PHP'
        $dcplViews = $this->resolveDcplUiViewsPath();
        if ($dcplViews) {
            $this->loadViewsFrom($dcplViews, 'dcpl');
            \Illuminate\Support\Facades\Blade::anonymousComponentNamespace('dcpl::components', 'dcpl');
        }
PHP;

        if (! str_contains($modified, 'resolveDcplUiViewsPath()')) {
            $modified = $this->injectAfterMethodOpen($modified, 'registerViews', $viewsSnippet);

            if (str_contains($modified, 'resolveDcplUiViewsPath()')) {
                $this->line('  <fg=green>+</>  Injected dcpl view-namespace call into registerViews()');
            } else {
                $this->warn('  Could not auto-inject into registerViews() — add manually:');
                $this->line('    <fg=gray>' . trim($viewsSnippet) . '</>');
            }
        }

        // ── Step 3: Inject CSS route registration ─────────────────────────────
        // Try boot() first, then registerRoutes().
        $cssCall = '        $this->registerDcplUiCssRoute();';

        if (! str_contains($modified, 'registerDcplUiCssRoute()')) {
            $target = str_contains($modified, 'function registerRoutes') ? 'registerRoutes' : 'boot';
            $modified = $this->injectBeforeMethodClose($modified, $target, $cssCall);

            if (str_contains($modified, 'registerDcplUiCssRoute()')) {
                $this->line("  <fg=green>+</>  Injected CSS route call into {$target}()");
            } else {
                $this->warn('  Could not auto-inject CSS route call — add this line to boot() or registerRoutes():');
                $this->line('    <fg=gray>$this->registerDcplUiCssRoute();</>');
            }
        }

        // ── Write back ─────────────────────────────────────────────────────────
        file_put_contents($path, $modified);
        $this->line("  <fg=green>✓</>  Wrote patched ServiceProvider.");

        return 'patched';
    }

    /**
     * Insert $snippet after the opening brace of a named method.
     *
     * Matches: [protected|public|private]? function methodName(…): …? {
     *                                                                    ^ insert here
     */
    private function injectAfterMethodOpen(string $source, string $method, string $snippet): string
    {
        // Match "function methodName" then everything up to the first {
        $pattern = '/(function\s+' . preg_quote($method, '/') . '\s*\([^)]*\)[^{]*\{)/s';

        return preg_replace_callback($pattern, function (array $m) use ($snippet) {
            return $m[1] . "\n" . $snippet . "\n";
        }, $source, 1) ?? $source;
    }

    /**
     * Insert $line just before the closing brace of a named method.
     *
     * Uses a simple brace-counting heuristic — reliable for single-level
     * method bodies without nested classes.
     */
    private function injectBeforeMethodClose(string $source, string $method, string $line): string
    {
        // Find the offset where the method signature starts.
        $pos = strpos($source, "function {$method}");
        if ($pos === false) {
            return $source;
        }

        // Walk forward to find the opening brace.
        $len   = strlen($source);
        $depth = 0;
        $open  = -1;

        for ($i = $pos; $i < $len; $i++) {
            if ($source[$i] === '{') {
                if ($depth === 0) {
                    $open = $i;
                }
                $depth++;
            } elseif ($source[$i] === '}') {
                $depth--;
                if ($depth === 0 && $open !== -1) {
                    // $i is the closing brace of the method.
                    return substr($source, 0, $i) . $line . "\n    " . substr($source, $i);
                }
            }
        }

        return $source;
    }

    // ── Tailwind config patching ──────────────────────────────────────────────

    private function resolveTailwindPath(): ?string
    {
        if ($opt = $this->option('tailwind')) {
            $path = $this->toAbsPath($opt);
            return file_exists($path) ? $path : null;
        }

        // Common locations relative to the Laravel app root.
        $candidates = [
            base_path('tailwind.config.js'),
            base_path('../tailwind.config.js'),
            base_path('../../tailwind.config.js'),
        ];

        foreach ($candidates as $path) {
            if (file_exists($path)) {
                return realpath($path);
            }
        }

        return null;
    }

    private function patchTailwindConfig(string $path): void
    {
        $source = file_get_contents($path);

        $changed = false;

        // ── Vendor scan path ───────────────────────────────────────────────────
        $vendorScan = "'vendor/dcplibrary/ui/resources/views/**/*.blade.php'";

        if (! str_contains($source, 'dcplibrary/ui')) {
            // Try to append into an existing content array.
            $source = preg_replace_callback(
                '/content\s*:\s*\[([^\]]*)\]/s',
                function (array $m) use ($vendorScan) {
                    $inner = rtrim($m[1]);
                    // Add a trailing comma if needed.
                    if ($inner !== '' && ! str_ends_with(trim($inner), ',')) {
                        $inner .= ',';
                    }
                    return 'content: [' . $inner . "\n        " . $vendorScan . ',]';
                },
                $source,
                1
            );

            if ($source !== null && str_contains($source, 'dcplibrary/ui')) {
                $this->line('  <fg=green>+</>  Added dcplibrary/ui vendor scan path to content[]');
                $changed = true;
            } else {
                $this->warn('  Could not auto-patch content[] — add manually:');
                $this->line("    <fg=gray>{$vendorScan}</>");
            }
        } else {
            $this->line('  <fg=yellow>skip</>  dcplibrary/ui vendor path already in tailwind.config.js');
        }

        // ── DCPL colour tokens ─────────────────────────────────────────────────
        $colorTokens = <<<'JS'
            'dcpl-blue':   '#0075A3',
            'dcpl-orange': '#F47920',
            'dcpl-green':  '#8BC53F',
            'dcpl-purple': '#8B5CF6',
            'dcpl-gold':   '#F4B942',
            'dcpl-text':   '#4d6375',
JS;

        if (! str_contains($source, 'dcpl-blue')) {
            // Try to inject into extend.colors or create it.
            if (str_contains($source, 'extend:')) {
                $source = preg_replace_callback(
                    '/extend\s*:\s*\{/s',
                    fn (array $m) => $m[0] . "\n        colors: {\n" . $colorTokens . "\n        },",
                    $source,
                    1
                );
            }

            if ($source !== null && str_contains($source, 'dcpl-blue')) {
                $this->line('  <fg=green>+</>  Added DCPL colour tokens to theme.extend.colors');
                $changed = true;
            } else {
                $this->warn('  Could not auto-inject DCPL colour tokens — add manually to theme.extend.colors:');
                $this->line("    <fg=gray>{$colorTokens}</>");
            }
        } else {
            $this->line('  <fg=yellow>skip</>  DCPL colour tokens already present in tailwind.config.js');
        }

        if ($changed) {
            file_put_contents($path, $source);
            $this->line('  <fg=green>✓</>  Wrote patched tailwind.config.js');
        }
    }

    private function printTailwindInstructions(): void
    {
        $this->newLine();
        $this->line('  Add to <fg=cyan>tailwind.config.js</>:');
        $this->line('');
        $this->line("  <fg=gray>// content array</>           'vendor/dcplibrary/ui/resources/views/**/*.blade.php',");
        $this->line('');
        $this->line('  <fg=gray>// theme.extend.colors</>');
        $this->line("  <fg=gray>'dcpl-blue':   '#0075A3',</>");
        $this->line("  <fg=gray>'dcpl-orange': '#F47920',</>");
        $this->line("  <fg=gray>'dcpl-green':  '#8BC53F',</>");
        $this->line("  <fg=gray>'dcpl-purple': '#8B5CF6',</>");
        $this->line("  <fg=gray>'dcpl-gold':   '#F4B942',</>");
        $this->line("  <fg=gray>'dcpl-text':   '#4d6375',</>");
    }

    // ── Utilities ─────────────────────────────────────────────────────────────

    private function toAbsPath(string $path): string
    {
        if (str_starts_with($path, '/')) {
            return $path;
        }

        return base_path($path);
    }
}
