<?php
namespace UltraCommunity\FrontPages;

use UltraCommunity\Controllers\ActivityController;
use UltraCommunity\Controllers\TemplatesController;
use UltraCommunity\Controllers\UserController;
use UltraCommunity\Entities\ActivityEntity;
use UltraCommunity\MchLib\Utils\MchWpUtils;
use UltraCommunity\UltraCommHooks;
use UltraCommunity\UltraCommUtils;

class ActivityPage extends BasePage
{
	public function __construct($pageId = null)
	{
		parent::__construct($pageId);
	}

	private static function initRenderingHooks()
	{
		static $hooksInitialized = null;
		if(null !== $hooksInitialized)
			return;

		$hooksInitialized = true;

		add_action(UltraCommHooks::ACTION_ACTIVITY_RENDER_HEADER, function (ActivityEntity $activityEntity){

			isset($activityEntity->UserEntity) ?: $activityEntity->UserEntity =  UserController::getUserEntityBy($activityEntity->UserId);

			$headerTitle = ActivityController::getActivityRenderTitle($activityEntity);

			TemplatesController::loadTemplate('activity/activity-layout-header.php', array('headerTitle' => $headerTitle, 'activityEntity' => $activityEntity));

		}, 10, 1);

		add_action(UltraCommHooks::ACTION_ACTIVITY_RENDER_FOOTER, function (ActivityEntity $activityEntity){


			$arrActions = array();

			$arrActions['comments'] = array();
			$arrActions['comments']['count'] = ActivityController::countActivityComments($activityEntity);
			$arrActions['comments']['text']  = sprintf( _n( '%s Comment', '%s Comments', $arrActions['comments']['count'] ), number_format_i18n( $arrActions['comments']['count']) );
			$arrActions['comments']['icon']  = empty($arrActions['comments']['count']) ? 'fa fa-comment-o' : 'fa fa-comment';
			$arrActions['comments']['url']   = null; //'#uc-comments-root-form-' . $activityEntity->ActivityId;
			$arrActions['comments']['class'] = 'uc-comments-target-root-form-' . $activityEntity->ActivityId;

			$arrActions['comments']['triggerLogIn'] = false;

			unset($arrActions['comments']['count']);

			if(ActivityController::userCanManageActivity(UserController::getLoggedInUser(), $activityEntity))
			{
				$arrActions['delete'] = array();
				$arrActions['delete']['text']  =  esc_html__('Delete', 'ultra-community');
				$arrActions['delete']['icon']  = 'fa fa-trash';
				$arrActions['delete']['triggerLogIn'] = true;
			}

			$activityEntity->FooterActions = (array)apply_filters(UltraCommHooks::FILTER_ACTIVITY_FOOTER_ACTIONS, $arrActions, $activityEntity->ActivityId);

			TemplatesController::loadTemplate('activity/activity-layout-footer.php', array('activityEntity' => $activityEntity));

		}, 10, 1);


		add_action(UltraCommHooks::ACTION_ACTIVITY_RENDER_COMMENTS_LIST, function (ActivityEntity $activityEntity){
			TemplatesController::loadTemplate('activity/activity-comments-list.php', array('activityEntity' => $activityEntity));
		}, 10, 1);

		add_action(UltraCommHooks::ACTION_ACTIVITY_RENDER_COMMENT, function ($activityComment, $activityEntity = null, $args = array() , $depth = 1){

			static $arrPostCommentsCounter = array();
			if(!empty($activityComment->comment_post_ID))
			{
				!empty($arrPostCommentsCounter[$activityComment->comment_post_ID]) ?: $arrPostCommentsCounter[$activityComment->comment_post_ID] = 0;
				$arrPostCommentsCounter[$activityComment->comment_post_ID]++;

				$activityComment->isHidden = $arrPostCommentsCounter[$activityComment->comment_post_ID] > 5;
			}


			TemplatesController::loadTemplate('activity/activity-comment.php', array('activityComment' => $activityComment, 'activityEntity' => $activityEntity,  'args' => $args, 'depth' => $depth));

		}, 10, 4);

		add_action(UltraCommHooks::ACTION_ACTIVITY_RENDER_COMMENTS_FORM, function (ActivityEntity $activityEntity){

			TemplatesController::loadTemplate('activity/activity-comments-form.php', array('activityEntity' => $activityEntity));

		}, 10, 1);


		add_action(UltraCommHooks::ACTION_ACTIVITY_RENDER_CONTENT, function(ActivityEntity $activityEntity){

			$templateFileName = null;

			switch ($activityEntity->ActionTypeId)
			{
				case ActivityEntity::ACTION_TYPE_NEW_BLOG_POST :
					$templateFileName = 'actions/action-new-blog-post.php'; break;

				case ActivityEntity::ACTION_TYPE_USER_PROFILE_PHOTO_CHANGED :
					$templateFileName = 'actions/action-user-photo-changed.php'; break;

				case ActivityEntity::ACTION_TYPE_USER_PROFILE_COVER_CHANGED :
					$templateFileName = 'actions/action-user-cover-changed.php'; break;

				case ActivityEntity::ACTION_TYPE_NEW_USER_REGISTRATION :
					$templateFileName = 'actions/action-new-user-registration.php'; break;

				case ActivityEntity::ACTION_TYPE_NEW_GROUP_CREATED :
					$templateFileName = 'actions/action-new-group-created.php'; break;

				case ActivityEntity::ACTION_TYPE_USER_FOLLOWING :
					$templateFileName = 'activity/actions/action-user-following.php'; break;

				case ActivityEntity::ACTION_TYPE_USER_FRIENDSHIP :
					$templateFileName = 'activity/actions/action-user-friendship.php'; break;

				case ActivityEntity::ACTION_TYPE_NEW_WALL_POST :

					!empty($activityEntity->MetaData->PostFormat) ?: $activityEntity->MetaData->PostFormat = ActivityEntity::ACTIVITY_POST_FORMAT_STATUS;

					switch ($activityEntity->MetaData->PostFormat)
					{
						case ActivityEntity::ACTIVITY_POST_FORMAT_STATUS :
							$templateFileName = 'actions/action-new-wall-post-status.php'; break;

						case ActivityEntity::ACTIVITY_POST_FORMAT_IMAGE :
							$templateFileName = 'actions/action-new-wall-post-images.php'; break;

						case ActivityEntity::ACTIVITY_POST_FORMAT_FILE :
							$templateFileName = 'actions/action-new-wall-post-file.php'; break;

						case ActivityEntity::ACTIVITY_POST_FORMAT_AUDIO :
							$templateFileName = 'actions/action-new-wall-post-audio.php'; break;

						case ActivityEntity::ACTIVITY_POST_FORMAT_VIDEO :
							$templateFileName = 'actions/action-new-wall-post-video.php'; break;

						case ActivityEntity::ACTIVITY_POST_FORMAT_LINK :
							$templateFileName = 'actions/action-new-wall-post-link.php'; break;

						case ActivityEntity::ACTIVITY_POST_FORMAT_QUOTE :
							$templateFileName = 'actions/action-new-wall-post-quote.php'; break;
					}

					break;
			}

			if($activityEntity->ActionTypeId === ActivityEntity::ACTION_TYPE_NEW_WALL_POST){
				if($activityEntity->MetaData->PostFormat !== ActivityEntity::ACTIVITY_POST_FORMAT_STATUS){
					TemplatesController::loadTemplate('actions/action-new-wall-post-status.php', array('activityEntity' => $activityEntity));
				}
			}

			TemplatesController::loadTemplate($templateFileName, array('activityEntity' => $activityEntity));

		}, 10, 1);

	}

	public function getShortCodeContent($arrAttributes, $shortCodeText = null, $shortCodeName = null)
	{
		self::initRenderingHooks();

	}

	public function isAuthenticationRequired()
	{
		return false;
	}

	public function processRequest()
	{
		self::initRenderingHooks();
	}


	public static function renderActivityComment($activityComment, $activityEntity = null, $args = array() , $depth = 1)
	{
		self::initRenderingHooks();

		do_action(UltraCommHooks::ACTION_ACTIVITY_RENDER_COMMENT, $activityComment, $activityEntity, $args, $depth);

	}

	public static function renderActivityPostForm($activityTargetType)
	{
		$showPostForm = false;
		$defaultPlaceholderText = esc_html__('Join the discussion', 'ultra-community');

		switch ($activityTargetType)
		{
			case ActivityEntity::ACTIVITY_TARGET_TYPE_USER :

				$activityTargetType = 'user';
				$showPostForm = UserController::isCurrentUserBrowsingOwnProfile();

				if($showPostForm)
				{
					if(empty($userDisplayName = UserController::getProfiledUser()->UserMetaEntity->FirstName)){
						$userDisplayName = uc_get_user_display_name(UserController::getProfiledUser());
					}

					$defaultPlaceholderText = sprintf(esc_html__("What's on your mind, %s ?", 'ultra-community'), $userDisplayName);
				}


				break;

			case ActivityEntity::ACTIVITY_TARGET_TYPE_GROUP :

				$activityTargetType = 'group';

				$showPostForm = true;

				break;

			default:

				$activityTargetType = 'site';

				break;
		}


		if(!MchWpUtils::applyFilters(UltraCommHooks::FILTER_ACTIVITY_SHOW_NEW_POST_FORM, $showPostForm, $activityTargetType))
			return;

		$defaultPlaceholderText = MchWpUtils::applyFilters(UltraCommHooks::FILTER_ACTIVITY_NEW_POST_FORM_HINT, $defaultPlaceholderText, $activityTargetType);

		$arrTemplateParams = array('activityTargetType' => $activityTargetType, 'formDisplayHint' => $defaultPlaceholderText);

		TemplatesController::loadTemplate('activity/activity-post-form.php', $arrTemplateParams);
	}

	public static function renderActivityEntity(ActivityEntity $activityEntity = null)
	{
		self::initRenderingHooks();

		if(empty($activityEntity->UserId))
			return;

		$activityEntity->UserEntity = UserController::getUserEntityBy($activityEntity->UserId);

		if(empty($activityEntity->UserEntity))
			return;

		!empty($activityEntity->MetaData->PostFormat) ?: $activityEntity->MetaData->PostFormat = ActivityEntity::ACTIVITY_POST_FORMAT_STATUS;

		foreach ($activityEntity->MetaData->Attachments as $index => $attachment)
		{
			if(empty($attachment->Name) || !empty($attachment->Url))
				continue;

			$filePath = UltraCommUtils::getActivityAttachmentsBaseDirectoryPath($activityEntity) . '/' . $attachment->Name;

			if(!is_file($filePath))
			{
				unset($activityEntity->MetaData->Attachments[$index]);
				continue;
			}

			$attachment->Url = UltraCommUtils::getActivityAttachmentsBaseUrl($activityEntity) . '/' . $attachment->Name;

			if($activityEntity->MetaData->PostFormat == ActivityEntity::ACTIVITY_POST_FORMAT_FILE){
				$attachment->Size = size_format(filesize($filePath), 2);
			}

		}

		$activityEntity->MetaData->Attachments = array_values($activityEntity->MetaData->Attachments);

		empty($activityEntity->MetaData->LinkUrl) ?: $activityEntity->MetaData->LinkUrl = untrailingslashit(esc_url($activityEntity->MetaData->LinkUrl));

		echo \apply_filters(UltraCommHooks::FILTER_ACTIVITY_TYPE_OUTPUT_CONTENT, TemplatesController::getTemplateOutputContent('activity/activity-layout.php', array('activityEntity' => $activityEntity)), $activityEntity);

	}

	public function getHeaderMarkup()
	{
		// TODO: Implement getHeaderMarkup() method.
	}

	public function getNavBarMarkup()
	{
		// TODO: Implement getNavBarMarkup() method.
	}

	public function getSideBarMarkup()
	{
		// TODO: Implement getSideBarMarkup() method.
	}

	public function getContentMarkup()
	{
		// TODO: Implement getContentMarkup() method.
	}

	public function getSubMenuTemplateArguments()
	{
		return array();
	}
}