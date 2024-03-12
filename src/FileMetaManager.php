<?php

declare(strict_types=1);

namespace SpipRemix\Polyfill\Meta;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use SpipRemix\Contracts\MetaManagerInterface;

/**
 * Undocumented class.
 *
 * @api
 *
 * @author JamesRezo <james@rezo.net>
 */
class FileMetaManager implements MetaManagerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;
    use DecoratedMetaManagerTrait;

    /**
     * @internal
     */
    public function getLogger(): ?LoggerInterface
    {
        return $this->logger;
    }
}
