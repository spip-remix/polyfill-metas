<?php

declare(strict_types=1);

namespace SpipRemix\Polyfill\Meta;

use SpipRemix\Component\Serializer\FreshInterface;
use SpipRemix\Contracts\MetaHandlerInterface;
use SpipRemix\Polyfill\Meta\Exception\MetaHandlerException;

/**
 * Undocumented class.
 *
 * @api
 *
 * @author JamesRezo <james@rezo.net>
 */
class FileMetaHandler implements MetaHandlerInterface, LoggerGetterInterface
{
    use DecoratedMetaHandlerTrait;

    // Durée maximale du cache. Le double pour l'antidater
    public const CACHE_PERIOD = 1 << 24;

    private FreshInterface $serializer;

    public function setSerializer(FreshInterface $serializer): void
    {
        $this->serializer = $serializer;
    }

    public function boot(): void
    {
        $all = $this->serializer->fresh((int) $this->clock->now()->format('U'), self::CACHE_PERIOD);

        if (\is_null($all)) {
            $this->decorated->boot();
            $all = $this->decorated->all();
            if (!$this->serializer->refresh($all)) {
                MetaHandlerException::throw('récupération des données (cache et base)');
            }
        }

        $this->with($all);
    }

    /**
     * Undocumented function.
     *
     * @param non-empty-string $tmpDir
     * @param non-empty-string $cacheDir
     * @param non-empty-string $table
     *
     * @return non-empty-string
     */
    public function getCacheFilename(
        string $tmpDir,
        string $cacheDir,
        string $table = 'meta'
    ): string{
        $cacheFilename = $table == 'meta' ? '{tmpDir}/meta_cache.php' : '{cacheDir}/{table}.php';
        /** @var non-empty-string $cacheFilename */
        $cacheFilename = str_replace(['{tmpDir}', '{cacheDir}', '{table}'], [$tmpDir, $cacheDir, $table], $cacheFilename);

        return $cacheFilename;
    }
}
