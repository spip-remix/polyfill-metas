<?php

declare(strict_types=1);

namespace SpipRemix\Polyfill\Meta;

use Psr\Clock\ClockInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use SpipRemix\Contracts\MetaHandlerInterface;

/**
 * Undocumented trait.
 *
 * @api
 *
 * @author JamesRezo <james@rezo.net>
 */
trait DecoratedMetaHandlerTrait
{
    use LoggerAwareTrait;

    private MetaHandlerInterface $decorated;

    private ClockInterface $clock;

    /**
     * @param array<string,mixed> $metas
     * @param array<string,bool> $importables
     */
    public function __construct(
        ?MetaHandlerInterface $decorated = null,
        array $metas = [],
        array $importables = [],
        private ?int $mtime = null,
        ?ClockInterface $clock = null,
    ) {
        $this->decorated = $decorated ?? new PersistentMetaHandler($metas, $importables);
        if ($decorated instanceof LoggerGetterInterface) {
            $this->setLogger($decorated->getLogger());
        }
        $this->clock = $clock ?? new class () implements ClockInterface {
            public function now(): \DateTimeImmutable
            {
                return new \DateTimeImmutable('now');
            }
        };
        if (\is_null($mtime)) {
            $this->mtime = (int) $this->clock->now()->format('U');
        }
    }

    /**
     * @param list<array{name:non-empty-string,value:mixed,importable?:bool}> $metas
     */
    public static function with(array $metas = []): static
    {
        $metas = [];
        $importables = [];

        foreach ($metas as $meta) {
            $metas[$meta['name']] = $meta['value'];
            $importables[$meta['name']] = $meta['importable'] ?? true;
        }

        return new static(metas:$metas, importables:$importables);
    }

    public function lastModified(): ?int
    {
        return $this->mtime;
    }

    /**
     * Démarrage du MetaHandler.
     */
    abstract public function boot(): void;

    /**
     * @internal
     */
    public function getLogger(): ?LoggerInterface
    {
        return $this->logger;
    }

    public function all(): array
    {
        return $this->decorated->all();
    }

    public function read(string $name, mixed $default = null): mixed
    {
        return $this->decorated->read($name, $default);
    }

    public function write(string $name, mixed $value = null, bool $importable = false): bool
    {
        return $this->decorated->write($name, $value, $importable);
    }

    public function clean(): bool
    {
        return $this->decorated->clean();
    }

    public function erase(string $name): bool
    {
        return $this->decorated->erase($name);
    }
}
