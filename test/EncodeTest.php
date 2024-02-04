<?php

namespace Happyhippyhippo\TextIO\tests;

use Happyhippyhippo\TextIO\Encode;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Happyhippyhippo\TextIO\Encode
 */
class EncodeTest extends TestCase
{
    /**
     * @param Encode $Encode
     * @param string $expected
     * @return void
     *
     * @covers ::bom
     * @dataProvider provideDataToByteOrderMarkTest
     */
    public function testByteOrderMark(Encode $Encode, string $expected): void
    {
        $this->assertEquals($expected, $Encode->bom());
    }

    /**
     * @return array<string,mixed>
     */
    public static function provideDataToByteOrderMarkTest(): array
    {
        return [
            'utf8' => [
                'Encode' => Encode::UTF8,
                'expected' => pack('CCC', 0xEF, 0xBB, 0xBF),
            ],
            'utf16be' => [
                'Encode' => Encode::UTF16BE,
                'expected' => pack('CC', 0xFE, 0xFF),
            ],
            'utf16le' => [
                'Encode' => Encode::UTF16LE,
                'expected' => pack('CC', 0xFF, 0xFE),
            ],
            'utf32be' => [
                'Encode' => Encode::UTF32BE,
                'expected' => pack('CCCC', 0x00, 0x00, 0xFE, 0xFF),
            ],
            'utf32le' => [
                'Encode' => Encode::UTF32LE,
                'expected' => pack('CCCC', 0xFF, 0xFe, 0x00, 0x00),
            ],
        ];
    }

    /**
     * @param Encode $Encode
     * @param int $expected
     * @return void
     *
     * @covers ::size
     * @dataProvider provideDataToSizeTest
     */
    public function testSize(Encode $Encode, int $expected): void
    {
        $this->assertEquals($expected, $Encode->size());
    }

    /**
     * @return array<string,mixed>
     */
    public static function provideDataToSizeTest(): array
    {
        return [
            'utf-8' => ['Encode' => Encode::UTF8, 'expected' => 1],
            'utf-16le' => ['Encode' => Encode::UTF16LE, 'expected' => 2],
            'utf-16be' => ['Encode' => Encode::UTF16BE, 'expected' => 2],
            'utf-32le' => ['Encode' => Encode::UTF32LE, 'expected' => 4],
            'utf-32be' => ['Encode' => Encode::UTF32BE, 'expected' => 4],
        ];
    }

    /**
     * @param string $input
     * @param Encode $source
     * @param Encode $target
     * @param string $expected
     * @return void
     *
     * @covers ::convert
     * @dataProvider provideDataToConvertTest
     */
    public function testConvert(string $input, Encode $source, Encode $target, string $expected): void
    {
        $this->assertEquals($expected, $target->convert($input, $source));
    }

    /**
     * @return array<string,mixed>
     */
    public static function provideDataToConvertTest(): array
    {
        return [
            'empty utf-8 to utf-32-be' => [
                'input' => '',
                'source Encode' => Encode::UTF8,
                'target Encode' => Encode::UTF32BE,
                'expected' => '',
            ],
            'utf-8 to utf-32-be' => [
                'input' => "abc\ndef",
                'source Encode' => Encode::UTF8,
                'target Encode' => Encode::UTF32BE,
                'expected' => mb_convert_encoding("abc\ndef", Encode::UTF32BE->value),
            ],
            'empty utf-32-le to utf-8' => [
                'input' => '',
                'source Encode' => Encode::UTF32LE,
                'target Encode' => Encode::UTF8,
                'expected' => '',
            ],
            'utf-32-le to utf-8' => [
                'input' => mb_convert_encoding("abc\ndef", Encode::UTF32LE->value),
                'source Encode' => Encode::UTF32LE,
                'target Encode' => Encode::UTF8,
                'expected' => "abc\ndef",
            ],
        ];
    }
}
