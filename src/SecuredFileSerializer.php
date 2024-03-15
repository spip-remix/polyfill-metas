<?php

namespace SpipRemix\Polyfill\Meta;

use Spip\Component\Filesystem\FilesystemInterface;
use SpipRemix\Contracts\EncoderInterface;
use SpipRemix\Polyfill\Meta\Exception\EncoderException;

/**
 * Gestion du fichier de cache sécurisé des métas.
 *
 * @author JamesRezo <james@rezo.net>
 */
class SecuredFileSerializer implements EncoderInterface
{
    public const PHP_SECURED_HEADER = '<?php die (\'Acces interdit\'); ?>' . "\n";

    /**
     * @param non-empty-string $filename Chemin absolu du fichier sécurisé
     */
    public function __construct(
        private FilesystemInterface $filesystem,
        private EncoderInterface $serializer,
        private string $filename,
    ) {
    }

    public function encode(mixed $decoded): string
    {
        $encoded = $this->serializer->encode($decoded);
        $encoded = self::PHP_SECURED_HEADER . $encoded;

        if(!\is_dir(dirname($this->filename))) {
            $this->filesystem->mkdir(\dirname($this->filename));
        }

        if (!$this->filesystem->write($this->filename, $encoded)) {
            throw EncoderException::with();
        }

        return $encoded;
    }

    public function decode(string $encoded): mixed
    {
        $decoded = null;

        $encoded = $this->filesystem->read($this->filename);
        dump($this->filename, $encoded);
        if (!empty($encoded)) {
            $encoded = substr($encoded, strlen(self::PHP_SECURED_HEADER));
            $decoded = $this->serializer->decode($encoded);
            if ($decoded === false) {
                throw EncoderException::with();
            }
        }

        return $decoded;
    }

    public function getTimestamp(): ?int
    {
        return $this->filesystem->mtime($this->filename);
    }
}
