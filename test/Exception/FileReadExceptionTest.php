<?php

namespace Exception;

use Exception;
use HappyHippyHippo\TextIO\Exception\FileReadException;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \HappyHippyHippo\TextIO\Exception\FileReadException
 */
class FileReadExceptionTest extends TestCase
{
    /**
     * @return void
     *
     * @covers ::__construct
     */
    public function testConstructor(): void
    {
        $code = 123;
        $reason = new Exception();

        $exception = new FileReadException($code, $reason);

        $this->assertEquals('file read error', $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
        $this->assertEquals($reason, $exception->getPrevious());
    }
}
