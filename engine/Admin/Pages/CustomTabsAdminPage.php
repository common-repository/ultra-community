<?php

namespace UltraCommunity\Admin\Pages;


use UltraCommunity\Controllers\ModulesController;
use UltraCommunity\Controllers\PostTypeController;
use UltraCommunity\Controllers\ShortCodesController;
use UltraCommunity\MchLib\Utils\MchUtils;
use UltraCommunity\MchLib\Utils\MchWpUtils;
use UltraCommunity\MchLib\Modules\MchGroupedModules;
use UltraCommunity\Modules\CustomTabs\CustomTabsAdminModule;
use UltraCommunity\Modules\SocialConnect\Settings\SocialConnectSettingsAdminModule;
use UltraCommunity\Modules\SocialConnect\SocialConnectAdminModule;
use UltraCommunity\UltraCommUtils;

class CustomTabsAdminPage extends BaseAdminPage
{

	public function __construct( $pageMenuTitle )
	{
		parent::__construct( $pageMenuTitle);

		if( ! ModulesController::isModuleRegistered(ModulesController::MODULE_CUSTOM_TABS) ){
			return;
		}


		$this->registerModules();

		$selfPageInstance = $this;

		MchWpUtils::addFilterHook(parent::FILTER_MODULE_SUBTAB_NAME, function ($moduleDisplayName, $adminModuleInstance, $pageInstance){

			if($adminModuleInstance instanceof CustomTabsAdminModule && $adminModuleInstance->getCustomPostTypeId())
				return esc_html($adminModuleInstance->getCustomPostType()->PostTitle);

			return $moduleDisplayName;

		}, 10, 3);


	}



	private function registerModules()
	{

		$arrGroupedModules = array();


		if(MchUtils::isNullOrEmpty(PostTypeController::getPublishedPosts(PostTypeController::POST_TYPE_CUSTOM_TAB, true)))
		{

			$customTabPostType            = PostTypeController::getPostTypeInstance( PostTypeController::POST_TYPE_CUSTOM_TAB );
			$customTabPostType->PostId    = null;
			$customTabPostType->PostTitle = 'Tab Title';

			$customTabPostType->PostId = PostTypeController::publishPostType($customTabPostType);

			$customTabAdminModule = CustomTabsAdminModule::getInstance(true);

			$customTabAdminModule->setCustomPostType($customTabPostType);
			$customTabAdminModule->saveOption(CustomTabsAdminModule::OPTION_CUSTOM_TAB_TITLE, __('Tab Title', 'ultra-community'));

		}

		foreach(PostTypeController::getPublishedPosts(PostTypeController::POST_TYPE_CUSTOM_TAB, true) as $publishedPostType)
		{
			$customTabModuleInstance = CustomTabsAdminModule::getInstance(true);
			$customTabModuleInstance->setCustomPostType($publishedPostType);
			$arrGroupedModules[] = $customTabModuleInstance;
			unset($customTabModuleInstance);
		}

		!empty($arrGroupedModules) ?: ModulesController::getAdminModuleInstance(ModulesController::MODULE_CUSTOM_TABS);

		$this->registerGroupedModules(array(new MchGroupedModules(__('Custom Tabs Configurations', 'ultra-community'), $arrGroupedModules)));


	}


	public function getPageHiddenContent()
	{

		$outputContent = '';

		/**
		 * @var $activeAdminModule \UltraCommunity\Modules\BaseAdminModule
		 */
		foreach ($this->getActiveAdminModules() as $activeAdminModule)
		{
			if(!$activeAdminModule->getCustomPostTypeId())
				continue;

			$text = null;

			$confirmationText = __('Are you sure you want to delete this custom tab?', 'ultra-community');
			$outputContent .= $this->getConfirmationPopupContent(__('Delete Custom Tab Configuration', 'ultra-community'), $confirmationText, 'uc-popup-delete-custom-tab-' . $activeAdminModule->getSettingKey());



			$addNewText = __('Adding New Custom Tab', 'ultra-community');

			$popupContent = <<<ADDNEW
<p style="font-size: 1.1em;font-weight: 500;text-align: center;margin: 15px 0;">$addNewText</p>
ADDNEW;

			$outputContent .= UltraCommUtils::getWrappedPopupHolderContent(
				'uc-popup-add-new-custom-tab-' . $activeAdminModule->getSettingKey(),
				__('Adding New Custom Tab', 'ultra-community') . " $text",
				$popupContent, null
			);

		}





		return $outputContent;

	}

}