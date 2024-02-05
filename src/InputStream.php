<?php

namespace HappyHippyHippo\TextIO;

use Generator;
use HappyHippyHippo\TextIO\Exception\FileNotFoundException;
use HappyHippyHippo\TextIO\Exception\FileNotReadableException;
use HappyHippyHippo\TextIO\Exception\FileOpenException;
use HappyHippyHippo\TextIO\Exception\FileReadException;

class InputStream implements InputStreamInterface
{
    /** @var resource */
    protected $handler;

    /** @var Encode */
    protected Encode $encoding = Encode::UTF8;

    /** @var string */
    protected string $buffer = '';

    /**
     * @param string $file
     * @return InputStream
     * @throws FileNotFoundException
     * @throws FileNotReadableException
     * @throws FileOpenException
     * @throws FileReadException
     */
    public static function make(string $file): InputStreamInterface
    {
        return new self($file);
    }

    /**
     * @param string $file
     * @throws FileNotFoundException
     * @throws FileNotReadableException
     * @throws FileOpenException
     * @throws FileReadException
     */
    public function __construct(protected string $file)
    {
        if (!file_exists($file)) {
            throw new FileNotFoundException($file);
        }
        if (!is_readable($file)) {
            throw new FileNotReadableException($file);
        }
        $handler = fopen($file, 'rb');
        if ($handler === false) {
            throw new FileOpenException($file);
        }
        $this->handler = $handler;
        $this->readBOM();
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
     * @param Encode $target
     * @return Generator
     * @throws FileReadException
     */
    public function lines(Encode $target = Encode::UTF8): Generator
    {
        $lineFeed = $target->convert("\n", Encode::UTF8);
        $line = '';
        while (!feof($this->handler) || $this->buffer !== '') {
            $bytes = $this->readBytes($this->encoding->size());
            if ($bytes !== '') {
                if ($this->encoding !== $target) {
                    $bytes = $target->convert($bytes, $this->encoding);
                }
                if ($bytes === $lineFeed) {
                    yield $line;
                    $line = '';
                    continue;
                }
                $line .= $bytes;
            }
        }
        if ($line !== '') {
            yield $line;
        }
    }

    /**
     * @param int $quantity
     * @return string
     * @throws FileReadException
     */
    protected function peak(int $quantity = 1): string
    {
        $this->buffer($quantity);
        return substr($this->buffer, 0, $quantity);
    }

    /**
     * @param int $quantity
     * @return string
     * @throws FileReadException
     */
    protected function readBytes(int $quantity = 1): string
    {
        $this->buffer($quantity);
        $data = substr($this->buffer, 0, $quantity);
        $this->buffer = substr($this->buffer, $quantity);
        return $data;
    }

    /**
     * @return Encode
     * @throws FileReadException
     */
    protected function readBOM(): Encode
    {
        $bom = $this->peak(4);
        if (str_starts_with($bom, Encode::UTF32BE->bom())) {
            $this->purge(4);
            return $this->encoding = Encode::UTF32BE;
        }
        if (str_starts_with($bom, Encode::UTF32LE->bom())) {
            $this->purge(4);
            return $this->encoding = Encode::UTF32LE;
        }
        if (str_starts_with($bom, Encode::UTF8->bom())) {
            $this->purge(3);
            return $this->encoding = Encode::UTF8;
        }
        if (str_starts_with($bom, Encode::UTF16BE->bom())) {
            $this->purge(2);
            return $this->encoding = Encode::UTF16BE;
        }
        if (str_starts_with($bom, Encode::UTF16LE->bom())) {
            $this->purge(2);
            return $this->encoding = Encode::UTF16LE;
        }
        return $this->encoding = Encode::UTF8;
    }

    /**
     * @param int $quantity
     * @throws FileReadException
     */
    protected function purge(int $quantity = 1): void
    {
        $this->buffer($quantity);
        $this->buffer = substr($this->buffer, $quantity);
    }

    /**
     * @param int $quantity
     * @return int
     * @throws FileReadException
     */
    protected function buffer(int $quantity = 1): int
    {
        $size = strlen($this->buffer);
        if ($size < $quantity) {
            $result = fread($this->handler, max(0, $quantity - $size));
            if ($result === false) {
                throw new FileReadException();
            }
            $this->buffer .= $result;
        }
        return strlen($this->buffer);
    }
}
