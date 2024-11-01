<?php
namespace UltraCommunity\Controllers;

use UltraCommunity\Entities\UserReviewEntity;
use UltraCommunity\Entities\GroupEntity;
use UltraCommunity\Entities\UserEntity;
use UltraCommunity\Entities\UserMetaEntity;
use UltraCommunity\MchLib\Utils\MchDirectoryUtils;
use UltraCommunity\MchLib\Utils\MchHttpRequest;
use UltraCommunity\MchLib\Utils\MchUtils;
use UltraCommunity\MchLib\Utils\MchValidator;
use UltraCommunity\MchLib\Utils\MchWpUtils;
use UltraCommunity\MchLib\WordPress\Repository\WpPostRepository;
use UltraCommunity\MchLib\WordPress\Repository\WpUserRepository;
use UltraCommunity\Modules\Appearance\UserProfile\UserProfileAppearanceAdminModule;
use UltraCommunity\Modules\Forms\FormFields\BaseField;
use UltraCommunity\Modules\Forms\FormFields\EmailField;
use UltraCommunity\Modules\Forms\FormFields\UserRegistrationDateField;
use UltraCommunity\Modules\Forms\UserProfileForm\UserProfileFormAdminModule;
use UltraCommunity\Modules\Forms\UserProfileForm\UserProfileFormPublicModule;
use UltraCommunity\Modules\GeneralSettings\Group\GroupSettingsPublicModule;
use UltraCommunity\Modules\UserRole\UserRoleAdminModule;
use UltraCommunity\Repository\BaseRepository;
use UltraCommunity\Repository\UserRelationsRepository;
use UltraCommunity\Repository\UserRepository;
use UltraCommunity\UltraCommException;
use UltraCommunity\UltraCommHelper;
use UltraCommunity\UltraCommHooks;
use UltraCommunity\UltraCommUtils;

final class UserController
{

	/**
	 * @var \UltraCommunity\Entities\UserEntity
	 */
	private static $profiledUser = null;

	/**
	 * @var \UltraCommunity\Entities\UserEntity
	 */
	private static $loggedInUser = null;

	private function __construct()
	{}

	public static function initUserHooks()
	{

		MchWpUtils::addFilterHook('editable_roles', function ($arrEditableRoles){
			if(!MchWpUtils::isAdminLoggedIn()){ // prevent non-admin to edit admin users
				unset($arrEditableRoles['administrator'], $arrEditableRoles[UserRoleController::ROLE_ADMIN_SLUG]);
			}
			return $arrEditableRoles;
		});

		MchWpUtils::addFilterHook('map_meta_cap', function ($caps, $cap, $userId, $args){
			if(!in_array($cap, array('edit_user', 'remove_user', 'promote_user', 'delete_user')))
				return $caps;

			if ( ! isset( $args[0] ) || $args[0] === $userId )
				return $caps;

			if(!MchWpUtils::isAdminLoggedIn() && MchWpUtils::isAdminUser($args[0])){
				$caps[] = 'do_not_allow';
			}

			return $caps;

		}, 10, 4);

		/*
		 *  * @param array $arrAllCaps All the capabilities of the user
            * @param array $arrCaps     [0] Required capability
            * @param array $args        [0] Requested capability
            *                           [1] User ID
            *                           [2] Associated object ID
		 */
		MchWpUtils::addFilterHook('user_has_cap', function ($arrAllCaps, $arrCaps, $args){

			$userId          = empty($args[1]) ? null : $args[1];
			$checkCapability = empty($args[0]) ? null : $args[0];

			if(empty($userId) || empty($checkCapability))
				return $arrAllCaps;


			if(isset($arrAllCaps[$checkCapability]))
				return $arrAllCaps;


			$defaultMemberRole = wp_roles()->get_role(UserRoleController::ROLE_MEMBER_SLUG);
			if(!isset($defaultMemberRole->capabilities[$checkCapability])) // is not a ultra community capability
				return $arrAllCaps;


			if(null === ($defaultUserRolePublicInstance = UltraCommHelper::getDefaultUserRolePublicInstance()))
				return $arrAllCaps;

			$defaultMemberRole = wp_roles()->get_role($defaultUserRolePublicInstance->getOption(UserRoleAdminModule::OPTION_ROLE_SLUG));

			if(empty($defaultMemberRole->capabilities[$checkCapability]))
				return $arrAllCaps;

			$arrAllCaps[$checkCapability] = true;

			return $arrAllCaps;

		}, 10, 3);

		MchWpUtils::addActionHook('deleted_user', function ($userId){

			if(!is_numeric($userId))
				return;

			if(@is_dir(UltraCommUtils::getUploadsBaseDirectoryPath() . "/$userId")){
				MchDirectoryUtils::deleteDirectory(UltraCommUtils::getUploadsBaseDirectoryPath() . "/$userId");
			}

			WpUserRepository::deleteAllUserCommentsByCommentType($userId,  array(PostTypeController::POST_TYPE_ACTIVITY, PostTypeController::POST_TYPE_USER_REVIEW));
			UserRelationsRepository::deleteRelationByUserId($userId);

		});

		MchWpUtils::addActionHook('clear_auth_cookie', function (){
			if($currentUserId = get_current_user_id()){
				$arrOnlineUsers = get_option('ultracomm-online-users', array());
				if(isset($arrOnlineUsers[$currentUserId])){
					unset($arrOnlineUsers[$currentUserId]);
					update_option( 'ultracomm-online-users', $arrOnlineUsers );
					UserController::getOnlineUserIds(false, true);
				}
			}
		});

		MchWpUtils::addActionHook('shutdown', function (){

			$arrOnlineUsers = get_option('ultracomm-online-users', array());
			$currentTime = MchHttpRequest::getServerRequestTime();
			$shouldUpdate = false;
			$loggedInUserId = self::getLoggedInUserId();


			if($loggedInUserId)
			{
				if(self::$loggedInUser->getPrivacyEntity()->showOnlineStatus())
				{
					$arrOnlineUsers[ $loggedInUserId ] = $currentTime;
					$shouldUpdate = true;
				}
				elseif(isset($arrOnlineUsers[$loggedInUserId]))
				{
					unset($arrOnlineUsers[$loggedInUserId]);
					$shouldUpdate = true;
				}
			}


			foreach ($arrOnlineUsers as $userId => $logTime)
			{
				if($logTime + (5 * 60) < $currentTime){ // 5 minutes interval timeout
					unset($arrOnlineUsers[$userId]); $shouldUpdate = true;
				}
			}

			if(!$shouldUpdate) return;

			arsort( $arrOnlineUsers ); update_option( 'ultracomm-online-users', $arrOnlineUsers );

		});

	}


	public static function isUserOnline($userKey)
	{
		if( ! ($userId = UserRepository::getUserIdFromKey($userKey)) )
			return false;

		return \apply_filters(UltraCommHooks::FILTER_USER_IS_ONLINE, isset(self::getOnlineUserIds()[$userId]), $userId);
	}

	public static function getOnlineUserIds($asArrayValues = false, $resetCache = false)
	{
		static $arrOnlineUsers = null; !$resetCache ?: $arrOnlineUsers = null;
		(null !== $arrOnlineUsers) ?: $arrOnlineUsers = get_option('ultracomm-online-users', array());
		if(($loggedInUserId = self::getLoggedInUserId()) && empty($arrOnlineUsers[$loggedInUserId]) && self::$loggedInUser->getPrivacyEntity()->showOnlineStatus()){
			$arrOnlineUsers[$loggedInUserId] = MchHttpRequest::getServerRequestTime();
			arsort($arrOnlineUsers);
		}

		return $asArrayValues ? array_keys($arrOnlineUsers) : $arrOnlineUsers;

	}


	public static function isUserLoggedIn()
	{
		return null !== self::getLoggedInUser();
	}

	public static function setProfiledUserSlug($userSlug)
	{
		self::$profiledUser = self::getUserBy($userSlug, true);
	}

	public static function getProfiledUser()
	{
		return (self::$profiledUser instanceof UserEntity) ? self::$profiledUser : self::$profiledUser = self::getUserBy(self::$profiledUser);
	}


	public static function getLoggedInUser()
	{
		$wpUser = \wp_get_current_user();
		if(empty($wpUser->ID)){
			return self::$loggedInUser = null;
		}

		if(!empty(self::$loggedInUser->Id) && ((int)self::$loggedInUser->Id === (int)$wpUser->ID)){
			return self::$loggedInUser = self::getUserBy(self::$loggedInUser);
		}

		return self::$loggedInUser = self::getUserBy((int)$wpUser->ID);
	}

	public static function getUserEntityBy($userKey, $useStringAsNiceName = false) //,
	{
		return self::getUserBy($userKey, $useStringAsNiceName);
	}

	/**
	 * @return \UltraCommunity\Entities\UserEntity | null
	 */
	private static function getUserBy($userKey, $useStringAsNiceName = false)
	{
		if( !empty($userKey->Id) && !empty($userKey->UserMetaEntity) ) //&& ($userKey instanceof UserEntity)
			return $userKey;

		if($useStringAsNiceName && \is_string($userKey))
		{
			$userEntity = new UserEntity(); $userEntity->NiceName = $userKey;
			$userKey = $userEntity;
		}

		return UserRepository::getUserEntityBy($userKey);
	}


	public static function saveUserInfo(UserEntity $userEntity)
	{
		UserRepository::saveUser($userEntity);

		if(!empty(self::$profiledUser->Id) && self::$profiledUser->Id == $userEntity->Id)
			self::$profiledUser = (int)self::$profiledUser->Id;

		if(!empty(self::$loggedInUser->Id) && self::$loggedInUser->Id == $userEntity->Id)
			self::$loggedInUser = (int)self::$loggedInUser->Id;

	}

	public static function isUserProfileAccessible(UserEntity $userEntity)
	{
		if(MchWpUtils::isAdminLoggedIn())
			return true;

//		if(null === ($userRolePublicModuleInstance = UltraCommHelper::getUserRolePublicInstanceByUserInfo($userEntity))){
//			return false;
//		}

		if(empty($userEntity->UserMetaEntity))
			return false;

		if($userEntity->IsUltraCommUser && UserMetaEntity::USER_STATUS_APPROVED !== (int)$userEntity->UserMetaEntity->UserStatus)
			return false;


//		if(empty($userEntity->UserPrivacyEntity)){
//			return true;
//		}

		return true;
	}

	public static function currentUserCanEditProfile()
	{
		if(empty(self::$profiledUser->Id) || !self::isUserLoggedIn())
			return false;

		if(MchWpUtils::isAdminLoggedIn())
			return true;

		if(UserRoleController::currentUserCanEditOtherProfiles()){
			return MchWpUtils::isAdminUser(self::$profiledUser->Id) ? false : true; // nobody can edit admin profiles except of course the admin
		}

		return (self::getLoggedInUserId() === (int)self::$profiledUser->Id) && UserRoleController::currentUserCanEditOwnProfile();

	}

	public static function currentUserCanViewProfileFormField(BaseField $profileFormField, $isUserEditingSettings = false)
	{

		if(MchWpUtils::isAdminLoggedIn())
			return true;

		if($isUserEditingSettings)
		{
			$arrFormFields = self::getProfiledUserProfileFormFields();

			if( ! isset($arrFormFields[$profileFormField->UniqueId]) )
				return false;

			return !empty($arrFormFields[$profileFormField->UniqueId]->IsVisibleOnEditProfile);
		}


		// Front end visibility started

		if(isset($profileFormField->FrontEndVisibility[BaseField::VISIBILITY_JUST_USERS_ROLES])){ // by user role
//			if(!self::isUserLoggedIn()){
//				return false;
//			}

//			$userRolePostTypePublicInstance = UltraCommHelper::getUserRolePublicInstanceByUserInfo(self::$profiledUser);
//			if(null === $userRolePostTypePublicInstance)
//				return false;

			foreach(UserRoleController::getUserRolePostTypes(self::$profiledUser) as $userRolePostType)
			{
				if(in_array($userRolePostType->PostId, $profileFormField->FrontEndVisibility[BaseField::VISIBILITY_JUST_USERS_ROLES]))
					return true;
			}

			return false;
			//return in_array($userRolePostTypePublicInstance->getCustomPostTypeId(), $profileFormField->FrontEndVisibility[BaseField::VISIBILITY_JUST_USERS_ROLES]);
		}

		switch($profileFormField->FrontEndVisibility)
		{
			case BaseField::VISIBILITY_EVERYBODY :
				return true;
			case BaseField::VISIBILITY_LOGGED_IN_USERS :
				return self::isUserLoggedIn();
			case BaseField::VISIBILITY_NOBODY :
				return false;
		}

		return false;
	}

	public static function currentUserCanEditProfileFormField(BaseField $profileFormField)
	{
		$arrFormFields = self::getProfiledUserProfileFormFields();

		if( ! isset($arrFormFields[$profileFormField->UniqueId]) )
			return false;

		return  ( !empty($profileFormField->IsEditable) || MchWpUtils::isAdminLoggedIn() );
	}

	public static function isCurrentUserBrowsingOwnProfile()
	{
		if(empty(self::$profiledUser->Id))
			return false;

		return (self::getLoggedInUserId() === (int)self::$profiledUser->Id);

	}

	/**
	 * @return BaseField[]
	 */
	public static function getProfiledUserProfileFormFields()
	{
		static $arrFormFields = null;
		if(null !== $arrFormFields)
			return $arrFormFields;

		return $arrFormFields = self::getUserProfileFormFields(self::getProfiledUser());
	}

	/**
	 * @param UserEntity $userEntity
	 *
	 * @return \UltraCommunity\Modules\Forms\FormFields\BaseField[]
	 */
	public static function getUserProfileFormFields(UserEntity $userEntity = null)
	{
		$userProfileForm =  UltraCommHelper::getUserProfileFormPublicInstance($userEntity);
		if(! $userProfileForm instanceof UserProfileFormPublicModule)
			return array();

		$arrFormFields = array();
		foreach( (array)$userProfileForm->getOption(UserProfileFormAdminModule::OPTION_FORM_FIELDS) as $profileFormField){
			empty($profileFormField->UniqueId) ?: $arrFormFields[$profileFormField->UniqueId] = $profileFormField;
		}

		return $arrFormFields;

	}

	public static function getProfiledUserPublicModuleProfileFormInstance()
	{
		static $publicModuleInstance  = null;

		if(null === self::getProfiledUser())
			return null;

		return (null !== $publicModuleInstance) ? $publicModuleInstance : $publicModuleInstance = UltraCommHelper::getUserProfileFormPublicInstance(self::$profiledUser);
	}


	public static function getUserProfileFormFieldValue(UserEntity $userEntity, BaseField $profileFormField, $escaped = true)
	{

		$mappedProperty = $profileFormField->getUserEntityMappedFieldName();

		$fieldValue= null;
		if(!empty($profileFormField->MappedUserMetaKey))
		{
			$fieldValue = get_user_meta( $userEntity->Id, $profileFormField->MappedUserMetaKey, true );
		}
		elseif(null !== $mappedProperty)
		{
			$mappedProperty = str_replace('_meta_','', $mappedProperty);
			if (property_exists($userEntity->UserMetaEntity, $mappedProperty)) {
				$fieldValue =  $userEntity->UserMetaEntity->{$mappedProperty};
			}
			elseif (property_exists($userEntity, $mappedProperty)){
				$fieldValue =  $userEntity->{$mappedProperty};
			}
		}
		elseif(!empty($profileFormField->RegisterFormFieldUniqueId))
		{
			if(!empty($userEntity->UserMetaEntity->RegisterFormValues[$profileFormField->RegisterFormFieldUniqueId])){
				$fieldValue =  $userEntity->UserMetaEntity->RegisterFormValues[$profileFormField->RegisterFormFieldUniqueId];
			}
		}

		if(empty($fieldValue))
		{

			if ( ! isset( $userEntity->UserMetaEntity->ProfileFormValues[ $profileFormField->UniqueId ] ) ) {
				return null;
			}

			$fieldValue = $userEntity->UserMetaEntity->ProfileFormValues[ $profileFormField->UniqueId ];

			if(!is_scalar($fieldValue))
			{
				return $fieldValue;
			}

			if ( isset( $profileFormField->OptionsList[ $fieldValue ] ) ) {
				$fieldValue = $profileFormField->OptionsList[ $fieldValue ];
			}

		}

		if(empty($fieldValue)){
			return null;
		}

		switch(true)
		{
			case $profileFormField instanceof UserRegistrationDateField :

				$dateFormatPattern = empty($profileFormField->FormatPattern) ? 'M, Y' : $profileFormField->FormatPattern;

				$fieldValue = date_i18n($dateFormatPattern, strtotime($fieldValue));

				break;
		}

		$fieldValue = MchWpUtils::stripSlashes(html_entity_decode(htmlspecialchars_decode($fieldValue)));

		if($profileFormField->IsHtmlAllowed)
			return $fieldValue;

		return $escaped ? esc_html($fieldValue) : $fieldValue;

	}

	/**
	 * @param string|int|\WP_User $userKey
	 */
	public static function changeUserStatus($userKey, $newUserStatusId)
	{
		if(!MchValidator::isInteger($newUserStatusId)){
			throw new UltraCommException(__('The new user status is not an int value', 'ultra-community'));
		}

		$userEntity = UserRepository::getUserEntityBy($userKey);
		if(null === $userEntity){
			throw new UltraCommException(__('This user does not exist', 'ultra-community'));
		}

		if($userEntity->UserMetaEntity->UserStatus == $newUserStatusId)
			return;

		$userEntity->UserMetaEntity->UserStatus = $newUserStatusId;
		if(!$userEntity->UserMetaEntity->hasValidUserStatus()){
			throw new UltraCommException(__('Invalid user status received', 'ultra-community'));
		}

		do_action(UltraCommHooks::ACTION_BEFORE_CHANGE_USER_STATUS, $userKey, $newUserStatusId);

		self::saveUserInfo($userEntity);
		self::resetLoggedInUser();

		static $afterHookAdded = false;
		$afterHookAdded ?: MchWpUtils::addActionHook(UltraCommHooks::ACTION_AFTER_USER_STATUS_CHANGED, function($userKey, $newUserStatusId){

			$newUserEntity = UserRepository::getUserEntityBy($userKey);
			switch ($newUserStatusId)
			{
				case UserMetaEntity::USER_STATUS_APPROVED:
					NotificationsController::sendNotification(NotificationsController::NOTIFICATION_EMAIL_WELCOME_NEW_USER, $newUserEntity);
					break;

				case UserMetaEntity::USER_STATUS_AWAITING_REVIEW:

					NotificationsController::sendNotification(NotificationsController::NOTIFICATION_EMAIL_ACCOUNT_AWAITING_REVIEW, $newUserEntity);

					break;

				case UserMetaEntity::USER_STATUS_AWAITING_EMAIL_CONFIRMATION :

					NotificationsController::sendNotification(NotificationsController::NOTIFICATION_EMAIL_ACCOUNT_AWAITING_EMAIL_CONFIRMATION, $newUserEntity);

					break;
			}

		}, 99, 2);

		$afterHookAdded = true;

		do_action(UltraCommHooks::ACTION_AFTER_USER_STATUS_CHANGED, $userKey, $newUserStatusId);

	}


	public static function resetLoggedInUser()
	{
		self::$loggedInUser = null;
	}

	public static function getLoggedInUserId()
	{
		$loggedInUser = self::getLoggedInUser();
		return empty($loggedInUser->Id) ? 0 : (int)$loggedInUser->Id;
	}


	public static function getUserStats($userKey, $section)
	{
		$userId = UserRepository::getUserIdFromKey($userKey);
		if(empty($userId))
			return 0;

		static $arrUserStats = array();
		if(isset($arrUserStats[$section][$userId])) {
			return $arrUserStats[$section][$userId];
		}

		isset($arrUserStats[$section]) ?: $arrUserStats[$section] = array();

		switch ($section)
		{
			case UserProfileAppearanceAdminModule::USER_STATS_COUNTER_POSTS     : $arrUserStats[$section][$userId] = WpUserRepository::getNumberOfPublishedPosts($userId, UltraCommHelper::getUserDisplayablePostTypes($userId)); break;
			case UserProfileAppearanceAdminModule::USER_STATS_COUNTER_COMMENTS  : $arrUserStats[$section][$userId] = WpUserRepository::getNumberOfApprovedComments($userId); break;
			case UserProfileAppearanceAdminModule::USER_STATS_COUNTER_FRIENDS   : $arrUserStats[$section][$userId] = UserRelationsController::countFriends($userId); break;
			case UserProfileAppearanceAdminModule::USER_STATS_COUNTER_FOLLOWERS : $arrUserStats[$section][$userId] = UserRelationsController::countFollowers($userId); break;
			case UserProfileAppearanceAdminModule::USER_STATS_COUNTER_FOLLOWING : $arrUserStats[$section][$userId] = UserRelationsController::countFollowing($userId); break;

		}

		return isset($arrUserStats[$section][$userId]) ? $arrUserStats[$section][$userId] : $arrUserStats[$section][$userId] = 0;

	}


	public static function getUserReviewsEntities($userKey)
	{
		return empty($userKey) ? array() : UserRepository::getUserReviews($userKey);
	}

	public static function getUserAverageRatingScore($userKey)
	{
		$arrRatings = array(1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0);
		foreach (self::getUserReviewsEntities($userKey) as $reviewEntity){
			!isset($arrRatings[$reviewEntity->StarsRating]) ?: $arrRatings[$reviewEntity->StarsRating]++;
		}

		$maxRatings = $sumRatings = 0;
		foreach ($arrRatings as $stars => $countStars){
			$maxRatings += $stars * $countStars;
			$sumRatings += $countStars;
		}

		return ($sumRatings === 0) ? 0 : number_format_i18n( $maxRatings /  $sumRatings, 1);
	}


	public static function getUserReviewsStarsPercentage($userKey)
	{
		$arrUserReviewEntities = self::getUserReviewsEntities($userKey);
		$arrRatings = array(1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0);

		if(empty($arrUserReviewEntities))
			return $arrRatings;

		foreach ($arrUserReviewEntities as $reviewEntity){
			!isset($arrRatings[$reviewEntity->StarsRating]) ?: $arrRatings[$reviewEntity->StarsRating]++;
		}

		$numberOfRatings = count($arrUserReviewEntities);
		foreach($arrRatings as &$percentValue){
			$percentValue = round( ($percentValue / $numberOfRatings) * 100 , 2);
		}

		return $arrRatings;
	}

	public static function getUserNumberOfReviews($userKey)
	{
		return count(self::getUserReviewsEntities($userKey));
	}


	public static function getUserSubmittedReviewForUser($reviewerKey, $userKey)
	{
		$reviewerKey = UserRepository::getUserIdFromKey($reviewerKey);
		$arrReviewerSubmittedReviews = array_filter(self::getUserReviewsEntities($userKey), function($userReviewEntity) use ($reviewerKey){
			return $userReviewEntity->ReviewerId == $reviewerKey;
		});

		return (isset($arrReviewerSubmittedReviews[0]) && ($arrReviewerSubmittedReviews[0] instanceof UserReviewEntity)) ?  $arrReviewerSubmittedReviews[0] : null;
	}
}