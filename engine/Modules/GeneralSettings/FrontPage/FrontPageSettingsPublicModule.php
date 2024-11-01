<?php
/**
 * Copyright (c) 2016 Ultra Community (http://www.ultracommunity.com)
 */

namespace UltraCommunity\Modules\GeneralSettings\FrontPage;

use UltraCommunity\MchLib\WordPress\Repository\WpPostRepository;
use UltraCommunity\Modules\BasePublicModule;
use UltraCommunity\UltraCommHooks;


class FrontPageSettingsPublicModule extends BasePublicModule
{
	protected function __construct()
	{
		parent::__construct();
	}

	public static function canUseGlobalPageTemplate()
	{
		return !!apply_filters(UltraCommHooks::FILTER_FRONT_END_CAN_USE_GLOBAL_TEMPLATE, self::getInstance()->getOption(FrontPageSettingsAdminModule::USE_GLOBAL_PAGE_TEMPLATE));
	}

	public static function getLoginPageUrl()
	{
		return self::getPageUrl(self::getLoginPageId());
	}

	public static function getRegistrationPageUrl()
	{
		return self::getPageUrl(self::getRegistrationPageId());
	}

	public static function getUserProfilePageUrl()
	{
		return self::getPageUrl(self::getUserProfilePageId());
	}

	public static function getForgotPasswordPageUrl()
	{
		return self::getPageUrl(self::getForgotPasswordPageId());
	}

	public static function getMembersDirectoryPageUrl()
	{
		return self::getPageUrl(self::getMembersDirectoryPageId());
	}

	public static function getGroupsDirectoryPageUrl()
	{
		return self::getPageUrl(self::getGroupsDirectoryPageId());
	}

	private static function getPageUrl($pageId)
	{
		static $arrPageUrl = array();

		if(isset($arrPageUrl[$pageId]))
			return $arrPageUrl[$pageId];

		$pageUrl = get_permalink((int)$pageId);

		return empty($pageUrl) ? null : $arrPageUrl[$pageId] = rtrim(($pageUrl) , '/\\' ) . '/';
	}


	public static function getLoginPageId()
	{
		return (int)self::getInstance()->getOption(FrontPageSettingsAdminModule::LOGIN_PAGE_ID);
	}

	public static function getRegistrationPageId()
	{
		return (int)self::getInstance()->getOption(FrontPageSettingsAdminModule::REGISTER_PAGE_ID);
	}

	public static function getUserProfilePageId()
	{
		return (int)self::getInstance()->getOption(FrontPageSettingsAdminModule::USER_PROFILE_PAGE_ID);
	}

	public static function getForgotPasswordPageId()
	{
		return (int)self::getInstance()->getOption(FrontPageSettingsAdminModule::FORGOT_PASSWORD_PAGE_ID);
	}

	public static function getMembersDirectoryPageId()
	{
		return (int)self::getInstance()->getOption(FrontPageSettingsAdminModule::MEMBERS_DIRECTORY_PAGE_ID);
	}

	public static function getGroupsDirectoryPageId()
	{
		return (int)self::getInstance()->getOption(FrontPageSettingsAdminModule::GROUPS_DIRECTORY_PAGE_ID);
	}

	public static function getUserProfilePageSlug()
	{
		if(null === ($wpPost = WpPostRepository::findByPostId(self::getUserProfilePageId())))
			return null;

		return $wpPost->post_name;

	}

	public static function getMembersDirectoryPageSlug()
	{
		if(null === ($wpPost = WpPostRepository::findByPostId(self::getMembersDirectoryPageId())))
			return null;

		return $wpPost->post_name;

	}

	public static function getGroupsDirectoryPageSlug()
	{
		if(null === ($wpPost = WpPostRepository::findByPostId(self::getGroupsDirectoryPageId())))
			return null;

		return $wpPost->post_name;

	}

}