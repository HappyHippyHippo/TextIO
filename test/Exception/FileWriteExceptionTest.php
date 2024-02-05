<?php

namespace Exception;

use Exception;
use HappyHippyHippo\TextIO\Exception\FileWriteException;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \HappyHippyHippo\TextIO\Exception\FileWriteException
 */
class FileWriteExceptionTest extends TestCase
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

        $exception = new FileWriteException($code, $reason);

        $this->assertEquals('file write error', $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
        $this->assertEquals($reason, $exception->getPrevious());
    }
}
