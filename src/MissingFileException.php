<?php

declare( strict_types = 1 );
namespace WaughJ\FileLoader
{
	class MissingFileException extends \RuntimeException
	{
		public function __construct( string $filename, string $file_url )
		{
			$this->filename = $filename;
			$this->file_url = $file_url;
			parent::__construct( "Error: could not find modified time for file \"{$filename}\"" );
		}

		public function getFilename() : string
		{
			return $this->filename;
		}

		public function getFileURL() : string
		{
			return $this->file_url;
		}

		private $filename;
	}
}
