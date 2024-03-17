<?php

namespace SpipRemix\Polyfill\Meta\Test\Test;

use PHPUnit\Framework\Attributes\CoversClass;
use Psr\Log\LoggerInterface;
use SpipRemix\Contracts\MetaHandlerInterface;
use SpipRemix\Polyfill\Meta\MetaHandlerTrait;
use SpipRemix\Polyfill\Meta\Test\TestCase;

#[CoversClass(MetaHandlerTrait::class)]
class MetaHandlerTraitTest extends TestCase
{
    private MetaHandlerInterface $metaHandler;

    public function setUp(): void
    {
        $this->metaHandler = $this->getMetaHandlerMock();
    }

    public function testAll(): void
    {
        // Given
        $expected = 'a:1:{i:0;a:3:{'
            . 's:3:"nom";s:5:"dummy";'
            . 's:6:"valeur";s:4:"test";'
            . 's:10:"importable";b:1;'
            . '}}';

        // When
        $actual = \serialize($this->metaHandler->all());

        // Then
        $this->assertEquals($expected, $actual);
    }

    public function testSerialization(): void
    {
        // Given
        $expected = 'O:44:"SpipRemix\Component\Sdk\Mock\MetaHandlerMock":2:{'
            . 's:5:"metas";a:1:{s:5:"dummy";s:4:"test";}'
            . 's:11:"importables";a:1:{s:5:"dummy";b:1;}'
            . '}';
        /** @var MetaHandlerInterface $metas */
        $metas = \unserialize($expected);
        $expectedDummy = 'test';

        // When
        $actual = \serialize($this->metaHandler);
        $actualDummy = $metas->read('dummy', 'dummy');

        // Then
        $this->assertEquals($expected, $actual);
        $this->assertEquals($expectedDummy, $actualDummy);
    }

    public function testDefaultValue(): void
    {
        // Given
        $expectedArray = [0, 1, 2];

        // When
        $actual = $this->metaHandler->read('unknown_meta', \range(0, 2));

        // Then
        $this->assertEquals($expectedArray, $actual);
    }

    public function testSet(): void
    {
        // Given
        $expectedNewMeta = 'added';
        $expectedModifiedMeta = 'changed';

        // When
        $actualBeforeNew = $this->metaHandler->read('new');
        $actualBeforeImportable = $this->filter($this->metaHandler, 'dummy', 'importable');
        $this->metaHandler->write('new', 'added', true);
        $this->metaHandler->write('dummy', 'changed', false);
        $actualModifiedImportable = $this->filter($this->metaHandler, 'dummy', 'importable');

        // Then
        $this->assertTrue($actualBeforeImportable);
        $this->assertEquals(null, $actualBeforeNew);
        $this->assertEquals($expectedNewMeta, $this->metaHandler->read('new'));
        $this->assertEquals($expectedModifiedMeta, $this->metaHandler->read('dummy'));
        $this->assertFalse($actualModifiedImportable);
    }

    public function testClear(): void
    {
        // When
        $this->metaHandler->clean();

        // Then
        $this->assertEmpty($this->metaHandler->all());
        $this->assertNull($this->metaHandler->read('dummy'));
    }

    public function testUnset(): void
    {
        // Given
        $this->metaHandler->write('new', 'added');

        // When
        $this->metaHandler->erase('new');

        // Then
        $this->assertNull($this->metaHandler->read('new'));
    }

    public function testGetLogger(): void
    {
        $this->assertInstanceOf(LoggerInterface::class, $this->metaHandler->getLogger());
    }
}
