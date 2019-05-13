<?php

declare( strict_types = 1 );
namespace WaughJ\FileLoader
{
	class MissingFileException extends \RuntimeException
	{
		public function __construct( string $message )
		{
			parent::__construct( $message );
		}
	}
}
