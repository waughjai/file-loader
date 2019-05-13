<?php

declare( strict_types = 1 );
namespace WaughJ\FileLoader
{
	class MissingFileException extends \RuntimeException
	{
		public function __construct( string $filename, string $fallback )
		{
			$this->filename = $filename;
			$this->fallback = $fallback;
			parent::__construct( "Error: could not find modified time for file \"{$filename}\"" );
		}

		public function getFilename() : string
		{
			return $this->filename;
		}

		public function getFallbackContent() : string
		{
			return $this->fallback;
		}

		private $filename;
	}
}
