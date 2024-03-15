<?php

declare(strict_types=1);

namespace SpipRemix\Polyfill\Meta;

use SpipRemix\Contracts\EncoderInterface;
use SpipRemix\Contracts\MetaManagerInterface;

/**
 * Undocumented class.
 *
 * @api
 *
 * @author JamesRezo <james@rezo.net>
 */
class FileMetaManager implements MetaManagerInterface
{
    use DecoratedMetaManagerTrait;
    // Durée maximale du cache. Le double pour l'antidater
    public const CACHE_PERIOD = 1 << 24;

    private EncoderInterface $serializer;

    public function setSerializer(EncoderInterface $serializer): void
    {
        $this->serializer = $serializer;
    }

    public function boot(): void
    {
        $timestamp = $this->serializer->getTimestamp();
        if (time() - $timestamp < self::CACHE_PERIOD) {
            $all = $this->serializer->decode(''); //c'est décode qui doit faire mtime et renvoyer null si absent ou vieux, comment envoyer le cache period ?
            $this->decorated->with($all);
            return;
        }
        $this->decorated->boot();
    }
}
