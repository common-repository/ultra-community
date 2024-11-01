<?php
namespace  UltraCommunity\MchLib\Modules;

use UltraCommunity\MchLib\Plugin\MchBasePlugin;
use UltraCommunity\MchLib\Utils\MchUtils;


class MchModulesController
{

	private static $arrRegisteredModules   = null;
	private static $arrAllAvailableModules = null;


	public static function initializeAvailableModules()
	{
		if(null !== self::$arrAllAvailableModules)
			return;

		self::$arrAllAvailableModules = (array)static::getAllAvailableModules();

		self::setRegisteredModules();
	}


	protected static function getAllAvailableModules()
	{
		self::initializeAvailableModules();
		return self::$arrAllAvailableModules;
	}

	public static function getRegisteredModules()
	{
		self::initializeAvailableModules();
		
		return self::$arrRegisteredModules;
	}

	private static function setRegisteredModules()
	{
		if(null !== self::$arrRegisteredModules)
			return;
		
		self::$arrRegisteredModules = array();

		$engineDirPath = MchUtils::isNullOrEmpty($pluginDirPath = MchBasePlugin::getPluginDirectoryPath() ) ? \dirname(\dirname(__DIR__)) . '/engine/' : $pluginDirPath . '/engine/';

		foreach(self::$arrAllAvailableModules as $moduleName => &$arrModule)
		{
			self::$arrRegisteredModules[$moduleName] = array();

			foreach ($arrModule['classes'] as $className => $fileRelPath)
			{
				$fileRelPath = ltrim($fileRelPath, '/\\');
				$filePath = $engineDirPath .  $fileRelPath ; //( $dirPath =  \dirname($filePath) . \DIRECTORY_SEPARATOR . \basename($filePath) );

				if( !empty($arrModule['info']['IsLicensed']) )
				{
					if( \class_exists($moduleClassName = self::getModuleStandAloneClassName($moduleName), false) )
					{
						$classReflector = new \ReflectionClass($moduleClassName);
						$moduleDirectoryPath = \dirname($classReflector->getFileName()) . '/engine/';
						$filePath = $moduleDirectoryPath . $fileRelPath;
					}
					else
					{
						$moduleMainFilePath = \dirname($filePath) . \DIRECTORY_SEPARATOR . self::getModuleStandAloneMainFileName($moduleName);
						if(\file_exists($moduleMainFilePath)){
							require $moduleMainFilePath;
						}
					}
				}


				if( !\file_exists($filePath) )
					break;

				self::$arrRegisteredModules[$moduleName][$className] = $filePath;

				unset($arrModule['classes'][$className]);

				foreach($arrModule['classes'] as $className2 => $fileRelPath2)
				{
					$fileRelPath2 = \dirname($filePath) . \DIRECTORY_SEPARATOR . \basename($fileRelPath2);
					
					!\file_exists($fileRelPath2) ?: self::$arrRegisteredModules[$moduleName][$className2] = $fileRelPath2;
				}
				
				break;
			}

			if(empty(self::$arrRegisteredModules[$moduleName]))
				unset(self::$arrRegisteredModules[$moduleName]);

			unset($arrModule['classes']);

		}

		\spl_autoload_register(function ($moduleClassName){

			if(!isset($moduleClassName[13]) || $moduleClassName[13] !== 'y' || $moduleClassName[0] !== 'U')
				return false;

			foreach(MchModulesController::getRegisteredModules() as $arrModuleClasses)
			{
				if(!isset($arrModuleClasses[$moduleClassName]))
					continue;

				return require $arrModuleClasses[$moduleClassName];
			}

			return false;

		}, false, true);

	}

	public static function getModuleStandAloneDirectoryName($moduleName)
	{
		return \strtolower(MchBasePlugin::getPluginSlug() . '-' . MchUtils::stripNonAlphaNumericCharacters($moduleName));
	}

	public static function getModuleStandAloneMainFileName($moduleName)
	{
		return self::getModuleStandAloneDirectoryName($moduleName) . '.php';
	}

//	public static function getModuleStandAloneMainFilePath($moduleName)
//	{
//		return self::getModuleStandAloneDirectoryPath($moduleName) . '.php';
//	}

	public static function getModuleStandAloneDirectoryPath($moduleName)
	{
		if(!self::isModuleRegistered($moduleName))
			return null;

		$moduleClassName = self::getModuleStandAloneClassName($moduleName);
		if(!class_exists($moduleClassName))
		{
			if(!defined('WP_PLUGIN_DIR'))
				return null;

			return 	WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . self::getModuleStandAloneDirectoryName($moduleName);
		}

		$classReflector = new \ReflectionClass($moduleClassName);

		return dirname($classReflector->getFileName());

	}

	public static function getModuleStandAloneClassName($moduleName)
	{
		return MchUtils::stripNonAlphaNumericCharacters(MchBasePlugin::getPluginName() . $moduleName);
	}


	public static function getModuleIdByName($moduleName)
	{
		return isset(self::$arrAllAvailableModules[$moduleName]['info']['ModuleId']) ? self::$arrAllAvailableModules[$moduleName]['info']['ModuleId'] : null;
	}

	public static function isLicensedModule($moduleIdORmoduleName)
	{
		$moduleName = ((false === filter_var($moduleIdORmoduleName, FILTER_VALIDATE_INT)) ? $moduleIdORmoduleName : self::getModuleNameById($moduleIdORmoduleName));

		return !empty(self::$arrAllAvailableModules[$moduleName]['info']['IsLicensed']);
	}

	public static function getModuleDisplayName($moduleIdORmoduleName)
	{
		$moduleName = ((false === filter_var($moduleIdORmoduleName, FILTER_VALIDATE_INT)) ? $moduleIdORmoduleName : self::getModuleNameById($moduleIdORmoduleName));

		return !empty(self::$arrAllAvailableModules[$moduleName]['info']['DisplayName']) ?  self::$arrAllAvailableModules[$moduleName]['info']['DisplayName'] : null;

	}


	public static function unRegisterModule($moduleName)
	{
		unset(self::$arrRegisteredModules[(string)$moduleName]);
	}

	public static function getNotLicensedModuleNames()
	{
		$arrFreeModules = array();
		foreach(self::$arrAllAvailableModules as $moduleName => $arrAllModuleSettings){
			empty(self::$arrAllAvailableModules[$moduleName]['info']['IsLicensed']) ?  $arrFreeModules[] = $moduleName : null;
		}

		return $arrFreeModules;
	}

	public static function getLicensedModuleNames()
	{
		$arrModules = array();
		foreach(self::$arrAllAvailableModules as $moduleName => $arrAllModuleSettings){
			!empty(self::$arrAllAvailableModules[$moduleName]['info']['IsLicensed']) ?  $arrModules[] = $moduleName : null;
		}

		return $arrModules;
	}


	public static function getModuleNameById($moduleId)
	{
		foreach(self::$arrAllAvailableModules as $moduleKey => $moduleValue)
		{
			if (isset($moduleValue['info']['ModuleId']) && $moduleValue['info']['ModuleId'] == $moduleId)
				return $moduleKey;
		}

		return null;
	}

	public static function getModuleDisplayNameByInstance(MchBaseModule $moduleInstance)
	{

		self::initializeAvailableModules();
		
		$moduleClass = get_class($moduleInstance);

		foreach(self::$arrRegisteredModules as $moduleKey => $arrModuleClasses)
		{
			if(!isset($arrModuleClasses[$moduleClass]))
				continue;

			return self::getModuleDisplayName($moduleKey);
		}

		return null;
	}

	public static function getModuleOptionDisplayText($moduleId, $optionId)
	{
		if(null === ($moduleAdminInstance = self::getAdminModuleInstance(self::getModuleNameById($moduleId))))
			return null;

		return $moduleAdminInstance->getOptionDisplayTextByOptionId($optionId);
	}

	public static function getModuleOptionId($moduleName, $optionName)
	{
		if(null === ($moduleAdminInstance = self::getAdminModuleInstance($moduleName)))
			return null;

		return $moduleAdminInstance->getOptionIdByOptionName($optionName);
	}

	public static function getModuleDirectoryPath($moduleName)
	{
		self::initializeAvailableModules();
		
		if(!isset(self::$arrRegisteredModules[$moduleName]) || !is_array(self::$arrRegisteredModules[$moduleName]))
			return null;

		return @dirname(reset(self::$arrRegisteredModules[$moduleName]));
	}

	/**
	 * @param string $moduleName
	 * @param int $moduleType
	 * @return \MchBaseModule | null
	 */
	private static function getModuleInstance($moduleName, $moduleType, $forceNewInstance)
	{
		self::initializeAvailableModules();
		
		if(!isset(self::$arrRegisteredModules[$moduleName]))
			return null;

		$arrModuleClasses = \array_keys(self::$arrRegisteredModules[$moduleName]);

		if(!isset($arrModuleClasses[1]))
			return null;

		for($i = 0; $i < 2; ++$i)
		{
			$isPublicClass = (\strpos($arrModuleClasses[$i], 'Public') !== false);

			if($isPublicClass && 2 === $moduleType)
			{

//				if(!\class_exists($arrModuleClasses[$i], false)) {
//					include self::$arrRegisteredModules[$moduleName][$arrModuleClasses[$i]];
//				}

				return $arrModuleClasses[$i]::getInstance($forceNewInstance);

			}

			if( !$isPublicClass && 1 === $moduleType )
				return $arrModuleClasses[$i]::getInstance($forceNewInstance);

		}

		return null;

	}

	/**
	 * @param string $moduleName Module name
	 *
	 * @return \UltraCommunity\MchLib\Modules\MchBaseAdminModule|null
	 */
	public static function getAdminModuleInstance($moduleName, $forceNewInstance = false)
	{
		return self::getModuleInstance($moduleName, 1, $forceNewInstance);
	}

	/**
	 * @param string $moduleName Module name
	 *
	 * @return \MchBasePublicModule|null
	 */
	public static function getPublicModuleInstance($moduleName, $forceNewInstance = false)
	{
		return self::getModuleInstance($moduleName, 2, $forceNewInstance);
	}

	/**
	 * @param $moduleName string Module name
	 *
	 * @return bool
	 */
	public static function isModuleRegistered($moduleName)
	{
		self::initializeAvailableModules();
		
		return 	isset(self::$arrRegisteredModules[$moduleName]);
	}

//	public static function autoLoadModulesClasses($moduleClassName)
//	{
//		if(null === self::$arrRegisteredModules)
//			self::setRegisteredModules();
//
//		//UltraCommunity
//		if(!isset($moduleClassName[13]) || $moduleClassName[13] !== 'y' || $moduleClassName[0] !== 'U')
//			return false;
//
//		foreach(self::$arrRegisteredModules as $arrModuleClasses)
//		{
//			if(!isset($arrModuleClasses[$moduleClassName]))
//				continue;
//
//			return require $arrModuleClasses[$moduleClassName];
//		}
//
//		return false;
//	}

}


