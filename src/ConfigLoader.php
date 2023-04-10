<?php

namespace ClearCutCoding\SymfonyCiRunner;

use ClearCutCoding\SymfonyCiRunner\Exception\ConfigNotFoundException;
use Symfony\Component\Yaml\Yaml;

final class ConfigLoader
{
    private const CONFIG_FILE = 'ci-runner.config.yaml';

    public function __construct()
    {
    }

    public function load(): array
    {
        $configFile = $this->locateConfigFile(__DIR__);
        if ($configFile === null) {
            throw new ConfigNotFoundException('Config not found for path ' . __DIR__);
        }
        $config = Yaml::parseFile($configFile);

        return $config ?? [];
    }

    private function locateConfigFile(string $path): ?string
    {
        $dir_path = realpath($path);

        if (!is_dir($dir_path)) {
            $dir_path = dirname($dir_path);
        }

        do {
            $maybe_path = $dir_path . DIRECTORY_SEPARATOR . self::CONFIG_FILE;

            if (file_exists($maybe_path)) {
                return $maybe_path;
            }

            $dir_path = dirname($dir_path);
        } while (dirname($dir_path) !== $dir_path);

        return null;
    }
}
