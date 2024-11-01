<?php
/**
 * Copyright (c) 2016 Ultra Community (http://www.ultracommunity.com)
 */

namespace UltraCommunity\MchLib\Utils;


final class MchDirectoryUtils
{
	public static function getDirectoryFiles($directoryPath, $pattern = "*.*", $sortFiles = false)
	{
		$directoryPath = rtrim($directoryPath, '/\\');
		if(empty($directoryPath))
			return array();

		$directoryPath .= DIRECTORY_SEPARATOR;
		$arrFiles = $sortFiles ? @glob($directoryPath . $pattern) : @glob($directoryPath . $pattern, GLOB_NOSORT);

		return (array)$arrFiles;

	}

	public static function isDirectoryAccessible($directoryPath, $createIfNotExists = false)
	{
		//PHP_VERSION_ID < 50300 ? @clearstatcache() : @clearstatcache(true, $directoryPath);

		if(!@is_dir($directoryPath) || !@is_readable($directoryPath))
		{

			if(!self::isPathAccessible($directoryPath))
				return false;

			if($createIfNotExists && !self::createDirectory($directoryPath))
				return false;
		}

		return function_exists('wp_is_writable') ? wp_is_writable($directoryPath) && @is_readable($directoryPath): @is_writable($directoryPath)  && @is_readable($directoryPath);
	}

	public static function createDirectory($directoryPath)
	{
		return \wp_mkdir_p(rtrim($directoryPath, '/\\'));
	}


	public static function getAllDirectoryFiles($dirPath)
	{
		if( (! @is_dir($dirPath) ) || ( ! @is_readable($dirPath) ) )
			return array();

		$arrFiles = array();

		foreach ( new \DirectoryIterator ( $dirPath ) as $file ) {
			if(!$file->isFile()) continue;
			$arrFiles[] = $file->getPathname();
		}

		return $arrFiles;
	}

	public static function getDirectorySubDirectories($directoryPath, $recursive = false)
	{
		$directoryPath = rtrim($directoryPath, '/\\');

		if(!$recursive)
		{
			return (false === ($arrSubDirectories = glob($directoryPath . '/*' , GLOB_ONLYDIR | GLOB_NOSORT)) ) ? array() : $arrSubDirectories;
		}

		$arrSubDirectories = array();
		$directoryIterator = new \RecursiveIteratorIterator( new \RecursiveDirectoryIterator($directoryPath, \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST );

		foreach($directoryIterator as $splFileInfoObject)
		{
			!$splFileInfoObject->isDir() ?: $arrSubDirectories[] = $splFileInfoObject->getRealpath();
		}

		return $arrSubDirectories;
	}

	public static function deleteDirectory($directoryPath)
	{
		$directoryPath = rtrim($directoryPath, '/\\');
		if(empty($directoryPath) || !@is_dir($directoryPath))
			return;

		foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directoryPath), \RecursiveIteratorIterator::CHILD_FIRST) as $fileInfo){
			$fileInfo->isDir() ? @rmdir($fileInfo->getRealPath()): @unlink($fileInfo->getRealPath());
		}

		@rmdir($directoryPath);

	}

	private static function isPathAccessible($path)
	{
		$openBaseDirSettings = strtolower( str_replace( '\\', '/', ini_get( 'open_basedir' ) ) );
		if(empty($openBaseDirSettings))
			return true;

		$path = \trailingslashit( strtolower( str_replace( '\\', '/', $path ) ) );

		foreach( (array)explode( PATH_SEPARATOR, $openBaseDirSettings ) as $openPath)
		{
			if(empty($openPath))
				continue;

			if( 0 === strpos($path, $openPath) )
				return true;
		}

		return false;

	}

}