<?php

declare( strict_types = 1 );
namespace WaughJ\FileLoader
{
	use WaughJ\Directory\Directory;
	use WaughJ\VerifiedArguments\VerifiedArguments;
	use Spatie\Url\Url;
	use function WaughJ\TestHashItem\TestHashItemString;

	class FileLoader
	{
		//
		//  PUBLIC
		//
		/////////////////////////////////////////////////////////

			public function __construct( array $arguments = [] )
			{
				if ( !isset( $arguments[ 'directory-url' ] ) || !$arguments[ 'directory-url' ] )
				{
					$arguments[ 'directory-url' ] = '';
				}
				else
				{
					$arguments[ 'directory-url' ] = ( string )( $arguments[ 'directory-url' ] );
					$this->directory_url = new Directory( ( string )( Url::fromString( $arguments[ 'directory-url' ] ) ) );
				}

				$this->directory_server = ( !isset( $arguments[ 'directory-server' ] ) || !$arguments[ 'directory-server' ] ) ? null : new Directory( $arguments[ 'directory-server' ] );
				$this->shared_directory = ( !isset( $arguments[ 'shared-directory' ] ) || !$arguments[ 'shared-directory' ] ) ? '' : new Directory( $arguments[ 'shared-directory' ] );
				$this->extension = TestHashItemString( $arguments, 'extension', '' );
			}

			public function getSource( string $local ) : string
			{
				$full = new Directory( [ $this->directory_url, $this->getLocalInShared( $local ) ] );
				return $full->getString([ 'ending-slash' => false, 'starting-slash' => false ]);
			}

			public function getSourceWithVersion( string $local ) : string
			{
				return $this->getSource( $local ) . $this->getVersionString( $local );
			}

			public function changeURLDirectory( $new_directory ) : FileLoader
			{
				return new FileLoader
				([
					'directory-url' => $new_directory,
					'directory-server' => $this->directory_server,
					'shared-directory' => $this->shared_directory,
					'extension' => $this->extension
				]);
			}

			public function changeServerDirectory( $new_directory ) : FileLoader
			{
				return new FileLoader
				([
					'directory-url' => $this->directory_url,
					'directory-server' => $new_directory,
					'shared-directory' => $this->shared_directory,
					'extension' => $this->extension
				]);
			}

			public function changeSharedDirectory( $new_directory ) : FileLoader
			{
				return new FileLoader
				([
					'directory-url' => $this->directory_url,
					'directory-server' => $this->directory_server,
					'shared-directory' => $new_directory,
					'extension' => $this->extension
				]);
			}

			public function changeExtension( $new_extension ) : FileLoader
			{
				return new FileLoader
				([
					'directory-url' => $this->directory_url,
					'directory-server' => $this->directory_server,
					'shared-directory' => $this->shared_directory,
					'extension' => $new_extension
				]);
			}

			public function getVersion( string $local ) : int
			{
				if ( $this->directory_server !== null )
				{
					$full = $this->getServerLocation( $local )->getString([ 'ending-slash' => false ]);
					$filetime = @filemtime( $full ); // O'erride PHP's idiotic way o' handling errors with a sane method.
					if ( $filetime === false )
					{
						// If filemtime can't find file, throw exception, but pass versionless
						// server & url filepaths. This allows those who want to deal with
						// to catch any errors, but also allows those who don't care
						// to still be able to safely access default paths.
						throw new MissingFileException( $full, $this->getSource( $local ) );
					}
					return $filetime;
				}
				return 0;
			}

			public function getDirectoryURL() : Directory
			{
				return $this->directory_url;
			}

			public function getServerDirectory() : Directory
			{
				return $this->directory_server;
			}

			public function getSharedDirectory() : Directory
			{
				return $this->shared_directory;
			}

			public function getExtension( string $local = null ) : string
			{
				return ( $this->extension !== '' )
					? $this->extension
					: ( ( $this->directory_server !== null && $local !== null )
						? pathinfo( $this->getServerLocation( $local )->getString() )[ 'extension' ]
						: '' );
			}



		//
		//  PRIVATE
		//
		/////////////////////////////////////////////////////////

			private function getVersionString( string $local ) : string
			{
				return ( $this->directory_server !== null ) ? '?m=' . $this->getVersion( $local ) : '';
			}

			private function getServerLocation( string $local ) : Directory
			{
				return $this->directory_server->addDirectory( $this->getLocalInShared( $local ) );
			}

			private function getLocalInShared( string $local ) : Directory
			{
				$local .= $this->getExtensionString();
				return ( $this->shared_directory === '' ) ? new Directory( $local ) : $this->shared_directory->addDirectory( new Directory( $local ) );
			}

			private function getExtensionString() : string
			{
				// Only print dot before extension if there actually is an extension.
				return ( $this->extension === '' ) ? '' : '.' . $this->extension;
			}

			private $directory_url;
			private $directory_server;
			private $shared_directory;
			private $extension;
	}
}
