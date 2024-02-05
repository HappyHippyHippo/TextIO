<?php

namespace HappyHippyHippo\TextIO\Exception;

use Throwable;

class FileWriteException extends Exception
{
    /**
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct('file write error', $code, $previous);
    }
}
