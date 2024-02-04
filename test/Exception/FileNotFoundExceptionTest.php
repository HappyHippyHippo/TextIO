<?php

namespace Happyhippyhippo\TextIO\tests\Exception;

use Exception;
use Happyhippyhippo\TextIO\Exception\FileNotFoundException;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Happyhippyhippo\TextIO\Exception\FileNotFoundException
 */
class FileNotFoundExceptionTest extends TestCase
{
    /**
     * @return void
     *
     * @covers ::__construct
     */
    public function testConstructor(): void
    {
        $path = 'test string';
        $code = 123;
        $reason = new Exception();

        $exception = new FileNotFoundException($path, $code, $reason);

        $this->assertEquals('file not found : ' . $path, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
        $this->assertEquals($reason, $exception->getPrevious());
    }
}
