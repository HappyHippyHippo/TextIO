<?php

namespace HappyHippyHippo\TextIO;

use HappyHippyHippo\TextIO\Exception\FileNotWritableException;
use HappyHippyHippo\TextIO\Exception\FileOpenException;
use HappyHippyHippo\TextIO\Exception\FileWriteException;

class OutputStream implements OutputStreamInterface
{
    /** @var resource */
    protected $handler;

    /**
     * @param string $file
     * @param Encode $encoding
     * @return OutputStreamInterface
     * @throws FileNotWritableException
     * @throws FileOpenException
     * @throws FileWriteException
     */
    public static function make(string $file, Encode $encoding = Encode::UTF8): OutputStreamInterface
    {
        return new self($file, $encoding);
    }

    /**
     * @param string $file
     * @param Encode $encoding
     * @throws FileNotWritableException
     * @throws FileOpenException
     * @throws FileWriteException
     */
    public function __construct(protected string $file, protected Encode $encoding = Encode::UTF8)
    {
        if (file_exists($file) && !is_writable($file)) {
            throw new FileNotWritableException($file);
        }
        $handler = fopen($file, 'wb');
        if ($handler === false) {
            throw new FileOpenException($file);
        }
        $this->handler = $handler;
        $this->writeBOM();
    }

    public function __destruct()
    {
        fclose($this->handler);
    }

    /**
     * @return Encode
     */
    public function encoding(): Encode
    {
        return $this->encoding;
    }

    /**
     * @param string $data
     * @param Encode $source
     * @return $this
     * @throws FileWriteException
     */
    public function write(string $data, Encode $source = Encode::UTF8): OutputStreamInterface
    {
        if ($this->encoding !== $source) {
            $data = $this->encoding->convert($data, $source);
        }
        return $this->writeData($data);
    }

    /**
     * @return $this
     * @throws FileWriteException
     */
    protected function writeBOM(): OutputStream
    {
        $this->writeData($this->encoding->bom());
        return $this;
    }

    /**
     * @return $this
     * @throws FileWriteException
     */
    protected function writeData(string $data): OutputStream
    {
        if (fwrite($this->handler, $data) === false) {
            throw new FileWriteException();
        }
        return $this;
    }
}
