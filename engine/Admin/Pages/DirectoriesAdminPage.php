<?php

namespace UltraCommunity\Admin\Pages;


use UltraCommunity\Controllers\ModulesController;
use UltraCommunity\Controllers\PostTypeController;
use UltraCommunity\MchLib\Utils\MchWpUtils;
use UltraCommunity\Modules\GroupsDirectory\GroupsDirectoryAdminModule;
use UltraCommunity\Modules\MembersDirectory\MembersDirectoryAdminModule;
use UltraCommunity\MchLib\Modules\MchGroupedModules;
use UltraCommunity\PostsType\MembersDirectoryPostType;
use UltraCommunity\UltraCommUtils;

class DirectoriesAdminPage extends BaseAdminPage
{

	CONST REQUEST_DIRECTORY_TYPE = 'uc-directory';

	public function __construct( $pageMenuTitle )
	{
		parent::__construct( $pageMenuTitle );

		$this->registerDirectoriesModules();

		MchWpUtils::addFilterHook( parent::FILTER_MODULE_SUBTAB_URL, function ( $subTabUrl, $adminModuleInstance, $pageInstance ) {

			if ( ! is_a( $pageInstance, __CLASS__ ) ) {
				return $subTabUrl;
			}

			return add_query_arg( DirectoriesAdminPage::REQUEST_DIRECTORY_TYPE, DirectoriesAdminPage::getActiveDirectoryType(), $subTabUrl );

		}, 10, 3 );


		MchWpUtils::addActionHook( 'mch-admin-page-top', function ( $pageInstance ) {

			if ( ! is_a( $pageInstance, __CLASS__ ) ) {
				return;
			}

			$subMenuList = '';
			foreach ( DirectoriesAdminPage::getAllDirectoriesType() as $directoryType => $directoryName ) {
				$subMenuClass = ( $directoryType === DirectoriesAdminPage::getActiveDirectoryType() ) ? 'current' : '';
				$pageUrl      = esc_url( add_query_arg( DirectoriesAdminPage::REQUEST_DIRECTORY_TYPE, $directoryType, $pageInstance->getAdminUrl() ) );
				$subMenuList .= "<li><a href=\"$pageUrl\" class=\"$subMenuClass\">$directoryName</a></li>";
			}

			echo "<div class=\"wp-filter\"><ul class=\"filter-links\">$subMenuList</ul></div>";

		} );


		MchWpUtils::addFilterHook(parent::FILTER_MODULE_SUBTAB_NAME, function ($moduleDisplayName, $adminModuleInstance, $pageInstance){

			if(!is_a($pageInstance, __CLASS__))
				return $moduleDisplayName;


			$optionName = DirectoriesAdminPage::getActiveDirectoryType() === 'groups' ? GroupsDirectoryAdminModule::OPTION_DIRECTORY_TITLE : MembersDirectoryAdminModule::OPTION_DIRECTORY_TITLE;

			$title = $adminModuleInstance->getOption($optionName);

			return empty($title) ?$moduleDisplayName : $title;

		}, 10, 3);

		//print_r(GroupsDirectoryAdminModule::getInstance()->getAllSavedOptions());exit;
	}

	public static function getActiveDirectoryType()
	{
		$arrDirectoryType = self::getAllDirectoriesType();

		if(!empty($_GET[self::REQUEST_DIRECTORY_TYPE]) && isset($arrDirectoryType[$_GET[self::REQUEST_DIRECTORY_TYPE]])){
			return $_GET[self::REQUEST_DIRECTORY_TYPE];
		}

		if(!empty($_POST['_wp_http_referer']))
		{
			$postedArguments = wp_parse_args($_POST['_wp_http_referer']);
			if(!empty($postedArguments[self::REQUEST_DIRECTORY_TYPE]) && isset($arrDirectoryType[$postedArguments[self::REQUEST_DIRECTORY_TYPE]])){
				return $postedArguments[self::REQUEST_DIRECTORY_TYPE];
			}

		}

		reset($arrDirectoryType);
		return key($arrDirectoryType);

	}


	public static function getAllDirectoriesType()
	{
		return array(
			'members' => esc_html__('Members Directory', 'ultra-community'),
			'groups'  => esc_html__('Groups Directory', 'ultra-community'),
		);
	}

	private function registerDirectoriesModules()
	{
		$activeFormType = self::getActiveDirectoryType();

		if($activeFormType === 'members')
		{
			$arrGroupedModules = array();

			foreach(PostTypeController::getPublishedPosts(PostTypeController::POST_TYPE_MEMBERS_DIRECTORY) as $publishedPostType)
			{
				$userRoleModuleInstance = MembersDirectoryAdminModule::getInstance(true);
				$userRoleModuleInstance->setCustomPostType($publishedPostType);
				$arrGroupedModules[] = $userRoleModuleInstance;
				unset($userRoleModuleInstance);
			}


			$this->registerGroupedModules(array(new MchGroupedModules(__('Members Directory Settings', 'ultra-community'), $arrGroupedModules)));

		}

		if($activeFormType === 'groups')
		{
			$arrGroupedModules = array();

			foreach(PostTypeController::getPublishedPosts(PostTypeController::POST_TYPE_GROUPS_DIRECTORY) as $publishedPostType)
			{
				$userRoleModuleInstance = GroupsDirectoryAdminModule::getInstance(true);
				$userRoleModuleInstance->setCustomPostType($publishedPostType);
				$arrGroupedModules[] = $userRoleModuleInstance;
				unset($userRoleModuleInstance);
			}

			$this->registerGroupedModules(array(new MchGroupedModules(__('Groups Directory Settings', 'ultra-community'), $arrGroupedModules)));
		}

	}


	public function getPageHiddenContent()
	{
		$outputContent = '';


		/**
		 * @var $activeAdminModule \UltraCommunity\Modules\BaseAdminModule
		 */
		foreach ($this->getActiveAdminModules() as $activeAdminModule)
		{

			if(!$activeAdminModule->getCustomPostType() instanceof MembersDirectoryPostType)
				continue;

			$text = null;

			$confirmationText = __('Are you sure you want to delete this Members Directory?', 'ultra-community');
			$outputContent .= $this->getConfirmationPopupContent(__('Delete Members Directory', 'ultra-community'), $confirmationText, 'uc-popup-delete-members-directory-' . $activeAdminModule->getSettingKey());

			$addNewText = __('Creating New Members Directory', 'ultra-community');
//			$outputContent .= $this->getConfirmationPopupContent(__('Add New User Role', 'ultra-community'), $addNewText, 'uc-popup-add-new-user-role-' . $activeAdminModule->getSettingKey());

			$addNewFormPopupContent = <<<ADDNEWFORM
<p style="font-size: 1.1em;font-weight: 500;text-align: center;margin: 15px 0;">$addNewText</p>
ADDNEWFORM;

			$outputContent .= UltraCommUtils::getWrappedPopupHolderContent(
					'uc-popup-add-new-members-directory-' . $activeAdminModule->getSettingKey(),
					__('New Members Directory', 'ultra-community') . " $text",
					$addNewFormPopupContent, null
			);

		}


		return $outputContent;

	}


}