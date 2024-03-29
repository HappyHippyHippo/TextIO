<?php

namespace HappyHippyHippo\TextIO\tests;

use HappyHippyHippo\TextIO\Encode;
use HappyHippyHippo\TextIO\Exception\Exception;
use HappyHippyHippo\TextIO\Exception\FileNotFoundException;
use HappyHippyHippo\TextIO\Exception\FileNotReadableException;
use HappyHippyHippo\TextIO\InputStream;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamFile;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use ReflectionMethod;

/**
 * @coversDefaultClass \HappyHippyHippo\TextIO\InputStream
 */
class InputStreamTest extends TestCase
{
    /** @var vfsStreamDirectory */
    private vfsStreamDirectory $root;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->root = vfsStream::setup();
    }

    /**
     * @return void
     * @throws Exception
     *
     * @covers ::__construct
     */
    public function testOpeningNonExistingFile(): void
    {
        $path = 'data.txt';

        $this->expectException(FileNotFoundException::class);

        new InputStream($this->root->url() . '/' . $path);
    }

    /**
     * @return void
     * @throws Exception
     *
     * @covers ::__construct
     */
    public function testOpeningNonReadableFile(): void
    {
        $path = 'data.txt';
        $this->root->addChild(new vfsStreamFile($path, 0));

        $this->expectException(FileNotReadableException::class);

        new InputStream($this->root->url() . '/' . $path);
    }

    /**
     * @return void
     * @throws Exception
     *
     * @covers ::__construct
     * @covers ::__destruct
     */
    public function testOpeningFile(): void
    {
        $path = 'data.txt';
        $this->root->addChild(new vfsStreamFile($path, 0777));

        $stream = new InputStream($this->root->url() . '/' . $path);
        $this->assertNotNull($stream);
    }

    /**
     * @return void
     * @throws Exception
     *
     * @covers ::__construct
     * @covers ::__destruct
     * @covers ::make
     */
    public function testMake(): void
    {
        $path = 'data.txt';
        $this->root->addChild(new vfsStreamFile($path, 0777));

        $stream = InputStream::make($this->root->url() . '/' . $path);
        $this->assertNotNull($stream);
    }

    /**
     * @param Encode $encoding
     * @return void
     *
     * @throws Exception
     * @covers ::encoding
     * @covers ::lines
     * @covers ::buffer
     * @dataProvider provideDataToReadBOMTest
     */
    public function testLines(Encode $encoding): void
    {
        $path = 'data.txt';
        $content = ['content 1', 'content 2', 'content 3'];
        $utfContent = mb_convert_encoding(implode("\n", $content), $encoding->value);
        $file = new vfsStreamFile($path, 0777);
        $file->setContent($encoding->bom() . $utfContent);
        $this->root->addChild($file);

        $stream = new InputStream($this->root->url() . '/' . $path);
        $this->assertEquals($encoding, $stream->encoding());
        $lines = [];
        foreach ($stream->lines() as $line) {
            $lines[] = $line;
        }
        $this->assertEquals($content, $lines);
    }

    /**
     * @return array<string,mixed>
     */
    public static function provideDataToReadBOMTest(): array
    {
        return [
            'utf-8' => ['encoding' => Encode::UTF8],
            'utf-16-be' => ['encoding' => Encode::UTF16BE],
            'utf-16-le' => ['encoding' => Encode::UTF16LE],
            'utf-32-be' => ['encoding' => Encode::UTF32BE],
            'utf-32-le' => ['encoding' => Encode::UTF32LE],
        ];
    }

    /**
     * @return void
     * @throws Exception
     * @throws ReflectionException
     *
     * @covers ::peak
     * @covers ::buffer
     */
    public function testPeakingAnEmptyStream(): void
    {
        $path = 'data.txt';
        $content = '';
        $file = new vfsStreamFile($path, 0777);
        $file->setContent($content);
        $this->root->addChild($file);

        $stream = new InputStream($this->root->url() . '/' . $path);
        $this->assertEmpty($this->call($stream, 'peak'));
    }

    /**
     * @return void
     * @throws Exception
     * @throws ReflectionException
     *
     * @covers ::peak
     * @covers ::buffer
     */
    public function testDefaultPeakQuantity(): void
    {
        $path = 'data.txt';
        $content = 'abcdef';
        $file = new vfsStreamFile($path, 0777);
        $file->setContent($content);
        $this->root->addChild($file);

        $stream = new InputStream($this->root->url() . '/' . $path);
        $this->assertEquals($content[0], $this->call($stream, 'peak'));
        $this->assertEquals($content[0], $this->call($stream, 'readBytes'));
    }

    /**
     * @return void
     * @throws Exception
     * @throws ReflectionException
     *
     * @covers ::peak
     * @covers ::buffer
     */
    public function testMultiBytePeak(): void
    {
        $path = 'data.txt';
        $content = 'abcdef';
        $file = new vfsStreamFile($path, 0777);
        $file->setContent($content);
        $this->root->addChild($file);

        $stream = new InputStream($this->root->url() . '/' . $path);
        $this->assertEquals(substr($content, 0, 3), $this->call($stream, 'peak', 3));
        $this->assertEquals($content[0], $this->call($stream, 'readBytes'));
        $this->assertEquals($content[1], $this->call($stream, 'readBytes'));
        $this->assertEquals($content[2], $this->call($stream, 'readBytes'));
    }

    /**
     * @return void
     * @throws Exception
     * @throws ReflectionException
     *
     * @covers ::peak
     * @covers ::buffer
     */
    public function testMultiplePeakCalls(): void
    {
        $path = 'data.txt';
        $content = 'abcdef';
        $file = new vfsStreamFile($path, 0777);
        $file->setContent($content);
        $this->root->addChild($file);

        $stream = new InputStream($this->root->url() . '/' . $path);
        $this->assertEquals(substr($content, 0, 2), $this->call($stream, 'peak', 2));
        $this->assertEquals($content[0], $this->call($stream, 'peak'));
        $this->assertEquals(substr($content, 0, 3), $this->call($stream, 'peak', 3));
        $this->assertEquals($content[0], $this->call($stream, 'readBytes'));
        $this->assertEquals($content[1], $this->call($stream, 'readBytes'));
        $this->assertEquals($content[2], $this->call($stream, 'readBytes'));
    }

    /**
     * @return void
     * @throws Exception
     * @throws ReflectionException
     *
     * @covers ::peak
     * @covers ::buffer
     */
    public function testPeakingRemainingStreamContent(): void
    {
        $path = 'data.txt';
        $content = 'abc';
        $file = new vfsStreamFile($path, 0777);
        $file->setContent($content);
        $this->root->addChild($file);

        $stream = new InputStream($this->root->url() . '/' . $path);
        $this->assertEquals($content, $this->call($stream, 'peak', 100));
        $this->assertEquals($content, $this->call($stream, 'readBytes', 100));
    }

    /**
     * @return void
     * @throws Exception
     * @throws ReflectionException
     *
     * @covers ::readBytes
     * @covers ::buffer
     */
    public function testReadingAnEmptyStream(): void
    {
        $path = 'data.txt';
        $this->root->addChild(new vfsStreamFile($path, 0777));

        $stream = new InputStream($this->root->url() . '/' . $path);
        $this->assertEmpty($this->call($stream, 'readBytes'));
    }

    /**
     * @return void
     * @throws Exception
     * @throws ReflectionException
     *
     * @covers ::readBytes
     * @covers ::buffer
     */
    public function testReadingRemainingStreamContent(): void
    {
        $path = 'data.txt';
        $content = 'abc';
        $file = new vfsStreamFile($path, 0777);
        $file->setContent($content);
        $this->root->addChild($file);

        $stream = new InputStream($this->root->url() . '/' . $path);
        $this->assertEquals($content, $this->call($stream, 'readBytes', 100));
    }

    /**
     * @return void
     * @throws Exception
     * @throws ReflectionException
     *
     * @covers ::readBytes
     * @covers ::buffer
     */
    public function testReadingPartialStreamContent(): void
    {
        $path = 'data.txt';
        $content = 'abc';
        $file = new vfsStreamFile($path, 0777);
        $file->setContent($content);
        $this->root->addChild($file);

        $stream = new InputStream($this->root->url() . '/' . $path);
        $this->assertEquals(substr($content, 0, 2), $this->call($stream, 'readBytes', 2));
    }

    /**
     * @return void
     * @throws Exception
     * @throws ReflectionException
     *
     * @covers ::readBytes
     * @covers ::buffer
     */
    public function testReadingDefaultQuantityOfTheStreamContent(): void
    {
        $path = 'data.txt';
        $content = 'abc';
        $file = new vfsStreamFile($path, 0777);
        $file->setContent($content);
        $this->root->addChild($file);

        $stream = new InputStream($this->root->url() . '/' . $path);
        $this->assertEquals($content[0], $this->call($stream, 'readBytes'));
    }

    /**
     * @return void
     * @throws Exception
     * @throws ReflectionException
     *
     * @covers ::readBytes
     * @covers ::buffer
     */
    public function testContinuousReadOfStreamContent(): void
    {
        $path = 'data.txt';
        $content = 'abc';
        $file = new vfsStreamFile($path, 0777);
        $file->setContent($content);
        $this->root->addChild($file);

        $stream = new InputStream($this->root->url() . '/' . $path);
        for ($i = 0, $length = strlen($content); $i < $length; $i++) {
            $this->assertEquals($content[$i], $this->call($stream, 'readBytes'));
        }
    }

    /**
     * @return void
     * @throws Exception
     * @throws ReflectionException
     *
     * @covers ::readBytes
     * @covers ::buffer
     */
    public function testMultipleBytesContinuousRead(): void
    {
        $path = 'data.txt';
        $content = 'abcdef';
        $file = new vfsStreamFile($path, 0777);
        $file->setContent($content);
        $this->root->addChild($file);

        $stream = new InputStream($this->root->url() . '/' . $path);
        $this->assertEquals(substr($content, 0, 3), $this->call($stream, 'readBytes', 3));
        $this->assertEquals(substr($content, 3, 3), $this->call($stream, 'readBytes', 3));
    }

    /**
     * @return void
     * @throws Exception
     * @throws ReflectionException
     *
     * @covers ::purge
     * @covers ::buffer
     */
    public function testPurgingAnEmptyStream(): void
    {
        $path = 'data.txt';
        $this->root->addChild(new vfsStreamFile($path, 0777));

        $stream = new InputStream($this->root->url() . '/' . $path);
        $this->call($stream, 'purge');
        $this->assertNotNull($stream);
    }

    /**
     * @return void
     * @throws Exception
     * @throws ReflectionException
     *
     * @covers ::purge
     * @covers ::buffer
     */
    public function testPurgingDefaultQuantity(): void
    {
        $path = 'data.txt';
        $content = 'abcdef';
        $file = new vfsStreamFile($path, 0777);
        $file->setContent($content);
        $this->root->addChild($file);

        $stream = new InputStream($this->root->url() . '/' . $path);
        $this->call($stream, 'purge');
        $this->assertEquals($content[1], $this->call($stream, 'readBytes'));
    }

    /**
     * @return void
     * @throws Exception
     * @throws ReflectionException
     *
     * @covers ::purge
     * @covers ::buffer
     */
    public function testPurgingMultiByteQuantity(): void
    {
        $path = 'data.txt';
        $content = 'abcdef';
        $file = new vfsStreamFile($path, 0777);
        $file->setContent($content);
        $this->root->addChild($file);

        $stream = new InputStream($this->root->url() . '/' . $path);
        $this->call($stream, 'purge', 3);
        $this->assertEquals($content[3], $this->call($stream, 'readBytes'));
    }

    /**
     * @return void
     * @throws Exception
     * @throws ReflectionException
     *
     * @covers ::purge
     * @covers ::buffer
     */
    public function testPurgingRemainingContent(): void
    {
        $path = 'data.txt';
        $content = 'abcdef';
        $file = new vfsStreamFile($path, 0777);
        $file->setContent($content);
        $this->root->addChild($file);

        $stream = new InputStream($this->root->url() . '/' . $path);
        $this->call($stream, 'purge', 100);
        $this->assertEmpty($this->call($stream, 'readBytes'));
    }

    /**
     * @return void
     * @throws Exception
     * @throws ReflectionException
     *
     * @covers ::encoding
     * @covers ::readBOM
     * @covers ::buffer
     */
    public function testReadingFileWithoutBOM(): void
    {
        $path = 'data.txt';
        $content = 'content';
        $file = new vfsStreamFile($path, 0777);
        $file->setContent($content);
        $this->root->addChild($file);

        $stream = new InputStream($this->root->url() . '/' . $path);
        $this->assertEquals(Encode::UTF8, $stream->encoding());
        $this->assertEquals($content, $this->call($stream, 'readBytes', 100));
    }

    /**
     * @param Encode $encoding
     * @return void
     *
     * @throws Exception
     * @throws ReflectionException
     * @covers ::encoding
     * @covers ::readBOM
     * @covers ::buffer
     * @dataProvider provideDataToReadBOMTest
     */
    public function testReadBOM(Encode $encoding): void
    {
        $path = 'data.txt';
        $content = 'content';
        $file = new vfsStreamFile($path, 0777);
        $file->setContent($encoding->bom() . $content);
        $this->root->addChild($file);

        $stream = new InputStream($this->root->url() . '/' . $path);
        $this->assertEquals($encoding, $stream->encoding());
        $this->assertEquals($content, $this->call($stream, 'readBytes', 100));
    }

    /**
     * @param InputStream $stream
     * @param string $method
     * @param mixed ...$args
     * @return mixed
     * @throws ReflectionException
     */
    protected function call(InputStream $stream, string $method, mixed ...$args): mixed
    {
        $method = new ReflectionMethod($stream, $method);
        return $method->invoke($stream, ...$args);
    }
}
