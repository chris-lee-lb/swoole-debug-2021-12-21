<?php

require 'vendor/autoload.php';

class Preloader
{
    private array $ignores = [];
    private array $paths;
    private array $fileMap;

    public function __construct(string ...$paths)
    {
        ini_set('display_startup_errors', 'On');
        ini_set('display_errors', 'On');
        ini_set('error_reporting', E_ALL & ~(E_WARNING|E_NOTICE));

        $this->paths   = $paths;
        $classMap      = require __DIR__ . '/vendor/composer/autoload_classmap.php';
        $this->fileMap = array_flip($classMap);
    }

    public function paths(string ...$paths): Preloader
    {
        $this->paths = array_merge(
            $this->paths,
            $paths
        );

        return $this;
    }

    public function ignore(string ...$names): Preloader
    {
        $this->ignores = array_merge(
            $this->ignores,
            $names
        );

        return $this;
    }

    public function load(): void
    {
        foreach ($this->paths as $path) {
            $this->loadPath(rtrim($path, '/'));
        }
    }

    private function loadPath(string $path): void
    {
        if (is_dir($path)) {
            $this->loadDir($path);

            return;
        }

        $this->loadFile($path);
    }

    private function loadDir(string $path): void
    {
        $handle = opendir($path);

        while ($file = readdir($handle)) {
            if (in_array($file, ['.', '..'])) {
                continue;
            }

            $this->loadPath("{$path}/{$file}");
        }

        closedir($handle);
    }

    private function loadFile(string $path): void
    {
        $class = $this->fileMap[$path] ?? null;

        if ($this->shouldIgnore($class)) {
            return;
        }

        require_once($path);
    }

    private function shouldIgnore(?string $name): bool
    {
        if ($name === null) {
            return true;
        }

        foreach ($this->ignores as $ignore) {
            if (strpos($name, $ignore) === 0) {
                return true;
            }
        }

        return false;
    }
}

(new Preloader())
    ->paths(
        __DIR__ . '/app',
        __DIR__ . '/vendor/laravel'
    )
    ->ignore(
        \Illuminate\Filesystem\Cache::class,
        \Illuminate\Foundation\Testing\TestCase::class,
        'Laravel\Octane',
        'Illuminate\Testing',
    )
    ->load();
