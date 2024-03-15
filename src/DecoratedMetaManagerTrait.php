<?php

declare(strict_types=1);

namespace SpipRemix\Polyfill\Meta;

use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use SpipRemix\Contracts\MetaManagerInterface;

/**
 * Undocumented trait.
 *
 * @api
 *
 * @author JamesRezo <james@rezo.net>
 */
trait DecoratedMetaManagerTrait
{
    use LoggerAwareTrait;
    private MetaManagerInterface $decorated;

    /**
     * @param array<string,mixed> $metas
     * @param array<string,bool> $importables
     */
    public function __construct(
        ?MetaManagerInterface $decorated = null,
        array $metas = [],
        array $importables = [],
    ) {
        $this->decorated = $decorated ?? new PersistentMetaManager($metas, $importables);
        if (!\is_null($this->decorated->getLogger())) {
            $this->setLogger($this->decorated->getLogger());
        }
    }

    /**
     * Démarrage du MetaManager.
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

    public function get(string $name, mixed $default = null): mixed
    {
        return $this->decorated->get($name, $default);
    }

    public function set(string $name, mixed $value = null, bool $importable = false): void
    {
        $this->decorated->set($name, $value, $importable);
    }

    public function clear(): void
    {
        $this->decorated->clear();
    }

    public function unset(string $name): void
    {
        $this->decorated->unset($name);
    }
}
