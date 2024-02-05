<?php

namespace HappyHippyHippo\TextIO\tests;

use HappyHippyHippo\TextIO\Encode;
use HappyHippyHippo\TextIO\Exception\Exception;
use HappyHippyHippo\TextIO\Exception\FileNotWritableException;
use HappyHippyHippo\TextIO\OutputStream;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamFile;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use ReflectionMethod;

/**
 * @coversDefaultClass \HappyHippyHippo\TextIO\OutputStream
 */
class OutputStreamTest extends TestCase
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
    public function testOpeningNonWritableFile(): void
    {
        $path = 'data.txt';
        $this->root->addChild(new vfsStreamFile($path, 0));

        $this->expectException(FileNotWritableException::class);

        new OutputStream($this->root->url() . '/' . $path);
    }

    /**
     * @return void
     * @throws Exception
     *
     * @covers ::__construct
     * @covers ::__destruct
     * @covers ::encoding
     * @covers ::writeBOM
     * @covers ::writeData
     */
    public function testOpeningNonExistingFile(): void
    {
        $path = 'data.txt';

        $stream = new OutputStream($this->root->url() . '/' . $path);
        $this->assertNotNull($stream);
        $this->assertEquals(Encode::UTF8, $stream->encoding());
    }

    /**
     * @return void
     * @throws Exception
     *
     * @covers ::__construct
     * @covers ::__destruct
     * @covers ::encoding
     * @covers ::writeBOM
     * @covers ::writeData
     */
    public function testOpeningExistingFile(): void
    {
        $path = 'data.txt';
        $file = new vfsStreamFile($path, 0777);
        $this->root->addChild($file);

        $stream = new OutputStream($this->root->url() . '/' . $path, Encode::UTF16BE);
        $this->assertNotNull($stream);
        $this->assertEquals(Encode::UTF16BE, $stream->encoding());
        $this->assertEquals(Encode::UTF16BE->bom(), $file->getContent());
    }

    /**
     * @return void
     * @throws Exception
     *
     * @covers ::__construct
     * @covers ::__destruct
     * @covers ::encoding
     * @covers ::writeBOM
     * @covers ::writeData
     * @covers ::make
     */
    public function testMake(): void
    {
        $path = 'data.txt';
        $file = new vfsStreamFile($path, 0777);
        $this->root->addChild($file);

        $stream = OutputStream::make($this->root->url() . '/' . $path, Encode::UTF16BE);
        $this->assertNotNull($stream);
        $this->assertEquals(Encode::UTF16BE, $stream->encoding());
        $this->assertEquals(Encode::UTF16BE->bom(), $file->getContent());
    }

    /**
     * @return void
     * @throws Exception
     *
     * @covers ::__construct
     * @covers ::__destruct
     * @covers ::write
     * @covers ::writeBOM
     * @covers ::writeData
     */
    public function testWrite(): void
    {
        $path = 'data.txt';
        $content = "text data string";
        $file = new vfsStreamFile($path, 0777);
        $this->root->addChild($file);

        $stream = new OutputStream($this->root->url() . '/' . $path);
        $stream->write($content);

        $this->assertNotNull($stream);
        $this->assertEquals(Encode::UTF8, $stream->encoding());
        $this->assertEquals(Encode::UTF8->bom() . $content, $file->getContent());
    }

    /**
     * @return void
     * @throws Exception
     *
     * @covers ::__construct
     * @covers ::__destruct
     * @covers ::write
     * @covers ::writeBOM
     * @covers ::writeData
     */
    public function testWriteOnDifferentEncode(): void
    {
        $path = 'data.txt';
        $content = "text data string";
        $file = new vfsStreamFile($path, 0777);
        $this->root->addChild($file);

        $stream = new OutputStream($this->root->url() . '/' . $path, Encode::UTF16BE);
        $stream->write($content);

        $this->assertNotNull($stream);
        $this->assertEquals(Encode::UTF16BE, $stream->encoding());
        $this->assertEquals(
            Encode::UTF16BE->bom() . Encode::UTF16BE->convert(
                $content,
                Encode::UTF8
            ),
            $file->getContent(),
        );
    }

    /**
     * @param OutputStream $stream
     * @param string $method
     * @param mixed ...$args
     * @return mixed
     * @throws ReflectionException
     */
    protected function call(OutputStream $stream, string $method, mixed ...$args): mixed
    {
        $method = new ReflectionMethod($stream, $method);
        return $method->invoke($stream, ...$args);
    }
}
