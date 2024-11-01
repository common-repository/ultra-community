<?php
namespace UltraCommunity\Admin\Pages;

use UltraCommunity\Controllers\ModulesController;
use UltraCommunity\MchLib\Modules\MchGroupedModules;

class AppearanceAdminPage extends BaseAdminPage
{
	public function __construct( $pageMenuTitle )
	{
		parent::__construct( $pageMenuTitle );

		$arrGroupedModules = array();
		$arrGroupedModules[] = ModulesController::getAdminModuleInstance(ModulesController::MODULE_APPEARANCE_GENERAL);
		$arrGroupedModules[] = ModulesController::getAdminModuleInstance(ModulesController::MODULE_APPEARANCE_DIRECTORIES);

		$arrGroupedModules[] = ModulesController::getAdminModuleInstance(ModulesController::MODULE_APPEARANCE_USER_PROFILE);
		$arrGroupedModules[] = ModulesController::getAdminModuleInstance(ModulesController::MODULE_APPEARANCE_GROUP_PROFILE);
		//$arrGroupedModules[] = ModulesController::getAdminModuleInstance(ModulesController::MODULE_APPEARANCE_COLORS);

		$this->registerGroupedModules(array(new MchGroupedModules(__('Appearance Settings', 'ultra-community'), $arrGroupedModules)));

	}


	public function getPageHiddenContent()
	{
		return null;
	}
}