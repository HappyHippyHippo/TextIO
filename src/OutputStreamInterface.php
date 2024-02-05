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
     * @param Encode $encode
     * @return $this
     * @throws FileWriteException
     */
    public function write(string $data, Encode $encode = Encode::UTF8): OutputStreamInterface;
}
