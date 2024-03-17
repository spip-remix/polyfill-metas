<?php

declare(strict_types=1);

namespace SpipRemix\Polyfill\Meta;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

interface LoggerGetterInterface extends LoggerAwareInterface
{
    public function getLogger(): ?LoggerInterface;
}
