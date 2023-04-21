<?php

declare(strict_types=1);

namespace Fs;

use DirectoryIterator;
use Iterator;

function clearDirectory(string $directory): bool
{
    if (!is_dir($directory)) {
        return false;
    }

    $mask = pathJoin($directory, "*");
    array_map('unlink', array_filter((array) array_merge(glob($mask))));

    return true;
}

function iterateDirectory(string $directory): Iterator
{
    foreach (new DirectoryIterator($directory) as $fileInfo) {
        if ($fileInfo->isDot() || !$fileInfo->isFile()) {
            continue;
        }

        yield $fileInfo->getPathname();
    }
}

function pathJoin(string ...$params): string
{
    return join(DIRECTORY_SEPARATOR, $params);
}
