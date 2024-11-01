<?php


namespace UltraCommunity\Admin\Pages;


use UltraCommunity\Admin\Pages\BaseAdminPage;
use UltraCommunity\Controllers\ModulesController;
use UltraCommunity\MchLib\Modules\MchGroupedModules;

class PostSubmissionsAdminPage extends BaseAdminPage
{
	public function __construct( $pageMenuTitle )
	{
		parent::__construct( $pageMenuTitle);
		
		if( ! ModulesController::isModuleRegistered(ModulesController::MODULE_POST_SUBMISSIONS) ){
			return;
		}
		
		$arrGroupedModules = array(
			ModulesController::getAdminModuleInstance(ModulesController::MODULE_POST_SUBMISSIONS),
		);
		
		
		$this->registerGroupedModules(array(new MchGroupedModules(__('General Settings', 'ultra-community'), $arrGroupedModules)));
		
		
		//$this->registerModules();
		
		$selfPageInstance = $this;
		
		
		
	}
	
	public function getPageHiddenContent()
	{
		return null;
	}
}