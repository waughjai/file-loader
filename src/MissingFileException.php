<?php

declare( strict_types = 1 );
namespace WaughJ\FileLoader
{
	class MissingFileException extends \RuntimeException
	{
		public function __construct( $filename, $fallback )
		{
			$this->filename = $filename;
			$this->fallback = $fallback;

			$message_filename = ( is_array( $filename ) && !empty( $filename ) ) ? $filename[ 0 ] : ( string )( $filename );
			parent::__construct( "Error: could not find modified time for file \"{$message_filename}\"" );
		}

		public function getFilename()
		{
			return $this->filename;
		}

		public function getFallbackContent()
		{
			return $this->fallback;
		}

		private $filename;
	}
}
