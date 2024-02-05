<?php

namespace HappyHippyHippo\TextIO;

use Generator;
use HappyHippyHippo\TextIO\Exception\FileReadException;

interface InputStreamInterface
{
    /**
     * @return Encode
     */
    public function encoding(): Encode;

    /**
     * @param Encode $target
     * @return Generator
     * @throws FileReadException
     */
    public function lines(Encode $target = Encode::UTF8): Generator;
}
