<?php

namespace SpipRemix\Polyfill\Meta\Test;

use PHPUnit\Framework\TestCase as FrameworkTestCase;
use SpipRemix\Component\Sdk\Mock\MetaManagerMock;
use SpipRemix\Contracts\MetaManagerInterface;

class TestCase extends FrameworkTestCase
{
    public function getMetaManagerMock()
    {
        $mock = new MetaManagerMock([
            'dummy' => 'test',
        ], [
            'dummy' => true,
        ]);
        $mock->setLogger(\spip_logger());

        return $mock;
    }

    /**
     * Filter all() pour récupérer la valeur ou l'importabilité d'une mét.
     *
     * @param string $what 'valeur' par défaut, autre possibilité: 'importable'
     */
    public function filter(MetaManagerInterface $metas, string $name, string $what = 'valeur'): mixed
    {
        if (!\in_array($what, ['valeur', 'importable'])) {
            return null;
        }

        $meta = array_filter($metas->all(), fn ($meta) => $meta['nom'] == $name);
        $meta = array_shift($meta);

        return $meta[$what];
    }
}
