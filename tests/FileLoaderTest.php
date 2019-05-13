<?php

use PHPUnit\Framework\TestCase;
use WaughJ\Directory\Directory;
use WaughJ\FileLoader\FileLoader;
use WaughJ\FileLoader\MissingFileException;

class FileLoaderTest extends TestCase
{
	public function testBlank()
	{
		$loader = new FileLoader();
		$this->assertEquals( $loader->getSource( 'logo.png' ), 'logo.png' );
	}

	public function testGetExtension()
	{
		$loader = new FileLoader([ 'directory-server' => getcwd() ]);
		$this->assertEquals( $loader->getExtension( 'README.md' ), 'md' );
	}

	public function testSrc()
	{
		$loader = new FileLoader([ 'directory-url' => 'https://www.jaimeson-waugh.com/' ]);
		$this->assertEquals( $loader->getSource( 'logo.png' ), 'https://www.jaimeson-waugh.com/logo.png' );
		$loader2 = new FileLoader([ 'directory-url' => '' ]);
		$this->assertEquals( $loader2->getSource( 'logo.png' ), 'logo.png' );
		$loader3 = new FileLoader([ 'directory-url' => false ]);
		$this->assertEquals( $loader3->getSource( 'logo.png' ), 'logo.png' );
		$loader4 = new FileLoader([ 'directory-url' => null ]);
		$this->assertEquals( $loader4->getSource( 'logo.png' ), 'logo.png' );
	}

	public function testNonexistentFile()
	{
		$loader = new FileLoader([ 'directory-url' => 'https://www.jaimeson-waugh.com/', 'directory-server' => getcwd() ]);
		$file = null;
		try
		{
			// 'Cause o' exception, this won't run.
			$file = $loader->getSourceWithVersion( 'bleb' );
		}
		catch ( MissingFileException $e )
		{
			$file = $e->getFallbackContent();
		}
		$this->assertEquals( 'https://www.jaimeson-waugh.com/bleb', $file );
	}

	public function testSrcWithVersion()
	{
		$loader = new FileLoader([ 'directory-url' => 'https://www.jaimeson-waugh.com/', 'directory-server' => getcwd() ]);
		$this->assertEquals( $loader->getSourceWithVersion( 'README.md' ), 'https://www.jaimeson-waugh.com/README.md?m=' . filemtime( getcwd() . '/' . 'README.md' ) );
		$this->assertEquals( $loader->getVersion( 'README.md' ), filemtime( getcwd() . '/' . 'README.md' ) );
		$loader2 = new FileLoader([ 'directory-url' => 'https://www.jaimeson-waugh.com/', 'directory-server' => '' ]);
		$this->assertEquals( $loader2->getSourceWithVersion( 'README.md' ), 'https://www.jaimeson-waugh.com/README.md' );
		$loader3 = new FileLoader([ 'directory-url' => 'https://www.jaimeson-waugh.com/', 'directory-server' => false ]);
		$this->assertEquals( $loader3->getSourceWithVersion( 'README.md' ), 'https://www.jaimeson-waugh.com/README.md' );
		$loader4 = new FileLoader([ 'directory-url' => 'https://www.jaimeson-waugh.com/', 'directory-server' => null ]);
		$this->assertEquals( $loader4->getSourceWithVersion( 'README.md' ), 'https://www.jaimeson-waugh.com/README.md' );
	}

	public function testSharedDirectory()
	{
		$loader = new FileLoader([ 'directory-url' => 'https://example.jp.com/', 'directory-server' => getcwd(), 'shared-directory' => 'src' ]);
		$this->assertEquals( $loader->getSourceWithVersion( 'FileLoader.php' ), 'https://example.jp.com/src/FileLoader.php?m=' . filemtime( getcwd() . '/src/' . 'FileLoader.php' ) );
		$loader2 = new FileLoader([ 'directory-url' => 'https://example.jp.com/', 'directory-server' => getcwd(), 'shared-directory' => '' ]);
		$this->assertEquals( $loader2->getSourceWithVersion( 'src/FileLoader.php' ), 'https://example.jp.com/src/FileLoader.php?m=' . filemtime( getcwd() . '/src/' . 'FileLoader.php' ) );
		$loader3 = new FileLoader([ 'directory-url' => 'https://example.jp.com/', 'directory-server' => getcwd(), 'shared-directory' => null ]);
		$this->assertEquals( $loader3->getSourceWithVersion( 'src/FileLoader.php' ), 'https://example.jp.com/src/FileLoader.php?m=' . filemtime( getcwd() . '/src/' . 'FileLoader.php' ) );
		$loader4 = new FileLoader([ 'directory-url' => 'https://example.jp.com/', 'directory-server' => getcwd(), 'shared-directory' => false ]);
		$this->assertEquals( $loader4->getSourceWithVersion( 'src/FileLoader.php' ), 'https://example.jp.com/src/FileLoader.php?m=' . filemtime( getcwd() . '/src/' . 'FileLoader.php' ) );
	}

	public function testExtension()
	{
		$loader = new FileLoader([ 'directory-url' => 'https://static.example.org/', 'directory-server' => getcwd(), 'shared-directory' => [ 'tests' ], 'extension' => 'php' ]);
		$this->assertEquals( $loader->getSourceWithVersion( 'FileLoaderTest' ), 'https://static.example.org/tests/FileLoaderTest.php?m=' . filemtime( getcwd() . '/tests/' . 'FileLoaderTest.php' ) );
		$loader2 = new FileLoader([ 'directory-url' => 'https://static.example.org/', 'directory-server' => getcwd(), 'shared-directory' => [ 'tests' ], 'extension' => null ]);
		$this->assertEquals( $loader2->getSourceWithVersion( 'FileLoaderTest.php' ), 'https://static.example.org/tests/FileLoaderTest.php?m=' . filemtime( getcwd() . '/tests/' . 'FileLoaderTest.php' ) );
		$loader3 = new FileLoader([ 'directory-url' => 'https://static.example.org/', 'directory-server' => getcwd(), 'shared-directory' => [ 'tests' ], 'extension' => false ]);
		$this->assertEquals( $loader3->getSourceWithVersion( 'FileLoaderTest.php' ), 'https://static.example.org/tests/FileLoaderTest.php?m=' . filemtime( getcwd() . '/tests/' . 'FileLoaderTest.php' ) );
	}

	public function testURLDirectoryChange()
	{
		$loader = new FileLoader([ 'directory-url' => 'https://example.jp.com/', 'directory-server' => getcwd(), 'shared-directory' => 'src' ]);
		$this->assertEquals( $loader->getSourceWithVersion( 'FileLoader.php' ), 'https://example.jp.com/src/FileLoader.php?m=' . filemtime( getcwd() . '/src/' . 'FileLoader.php' ) );
		$loader2 = $loader->changeURLDirectory( 'https://static.example.com' );
		$this->assertEquals( $loader2->getSourceWithVersion( 'FileLoader.php' ), 'https://static.example.com/src/FileLoader.php?m=' . filemtime( getcwd() . '/src/' . 'FileLoader.php' ) );
	}

	public function testServerDirectoryChange()
	{
		$loader = new FileLoader([ 'directory-url' => 'https://example.jp.com/', 'directory-server' => getcwd() ]);
		$this->assertEquals( $loader->getSourceWithVersion( 'README.md' ), 'https://example.jp.com/README.md?m=' . filemtime( getcwd() . '/' . 'README.md' ) );
		$loader2 = $loader->changeServerDirectory( new Directory([ getcwd(), 'src' ]) );
		$this->assertEquals( $loader2->getSourceWithVersion( 'FileLoader.php' ), 'https://example.jp.com/FileLoader.php?m=' . filemtime( getcwd() . '/src/' . 'FileLoader.php' ) );
	}

	public function testSharedDirectoryChange()
	{
		$loader = new FileLoader([ 'directory-url' => 'https://example.jp.com/', 'directory-server' => getcwd(), 'shared-directory' => 'src' ]);
		$this->assertEquals( $loader->getSourceWithVersion( 'FileLoader.php' ), 'https://example.jp.com/src/FileLoader.php?m=' . filemtime( getcwd() . '/src/' . 'FileLoader.php' ) );
		$loader2 = $loader->changeSharedDirectory( 'tests' );
		$this->assertEquals( $loader2->getSourceWithVersion( 'FileLoaderTest.php' ), 'https://example.jp.com/tests/FileLoaderTest.php?m=' . filemtime( getcwd() . '/tests/' . 'FileLoaderTest.php' ) );
	}

	public function testExtensionChange()
	{
		$loader = new FileLoader([ 'directory-url' => 'https://example.jp.com/', 'directory-server' => getcwd(), 'shared-directory' => '', 'extension' => 'json' ]);
		$this->assertEquals( $loader->getSourceWithVersion( 'composer' ), 'https://example.jp.com/composer.json?m=' . filemtime( getcwd() . '/' . 'composer.json' ) );
		$loader2 = $loader->changeExtension( 'xml' );
		$this->assertEquals( $loader2->getSourceWithVersion( 'phpunit' ), 'https://example.jp.com/phpunit.xml?m=' . filemtime( getcwd() . '/' . 'phpunit.xml' ) );
	}

	public function testMultipleChanges()
	{
		$loader = new FileLoader([ 'directory-url' => 'https://example.jp.com/', 'directory-server' => getcwd(), 'extension' => 'md' ]);
		$this->assertEquals( $loader->getSourceWithVersion( 'README' ), 'https://example.jp.com/README.md?m=' . filemtime( getcwd() . '/' . 'README.md' ) );
		$loader2 = $loader->changeURLDirectory( 'https://www.somethingelse.com' )->changeExtension( 'php' )->changeSharedDirectory( 'src' );
		$this->assertEquals( $loader2->getSourceWithVersion( 'FileLoader' ), 'https://www.somethingelse.com/src/FileLoader.php?m=' . filemtime( getcwd() . '/src/' . 'FileLoader.php' ) );
	}

	public function testGetters()
	{
		$loader = new FileLoader([ 'directory-url' => 'https://example.jp.com/', 'directory-server' => getcwd(), 'shared-directory' => 'src' ]);
		$this->assertEquals( new Directory( 'src' ), $loader->getSharedDirectory() );
		$this->assertEquals( new Directory( getcwd() ), $loader->getServerDirectory() );
		$this->assertEquals( new Directory( 'https://example.jp.com/' ), $loader->getDirectoryURL() );
	}
}
