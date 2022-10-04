<?php

declare(strict_types=1);

namespace parser;

require("consolelogger.php");

use DirectoryIterator;
use DOMDocument;
use DOMElement;
use DOMNodeList;
use DomXPath;
use Error;
use Iterator;
use UnexpectedValueException;
use Logger\LoggerInterface;
use Logger\ConsoleLogger;


$inDirectory = pathJoin(__DIR__, "in");
$outDirectory = pathJoin(__DIR__, "out");

function pathJoin(...$params)
{
    return join(DIRECTORY_SEPARATOR, $params);
}

function replaceElements(DOMDocument $doc, array $sortedElements): DOMDocument
{
    $body = $doc->getElementsByTagName("body")->item(0);

    foreach ($sortedElements as $element) {
        $body->appendChild($element);
    }

    return $doc;
}

function findElements(DOMDocument $doc, string $classname): mixed
{
    $xpath = new DomXPath($doc);

    return $xpath->query("//div[contains(@class, \"$classname\")]");
}

function sortElements(DOMNodeList $elements): array
{
    $sorted = iterator_to_array($elements);

    usort($sorted, fn (DOMElement $a, DOMElement $b): int => intval($a->firstElementChild->nodeValue) - intval($b->firstElementChild->nodeValue));

    return $sorted;
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

function processFile(
    string $inFilename,
    string $outFilename,
    string $classname
): void {
    $doc = new DOMDocument();
    $doc->loadHTMLFile($inFilename);

    $elements = findElements($doc, $classname);
    $isNoElements = !$elements || $elements->length === 0;

    if ($isNoElements) {
        throw new Error("Cannot find blocks to sort. Exit program");
    }

    $doc = replaceElements($doc, sortElements($elements));
    $doc->saveHTMLFile($outFilename);
}

function clearDirectory(string $directory): void
{
    if (is_dir($directory)) {
        $mask = pathJoin($directory, "*");
        array_map('unlink', array_filter((array) array_merge(glob($mask))));
    } else {
        mkdir($directory);
    }
}

function main(
    string $inDirectory,
    string $outDirectory,
    LoggerInterface $logger
): void {
    $classname = "bookmark";

    if (!is_dir($inDirectory)) {
        throw new Error("Source directory don't exists");
    }

    clearDirectory($outDirectory);
    $logger->log("Destination directory clean");

    foreach (iterateDirectory($inDirectory) as $inFilename) {
        $filename = basename($inFilename);
        $outFilename = pathJoin($outDirectory, $filename);

        $logger->log("Processing file \"$filename\"");
        processFile($inFilename, $outFilename, $classname);
        $logger->log("File \"$filename\" processed");
    }
}

main(
    $inDirectory,
    $outDirectory,
    new ConsoleLogger()
);
