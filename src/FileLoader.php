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
					$this->directory_url = Url::fromString( $arguments[ 'directory-url' ] );
				}

				$this->directory_server = ( !isset( $arguments[ 'directory-server' ] ) || !$arguments[ 'directory-server' ] ) ? null : new Directory( $arguments[ 'directory-server' ] );
				$this->shared_directory = ( !isset( $arguments[ 'shared-directory' ] ) || !$arguments[ 'shared-directory' ] ) ? '' : new Directory( $arguments[ 'shared-directory' ] );
				$this->extension = TestHashItemString( $arguments, 'extension', '' );
			}

			public function getSource( string $local ) : string
			{
				$local = new Directory( $this->getLocalInShared( $local ) );
				$directory = ( string )( $this->directory_url );
				return $directory . $local->getString([ 'ending-slash' => false, 'starting-slash' => ( $directory !== '' ) ]);
				// Have starting slash if there is a directory beforehand, to act as divider; don't have it if we have local 'lone.
			}

			public function getSourceWithVersion( string $local ) : string
			{
				$version_string = '';
				$source = $this->getSource( $local );
				try
				{
					$version_string = $this->getVersionString( $local );
				}
				catch ( \Exception $e )
				{
					echo( "Error: could not find modified time for file \"{$source}\"" );
				}

				return $source . $version_string;
			}

			public function getExtension( string $local ) : string
			{
				return ( $this->extension !== '' )
					? $this->extension
					: ( ( $this->directory_server !== null )
						? pathinfo( $this->getServerLocation( $local )->getString() )[ 'extension' ]
						: '' );
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
					$server_location = $this->getServerLocation( $local );
					$filetime = filemtime( $server_location->getString([ 'ending-slash' => false ]) );
					return ( $filetime !== false ) ? $filetime : 0;
				}
				return 0;
			}



		//
		//  PRIVATE
		//
		/////////////////////////////////////////////////////////

			private function getVersionString( string $local ) : string
			{
				if ( $this->directory_server !== null )
				{
					$server_location = $this->getServerLocation( $local );
					$version = $this->getVersion( $local );
					return '?m=' . ( string )( ( $version > 0 ) ? $version : '' );
				}
				return '';
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
