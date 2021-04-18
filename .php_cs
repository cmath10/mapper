<?php

declare(strict_types=1);

use PhpCsFixer\Config;

$config = new class() extends Config {
    public function getRules(): array
    {
        return [
            '@PSR2' => true,
            '@PSR12' => true,
        ];
    }
};

$config->getFinder()
    ->files()
    ->in(__DIR__)
    ->exclude('.cache')
    ->exclude('vendor')
    ->name('*.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true)
;

$config->setCacheFile(__DIR__ . '/.cache/php-cs-fixer/php_cs.cache');

return $config;
