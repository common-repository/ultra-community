<?php
use UltraCommunity\Controllers\FrontPageController;
use UltraCommunity\Controllers\GroupController;
use UltraCommunity\Controllers\UserController;
use UltraCommunity\Entities\GroupEntity;
use UltraCommunity\Entities\UserEntity;
use UltraCommunity\MchLib\Utils\MchWpUtils;
use UltraCommunity\MchLib\WordPress\Repository\WpPostRepository;
use UltraCommunity\UltraCommHelper;
use UltraCommunity\UltraCommHooks;
use UltraCommunity\UltraCommUtils;

############################ Front Page Functions ##################################
function uc_get_logout_url()
{
	return FrontPageController::getLogOutPageUrl();
}

############################ User Functions ##################################

function uc_get_user_profile_url($userKey)
{
	return UltraCommHelper::getUserProfileUrl($userKey);
}

function uc_get_user_avatar_url($userKey, $size = 150)
{
	return UltraCommHelper::getUserAvatarUrl($userKey, $size);
}

function uc_get_user_display_name($userKey)
{
	return UltraCommHelper::getUserDisplayName(UserController::getUserEntityBy($userKey));
}

############################ Groups Functions ##################################

/**
 * @param int| WP_User | UserEntity| $user_key
 */
function uc_get_user_created_groups($userKey, $pageNumber = 1, $postsPerPage = 12)
{
	return GroupController::getUserCreatedGroups($userKey, $pageNumber, $postsPerPage);
}

/**
 * @param $groupKey
 *
 * @return null|GroupEntity
 */
function uc_get_group_by($groupKey)
{
	return GroupController::getGroupEntityBy($groupKey);
}

function uc_get_all_group_types()
{
	return GroupEntity::getAllGroupTypes();
}

function uc_get_group_privacy_activity_posting_types()
{
	return GroupEntity::getGroupPrivacyActivityPostingTypes();
}

function uc_get_group_privacy_activity_commenting_types()
{
	return GroupEntity::getGroupPrivacyActivityCommentingTypes();
}

function uc_user_can_join_group($userKey, $groupKey)
{
	return GroupController::userCanJoinGroup($userKey, $groupKey);
}

function uc_user_can_create_groups($userKey)
{
	return GroupController::userCanCreateGroups($userKey);
}

function uc_count_user_all_groups($userKey)
{
	return GroupController::countUserAllGroups($userKey);
}

function uc_count_user_created_groups($userKey)
{
	return count((array)GroupController::getUserCreatedGroups($userKey, 1, PHP_INT_MAX));
}

############################ User Post Submission Functions ##################################

/**
 * @param int| WP_Post $postKey
 *
 * @return boolean
 */
function uc_is_user_submitted_post($postKey)
{
	return empty($wpPost = get_post($postKey)) ? false : (bool)WpPostRepository::getPostMeta($wpPost->ID, '_is_uc_submission');
}


############################ Utils Functions ##################################

function uc_filter_temp_uploads_directory($arrWpUploadDirInfo)
{
	$arrWpUploadDirInfo['subdir'] = '/ultra-comm/temp/uploads';

	$arrWpUploadDirInfo['path']   = $arrWpUploadDirInfo['basedir'] . $arrWpUploadDirInfo['subdir'];
	$arrWpUploadDirInfo['url']    = $arrWpUploadDirInfo['baseurl'] . $arrWpUploadDirInfo['subdir'];

	return $arrWpUploadDirInfo;
}

function uc_get_pagination_markup($activePageNumber, $totalPages)
{
	return UltraCommUtils::getPaginationOutputContent($activePageNumber, $totalPages);
}

foreach(array('RequestHandler.php', 'PublicEngine.php', 'AdminEngine.php', 'AjaxHandler.php', 'AjaxAdminEngine.php', 'AjaxPublicEngine.php', 'UltraCommHooks.php') as $fileName)
{
	require $fileName;
}

############################ Autoload Class Mapping ##################################
\spl_autoload_register(function($className){

	static $arrClassMap = array(

//		'UltraCommunity\PublicEngine'       => 'PublicEngine.php',
//		'UltraCommunity\AdminEngine'        => 'AdminEngine.php',
//		'UltraCommunity\AjaxAdminEngine'    => 'AjaxAdminEngine.php',
//		'UltraCommunity\AjaxPublicEngine'   => 'AjaxPublicEngine.php',
//		'UltraCommunity\UltraCommHooks'     => 'UltraCommHooks.php',
//		'UltraCommunity\AjaxHandler'        => 'AjaxHandler.php',

		'UltraCommunity\UltraCommHelper'    => 'UltraCommHelper.php',

		'UltraCommunity\UltraCommUtils'           => 'UltraCommUtils.php',
		'UltraCommunity\UltraCommException'       => 'UltraCommException.php',

		'UltraCommunity\Controllers\ModulesController'        => 'Controllers/ModulesController.php',
		'UltraCommunity\Controllers\PostTypeController'       => 'Controllers/PostTypeController.php',
		'UltraCommunity\Controllers\FrontPageController'      => 'Controllers/FrontPageController.php',
		'UltraCommunity\Controllers\ShortCodesController'     => 'Controllers/ShortCodesController.php',
		'UltraCommunity\Controllers\UserController'           => 'Controllers/UserController.php',
		'UltraCommunity\Controllers\GroupController'          => 'Controllers/GroupController.php',
		'UltraCommunity\Controllers\UserRoleController'       => 'Controllers/UserRoleController.php',
		'UltraCommunity\Controllers\ActivityController'       => 'Controllers/ActivityController.php',
		'UltraCommunity\Controllers\TasksController'          => 'Controllers/TasksController.php',
		'UltraCommunity\Controllers\WidgetsController'        => 'Controllers/WidgetsController.php',
		'UltraCommunity\Controllers\UploadsController'        => 'Controllers/UploadsController.php',
		'UltraCommunity\Controllers\TemplatesController'      => 'Controllers/TemplatesController.php',
		'UltraCommunity\Controllers\PluginVersionController'  => 'Controllers/PluginVersionController.php',
		'UltraCommunity\Controllers\UserRelationsController'  => 'Controllers/UserRelationsController.php',
		'UltraCommunity\Controllers\NotificationsController'  => 'Controllers/NotificationsController.php',


		'UltraCommunity\Repository\BaseRepository'        => 'Repository/BaseRepository.php',
		'UltraCommunity\Repository\UserRepository'        => 'Repository/UserRepository.php',
		'UltraCommunity\Repository\GroupRepository'       => 'Repository/GroupRepository.php',
		'UltraCommunity\Repository\ActivityRepository'    => 'Repository/ActivityRepository.php',
		'UltraCommunity\Repository\UserRelationsRepository'    => 'Repository/UserRelationsRepository.php',

		'UltraCommunity\Admin\Pages\BaseAdminPage'             => 'Admin/Pages/BaseAdminPage.php',
		'UltraCommunity\Admin\Pages\UserRoleAdminPage'         => 'Admin/Pages/UserRoleAdminPage.php',
		'UltraCommunity\Admin\Pages\AppearanceAdminPage'       => 'Admin/Pages/AppearanceAdminPage.php',
		'UltraCommunity\Admin\Pages\FormsAdminPage'            => 'Admin/Pages/FormsAdminPage.php',
		'UltraCommunity\Admin\Pages\GeneralSettingsAdminPage'  => 'Admin/Pages/GeneralSettingsAdminPage.php',
		'UltraCommunity\Admin\Pages\MembersDirectoryAdminPage' => 'Admin/Pages/MembersDirectoryAdminPage.php',
		'UltraCommunity\Admin\Pages\DirectoriesAdminPage'      => 'Admin/Pages/DirectoriesAdminPage.php',
		'UltraCommunity\Admin\Pages\ManageUsersAdminPage'      => 'Admin/Pages/ManageUsersAdminPage.php',
		'UltraCommunity\Admin\Pages\SocialConnectAdminPage'    => 'Admin/Pages/SocialConnectAdminPage.php',
		'UltraCommunity\Admin\Pages\ExtensionsAdminPage'       => 'Admin/Pages/ExtensionsAdminPage.php',
		'UltraCommunity\Admin\Pages\AddOnsAdminPage'           => 'Admin/Pages/AddOnsAdminPage.php',
		'UltraCommunity\Admin\Pages\CustomTabsAdminPage'       => 'Admin/Pages/CustomTabsAdminPage.php',
		'UltraCommunity\Admin\Pages\PostSubmissionsAdminPage'  => 'Admin/Pages/PostSubmissionsAdminPage.php',

		'UltraCommunity\PostsType\LoginFormPostType'          => 'PostsType/LoginFormPostType.php',
		'UltraCommunity\PostsType\RegisterFormPostType'       => 'PostsType/RegisterFormPostType.php',
		'UltraCommunity\PostsType\UserRolePostType'           => 'PostsType/UserRolePostType.php',
		'UltraCommunity\PostsType\SocialConnectPostType'      => 'PostsType/SocialConnectPostType.php',
		'UltraCommunity\PostsType\UserProfileFormPostType'    => 'PostsType/UserProfileFormPostType.php',
		'UltraCommunity\PostsType\ForgotPasswordFormPostType' => 'PostsType/ForgotPasswordFormPostType.php',
		'UltraCommunity\PostsType\MembersDirectoryPostType'   => 'PostsType/MembersDirectoryPostType.php',
		'UltraCommunity\PostsType\UserSubscriptionPostType'   => 'PostsType/UserSubscriptionPostType.php',
		'UltraCommunity\PostsType\CustomTabPostType'          => 'PostsType/CustomTabPostType.php',
		'UltraCommunity\PostsType\GroupPostType'              => 'PostsType/GroupPostType.php',
		'UltraCommunity\PostsType\ActivityPostType'           => 'PostsType/ActivityPostType.php',
		'UltraCommunity\PostsType\UserReviewPostType'         => 'PostsType/UserReviewPostType.php',
		'UltraCommunity\PostsType\GroupsDirectoryPostType'    => 'PostsType/GroupsDirectoryPostType.php',


		'UltraCommunity\FrontPages\BasePage'             => 'FrontPages/BasePage.php',
		'UltraCommunity\FrontPages\LoginPage'            => 'FrontPages/LoginPage.php',
		'UltraCommunity\FrontPages\GroupProfilePage'     => 'FrontPages/GroupProfilePage.php',
		'UltraCommunity\FrontPages\RegisterPage'         => 'FrontPages/RegisterPage.php',
		'UltraCommunity\FrontPages\UserProfilePage'      => 'FrontPages/UserProfilePage.php',
		'UltraCommunity\FrontPages\UserSettingsPage'     => 'FrontPages/UserSettingsPage.php',
		'UltraCommunity\FrontPages\ForgotPasswordPage'   => 'FrontPages/ForgotPasswordPage.php',
		'UltraCommunity\FrontPages\MembersDirectoryPage' => 'FrontPages/MembersDirectoryPage.php',
		'UltraCommunity\FrontPages\GroupsDirectoryPage'  => 'FrontPages/GroupsDirectoryPage.php',
		'UltraCommunity\FrontPages\ActivityPage'         => 'FrontPages/ActivityPage.php',

		'UltraCommunity\Entities\UserEntity'         => 'Entities/UserEntity.php',
		'UltraCommunity\Entities\GroupEntity'        => 'Entities/GroupEntity.php',
		'UltraCommunity\Entities\GroupUserEntity'    => 'Entities/GroupUserEntity.php',
		'UltraCommunity\Entities\EmailEntity'        => 'Entities/EmailEntity.php',
		'UltraCommunity\Entities\UserMetaEntity'     => 'Entities/UserMetaEntity.php',
		'UltraCommunity\Entities\ActivityEntity'     => 'Entities/ActivityEntity.php',
		'UltraCommunity\Entities\UserRelationEntity' => 'Entities/UserRelationEntity.php',
		'UltraCommunity\Entities\PageActionEntity'   => 'Entities/PageActionEntity.php',
		'UltraCommunity\Entities\UserReviewEntity'   => 'Entities/UserReviewEntity.php',
		'UltraCommunity\Entities\UserPrivacyEntity'  => 'Entities/UserPrivacyEntity.php',

		//'UltraCommunity\Tasks\UserRolesMapperTask' => 'Tasks/UserRolesMapperTask.php',

	);


	if (!isset($arrClassMap[$className]))
		return null;

	$filePath = __DIR__ . \DIRECTORY_SEPARATOR . $arrClassMap[$className];
	unset($arrClassMap[$className]);

	return \file_exists($filePath) ? require $filePath : null;


}, false, true);


############################################## Polyfills ##########################

if( \PHP_VERSION_ID < 70300 )
{
	if(!\function_exists('array_key_first'))
	{
		function array_key_first(array $array) {
			foreach($array as $key => $unused)
				return $key; return null;
		}

	}

	if(!\function_exists('array_key_last'))
	{
		function array_key_last( array $array ) {
			\end( $array ); return \key( $array );
		}
	}
}

if ( isset($GLOBALS['wp_version']) && \version_compare( $GLOBALS['wp_version'], '5.3', '<' ) )
{
	if(!\function_exists('wp_timezone_string'))
	{
		function wp_timezone_string() {
			$timezone_string = get_option( 'timezone_string' );

			if ( $timezone_string ) {
				return $timezone_string;
			}

			$offset  = (float) get_option( 'gmt_offset' );
			$hours   = (int) $offset;
			$minutes = ( $offset - $hours );

			$sign      = ( $offset < 0 ) ? '-' : '+';
			$abs_hour  = abs( $hours );
			$abs_mins  = abs( $minutes * 60 );
			$tz_offset = sprintf( '%s%02d:%02d', $sign, $abs_hour, $abs_mins );

			return $tz_offset;
		}
	}

	if(!\function_exists('wp_timezone'))
	{
		function wp_timezone() {
			return new \DateTimeZone( wp_timezone_string() );
		}
	}

}




