<?php

declare(strict_types=1);

namespace App;

require "consolelogger.php";
require "parser.php";
require "fs.php";

use Error;
use Fs;
use Logger\LoggerInterface;
use Logger\ConsoleLogger;

use function Parser\processFile;

$inDirectory = Fs\pathJoin(__DIR__, "in");
$outDirectory = Fs\pathJoin(__DIR__, "out");

function main(
    string $inDirectory,
    string $outDirectory,
    LoggerInterface $logger
): void {
    $classname = "bookmark";

    if (!is_dir($inDirectory)) {
        throw new Error("Source directory don't exists");
    }

    Fs\clearDirectory($outDirectory) || mkdir($outDirectory);
    $logger->log("Destination directory clean");

    foreach (Fs\iterateDirectory($inDirectory) as $inFilename) {
        $filename = basename($inFilename);
        $outFilename = Fs\pathJoin($outDirectory, $filename);

        $logger->log(sprintf('Processing file "%s"', $filename));
        processFile($inFilename, $outFilename, $classname);
        $logger->log(sprintf('File "%s" processed', $filename));
    }
}

main(
    $inDirectory,
    $outDirectory,
    new ConsoleLogger()
);
