<?php
/**
 * Copyright (c) 2016 Ultra Community (http://www.ultracommunity.com)
 */

namespace UltraCommunity\Controllers;
use UltraCommunity\MchLib\Modules\MchModulesController;
use UltraCommunity\MchLib\Plugin\MchBasePlugin;

final class ModulesController extends MchModulesController
{
	CONST MODULE_USER_ROLE    = 'UserRole';


	CONST MODULE_LOGIN_FORM           = 'LoginForm';
	CONST MODULE_REGISTER_FORM        = 'RegisterForm';
	CONST MODULE_USER_PROFILE_FORM    = 'UserProfileForm';
	CONST MODULE_FORGOT_PASSWORD_FORM = 'ForgotPasswordForm';

	CONST MODULE_APPEARANCE_GENERAL       = 'GeneralAppearance';
	CONST MODULE_APPEARANCE_DIRECTORIES   = 'DirectoriesAppearance';
	CONST MODULE_APPEARANCE_USER_PROFILE  = 'UserProfileAppearance';
	CONST MODULE_APPEARANCE_GROUP_PROFILE = 'GroupProfileAppearance';

	CONST MODULE_FRONT_PAGE_SETTINGS = 'PageSettings';
	CONST MODULE_USER_SETTINGS       = 'UserSettings';

	CONST MODULE_PLUGIN_SETTINGS     = 'PluginSettings';

	CONST MODULE_LICENSES       = 'Licenses';

	CONST MODULE_EMAILS_SETTINGS   = 'EmailsSettings';
	CONST MODULE_MEMBERS_DIRECTORY = 'MembersDirectory';

	CONST MODULE_GROUPS_DIRECTORY = 'GroupsDirectory';

	CONST MODULE_EXTENDED_ACTIVITY  = 'ExtendedActivity';

	CONST MODULE_USER_FRIENDS       = 'UserFriends';
	CONST MODULE_USER_FOLLOWERS     = 'UserFollowers';
	CONST MODULE_USER_REVIEWS       = 'UserReviews';
	CONST MODULE_BBPRESS            = 'BBPress';
	CONST MODULE_CUSTOM_TABS        = 'CustomTabs';
	CONST MODULE_WOOCOMMERCE        = 'WooCommerce';
	CONST MODULE_SOCIAL_CONNECT     = 'SocialConnect';
	CONST MODULE_SOCIAL_SHARE       = 'SocialShare';

	CONST MODULE_POST_SUBMISSIONS   = 'PostSubmissions';
	
	CONST MODULE_USER_LETTER_AVATAR = 'UserLetterAvatar';
	CONST MODULE_USER_SUBSCRIPTIONS = 'UserSubscriptions';
	CONST MODULE_USER_NOTIFICATIONS = 'UserNotifications';
	CONST MODULE_USER_SUBSCRIPTIONS_PAYMENT_GATEWAYS = 'UserSubscriptionsPaymentGateways';
	CONST MODULE_USER_SUBSCRIPTIONS_SUBSCRIPTION_LEVELS = 'UserSubscriptionsSubscriptionLevels';
	CONST MODULE_USER_SUBSCRIPTIONS_RESTRICTION_RULES = 'UserRestrictionRules';

	protected static function getAllAvailableModules()
	{
		return array(

			self::MODULE_USER_ROLE => array(
				'info'    => array(
					'DisplayName' => __('User Role', 'ultra-community'),
				),
				'classes' => array(
					'UltraCommunity\Modules\UserRole\UserRolePublicModule' => 'Modules/UserRole/UserRolePublicModule.php',
					'UltraCommunity\Modules\UserRole\UserRoleAdminModule'  => 'Modules/UserRole/UserRoleAdminModule.php',
				),
			),

			self::MODULE_APPEARANCE_GENERAL => array(
				'info'    => array(
					'DisplayName' => __('General', 'ultra-community'),
				),
				'classes' => array(
					'UltraCommunity\Modules\Appearance\General\GeneralAppearancePublicModule' => 'Modules/Appearance/General/GeneralAppearancePublicModule.php',
					'UltraCommunity\Modules\Appearance\General\GeneralAppearanceAdminModule'  => 'Modules/Appearance/General/GeneralAppearanceAdminModule.php',
				),
			),

			self::MODULE_APPEARANCE_DIRECTORIES => array(
				'info'    => array(
					'DisplayName' => __('Directories', 'ultra-community'),
				),
				'classes' => array(
					'UltraCommunity\Modules\Appearance\Directories\DirectoriesAppearancePublicModule' => 'Modules/Appearance/Directories/DirectoriesAppearancePublicModule.php',
					'UltraCommunity\Modules\Appearance\Directories\DirectoriesAppearanceAdminModule'  => 'Modules/Appearance/Directories/DirectoriesAppearanceAdminModule.php',
				),
			),


			self::MODULE_APPEARANCE_USER_PROFILE => array(
				'info'    => array(
					'DisplayName' => __('User Profile', 'ultra-community'),
				),
				'classes' => array(
					'UltraCommunity\Modules\Appearance\UserProfile\UserProfileAppearancePublicModule' => 'Modules/Appearance/UserProfile/UserProfileAppearancePublicModule.php',
					'UltraCommunity\Modules\Appearance\UserProfile\UserProfileAppearanceAdminModule' => 'Modules/Appearance/UserProfile/UserProfileAppearanceAdminModule.php',
				),
			),

			self::MODULE_APPEARANCE_GROUP_PROFILE => array(
				'info'    => array(
					'DisplayName' => __('Group Profile', 'ultra-community'),
				),
				'classes' => array(
					'UltraCommunity\Modules\Appearance\GroupProfile\GroupProfileAppearancePublicModule' => 'Modules/Appearance/GroupProfile/GroupProfileAppearancePublicModule.php',
					'UltraCommunity\Modules\Appearance\GroupProfile\GroupProfileAppearanceAdminModule'  => 'Modules/Appearance/GroupProfile/GroupProfileAppearanceAdminModule.php',
				),
			),

			self::MODULE_LOGIN_FORM => array(
				'info'    => array(
					'DisplayName' => __('Login Form', 'ultra-community'),
				),
				'classes' => array(
					'UltraCommunity\Modules\Forms\LoginForm\LoginFormPublicModule' => 'Modules/Forms/LoginForm/LoginFormPublicModule.php',
					'UltraCommunity\Modules\Forms\LoginForm\LoginFormAdminModule' => 'Modules/Forms/LoginForm/LoginFormAdminModule.php',
				),
			),

			self::MODULE_REGISTER_FORM => array(
				'info'    => array(
					'DisplayName' => __('Register Form', 'ultra-community'),
				),
				'classes' => array(
					'UltraCommunity\Modules\Forms\RegisterForm\RegisterFormPublicModule' => 'Modules/Forms/RegisterForm/RegisterFormPublicModule.php',
					'UltraCommunity\Modules\Forms\RegisterForm\RegisterFormAdminModule' => 'Modules/Forms/RegisterForm/RegisterFormAdminModule.php',
				),
			),

			self::MODULE_USER_PROFILE_FORM => array(
					'info'    => array(
							'DisplayName' => __('UserProfile Form', 'ultra-community'),
					),
					'classes' => array(
							'UltraCommunity\Modules\Forms\UserProfileForm\UserProfileFormPublicModule' => 'Modules/Forms/UserProfileForm/UserProfileFormPublicModule.php',
							'UltraCommunity\Modules\Forms\UserProfileForm\UserProfileFormAdminModule' => 'Modules/Forms/UserProfileForm/UserProfileFormAdminModule.php',
					),
			),

			self::MODULE_FORGOT_PASSWORD_FORM => array(
					'info'    => array(
							'DisplayName' => __('ForgotPassword Form', 'ultra-community'),
					),
					'classes' => array(
							'UltraCommunity\Modules\Forms\ForgotPasswordForm\ForgotPasswordFormPublicModule' => 'Modules/Forms/ForgotPasswordForm/ForgotPasswordFormPublicModule.php',
							'UltraCommunity\Modules\Forms\ForgotPasswordForm\ForgotPasswordFormAdminModule' => 'Modules/Forms/ForgotPasswordForm/ForgotPasswordFormAdminModule.php',
					),
			),

			self::MODULE_FRONT_PAGE_SETTINGS => array(
				'info'    => array(
					'DisplayName' => __('FrontPage Settings', 'ultra-community'),
				),
				'classes' => array(
					'UltraCommunity\Modules\GeneralSettings\FrontPage\FrontPageSettingsPublicModule' => 'Modules/GeneralSettings/FrontPage/FrontPageSettingsPublicModule.php',
					'UltraCommunity\Modules\GeneralSettings\FrontPage\FrontPageSettingsAdminModule'  => 'Modules/GeneralSettings/FrontPage/FrontPageSettingsAdminModule.php',
				),
			),

			self::MODULE_USER_SETTINGS => array(
				'info'    => array(
					'DisplayName' => __('Users Settings', 'ultra-community'),
				),
				'classes' => array(
					'UltraCommunity\Modules\GeneralSettings\User\UserSettingsPublicModule' => 'Modules/GeneralSettings/User/UserSettingsPublicModule.php',
					'UltraCommunity\Modules\GeneralSettings\User\UserSettingsAdminModule'  => 'Modules/GeneralSettings/User/UserSettingsAdminModule.php',
				),
			),

			self::MODULE_LICENSES => array(
					'info'    => array(
							'DisplayName' => __('Licenses', 'ultra-community'),
					),
					'classes' => array(
							'UltraCommunity\Modules\GeneralSettings\Licenses\LicensesPublicModule' => 'Modules/GeneralSettings/Licenses/LicensesPublicModule.php',
							'UltraCommunity\Modules\GeneralSettings\Licenses\LicensesAdminModule'  => 'Modules/GeneralSettings/Licenses/LicensesAdminModule.php',
					),
			),

			self::MODULE_PLUGIN_SETTINGS => array(
				'info'    => array(
					'DisplayName' => __('Plugin Settings', 'ultra-community'),
				),
				'classes' => array(
					'UltraCommunity\Modules\GeneralSettings\Plugin\PluginSettingsPublicModule' => 'Modules/GeneralSettings/Plugin/PluginSettingsPublicModule.php',
					'UltraCommunity\Modules\GeneralSettings\Plugin\PluginSettingsAdminModule'  => 'Modules/GeneralSettings/Plugin/PluginSettingsAdminModule.php',
				),
			),

			self::MODULE_EMAILS_SETTINGS => array(
					'info'    => array(
							'DisplayName' => __('Emails', 'ultra-community'),
					),
					'classes' => array(
							'UltraCommunity\Modules\GeneralSettings\Emails\EmailsSettingsPublicModule' => 'Modules/GeneralSettings/Emails/EmailsSettingsPublicModule.php',
							'UltraCommunity\Modules\GeneralSettings\Emails\EmailsSettingsAdminModule'  => 'Modules/GeneralSettings/Emails/EmailsSettingsAdminModule.php',
					),
			),


			self::MODULE_MEMBERS_DIRECTORY => array(
				'info'    => array(
					'DisplayName' => __('Members Directory', 'ultra-community'),
				),
				'classes' => array(
					'UltraCommunity\Modules\MembersDirectory\MembersDirectoryPublicModule' => 'Modules/MembersDirectory/MembersDirectoryPublicModule.php',
					'UltraCommunity\Modules\MembersDirectory\MembersDirectoryAdminModule'  => 'Modules/MembersDirectory/MembersDirectoryAdminModule.php',
				),
			),


			self::MODULE_GROUPS_DIRECTORY => array(
				'info'    => array(
					'DisplayName' => __('Groups Directory', 'ultra-community'),
				),
				'classes' => array(
					'UltraCommunity\Modules\GroupsDirectory\GroupsDirectoryPublicModule' => 'Modules/GroupsDirectory/GroupsDirectoryPublicModule.php',
					'UltraCommunity\Modules\GroupsDirectory\GroupsDirectoryAdminModule'  => 'Modules/GroupsDirectory/GroupsDirectoryAdminModule.php',
				),
			),


			self::MODULE_SOCIAL_CONNECT => array(
				'info'    => array(
					'DisplayName' => __('Social Connect', 'ultra-community'),
					'IsLicensed'  => true, 'ModuleId' => 11621
				),
				'classes' => array(
					'UltraCommunity\Modules\SocialConnect\SocialConnectPublicModule' => 'Modules/SocialConnect/SocialConnectPublicModule.php',
					'UltraCommunity\Modules\SocialConnect\SocialConnectAdminModule'  => 'Modules/SocialConnect/SocialConnectAdminModule.php',
				),
			),

			self::MODULE_EXTENDED_ACTIVITY => array(
					'info'    => array(
							'DisplayName' => __('Extended Activity', 'ultra-community'),
							'IsLicensed'  => true, 'ModuleId' => 11541
					),
					'classes' => array(
							'UltraCommunity\Modules\ExtendedActivity\ExtendedActivityPublicModule' => 'Modules/ExtendedActivity/ExtendedActivityPublicModule.php',
							'UltraCommunity\Modules\ExtendedActivity\ExtendedActivityAdminModule'  => 'Modules/ExtendedActivity/ExtendedActivityAdminModule.php',
					),
			),


			self::MODULE_USER_FOLLOWERS => array(
				'info'    => array(
					'DisplayName' => __('User Followers', 'ultra-community'),
					'IsLicensed'  => true, 'ModuleId' => 11631
				),
				'classes' => array(
					'UltraCommunity\Modules\UserFollowers\UserFollowersPublicModule' => 'Modules/UserFollowers/UserFollowersPublicModule.php',
					'UltraCommunity\Modules\UserFollowers\UserFollowersAdminModule'  => 'Modules/UserFollowers/UserFollowersAdminModule.php',
				),
			),

			self::MODULE_USER_FRIENDS => array(
				'info'    => array(
					'DisplayName' => __('User Friends', 'ultra-community'),
					'IsLicensed'  => true, 'ModuleId' => 11626
				),
				'classes' => array(
					'UltraCommunity\Modules\UserFriends\UserFriendsPublicModule' => 'Modules/UserFriends/UserFriendsPublicModule.php',
					'UltraCommunity\Modules\UserFriends\UserFriendsAdminModule'  => 'Modules/UserFriends/UserFriendsAdminModule.php',
				),
			),

			self::MODULE_USER_REVIEWS => array(
				'info'    => array(
					'DisplayName' => __('User Reviews', 'ultra-community'),
					'IsLicensed'  => true, 'ModuleId' => 11629
				),
				'classes' => array(
					'UltraCommunity\Modules\UserReviews\UserReviewsPublicModule' => 'Modules/UserReviews/UserReviewsPublicModule.php',
					'UltraCommunity\Modules\UserReviews\UserReviewsAdminModule'  => 'Modules/UserReviews/UserReviewsAdminModule.php',
				),
			),

			self::MODULE_BBPRESS => array(
					'info'    => array(
							'DisplayName' => __('bbPress', 'ultra-community'),
							'IsLicensed'  => true, 'ModuleId' => 11524
					),
					'classes' => array(
							'UltraCommunity\Modules\BBPress\BBPressPublicModule' => 'Modules/BBPress/BBPressPublicModule.php',
							'UltraCommunity\Modules\BBPress\BBPressAdminModule'  => 'Modules/BBPress/BBPressAdminModule.php',
					),
			),

			self::MODULE_CUSTOM_TABS => array(
					'info'    => array(
							'DisplayName' => __('Custom Tabs', 'ultra-community'),
							'IsLicensed'  => true, 'ModuleId' => 11710
					),
					'classes' => array(
							'UltraCommunity\Modules\CustomTabs\CustomTabsPublicModule' => 'Modules/CustomTabs/CustomTabsPublicModule.php',
							'UltraCommunity\Modules\CustomTabs\CustomTabsAdminModule'  => 'Modules/CustomTabs/CustomTabsAdminModule.php',
					),
			),

			self::MODULE_SOCIAL_SHARE => array(
					'info'    => array(
							'DisplayName' => __('Social Share', 'ultra-community'),
							'IsLicensed'  => true, 'ModuleId' => 11828
					),
					'classes' => array(
							'UltraCommunity\Modules\SocialShare\SocialSharePublicModule' => 'Modules/SocialShare/SocialSharePublicModule.php',
							'UltraCommunity\Modules\SocialShare\SocialShareAdminModule'  => 'Modules/SocialShare/SocialShareAdminModule.php',
					),
			),

			self::MODULE_POST_SUBMISSIONS => array(
				'info'    => array(
					'DisplayName' => __('Post Submissions', 'ultra-community'),
					'IsLicensed'  => true, 'ModuleId' => 11885
				),
				'classes' => array(
					'UltraCommunity\Modules\PostSubmissions\PostSubmissionsPublicModule' => 'Modules/PostSubmissions/PostSubmissionsPublicModule.php',
					'UltraCommunity\Modules\PostSubmissions\PostSubmissionsAdminModule'  => 'Modules/PostSubmissions/PostSubmissionsAdminModule.php',
				),
			),
			
			self::MODULE_USER_LETTER_AVATAR=> array(
				'info'    => array(
					'DisplayName' => __('Letter Avatar', 'ultra-community'),
					'IsLicensed'  => true, 'ModuleId' => 13875
				),
				'classes' => array(
					'UltraCommunity\Modules\UserLetterAvatar\UserLetterAvatarPublicModule' => 'Modules/UserLetterAvatar/UserLetterAvatarPublicModule.php',
					'UltraCommunity\Modules\UserLetterAvatar\UserLetterAvatarAdminModule'  => 'Modules/UserLetterAvatar/UserLetterAvatarAdminModule.php',
				),
			),
			
			self::MODULE_USER_NOTIFICATIONS=> array(
				'info'    => array(
					'DisplayName' => __('User Notifications', 'ultra-community'),
					'IsLicensed'  => true, 'ModuleId' => 13931
				),
				'classes' => array(
					'UltraCommunity\Modules\UserNotifications\UserNotificationsPublicModule' => 'Modules/UserNotifications/UserNotificationsPublicModule.php',
					'UltraCommunity\Modules\UserNotifications\UserNotificationsAdminModule'  => 'Modules/UserNotifications/UserNotificationsAdminModule.php',
				),
			),
			
			self::MODULE_USER_SUBSCRIPTIONS => array(
					'info'    => array(
							'DisplayName' => __('Subscriptions', 'ultra-community'),
							'IsLicensed'  => true, 'ModuleId' => 0
					),
					'classes' => array(
							'UltraCommunity\Modules\UserSubscriptions\UserSubscriptionsPublicModule' => 'Modules/UserSubscriptions/UserSubscriptionsPublicModule.php',
							'UltraCommunity\Modules\UserSubscriptions\UserSubscriptionsAdminModule'  => 'Modules/UserSubscriptions/UserSubscriptionsAdminModule.php',
					),
			),

			self::MODULE_USER_SUBSCRIPTIONS_PAYMENT_GATEWAYS => array(
					'info'    => array(
							'DisplayName' => __('Payment Gateways', 'ultra-community'),
					),
					'classes' => array(
							'UltraCommunity\Modules\UserSubscriptions\SubModules\PaymentGateways\PaymentGatewaysAdminModule' => 'Modules/UserSubscriptions/SubModules/PaymentGateways/PaymentGatewaysAdminModule.php',
							'UltraCommunity\Modules\UserSubscriptions\SubModules\PaymentGateways\PaymentGatewaysPublicModule' => 'Modules/UserSubscriptions/SubModules/PaymentGateways/PaymentGatewaysPublicModule.php',
					),
			),

			self::MODULE_USER_SUBSCRIPTIONS_SUBSCRIPTION_LEVELS => array(
					'info'    => array(
							'DisplayName' => __('Subscription Levels', 'ultra-community'),
					),
					'classes' => array(
							'UltraCommunity\Modules\UserSubscriptions\SubModules\SubscriptionLevels\SubscriptionLevelsAdminModule' => 'Modules/UserSubscriptions/SubModules/SubscriptionLevels/SubscriptionLevelsAdminModule.php',
							'UltraCommunity\Modules\UserSubscriptions\SubModules\SubscriptionLevels\SubscriptionLevelsPublicModule' => 'Modules/UserSubscriptions/SubModules/SubscriptionLevels/SubscriptionLevelsPublicModule.php',
					),
			),

			self::MODULE_USER_SUBSCRIPTIONS_RESTRICTION_RULES => array(
					'info'    => array(
							'DisplayName' => __('Restriction Rules', 'ultra-community'),
					),
					'classes' => array(
							'UltraCommunity\Modules\UserSubscriptions\SubModules\RestrictionRules\RestrictionRulesAdminModule' => 'Modules/UserSubscriptions/SubModules/RestrictionRules/RestrictionRulesAdminModule.php',
							'UltraCommunity\Modules\UserSubscriptions\SubModules\RestrictionRules\RestrictionRulesPublicModule' => 'Modules/UserSubscriptions/SubModules/RestrictionRules/RestrictionRulesPublicModule.php',
					),
			),

		);
	}

}

\spl_autoload_register(function($className){

	static $arrClassMap = array(

		'UltraCommunity\Modules\BaseAdminModule'  => 'Modules/BaseAdminModule.php',
		'UltraCommunity\Modules\BasePublicModule' => 'Modules/BasePublicModule.php',

		'UltraCommunity\Modules\Forms\BaseForm\BaseFormAdminModule'  => 'Modules/Forms/BaseForm/BaseFormAdminModule.php',
		'UltraCommunity\Modules\Forms\BaseForm\BaseFormPublicModule' => 'Modules/Forms/BaseForm/BaseFormPublicModule.php',

		'UltraCommunity\Modules\Forms\FormFields\BaseField'                 => 'Modules/Forms/FormFields/BaseField.php',
		'UltraCommunity\Modules\Forms\FormFields\UserNameField'             => 'Modules/Forms/FormFields/UserNameField.php',
		'UltraCommunity\Modules\Forms\FormFields\UserEmailField'            => 'Modules/Forms/FormFields/UserEmailField.php',
		'UltraCommunity\Modules\Forms\FormFields\UserNameOrEmailField'      => 'Modules/Forms/FormFields/UserNameOrEmailField.php',
		'UltraCommunity\Modules\Forms\FormFields\UserFirstNameField'        => 'Modules/Forms/FormFields/UserFirstNameField.php',
		'UltraCommunity\Modules\Forms\FormFields\UserLastNameField'         => 'Modules/Forms/FormFields/UserLastNameField.php',
		'UltraCommunity\Modules\Forms\FormFields\UserFullNameField'         => 'Modules/Forms/FormFields/UserFullNameField.php',
		'UltraCommunity\Modules\Forms\FormFields\UserPasswordField'         => 'Modules/Forms/FormFields/UserPasswordField.php',
		'UltraCommunity\Modules\Forms\FormFields\UserDisplayNameField'      => 'Modules/Forms/FormFields/UserDisplayNameField.php',
		'UltraCommunity\Modules\Forms\FormFields\UserNickNameField'         => 'Modules/Forms/FormFields/UserNickNameField.php',
		'UltraCommunity\Modules\Forms\FormFields\UserBioField'              => 'Modules/Forms/FormFields/UserBioField.php',
		'UltraCommunity\Modules\Forms\FormFields\UserWebUrlField'           => 'Modules/Forms/FormFields/UserWebUrlField.php',
		'UltraCommunity\Modules\Forms\FormFields\UserRegistrationDateField' => 'Modules/Forms/FormFields/UserRegistrationDateField.php',

		'UltraCommunity\Modules\Forms\FormFields\SubscriptionLevelsField' => 'Modules/Forms/FormFields/SubscriptionLevelsField.php',
		//'UltraCommunity\Modules\Forms\FormFields\SubscriptionLevelField'  => 'Modules/Forms/FormFields/SubscriptionLevelField.php',

		'UltraCommunity\Modules\Forms\FormFields\UserGenderDropDownField' => 'Modules/Forms/FormFields/UserGenderDropDownField.php',
		'UltraCommunity\Modules\Forms\FormFields\UserGenderRadioField'    => 'Modules/Forms/FormFields/UserGenderRadioField.php',
		'UltraCommunity\Modules\Forms\FormFields\SocialConnectField'      => 'Modules/Forms/FormFields/SocialConnectField.php',
		'UltraCommunity\Modules\Forms\FormFields\ProfileSectionField'     => 'Modules/Forms/FormFields/ProfileSectionField.php',

		'UltraCommunity\Modules\Forms\FormFields\DividerField'          => 'Modules/Forms/FormFields/DividerField.php',
		'UltraCommunity\Modules\Forms\FormFields\DropDownField'         => 'Modules/Forms/FormFields/DropDownField.php',
		'UltraCommunity\Modules\Forms\FormFields\CountryField'          => 'Modules/Forms/FormFields/CountryField.php',
		'UltraCommunity\Modules\Forms\FormFields\LanguageField'         => 'Modules/Forms/FormFields/LanguageField.php',
		'UltraCommunity\Modules\Forms\FormFields\EmailField'            => 'Modules/Forms/FormFields/EmailField.php',
		'UltraCommunity\Modules\Forms\FormFields\WebUrlField'           => 'Modules/Forms/FormFields/WebUrlField.php',
		'UltraCommunity\Modules\Forms\FormFields\TextField'             => 'Modules/Forms/FormFields/TextField.php',
		'UltraCommunity\Modules\Forms\FormFields\TextAreaField'         => 'Modules/Forms/FormFields/TextAreaField.php',
		'UltraCommunity\Modules\Forms\FormFields\CheckBoxField'         => 'Modules/Forms/FormFields/CheckBoxField.php',
		'UltraCommunity\Modules\Forms\FormFields\RadioButtonField'      => 'Modules/Forms/FormFields/RadioButtonField.php',
		'UltraCommunity\Modules\Forms\FormFields\SocialNetworkUrlField' => 'Modules/Forms/FormFields/SocialNetworkUrlField.php',

	);

	if (!isset($arrClassMap[$className]))
		return null;

	$filePath = MchBasePlugin::getPluginDirectoryPath() . '/engine/' . $arrClassMap[$className];
	unset($arrClassMap[$className]);

	return \file_exists($filePath) ? include $filePath : null;

}, false);
