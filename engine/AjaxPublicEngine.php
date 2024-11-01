<?php
/**
 * Copyright (c) 2016 Ultra Community (http://www.ultracommunity.com)
 */

namespace UltraCommunity;

use UltraCommunity\Controllers\ActivityController;
use UltraCommunity\Controllers\FrontPageController;
use UltraCommunity\Controllers\GroupController;
use UltraCommunity\Controllers\ModulesController;
use UltraCommunity\Controllers\NotificationsController;
use UltraCommunity\Controllers\PostTypeController;
use UltraCommunity\Controllers\TemplatesController;
use UltraCommunity\Controllers\UserController;
use UltraCommunity\Controllers\UserRelationsController;
use UltraCommunity\Controllers\UserRoleController;
use UltraCommunity\Entities\ActivityEntity;
use UltraCommunity\Entities\ActivityMetaData;
use UltraCommunity\Entities\GroupEntity;
use UltraCommunity\Entities\GroupUserEntity;
use UltraCommunity\Entities\UserEntity;
use UltraCommunity\Entities\UserPrivacyEntity;
use UltraCommunity\Entities\UserRelationEntity;
use UltraCommunity\FrontPages\ActivityPage;
use UltraCommunity\FrontPages\GroupsDirectoryPage;
use UltraCommunity\FrontPages\MembersDirectoryPage;
use UltraCommunity\FrontPages\UserProfilePage;
use UltraCommunity\FrontPages\UserSettingsPage;
use UltraCommunity\MchLib\Utils\MchDirectoryUtils;
use UltraCommunity\MchLib\Utils\MchFileUtils;
use UltraCommunity\MchLib\Utils\MchImageUtils;
use UltraCommunity\MchLib\Utils\MchUtils;
use UltraCommunity\MchLib\Utils\MchValidator;
use UltraCommunity\MchLib\Utils\MchWpUtils;
use UltraCommunity\MchLib\WordPress\Repository\WpPostRepository;
use UltraCommunity\MchLib\WordPress\Repository\WpUserRepository;
use UltraCommunity\Modules\Appearance\UserProfile\UserProfileAppearanceAdminModule;
use UltraCommunity\Modules\Forms\ForgotPasswordForm\ForgotPasswordFormPublicModule;
use UltraCommunity\Modules\Forms\FormFields\CheckBoxField;
use UltraCommunity\Modules\Forms\FormFields\UserEmailField;
use UltraCommunity\Modules\Forms\FormFields\UserNameField;
use UltraCommunity\Modules\Forms\LoginForm\LoginFormPublicModule;
use UltraCommunity\Modules\Forms\RegisterForm\RegisterFormPublicModule;
use UltraCommunity\Modules\PostSubmissions\PostSubmissionsAdminModule;
use UltraCommunity\Modules\PostSubmissions\PostSubmissionsPublicModule;
use UltraCommunity\Modules\UserRole\UserRoleAdminModule;
use UltraCommunity\PostsType\ActivityPostType;
use UltraCommunity\PostsType\UserReviewPostType;
use UltraCommunity\Repository\GroupRepository;
use UltraCommunity\Repository\UserRepository;

final class AjaxPublicEngine
{
	CONST FULLY_QUALIFIED_CLASS_NAME = __CLASS__;


	public static function saveUserReview()
	{

		if(!UserController::getProfiledUser()){
			MchWpUtils::sendAjaxErrorMessage(__('Invalid request received', 'ultra-community'));
		}

		if( !UserController::isUserLoggedIn()){
			MchWpUtils::sendAjaxErrorMessage(__('Please login to review this user!', 'ultra-community'));
		}

		if(empty($_POST['txtUserStars']) || !MchValidator::isInteger($_POST['txtUserStars'])){
			MchWpUtils::sendAjaxErrorMessage(__('Please select the number of stars!', 'ultra-community'));
		}

		if(empty($_POST['txtUserReviewContent'])){
			MchWpUtils::sendAjaxErrorMessage(__('Please write a short review!', 'ultra-community'));
		}

		$userReviewsPostType = reset($arrUserReviewsPostType = (array)WpPostRepository::findByPostType(PostTypeController::POST_TYPE_USER_REVIEW, array('author' => UserController::getProfiledUser()->Id)));

		if(empty($userReviewsPostType))
		{
			$userReviewsPostType = new UserReviewPostType(PostTypeController::POST_TYPE_USER_REVIEW);
			$userReviewsPostType->UserId = UserController::getProfiledUser()->Id;
			$userReviewsPostType->PostId = PostTypeController::publishPostType($userReviewsPostType);
		}


		if(MchWpUtils::isWPPost($userReviewsPostType)){
			$userReviewsPostType = new UserReviewPostType(PostTypeController::POST_TYPE_USER_REVIEW, $userReviewsPostType);
		}

		$arrCommentsArgs = array(
				'post_id' => $userReviewsPostType->PostId,
				'user_id' => UserController::getLoggedInUserId(),
				'type' => PostTypeController::POST_TYPE_USER_REVIEW,
				'status' => 1
		);

		$arrUserSubmittedReview = (array)get_comments($arrCommentsArgs);
		$arrUserSubmittedReview = reset($arrUserSubmittedReview);
		$arrCommentData = array(
				'comment_post_ID'      => $userReviewsPostType->PostId,
				'comment_author'       => '',
				'comment_author_email' => '',
				'comment_author_url'   => '',
				'comment_content'      => $_POST['txtUserReviewContent'],
				'comment_type'         => PostTypeController::POST_TYPE_USER_REVIEW,
				'user_id'              => UserController::getLoggedInUserId(),
				'comment_meta'         => array('uc-stars-rating' => $_POST['txtUserStars'])
		);

		MchWpUtils::addFilterHook('pre_comment_approved', function($isApproved, $arrCommentData ){
			return (!empty($arrCommentData['comment_type']) && $arrCommentData['comment_type'] == PostTypeController::POST_TYPE_USER_REVIEW) ? 1 : $isApproved;
		}, PHP_INT_MAX, 2);

		if(!empty($arrUserSubmittedReview))
		{
			$arrCommentData['comment_ID'] = $arrUserSubmittedReview->comment_ID;
			wp_update_comment($arrCommentData);
		}
		else
		{
			wp_new_comment( $arrCommentData, true );
		}

		$arrUserSubmittedReview = (array)get_comments($arrCommentsArgs);
		$arrUserSubmittedReview = reset($arrUserSubmittedReview);

		empty($arrUserSubmittedReview) ? MchWpUtils::sendAjaxErrorMessage(__('Error submitting your review', 'ultra-community')) : MchWpUtils::sendAjaxSuccessMessage(
				array('message' => __('Your review has been submitted successfully', 'ultra-community'), 'userReview' => '') // add template here
		);
	}

	public static function userAcceptFriendship()
	{
		if(empty($_POST['relationTargetId']) || !MchValidator::isInteger($_POST['relationTargetId']) || !UserController::isUserLoggedIn()){
			MchWpUtils::sendAjaxErrorMessage(__('Invalid request received', 'ultra-community'));
		}

		foreach (UserRelationsController::getFriendshipPendingRequests(UserController::getLoggedInUser()) as $userRelationEntity)
		{
			if(empty($userRelationEntity->PrimaryUserId) || empty($userRelationEntity->SecondaryUserId))
				continue;

			if($userRelationEntity->SecondaryUserId !== UserController::getLoggedInUserId()){
				continue;
			}

			$userRelationEntity->StatusId = UserRelationEntity::RELATION_STATUS_ACTIVE;

			UserRelationsController::saveUserRelation($userRelationEntity);

			MchWpUtils::sendAjaxSuccessMessage(__('Accepted', 'ultra-community'));
		}


	}

	public static function userDeclineFriendship()
	{
		if(empty($_POST['relationTargetId']) || !MchValidator::isInteger($_POST['relationTargetId']) || !UserController::isUserLoggedIn()){
			MchWpUtils::sendAjaxErrorMessage(__('Invalid request received', 'ultra-community'));
		}

		foreach (UserRelationsController::getFriendshipPendingRequests(UserController::getLoggedInUser()) as $userRelationEntity)
		{
			if(empty($userRelationEntity->PrimaryUserId) || empty($userRelationEntity->SecondaryUserId))
				continue;

			if($userRelationEntity->SecondaryUserId !== UserController::getLoggedInUserId()){
				continue;
			}

			UserRelationsController::removeUserRelation($userRelationEntity);

			MchWpUtils::sendAjaxSuccessMessage(__('Declined', 'ultra-community'));

		}

	}


	public static function userUnFollow()
	{
		if(empty($_POST['secondaryUserId']) || !MchValidator::isInteger($_POST['secondaryUserId']) || !UserController::isUserLoggedIn()){
			MchWpUtils::sendAjaxErrorMessage(__('Invalid request received', 'ultra-community'));
		}

		$arrFollowing = UserRelationsController::getUserActiveFollowingUsers(UserController::getLoggedInUser());
		$relationId   = empty($arrFollowing[$_POST['secondaryUserId']]) ? 0 : $arrFollowing[$_POST['secondaryUserId']];

		UserRelationsController::removeUserRelation($relationId);

		MchWpUtils::sendAjaxSuccessMessage();

	}

	public static function userUnFriend()
	{
		if(empty($_POST['secondaryUserId']) || !MchValidator::isInteger($_POST['secondaryUserId']) || !UserController::isUserLoggedIn()){
			MchWpUtils::sendAjaxErrorMessage(__('Invalid request received', 'ultra-community'));
		}

		$arrUserFriends = UserRelationsController::getUserActiveFriends(UserController::getLoggedInUser());
		$relationId     = empty($arrUserFriends[$_POST['secondaryUserId']]) ? 0 : $arrUserFriends[$_POST['secondaryUserId']];

		UserRelationsController::removeUserRelation($relationId);

		MchWpUtils::sendAjaxSuccessMessage();
	}

	public static function addUserFriendRequest()
	{
		if(empty($_POST['secondaryUserId']) || !MchValidator::isInteger($_POST['secondaryUserId']) || !UserController::isUserLoggedIn()){
			MchWpUtils::sendAjaxErrorMessage(__('Invalid request received', 'ultra-community'));
		}

		if(!UserRelationsController::userCanSendFriendshipRequest(UserController::getLoggedInUser(), $_POST['secondaryUserId'])){
			MchWpUtils::sendAjaxErrorMessage(__('You cannot send this friendship request!', 'ultra-community'));
		}

		$userRelationEntity = new UserRelationEntity(null, UserController::getLoggedInUser()->Id, $_POST['secondaryUserId'], UserRelationEntity::RELATION_TYPE_FRIENDSHIP, UserRelationEntity::RELATION_STATUS_PENDING);
		if(!UserRelationsController::saveUserRelation($userRelationEntity)){
			MchWpUtils::sendAjaxErrorMessage(__('An error was encountered', 'ultra-community'));
		}

		MchWpUtils::sendAjaxSuccessMessage( __('Request Sent', 'ultra-community') );

	}



	public static function addUserFollow()
	{
		if(empty($_POST['secondaryUserId']) || !MchValidator::isInteger($_POST['secondaryUserId']) || !UserController::isUserLoggedIn()){
			MchWpUtils::sendAjaxErrorMessage(__('Invalid request received', 'ultra-community'));
		}

		if(!UserRelationsController::userCanFollow(UserController::getLoggedInUser(), $_POST['secondaryUserId'])){
			MchWpUtils::sendAjaxErrorMessage(__('You cannot follow this user!', 'ultra-community'));
		}

		$userRelationEntity = new UserRelationEntity(null, UserController::getLoggedInUser()->Id, $_POST['secondaryUserId'], UserRelationEntity::RELATION_TYPE_FOLLOWING);

		if(!UserRelationsController::saveUserRelation($userRelationEntity)){
			MchWpUtils::sendAjaxErrorMessage(__('An error was encountered', 'ultra-community'));
		}

		MchWpUtils::sendAjaxSuccessMessage( __('Following', 'ultra-community') );

	}



	public static function saveGroupPicture()
	{
		if(!GroupController::getProfiledGroup()){
			MchWpUtils::sendAjaxErrorMessage(__('Invalid request received', 'ultra-community'));
		}

		if(!GroupController::userCanEditGroup(UserController::getLoggedInUser(), GroupController::getProfiledGroup())){
			MchWpUtils::sendAjaxErrorMessage(__('You cannot change the picture of this group', 'ultra-community'));
		}


		UltraCommUtils::registerTempUploadsDirectoryFilter();
		function_exists('wp_handle_upload') || require_once( ABSPATH . 'wp-admin/includes/file.php' );

		empty($_FILES['profilePicture']['name']) ?: $_FILES['profilePicture']['name'] = strtolower($_FILES['profilePicture']['name']);

		$arrUploadedFileInfo = wp_handle_upload( $_FILES['profilePicture'], array('test_form' => false));
		UltraCommUtils::removeTempUploadsDirectoryFilter();


		if(isset($arrUploadedFileInfo['error']) || empty($arrUploadedFileInfo['file']))
		{
			MchWpUtils::sendAjaxErrorMessage(empty($arrUploadedFileInfo['error']) ? __('An error was encountered while uploading the file!', 'ultra-community') : $arrUploadedFileInfo['error']);
		}

		MchDirectoryUtils::createDirectory(UltraCommUtils::getGroupPictureBaseDirectoryPath(GroupController::getProfiledGroup()->Id));

		$uploadedFilePath    = wp_unique_filename(UltraCommUtils::getGroupPictureBaseDirectoryPath(GroupController::getProfiledGroup()->Id), 'avatar.png');

		if(!rename($arrUploadedFileInfo['file'], UltraCommUtils::getGroupPictureBaseDirectoryPath(GroupController::getProfiledGroup()->Id) . DIRECTORY_SEPARATOR . $uploadedFilePath)){
			MchWpUtils::sendAjaxErrorMessage(__('An error was encountered while uploading the file!', 'ultra-community'));
		}

		GroupController::getProfiledGroup()->PictureFileName = MchFileUtils::getFileBaseName($uploadedFilePath);

		GroupController::saveGroup(GroupController::getProfiledGroup());

		$arrResponse = array('avatarUrl' => UltraCommHelper::getGroupPictureUrl(GroupController::getProfiledGroup()->Id));
		$arrResponse['message'] = __('Group picture was successfully changed!', 'ultra-community');

		MchWpUtils::sendAjaxSuccessMessage($arrResponse);

	}


	public static function saveGroupCoverPicture()
	{
		if(!GroupController::getProfiledGroup()){
			MchWpUtils::sendAjaxErrorMessage(__('Invalid request received', 'ultra-community'));
		}

		if(!GroupController::userCanEditGroup(UserController::getLoggedInUser(), GroupController::getProfiledGroup())){
			MchWpUtils::sendAjaxErrorMessage(__('You cannot change the cover picture of this group', 'ultra-community'));
		}

		UltraCommUtils::registerTempUploadsDirectoryFilter();
		function_exists('wp_handle_upload') || require_once( ABSPATH . 'wp-admin/includes/file.php' );

		empty($_FILES['coverPicture']['name']) ?: $_FILES['coverPicture']['name'] = strtolower($_FILES['coverPicture']['name']);

		$arrUploadedFileInfo = wp_handle_upload( $_FILES['coverPicture'], array('test_form' => false));
		UltraCommUtils::removeTempUploadsDirectoryFilter();


		if(isset($arrUploadedFileInfo['error']) || empty($arrUploadedFileInfo['file']))
		{
			MchWpUtils::sendAjaxErrorMessage(empty($arrUploadedFileInfo['error']) ? __('An error was encountered while uploading the file!', 'ultra-community') : $arrUploadedFileInfo['error']);
		}

		MchDirectoryUtils::createDirectory(UltraCommUtils::getGroupCoverBaseDirectoryPath(GroupController::getProfiledGroup()->Id));

		$uploadedFilePath    = wp_unique_filename(UltraCommUtils::getGroupCoverBaseDirectoryPath(GroupController::getProfiledGroup()->Id), 'cover.png');

		if(!rename($arrUploadedFileInfo['file'], UltraCommUtils::getGroupCoverBaseDirectoryPath(GroupController::getProfiledGroup()->Id) . DIRECTORY_SEPARATOR . $uploadedFilePath)){
			MchWpUtils::sendAjaxErrorMessage(__('An error was encountered while uploading the file!', 'ultra-community'));
		}

		GroupController::getProfiledGroup()->CoverFileName = MchFileUtils::getFileBaseName($uploadedFilePath);

		GroupController::saveGroup(GroupController::getProfiledGroup());

		$arrResponse = array('coverUrl' => UltraCommHelper::getGroupCoverUrl(GroupController::getProfiledGroup()->Id));
		$arrResponse['message'] = __('Group cover picture was successfully changed!', 'ultra-community');

		MchWpUtils::sendAjaxSuccessMessage($arrResponse);


	}

	public static function saveUserProfileCover()
	{
		if(!UserController::currentUserCanEditProfile()){
			MchWpUtils::sendAjaxErrorMessage(__('You cannot change the profile cover picture', 'ultra-community'));
		}


		UltraCommUtils::registerTempUploadsDirectoryFilter();
		function_exists('wp_handle_upload') || require_once( ABSPATH . 'wp-admin/includes/file.php' );

		empty($_FILES['coverPicture']['name']) ?: $_FILES['coverPicture']['name'] = strtolower($_FILES['coverPicture']['name']);

		$arrUploadedFileInfo = wp_handle_upload( $_FILES['coverPicture'], array('test_form' => false));
		UltraCommUtils::removeTempUploadsDirectoryFilter();


		if(isset($arrUploadedFileInfo['error']) || empty($arrUploadedFileInfo['file']))
		{
			MchWpUtils::sendAjaxErrorMessage(empty($arrUploadedFileInfo['error']) ? __('An error was encountered while uploading the file!', 'ultra-community') : $arrUploadedFileInfo['error']);
		}

		MchDirectoryUtils::createDirectory(UltraCommUtils::getProfileCoverBaseDirectoryPath(UserController::getProfiledUser()));

		$uploadedFilePath    = wp_unique_filename(UltraCommUtils::getProfileCoverBaseDirectoryPath(UserController::getProfiledUser()), 'cover.png');

		if(!rename($arrUploadedFileInfo['file'], UltraCommUtils::getProfileCoverBaseDirectoryPath(UserController::getProfiledUser()) . DIRECTORY_SEPARATOR . $uploadedFilePath)){
			MchWpUtils::sendAjaxErrorMessage(__('An error was encountered while uploading the file!', 'ultra-community'));
		}

		UserController::getProfiledUser()->UserMetaEntity->ProfileCoverFileName = MchFileUtils::getFileBaseName($uploadedFilePath);

		UserController::saveUserInfo(UserController::getProfiledUser());

		do_action(UltraCommHooks::ACTION_AFTER_USER_PROFILE_COVER_CHANGED, UserController::getProfiledUser());

		$arrResponse = array('coverUrl' => UltraCommHelper::getUserProfileCoverUrl(UserController::getUserEntityBy(UserController::getProfiledUser()->Id)));
		$arrResponse['message'] = __('Your profile cover picture was successfully changed!', 'ultra-community');

		MchWpUtils::sendAjaxSuccessMessage($arrResponse);

	}


	public static function saveUserProfilePicture()
	{
		if(!UserController::currentUserCanEditProfile()){
			MchWpUtils::sendAjaxErrorMessage(__('You cannot change the profile picture', 'ultra-community'));
		}


		UltraCommUtils::registerTempUploadsDirectoryFilter();
		function_exists('wp_handle_upload') || require_once( ABSPATH . 'wp-admin/includes/file.php' );

		empty($_FILES['profilePicture']['name']) ?: $_FILES['profilePicture']['name'] = strtolower($_FILES['profilePicture']['name']);

		$arrUploadedFileInfo = wp_handle_upload( $_FILES['profilePicture'], array('test_form' => false));
		UltraCommUtils::removeTempUploadsDirectoryFilter();


		if(isset($arrUploadedFileInfo['error']) || empty($arrUploadedFileInfo['file']))
		{
			MchWpUtils::sendAjaxErrorMessage(empty($arrUploadedFileInfo['error']) ? __('An error was encountered while uploading the file!', 'ultra-community') : $arrUploadedFileInfo['error']);
		}

		MchDirectoryUtils::createDirectory(UltraCommUtils::getAvatarBaseDirectoryPath(UserController::getProfiledUser()));

		$uploadedFilePath    = wp_unique_filename(UltraCommUtils::getAvatarBaseDirectoryPath(UserController::getProfiledUser()), 'avatar.png');

		if(!rename($arrUploadedFileInfo['file'], UltraCommUtils::getAvatarBaseDirectoryPath(UserController::getProfiledUser()) . DIRECTORY_SEPARATOR . $uploadedFilePath)){
			MchWpUtils::sendAjaxErrorMessage(__('An error was encountered while uploading the file!', 'ultra-community'));
		}

		UserController::getProfiledUser()->UserMetaEntity->AvatarFileName = MchFileUtils::getFileBaseName($uploadedFilePath);

		UserController::saveUserInfo(UserController::getProfiledUser());

		do_action(UltraCommHooks::ACTION_AFTER_USER_PROFILE_PHOTO_CHANGED, UserController::getProfiledUser());


		$arrResponse = array('avatarUrl' => UltraCommHelper::getUserAvatarUrl(UserController::getProfiledUser()->Id, 200));
		$arrResponse['message'] = __('Your profile picture was successfully changed!', 'ultra-community');

		MchWpUtils::sendAjaxSuccessMessage($arrResponse);
	}


	public static function getLoggedInUserInfo()
	{
		MchWpUtils::sendAjaxSuccessMessage(array('userId' => get_current_user_id(), AjaxHandler::REQUEST_NONCE_KEY => AjaxHandler::getAjaxNonce()));
	}

	public static function authenticateUser()
	{
		$pageInstance = FrontPageController::getFrontPageInstance( FrontPageController::PAGE_LOGIN );
		if ( empty( $_POST[ $pageInstance->getPostRequestActionKey() ] ))
			MchWpUtils::sendAjaxErrorMessage(__('Invalid authentication request!', 'ultra-community'));

		$publicLoginModuleInstance = PostTypeController::getAssociatedPublicModuleInstance(PostTypeController::getPostTypeInstanceByPostId($_POST[$pageInstance->getPostRequestActionKey()]));
		if(! $publicLoginModuleInstance instanceof LoginFormPublicModule){
			MchWpUtils::sendAjaxErrorMessage(__('Invalid authentication request!', 'ultra-community'));
		}

		
		
		try
		{
			$publicLoginModuleInstance->authenticateUser();
			MchWpUtils::autoLogInUser(get_current_user_id(), true);
		}
		catch (UltraCommException $ue)
		{
			MchWpUtils::sendAjaxErrorMessage($ue->getMessage());
		}

		$userEntity = UserController::getUserEntityBy(get_current_user_id());

		if(null === $userEntity) //|| (null === $userRoleInstance = UltraCommHelper::getUserRolePublicInstanceByUserInfo($userEntity))
		{
			MchWpUtils::logOutCurrentUser();
			MchWpUtils::sendAjaxErrorMessage(__('Invalid username or password !', 'ultra-community'));
		}

		$arrResponse = array('userId' => get_current_user_id(), AjaxHandler::REQUEST_NONCE_KEY => AjaxHandler::getAjaxNonce());

		$redirectUrl = null;

		foreach(UserRoleController::getUserRolePostTypes($userEntity) as $userRolePostType)
		{
			if(null === ($userRoleInstance = PostTypeController::getAssociatedPublicModuleInstance($userRolePostType)))
				continue;

			$roleRedirectUrl = $userRoleInstance->getOption(UserRoleAdminModule::OPTION_AFTER_LOGIN_REDIRECT_URL);
			if(empty($roleRedirectUrl))
				continue;

			$redirectUrl = $roleRedirectUrl; break;

		}

		!empty($redirectUrl) ?: $redirectUrl = UltraCommHelper::getUserProfileUrl($userEntity);

//		$redirectUrl = !MchUtils::isNullOrEmpty($redirectUrl = $userRoleInstance->getOption(UserRoleAdminModule::OPTION_AFTER_LOGIN_REDIRECT_URL))
//			? $redirectUrl : UltraCommHelper::getUserProfileUrl($userEntity);

		!MchWpUtils::isAdminLoggedIn() ?: $redirectUrl = admin_url();

		$redirectUrl = empty($redirectUrl) ? home_url('/') : esc_url($redirectUrl);

		if(empty($_POST['isModalLogin']))
		{
			$arrResponse['redirectUrl'] = $redirectUrl;
		}

		MchWpUtils::sendAjaxSuccessMessage($arrResponse);

	}

	public static function userRegistration()
	{
		$pageInstance = FrontPageController::getFrontPageInstance( FrontPageController::PAGE_REGISTRATION);
		if ( empty( $_POST[ $pageInstance->getPostRequestActionKey() ] ))
			MchWpUtils::sendAjaxErrorMessage(__('Invalid registration request received!', 'ultra-community'));


		$publicRegisterModuleInstance = PostTypeController::getAssociatedPublicModuleInstance(PostTypeController::getPostTypeInstanceByPostId($_POST[$pageInstance->getPostRequestActionKey()]));
		if(! $publicRegisterModuleInstance instanceof RegisterFormPublicModule){
			MchWpUtils::sendAjaxErrorMessage(__('Invalid registration request!', 'ultra-community'));
		}

		$successMessage = $redirectTo = null;
		try
		{
			MchWpUtils::addActionHook(UltraCommHooks::ACTION_AFTER_USER_REGISTERED, function (UserEntity $userEntity = null, $arrAdditionalInfo) use(&$successMessage, &$redirectTo){

				empty($arrAdditionalInfo['successMessage']) ?: $successMessage = $arrAdditionalInfo['successMessage'];
				empty($arrAdditionalInfo['redirectTo'])     ?: $redirectTo     = $arrAdditionalInfo['redirectTo'];

			}, 99, 2);

			$publicRegisterModuleInstance->processUserRegistration();
		}
		catch(UltraCommException $ue)
		{
			MchWpUtils::logOutCurrentUser();
			MchWpUtils::sendAjaxErrorMessage($ue->getMessage());
		}


		$arrResponseData = array('message' => $successMessage);
		empty($redirectTo) ?: $arrResponseData['redirectUrl'] = $redirectTo;

		MchWpUtils::sendAjaxSuccessMessage($arrResponseData);

	}

	public static function userForgotPassword()
	{
		$pageInstance = FrontPageController::getFrontPageInstance( FrontPageController::PAGE_FORGOT_PASSWORD);
		if ( empty( $_POST[ $pageInstance->getPostRequestActionKey() ] ))
			MchWpUtils::sendAjaxErrorMessage(__('Invalid request received!', 'ultra-community'));


		$publicModuleInstance = PostTypeController::getAssociatedPublicModuleInstance(PostTypeController::getPostTypeInstanceByPostId($_POST[$pageInstance->getPostRequestActionKey()]));
		if(! $publicModuleInstance instanceof ForgotPasswordFormPublicModule){
			MchWpUtils::sendAjaxErrorMessage(__('Invalid request!', 'ultra-community'));
		}

		if(empty($_POST['txtPasswordResetUserToken']))
		{
			try
			{
				$publicModuleInstance->processForgotPassword();
			}
			catch ( UltraCommException $ue ) {
				MchWpUtils::sendAjaxErrorMessage( $ue->getMessage() );
			}

			MchWpUtils::sendAjaxSuccessMessage( __( 'An email with a link to reset your password has been sent to you!', 'ultra-community' ) );
		}


		// Reset Password

		if(empty($_POST['txtPasswordResetUserId']) || !MchValidator::isInteger($_POST['txtPasswordResetUserId'])){
			MchWpUtils::sendAjaxErrorMessage( __('Invalid password reset request!', 'ultra-community'));
		}

		if(!MchWpUtils::isWPUser($wpUser = WpUserRepository::getUserById($_POST['txtPasswordResetUserId'])) || !MchWpUtils::isWPUser(check_password_reset_key($_POST['txtPasswordResetUserToken'], $wpUser->user_login))){
			MchWpUtils::sendAjaxErrorMessage( __('Invalid password reset request!', 'ultra-community'));
		}


		!isset($_POST['txtPassword'])        ?: $_POST['txtPassword']        = trim($_POST['txtPassword']);
		!isset($_POST['txtConfirmPassword']) ?: $_POST['txtConfirmPassword'] = trim($_POST['txtConfirmPassword']);

		if(empty($_POST['txtPassword'])){
			MchWpUtils::sendAjaxErrorMessage(__('Please provide your new password!', 'ultra-community'));
			return;
		}

		if(empty($_POST['txtConfirmPassword'])){
			MchWpUtils::sendAjaxErrorMessage(__('Please confirm your new password!', 'ultra-community'));
			return;
		}

		if($_POST['txtPassword'] !== $_POST['txtConfirmPassword']){
			MchWpUtils::sendAjaxErrorMessage(__('Password and confirmed password do not match!', 'ultra-community'));
			return;
		}

		reset_password($wpUser, $_POST['txtPassword']);

		add_filter( 'send_password_change_email', '__return_false');

		NotificationsController::sendNotification(NotificationsController::NOTIFICATION_EMAIL_PASSWORD_CHANGED, UserRepository::getUserEntityBy($wpUser));

		MchWpUtils::sendAjaxSuccessMessage( __('Your password has been successfully changed!', 'ultra-community') );

	}





	public static function saveActivityComment()
	{
		$activityEntity = ActivityController::getActivityEntityByKey(empty($_POST['activityId']) ? null : (int)$_POST['activityId']);
		if(empty($_POST['txtCommentContent'])){
			MchWpUtils::sendAjaxErrorMessage(__('Please say something before posting!', 'ultra-community'));
		}
		if(empty($activityEntity->PostTypeId)){
			MchWpUtils::sendAjaxErrorMessage(__('Invalid request received!', 'ultra-community'));
		}

		$activityPostType = PostTypeController::getPostTypeInstanceByPostId($activityEntity->PostTypeId);
		if(empty($activityPostType->PostType)){
			MchWpUtils::sendAjaxErrorMessage(__('Invalid request received!', 'ultra-community'));
		}

		$arrCommentData = array(
			'comment_post_ID'      => $activityEntity->PostTypeId,
			'comment_author'       => '',
			'comment_author_email' => '',
			'comment_author_url'   => '',
			'comment_content'      => $_POST['txtCommentContent'],
			'comment_type'         => PostTypeController::POST_TYPE_ACTIVITY,
			'comment_parent'       => empty($_POST['txtCommentParentId']) ? 0 : (int)($_POST['txtCommentParentId']),
			'user_id'              => UserController::getLoggedInUserId()
		);

		if(!MchValidator::isInteger($insertedCommentId = wp_new_comment( $arrCommentData, true )) || MchUtils::isNullOrEmpty($activityComment = get_comment($insertedCommentId))){
			$errorMessage = ($insertedCommentId instanceof \WP_Error) ? $insertedCommentId->get_error_message() : __('An error was encountered while posting your comment!', 'ultra-community');
			MchWpUtils::sendAjaxErrorMessage($errorMessage);
		}

		ob_start();
		ActivityPage::renderActivityComment($activityComment, $activityEntity);
		$commentOutput =  ob_get_clean() . '</li>';
		empty($activityComment->comment_parent) ?: $commentOutput = '<ul class="children">' . $commentOutput . '</ul>';

		MchWpUtils::sendAjaxSuccessMessage(array('message' => esc_html__('Your comment has been submitted successfully!', 'ultra-community'), 'commentOutput' => $commentOutput));

	}



	public static function saveActivityPost()
	{
		//print_r(ModulesController::getRegisteredModules());exit;


		if(empty($_POST['activityPostTarget'])){
			MchWpUtils::sendAjaxErrorMessage(__('Invalid Activity Post Received!', 'ultra-community'));
		}

		if(empty($_POST['activityPostTarget'])){
			MchWpUtils::sendAjaxErrorMessage(__('Invalid Activity Post Received!', 'ultra-community'));
		}

		if('user' === $_POST['activityPostTarget']){
			if(!UserController::getProfiledUser()){
				MchWpUtils::sendAjaxErrorMessage(__('Invalid Activity Post Received! ', 'ultra-community'));
			}
		}

		if('group' === $_POST['activityPostTarget']){
			if(!GroupController::getProfiledGroup()){
				MchWpUtils::sendAjaxErrorMessage(__('Invalid Activity Post Received! ', 'ultra-community'));
			}
		}

		$activityPostType = PostTypeController::getPostTypeInstance(PostTypeController::POST_TYPE_ACTIVITY);
		$activityPostType->PostContent = empty($_POST['txtActivityPostContent']) ? null : $_POST['txtActivityPostContent'];

		$activityEntity = new ActivityEntity();

		$activityEntity->TargetTypeId = ('group' === $_POST['activityPostTarget']) ? ActivityEntity::ACTIVITY_TARGET_TYPE_GROUP : ActivityEntity::ACTIVITY_TARGET_TYPE_USER;
		$activityEntity->ActionTypeId = ActivityEntity::ACTION_TYPE_NEW_WALL_POST;
		$activityEntity->UserId       = UserController::getLoggedInUserId();
		$activityEntity->TargetId     = ('group' === $_POST['activityPostTarget']) ? GroupController::getProfiledGroup()->Id : UserController::getProfiledUser()->Id;

		$activityEntity->StatusId = ActivityEntity::ACTIVITY_STATUS_ACTIVE;

		$activityEntity->PostFormatTypeId = ActivityEntity::ACTIVITY_POST_FORMAT_STATUS;


		if($activityEntity->TargetTypeId === ActivityEntity::ACTIVITY_TARGET_TYPE_USER)
		{
			if($activityEntity->UserId !== $activityEntity->TargetId){
				MchWpUtils::sendAjaxErrorMessage(__('You are not allowed to post on this profile! ', 'ultra-community'));
			}
		}

		if($activityEntity->TargetTypeId === ActivityEntity::ACTIVITY_TARGET_TYPE_GROUP)
		{
			//to do: check if user can post
		}

		if(!empty($_POST['activityPostFormat']))
		{
			switch($_POST['activityPostFormat'])
			{
				case 'image' : $activityEntity->PostFormatTypeId  = ActivityEntity::ACTIVITY_POST_FORMAT_IMAGE;break;
				case 'file'  : $activityEntity->PostFormatTypeId  = ActivityEntity::ACTIVITY_POST_FORMAT_FILE;break;
				case 'audio' : $activityEntity->PostFormatTypeId  = ActivityEntity::ACTIVITY_POST_FORMAT_AUDIO;break;
				case 'video' : $activityEntity->PostFormatTypeId  = ActivityEntity::ACTIVITY_POST_FORMAT_VIDEO;break;
				case 'link'  : $activityEntity->PostFormatTypeId  = ActivityEntity::ACTIVITY_POST_FORMAT_LINK;break;
				case 'quote' : $activityEntity->PostFormatTypeId  = ActivityEntity::ACTIVITY_POST_FORMAT_QUOTE;break;
			}
		}


		$activityEntity->MetaData->PostFormat = $activityEntity->PostFormatTypeId;

		if($activityEntity->PostFormatTypeId === ActivityEntity::ACTIVITY_POST_FORMAT_QUOTE)
		{
			empty($_POST['txtQuoteAuthor']) ?: $activityEntity->MetaData->QuoteAuthor = MchWpUtils::sanitizeText($_POST['txtQuoteAuthor']);
			empty($_POST['txtQuoteText'])   ?: $activityEntity->MetaData->QuoteText   = MchWpUtils::sanitizeTextArea($_POST['txtQuoteText']);

			if(empty($activityEntity->MetaData->QuoteAuthor)){
				MchWpUtils::sendAjaxErrorMessage(__('Please specify the Author', 'ultra-community'));
			}

			if(empty($activityEntity->MetaData->QuoteText)){
				MchWpUtils::sendAjaxErrorMessage(__('Please specify the Quote', 'ultra-community'));
			}

		}

		if($activityEntity->PostFormatTypeId === ActivityEntity::ACTIVITY_POST_FORMAT_LINK)
		{
			empty($_POST['txtLinkUrl'])         ?: $activityEntity->MetaData->LinkUrl         = MchWpUtils::sanitizeText($_POST['txtLinkUrl']);
			empty($_POST['txtLinkTitle'])       ?: $activityEntity->MetaData->LinkTitle       = MchWpUtils::sanitizeText($_POST['txtLinkTitle']);
			empty($_POST['txtLinkDescription']) ?: $activityEntity->MetaData->LinkDescription = MchWpUtils::sanitizeTextArea($_POST['txtLinkDescription']);

			if(empty($activityEntity->MetaData->LinkUrl)){
				MchWpUtils::sendAjaxErrorMessage(__('Please specify the Link URL!', 'ultra-community'));
			}
			if(empty($activityEntity->MetaData->LinkTitle)){
				MchWpUtils::sendAjaxErrorMessage(__('Please specify the Title of this link!', 'ultra-community'));
			}
			if(empty($activityEntity->MetaData->LinkDescription)){
				MchWpUtils::sendAjaxErrorMessage(__('Please type a brief description of this link', 'ultra-community'));
			}

		}


		if($activityEntity->PostFormatTypeId === ActivityEntity::ACTIVITY_POST_FORMAT_STATUS && empty($activityPostType->PostContent)){
			MchWpUtils::sendAjaxErrorMessage(__('Please type something before posting', 'ultra-community'));
		}

		$_POST['uploadedFiles'] = isset($_POST['uploadedFiles']) ? (array)json_decode($_POST['uploadedFiles']) : array();


		if(empty($_POST['uploadedFiles']) && in_array($activityEntity->PostFormatTypeId, array(ActivityEntity::ACTIVITY_POST_FORMAT_IMAGE, ActivityEntity::ACTIVITY_POST_FORMAT_FILE, ActivityEntity::ACTIVITY_POST_FORMAT_AUDIO, ActivityEntity::ACTIVITY_POST_FORMAT_VIDEO)))
		{
			MchWpUtils::sendAjaxErrorMessage(__('Please upload something before posting', 'ultra-community'));
		}


		try
		{
			$activityEntity->PostTypeId = PostTypeController::publishPostType($activityPostType);

			if(empty($activityEntity->PostTypeId)){
				MchWpUtils::sendAjaxErrorMessage(__('An error was encountered while saving your post', 'ultra-community'));
			}

			$activityEntity = ActivityController::getActivityEntityByKey(ActivityController::saveActivity($activityEntity));

			if(!empty($_POST['uploadedFiles']) && !in_array($activityEntity->PostFormatTypeId, array(ActivityEntity::ACTIVITY_POST_FORMAT_STATUS,  ActivityEntity::ACTIVITY_POST_FORMAT_QUOTE)))
			{
				$tempUploadsDirectoryPath = UltraCommUtils::getTempUploadsDirectoryPath();
				$activityUploadsDirectoryPath = UltraCommUtils::getActivityAttachmentsBaseDirectoryPath($activityEntity);
				MchDirectoryUtils::createDirectory($activityUploadsDirectoryPath);

				foreach ($_POST['uploadedFiles'] as $uploadedFile)
				{
					if(empty($uploadedFile->name) || empty($uploadedFile->type))
						continue;

					if(!MchFileUtils::fileExists($tempUploadsDirectoryPath . DIRECTORY_SEPARATOR . $uploadedFile->name)){
						continue;
					}

					$arrFileInfo = wp_check_filetype($tempUploadsDirectoryPath . DIRECTORY_SEPARATOR . $uploadedFile->name);

					if(empty($arrFileInfo['type']))
						continue;

					$arrFileInfo['type'] = strtolower($arrFileInfo['type']);

					switch($activityEntity->PostFormatTypeId)
					{
						case ActivityEntity::ACTIVITY_POST_FORMAT_IMAGE :
						case ActivityEntity::ACTIVITY_POST_FORMAT_LINK  :
							if(false === strpos($arrFileInfo['type'], 'image/'))
								continue 2;
							break;
						case ActivityEntity::ACTIVITY_POST_FORMAT_AUDIO :
							if(false === strpos($arrFileInfo['type'], 'audio/'))
								continue 2;
							break;

						case ActivityEntity::ACTIVITY_POST_FORMAT_VIDEO :
							if(false === strpos($arrFileInfo['type'], 'video/'))
								continue 2;
							break;

					}

					$activityAttachment = new \stdClass();

					$activityAttachment->Name = wp_unique_filename($activityUploadsDirectoryPath, $uploadedFile->name);
					$activityAttachment->Type = MchWpUtils::sanitizeText($uploadedFile->type);

					if(0 === strpos($arrFileInfo['type'], 'image'))
					{
						$arrImageSizes = MchImageUtils::getSize($tempUploadsDirectoryPath . DIRECTORY_SEPARATOR . $uploadedFile->name);

						if(empty($arrImageSizes['width']) || empty($arrImageSizes['height'])){
							continue; //not valid image
						}

						$activityAttachment->Width  = $arrImageSizes['width'];
						$activityAttachment->Height = $arrImageSizes['height'];
					}



					if(!@rename($tempUploadsDirectoryPath . DIRECTORY_SEPARATOR . $uploadedFile->name, $activityUploadsDirectoryPath . DIRECTORY_SEPARATOR . $activityAttachment->Name)){
						continue;
					}

					$activityEntity->MetaData->Attachments[] = $activityAttachment;

				}
			}

			ActivityController::saveActivity($activityEntity);

		}
		catch(UltraCommException $ue)
		{
			MchWpUtils::sendAjaxErrorMessage($ue->getMessage());
		}

		MchWpUtils::sendAjaxSuccessMessage(MchUtils::captureOutputBuffer(function () use ($activityEntity){
			ActivityPage::renderActivityEntity(ActivityController::getActivityEntityByKey($activityEntity->ActivityId));
		}, true));


	}


	public static function saveActivityLike()
	{
		if(empty($_POST['activityId']) || !MchValidator::isInteger($_POST['activityId']) || !UserController::isUserLoggedIn() || !ModulesController::isModuleRegistered(ModulesController::MODULE_EXTENDED_ACTIVITY)){
			MchWpUtils::sendAjaxErrorMessage(__('Invalid Request Received!', 'ultra-community'));
		}

		if(null === ($activityEntity = ActivityController::getActivityEntityByKey($_POST['activityId']))){
			MchWpUtils::sendAjaxErrorMessage(__('Invalid Request Received!', 'ultra-community'));
		}

		$activityEntity->MetaData->UserLikes = empty($activityEntity->MetaData->UserLikes) ? array() : (array)$activityEntity->MetaData->UserLikes;
		$activityEntity->MetaData->UserLikes[UserController::getLoggedInUserId()] = true;

		$activityEntity->MetaData->UserLikes = array_reverse($activityEntity->MetaData->UserLikes, true);

		$countActivityLikes = count($activityEntity->MetaData->UserLikes);

		ActivityController::saveActivity($activityEntity);

		MchWpUtils::sendAjaxSuccessMessage(array('newText' => sprintf( _n( '%s Like', '%s Likes', $countActivityLikes, 'ultra-community' ), number_format_i18n( $countActivityLikes ) ) ));
	}



	public static function deleteUserActivity()
	{
		if(empty($_POST['activityId']) || !MchValidator::isInteger($_POST['activityId']) || !UserController::isUserLoggedIn()){
			MchWpUtils::sendAjaxErrorMessage(__('Invalid Request Received!', 'ultra-community'));
		}

		if(null === ($activityEntity = ActivityController::getActivityEntityByKey($_POST['activityId']))){
			MchWpUtils::sendAjaxErrorMessage(__('Invalid Request Received!', 'ultra-community'));
		}

		if(!ActivityController::userCanManageActivity(UserController::getLoggedInUser(), $activityEntity)){
			MchWpUtils::sendAjaxErrorMessage(__('This activity cannot be deleted!', 'ultra-community'));
		}

		$activityPostType = PostTypeController::getPostTypeInstanceByPostId($activityEntity->PostTypeId);
		if(empty($activityPostType->PostType) || $activityPostType->PostType !== PostTypeController::POST_TYPE_ACTIVITY){
			MchWpUtils::sendAjaxErrorMessage(__('This activity cannot be deleted!', 'ultra-community'));
		}

		WpPostRepository::delete($activityPostType->PostId);

		MchWpUtils::sendAjaxSuccessMessage('');

	}


	public static function getUserProfileActivityPerPage()
	{

		if(!UserController::getProfiledUser()){
			MchWpUtils::sendAjaxErrorMessage(__("Invalid request received!", 'ultra-community'));
		}

		$pageNumber = empty($_POST['pageNumber']) || !MchValidator::isInteger($_POST['pageNumber']) ? 2 : (int)$_POST['pageNumber'];

		$arrTemplateArguments = array(
			'currentPage' => $pageNumber,
		);

		$arrActivityEntities = UserController::isCurrentUserBrowsingOwnProfile()
				? ActivityController::getUserProfileActivityFeed(UserController::getProfiledUser(), $pageNumber, 10)
				: ActivityController::getUserProfileActivityList(UserController::getProfiledUser(), $pageNumber, 10);

		if(empty($arrActivityEntities))
		{
			MchWpUtils::sendAjaxErrorMessage('no more');
		}

		$arrTemplateArguments['arrActivityEntities'] = $arrActivityEntities;

		echo TemplatesController::getTemplateOutputContent(TemplatesController::USER_PROFILE_ACTIVITY_TEMPLATE, $arrTemplateArguments);

	}




	public static function changeGroupUserStatus()
	{
		if( empty($_POST['status']) || empty($_POST['userId']) || MchUtils::isNullOrEmpty(GroupController::getProfiledGroup())){
			MchWpUtils::sendAjaxErrorMessage(__("Invalid request received!", 'ultra-community'));
		}

		$groupUserEntity = GroupController::getGroupUserEntity(GroupController::getProfiledGroup(), $_POST['userId']);

		if(null === $groupUserEntity){
			MchWpUtils::sendAjaxErrorMessage(__("Invalid request received!", 'ultra-community'));
		}

		if(GroupController::isUserGroupAdmin($groupUserEntity->UserId, $groupUserEntity->GroupId)){
			MchWpUtils::sendAjaxErrorMessage(__("Invalid request received!", 'ultra-community'));
		}

		if(!GroupController::userCanManageGroupUsers(UserController::getLoggedInUser(), GroupController::getProfiledGroup())){
			MchWpUtils::sendAjaxErrorMessage(__("You don't have permission to manage group users!", 'ultra-community'));
		}

		if($_POST['status'] === 'deleteUser')
		{
			if(GroupController::userCanEditGroup($groupUserEntity->UserId, $groupUserEntity->GroupId)){
				MchWpUtils::sendAjaxErrorMessage(__("User cannot be deleted", 'ultra-community'));
			}

			GroupRepository::deleteGroupUser($groupUserEntity->GroupId, $groupUserEntity->UserId);
			MchWpUtils::sendAjaxSuccessMessage( __('Deleted', 'ultra-community') );
		}

		if($_POST['status'] === 'declineJoinRequest')
		{
			if(GroupController::userCanEditGroup($groupUserEntity->UserId, $groupUserEntity->GroupId)){
				MchWpUtils::sendAjaxErrorMessage(__("Cannot decline request", 'ultra-community'));
			}

			GroupRepository::deleteGroupUser($groupUserEntity->GroupId, $groupUserEntity->UserId);
			MchWpUtils::sendAjaxSuccessMessage( __('Declined', 'ultra-community') );
		}


		$successMessage = null;
		switch($_POST['status'])
		{
			case 'blockUser'         : $groupUserEntity->UserStatusId = GroupUserEntity::GROUP_USER_STATUS_BLOCKED; $successMessage = __('Blocked', 'ultra-community'); break;
			case 'unBlockUser'       : $groupUserEntity->UserStatusId = GroupUserEntity::GROUP_USER_STATUS_ACTIVE;  $successMessage = __('UnBlocked', 'ultra-community'); break;
			case 'acceptJoinRequest' : $groupUserEntity->UserStatusId = GroupUserEntity::GROUP_USER_STATUS_ACTIVE;  $successMessage = __('Accepted', 'ultra-community');break;
		}

		if( $groupUserEntity->UserStatusId === GroupUserEntity::GROUP_USER_STATUS_BLOCKED )
		{
			if(GroupController::userCanEditGroup($groupUserEntity->UserId, $groupUserEntity->GroupId)){
				MchWpUtils::sendAjaxErrorMessage(__("Cannot block this user", 'ultra-community'));
			}

		}


		GroupRepository::saveGroupUser($groupUserEntity);

		MchWpUtils::sendAjaxSuccessMessage($successMessage);


	}

	public static function userJoinGroup()
	{

		if(empty($_POST['groupId']) || !MchValidator::isInteger($_POST['groupId'])){
			MchWpUtils::sendAjaxErrorMessage(__('Invalid request received!', 'ultra-community'));
		}

		if(!GroupController::userCanJoinGroup(UserController::getLoggedInUser(), $_POST['groupId'])){
			MchWpUtils::sendAjaxErrorMessage(__('You can\'t join this group!', 'ultra-community'));
		}

		$groupUserEntity = new GroupUserEntity();
		$groupUserEntity->GroupId      = $_POST['groupId'];
		$groupUserEntity->UserId       = UserController::getLoggedInUser()->Id;
		$groupUserEntity->UserTypeId   = GroupUserEntity::GROUP_USER_TYPE_MEMBER;

		switch (GroupController::getGroupTypeId($_POST['groupId']))
		{
			case GroupEntity::GROUP_TYPE_PUBLIC :
				$groupUserEntity->UserStatusId = GroupUserEntity::GROUP_USER_STATUS_ACTIVE;
				break;

			case GroupEntity::GROUP_TYPE_PRIVATE :
				$groupUserEntity->UserStatusId = GroupUserEntity::GROUP_USER_STATUS_PENDING;
				break;

			case GroupEntity::GROUP_TYPE_SECRET :

				switch (GroupController::getGroupUserStatusId($groupUserEntity->GroupId, UserController::getLoggedInUser()->Id))
				{
					case GroupUserEntity::GROUP_USER_STATUS_INVITED:
						$groupUserEntity->UserStatusId = GroupUserEntity::GROUP_USER_STATUS_ACTIVE;
						break;
				}

				break;
		}

		if(empty($groupUserEntity->UserStatusId))
		{
			MchWpUtils::sendAjaxErrorMessage(__('You can\'t join this group!', 'ultra-community'));
		}

		try
		{
			GroupController::saveGroupUser($groupUserEntity);
		}
		catch (UltraCommException $ue)
		{
			MchWpUtils::sendAjaxErrorMessage($ue->getMessage());
		}

		switch ($groupUserEntity->UserStatusId)
		{
			case GroupUserEntity::GROUP_USER_STATUS_ACTIVE :
				MchWpUtils::sendAjaxSuccessMessage( __('Joined', 'ultra-community') );
				break;

			case GroupUserEntity::GROUP_USER_STATUS_PENDING :
				MchWpUtils::sendAjaxSuccessMessage( __('Request Sent', 'ultra-community') );
				break;

		}

		MchWpUtils::sendAjaxSuccessMessage( __('Request Sent', 'ultra-community') );
	}

	public static function userLeaveGroup()
	{

		if(empty($_POST['groupId']) || !MchValidator::isInteger($_POST['groupId']) || !UserController::isUserLoggedIn()){
			MchWpUtils::sendAjaxErrorMessage(__('Invalid request received!', 'ultra-community'));
		}

		if(GroupController::isUserGroupAdmin(UserController::getLoggedInUser(), $_POST['groupId'])){
			MchWpUtils::sendAjaxErrorMessage(__('You cannot leave your own group', 'ultra-community'));
		}

		try
		{
			GroupRepository::deleteGroupUser($_POST['groupId'], UserController::getLoggedInUser()->Id);
		}
		catch(UltraCommException $ue)
		{
			MchWpUtils::sendAjaxErrorMessage($ue->getMessage());
		}

		MchWpUtils::sendAjaxSuccessMessage(__('Unsubscribed', 'ultra-community'));

	}


	public static function getDirectoryListingPerPage()
	{
		if(empty($_POST['directoryId']) || !MchValidator::isInteger($_POST['directoryId'])){
			MchWpUtils::sendAjaxErrorMessage(__("Invalid request received!", 'ultra-community'));
		}

		$pageNumber = empty($_POST['pageNumber']) || !MchValidator::isInteger($_POST['pageNumber']) ? 2 : (int)$_POST['pageNumber'];

		$frontPageInstance = FrontPageController::getPageInstanceByCustomPostTypeId($_POST['directoryId']);

		if( (!$frontPageInstance instanceof  MembersDirectoryPage) && (!$frontPageInstance instanceof  GroupsDirectoryPage) )
			MchWpUtils::sendAjaxErrorMessage(__("Invalid request received!", 'ultra-community'));

		$frontPageInstance->setDirectoryPageNumber($pageNumber);

		$outputContent = $frontPageInstance->getContentMarkup();

		if(empty($outputContent)){
			MchWpUtils::sendAjaxErrorMessage(__("No more directory members!", 'ultra-community'));
		}

		echo $outputContent;
	}


	public static function getUserPostsPerPage()
	{
		if(null === UserController::getProfiledUser()){
			MchWpUtils::sendAjaxErrorMessage('error');
		}

		$userProfilePage = new UserProfilePage();
		$userProfilePage->setActiveSectionSlug(UserProfileAppearanceAdminModule::PROFILE_SECTION_POSTS);

		$userProfilePage->renderProfileSectionContent();

		exit;
	}

	public static function getUserCommentsPerPage()
	{
		if(null === UserController::getProfiledUser()){
			MchWpUtils::sendAjaxErrorMessage('error');
		}

		$userProfilePage = new UserProfilePage();
		$userProfilePage->setActiveSectionSlug(UserProfileAppearanceAdminModule::PROFILE_SECTION_COMMENTS);

		$userProfilePage->renderProfileSectionContent();

		exit;
	}

	public static function updateUserProfile()
	{

		if( ! UserController::currentUserCanEditProfile() ){
			MchWpUtils::sendAjaxErrorMessage(__("You're not allowed to edit this profile!", 'ultra-community'));
		}

		if(MchUtils::isNullOrEmpty(UserController::getProfiledUser())){
			MchWpUtils::sendAjaxErrorMessage(__("Invalid update request received!", 'ultra-community'));
		}

		foreach (UserController::getProfiledUserProfileFormFields() as $formField)
		{
			try
			{

				if(!isset($_POST[$formField->UniqueId]))
					continue;

				$formField->Value = $_POST[$formField->UniqueId] ; //isset($_POST[$formField->UniqueId]) ? $_POST[$formField->UniqueId] : null;

				if($formField instanceof CheckBoxField)
				{
					$formField->Value = array_filter(empty($formField->Value) ? array() : (array)$formField->Value, function($checkValue){return filter_var($checkValue, FILTER_VALIDATE_BOOLEAN);});
					$formField->Value = array_keys($formField->Value);
				}

				if(!$formField->isValid(true))
					continue;

				if(!UserController::currentUserCanEditProfileFormField($formField)){
					continue;
				}

				$mappedProperty = $formField->getUserEntityMappedFieldName();
				!MchUtils::stringStartsWith($mappedProperty, '_meta_') ?: $mappedProperty = str_replace('_meta_','', $mappedProperty);

				if(!empty($mappedProperty))
				{
					if (property_exists(UserController::getProfiledUser()->UserMetaEntity, $mappedProperty)) {
						UserController::getProfiledUser()->UserMetaEntity->{$mappedProperty} = $formField->Value;
					}
					elseif (property_exists(UserController::getProfiledUser(), $mappedProperty)){
						UserController::getProfiledUser()->{$mappedProperty} = $formField->Value;
					}
				}
				elseif (!empty($formField->MappedUserMetaKey))
				{
					update_user_meta(UserController::getProfiledUser()->Id, $formField->MappedUserMetaKey, $formField->Value);
					continue;
				}
				else
				{
					UserController::getProfiledUser()->UserMetaEntity->ProfileFormValues[$formField->UniqueId] = $formField->Value;
				}

				if(MchWpUtils::isAdminUser(UserController::getProfiledUser()->Id))
				{
					if($formField instanceof UserEmailField){
						if( strcasecmp(UserController::getProfiledUser()->Email, $formField->Value) !== 0){
							MchWpUtils::sendAjaxErrorMessage(__("For security reasons Admins are not allowed to change Email Address!", 'ultra-community'));
						}
					}

				}

				if(MchWpUtils::isAdminUser(UserController::getProfiledUser()->Id))
				{
					if($formField instanceof UserNameField){
						if( strcmp(UserController::getProfiledUser()->UserName, $formField->Value) !== 0 ){
							MchWpUtils::sendAjaxErrorMessage(__("For security reasons Admins are not allowed to change UserName!", 'ultra-community'));
						}
					}

				}

				UserController::saveUserInfo(UserController::getProfiledUser());

			}
			catch(UltraCommException $ue)
			{
				MchWpUtils::sendAjaxErrorMessage($ue->getMessage());
			}

		}

		MchWpUtils::sendAjaxSuccessMessage(__('Your changes were successfully saved!', 'ultra-community'));

	}



	public static function updateUserAccountPrivacy()
	{

		if(!UserController::isCurrentUserBrowsingOwnProfile() && !MchWpUtils::isAdminLoggedIn()){
			MchWpUtils::sendAjaxErrorMessage(__("You're not allowed to edit this profile!", 'ultra-community'));
		}

		if(empty($profiledUserId = UserController::getProfiledUser() ? UserController::getProfiledUser()->Id : null)){
			MchWpUtils::sendAjaxErrorMessage(__("You're not allowed to edit this profile!", 'ultra-community'));
		}

		$arrUserPrivacy = array(UserPrivacyEntity::META_KEY_PROFILE_VISIBILITY, UserPrivacyEntity::META_KEY_HIDE_IN_DIRECTORIES, UserPrivacyEntity::META_KEY_HIDE_IN_SEARCHES, UserPrivacyEntity::META_KEY_HIDE_ONLINE_STATUS);
		$arrUserPrivacy = array_fill_keys($arrUserPrivacy, 0);

		foreach(array_keys($arrUserPrivacy) as $privacyMetaKey){
			WpUserRepository::deleteUserMeta($profiledUserId, $privacyMetaKey);
		}

		if(!empty($_POST['selectProfileVisibility']) && isset(UserPrivacyEntity::getProfileVisibilityOptions()[$_POST['selectProfileVisibility']])){
			((int)$_POST['selectProfileVisibility'] === UserPrivacyEntity::PROFILE_VISIBILITY_EVERYONE) ?: $arrUserPrivacy[UserPrivacyEntity::META_KEY_PROFILE_VISIBILITY] = (int)$_POST['selectProfileVisibility'];
		}

		$_POST['userMetaPrivacy'] = empty($_POST['userMetaPrivacy']) ? array() : (array)$_POST['userMetaPrivacy'];
		foreach($_POST['userMetaPrivacy'] as $metaKey => $metaValue)
		{
			!isset($arrUserPrivacy[$metaKey]) ?: $arrUserPrivacy[$metaKey] = filter_var($metaValue, FILTER_VALIDATE_BOOLEAN);
		}

		$arrUserPrivacy = array_filter($arrUserPrivacy);

		foreach(array_filter($arrUserPrivacy) as $metaKey => $metaValue)
		{
			WpUserRepository::saveUserMeta($profiledUserId, $metaKey, $metaValue);
		}

		MchWpUtils::sendAjaxSuccessMessage(__('Your changes were successfully saved!', 'ultra-community'));

	}


	public static function updateUserAccountSettings()
	{
		if( ! UserController::currentUserCanEditProfile() ){
			MchWpUtils::sendAjaxErrorMessage(__("You're not allowed to edit this profile!", 'ultra-community'));
		}

		if(MchUtils::isNullOrEmpty(UserController::getProfiledUser())){
			MchWpUtils::sendAjaxErrorMessage(__("Invalid update request received!", 'ultra-community'));
		}

		if(!empty($_POST['uc-user-first-name'])){
			UserController::getProfiledUser()->UserMetaEntity->FirstName = MchWpUtils::sanitizeText($_POST['uc-user-first-name']);
		}

		if(!empty($_POST['uc-user-last-name'])){
			UserController::getProfiledUser()->UserMetaEntity->LastName = MchWpUtils::sanitizeText($_POST['uc-user-last-name']);
		}

		if(!empty($_POST['uc-user-email-address']))
		{

			if(!MchValidator::isEmail($userEmailAddress = MchWpUtils::sanitizeEmail($_POST['uc-user-email-address']) )){
				MchWpUtils::sendAjaxErrorMessage( __( "Please provide a valid Email Address!", 'ultra-community' ) );
			}

			if(MchWpUtils::isAdminUser(UserController::getProfiledUser()->Id))
			{
				if( strcasecmp( UserController::getProfiledUser()->Email, $userEmailAddress) !== 0 )
				{
					MchWpUtils::sendAjaxErrorMessage( __( "For security reasons Admins are not allowed to change Email Address!", 'ultra-community' ) );
				}
			}

			UserController::getProfiledUser()->Email = $userEmailAddress;
		}

		try
		{
			UserController::saveUserInfo(UserController::getProfiledUser());
		}
		catch(UltraCommException $ue)
		{
			MchWpUtils::sendAjaxErrorMessage($ue->getMessage());
		}

		MchWpUtils::sendAjaxSuccessMessage(__('Your changes were successfully saved!', 'ultra-community'));
	}

//	public static function deleteUserAvatar()
//	{
//		if(! UserController::getProfiledUser() instanceof UserEntity){
//			MchWpUtils::sendAjaxErrorMessage(__('Invalid request received', 'ultra-community'));
//		}
//
//		if(!UserController::currentUserCanEditProfile()){
//			MchWpUtils::sendAjaxErrorMessage(__("You're not allowed to edit this profile!", 'ultra-community'));
//		}
//
//
//		MchDirectoryUtils::deleteDirectory(UltraCommUtils::getAvatarBaseDirectoryPath(UserController::getProfiledUser()));
//
//		UserController::getProfiledUser()->UserMetaEntity->AvatarFileName = null;
//
//		try
//		{
//			UserController::saveUserInfo( UserController::getProfiledUser() );
//		}
//		catch(UltraCommException $ue)
//		{
//			MchWpUtils::sendAjaxErrorMessage($ue->getMessage());
//		}
//
//		MchWpUtils::sendAjaxSuccessMessage(array('userAvatarUrl' => UltraCommHelper::getUserDefaultAvatarUrl()));
//	}
//
//
//	public static function deleteUserCover()
//	{
//		if(! UserController::getProfiledUser() instanceof UserEntity){
//			MchWpUtils::sendAjaxErrorMessage(__('Invalid request received', 'ultra-community'));
//		}
//
//		if(!UserController::currentUserCanEditProfile()){
//			MchWpUtils::sendAjaxErrorMessage(__("You're not allowed to edit this profile!", 'ultra-community'));
//		}
//
//
//		MchDirectoryUtils::deleteDirectory(UltraCommUtils::getProfileCoverBaseDirectoryPath(UserController::getProfiledUser()));
//
//		UserController::getProfiledUser()->UserMetaEntity->ProfileCoverFileName = null;
//
//		try
//		{
//			UserController::saveUserInfo( UserController::getProfiledUser() );
//		}
//		catch(UltraCommException $ue)
//		{
//			MchWpUtils::sendAjaxErrorMessage($ue->getMessage());
//		}
//
//		MchWpUtils::sendAjaxSuccessMessage(array('userCoverUrl' => UltraCommHelper::getUserDefaultCoverUrl()));
//
//	}



	public static function saveUserGroup()
	{

		$isCreatingGroup = empty($_POST['uc-Id']);

		try
		{
			$groupEntity = new GroupEntity();

			if(empty($isCreatingGroup))
			{
				$groupEntity = GroupController::getGroupEntityBy($_POST['uc-Id']);
				if(empty($groupEntity->AdminUserId)){
					MchWpUtils::sendAjaxErrorMessage(__('Invalid request received', 'ultra-community'));
				}

				//$groupEntity->AdminUserId = $savedGroupEntity->AdminUserId;
			}

			!empty($groupEntity->AdminUserId) ?: $groupEntity->AdminUserId = UserController::getProfiledUser()->Id;


			$groupEntity = MchUtils::populateObjectFromArray($groupEntity, $_POST, 'uc-', '');

			empty($_POST['uc-Description']) ?: $groupEntity->Description = $_POST['uc-Description'];

			$groupEntityId = GroupController::saveGroup($groupEntity);
			if(empty($groupEntityId))
			{
				$message = $isCreatingGroup ? __('An error was encountered while trying to create this group', 'ultra-community') : __('An error was encountered while trying to save changes for this group', 'ultra-community');
				MchWpUtils::sendAjaxErrorMessage($message);
			}

		}
		catch (UltraCommException $ue)
		{
			MchWpUtils::sendAjaxErrorMessage($ue->getMessage());
		}


		if($isCreatingGroup)
		{
			MchWpUtils::sendAjaxSuccessMessage(array('message' =>  __('Group was successfully created!', 'ultra-community'), 'redirectUrl' => FrontPageController::getUserSettingsPageUrlBySection(UserSettingsPage::SETTINGS_SECTION_GROUPS_EDIT_GROUP, UserController::getProfiledUser()->NiceName, $groupEntityId)));
		}

		MchWpUtils::sendAjaxSuccessMessage(__('Your changes were successfully saved!', 'ultra-community'));
	}

	public static function deleteUserGroup()
	{

		if(! GroupController::getProfiledGroup() ){
			MchWpUtils::sendAjaxErrorMessage(__('Invalid request received', 'ultra-community'));
		}

		if(empty($_POST['uc-checkConfirmation']))
		{
			MchWpUtils::sendAjaxErrorMessage(__('Please confirm you want to delete this group', 'ultra-community'));
		}

		try
		{
			GroupController::deleteGroup(GroupController::getProfiledGroup()->Id);
		}
		catch (UltraCommException $ue)
		{
			MchWpUtils::sendAjaxErrorMessage($ue->getMessage());
		}

		MchWpUtils::sendAjaxSuccessMessage(__('Group was successfully deleted!', 'ultra-community'));
	}


	public static function uploadTemporaryFile()
	{

		if(null !== ($tempUploadsDirectoryPath = UltraCommUtils::getTempUploadsDirectoryPath())){
			foreach (new \DirectoryIterator($tempUploadsDirectoryPath) as $fileInfo){
				if (!$fileInfo->isDot() && $fileInfo->isFile() && time() - $fileInfo->getCTime() >= 1 * 1 * 60 * 60) {
					@unlink($fileInfo->getRealPath());
				}
			}
		}

		if(empty($_FILES['ucTempFile'])){
			return;
		}

		UltraCommUtils::registerTempUploadsDirectoryFilter();

		function_exists('wp_handle_upload') || require_once( ABSPATH . 'wp-admin/includes/file.php' );

		empty($_FILES['ucTempFile']['name']) ?: $_FILES['ucTempFile']['name'] = strtolower($_FILES['ucTempFile']['name']);

		$arrUploadedFileInfo = wp_handle_upload( $_FILES['ucTempFile'], array('test_form' => false));
		UltraCommUtils::removeTempUploadsDirectoryFilter();


		$arrResponse = array();
		if($arrUploadedFileInfo && !isset($arrUploadedFileInfo['error']))
		{
			$arrResponse['name'] = wp_basename($arrUploadedFileInfo['file']);
			$arrResponse['url']  = $arrUploadedFileInfo['url'];
			$arrResponse['type'] = $arrUploadedFileInfo['type'];
			$arrResponse['size'] = filesize($arrUploadedFileInfo['file']);

		}
		else
		{
			$arrResponse['error'] = empty($arrUploadedFileInfo['error']) ? __('An error was encountered while uploading the file!', 'ultra-community') : $arrUploadedFileInfo['error'];
		}

		wp_send_json(array('files' => array($arrResponse)));

	}


	public static function updateUserPassword()
	{
		if( ! UserController::currentUserCanEditProfile() ){
			MchWpUtils::sendAjaxErrorMessage(__("You're not allowed to edit this profile!", 'ultra-community'));
		}

		if(MchWpUtils::isAdminUser(UserController::getProfiledUser()->Id) ){
			MchWpUtils::sendAjaxErrorMessage(__("For security reasons Admins are not allowed to change their password!", 'ultra-community'));
		}

		if(!UserController::isCurrentUserBrowsingOwnProfile()){
			MchWpUtils::sendAjaxErrorMessage(__("You're not allowed to change the password!", 'ultra-community'));
		}

		if(MchUtils::isNullOrEmpty(UserController::getProfiledUser())){
			MchWpUtils::sendAjaxErrorMessage(__("Invalid update request received!", 'ultra-community'));
		}

		if(empty($_POST['uc-user-current-password'])){
			MchWpUtils::sendAjaxErrorMessage(__("Please provide your current password!", 'ultra-community'));
		}

		if(empty($_POST['uc-user-new-password'])){
			MchWpUtils::sendAjaxErrorMessage(__("Please provide your new password!", 'ultra-community'));
		}

		if(empty($_POST['uc-user-confirm-password'])){
			MchWpUtils::sendAjaxErrorMessage(__("Please confirm your new password!", 'ultra-community'));
		}

		if(0 !== strcmp($_POST['uc-user-new-password'], $_POST['uc-user-confirm-password'])){
			MchWpUtils::sendAjaxErrorMessage(__("Your confirmed password does not match!", 'ultra-community'));
		}


		$profiledUser = WpUserRepository::getUserById(UserController::getProfiledUser()->Id);
		if(!MchWpUtils::isWPUser($profiledUser) || !wp_check_password($_POST['uc-user-current-password'], $profiledUser->user_pass, $profiledUser->ID)){
			MchWpUtils::sendAjaxErrorMessage(__("Invalid current password received!", 'ultra-community'));
		}

		if(!UserRoleController::currentUserCanChangePassword()){
			MchWpUtils::sendAjaxErrorMessage(__("You're not allowed to change your password!", 'ultra-community'));
		}

		reset_password($profiledUser, $_POST['uc-user-new-password']);
		wp_signon(
				array(
						'user_login'    => $profiledUser->user_login,
						'user_password' => $_POST['uc-user-new-password'],
				)
		);

		MchWpUtils::sendAjaxSuccessMessage(__('Your password was successfully changed!', 'ultra-community'));

	}

	public static function deleteUserAccount()
	{

		if( ! UserController::currentUserCanEditProfile() ){
			MchWpUtils::sendAjaxErrorMessage(__("You're not allowed to delete your account!", 'ultra-community'));
		}

		if(MchUtils::isNullOrEmpty(UserController::getProfiledUser())){
			MchWpUtils::sendAjaxErrorMessage(__("Invalid request received!", 'ultra-community'));
		}

		if(MchWpUtils::isAdminUser(UserController::getProfiledUser()->Id)){
			MchWpUtils::sendAjaxErrorMessage(__("For security reasons Admin account cannot be deleted!", 'ultra-community'));
		}

		if(empty($_POST['uc-user-current-password'])){
			MchWpUtils::sendAjaxErrorMessage(__("Please provide your current password!", 'ultra-community'));
		}

		if(!UserRoleController::currentUserCanDeleteAccount()){
			MchWpUtils::sendAjaxErrorMessage(__("You're not allowed to delete your account!", 'ultra-community'));
		}

		if(!UserController::isCurrentUserBrowsingOwnProfile()){
			MchWpUtils::sendAjaxErrorMessage(__("You're not allowed to delete this account!", 'ultra-community'));
		}

		$profiledUser = WpUserRepository::getUserById(UserController::getProfiledUser()->Id);
		if(!MchWpUtils::isWPUser($profiledUser) || !wp_check_password($_POST['uc-user-current-password'], $profiledUser->user_pass, $profiledUser->ID)){
			MchWpUtils::sendAjaxErrorMessage(__("Invalid current password received!", 'ultra-community'));
		}

		if((int)UserController::getProfiledUser()->Id === UserController::getLoggedInUserId())
		{
			MchWpUtils::logOutCurrentUser();
		}


		function_exists('wp_delete_user') ?: require_once( ABSPATH . 'wp-admin/includes/user.php' );

		if ( is_multisite() )
		{
			function_exists('wpmu_delete_user') || require_once( ABSPATH . 'wp-admin/includes/ms.php' );
			wpmu_delete_user(UserController::getProfiledUser()->Id );
		}
		else
		{
			wp_delete_user( UserController::getProfiledUser()->Id );
		}

		MchWpUtils::sendAjaxSuccessMessage(array('message' => __('Your account was successfully deleted!', 'ultra-community'), 'redirectUrl' => home_url()));

	}

//	public static function saveUserPostSubmission()
//	{
//		if(!ModulesController::isModuleRegistered(ModulesController::MODULE_POST_SUBMISSIONS))
//			return;
//
//		$arrResponseData = array('message' => __('Your submission was successfully saved!', 'ultra-community'));
//		try
//		{
//			$arrResponseData = array_merge(PostSubmissionsPublicModule::getInstance()->saveUserPostSubmission(), $arrResponseData);
//		}
//		catch (UltraCommException $ue)
//		{
//			MchWpUtils::sendAjaxErrorMessage($ue->getMessage());
//		}
//
//		MchWpUtils::sendAjaxSuccessMessage($arrResponseData);
//
//	}
//
//	public static function deleteUserPostSubmission()
//	{
//		if(!ModulesController::isModuleRegistered(ModulesController::MODULE_POST_SUBMISSIONS))
//			return;
//		try
//		{
//			 PostSubmissionsPublicModule::getInstance()->deleteUserPostSubmission();
//		}
//		catch (UltraCommException $ue)
//		{
//			MchWpUtils::sendAjaxErrorMessage($ue->getMessage());
//		}
//
//		MchWpUtils::sendAjaxSuccessMessage(__('Your submission was successfully deleted!', 'ultra-community'));
//	}

	private function __construct(){
	}
	private function __clone(){
	}
	private function __wakeup(){
	}

}