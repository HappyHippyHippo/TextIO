<?php

namespace Happyhippyhippo\TextIO\tests\Exception;

use Exception;
use Happyhippyhippo\TextIO\Exception\FileOpenException;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Happyhippyhippo\TextIO\Exception\FileOpenException
 */
class FileOpenExceptionTest extends TestCase
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

        $exception = new FileOpenException($path, $code, $reason);

        $this->assertEquals('file open error : ' . $path, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
        $this->assertEquals($reason, $exception->getPrevious());
    }
}
