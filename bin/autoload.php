<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

/** @noinspection PhpIncludeInspection */
require_once array_reduce(
    [
        __DIR__ . '/../vendor/autoload.php',
    ],
    function (?string $carry, string $file): ?string {
        return file_exists($file)
            ? $file
            : $carry;
    },
    realpath(__DIR__ . '/../../../autoload.php') ?: null
);
