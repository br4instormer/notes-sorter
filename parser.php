<?php

declare(strict_types=1);

namespace Parser;

use DOMDocument;
use DOMElement;
use DOMNodeList;
use DomXPath;
use Error;

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

    return $xpath->query(sprintf('//div[contains(@class, "%s")]', $classname));
}

function sortElements(DOMNodeList $elements): array
{
    $sorted = iterator_to_array($elements);

    usort($sorted, fn (DOMElement $a, DOMElement $b): int => intval($a->firstElementChild->nodeValue) <=> intval($b->firstElementChild->nodeValue));

    return $sorted;
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
