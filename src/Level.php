<?php declare(strict_types=1);

namespace AP\Logger;

enum Level: int
{
    case DEBUG = 1;
    case INFO = 2;
    case WARNING = 3;
    case ERROR = 4;
}