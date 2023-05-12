<?php

namespace ClearCutCoding\SymfonyCiRunner;

/**
 * @psalm-suppress ForbiddenCode
 */
final class Runner
{
    private bool $noModsRun = false;

    // TERMINAL COLORS
    private const COL_RED = "\033[1;31m";
    private const COL_YELLOW = "\033[1;33m";
    private const COL_GREEN = "\033[0;32m";
    private const COL_BLUE = "\033[0;34m";
    private const COL_NC = "\033[0m"; // No Color

    private readonly array $config;

    private readonly string $vendorRoot;

    public function __construct(
        private readonly ConfigLoader $configLoader
    ) {
        $this->config = $this->configLoader->load();
        $this->vendorRoot = $this->config['vendor-root'] ?? './';
    }

    public function run(array $argv): void
    {
        // if true, don't run any ci that will modify file.  e.g. useful for things like gitlab runner
        $this->noModsRun = in_array('--no-mods', $argv);

        $this->rector($this->config['run']['rector'] ?? false);
        $this->phpcsfixer($this->config['run']['phpcsfixer'] ?? false);
        $this->lintyaml($this->config['run']['lintyaml'] ?? false);
        $this->linttwig($this->config['run']['linttwig'] ?? false);
        $this->phpcs($this->config['run']['phpcs'] ?? false);
        $this->phpunit($this->config['run']['phpunit'] ?? false);
        $this->phpmd($this->config['run']['phpmd'] ?? false);
        $this->phpstan($this->config['run']['phpstan'] ?? false);
        $this->psalm($this->config['run']['psalm'] ?? false);
    }

    private function begin(bool $do, string $title): bool
    {
        if (!$do) {
            echo "\n" . self::COL_RED . "BYPASS {$title}" . self::COL_NC . "\n";

            return false;
        }

        echo "\n" . self::COL_GREEN . "START {$title}" . self::COL_NC . "\n";

        return true;
    }

    private function end(string $title, array $output, int $exitCode): bool
    {
        echo implode(PHP_EOL, $output);
        echo "\n" . self::COL_GREEN . "END {$title}" . self::COL_NC . "\n";

        if ($exitCode !== 0) {
            exit;
        }

        return true;
    }

    private function rector(bool $do): bool
    {
        $title = 'RECTOR';

        if ($this->noModsRun) {
            echo "\n" . self::COL_RED . "IGNORE {$title}" . self::COL_NC . "\n";

            return true;
        }

        if (!$this->begin($do, $title)) {
            return true;
        }

        $cmd = "{$this->vendorRoot}vendor/bin/rector";
        exec($cmd, $output, $exitCode);

        return $this->end($title, $output, $exitCode);
    }

    private function phpcsfixer(bool $do): bool
    {
        $title = 'PHP-CS-FIXER';

        if ($this->noModsRun) {
            echo "\n" . self::COL_RED . "IGNORE {$title}" . self::COL_NC . "\n";

            return true;
        }

        if (!$this->begin($do, $title)) {
            return true;
        }

        $cmd = "PHP_CS_FIXER_IGNORE_ENV=1 {$this->vendorRoot}vendor/bin/php-cs-fixer fix";
        exec($cmd, $output, $exitCode);

        return $this->end($title, $output, $exitCode);
    }

    private function lintyaml(bool $do): bool
    {
        $title = 'YAML LINT';
        if (!$this->begin($do, $title)) {
            return true;
        }

        $cmd = 'bin/console lint:yaml config';
        exec($cmd, $output, $exitCode);
        echo implode(PHP_EOL, $output);

        $cmd = 'bin/console lint:yaml src';
        exec($cmd, $output, $exitCode);

        return $this->end($title, $output, $exitCode);
    }

    private function linttwig(bool $do): bool
    {
        $title = 'TWIG LINT';
        if (!$this->begin($do, $title)) {
            return true;
        }

        $cmd = 'bin/console lint:twig src';
        exec($cmd, $output, $exitCode);

        return $this->end($title, $output, $exitCode);
    }

    private function phpcs(bool $do): bool
    {
        $title = 'PHPCS';
        if (!$this->begin($do, $title)) {
            return true;
        }

        $cmd = "{$this->vendorRoot}vendor/bin/phpcs --report=checkstyle --extensions=php src tests";
        exec($cmd, $output, $exitCode);

        return $this->end($title, $output, $exitCode);
    }

    private function phpunit(bool $do): bool
    {
        $title = 'PHPUNIT';
        if (!$this->begin($do, $title)) {
            return true;
        }

        $cmd = 'bin/phpunit --configuration phpunit.xml.dist';
        exec($cmd, $output, $exitCode);

        return $this->end($title, $output, $exitCode);
    }

    private function phpmd(bool $do): bool
    {
        $title = 'PHPMD';
        if (!$this->begin($do, $title)) {
            return true;
        }

        $cmd = "{$this->vendorRoot}vendor/bin/phpmd src,tests text controversial,unusedcode";
        exec($cmd, $output, $exitCode);

        return $this->end($title, $output, $exitCode);
    }

    private function phpstan(bool $do): bool
    {
        $title = 'PHPSTAN';
        if (!$this->begin($do, $title)) {
            return true;
        }

        $cmd = 'php -d memory_limit=-1 vendor/bin/phpstan analyse src tests';
        exec($cmd, $output, $exitCode);

        return $this->end($title, $output, $exitCode);
    }

    private function psalm(bool $do): bool
    {
        $title = 'PSALM';
        if (!$this->begin($do, $title)) {
            return true;
        }

        $cmd = "{$this->vendorRoot}vendor/bin/psalm --show-info=true";
        exec($cmd, $output, $exitCode);

        return $this->end($title, $output, $exitCode);
    }
}
