<?php
namespace UltraCommunity\Admin\Pages;



use UltraCommunity\MchLib\Modules\MchGroupedModules;

class AddOnsAdminPage extends BaseAdminPage
{
	public function __construct( $pageMenuTitle )
	{
		parent::__construct( $pageMenuTitle);
		
//		$arrGroupedModules = array();
//		$arrGroupedModules[] = ModulesController::getAdminModuleInstance(ModulesController::MODULE_USER_SUBSCRIPTIONS);
//		$arrGroupedModules[] = ModulesController::getAdminModuleInstance(ModulesController::MODULE_USER_SUBSCRIPTIONS_PAYMENT_GATEWAYS);
		
		$arrGroupedModules = (array)apply_filters('uc_filter_extensions_page_modules', array(), $this);
		
		$this->registerGroupedModules(array(new MchGroupedModules(__('Extensions Settings', 'ultra-community'), $arrGroupedModules)));
		
		
//		MchWpUtils::addFilterHook(parent::FILTER_MODULE_SUBTAB_NAME, function ($moduleDisplayName, $adminModuleInstance, $pageInstance){
//
//			if(!is_a($pageInstance, __CLASS__))
//				return $moduleDisplayName;
//
//			if($adminModuleInstance instanceof UserSubscriptionsAdminModule)
//				return __('General Settings', 'ultra-community');
//
//			return $moduleDisplayName;
//
//		}, 10, 3);
		
		
		
	}
	
	public function getPageHiddenContent()
	{
		return null;
	}
	
}