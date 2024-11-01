<?php
namespace  UltraCommunity\Modules\Appearance\Directories;


use UltraCommunity\MchLib\Utils\MchHtmlUtils;
use UltraCommunity\MchLib\Utils\MchWpUtils;
use UltraCommunity\Modules\Appearance\UserProfile\UserProfileAppearanceAdminModule;
use UltraCommunity\Modules\BaseAdminModule;

class DirectoriesAppearanceAdminModule extends BaseAdminModule
{
	CONST PAGINATION_TYPE_STANDARD = 'standard';
	CONST PAGINATION_TYPE_SCROLL   = 'scroll';

	CONST DISPLAY_STATS_TYPE_ICONS   = 'icons';
	CONST DISPLAY_STATS_TYPE_NUMBERS = 'numbers';

	CONST PICTURE_STYLE_CIRCLE  = UserProfileAppearanceAdminModule::PICTURE_STYLE_CIRCLE;
	CONST PICTURE_STYLE_SQUARE  = UserProfileAppearanceAdminModule::PICTURE_STYLE_SQUARE;


	CONST OPTION_MEMBERS_PAGINATION_TYPE = 'MembersPaginationType';
	CONST OPTION_GROUPS_PAGINATION_TYPE  = 'GroupsPaginationType';

	CONST OPTION_MEMBERS_DISPLAY_STATS_TYPE = 'MembersDisplayStatsType';
	CONST OPTION_GROUPS_DISPLAY_STATS_TYPE  = 'GroupsDisplayStatsType';

	CONST OPTION_MEMBERS_PICTURE_STYLE = 'MembersPictureStyle';
	CONST OPTION_GROUPS_PICTURE_STYLE  = 'GroupsPictureStyle';


	CONST OPTION_MEMBERS_DISPLAY_COVER = 'MembersDisplayCover';
	CONST OPTION_GROUPS_DISPLAY_COVER  = 'GroupsDisplayCover';

	CONST OPTION_MEMBERS_HIGHLIGHT_STATS = 'MembersHighLightStats';
	CONST OPTION_GROUPS_HIGHLIGHT_STATS  = 'GroupsHighLightStats';


	CONST OPTION_MEMBERS_DISPLAY_ONLINE_STATUS   = 'MembersDisplayOnlineStatus';
	CONST OPTION_MEMBERS_DISPLAY_SOCIAL_NETWORKS = 'MembersDisplaySocialNetworks';
	CONST OPTION_MEMBERS_DISPLAY_RATINGS         = 'MembersDisplayRatings';

	CONST OPTION_MEMBERS_PER_PAGE = 'MembersPerPage';
	CONST OPTION_GROUPS_PER_PAGE  = 'GroupsPerPage';


	public function getDefaultOptions()
	{
		static $arrDefaultSettingOptions = null;
		if(null !== $arrDefaultSettingOptions)
			return $arrDefaultSettingOptions;

		$arrDefaultSettingOptions =  array(

			self::OPTION_MEMBERS_PAGINATION_TYPE =>array(
				'Value'      => self::PAGINATION_TYPE_SCROLL,
				'LabelText'  => __('Directory Pagination Type', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_SELECT,
				'HelpText'   => __('Members Directory pagination type', 'ultra-community')
			),

			self::OPTION_MEMBERS_PICTURE_STYLE =>array(
				'Value'      => self::PICTURE_STYLE_CIRCLE,
				'LabelText'  => __('User Picture Style', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_SELECT,
				'HelpText'   => __('Select the user picture style', 'ultra-community')
			),


			self::OPTION_MEMBERS_DISPLAY_STATS_TYPE =>array(
				'Value'      => self::DISPLAY_STATS_TYPE_NUMBERS,
				'LabelText'  => __('Display User Stats As', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_SELECT,
				'HelpText'   => __('Select how you prefer to display user statistics.', 'ultra-community')
			),

			self::OPTION_MEMBERS_DISPLAY_COVER =>array(
				'Value'      => true,
				'LabelText'  => __('Display User Cover Image', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_CHECKBOX,
				'HelpText'   => __('Display user card cover image', 'ultra-community')
			),

			self::OPTION_MEMBERS_DISPLAY_ONLINE_STATUS =>array(
				'Value'      => true,
				'LabelText'  => __('Display User Online Status', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_CHECKBOX,
				'HelpText'   => __('Shows the user online status', 'ultra-community')
			),

			self::OPTION_MEMBERS_DISPLAY_RATINGS =>array(
					'Value'      => true,
					'LabelText'  => __('Display User Ratings', 'ultra-community'),
					'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_CHECKBOX,
					'HelpText'   => __('Display user ratings', 'ultra-community')
			),

			self::OPTION_MEMBERS_DISPLAY_SOCIAL_NETWORKS =>array(
				'Value'      => true,
				'LabelText'  => __('Display User Social Networks', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_CHECKBOX,
				'HelpText'   => __('Display user social networks link icons', 'ultra-community')
			),


			self::OPTION_MEMBERS_HIGHLIGHT_STATS =>array(
				'Value'      => true,
				'LabelText'  => __('Highlight User Statistics', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_CHECKBOX,
				'HelpText'   => __('Enabling this option, the user statistics will be displayed with a light grey background', 'ultra-community')
			),

			self::OPTION_MEMBERS_PER_PAGE =>array(
				'Value'      => 9,
				'LabelText'  => __('Members Per Page', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_TEXT,
				'HelpText'   => __('Maximum number of members to display per page.', 'ultra-community')
			),

			############################## Groups Directory ###########################

			self::OPTION_GROUPS_PAGINATION_TYPE =>array(
				'Value'      => self::PAGINATION_TYPE_SCROLL,
				'LabelText'  => __('Directory Pagination Type', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_SELECT,
				'HelpText'   => __('Groups Directory pagination type', 'ultra-community')
			),

			self::OPTION_GROUPS_PICTURE_STYLE =>array(
				'Value'      => self::PICTURE_STYLE_CIRCLE,
				'LabelText'  => __('Group Picture Style', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_SELECT,
				'HelpText'   => __('Select the group picture style', 'ultra-community')
			),

			self::OPTION_GROUPS_DISPLAY_STATS_TYPE =>array(
				'Value'      => self::DISPLAY_STATS_TYPE_ICONS,
				'LabelText'  => __('Display Group Stats As', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_SELECT,
				'HelpText'   => __('Select how you prefer to display group statistics.', 'ultra-community')
			),

			self::OPTION_GROUPS_DISPLAY_COVER =>array(
				'Value'      => true,
				'LabelText'  => __('Display Group Cover Image', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_CHECKBOX,
				'HelpText'   => __('Display group cover image', 'ultra-community')
			),

			self::OPTION_GROUPS_HIGHLIGHT_STATS =>array(
				'Value'      => true,
				'LabelText'  => __('Highlight Group Statistics', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_CHECKBOX,
				'HelpText'   => __('Enabling this option, the group statistics will be displayed with a light grey background', 'ultra-community')
			),

			self::OPTION_GROUPS_PER_PAGE =>array(
				'Value'      => 9,
				'LabelText'  => __('Groups Per Page', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_TEXT,
				'HelpText'   => __('Maximum number of groups to display per page.', 'ultra-community')
			),

		);

		return $arrDefaultSettingOptions;

	}

	public function validateModuleSettingsFields($arrSettingOptions)
	{
		$arrSettingOptions = $this->sanitizeModuleSettings($arrSettingOptions);
		$this->registerSuccessMessage(__('Your changes were successfully saved!', 'ultra-community'));
		return $arrSettingOptions;

	}



	public function renderModuleSettingsField(array $arrSettingsField)
	{
		$fieldKey   = key( $arrSettingsField );
		$fieldValue = $this->getOption( $fieldKey );

		MchWpUtils::addFilterHook($this->getFieldAttributesFilterName($fieldKey), function( $arrFieldAttributes ) use ( $fieldValue, $fieldKey) {

			if(in_array($fieldKey, array(
				DirectoriesAppearanceAdminModule::OPTION_MEMBERS_PAGINATION_TYPE,
				DirectoriesAppearanceAdminModule::OPTION_GROUPS_PAGINATION_TYPE,
			))){

				$arrFieldAttributes['class'][] = 'uc-select2';
				$arrFieldAttributes['value']   = $fieldValue;
				$arrFieldAttributes['options'] = array( DirectoriesAppearanceAdminModule::PAGINATION_TYPE_SCROLL => __('Infinite Scroll', 'ultra-community'), DirectoriesAppearanceAdminModule::PAGINATION_TYPE_STANDARD => __('Standard', 'ultra-community'));
				return $arrFieldAttributes;

			}

			if(in_array($fieldKey, array(
				DirectoriesAppearanceAdminModule::OPTION_GROUPS_DISPLAY_STATS_TYPE,
				DirectoriesAppearanceAdminModule::OPTION_MEMBERS_DISPLAY_STATS_TYPE,
			))){

				$arrFieldAttributes['class'][] = 'uc-select2';
				$arrFieldAttributes['value']   = $fieldValue;
				$arrFieldAttributes['options'] = array( DirectoriesAppearanceAdminModule::DISPLAY_STATS_TYPE_NUMBERS => __('Numbers', 'ultra-community'), DirectoriesAppearanceAdminModule::DISPLAY_STATS_TYPE_ICONS => __('Icons', 'ultra-community'));
				return $arrFieldAttributes;

			}

			if(in_array($fieldKey, array(
				DirectoriesAppearanceAdminModule::OPTION_GROUPS_PICTURE_STYLE,
				DirectoriesAppearanceAdminModule::OPTION_MEMBERS_PICTURE_STYLE,
			))){

				$arrFieldAttributes['class'][] = 'uc-select2';
				$arrFieldAttributes['value']   = $fieldValue;
				$arrFieldAttributes['options'] = array( DirectoriesAppearanceAdminModule::PICTURE_STYLE_CIRCLE => __('Circle', 'ultra-community'), DirectoriesAppearanceAdminModule::PICTURE_STYLE_SQUARE => __('Square', 'ultra-community'));
				return $arrFieldAttributes;

			}




			return $arrFieldAttributes;


		});



		return parent::renderModuleSettingsField($arrSettingsField);

	}

	public  function renderModuleSettingsSectionHeader(array $arrSectionInfo)
	{

		echo '<div class="uc-settings-section-header uc-clear" style="border:0"><h3>' . __('Directories Appearance Settings', 'ultra-community') . '</h3></div>';

		MchWpUtils::addFilterHook(self::FILTER_FIELD_SETTINGS_TABLE_ROW_OUTPUT, function ($fieldTableRowOutput, $fieldKey){

			if($fieldKey === DirectoriesAppearanceAdminModule::OPTION_MEMBERS_PAGINATION_TYPE)
			{
				return $this->addSettingsSectionDivider(esc_html__('Members Directory Settings', 'ultra-community'), $fieldTableRowOutput, true);
			}

			if($fieldKey === DirectoriesAppearanceAdminModule::OPTION_GROUPS_PAGINATION_TYPE)
			{
				return $this->addSettingsSectionDivider(esc_html__('Groups Directory Settings', 'ultra-community'), $fieldTableRowOutput, true);
			}

			return 	$fieldTableRowOutput;

		}, 10, 2);


	}

}