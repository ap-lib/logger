<?php declare(strict_types=1);

namespace AP\Logger;

use AP\Logger\Dumper\AddInterface;
use AP\Sanitizer\BaseSanitizer;
use AP\Sanitizer\Sanitized;
use AP\Sanitizer\Sanitizer;
use AP\Sanitizer\ThrowableSanitizer;

/**
 * Routes log messages to the appropriate logger
 *
 * Supports multiple loggers for different modules and lazy initialization
 * of the default logger to optimize performance in production systems
 */
class Router
{
    protected ?Logger $defaultLogger = null;
    /**
     * @var array<string, Logger>  Special logger for module by module name
     */
    protected array $specialLoggers = [];

    protected ?Sanitizer $contextSanitizer = null;

    /**
     * @param string $defaultModuleName
     */
    public function __construct(readonly protected string $defaultModuleName)
    {
    }

    /**
     * Assigns a special dumper to a specific module
     *
     * @param string        $module Module name
     * @param AddInterface  $dumper Dumper instance for handling log entries
     * @return static
     */
    final public function setSpecialDumper(string $module, AddInterface $dumper): static
    {
        if (isset($this->specialLoggers[$module])) {
            $this->specialLoggers[$module]->setDumper($dumper);
        } else {
            $this->specialLoggers[$module] = new Logger($dumper);
        }
        return $this;
    }

    /**
     * Sets the default dumper for logs that do not belong to a specific module
     *
     * @param AddInterface $dumper Dumper instance for handling log entries
     * @return static
     */
    final public function setDefaultDumper(AddInterface $dumper): static
    {
        if ($this->defaultLogger instanceof Logger) {
            $this->defaultLogger->setDumper($dumper);
        } else {
            $this->defaultLogger = new Logger($dumper);
        }
        return $this;
    }

    /**
     * Lazily creates and returns the default logger
     *
     * The default logger is only instantiated when first used, as production
     * systems are likely to provide a custom logger.
     *
     * @return Logger Default logger instance
     */
    private function getDefaultLogger(): Logger
    {
        if (is_null($this->defaultLogger)) {
            $this->defaultLogger = new Logger();
        }
        return $this->defaultLogger;
    }

    private function getLogger(string $module): Logger
    {
        return $this->specialLoggers[$module] ?? $this->getDefaultLogger();
    }

    /**
     * Sets a custom context sanitizer
     *
     * @param Sanitizer $contextSanitizer Sanitizer instance for processing context data
     * @return static
     */
    public function setContextSanitizer(Sanitizer $contextSanitizer): static
    {
        $this->contextSanitizer = $contextSanitizer;
        return $this;
    }

    /**
     * Returns the context sanitizer, creating a default with [ThrowableSanitizer] one if none is set
     *
     * @return Sanitizer
     */
    final public function getContextSanitizer(): Sanitizer
    {
        if (is_null($this->contextSanitizer)) {
            $this->contextSanitizer = new BaseSanitizer([
                new ThrowableSanitizer()
            ]);
        }
        return $this->contextSanitizer;
    }

    /**
     * Routes a log entry to the appropriate logger
     *
     * @param Level       $level   Logging severity level
     * @param string      $message Log message
     * @param mixed       $context Additional contextual data
     * @param string|null $module
     */
    public function add(Level $level, string $message, mixed $context, ?string $module): void
    {
        $module  = is_null($module) ? $this->defaultModuleName : $module;
        $context = $this->getContextSanitizer()->sanitize($context);
        $context = $context instanceof Sanitized ? $context->value : [];

        self::getLogger($module)->add(
            $level,
            $message,
            is_array($context) ? $context : [],
            $module,
        );
    }

    /**
     * Commits all pending log entries for both the default and module-specific loggers
     */
    final public function commitAll(): void
    {
        if ($this->defaultLogger instanceof Logger) {
            $this->defaultLogger->commit();
        }
        foreach ($this->specialLoggers as $logger) {
            $logger->commit();
        }
    }
}