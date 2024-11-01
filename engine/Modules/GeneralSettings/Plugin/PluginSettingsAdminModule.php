<?php
/**
 * Copyright (c) 2016 Ultra Community (http://www.ultracommunity.com)
 */

namespace UltraCommunity\Modules\GeneralSettings\Plugin;


use UltraCommunity\Modules\BaseAdminModule;

class PluginSettingsAdminModule extends BaseAdminModule
{
	CONST PLUGIN_ACTIVE_VERSION = 'PV';
	CONST SETTINGS_LAST_UPDATED = 'SU';

	public function getDefaultOptions()
	{
		static $arrDefaultSettingOptions = null;
		if(null !== $arrDefaultSettingOptions)
			return $arrDefaultSettingOptions;

		$arrDefaultSettingOptions = array(

//			self::PLUGIN_ACTIVE_VERSION => array(
//				'Value'      => NULL,
//			),
//
//			self::SETTINGS_LAST_UPDATED => array(
//				'Value'      => NULL,
//			),
		);

		return $arrDefaultSettingOptions;
	}

	public function validateModuleSettingsFields($arrOptions)
	{
		// TODO: Implement validateModuleSettingsFields() method.
	}
}