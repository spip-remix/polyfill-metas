<?php

namespace SpipRemix\Polyfill\Meta\Exception;

use SpipRemix\Contracts\Exception\ExceptionInterface;

/**
 * Undocumented class.
 */
final class MetaManagerException extends \LogicException implements ExceptionInterface
{
    public static function throw(string ...$context): static
    {
        throw new static(sprintf('Erreur %s du MetaManager.', ...$context));
    }
}
