<?php
/**
 * Copyright (c) 2016 Ultra Community (http://www.ultracommunity.com)
 */

namespace UltraCommunity\MchLib\Utils;

final class MchUtils
{

	public static function pluckArray(array $arrList, $fieldToExtract, $indexKey = null)
	{
		return (\PHP_VERSION_ID < 70000) ? \wp_list_pluck($arrList, $fieldToExtract, $indexKey) : \array_column($arrList, $fieldToExtract, $indexKey);
	}


	public static function isNullOrEmpty($someVariable)
	{
		return empty($someVariable);
	}


	public static function castToObject($sourceVariable, $destinationObject)
	{

		\is_object($sourceVariable) ?: $sourceVariable = (object)$sourceVariable;

		try
		{

			$sourceClassReflector        = new \ReflectionObject($sourceVariable);
			$destinationClassReflector   = new \ReflectionObject( $destinationObject );
			$destinationObject           = $destinationClassReflector->newInstanceWithoutConstructor();


			foreach($sourceClassReflector->getProperties() as $sourceProperty)
			{
				$sourceProperty->setAccessible(true);
				$propertyName  = $sourceProperty->getName();
				$propertyValue = $sourceProperty->getValue($sourceVariable);

				if( ! $destinationClassReflector->hasProperty($propertyName) )
				{
					$destinationObject->{$propertyName} = $propertyValue;
					continue;
				}



				$propDest = $destinationClassReflector->getProperty($propertyName);
				$propDest->setAccessible(true);
				$propDest->setValue($destinationObject, $propertyValue);

			}


		}
		catch(\ReflectionException $rfe)
		{
			return null;
		}
		finally
		{
			return $destinationObject;
		}

	}

	public static function captureOutputBuffer(callable $function, $minifyOutput = false)
	{
		ob_start(); $function();
		return $minifyOutput ? MchMinifier::getMinifiedHTML(ob_get_clean()) : ob_get_clean();
	}


	public static function filterObjectEmptyProperties($objectInstance, $allowValueOfZero = true)
	{
		foreach ( (array)get_object_vars($objectInstance) as $propertyName => $propertyValue)
		{

			if(!MchValidator::isNullOrEmpty($propertyValue, $allowValueOfZero))
				continue;

			unset($objectInstance->{$propertyName});
		}

		return $objectInstance;
	}

	public static function stringToUTF8($strValue)
	{
		return iconv(mb_detect_encoding($strValue, mb_detect_order(), true), "UTF-8", $strValue);
	}


	public static function populateObjectFromArray($objectInstance, array $arrKeyValues, $searchTextInKey = null, $replaceTextInKey = null)
	{
		foreach ($arrKeyValues as $key => $value)
		{
			(null === $searchTextInKey) ?: $key = \str_replace($searchTextInKey, $replaceTextInKey, $key);

			if (! \property_exists($objectInstance, $key) )
				continue;

			!is_array($value) ?: $value = array_map('sanitize_text_field', $value);

			$objectInstance->{$key} = is_scalar($value) ? sanitize_text_field($value) : $value;
		}

		return $objectInstance;
	}


	public static function getClassShortNameFromNameSpace($objectORstring)
	{
		return substr(strrchr(  is_object($objectORstring) ? get_class($objectORstring) : $objectORstring , '\\' ), 1);
	}

	public static function isValidURL($url)
	{
		return false !== filter_var($url, \FILTER_VALIDATE_URL);
	}

	public static function stripNonAlphaCharacters($strText)
	{
		return preg_replace("/[^a-z]/i", '', $strText );
	}

	public static function stripNonAlphaNumericCharacters($strText)
	{
		return preg_replace("/[^A-Za-z0-9]/", '', $strText);
	}

	public static function replaceNonAlphaCharacters($strText, $token = '-')
	{
		$strText = str_replace(' ', '-', $strText);
		$strText = preg_replace('/[^A-Za-z\-]/', '-', $strText);
		$strText = preg_replace('/-+/', $token, trim($strText, '-'));

		return $token === '-' ? $strText : str_replace('-', $token, $strText);
	}

	public static function replaceNonAlphaNumericCharacters($strText, $token = '-')
	{
		$strText = str_replace(' ', '-', $strText);
		$strText = preg_replace('/[^A-Za-z0-9\-]/', '-', $strText);
		$strText = preg_replace('/-+/', $token, trim($strText, '-'));
		return $token === '-' ? $strText : str_replace('-', $token, $strText);
	}

	public static function stripLeftAndRightSlashes($str)
	{
		return trim($str, '/\\');
	}

	public static function stringStartsWith($string, $stringToFind)
	{
		return 0 === strpos($string, $stringToFind);
	}

	public static function stringEndsWith($string, $stringToFind, $caseSensitive = true)
	{
		return 0 === substr_compare($string, $stringToFind, -($count = strlen($stringToFind)) , $count, $caseSensitive ? false : true);
	}

	public static function normalizeNewLine($strText, $to = PHP_EOL )
	{
		if ( ! is_string($strText) )
			return $strText;

		$arrNewLine = array( "\r\n", "\r", "\n" );

		return str_replace($arrNewLine, $to, $strText);
	}


	public static function getFormattedKeyFromText($strText)
	{
		return preg_replace( '/[^a-z0-9\-]/', '', strtolower($strText) );
	}


	public static function getVersionIdFromString($strDottedVersion)
	{
		$arrVersion = explode('.', $strDottedVersion);

		for($i = 0; $i < 3; ++$i)
		{
			if(!isset($arrVersion[$i])){
				$arrVersion[$i] = 0;
				continue;
			}
			$arrVersion[$i] = (int)$arrVersion[$i];
		}

		return ($arrVersion[0] * 10000 + $arrVersion[1] * 100 + $arrVersion[2]);
	}


	public static function getStringVersionFromId($intVersionId)
	{
		$majorVersion   = floor( $intVersionId / 10000);
		$minorVersion   = floor( ( $intVersionId % 10000) / 100);
		$releaseVersion = floor( ( $intVersionId % 10000) % 100);

		return 	"$majorVersion.$minorVersion.$releaseVersion";
	}


	public static function hexToRgb($hex, $alpha = 1, $useCssFormat = true)
	{
		$hex      = ltrim(trim($hex), '#') ;//str_replace('#', '', $hex);
		$length   = strlen($hex);

		$rgb = array();

		$rgb['r'] = hexdec($length === 6 ? substr($hex, 0, 2) : ($length === 3 ? str_repeat(substr($hex, 0, 1), 2) : 0));
		$rgb['g'] = hexdec($length === 6 ? substr($hex, 2, 2) : ($length === 3 ? str_repeat(substr($hex, 1, 1), 2) : 0));
		$rgb['b'] = hexdec($length === 6 ? substr($hex, 4, 2) : ($length === 3 ? str_repeat(substr($hex, 2, 1), 2) : 0));

		if ( $alpha < 1) {
			$rgb['a'] = $alpha;
		}

		if(!$useCssFormat)
			return $rgb;

		return isset($rgb['a']) ? 'rgba' . '(' . implode(', ', $rgb) . ')' : 'rgb' . '(' . implode(', ', $rgb) . ')';

	}

	public static function getRandomHtmlElementId($length = 5)
	{
		$randomId = strtolower(self::getRandomString($length));
		while (! preg_match('/[a-z]/', $randomId[0]) ){
			$randomId = strtolower(self::getRandomString($length));
		}
		return $randomId;
	}

	public static function getRandomString($length, $allowSpecialCharacters = false)
	{
		if(($length = (int)$length) > 256){
			$length = 256;
		}
		$length > 0 ?: $length = 1;

		$randomString     = '';

		$characters       = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		$charactersLength = 62;

		if($allowSpecialCharacters)
		{
			$characters = '!"#$%&\'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\]^_`abcdefghijklmnopqrstuvwxyz{|}~';
			$charactersLength = 94;
		}

		$arrNumberInfo     =  (62 === $charactersLength) ? array(1, 63) : array(1, 127);
		$arrNumberInfo[0] *= $length ;
		$arrNumberInfo[1]  = 256 - (256 % $length);

		while(!isset($randomString[$length -1]))
		{
			$randomBytes = self::getBytesUsingMTRand($arrNumberInfo[0]);

			for ($i = 0; $i < $arrNumberInfo[0]; ++$i)
			{
				if (ord($randomBytes[$i]) <= $arrNumberInfo[1])
				{
					$randomString .= $characters[ord($randomBytes[$i]) % $charactersLength];
				}
			}
		}

		return !isset($randomString[$length]) ? $randomString : substr($randomString, 0, $length);
	}

	private static function getBytesUsingMTRand($length)
	{
		$randomBytes = '';

		for($i = 0; $i < $length; $i++)
		{
			$randomBytes .= chr((mt_rand() ^ mt_rand()) % 256);
		}

		return $randomBytes;
	}



	public static function overlapIntervals(array $arrIntervals)
	{
		if(!isset($arrIntervals[1]))
			return $arrIntervals;

		$arrIntervals = array_values($arrIntervals);
		usort($arrIntervals, array(__CLASS__, 'sortIntervals'));
		$n = 0; $len = count($arrIntervals);
		for ($i = 1; $i < $len; ++$i)
		{
			if ($arrIntervals[$i][0] > $arrIntervals[$n][1] + 1) {
				$n = $i;
			}
			else
			{
				if ($arrIntervals[$n][1] < $arrIntervals[$i][1])
					$arrIntervals[$n][1] = $arrIntervals[$i][1];

				unset($arrIntervals[$i]);
			}
		}

		return array_values($arrIntervals);
	}

	private static function sortIntervals($firstArray, $secondArray)
	{
		return $firstArray[0] - $secondArray[0];
	}


	public static function longDigitBaseConvert($longDigit, $sourceBase, $destBase, $minDigits = 1)
	{
		$longDigit   = strtolower($longDigit);
		$sourceBase  = (int)$sourceBase;
		$destBase    = (int)$destBase;
		$minDigits   = (int)$minDigits;

		if($minDigits < 1 || $sourceBase < 2 || $destBase < 2 || $sourceBase > 36 || $destBase > 36)
			return null;


		static $gmpExtensionLoaded = null;
		(null === $gmpExtensionLoaded) ?  $gmpExtensionLoaded = extension_loaded( 'gmp' ) : null;

		$result = null;

		if( $gmpExtensionLoaded )
		{
			$longDigit = ltrim( $longDigit, '0' );
			$result = gmp_strval( gmp_init( $longDigit ? $longDigit : '0', $sourceBase ), $destBase );
			return str_pad( $result, $minDigits, '0', STR_PAD_LEFT );
		}

		static $bcmathExtensionLoaded = null;
		(null === $bcmathExtensionLoaded) ?  $bcmathExtensionLoaded = extension_loaded( 'bcmath' ) : null;

		static $arrBaseChars = array(
				10 => 'a', 11 => 'b', 12 => 'c', 13 => 'd', 14 => 'e', 15 => 'f',
				16 => 'g', 17 => 'h', 18 => 'i', 19 => 'j', 20 => 'k', 21 => 'l',
				22 => 'm', 23 => 'n', 24 => 'o', 25 => 'p', 26 => 'q', 27 => 'r',
				28 => 's', 29 => 't', 30 => 'u', 31 => 'v', 32 => 'w', 33 => 'x',
				34 => 'y', 35 => 'z',

				'0' => 0, '1' => 1, '2' => 2, '3' => 3, '4' => 4, '5' => 5,
				'6' => 6, '7' => 7, '8' => 8, '9' => 9, 'a' => 10, 'b' => 11,
				'c' => 12, 'd' => 13, 'e' => 14, 'f' => 15, 'g' => 16, 'h' => 17,
				'i' => 18, 'j' => 19, 'k' => 20, 'l' => 21, 'm' => 22, 'n' => 23,
				'o' => 24, 'p' => 25, 'q' => 26, 'r' => 27, 's' => 28, 't' => 29,
				'u' => 30, 'v' => 31, 'w' => 32, 'x' => 33, 'y' => 34, 'z' => 35
		);


		if($bcmathExtensionLoaded)
		{
			$decimal = '0';
			foreach ( str_split($longDigit) as $char ) {
				$decimal = bcmul( $decimal, $sourceBase );
				$decimal = bcadd( $decimal, $arrBaseChars[$char] );
			}

			for ( $result = ''; bccomp( $decimal, 0 ); $decimal = bcdiv( $decimal, $destBase, 0 ) ) {
				$result .= $arrBaseChars[bcmod( $decimal, $destBase )];
			}

			return str_pad( strrev( $result ), $minDigits, '0', STR_PAD_LEFT );
		}


		$inDigits = array();
		foreach ( str_split($longDigit) as $char ) {
			$inDigits[] = $arrBaseChars[$char];
		}

		$result = '';
		while ( $inDigits )
		{
			$work = 0;
			$workDigits = array();

			foreach ( $inDigits as $digit )
			{
				$work *= $sourceBase;
				$work += $digit;

				if ( $workDigits || $work >= $destBase ) {
					$workDigits[] = (int)( $work / $destBase );
				}

				$work %= $destBase;
			}

			$result  .= $arrBaseChars[$work];
			$inDigits = $workDigits;
		}

		return str_pad( strrev( $result ), $minDigits, '0', STR_PAD_LEFT );

	}


	public static function getArrayKeyNumericIndex($keyName, array $arr)
	{
		return false === ($index = array_search($keyName, array_keys($arr))) ? -1 : $index;
	}

	public static function addArrayKeyValueAfterSpecificKey($afterKey, $arrAssociative, $newKey, $newValue)
	{
		if(-1 === ($keyIndex = self::getArrayKeyNumericIndex($afterKey, $arrAssociative)) )
			return $arrAssociative;

		return array_merge( array_slice($arrAssociative, 0, $keyIndex + 1, true), array($newKey => $newValue), array_slice($arrAssociative, $keyIndex + 1, null, true ) );
	}
}