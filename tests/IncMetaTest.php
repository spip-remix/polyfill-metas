<?php

declare(strict_types=1);

namespace SpipRemix\Polyfill\Meta\Test;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SpipRemix\Polyfill\Meta\DecoratedMetaManagerTrait;

#[CoversFunction('lire_meta')]
#[CoversFunction('ecrire_meta')]
#[CoversFunction('effacer_meta')]
#[CoversFunction('_service_metas')]
#[CoversClass(DecoratedMetaManagerTrait::class)]
class IncMetaTest extends TestCase
{
    public function setUp(): void
    {
        $GLOBALS['meta'] = ['test' => 'test'];
        $GLOBALS['other'] = [];
    }

    public function tearDown(): void
    {
        unset($GLOBALS['meta']);
        unset($GLOBALS['other']);
    }

    public static function dataLireMeta(): array
    {
        return [
            'name_unknown' => [
                'expected' => null,
                'other_expected' => null,
                'name' => 'dummy',
                'default' => null,
            ],
            'default' => [
                'expected' => 'default',
                'other_expected' => 'default',
                'name' => 'dummy',
                'default' => 'default',
            ],
            'name_exists' => [
                'expected' => 'test',
                'other_expected' => null,
                'name' => 'test',
                'default' => null,
            ],
        ];
    }

    #[DataProvider('dataLireMeta')]
    public function test lire meta($expected, $other_expected, $name, $default): void
    {
        // Given
        // When
        $empty_actual = \lire_meta($name, $default, '');
        $actual = \lire_meta($name, $default);
        $other_actual = \lire_meta($name, $default, 'other');

        // Then
        $this->assertEquals($expected, $empty_actual);
        $this->assertEquals($expected, $actual);
        $this->assertEquals($other_expected, $other_actual);
    }

    public static function dataEcrireMeta(): array
    {
        return [
            'changed' => [
                'expected' => 'set',
                'name' => 'test',
            ],
            'new' => [
                'expected' => 'set',
                'name' => 'dummy',
            ],
        ];
    }

    #[DataProvider('dataEcrireMeta')]
    public function test ecrire meta($expected, $name): void
    {
        // Given
        // When
        \ecrire_meta($name, 'set');
        \ecrire_meta($name, 'set', false, 'other');

        // Then
        $this->assertEquals($expected, $GLOBALS['meta'][$name]);
        $this->assertEquals($expected, $GLOBALS['other'][$name]);
    }

    public function test effacer meta(): void
    {
        // Given
        // When
        \effacer_meta('test');

        // Then
        $this->assertNull(\lire_meta('test'));
        $this->assertFalse(isset($GLOBALS['meta']['test']));
    }
}
