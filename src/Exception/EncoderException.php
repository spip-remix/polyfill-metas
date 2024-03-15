<?php

declare(strict_types=1);

namespace SpipRemix\Polyfill\Meta\Exception;

use SpipRemix\Contracts\Exception\ExceptionInterface;

final class EncoderException extends \LogicException implements ExceptionInterface
{
    public static function throw(string ...$context): static
    {
        throw new static(sprintf('Encoder: La valeur "%s" n\'est pas encodable.', ...$context));
    }
}
