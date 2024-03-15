<?php

namespace SpipRemix\Polyfill\Meta;

use SpipRemix\Contracts\EncoderInterface;
use SpipRemix\Polyfill\Meta\Exception\EncoderException;

/**
 * Sérialisation/Dé-sérialisation Native de PHP.
 *
 * @api
 *
 * @author JamesRezo <james@rezo.net>
 */
class NativeSerializer implements EncoderInterface
{
    public function encode(mixed $decoded): string
    {
        // Éviter les fonctions anonymes
        if ($decoded instanceof \Closure) {
            throw EncoderException::with('fonction');
        }

        // Ne sérialiser que les objets et les tableaux
        if (\is_array($decoded) || \is_object($decoded)) {
            return \serialize($decoded);
        }

        return $decoded;
    }

    public function decode(string $encoded): mixed
    {
        // Éviter un warning PHP
        \set_error_handler(function (int $errno, ...$unused) {
            if ($errno == 2) {
                return true;
            }

            return false;
        }, \E_WARNING);
        $decoded = \unserialize($encoded);
        \restore_error_handler();

        // Si la chaîne encodée n'est pas sérialisable,
        // c'est une chaîne stockée directement en base.
        if ($decoded === false) {
            return $encoded;
        }

        return $decoded;
    }
}
