<?php
/**
 * Copyright (c) 2016 Ultra Community (http://www.ultracommunity.com)
 */

namespace UltraCommunity\Admin\Pages;

use UltraCommunity\Controllers\ModulesController;
use UltraCommunity\MchLib\Utils\MchWpUtils;
use UltraCommunity\MchLib\Modules\MchGroupedModules;
use UltraCommunity\Modules\GeneralSettings\Emails\EmailsSettingsAdminModule;
use UltraCommunity\Modules\GeneralSettings\FrontPage\FrontPageSettingsAdminModule;
use UltraCommunity\Modules\GeneralSettings\Licenses\LicensesAdminModule;
use UltraCommunity\Modules\GeneralSettings\User\UserSettingsAdminModule;
use UltraCommunity\Modules\GeneralSettings\Group\GroupSettingsAdminModule;

class GeneralSettingsAdminPage extends BaseAdminPage
{
	public function __construct( $pageMenuTitle )
	{
		parent::__construct( $pageMenuTitle );

		$this->registerPageModules();

		MchWpUtils::addFilterHook(parent::FILTER_MODULE_SUBTAB_NAME, function ($moduleDisplayName, $adminModuleInstance, $pageInstance){

			if(!is_a($pageInstance, __CLASS__))
				return $moduleDisplayName;

			if($adminModuleInstance instanceof UserSettingsAdminModule)
				return __('Users', 'ultra-community');

			if($adminModuleInstance instanceof FrontPageSettingsAdminModule)
				return __('Front Pages', 'ultra-community');

			if($adminModuleInstance instanceof EmailsSettingsAdminModule)
				return __('Emails', 'ultra-community');

			if($adminModuleInstance instanceof GroupSettingsAdminModule)
				return __('Groups', 'ultra-community');

			if($adminModuleInstance instanceof LicensesAdminModule)
				return __('Licenses', 'ultra-community');

			return $moduleDisplayName;

		}, 10, 3);

		MchWpUtils::addActionHook(parent::ACTION_SETTINGS_FORM_BEFORE_FIELDS, function ($adminPageInstance, $activeAdminModuleInstance){
			if(!is_a($adminPageInstance, __CLASS__))
				return;

			if(! $activeAdminModuleInstance instanceof FrontPageSettingsAdminModule)
				return;

			global $wp_rewrite;
			if($wp_rewrite->using_permalinks())
				return;

			$notice = sprintf( __( '<strong>UltraCommunity is almost ready!</strong> You must <a href="%s">update your permalink structure</a> to something other than the default for it to work.', 'ultra-community' ), admin_url( 'options-permalink.php' ) );

			echo '<p style="padding: 1em; font-size: 15px" class = "notice notice-error">'.$notice.'</p>';

		}, 10, 2);


	}


	private function registerPageModules()
	{
		$arrGroupedModules = array();

		$arrGroupedModules[] = ModulesController::getAdminModuleInstance(ModulesController::MODULE_FRONT_PAGE_SETTINGS);
		$arrGroupedModules[] = ModulesController::getAdminModuleInstance(ModulesController::MODULE_USER_SETTINGS);
		//$arrGroupedModules[] = ModulesController::getAdminModuleInstance(ModulesController::MODULE_GROUP_SETTINGS);
		$arrGroupedModules[] = ModulesController::getAdminModuleInstance(ModulesController::MODULE_EMAILS_SETTINGS);


		if(LicensesAdminModule::getInstance()->getDefaultOptions())
		{
			$arrGroupedModules[] = ModulesController::getAdminModuleInstance(ModulesController::MODULE_LICENSES);
		}

		$this->registerGroupedModules(array(new MchGroupedModules(__('General Settings', 'ultra-community'), $arrGroupedModules)));

	}


	public function getPageHiddenContent()
	{
		return null;
	}
}