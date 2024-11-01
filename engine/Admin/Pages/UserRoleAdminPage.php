<?php
/**
 * Copyright (c) 2016 Ultra Community (http://www.ultracommunity.com)
 */
namespace UltraCommunity\Admin\Pages;


use UltraCommunity\Controllers\ModulesController;
use UltraCommunity\Controllers\PostTypeController;
use UltraCommunity\MchLib\Utils\MchWpUtils;
use UltraCommunity\MchLib\Modules\MchGroupedModules;
use UltraCommunity\Modules\UserRole\UserRoleAdminModule;
use UltraCommunity\UltraCommUtils;


class UserRoleAdminPage extends BaseAdminPage
{

	public function __construct( $pageMenuTitle )
	{
		parent::__construct( $pageMenuTitle);

		$this->registerModules();

		$selfPageInstance = $this;

//		MchWpUtils::addActionHook('current_screen', function() use ($selfPageInstance){
//
//			if( ! MchWpUtils::isSuperAdminLoggedIn() )
//				return;
//
//			if(empty($_GET['add-new']) || 1 !== (int)$_GET['add-new'] || !$selfPageInstance->isActive() || !MchWpUtils::isSuperAdminLoggedIn() || !ModulesController::isModuleRegistered(ModulesController::MODULE_USER_ROLE))
//				return;
//
//			$adminModuleInstance = UserRoleAdminModule::getInstance(true);
//			$adminModuleInstance->setModuleCustomPostType(PostTypeController::getPostTypeInstance(PostTypeController::POST_TYPE_USER_ROLE));
//			$adminModuleInstance->getModuleCustomPostType()->PostTitle = $adminModuleInstance->getDefaultOptionValue(UserRoleAdminModule::OPTION_DIRECTORY_TITLE);
//
//			$adminModuleInstance->getModuleCustomPostType()->PostId = PostTypeController::publishPostType($adminModuleInstance->getModuleCustomPostType());
//			$adminModuleInstance->saveDefaultOptions();
//			wp_safe_redirect($selfPageInstance->getAdminUrl(false));exit;
//
//		});

		MchWpUtils::addFilterHook(parent::FILTER_MODULE_SUBTAB_NAME, function ($moduleDisplayName, $adminModuleInstance, $pageInstance){

			if(!is_a($pageInstance, __CLASS__) || !($adminModuleInstance instanceof UserRoleAdminModule))
				return $moduleDisplayName;
//print_r($adminModuleInstance->getAllSavedOptions());

			$title = $adminModuleInstance->getOption(UserRoleAdminModule::OPTION_ROLE_TITLE);

			return empty($title) ? $adminModuleInstance->getDefaultOptionValue(UserRoleAdminModule::OPTION_ROLE_TITLE) : $title;

		}, 10, 3);

	}

	public function registerPageMetaBoxes()
	{
		parent::registerPageMetaBoxes();

		if($this->getPageLayoutColumns() < 2)
			return;
return;
		add_meta_box(
			"uc-form-actions-metabox",
			__('User Role Options', 'ultra-community'),
			array( $this, 'renderUserRoleFormActionsMetaBox' ),
			$this->getAdminScreenId(),
			'side',
			'core',
			null
		);

	}

	public function renderUserRoleFormActionsMetaBox()
	{
		$addNewButtonText = __('Add New User Role', 'ultra-community');
		$deleteButtonText = __('Delete User Role', 'ultra-community');

		$addNewUrl = $this->getAdminUrl(true);

		$customPostTypeId = -1;
		foreach ($this->getActiveAdminModules() as $activeAdminModule){
			if(!$activeAdminModule instanceof UserRoleAdminModule)
				continue;
			$customPostTypeId = $activeAdminModule->getCustomPostTypeId();
			break;
		}

		if(-1 === $customPostTypeId)
			return;


		$htmlCode = '';

		$htmlCode  .= '<div class="uch">';
		$htmlCode  .= '<div class="uc-g">';
		$htmlCode .= '<div class="uc-u-1">';
			$htmlCode .= '<ul style="">';


				$htmlCode .= '<li style="padding: 2px 0 7px; border-bottom: 1px solid #e1e1e1; text-align: center;" >';
				$htmlCode .= "<a  style=\"width:75%\" href=\"$addNewUrl\" class=\"uc-button uc-button-primary uc-tooltip\"><i class=\"fa fa-plus\"></i>  $addNewButtonText</a>";
				$htmlCode .= '</li>';

				$htmlCode .= '<li style="padding: 7px 0; border-bottom: 1px solid #e1e1e1; text-align: center">';
				$htmlCode .= "<button style=\"width:75%\" id = \"uc-delete-user-role\" data-postid = \"$customPostTypeId\" class=\"uc-button  uc-button-danger uc-tooltip\"><i class=\"fa fa-trash\"></i>  $deleteButtonText</button>";
				$htmlCode .= '</li>';


			$htmlCode .= '</ul>';
		$htmlCode .= '</div>';
		$htmlCode .= '</div>';
		$htmlCode .= '</div>';

		echo $htmlCode;
	}


	private function registerModules()
	{
		if( ! ModulesController::isModuleRegistered(ModulesController::MODULE_USER_ROLE) ){
			return;
		}

		$arrUserRolePostType = PostTypeController::getPublishedPosts(PostTypeController::POST_TYPE_USER_ROLE);
		foreach($arrUserRolePostType as $index => $publishedPostType)
		{
			if(null === ($adminModuleInstance = PostTypeController::getAssociatedAdminModuleInstance($publishedPostType))){
				unset($arrUserRolePostType[$index]);
				continue;
			}

			$publishedPostType->Priority = (int)$adminModuleInstance->getOption(UserRoleAdminModule::OPTION_ROLE_PRIORITY);

		}


		usort($arrUserRolePostType, function($userRolePostType1, $userRolePostType2){ // ascending by role priority
			return $userRolePostType1->Priority - $userRolePostType2->Priority;
		});

		$arrGroupedModules = array();

		foreach($arrUserRolePostType as $publishedPostType)
		{
			$userRoleModuleInstance = UserRoleAdminModule::getInstance(true);
			$userRoleModuleInstance->setCustomPostType($publishedPostType);
			$arrGroupedModules[] = $userRoleModuleInstance;
			unset($userRoleModuleInstance);
		}

		!empty($arrGroupedModules) ?: ModulesController::getAdminModuleInstance(ModulesController::MODULE_USER_ROLE);

		$this->registerGroupedModules(array(new MchGroupedModules(__('User Role Settings', 'ultra-community'), $arrGroupedModules)));
	}


	public function getPageHiddenContent()
	{
//		$title = __('Delete user role', 'ultra-community');
//		$text  = __('Are you sure you want to delete this user role?', 'ultra-community');
//
//		return $this->getConfirmationPopupContent($title, $text);


		$outputContent = '';

		/**
		 * @var $activeAdminModule \UltraCommunity\Modules\BaseAdminModule
		 */
		foreach ($this->getActiveAdminModules() as $activeAdminModule)
		{
			if(!$activeAdminModule->getCustomPostTypeId())
				continue;

			$text = null;

			$confirmationText = __('Are you sure you want to delete this user role?', 'ultra-community');
			$outputContent .= $this->getConfirmationPopupContent(__('Delete User Role', 'ultra-community'), $confirmationText, 'uc-popup-delete-user-role-' . $activeAdminModule->getSettingKey());



			$addNewText = __('Adding New User Role', 'ultra-community');
//			$outputContent .= $this->getConfirmationPopupContent(__('Add New User Role', 'ultra-community'), $addNewText, 'uc-popup-add-new-user-role-' . $activeAdminModule->getSettingKey());

			$addNewFormPopupContent = <<<ADDNEWFORM
<p style="font-size: 1.1em;font-weight: 500;text-align: center;margin: 15px 0;">$addNewText</p>
ADDNEWFORM;

			$outputContent .= UltraCommUtils::getWrappedPopupHolderContent(
				'uc-popup-add-new-user-role-' . $activeAdminModule->getSettingKey(),
				__('Add New User Role', 'ultra-community') . " $text",
				$addNewFormPopupContent, null
			);

		}


		return $outputContent;

	}

}