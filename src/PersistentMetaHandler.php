<?php

declare(strict_types=1);

namespace SpipRemix\Polyfill\Meta;

use SpipRemix\Contracts\MetaHandlerInterface;

/**
 * Undocumented class.
 *
 * @api
 *
 * @author JamesRezo <james@rezo.net>
 */
class PersistentMetaHandler implements MetaHandlerInterface, LoggerGetterInterface
{
    use MetaHandlerTrait;

    /** @todo ajouter le timestamp maj ? */

    public function boot(): void
    {
        // SELECT * FROM spip_meta;
    }

    private function checkLastMaj(): void
    {
        // SELECT MAX(maj) AS last_maj FROM spip_meta;
    }
}
