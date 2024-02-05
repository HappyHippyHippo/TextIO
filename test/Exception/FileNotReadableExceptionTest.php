<?php

namespace HappyHippyHippo\TextIO\tests\Exception;

use Exception;
use HappyHippyHippo\TextIO\Exception\FileNotReadableException;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \HappyHippyHippo\TextIO\Exception\FileNotReadableException
 */
class FileNotReadableExceptionTest extends TestCase
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

        $exception = new FileNotReadableException($path, $code, $reason);

        $this->assertEquals('file not readable : ' . $path, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
        $this->assertEquals($reason, $exception->getPrevious());
    }
}
