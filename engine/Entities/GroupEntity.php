<?php


namespace UltraCommunity\Entities;

use UltraCommunity\MchLib\Utils\MchValidator;
use UltraCommunity\MchLib\Utils\MchWpUtils;
use UltraCommunity\MchLib\Utils\MchUtils;

class GroupEntity
{
	CONST GROUP_TYPE_PUBLIC  = 1;
	CONST GROUP_TYPE_PRIVATE = 2;
	CONST GROUP_TYPE_SECRET  = 3;


//	CONST GROUP_PRIVACY_ACTIVITY_VIEW_EVERYBODY     = 1;
//	CONST GROUP_PRIVACY_ACTIVITY_VIEW_ALL_MEMBERS   = 2;
//	CONST GROUP_PRIVACY_ACTIVITY_VIEW_GROUP_MEMBERS = 3;

//	CONST GROUP_PRIVACY_ACTIVITY_POST_ALL_MEMBERS   = 1;
	CONST GROUP_PRIVACY_ACTIVITY_POST_GROUP_MEMBERS = 1;
	CONST GROUP_PRIVACY_ACTIVITY_POST_ADMINS_ONLY   = 2;

//	CONST GROUP_PRIVACY_ACTIVITY_COMMENT_ALL_MEMBERS   = 1;
	CONST GROUP_PRIVACY_ACTIVITY_COMMENT_GROUP_MEMBERS = 1;
	CONST GROUP_PRIVACY_ACTIVITY_COMMENT_ADMINS_ONLY   = 2;


	//https://www.eff.org/deeplinks/2017/06/understanding-public-closed-and-secret-facebook-groups

	public $Id          = null;
	public $Name        = null;
	public $Slug        = null;
	public $Description = null;
	public $CreatedDate = null;

	public $AdminUserId = null;

	public $GroupTypeId = null;
	
	
	public $PictureFileName = null;
	public $CoverFileName   = null;
	
	public $GroupPrivacyActivityView    = null;
	public $GroupPrivacyActivityPost    = null;
	public $GroupPrivacyActivityComment = null;

	public function __construct()
	{}

	public static function getAllGroupTypes()
	{
		static $arrGroupType = null;

		return (null !== $arrGroupType) ? $arrGroupType : $arrGroupType = array(
			
			self::GROUP_TYPE_PUBLIC  => esc_html__('Public', 'ultra-community'),
			self::GROUP_TYPE_PRIVATE => esc_html__('Private', 'ultra-community'),
			self::GROUP_TYPE_SECRET  => esc_html__('Secret', 'ultra-community'),
			
		);

	}

	public static function getGroupTypeDescription($groupTypeId)
	{
		$arrGroupType = self::getAllGroupTypes();
		return isset($arrGroupType[$groupTypeId]) ? $arrGroupType[$groupTypeId] : null;
	}
	
	public static function getGroupTypeIconClass($groupTypeId)
	{
		switch ($groupTypeId)
		{
			case self::GROUP_TYPE_PUBLIC  : return 'fa-globe';
			case self::GROUP_TYPE_PRIVATE : return 'fa-lock';
			case self::GROUP_TYPE_SECRET  : return 'fa-user-secret';
		}
		
		return null;
	}
	
	
	
//	public static function getGroupPrivacyActivityViewTypes()
//	{
//		return array(
//			self::GROUP_PRIVACY_ACTIVITY_VIEW_EVERYBODY     => __('Everybody', 'ultra-community'),
//			self::GROUP_PRIVACY_ACTIVITY_VIEW_ALL_MEMBERS   => __('All Members', 'ultra-community'),
//			self::GROUP_PRIVACY_ACTIVITY_VIEW_GROUP_MEMBERS => __('Just Group Members', 'ultra-community'),
//		);
//	}

	public static function getGroupPrivacyActivityPostingTypes()
	{
		return array(
//			self::GROUP_PRIVACY_ACTIVITY_POST_ALL_MEMBERS     => __('All Members', 'ultra-community'),
			self::GROUP_PRIVACY_ACTIVITY_POST_GROUP_MEMBERS   => esc_html__('All Group Members', 'ultra-community'),
			self::GROUP_PRIVACY_ACTIVITY_POST_ADMINS_ONLY     => esc_html__('Just Administrators', 'ultra-community'),
		);
	}

	public static function getGroupPrivacyActivityCommentingTypes()
	{
		return array(
			//self::GROUP_PRIVACY_ACTIVITY_COMMENT_ALL_MEMBERS     => __('All Members', 'ultra-community'),
			self::GROUP_PRIVACY_ACTIVITY_COMMENT_GROUP_MEMBERS   => esc_html__('All Group Members', 'ultra-community'),
			self::GROUP_PRIVACY_ACTIVITY_COMMENT_ADMINS_ONLY     => esc_html__('Just Administrators', 'ultra-community'),
		);
	}

	/**
	 * @param \WP_Post $wpPost
	 *
	 * @return GroupEntity|null
	 */
	public static function fromWPPost(\WP_Post $wpPost = null)
	{
		if(empty($wpPost->ID) || empty($wpPost->post_author))
			return null;

		$groupEntity = new GroupEntity();
		$groupEntity->Id = (int)$wpPost->ID;
		$groupEntity->AdminUserId = (int)$wpPost->post_author;

		empty($wpPost->post_name)    ?: $groupEntity->Slug = $wpPost->post_name;
		empty($wpPost->post_date)    ?: $groupEntity->CreatedDate = $wpPost->post_date;


		empty($wpPost->post_title)   ?: $groupEntity->Name       = apply_filters( 'the_title', $wpPost->post_title, $wpPost->ID);
		empty($wpPost->post_content) ?: $groupEntity->Description = MchWpUtils::stripSlashes(html_entity_decode(htmlspecialchars_decode($wpPost->post_content))) ;//str_replace( ']]>', ']]&gt;', apply_filters( 'the_content', $wpPost->post_content));
		empty($wpPost->post_date)    ?: $groupEntity->CreatedDate = $wpPost->post_date;

		return 	$groupEntity;

	}

	public function sanitizeFields()
	{

		$this->Id           = (int)$this->Id;
		$this->AdminUserId  = (int)$this->AdminUserId;
		$this->GroupTypeId  = (int)$this->GroupTypeId ;

		$this->Description = MchWpUtils::sanitizeTextArea(MchUtils::normalizeNewLine($this->Description));

		$this->Name        = MchWpUtils::sanitizeText($this->Name);
		$this->Slug        = MchWpUtils::sanitizeText($this->Slug);

		return $this;
	}

	public function escapeFields()
	{
		foreach ( (array)get_object_vars($this) as $propertyName => $propertyValue){

			if('Description' === $propertyName){
				$this->{$propertyName} = esc_textarea($propertyValue);
				continue;

			}

			$this->{$propertyName} = esc_html($propertyValue);
		}
	}
}