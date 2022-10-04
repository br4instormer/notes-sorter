<?php

declare(strict_types=1);

namespace Logger;

interface LoggerInterface
{
    public function log(string $message): void;
}
