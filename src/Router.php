<?php declare(strict_types=1);

namespace AP\Logger;

use AP\Logger\Context\Format\FormatInterface;
use AP\Logger\Dumper\AddInterface;

class Router
{
    protected ?Logger $defaultLogger = null;
    /**
     * @var array<string, Logger>  Special logger for module by module name
     */
    protected array $specialLoggers = [];

    /**
     * @var array<FormatInterface>
     */
    protected array $formatters;

    /**
     * @param string $defaultModuleName
     */
    public function __construct(readonly protected string $defaultModuleName)
    {
    }

    final public function setSpecialDumper(string $module, AddInterface $dumper): static
    {
        if (isset($this->specialLoggers[$module])) {
            $this->specialLoggers[$module]->setDumper($dumper);
        } else {
            $this->specialLoggers[$module] = new Logger($dumper);
        }
        return $this;
    }

    final public function setDefaultDumper(AddInterface $dumper): static
    {
        if ($this->defaultLogger instanceof Logger) {
            $this->defaultLogger->setDumper($dumper);
        } else {
            $this->defaultLogger = new Logger($dumper);
        }
        return $this;
    }

    final public function commitAll(): void
    {
        if ($this->defaultLogger instanceof Logger) {
            $this->defaultLogger->commit();
        }
        foreach ($this->specialLoggers as $logger) {
            $logger->commit();
        }
    }

    /**
     * Adds a formatter to the list of formatters.
     *
     * @param FormatInterface $formatter
     * @return static
     */
    final public function appendFormatter(FormatInterface $formatter): static
    {
        $this->formatters[] = $formatter;
        return $this;
    }

    /**
     * Prepends a formatter to the list, ensuring it is applied first.
     *
     * @param FormatInterface $formatter
     * @return static
     */
    final public function prependFormatter(FormatInterface $formatter): static
    {
        $this->formatters = array_merge([$formatter], $this->formatters);
        return $this;
    }

    /**
     * Creates a default logger only when needed, as production systems are likely to replace it.
     * It doesn't make sense to always instantiate the default logger upfront.
     *
     * @return Logger
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

    public function add(Level $level, string $message, mixed $context, ?string $module): void
    {
        foreach ($this->formatters as $formatter) {
            $temp = $formatter->format($context);
            if (is_array($temp)) {
                $context = $temp;
            }
        }
        $module = is_null($module) ? $this->defaultModuleName : $module;
        self::getLogger($module)->add(
            $level,
            $message,
            is_array($context) ? $context : [],
            $module,
        );
    }
}