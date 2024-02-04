<?php

namespace Happyhippyhippo\TextIO\Exception;

use Throwable;

class FileNotReadableException extends Exception
{
    /**
     * @param string $path
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(string $path, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct('file not readable : ' . $path, $code, $previous);
    }
}
