<?php

namespace UltraCommunity\Modules\GroupsDirectory;

use UltraCommunity\Controllers\PostTypeController;
use UltraCommunity\Entities\GroupEntity;
use UltraCommunity\MchLib\Utils\MchHtmlUtils;
use UltraCommunity\MchLib\Utils\MchWpUtils;
use UltraCommunity\Modules\Appearance\GroupProfile\GroupProfileAppearanceAdminModule;
use UltraCommunity\Modules\BaseAdminModule;
use UltraCommunity\Modules\UserRole\UserRoleAdminModule;


class GroupsDirectoryAdminModule extends BaseAdminModule
{

	CONST GROUP_TYPE_PUBLIC  = 1;
	CONST GROUP_TYPE_PRIVATE = 2;
	CONST GROUP_TYPE_SECRET  = 3;

	CONST GROUP_STATS_COUNTER_POSTS     = GroupProfileAppearanceAdminModule::GROUP_STATS_COUNTER_POSTS;
	CONST GROUP_STATS_COUNTER_MEMBERS   = GroupProfileAppearanceAdminModule::GROUP_STATS_COUNTER_MEMBERS;


	CONST OPTION_DIRECTORY_TITLE      = 'Name';
	CONST OPTION_DIRECTORY_CUSTOM_CSS = 'CustomCss';
	CONST OPTION_LIST_GROUPS_TYPE     = 'ListGroupsType';
	CONST OPTION_ALLOWED_USER_ROLES   = 'AllowedUserRoles';
	CONST OPTION_GROUP_STATS_COUNTERS = 'GroupStatsCounters';

	CONST OPTION_IS_DEFAULT_GROUPS_DIRECTORY = 'IsDefault';



	protected function __construct()
	{
		parent::__construct();
	}

	public static function getAllGroupTypes()
	{
		static $arrGroupType = null;

		return (null !== $arrGroupType) ? $arrGroupType : $arrGroupType = array(
				self::GROUP_TYPE_PUBLIC  => esc_html__('Public', 'ultra-community'),
				self::GROUP_TYPE_PRIVATE => esc_html__('Private', 'ultra-community'),
				self::GROUP_TYPE_SECRET  => esc_html__('Secret', 'ultra-community'),
		);

	}

	public static function getDefinedGroupStatsCounters()
	{
		return GroupProfileAppearanceAdminModule::getDefinedGroupStatsCounters();
	}



	public static function getGroupTypeDescription($groupTypeId)
	{
		$arrGroupType = self::getAllGroupTypes();
		return isset($arrGroupType[$groupTypeId]) ? $arrGroupType[$groupTypeId] : null;
	}

	public function getDefaultOptions()
	{
		static $arrDefaultSettingOptions = null;
		if(null !== $arrDefaultSettingOptions)
			return $arrDefaultSettingOptions;


		$arrDefaultSettingOptions = array(

			self::OPTION_DIRECTORY_TITLE => array(
				'Value'      => __('Default Groups Directory', 'ultra-community'),
				'LabelText'  => __('Groups Directory Name', 'ultra-community'),
				'HelpText'   => __('The title of this groups directory', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_TEXT
			),


			self::OPTION_LIST_GROUPS_TYPE => array(
					'Value'      => null,
					'LabelText'  => __('List The Following Group Types', 'ultra-community'),
					'HelpText'   => __('The list of group types that should be listed in this directory. Leave this option empty to list all group types!', 'ultra-community'),
					'InputType'  => MchHtmlUtils::FORM_ELEMENT_SELECT
			),

			self::OPTION_ALLOWED_USER_ROLES => array(
					'Value'      => null,
					'LabelText'  => __('User Roles Allowed to Access', 'ultra-community'),
					'InputType'  => MchHtmlUtils::FORM_ELEMENT_SELECT,
					'HelpText'   => __('The list of user roles that can view this directory. Leave this blank if you want this Groups Directory to be publicly visible', 'ultra-community')
			),


			self::OPTION_GROUP_STATS_COUNTERS => array(
				'Value'      => array(self::GROUP_STATS_COUNTER_POSTS, self::GROUP_STATS_COUNTER_MEMBERS),
				'LabelText'  => __('Group Stats Counters', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_SELECT,
				'HelpText'   => __('Choose the stats counters that will be displayed at the bottom of the group card', 'ultra-community'),
			),

			self::OPTION_DIRECTORY_CUSTOM_CSS => array(

				'Value'      => null,
				'LabelText'  => __('Directory Custom CSS', 'ultra-community'),
				'HelpText'   => __('Add your directory custom CSS', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_TEXTAREA,

			),

		);

		return $arrDefaultSettingOptions;

	}

	public function validateModuleSettingsFields($arrSettingOptions)
	{
		$arrSettingOptions = $this->sanitizeModuleSettings($arrSettingOptions);

		if(empty($arrSettingOptions[self::OPTION_DIRECTORY_TITLE]))
		{
			$this->registerErrorMessage(__('Please provide a Name for this directory!', 'ultra-community'));
			return $this->getAllSavedOptions();
		}

		if(isset($arrSettingOptions[self::OPTION_GROUP_STATS_COUNTERS]) && !empty($_POST[ 'ordered-' . $this->getSettingKey() ][self::OPTION_GROUP_STATS_COUNTERS][0]))
		{
			$arrSettingOptions[self::OPTION_GROUP_STATS_COUNTERS] = explode(',', $_POST[ 'ordered-' . $this->getSettingKey() ][self::OPTION_GROUP_STATS_COUNTERS][0]);

			$arrSettingOptions[self::OPTION_GROUP_STATS_COUNTERS] = array_filter($arrSettingOptions[self::OPTION_GROUP_STATS_COUNTERS]);

			$arrSettingOptions[self::OPTION_GROUP_STATS_COUNTERS] = array_map('sanitize_text_field', $arrSettingOptions[self::OPTION_GROUP_STATS_COUNTERS]);

			$arrSettingOptions[self::OPTION_GROUP_STATS_COUNTERS] = array_values($arrSettingOptions[self::OPTION_GROUP_STATS_COUNTERS]);

		}


		$customPostType = (null !== $this->getCustomPostType()) ? $this->getCustomPostType() : PostTypeController::getPostTypeInstance(PostTypeController::POST_TYPE_MEMBERS_DIRECTORY);

		$customPostType->PostTitle = $arrSettingOptions[self::OPTION_DIRECTORY_TITLE];

		PostTypeController::publishPostType($customPostType);

		$this->setCustomPostType($customPostType);


		if($this->getOption(self::OPTION_IS_DEFAULT_GROUPS_DIRECTORY)){
			$arrSettingOptions[self::OPTION_IS_DEFAULT_GROUPS_DIRECTORY] = true;
		}

		//$arrSettingOptions[self::OPTION_IS_DEFAULT_GROUPS_DIRECTORY] = true;

		$this->registerSuccessMessage(__('Your changes were successfully saved!', 'ultra-community'));

		return $arrSettingOptions;

	}

	public  function renderModuleSettingsSectionHeader(array $arrSectionInfo)
	{
		parent::renderModuleSettingsSectionHeader($arrSectionInfo);
		
	}


	public function renderModuleSettingsField(array $arrSettingsField)
	{
		$fieldKey   = key( $arrSettingsField );
		$fieldValue = $this->getOption( $fieldKey );


		MchWpUtils::addFilterHook($this->getFieldAttributesFilterName($fieldKey), function( $arrFieldAttributes ) use ($fieldKey, $fieldValue){

			if($fieldKey === GroupsDirectoryAdminModule::OPTION_LIST_GROUPS_TYPE)
			{
				$arrFieldAttributes['multiple'] = 'multiple';
				$arrFieldAttributes['class'][]  = 'uc-select2-multiple';
				$arrFieldAttributes['value']    = $fieldValue;
				$arrFieldAttributes['options']  = array();

				foreach (GroupsDirectoryAdminModule::getAllGroupTypes() as $groupTypeId =>  $groupType) {
					$arrFieldAttributes['options'][$groupTypeId] = $groupType;
				}

			}


			if($fieldKey === GroupsDirectoryAdminModule::OPTION_ALLOWED_USER_ROLES )
			{
				$arrFieldAttributes['multiple'] = 'multiple';
				$arrFieldAttributes['class'][]  = 'uc-select2-multiple';
				$arrFieldAttributes['value']    = $fieldValue;
				$arrFieldAttributes['options']  = array();

				foreach ((array)PostTypeController::getPublishedPosts(PostTypeController::POST_TYPE_USER_ROLE) as $customPostUserRole) {
					$adminModule = PostTypeController::getAssociatedAdminModuleInstance($customPostUserRole);
					if (!$adminModule instanceof UserRoleAdminModule)
						continue;
					$arrFieldAttributes['options'][$customPostUserRole->PostId] = $adminModule->getOption(UserRoleAdminModule::OPTION_ROLE_TITLE);
				}

			}


			if($fieldKey === GroupsDirectoryAdminModule::OPTION_GROUP_STATS_COUNTERS)
			{

				$arrFieldAttributes['value']    = (array)$fieldValue;
				$arrFieldAttributes['multiple'] = 'multiple';
				$arrFieldAttributes['class'][]  = 'uc-select2-multiple';
				$arrFieldAttributes['options']  = array();

				$arrCounters = array(
					GroupsDirectoryAdminModule::GROUP_STATS_COUNTER_POSTS    => __('Posts', 'ultra-community'),
					GroupsDirectoryAdminModule::GROUP_STATS_COUNTER_MEMBERS  => __('Members', 'ultra-community'),
					//GroupsDirectoryAdminModule::GROUP_STATS_COUNTER_COMMENTS => __('Comments', 'ultra-community'),
				);

				foreach ($arrFieldAttributes['value'] as $index => $savedCounter)
				{
					if(!isset($arrCounters[$savedCounter])){
						unset($arrFieldAttributes['value'][$index]);
						continue;
					}

					$arrFieldAttributes['options'][$savedCounter] =  $arrCounters[$savedCounter];
				}

				foreach ($arrCounters as $counterKey => $counterName)
				{
					if(isset($arrFieldAttributes['options'][$counterKey]))
						continue;
					$arrFieldAttributes['options'][$counterKey] =  $counterName;
				}

			}





			return $arrFieldAttributes;

		});



		return parent::renderModuleSettingsField($arrSettingsField);

	}

}