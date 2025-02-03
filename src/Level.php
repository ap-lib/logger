<?php declare(strict_types=1);

namespace AP\Logger;

/**
 * Enum representing different levels of logging severity
 *
 * Levels:
 * - DEBUG 1: used for detailed debugging information
 * - INFO 2: used for general informational messages
 * - WARNING 3: used for potentially harmful situations
 * - ERROR 4: used for serious error events that require immediate attention
 */
enum Level: int
{
    case DEBUG = 1;
    case INFO = 2;
    case WARNING = 3;
    case ERROR = 4;
}