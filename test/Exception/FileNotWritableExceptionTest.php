<?php

namespace Exception;

use Exception;
use HappyHippyHippo\TextIO\Exception\FileNotWritableException;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \HappyHippyHippo\TextIO\Exception\FileNotWritableException
 */
class FileNotWritableExceptionTest extends TestCase
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

        $exception = new FileNotWritableException($path, $code, $reason);

        $this->assertEquals('file not writable : ' . $path, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
        $this->assertEquals($reason, $exception->getPrevious());
    }
}
