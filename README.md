File Loader
=========================

Object for easily generating image sources with autoversioning for handling cache corruption.

Loader object takes in an optional hash map as its 1 argument ( though using this object without any arguments will just return the very string you give it from getSource ). The object will recognize any & all o' 4 o' the following optional keys for that hash map:
* "directory-url": URL directory in which to look for file. If set, automatically appends this before given local filename. If none set, URL will just be the local.
* "directory-server": Server directory in which file is located. Used for finding modified time for this file, used for determining the "version" o' this file automatically for easy, automatic cache clearing when a file changes ( & needs to be reloaded ). If not set, no versioning is used.
* "shared-directory": Directory that goes after both URL & Server directories listed before.
* "extension": File extension to automatically add to end o' local file.

After setting this object up, you can use it to get sources for local filenames easily.

## Example

All CSS files are kept in /home/example.com/public_html/assets/css & are loaded from https://www.example.com/assets/css:

	use WaughJ\FileLoader\FileLoader;

	$loader = new FileLoader
	([
		'directory-url' => 'https://www.example.com',
		'directory-server' => '/home/example.com/public_html',
		'shared-directory' => 'assets/css',
		'extension' => 'css'
	]);

Then you can easily load files with just the local name with the "getSourceWithVersion" method:

	// Get main CSS file
	$loader->getSourceWithVersion( 'main' );

This will output "https://www.example.com/assets/css/main.css?m={#######}", with the #s being a long # representing the last modified time.

You can make new versions o' a loader with 1 o' the 4 attributes changed with the following methods, which return a new instance o' the loader object with all the other attributes the same:
* changeURLDirectory
* changeServerDirectory
* changeSharedDirectory
* changeExtension

To change multiple attributes easily, just chain them:

	$loader2 = $loader1->changeURLDirectory( 'https:/www.new-website.com' )->changeExtension( 'sass' );

If you want to change the same loader, just set the loader equal to change, replacing the ol' instance with the new:

	$loader1 = $loader1->changeURLDirectory( 'https:/www.new-website.com' )->changeExtension( 'sass' );

## Error Handling

The "getSourceWithVersion" method analyzes the file on the server's modified date to create the version string. If it can't access the file, it throws a WaughJ\FileLoader\MissingFileException exception, which includes the server & url paths in it. If you want a safe way to fallback to versionless source without breaking the website or program, run a simple try & catch & in the catch use the exception object's "getFallbackContent" method to get the versionless source:

	$file_url = null;
	try
	{
		$file_url = $loader->getSourceWithVersion( 'main' );
	}
	catch ( MissingFileException $e )
	{
		// Perhaps run some logging code that logs that $e->getFilename() couldn't be found.

		// If exception is thrown, $file_url will be the equivalent o' $loader->getSource( 'main' ).
		$file_url = $e->getFallbackContent();
	}
