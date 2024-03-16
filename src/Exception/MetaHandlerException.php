<?php

namespace SpipRemix\Polyfill\Meta\Exception;

use SpipRemix\Contracts\Exception\ExceptionInterface;

/**
 * Undocumented class.
 */
final class MetaHandlerException extends \LogicException implements ExceptionInterface
{
    public static function throw(string ...$context): static
    {
        throw new static(sprintf('Erreur %s du MetaHandler.', ...$context));
    }
}
