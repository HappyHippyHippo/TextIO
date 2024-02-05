<?php

namespace HappyHippyHippo\TextIO\Exception;

use Throwable;

class FileNotFoundException extends Exception
{
    /**
     * @param string $path
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(string $path, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct('file not found : ' . $path, $code, $previous);
    }
}
