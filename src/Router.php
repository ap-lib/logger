<?php declare(strict_types=1);

namespace AP\Logger;

use AP\Logger\Dumper\AddInterface;
use AP\Normalizer\BaseNormalizer;
use AP\Normalizer\Normalized;
use AP\Normalizer\ThrowableNormalizer;

class Router
{
    protected ?Logger $defaultLogger = null;
    /**
     * @var array<string, Logger>  Special logger for module by module name
     */
    protected array $specialLoggers = [];

    protected BaseNormalizer $contextNormalizer;

    /**
     * @param string $defaultModuleName
     */
    public function __construct(readonly protected string $defaultModuleName)
    {
        $this->contextNormalizer = new BaseNormalizer([
            new ThrowableNormalizer
        ]);
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

    final public function getContextNormalizer(): BaseNormalizer
    {
        return $this->contextNormalizer;
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
        $module  = is_null($module) ? $this->defaultModuleName : $module;
        $context = $this->contextNormalizer->normalize($context);
        $context = $context instanceof Normalized ? $context->value : [];

        self::getLogger($module)->add(
            $level,
            $message,
            is_array($context) ? $context : [],
            $module,
        );
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
}