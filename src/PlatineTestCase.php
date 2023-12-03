<?php

/**
 * Platine Dev Tools
 *
 * Platine Dev Tools is a collection of some classes/functions
 * designed for development
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2020 Platine Dev Tools
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

/**
 *  @file PlatineTestCase.php
 *
 *  The Base class used for test case
 *
 *  @package    Platine\Dev
 *  @author Platine Developers Team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   https://www.platine-php.com
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Dev;

use InvalidArgumentException;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamContainer;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamFile;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionProperty;

/**
 * @class PlatineTestCase
 * @package Platine\Dev
 */
class PlatineTestCase extends TestCase
{
    /**
     * Method to test private & protected method
     *
     * @param object $object the class instance to use
     * @param string $method the name of the method
     * @param array<int, mixed> $args the list of method arguments
     * @return mixed
     */
    public function runPrivateProtectedMethod(
        object $object,
        string $method,
        array $args = []
    ) {
        $reflection = new ReflectionClass(get_class($object));
        $reflectionMethod = $reflection->getMethod($method);
        $reflectionMethod->setAccessible(true);
        return $reflectionMethod->invokeArgs($object, $args);
    }

    /**
     * Method to set/get private & protected attribute
     *
     * @param string $className the name of the class
     * @param string $attr the name of the class attribute
     */
    public function getPrivateProtectedAttribute(
        string $className,
        string $attr
    ): ReflectionProperty {
        $rProp = new ReflectionProperty($className, $attr);
        $rProp->setAccessible(true);
        return $rProp;
    }

    /**
     * Create virtual file with the given content
     * @param  string $filename
     * @param  vfsStreamContainer<vfsStreamContainerIterator> $destination
     * @param  string $content
     * @return vfsStreamFile
     */
    public function createVfsFile(
        string $filename,
        vfsStreamContainer $destination,
        string $content = ''
    ): vfsStreamFile {
        return vfsStream::newFile($filename)
                        ->at($destination)
                        ->setContent($content);
    }

    /**
     * Create virtual file without content
     * @param  string $filename
     * @param  vfsStreamContainer<vfsStreamContainerIterator> $destination
     * @return vfsStreamFile
     */
    public function createVfsFileOnly(
        string $filename,
        vfsStreamContainer $destination
    ): vfsStreamFile {
        return vfsStream::newFile($filename)
                        ->at($destination);
    }

    /**
     * Create virtual directory
     * @param  string $name
     * @param  vfsStreamContainer<vfsStreamContainerIterator> $destination
     * @return vfsStreamDirectory
     */
    public function createVfsDirectory(
        string $name,
        vfsStreamContainer $destination = null
    ): vfsStreamDirectory {
        if ($destination) {
            return vfsStream::newDirectory($name)->at($destination);
        }
        return vfsStream::newDirectory($name);
    }

    /**
     * Return the list of methods to mocks in the parameters of PHPUnit::TestCase::getMock()
     *
     * @param class-string<object>|object $class
     * @param string[] $exclude list of methods to exclude
     * @return string[]
     */
    public function getClassMethodsToMock($class, array $exclude = []): array
    {
        $methods = [];

        if (is_string($class) && !class_exists($class)) {
            throw new InvalidArgumentException(
                sprintf('Can not find class [%s]', $class)
            );
        }

        $reflectionClass = new ReflectionClass($class);

        foreach ($reflectionClass->getMethods() as $reflectionMethod) {
            if (!in_array($reflectionMethod->name, $exclude)) {
                $methods[] = $reflectionMethod->name;
            }
        }

        return $methods;
    }

    /**
     * Get the instance of the given class
     * @param class-string $class
     * @param array<string, mixed> $mockMethods
     * @param array<int, string> $excludes
     * @return mixed
     */
    public function getMockInstance(
        string $class,
        array $mockMethods = [],
        array $excludes = []
    ) {
        $methods = $this->getClassMethodsToMock($class, $excludes);

        $mock = $this->getMockBuilder($class)
                    ->disableOriginalConstructor()
                    ->onlyMethods($methods)
                    ->getMock();

        foreach ($mockMethods as $method => $returnValue) {
            $mock->expects($this->any())
                ->method($method)
                ->will($this->returnValue($returnValue));
        }

        return $mock;
    }

    /**
     * Get the instance of the given class using return map
     * @param class-string $class
     * @param array<string, mixed> $mockMethods
     * @param array<int, string> $excludes
     * @return mixed
     */
    public function getMockInstanceMap(
        string $class,
        array $mockMethods = [],
        array $excludes = []
    ) {
        $methods = $this->getClassMethodsToMock($class, $excludes);

        $mock = $this->getMockBuilder($class)
                    ->disableOriginalConstructor()
                    ->onlyMethods($methods)
                    ->getMock();

        foreach ($mockMethods as $method => $returnValues) {
            $mock->expects($this->any())
                ->method($method)
                ->will(
                    $this->returnValueMap($returnValues)
                );
        }

        return $mock;
    }

    /**
     * Return the value of private or protected property
     * @param class-string $class
     * @param object $instance
     * @param string $name
     * @return mixed
     */
    public function getPropertyValue(string $class, object $instance, string $name)
    {
        $reflection = $this->getPrivateProtectedAttribute($class, $name);
        return $reflection->getValue($instance);
    }

    /**
     * Set the value of private or protected property
     * @param class-string $class
     * @param object $instance
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function setPropertyValue(string $class, object $instance, string $name, $value)
    {
        $reflection = $this->getPrivateProtectedAttribute($class, $name);
        $reflection->setValue($instance, $value);
    }

    /**
     * Test assert command expected given output
     * @param string $expected
     * @param string $output
     * @return void
     */
    public function assertCommandOutput(string $expected, string $output): void
    {
        $result = str_replace("\n", PHP_EOL, $expected);
        $this->assertEquals($result, $output);
    }

    /**
     * @codeCoverageIgnore
     * @return void
     */
    protected function tearDown(): void
    {
        //restore all mock variable global value to "false"
        foreach ($GLOBALS as $key => $value) {
            if (substr((string) $key, 0, 5) === 'mock_') {
                $GLOBALS[$key] = false;
            }
        }
    }
}
