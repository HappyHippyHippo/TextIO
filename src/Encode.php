<?php

namespace Happyhippyhippo\TextIO;

enum Encode: string
{
    case UTF8 = 'UTF-8';
    case UTF16BE = 'UTF-16BE';
    case UTF16LE = 'UTF-16LE';
    case UTF32BE = 'UTF-32BE';
    case UTF32LE = 'UTF-32LE';

    /**
     * @return string
     */
    public function bom(): string
    {
        return match ($this) {
            self::UTF8 => pack('CCC', 0xEF, 0xBB, 0xBF),
            self::UTF16BE => pack('CC', 0xFE, 0xFF),
            self::UTF16LE => pack('CC', 0xFF, 0xFE),
            self::UTF32BE => pack('CCCC', 0x00, 0x00, 0xFE, 0xFF),
            self::UTF32LE => pack('CCCC', 0xFF, 0xFe, 0x00, 0x00),
        };
    }

    public function size(): int
    {
        return match ($this) {
            self::UTF8 => 1,
            self::UTF16BE, self::UTF16LE => 2,
            self::UTF32BE, self::UTF32LE => 4,
        };
    }

    /**
     * @param string $bytes
     * @param Encode $from
     * @return string
     */
    public function convert(string $bytes, Encode $from): string
    {
        return mb_convert_encoding($bytes, $this->value, $from->value);
    }
}
