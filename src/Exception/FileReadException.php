<?php

namespace HappyHippyHippo\TextIO\Exception;

use Throwable;

class FileReadException extends Exception
{
    /**
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct('file read error', $code, $previous);
    }
}
