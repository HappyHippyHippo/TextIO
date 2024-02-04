<?php

namespace Happyhippyhippo\TextIO;

use Generator;
use Happyhippyhippo\TextIO\Exception\Exception;
use Happyhippyhippo\TextIO\Exception\FileNotFoundException;
use Happyhippyhippo\TextIO\Exception\FileNotReadableException;
use Happyhippyhippo\TextIO\Exception\FileOpenException;

class InputStream
{
    /** @var resource */
    protected $handler;

    /** @var Encode */
    protected Encode $encoding = Encode::UTF8;

    /** @var string */
    protected string $buffer = '';

    /**
     * @param string $file
     * @throws Exception
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
     * @param int $quantity
     * @return string
     */
    public function peak(int $quantity = 1): string
    {
        $this->buffer($quantity);
        return substr($this->buffer, 0, $quantity);
    }

    /**
     * @param int $quantity
     * @return string
     */
    public function readBytes(int $quantity = 1): string
    {
        $this->buffer($quantity);
        $data = substr($this->buffer, 0, $quantity);
        $this->buffer = substr($this->buffer, $quantity);
        return $data;
    }

    /**
     * @param int $quantity
     */
    public function purge(int $quantity = 1): void
    {
        $this->buffer($quantity);
        $this->buffer = substr($this->buffer, $quantity);
    }

    /**
     * @param Encode $target
     * @return Generator
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
     * @return Encode
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
     * @return int
     */
    protected function buffer(int $quantity = 1): int
    {
        $size = strlen($this->buffer);
        if ($size < $quantity) {
            $this->buffer .= stream_get_contents($this->handler, $quantity - $size);
        }
        return strlen($this->buffer);
    }
}
