<?php
/**
 * Copyright (c) 2017 Ultra Community (http://www.ultracommunity.com)
 */

namespace UltraCommunity\Admin\Pages;


use UltraCommunity\Controllers\ModulesController;
use UltraCommunity\Controllers\PostTypeController;
use UltraCommunity\MchLib\Modules\MchGroupedModules;
use UltraCommunity\Modules\MembersDirectory\MembersDirectoryAdminModule;

class MembersDirectoryAdminPage extends BaseAdminPage
{

	public function __construct( $pageMenuTitle )
	{
		parent::__construct( $pageMenuTitle);

		$this->registerModules();

		$selfPageInstance = $this;

//		MchWpUtils::addFilterHook(parent::FILTER_MODULE_SUBTAB_NAME, function ($moduleDisplayName, $adminModuleInstance, $pageInstance){
//
//			if(!is_a($pageInstance, __CLASS__) || !($adminModuleInstance instanceof UserRoleAdminModule))
//				return $moduleDisplayName;
//
//			$title = $adminModuleInstance->getOption(UserRoleAdminModule::OPTION_DIRECTORY_TITLE);
//			return empty($title) ? $adminModuleInstance->getDefaultOptionValue(UserRoleAdminModule::OPTION_DIRECTORY_TITLE) : $title;
//
//		}, 10, 3);

	}


	private function registerModules()
	{

		if( ! ModulesController::isModuleRegistered(ModulesController::MODULE_MEMBERS_DIRECTORY) ){
			return;
		}

		$arrGroupedModules = array();

		foreach(PostTypeController::getPublishedPosts(PostTypeController::POST_TYPE_MEMBERS_DIRECTORY) as $publishedPostType)
		{
			$userRoleModuleInstance = MembersDirectoryAdminModule::getInstance(true);
			$userRoleModuleInstance->setCustomPostType($publishedPostType);
			$arrGroupedModules[] = $userRoleModuleInstance;
			unset($userRoleModuleInstance);
		}

		!empty($arrGroupedModules) ?: ModulesController::getAdminModuleInstance(ModulesController::MODULE_MEMBERS_DIRECTORY);

		$this->registerGroupedModules(array(new MchGroupedModules(__('Members Directory Settings', 'ultra-community'), $arrGroupedModules)));

	}


	public function getPageHiddenContent()
	{
	}

}