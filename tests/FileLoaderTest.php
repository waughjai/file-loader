<?php

use PHPUnit\Framework\TestCase;
use WaughJ\FileLoader\FileLoader;

class FileLoaderTest extends TestCase
{
	public function testSrcGenerator()
	{
		$loader = new FileLoader([ 'directory-url' => 'https://www.jaimeson-waugh.com/' ]);
		$this->assertEquals( $loader->getSource( 'logo.png' ), 'https://www.jaimeson-waugh.com/logo.png' );
	}

	public function testSrcWithVersion()
	{
		$loader = new FileLoader([ 'directory-url' => 'https://www.jaimeson-waugh.com/', 'directory-server' => getcwd() ]);
		$this->assertEquals( $loader->getSourceWithVersion( 'README.md' ), 'https://www.jaimeson-waugh.com/README.md?m=' . filemtime( getcwd() . '/' . 'README.md' ) );
	}

	public function testSharedDirectory()
	{
		$loader = new FileLoader([ 'directory-url' => 'https://example.jp.com/', 'directory-server' => getcwd(), 'shared-directory' => 'src' ]);
		$this->assertEquals( $loader->getSourceWithVersion( 'FileLoader.php' ), 'https://example.jp.com/src/FileLoader.php?m=' . filemtime( getcwd() . '/src/' . 'FileLoader.php' ) );
	}

	public function testExtension()
	{
		$loader = new FileLoader([ 'directory-url' => 'https://static.example.org/', 'directory-server' => getcwd(), 'shared-directory' => [ 'tests' ], 'extension' => 'php' ]);
		$this->assertEquals( $loader->getSourceWithVersion( 'FileLoaderTest' ), 'https://static.example.org/tests/FileLoaderTest.php?m=' . filemtime( getcwd() . '/tests/' . 'FileLoaderTest.php' ) );
	}
}
