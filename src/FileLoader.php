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
		public function __construct( array $arguments )
		{
			if ( !isset( $arguments[ 'directory-url' ] ) )
			{
				$arguments[ 'directory-url' ] = '';
			}
			$this->directory_url = Url::fromString( $arguments[ 'directory-url' ] );

			$this->directory_server = ( !isset( $arguments[ 'directory-server' ] ) || !$arguments[ 'directory-server' ] ) ? null : new Directory( $arguments[ 'directory-server' ] );
			$this->shared_directory = ( !isset( $arguments[ 'shared-directory' ] ) || !$arguments[ 'shared-directory' ] ) ? '' : new Directory( $arguments[ 'shared-directory' ] );
			$this->extension = TestHashItemString( $arguments, 'extension', '' );
		}

		public function getSource( string $local ) : string
		{
			$local = new Directory( $this->getLocalInShared( $local ) );
			return ( string )( $this->directory_url ) . $local->getString([ 'ending-slash' => false ]);
		}

		public function getSourceWithVersion( string $local ) : string
		{
			return $this->getSource( $local ) . $this->getVersionString( $local );
		}

		private function getVersionString( string $local ) : string
		{
			if ( $this->directory_server !== null )
			{
				$server_location = $this->directory_server->addDirectory( $this->getLocalInShared( $local ) );
				return '?m=' . filemtime( $server_location->getString([ 'ending-slash' => false ]) );
			}
			return '';
		}

		private function getLocalInShared( string $local ) : Directory
		{
			$local .= $this->getExtensionString();
			return ( $this->shared_directory === '' ) ? new Directory( $local ) : $this->shared_directory->addDirectory( new Directory( $local ) );
		}

		private function getExtensionString() : string
		{
			return ( ( $this->extension === '' ) ? '' : '.' ) . $this->extension;
		}

		private $directory_url;
		private $directory_server;
		private $shared_directory;
		private $extension;
	}
}
