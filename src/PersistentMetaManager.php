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
class PersistentMetaManager implements MetaManagerInterface
{
    use MetaManagerTrait;

    public function boot(): void
    {
        // SELECT * FROM spip_meta;
    }
}
