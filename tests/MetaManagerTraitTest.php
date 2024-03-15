<?php

namespace SpipRemix\Contracts\Test;

use PHPUnit\Framework\Attributes\CoversClass;
use Psr\Log\LoggerInterface;
use SpipRemix\Contracts\MetaManagerInterface;
use SpipRemix\Contracts\MetaManagerTrait;

#[CoversClass(MetaManagerTrait::class)]
class MetaManagerTraitTest extends TestCase
{
    private MetaManagerInterface $metaManager;

    public function setUp(): void
    {
        $this->metaManager = $this->getMetaManagerMock();
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
        $actual = \serialize($this->metaManager->all());

        // Then
        $this->assertEquals($expected, $actual);
    }

    public function testSerialization(): void
    {
        // Given
        $expected = 'O:44:"SpipRemix\Component\Sdk\Mock\MetaManagerMock":2:{'
            . 's:5:"metas";a:1:{s:5:"dummy";s:4:"test";}'
            . 's:11:"importables";a:1:{s:5:"dummy";b:1;}'
            . '}';
        /** @var MetaManagerInterface $metas */
        $metas = \unserialize($expected);
        $expectedDummy = 'test';

        // When
        $actual = \serialize($this->metaManager);
        $actualDummy = $metas->get('dummy', 'dummy');

        // Then
        $this->assertEquals($expected, $actual);
        $this->assertEquals($expectedDummy, $actualDummy);
    }

    public function testDefaultValue(): void
    {
        // Given
        $expectedArray = [0, 1, 2];

        // When
        $actual = $this->metaManager->get('unknown_meta', \range(0, 2));

        // Then
        $this->assertEquals($expectedArray, $actual);
    }

    public function testSet(): void
    {
        // Given
        $expectedNewMeta = 'added';
        $expectedModifiedMeta = 'changed';

        // When
        $actualBeforeNew = $this->metaManager->get('new');
        $actualBeforeImportable = $this->filter($this->metaManager, 'dummy', 'importable');
        $this->metaManager->set('new', 'added', true);
        $this->metaManager->set('dummy', 'changed', false);
        $actualModifiedImportable = $this->filter($this->metaManager, 'dummy', 'importable');

        // Then
        $this->assertTrue($actualBeforeImportable);
        $this->assertEquals(null, $actualBeforeNew);
        $this->assertEquals($expectedNewMeta, $this->metaManager->get('new'));
        $this->assertEquals($expectedModifiedMeta, $this->metaManager->get('dummy'));
        $this->assertFalse($actualModifiedImportable);
    }

    public function testClear(): void
    {
        // When
        $this->metaManager->clear();

        // Then
        $this->assertEmpty($this->metaManager->all());
        $this->assertNull($this->metaManager->get('dummy'));
    }

    public function testUnset(): void
    {
        // Given
        $this->metaManager->set('new', 'added');

        // When
        $this->metaManager->unset('new');

        // Then
        $this->assertNull($this->metaManager->get('new'));
    }

    public function testGetLogger(): void
    {
        $this->assertInstanceOf(LoggerInterface::class, $this->metaManager->getLogger());
    }
}
