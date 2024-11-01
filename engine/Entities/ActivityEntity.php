<?php
namespace UltraCommunity\Entities;


use UltraCommunity\Controllers\ModulesController;
use UltraCommunity\Controllers\PostTypeController;
use UltraCommunity\MchLib\Utils\MchUtils;
use UltraCommunity\MchLib\Utils\MchWpUtils;
use UltraCommunity\MchLib\WordPress\Repository\WpPostRepository;
use UltraCommunity\Modules\Appearance\UserProfile\UserProfileAppearanceAdminModule;
use UltraCommunity\Modules\ExtendedActivity\ExtendedActivityPublicModule;
use UltraCommunity\PostsType\ActivityPostType;
use UltraCommunity\Repository\ActivityRepository;
use UltraCommunity\UltraCommHelper;
use UltraCommunity\UltraCommUtils;

class ActivityMetaData
{
	public $PostFormat  = null;
	public $Attachments = null;

	public $QuoteAuthor = null;
	public $QuoteText   = null;

	public $LinkUrl         = null;
	public $LinkTitle       = null;
	public $LinkDescription = null;

	public $UserLikes    = null;

	public function __construct()
	{
		$this->init();
	}
	private function init()
	{
		!empty($this->Attachments) ?: $this->Attachments = array();
		!empty($this->UserLikes)   ?: $this->UserLikes   = array();
		!empty($this->PostFormat)  ?: $this->PostFormat  = ActivityEntity::ACTIVITY_POST_FORMAT_STATUS;

	}

	public function __sleep()
	{
		$arrObjectProperties = get_object_vars(MchUtils::filterObjectEmptyProperties($this));
		return array_keys($arrObjectProperties);
	}
	public function __wakeup()
	{
		$this->init();
	}
}

class ActivityEntity
{
	CONST ACTIVITY_STATUS_ACTIVE = 1;

	CONST ACTIVITY_TARGET_TYPE_USER  = 1;
	CONST ACTIVITY_TARGET_TYPE_GROUP = 2;

	CONST ACTIVITY_POST_FORMAT_STATUS = 1;
	CONST ACTIVITY_POST_FORMAT_IMAGE  = 2;
	CONST ACTIVITY_POST_FORMAT_FILE   = 3;
	CONST ACTIVITY_POST_FORMAT_AUDIO  = 4;
	CONST ACTIVITY_POST_FORMAT_VIDEO  = 5;
	CONST ACTIVITY_POST_FORMAT_LINK   = 6;
	CONST ACTIVITY_POST_FORMAT_QUOTE  = 7;

	CONST ACTION_TYPE_NEW_WALL_POST         = 1;
	CONST ACTION_TYPE_NEW_USER_REGISTRATION = 2;
	CONST ACTION_TYPE_NEW_BLOG_POST         = 3;

	CONST ACTION_TYPE_USER_FRIENDSHIP = 4;
	CONST ACTION_TYPE_USER_FOLLOWING  = 5;

	CONST ACTION_TYPE_NEW_GROUP_CREATED  = 6;
	CONST ACTION_TYPE_USER_JOINED_GROUP  = 7;

	CONST ACTION_TYPE_USER_PROFILE_PHOTO_CHANGED = 8;
	CONST ACTION_TYPE_USER_PROFILE_COVER_CHANGED = 9;


	public $ActivityId = null;
	public $TargetTypeId = null;
	public $ActionTypeId = null;
	public $UserId = null;
	public $TargetId = null;
	public $PostTypeId = null;
	public $CreatedDate = null;
	public $StatusId = null;
	public $PostFormatTypeId = null;
	private $permaLink = null;

	/**
	 * @var null|ActivityMetaData
	 */
	public $MetaData = null;

	/**
	 * @var null|\WP_Post
	 */
	//private $PostObject = null;

	public function __construct()
	{
		$this->MetaData = new ActivityMetaData();
	}

	public function getAttachmentUrl($attachmentArrayIndex)
	{
		if(null === $this->getAttachmentFilePath($attachmentArrayIndex))
			return null;

		return UltraCommUtils::getActivityAttachmentsBaseUrl($this) . '/' . $this->MetaData->Attachments[$attachmentArrayIndex]->Name;
	}

	public function getAttachmentFilePath($attachmentArrayIndex)
	{
		($this->MetaData instanceof ActivityMetaData) ?: $this->MetaData = new ActivityMetaData();

		if(empty($this->MetaData->Attachments[$attachmentArrayIndex]->Name))
			return null;

		$filePath = UltraCommUtils::getActivityAttachmentsBaseDirectoryPath($this) . '/' . $this->MetaData->Attachments[$attachmentArrayIndex]->Name;

		return \is_file($filePath) ? $filePath : null;
	}

	public function getPostObjectContent()
	{
		if(ModulesController::isModuleRegistered(ModulesController::MODULE_EXTENDED_ACTIVITY)) {
			return ExtendedActivityPublicModule::getActivityPostObjectContent($this);
		}

		if(! MchWpUtils::isWPPost($postObject = WpPostRepository::findByPostId($this->PostTypeId)) )
			return null;

		setup_postdata( $GLOBALS['post'] = $postObject );
		$postObject->post_content = get_the_content(null, true);
		wp_reset_postdata();

		return wpautop( $postObject->post_content );

	}

	public function getPermaLink()
	{
		if(null !== $this->permaLink)
			return $this->permaLink;

		if(empty($this->PostTypeId) || empty($this->ActivityId) || empty($this->TargetId) || empty($this->TargetTypeId))
			return null;

		if($this->TargetTypeId === self::ACTIVITY_TARGET_TYPE_USER)
		{
			//$wpUser = new \WP_User($this->TargetId);
			$wpUser = new \WP_User($this->UserId);
			if(!$wpUser->exists())
				return null;

			return UltraCommHelper::getUserProfileUrl($wpUser, UserProfileAppearanceAdminModule::PROFILE_SECTION_ACTIVITY) . $this->ActivityId;
		}


		if(! ($groupCustomPost = \WP_Post::get_instance($this->TargetId)) )
			return null;

		return UltraCommHelper::getGroupUrl($groupCustomPost, UserProfileAppearanceAdminModule::PROFILE_SECTION_ACTIVITY) . $this->ActivityId;

	}

}