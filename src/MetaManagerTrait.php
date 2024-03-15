<?php

declare(strict_types=1);

namespace SpipRemix\Polyfill\Meta;

use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;

/**
 * Trait pour mise en commun de la gestion des métas.
 *
 * @author JamesRezo <james@rezo.net>
 */
trait MetaManagerTrait
{
    use LoggerAwareTrait;

    /**
     * @todo peut-être éviter un constructeur ici (class abstraite dans le polyfill?)
     * @todo mono tableau pour les valeurs et l'importabilité ?
     * @todo ajouter le timestamp maj ?
     * @todo class Meta ?
     */
    public function __construct(
        /** @var array<string,mixed> $metas */
        private array $metas = [],
        /** @var array<string,bool> $metas */
        private array $importables = [],
    ) {
    }

    abstract public function boot(): void;

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

    public function get(string $name, mixed $default = null): mixed
    {
        if (array_key_exists($name, $this->metas)) {
            return $this->metas[$name];
        }

        return $default;
    }

    public function set(string $name, mixed $value = null, bool $importable = true): void
    {
        $this->metas[$name] = $value;
        $this->importables[$name] = $importable;
    }

    public function clear(): void
    {
        $this->metas = [];
        $this->importables = [];
    }

    public function unset(string $name): void
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

    /**
     * Affecter un lot de métas (lors du boot par exemple).
     *
     * @param list<array{name:non-empty-string,value:mixed,importable:bool}> $metas
     */
    protected function with(array $metas): void
    {
        foreach ($metas as $meta) {
            $this->metas[$meta['name']] = $meta['value'];
            $this->importables[$meta['name']] = $meta['importable'];
        }
    }
}
