<?php

declare(strict_types=1);

namespace SpipRemix\Polyfill\Meta;

use Psr\Clock\ClockInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;

/**
 * Trait pour mise en commun de la gestion des métas.
 *
 * @author JamesRezo <james@rezo.net>
 */
trait MetaHandlerTrait
{
    use LoggerAwareTrait;

    private ClockInterface $clock;

    /**
     * @todo Éviter un constructeur ici. Utilliser with() si besoin
     * @todo mono tableau pour les valeurs et l'importabilité ?
     * @todo class Meta ?
     */
    public function __construct(
        /** @var array<string,mixed> $metas */
        private array $metas = [],
        /** @var array<string,bool> $metas */
        private array $importables = [],
        private ?int $mtime = null,
        ?ClockInterface $clock = null,
    ) {
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

        return new static($metas, $importables);
    }

    /**
     * @throws MetaHandlerException
     */
    abstract public function boot(): void;

    /**
     * @return null|positive-int
     */
    public function lastModified(): ?int
    {
        return $this->mtime;
    }

    /**
     * @internal Undocumented function.
     */
    public function getLogger(): ?LoggerInterface
    {
        return $this->logger;
    }

    public function all(): array
    {
        $metas = [];

        foreach ($this->metas as $name => $value) {
            $metas[] = ['nom' => $name, 'valeur' => $value, 'importable' => $this->importables[$name] ?? true];
        }

        return $metas;
    }

    public function read(string $name, mixed $default = null): mixed
    {
        if (array_key_exists($name, $this->metas)) {
            return $this->metas[$name];
        }

        return $default;
    }

    public function write(string $name, mixed $value, bool $importable = true): bool
    {
        $this->metas[$name] = $value;
        $this->importables[$name] = $importable;

        return true;
    }

    public function clean(): void
    {
        $this->metas = [];
        $this->importables = [];
    }

    public function erase(string $name): void
    {
        unset($this->metas[$name]);
        unset($this->importables[$name]);
    }

    public function __serialize(): array
    {
        return [
            'metas' => $this->metas,
            'importables' => $this->importables
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->metas = $data['metas'];
        $this->importables = $data['importables'];
    }
}
