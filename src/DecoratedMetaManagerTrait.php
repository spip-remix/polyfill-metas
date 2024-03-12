<?php

declare(strict_types=1);

namespace SpipRemix\Polyfill\Meta;

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
        // $this->decorated->setLogger($decorated->getLogger());
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
