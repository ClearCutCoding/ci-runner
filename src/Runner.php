<?php

namespace ClearCutCoding\SymfonyCiRunner;

final class Runner
{
    private ConfigLoader $configLoader;

    private bool $noModsRun = false;

    // TERMINAL COLORS
    private const COL_RED = "\033[1;31m";
    private const COL_YELLOW = "\033[1;33m";
    private const COL_GREEN = "\033[0;32m";
    private const COL_BLUE = "\033[0;34m";
    private const COL_NC = "\033[0m"; // No Color

    public function __construct(
        ConfigLoader $configLoader
    ) {
        $this->configLoader = $configLoader;
    }

    public function run($argv): void
    {
        // if true, don't run any ci that will modify file.  e.g. useful for things like gitlab runner
        $this->noModsRun = in_array('--no-mods', $argv);

        $config = $this->configLoader->load();

        $this->phpcsfixer($config['phpcsfixer'] ?? false);
        $this->lintyaml($config['lintyaml'] ?? false);
        $this->linttwig($config['linttwig'] ?? false);
        $this->phpcs($config['phpcs'] ?? false);
        $this->phpunit($config['phpunit'] ?? false);
        $this->phpmd($config['phpmd'] ?? false);
        $this->phpstan($config['phpstan'] ?? false);
        $this->psalm($config['psalm'] ?? false);
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

    private function end(string $title): void
    {
        echo "\n" . self::COL_GREEN . "END {$title}" . self::COL_NC . "\n";
    }

    private function phpcsfixer(bool $do): void
    {
        $title = 'PHP-CS-FIXER';

        if ($this->noModsRun) {
            echo "\n" . self::COL_RED . "IGNORE {$title}" . self::COL_NC . "\n";

            return;
        }

        if (!$this->begin($do, $title)) {
            return;
        }

        $cmd = 'PHP_CS_FIXER_IGNORE_ENV=1 vendor/bin/php-cs-fixer fix';
        echo shell_exec($cmd);

        $this->end($title);
    }

    private function lintyaml(bool $do): void
    {
        $title = 'YAML LINT';
        if (!$this->begin($do, $title)) {
            return;
        }

        $cmd = 'bin/console lint:yaml config';
        echo shell_exec($cmd);
        $cmd = 'bin/console lint:yaml src';
        echo shell_exec($cmd);

        $this->end($title);
    }

    private function linttwig(bool $do): void
    {
        $title = 'TWIG LINT';
        if (!$this->begin($do, $title)) {
            return;
        }

        $cmd = 'bin/console lint:twig src';
        echo shell_exec($cmd);

        $this->end($title);
    }

    private function phpcs(bool $do): void
    {
        $title = 'PHPCS';
        if (!$this->begin($do, $title)) {
            return;
        }

        $cmd = 'vendor/bin/phpcs --report=checkstyle --extensions=php src tests';
        echo shell_exec($cmd);

        $this->end($title);
    }

    private function phpunit(bool $do): void
    {
        $title = 'PHPUNIT';
        if (!$this->begin($do, $title)) {
            return;
        }

        $cmd = 'bin/phpunit --configuration phpunit.xml.dist';
        echo shell_exec($cmd);

        $this->end($title);
    }

    private function phpmd(bool $do): void
    {
        $title = 'PHPMD';
        if (!$this->begin($do, $title)) {
            return;
        }

        $cmd = 'vendor/bin/phpmd src,tests text controversial,unusedcode';
        echo shell_exec($cmd);

        $this->end($title);
    }

    private function phpstan(bool $do): void
    {
        $title = 'PHPSTAN';
        if (!$this->begin($do, $title)) {
            return;
        }

        $cmd = 'php -d memory_limit=-1 vendor/bin/phpstan analyse src tests';
        echo shell_exec($cmd);

        $this->end($title);
    }

    private function psalm(bool $do): void
    {
        $title = 'PSALM';
        if (!$this->begin($do, $title)) {
            return;
        }

        $cmd = 'vendor/bin/psalm --show-info=true';
        echo shell_exec($cmd);

        $this->end($title);
    }
}
