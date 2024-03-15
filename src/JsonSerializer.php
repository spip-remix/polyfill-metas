<?php

namespace SpipRemix\Polyfill\Meta;

use SpipRemix\Contracts\EncoderInterface;
use SpipRemix\Polyfill\Meta\Exception\EncoderException;

/**
 * Sérialisation/Dé-sérialisation JSON.
 *
 * @api
 *
 * @author JamesRezo <james@rezo.net>
 */
class JsonSerializer implements EncoderInterface
{
    public function encode(mixed $decoded): string
    {
        // Éviter les fonctions anonymes
        if ($decoded instanceof \Closure) {
            throw EncoderException::with('fonction');
        }

        return \json_encode($decoded);
    }

    public function decode(string $encoded): mixed
    {
        // \set_error_handler(function (int $errno, ...$unused) {
        //     if ($errno == 2) {
        //         return true;
        //     }

        //     return false;
        // }, \E_WARNING);
        // $decoded = \unserialize($encoded);
        // \restore_error_handler();

        // Si la chaîne encodée n'est pas sérialisable,
        // c'est une valeur stockable directement en base.
        // if ($decoded === false) {
        //     return $encoded;
        // }

        return \json_decode($encoded);
    }
}
