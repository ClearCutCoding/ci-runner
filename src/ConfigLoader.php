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
        $dirPath = realpath($path);

        if (!is_dir($dirPath)) {
            $dirPath = dirname($dirPath);
        }

        do {
            $maybePath = $dirPath . DIRECTORY_SEPARATOR . self::CONFIG_FILE;

            if (file_exists($maybePath)) {
                return $maybePath;
            }

            $dirPath = dirname($dirPath);
        } while (dirname($dirPath) !== $dirPath);

        return null;
    }
}
