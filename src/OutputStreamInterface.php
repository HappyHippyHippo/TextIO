<?php

namespace HappyHippyHippo\TextIO;

use HappyHippyHippo\TextIO\Exception\FileWriteException;

interface OutputStreamInterface
{
    /**
     * @return Encode
     */
    public function encoding(): Encode;

    /**
     * @param string $data
     * @param Encode $source
     * @return $this
     * @throws FileWriteException
     */
    public function write(string $data, Encode $source = Encode::UTF8): OutputStreamInterface;
}
