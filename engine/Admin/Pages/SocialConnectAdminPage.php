<?php

namespace UltraCommunity\Admin\Pages;


use UltraCommunity\Controllers\ModulesController;
use UltraCommunity\Controllers\PostTypeController;
use UltraCommunity\Controllers\ShortCodesController;
use UltraCommunity\MchLib\Utils\MchUtils;
use UltraCommunity\MchLib\Utils\MchWpUtils;
use UltraCommunity\MchLib\Modules\MchGroupedModules;
use UltraCommunity\Modules\SocialConnect\Settings\SocialConnectSettingsAdminModule;
use UltraCommunity\Modules\SocialConnect\SocialConnectAdminModule;
use UltraCommunity\UltraCommUtils;

class SocialConnectAdminPage extends BaseAdminPage
{

	public function __construct( $pageMenuTitle )
	{
		parent::__construct( $pageMenuTitle);

		if( ! ModulesController::isModuleRegistered(ModulesController::MODULE_SOCIAL_CONNECT) ){
			return;
		}

		$this->registerModules();

		$selfPageInstance = $this;

		MchWpUtils::addFilterHook(parent::FILTER_MODULE_SUBTAB_NAME, function ($moduleDisplayName, $adminModuleInstance, $pageInstance){

			if($adminModuleInstance instanceof SocialConnectSettingsAdminModule)
				return __('Providers Settings', 'ultra-community');

			if($adminModuleInstance instanceof SocialConnectAdminModule)
				return $adminModuleInstance->getOption(SocialConnectAdminModule::OPTION_CONFIGURATION_TITLE);


			return $moduleDisplayName;

		}, 10, 3);


	}



	private function registerModules()
	{

		$arrGroupedModules = array();


		if(MchUtils::isNullOrEmpty(PostTypeController::getPublishedPosts(PostTypeController::POST_TYPE_SOCIAL_CONNECT, true)))
		{

			$socialConnectPostType            = PostTypeController::getPostTypeInstance( PostTypeController::POST_TYPE_SOCIAL_CONNECT );
			$socialConnectPostType->PostId    = null;
			$socialConnectPostType->PostTitle = 'UltraComm Social Connect CPT';

			$socialConnectPostType->PostId = PostTypeController::publishPostType($socialConnectPostType);

			$socialConnectAdminModule = SocialConnectAdminModule::getInstance(true);

			$socialConnectAdminModule->setCustomPostType($socialConnectPostType);
			$socialConnectAdminModule->saveOption(SocialConnectAdminModule::OPTION_CONFIGURATION_TITLE, __('Default', 'ultra-community'));


		}

		foreach(PostTypeController::getPublishedPosts(PostTypeController::POST_TYPE_SOCIAL_CONNECT, true) as $publishedPostType)
		{
			$userRoleModuleInstance = SocialConnectAdminModule::getInstance(true);
			$userRoleModuleInstance->setCustomPostType($publishedPostType);
			$arrGroupedModules[] = $userRoleModuleInstance;
			unset($userRoleModuleInstance);
		}

		!empty($arrGroupedModules) ?: ModulesController::getAdminModuleInstance(ModulesController::MODULE_SOCIAL_CONNECT);

		$this->registerGroupedModules(array(new MchGroupedModules(__('Social Connect Configurations', 'ultra-community'), $arrGroupedModules)));

		$arrGroupedModules = array();
		$arrGroupedModules[] = SocialConnectSettingsAdminModule::getInstance();

		$this->registerGroupedModules(array(new MchGroupedModules(__('Social Connect Providers Settings', 'ultra-community'), $arrGroupedModules)));

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

			$confirmationText = __('Are you sure you want to delete this configuration?', 'ultra-community');
			$outputContent .= $this->getConfirmationPopupContent(__('Delete Social Connect Configuration', 'ultra-community'), $confirmationText, 'uc-popup-delete-social-connect-config-' . $activeAdminModule->getSettingKey());



			$addNewText = __('Adding New Social Connect Configuration', 'ultra-community');

			$popupContent = <<<ADDNEW
<p style="font-size: 1.1em;font-weight: 500;text-align: center;margin: 15px 0;">$addNewText</p>
ADDNEW;

			$outputContent .= UltraCommUtils::getWrappedPopupHolderContent(
				'uc-popup-add-new-social-connect-config-' . $activeAdminModule->getSettingKey(),
				__('Add New Social Connect Configuration', 'ultra-community') . " $text",
				$popupContent, null
			);


			$embedText = __('To embed this social connect configuration on your site, please copy and paste the following shortcode inside a page or a post content', 'ultra-community');


			$embedShortCode = ShortCodesController::getEmbeddableShortCode($activeAdminModule->getCustomPostType());


			$embedOutputContent = <<<EMBEDFORM
<div class = "uc-gap-20"></div>

	<p style="font-size: 1.1em;font-weight: 400;text-align: center;margin:0;">$embedText</p>

		<p style="font-size: 1.3em; width: 70%; font-weight: 500;text-align: center;padding:10px 0; margin:15px auto; border:1px solid #ccc; color:#000;">$embedShortCode</p>

<div class = "uc-gap-20"></div>
EMBEDFORM;

			$outputContent .= UltraCommUtils::getWrappedPopupHolderContent(
					'uc-popup-embed-form-short-code-' . $activeAdminModule->getSettingKey(),
					__('Embed Social Connect Short Code', 'ultra-community') . " $text",
					$embedOutputContent, null
			);



		}


		return $outputContent;

	}

}