<?php

declare(strict_types=1);

namespace SpipRemix\Polyfill\Meta;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use SpipRemix\Contracts\MetaManagerInterface;
use SpipRemix\Contracts\MetaManagerTrait;

/**
 * Undocumented class.
 *
 * @api
 *
 * @author JamesRezo <james@rezo.net>
 */
class PersistentMetaManager implements MetaManagerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;
    use MetaManagerTrait;

    /**
     * @internal
     */
    public function getLogger(): ?LoggerInterface
    {
        return $this->logger;
    }
}
