<?php

declare(strict_types=1);

namespace Platine\Test;

use InvalidArgumentException;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamFile;
use PHPUnit\Framework\TestCase;
use Platine\Dev\PlatineTestCase;
use Platine\Test\Fixture\Dev\ClassToMock;
use Platine\Test\Fixture\Dev\GetPrivateProtectedAttributeTestClass;
use ReflectionProperty;

/**
 * PlatineTestCase class tests
 */
class PlatineTestCaseTest extends TestCase
{
    public function testGetPrivateProtectedAttribute(): void
    {
        $p = new PlatineTestCase();

        $o = new GetPrivateProtectedAttributeTestClass();
        $oName = $p->getPrivateProtectedAttribute(
            GetPrivateProtectedAttributeTestClass::class,
            'name'
        );
        $oValue = $p->getPrivateProtectedAttribute(
            GetPrivateProtectedAttributeTestClass::class,
            'value'
        );

        $this->assertInstanceOf(ReflectionProperty::class, $oName);
        $this->assertInstanceOf(ReflectionProperty::class, $oValue);
        $this->assertEquals('foo', $oName->getValue($o));
        $this->assertEquals(123, $oValue->getValue($o));
    }

    public function testRunPrivateProtectedMethod(): void
    {
        $p = new PlatineTestCase();

        $o = new GetPrivateProtectedAttributeTestClass();
        $name = $p->runPrivateProtectedMethod($o, 'privateMethod');
        $value = $p->runPrivateProtectedMethod($o, 'protectedMethod', [2]);

        $this->assertEquals('foo', $name);
        $this->assertEquals(246, $value);
    }

    public function testCreateVfsFile(): void
    {
        $p = new PlatineTestCase();

        $vfsRoot = vfsStream::setup();
        $vfsPath = vfsStream::newDirectory('platine')->at($vfsRoot);

        $filename = 'app.txt';
        $this->assertFalse($vfsPath->hasChild($filename));

        $vfsFile1 = $p->createVfsFile($filename, $vfsPath);

        $this->assertEmpty($vfsFile1->getContent());

        //using content
        $vfsFile2 = $p->createVfsFile($filename, $vfsPath, 'foo');
        $this->assertEquals('foo', $vfsFile2->getContent());
    }

    public function testCreateVfsFileOnly(): void
    {
        $p = new PlatineTestCase();

        $vfsRoot = vfsStream::setup();
        $vfsPath = vfsStream::newDirectory('platine')->at($vfsRoot);

        $filename = 'app.txt';
        $this->assertFalse($vfsPath->hasChild($filename));

        $vfsFile = $p->createVfsFileOnly($filename, $vfsPath);

        $this->assertTrue($vfsPath->hasChild($filename));
        $this->assertInstanceOf(vfsStreamFile::class, $vfsFile);
    }

    public function testCreateVfsDirectory(): void
    {
        $p = new PlatineTestCase();

        $vfsRoot = vfsStream::setup();
        $vfsPath = vfsStream::newDirectory('platine')->at($vfsRoot);

        $directory = 'tmp';
        $this->assertFalse($vfsPath->hasChild($directory));

        $vfsDir = $p->createVfsDirectory($directory, $vfsPath);

        $this->assertTrue($vfsPath->hasChild($directory));
        $this->assertInstanceOf(vfsStreamDirectory::class, $vfsDir);

        //using root path
        $this->assertFalse($vfsRoot->hasChild($directory));

        $vfsDirNoParent = $p->createVfsDirectory($directory);

        $this->assertEquals($directory, $vfsDirNoParent->path());
        $this->assertInstanceOf(vfsStreamDirectory::class, $vfsDir);
    }


    public function testCreateFile(): void
    {
        $p = new PlatineTestCase();

        $filename = '/app.txt';
        $f = $p->createFile($filename, 'foo');
        $this->assertEquals('/app.txt', $f->path());
        $this->assertEquals('foo', $f->data());
    }


    public function testCreateDirectory(): void
    {
        $p = new PlatineTestCase();

        $path = '/platine';
        $o = $p->createDirectory($path, true);

        $this->assertEquals('/platine', $o->path());
    }

    public function testGetClassMethodsToMockMockAllMethod(): void
    {
        $className = ClassToMock::class;
        $p = new PlatineTestCase();

        $methods = $p->getClassMethodsToMock($className, []);

        $this->assertIsArray($methods);
        $this->assertCount(2, $methods);
        $this->assertContains('a', $methods);
        $this->assertContains('b', $methods);

        /** @var ClassToMock $mock */
        $mock = $this->getMockBuilder($className)
                ->onlyMethods($methods)
                ->getMock();

        $this->assertEquals(0, $mock->a());
        $this->assertFalse($mock->b(67));
    }

    public function testGetClassMethodsToMockMockPartialMethod(): void
    {
        $className = ClassToMock::class;
        $p = new PlatineTestCase();

        $methods = $p->getClassMethodsToMock($className, ['a']);

        $this->assertIsArray($methods);
        $this->assertCount(1, $methods);
        $this->assertContains('b', $methods);

        /** @var ClassToMock $mock */
        $mock = $this->getMockBuilder($className)
                ->onlyMethods($methods)
                ->getMock();

        $this->assertEquals($mock->a(), 10);
        $this->assertFalse($mock->b(67));
    }

    public function testGetClassMethodsToMockClassNotExist(): void
    {
        $className = 'ClassToMockDoesNotExist';
        $p = new PlatineTestCase();

        $this->expectException(InvalidArgumentException::class);
        $p->getClassMethodsToMock($className);
    }

    public function testGetMockInstance(): void
    {
        $p = new PlatineTestCase();

        $mock = $p->getMockInstance(ClassToMock::class, ['a' => 50], ['b']);
        $this->assertEquals(50, $mock->a());
        $this->assertTrue($mock->b(34));
        $this->assertFalse($mock->b(-34));
    }

    public function testGetMockInstanceMap(): void
    {
        $p = new PlatineTestCase();

        $mock = $p->getMockInstanceMap(
            ClassToMock::class,
            [
                'b' => [
                    [1, false],
                    [2, true],
                ]
            ],
            ['a']
        );
        $this->assertEquals(10, $mock->a());
        $this->assertTrue($mock->b(2));
        $this->assertFalse($mock->b(1));
    }

    public function testGetSetPropertyValue(): void
    {
        $instance = new ClassToMock();
        $p = new PlatineTestCase();

        $p->setPropertyValue(ClassToMock::class, $instance, 'a', 22);
        $a22 = $p->getPropertyValue(ClassToMock::class, $instance, 'a');
        $this->assertEquals(22, $a22);
    }

    public function testCommandOutput(): void
    {
        $p = new PlatineTestCase();

        $expected = 'a';
        $p->assertCommandOutput($expected, 'a');
    }
}
