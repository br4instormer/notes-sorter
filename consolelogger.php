<?php

declare(strict_types=1);

namespace Logger;

require("loggerinterface.php");

class ConsoleLogger implements LoggerInterface
{
    public function log(string $message): void
    {
        echo $message . PHP_EOL;
    }
}
