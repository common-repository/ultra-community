<?php
/**
 * Copyright (c) 2016 Ultra Community (http://www.ultracommunity.com)
 */

namespace UltraCommunity;

use UltraCommunity\Controllers\GroupController;
use UltraCommunity\Controllers\ModulesController;
use UltraCommunity\Controllers\PluginVersionController;
use UltraCommunity\Controllers\UserController;
use UltraCommunity\MchLib\Utils\MchWpUtils;

final class AjaxHandler
{
	CONST REQUEST_NONCE_KEY = 'ajaxRequestNonce';

	public static function handleRequest()
	{

		MchWpUtils::addActionHook('init', function () {


			if (!AjaxHandler::isAjaxRequestValid())
			{

				if ( !empty( $_POST['action'] ) &&  $_POST['action'] === 'update-plugin' ) {
					MchWpUtils::addActionHook('admin_init', function (){PluginVersionController::checkExtensionsUpdates();});
				}

				return;
			}

			empty($_POST) ?: $_POST = stripslashes_deep( $_POST );

			empty($_POST['profiledUserSlug']) ?: UserController::setProfiledUserSlug($_POST['profiledUserSlug']);
			empty($_POST['profiledGroupId'])  ?: GroupController::setProfiledGroup($_POST['profiledGroupId']);

			$actionName = MchWpUtils::sanitizeText($_POST['action']);

			$arrAjaxActions = array( // 0 - no private, 1 - user logged in , PHP_INT_MAX - admin logged in
				'getAllFormFieldsType'   => PHP_INT_MAX,
				'getProfileHeaderFields' => PHP_INT_MAX,
				'getFontAwesomeIcons'    => PHP_INT_MAX,
				'getFieldTypeSettings'   => PHP_INT_MAX,
				'getFormFieldSettings'   => PHP_INT_MAX,

				'getAllAvailableFormFieldsType' => PHP_INT_MAX,
				'saveFormFieldsOrder'           => PHP_INT_MAX,
				'saveProfileHeaderFieldsOrder'  => PHP_INT_MAX,
				'deleteFormField'               => PHP_INT_MAX,
				'deleteProfileHeaderField'      => PHP_INT_MAX,
				'saveFormFieldSettings'         => PHP_INT_MAX,
				'saveHeaderProfileField'       => PHP_INT_MAX,

				'addNewForm'           => PHP_INT_MAX,
				'deleteForm'           => PHP_INT_MAX,
				'addNewUserRole'       => PHP_INT_MAX,
				'deleteUserRole'       => PHP_INT_MAX,


				'addNewSocialConnectConfig' => PHP_INT_MAX,
				'deleteSocialConnectConfig' => PHP_INT_MAX,


				'addNewMembersDirectory' => PHP_INT_MAX,
				'deleteMembersDirectory' => PHP_INT_MAX,

				'addNewCustomTab' => PHP_INT_MAX,
				'deleteCustomTab' => PHP_INT_MAX,

				'loadSubscriptionLevelForm'   => PHP_INT_MAX,
				'saveUserSubscriptionLevel'   => PHP_INT_MAX,
				'deleteUserSubscriptionLevel' => PHP_INT_MAX,
				'saveSubscriptionAccessLevel' => PHP_INT_MAX,
				'loadRestrictionRuleForm'     => PHP_INT_MAX,
				'deleteRestrictionRule'       => PHP_INT_MAX,
				'searchPostTypeToRestrict'    => PHP_INT_MAX,
				'searchTaxonomyToRestrict'    => PHP_INT_MAX,
				'saveRestrictionRule'         => PHP_INT_MAX,

				'saveUserProfilePicture'    => 1,
				'saveUserProfileCover'      => 1,
				'updateUserProfile'         => 1,
				'saveUserReview'            => 1,
				'deleteUserAccount'         => 1,
				'updateUserAccountSettings' => 1,
				'updateUserPassword'        => 1,
				'updateUserAccountPrivacy'  => 1,

				//'deleteUserCover'         => 1,

				'saveUserGroup'         => 1,
				'deleteUserGroup'       => 1,
				'userJoinGroup'         => 1,
				'userLeaveGroup'        => 1,
				'changeGroupUserStatus' => 1,
				'saveGroupCoverPicture' => 1,
				'saveGroupPicture'      => 1,

				'uploadTemporaryFile'   => 1,

				'saveActivityPost'         => 1,
				'saveActivityComment'      => 1,
				'saveActivityLike'         => 1,
				'deleteUserActivity'       => 1,
//				'saveUserPostSubmission'   => 1,
//				'deleteUserPostSubmission' => 1,

				'getUserProfileActivityPerPage' => 0,

				'userForgotPassword'              => 0,
				'userRegistration'                => 0,
				'authenticateUser'                => 0,
				'getLoggedInUserInfo'             => 0,
				'getUserPostsPerPage'             => 0,
				'getUserCommentsPerPage'          => 0,
				
				'getDirectoryListingPerPage'      => 0,


				'addUserFollow'         => 1,
				'addUserFriendRequest'  => 1,
				'userUnFollow'          => 1,
				'userUnFriend'          => 1,
				'userDeclineFriendship' => 1,
				'userAcceptFriendship'  => 1,
			);


			$arrAjaxActions = \apply_filters(UltraCommHooks::FILTER_AJAX_REGISTERED_ACTIONS, $arrAjaxActions);


			if (!isset($arrAjaxActions[$actionName])) {
				return;
			}

			!ob_get_level() ?: ob_end_clean();

			//$_POST = stripslashes_deep($_POST);
			if (PHP_INT_MAX === $arrAjaxActions[$actionName])
			{
				if (MchWpUtils::isAdminLoggedIn())
				{
					foreach ( \apply_filters(UltraCommHooks::FILTER_AJAX_REGISTERED_CLASS_NAMES, array(AjaxAdminEngine::class), $actionName) as $fullyQualifiedClassName)
					{
						if(!\method_exists($fullyQualifiedClassName, $actionName))
							continue;

						\add_action('wp_ajax_' . $actionName, array($fullyQualifiedClassName, $actionName));
					}
				}

				return;
			}

			if (1 === $arrAjaxActions[$actionName] && !MchWpUtils::isUserLoggedIn()) {
				return;
			}

			if (!\WP_DEBUG || (\WP_DEBUG && !\WP_DEBUG_DISPLAY)) {
				@ini_set('display_errors', 0);
			}

			!isset($GLOBALS['wpdb']) ?: $GLOBALS['wpdb']->hide_errors();

			send_origin_headers();
			@header('Content-Type: text/html; charset=' . get_option('blog_charset'));
			@header('X-Robots-Tag: noindex');
			send_nosniff_header();
			nocache_headers();
			status_header(200);
			unset($arrAjaxActions);

			foreach ( \apply_filters(UltraCommHooks::FILTER_AJAX_REGISTERED_CLASS_NAMES, array(AjaxPublicEngine::FULLY_QUALIFIED_CLASS_NAME), $actionName) as $fullyQualifiedClassName)
			{
				if(!\method_exists($fullyQualifiedClassName, $actionName))
					continue;

				\call_user_func(array($fullyQualifiedClassName, $actionName));

				exit;
			}

		}, 0);

	}


	public static function getAjaxNonce()
	{
		return \wp_create_nonce(__CLASS__);
	}

	public static function getAjaxUrl()
	{
		return \admin_url( 'admin-ajax.php', 'relative' );

		//return esc_url(MchWpUtils::getAjaxUrl());
	}

	public static function isAjaxRequestValid()
	{
		if(empty($_POST['action']) || !MchWpUtils::isAjaxRequest())
			return false;

		$isValidRequest = \check_ajax_referer(__CLASS__, self::REQUEST_NONCE_KEY, false) || ('getLoggedInUserInfo' ===  $_POST['action']);

		return $isValidRequest;
	}

	private function __construct(){
	}
	private function __clone(){
	}
	private function __wakeup(){
	}
}