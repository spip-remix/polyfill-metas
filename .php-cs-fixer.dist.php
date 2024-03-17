<?php

/**
 * @see https://cs.symfony.com/doc/config.html
 */

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__ . '/src', __DIR__ . '/inc')
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@PER-CS' => true,
    ])
    ->setFinder($finder)
;
