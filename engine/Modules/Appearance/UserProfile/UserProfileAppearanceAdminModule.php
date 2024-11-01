<?php
/**
 * Copyright (c) 2017 Ultra Community (http://www.ultracommunity.com)
 */

namespace UltraCommunity\Modules\Appearance\UserProfile;


use UltraCommunity\Controllers\ModulesController;
use UltraCommunity\Controllers\PostTypeController;
use UltraCommunity\Controllers\WidgetsController;
use UltraCommunity\MchLib\Utils\MchHtmlUtils;
use UltraCommunity\MchLib\Utils\MchHttpRequest;
use UltraCommunity\MchLib\Utils\MchWpUtils;
use UltraCommunity\Modules\BaseAdminModule;
use UltraCommunity\Modules\CustomTabs\CustomTabsAdminModule;
use UltraCommunity\Modules\GeneralSettings\Plugin\PluginSettingsAdminModule;
use UltraCommunity\Modules\MembersDirectory\MembersDirectoryAdminModule;
use UltraCommunity\UltraCommHooks;

class UserProfileAppearanceAdminModule extends BaseAdminModule
{
	CONST PICTURE_STYLE_CIRCLE       = 1;
	CONST PICTURE_STYLE_SQUARE       = 2;
	
	CONST PROFILE_SECTION_ABOUT      = 'about';
	CONST PROFILE_SECTION_POSTS      = 'posts';
	CONST PROFILE_SECTION_GROUPS     = 'groups';
	CONST PROFILE_SECTION_COMMENTS   = 'comments';
	CONST PROFILE_SECTION_ACTIVITY   = 'activity';
	CONST PROFILE_SECTION_FRIENDS    = 'friends';
	CONST PROFILE_SECTION_FOLLOWERS  = 'followers';
	CONST PROFILE_SECTION_FOLLOWING  = 'following';
	CONST PROFILE_SECTION_REVIEWS    = 'reviews';
	
	CONST OPTION_USER_PROFILE_SECTIONS  = 'ProfileSections';

	CONST PROFILE_SECTION_FORUMS           = 'forums';
	CONST PROFILE_SECTION_FORUMS_TOPICS    = 'forums-topics';
	CONST PROFILE_SECTION_FORUMS_REPLIES   = 'forums-replies';
	CONST PROFILE_SECTION_FORUMS_FAVORITES = 'forums-favorites';

	CONST USER_STATS_COUNTER_POSTS      = 'posts';
	CONST USER_STATS_COUNTER_COMMENTS   = 'comments';
	CONST USER_STATS_COUNTER_FRIENDS    = 'friends';
	CONST USER_STATS_COUNTER_FOLLOWERS  = 'followers';
	CONST USER_STATS_COUNTER_FOLLOWING  = 'following';
	
	
	CONST OPTION_HEADER_STYLE_ID              = 'HeaderStyleId';
	CONST OPTION_HEADER_USER_PICTURE_STYLE    = 'HeaderPictureStyle';
	CONST OPTION_HEADER_STATS_COUNTERS        = 'HeaderStatsCounters';
	CONST OPTION_HEADER_TAGLINE_FIELDS        = 'HeaderTagLineFields';
	CONST OPTION_HEADER_SHOW_ONLINE_STATUS    = 'HeaderOnlineStatus';
	CONST OPTION_HEADER_SHOW_SOCIAL_NETWORKS  = 'HeaderShowSocialNetworks';
	CONST OPTION_HEADER_SOCIAL_NETWORKS_STYLE = 'HeaderSocialNetworksStyle';


	CONST OPTION_SIDE_BAR_POSITION = 'SideBarPosition';
	CONST OPTION_SIDE_BAR_WIDGETS  = 'SideBarWidgets';

	public function __construct()
	{
		parent::__construct();
	}

	public static function getDefinedUserProfileSections()
	{
		/**
		 * @var $arrProfileSections array - the key is the section slug. to generate a section slug use - MchWpUtils::formatUrlPath(SECTION NAME HERE)
		 */
		$arrProfileSections = array(
			self::PROFILE_SECTION_ACTIVITY => esc_html__('Activity', 'ultra-community'),
			self::PROFILE_SECTION_ABOUT    => esc_html__('About', 'ultra-community'),
			self::PROFILE_SECTION_POSTS    => esc_html__('Posts', 'ultra-community'),
			self::PROFILE_SECTION_COMMENTS => esc_html__('Comments', 'ultra-community'),
			self::PROFILE_SECTION_GROUPS   => esc_html__('Groups', 'ultra-community'),
		);


		if(ModulesController::isModuleRegistered(ModulesController::MODULE_USER_REVIEWS))
		{
			$arrProfileSections[self::PROFILE_SECTION_REVIEWS] = esc_html__('Reviews', 'ultra-community');
		}


		if(ModulesController::isModuleRegistered(ModulesController::MODULE_USER_FRIENDS))
		{
			$arrProfileSections[self::PROFILE_SECTION_FRIENDS] = esc_html__('Friends', 'ultra-community');
		}


		if(ModulesController::isModuleRegistered(ModulesController::MODULE_USER_FOLLOWERS))
		{
			$arrProfileSections[self::PROFILE_SECTION_FOLLOWERS] = esc_html__('Followers', 'ultra-community');
			$arrProfileSections[self::PROFILE_SECTION_FOLLOWING] = esc_html__('Following', 'ultra-community');
		}


		if(ModulesController::isModuleRegistered(ModulesController::MODULE_BBPRESS)) {

//			$arrProfileSections[ self::PROFILE_SECTION_FORUMS_TOPICS ]    = esc_html__( 'Topics', 'ultra-community' );
//			$arrProfileSections[ self::PROFILE_SECTION_FORUMS_REPLIES ]   = esc_html__( 'Replies', 'ultra-community' );
//			$arrProfileSections[ self::PROFILE_SECTION_FORUMS_FAVORITES ] = esc_html__( 'Favorites', 'ultra-community' );

			$arrProfileSections[ self::PROFILE_SECTION_FORUMS ] = esc_html__( 'Forums', 'ultra-community' );

		}

		foreach(self::getCustomTabsUserProfileSections() as $sectionSlug => $customTabPostType)
		{
			$arrProfileSections[$sectionSlug] = $customTabPostType->PostTitle;
		}

		return $arrProfileSections;


	}

	public static function getBBPressForumSubSections()
	{
		static $arrForumsSubSections = null;
		if(null !== $arrForumsSubSections)
			return $arrForumsSubSections;

		$arrForumsSubSections = array();
		$arrForumsSubSections[ UserProfileAppearanceAdminModule::PROFILE_SECTION_FORUMS_TOPICS ]    = esc_html__( 'Topics', 'ultra-community' );
		$arrForumsSubSections[ UserProfileAppearanceAdminModule::PROFILE_SECTION_FORUMS_REPLIES ]   = esc_html__( 'Replies', 'ultra-community' );
		$arrForumsSubSections[ UserProfileAppearanceAdminModule::PROFILE_SECTION_FORUMS_FAVORITES ] = esc_html__( 'Favorites', 'ultra-community' );

		return $arrForumsSubSections;
	}

	public static function getCustomTabsUserProfileSections()
	{
		if(!ModulesController::isModuleRegistered(ModulesController::MODULE_CUSTOM_TABS))
			return array();

		static $arrProfileSections = array();

		foreach ( PostTypeController::getPublishedPosts( PostTypeController::POST_TYPE_CUSTOM_TAB ) as $customTabPostType )
		{
			if ( ! ($customTabPublicModule = PostTypeController::getAssociatedPublicModuleInstance( $customTabPostType ) ) ) {
				continue;
			}

			if ( (int)$customTabPublicModule->getOption( CustomTabsAdminModule::OPTION_CUSTOM_TAB_TARGET ) !== CustomTabsAdminModule::CUSTOM_TAB_TARGET_USER_PROFILE_SECTION ) {
				continue;
			}

			$customTabUrl = $customTabPublicModule->getOption( CustomTabsAdminModule::OPTION_CUSTOM_TAB_URL );

			empty($customTabUrl) ?: $customTabPostType->PostUrl = esc_url($customTabUrl);
			$customTabPostType->PostTitle = esc_html($customTabPostType->PostTitle);

			$customTabPostType->IconClass = $customTabPublicModule->getOption( CustomTabsAdminModule::OPTION_CUSTOM_TAB_ICON );

			$arrProfileSections[ $customTabPostType->PostSlug ] = $customTabPostType;

		}


		return $arrProfileSections;
	}

	public static function getDefinedUserStatsCounters()
	{
		$arrCounters = array(
				self::USER_STATS_COUNTER_POSTS    => esc_html__('Posts', 'ultra-community'),
				self::USER_STATS_COUNTER_COMMENTS => esc_html__('Comments', 'ultra-community'),
		);

		if(ModulesController::isModuleRegistered(ModulesController::MODULE_USER_FRIENDS))
		{
			$arrCounters[self::USER_STATS_COUNTER_FRIENDS] = esc_html__('Friends', 'ultra-community');
		}

		if(ModulesController::isModuleRegistered(ModulesController::MODULE_USER_FOLLOWERS))
		{
			$arrCounters[self::USER_STATS_COUNTER_FOLLOWERS] = esc_html__('Followers', 'ultra-community');
			$arrCounters[self::USER_STATS_COUNTER_FOLLOWING] = esc_html__('Following', 'ultra-community');
		}

		return $arrCounters;
	}


	public function getDefaultOptions()
	{
		static $arrDefaultSettingOptions = null;
		if(null !== $arrDefaultSettingOptions)
			return $arrDefaultSettingOptions;

		$arrDefaultSettingOptions = array(

			self::OPTION_USER_PROFILE_SECTIONS => array(
					'LabelText'  => __('User Profile Menu Tabs', 'ultra-community'),
					'Value'      => array(self::PROFILE_SECTION_ACTIVITY, self::PROFILE_SECTION_POSTS, self::PROFILE_SECTION_COMMENTS, self::PROFILE_SECTION_GROUPS, self::PROFILE_SECTION_ABOUT,),
					'InputType'  => MchHtmlUtils::FORM_ELEMENT_SELECT,
					'HelpText'   => __('Defines the available sections for User Profile. For each available section a menu tab will be created. Each menu tab will be displayed in the same order you add them over here.', 'ultra-community')
			),


			self::OPTION_HEADER_STYLE_ID =>array(
					'Value'      => 3,
					'LabelText'  => __('Page Header Style', 'ultra-community'),
					'InputType'  => MchHtmlUtils::FORM_ELEMENT_SELECT,
					'HelpText'   => __('Select User Profile Header Style', 'ultra-community')
			),

			self::OPTION_HEADER_USER_PICTURE_STYLE =>array(
					'Value'      => self::PICTURE_STYLE_CIRCLE,
					'LabelText'  => __('User Picture Style', 'ultra-community'),
					'InputType'  => MchHtmlUtils::FORM_ELEMENT_SELECT,
					'HelpText'   => __('The social networks icons style', 'ultra-community')
			),

			self::OPTION_HEADER_STATS_COUNTERS =>array(
					'Value'      => array(self::USER_STATS_COUNTER_POSTS, self::USER_STATS_COUNTER_COMMENTS),
					'LabelText'  => __('User Statistics Counters', 'ultra-community'),
					'InputType'  => MchHtmlUtils::FORM_ELEMENT_SELECT,
					'HelpText'   => __('Select user statistics to be shown in the profile header', 'ultra-community')
			),

			self::OPTION_HEADER_TAGLINE_FIELDS =>array(
					'Value'      => null,
					'LabelText'  => __('User TagLine Fields', 'ultra-community'),
					'InputType'  => MchHtmlUtils::FORM_ELEMENT_SELECT,
					'HelpText'   => __('Select Fields to be shown in User TagLine ', 'ultra-community')
			),

			self::OPTION_HEADER_SHOW_ONLINE_STATUS =>array(
					'Value'      => true,
					'LabelText'  => __('Show User Online Status', 'ultra-community'),
					'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_CHECKBOX,
					'HelpText'   => __('Select if you prefer to show the user online status', 'ultra-community')
			),

			self::OPTION_HEADER_SHOW_SOCIAL_NETWORKS =>array(
					'Value'      => true,
					'LabelText'  => __('Show User Social Networks', 'ultra-community'),
					'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_CHECKBOX,
					'HelpText'   => __('Select if you prefer to show the user social networks links', 'ultra-community')
			),

			self::OPTION_HEADER_SOCIAL_NETWORKS_STYLE =>array(
					'Value'      => self::PICTURE_STYLE_CIRCLE,
					'LabelText'  => __('Social Networks Icons Style', 'ultra-community'),
					'InputType'  => MchHtmlUtils::FORM_ELEMENT_SELECT,
					'HelpText'   => __('The social networks icons style', 'ultra-community')
			),



			################################## Sidebar Settings ###########################################

			self::OPTION_SIDE_BAR_POSITION =>array(
				'Value'      => 'left',
				'LabelText'  => __('Page Sidebar Position', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_SELECT,
				'HelpText'   => __('The position of the sidebar.', 'ultra-community')
			),

			self::OPTION_SIDE_BAR_WIDGETS =>array(
				'Value'      => array_keys(WidgetsController::getUserAvailableWidgets()),
				'LabelText'  => __('Page Sidebar Widgets', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_SELECT,
				'HelpText'   => __('Choose which widgets to add in the user profile page sidebar. They will be added in the same order you select them over here!', 'ultra-community')
			),

		);

		return $arrDefaultSettingOptions;
	}

	public function validateModuleSettingsFields($arrSettingOptions)
	{
		$arrSettingOptions = $this->sanitizeModuleSettings($arrSettingOptions);


		if(isset($arrSettingOptions[self::OPTION_USER_PROFILE_SECTIONS]) && !empty($_POST[ 'ordered-' . $this->getSettingKey() ][self::OPTION_USER_PROFILE_SECTIONS][0]))
		{
			$arrSettingOptions[self::OPTION_USER_PROFILE_SECTIONS] = explode(',', $_POST[ 'ordered-' . $this->getSettingKey() ][self::OPTION_USER_PROFILE_SECTIONS][0]);

			$arrSettingOptions[self::OPTION_USER_PROFILE_SECTIONS] = array_filter($arrSettingOptions[self::OPTION_USER_PROFILE_SECTIONS]);

			$arrSettingOptions[self::OPTION_USER_PROFILE_SECTIONS] = array_map('sanitize_text_field', $arrSettingOptions[self::OPTION_USER_PROFILE_SECTIONS]);

			$arrSettingOptions[self::OPTION_USER_PROFILE_SECTIONS] = array_values($arrSettingOptions[self::OPTION_USER_PROFILE_SECTIONS]);

		}

		if(isset($arrSettingOptions[self::OPTION_HEADER_STATS_COUNTERS]) && !empty($_POST[ 'ordered-' . $this->getSettingKey() ][self::OPTION_HEADER_STATS_COUNTERS][0]))
		{
			$arrSettingOptions[self::OPTION_HEADER_STATS_COUNTERS] = explode(',', $_POST[ 'ordered-' . $this->getSettingKey() ][self::OPTION_HEADER_STATS_COUNTERS][0]);

			$arrSettingOptions[self::OPTION_HEADER_STATS_COUNTERS] = array_filter($arrSettingOptions[self::OPTION_HEADER_STATS_COUNTERS]);

			$arrSettingOptions[self::OPTION_HEADER_STATS_COUNTERS] = array_map('sanitize_text_field', $arrSettingOptions[self::OPTION_HEADER_STATS_COUNTERS]);

			$arrSettingOptions[self::OPTION_HEADER_STATS_COUNTERS] = array_values($arrSettingOptions[self::OPTION_HEADER_STATS_COUNTERS]);

		}

		if(isset($arrSettingOptions[self::OPTION_HEADER_TAGLINE_FIELDS]) && !empty($_POST[ 'ordered-' . $this->getSettingKey() ][self::OPTION_HEADER_TAGLINE_FIELDS][0]))
		{
			$arrSettingOptions[self::OPTION_HEADER_TAGLINE_FIELDS] = explode(',', $_POST[ 'ordered-' . $this->getSettingKey() ][self::OPTION_HEADER_TAGLINE_FIELDS][0]);

			$arrSettingOptions[self::OPTION_HEADER_TAGLINE_FIELDS] = array_filter($arrSettingOptions[self::OPTION_HEADER_TAGLINE_FIELDS]);

			$arrSettingOptions[self::OPTION_HEADER_TAGLINE_FIELDS] = array_map('sanitize_text_field', $arrSettingOptions[self::OPTION_HEADER_TAGLINE_FIELDS]);

			$arrSettingOptions[self::OPTION_HEADER_TAGLINE_FIELDS] = array_values($arrSettingOptions[self::OPTION_HEADER_TAGLINE_FIELDS]);

		}

		if(isset($arrSettingOptions[self::OPTION_SIDE_BAR_WIDGETS]) && !empty($_POST[ 'ordered-' . $this->getSettingKey() ][self::OPTION_SIDE_BAR_WIDGETS][0]))
		{
			$arrSettingOptions[self::OPTION_SIDE_BAR_WIDGETS] = explode(',', $_POST[ 'ordered-' . $this->getSettingKey() ][self::OPTION_SIDE_BAR_WIDGETS][0]);

			$arrSettingOptions[self::OPTION_SIDE_BAR_WIDGETS] = array_filter($arrSettingOptions[self::OPTION_SIDE_BAR_WIDGETS]);

			$arrSettingOptions[self::OPTION_SIDE_BAR_WIDGETS] = array_map('sanitize_text_field', $arrSettingOptions[self::OPTION_SIDE_BAR_WIDGETS]);

			$arrSettingOptions[self::OPTION_SIDE_BAR_WIDGETS] = array_values($arrSettingOptions[self::OPTION_SIDE_BAR_WIDGETS]);

		}

		PluginSettingsAdminModule::getInstance()->saveOption(PluginSettingsAdminModule::SETTINGS_LAST_UPDATED, MchHttpRequest::getServerRequestTime());

		return $arrSettingOptions;
	}

	public  function renderModuleSettingsSectionHeader(array $arrSectionInfo)
	{
		echo '<div class="uc-settings-section-header uc-clear"><h3>' . __('User Profile Appearance', 'ultra-community') . '</h3></div>';


		MchWpUtils::addFilterHook(self::FILTER_FIELD_SETTINGS_TABLE_ROW_OUTPUT, function ($fieldTableRowOutput, $fieldKey){

			if($fieldKey === UserProfileAppearanceAdminModule::OPTION_USER_PROFILE_SECTIONS)
			{
				return $this->addSettingsSectionDivider(esc_html__('Profile Page Header Settings', 'ultra-community'), $fieldTableRowOutput, false);
			}

			if($fieldKey === UserProfileAppearanceAdminModule::OPTION_SIDE_BAR_POSITION)
			{
				return $this->addSettingsSectionDivider(esc_html__('Profile Sidebar Settings', 'ultra-community'), $fieldTableRowOutput, true);
			}

			return 	$fieldTableRowOutput;

		}, 10, 2);

	}


	public function renderModuleSettingsField(array $arrSettingsField)
	{
		$fieldKey   = key( $arrSettingsField );
		$fieldValue = $this->getOption( $fieldKey );


		MchWpUtils::addFilterHook($this->getFieldAttributesFilterName($fieldKey) . '-output-html', function( $outputHtml, $arrAttr ) use ($fieldKey, $fieldValue){

			if(!in_array($fieldKey, array(UserProfileAppearanceAdminModule::OPTION_SIDE_BAR_WIDGETS, UserProfileAppearanceAdminModule::OPTION_USER_PROFILE_SECTIONS, UserProfileAppearanceAdminModule::OPTION_HEADER_TAGLINE_FIELDS, UserProfileAppearanceAdminModule::OPTION_HEADER_STATS_COUNTERS)))
			{
				return $outputHtml;
			}

			$hiddenElementOutputHtml = '<input type="hidden" name = "' . 'ordered-' . $arrAttr['name'] . '" value="' . implode(',', (array)$fieldValue) . '" />';

			return $hiddenElementOutputHtml . $outputHtml;

		}, 10, 2);


		MchWpUtils::addFilterHook($this->getFieldAttributesFilterName($fieldKey), function( $arrFieldAttributes ) use ( $fieldValue, $fieldKey) {



			if($fieldKey === UserProfileAppearanceAdminModule::OPTION_HEADER_TAGLINE_FIELDS)
			{
				$arrFieldAttributes['multiple'] = 'multiple';
				$arrFieldAttributes['class'][]  = 'uc-select2-multiple';
				$arrFieldAttributes['value']    = $fieldValue;
				$arrFieldAttributes['options']  = array();

				$arrAvailableFields = MembersDirectoryAdminModule::getUserTagLineSelectOptions();
				$arrFieldAttributes['options'] = (array)$arrFieldAttributes['options'];
				foreach((array)$fieldValue as $savedFormField)
				{
					if(!isset($arrAvailableFields[$savedFormField]))
						continue;

					$arrFieldAttributes['options'][$savedFormField] = $arrAvailableFields[$savedFormField];
					unset($arrAvailableFields[$savedFormField]);
				}

				foreach($arrAvailableFields as $key => $availableField)
				{
					$arrFieldAttributes['options'][$key] = $availableField;
				}

				return $arrFieldAttributes;
			}

			if($fieldKey === UserProfileAppearanceAdminModule::OPTION_SIDE_BAR_WIDGETS)
			{
				$arrFieldAttributes['multiple'] = 'multiple';
				$arrFieldAttributes['class'][]  = 'uc-select2-multiple';
				$arrFieldAttributes['value']    = $fieldValue;
				$arrFieldAttributes['options']  = array();

				$arrAvailableWidgets = WidgetsController::getUserAvailableWidgets();

				foreach((array)$fieldValue as $savedWidget)
				{
					if(!isset($arrAvailableWidgets[$savedWidget]))
						continue;

					$arrFieldAttributes['options'][$savedWidget] = $arrAvailableWidgets[$savedWidget];
					unset($arrAvailableWidgets[$savedWidget]);
				}

				foreach($arrAvailableWidgets as $key => $value)
				{
					$arrFieldAttributes['options'][$key] = $value;
				}

				return $arrFieldAttributes;
			}


			if($fieldKey === UserProfileAppearanceAdminModule::OPTION_SIDE_BAR_POSITION){

				$arrFieldAttributes['class'][] = 'uc-select2';
				$arrFieldAttributes['value']   = $fieldValue;
				$arrFieldAttributes['options'] = array( 'left' => __('Left', 'ultra-community'), 'right' => __('Right', 'ultra-community'));
				return $arrFieldAttributes;
			}


			if($fieldKey === UserProfileAppearanceAdminModule::OPTION_HEADER_STYLE_ID){

				$arrFieldAttributes['class'][] = 'uc-select2';
				$arrFieldAttributes['value']   = $fieldValue;

				$arrFieldAttributes['options'] = array();
				for($i = 1; $i <=3 ; ++$i)
				{
					$arrFieldAttributes['options'][$i] = sprintf(__('Header Style %d', 'ultra-community'), $i);
				}


				return $arrFieldAttributes;
			}


			if($fieldKey === UserProfileAppearanceAdminModule::OPTION_HEADER_USER_PICTURE_STYLE || $fieldKey === UserProfileAppearanceAdminModule::OPTION_HEADER_SOCIAL_NETWORKS_STYLE){

				$arrFieldAttributes['class'][] = 'uc-select2';
				$arrFieldAttributes['value']   = $fieldValue;
				$arrFieldAttributes['options'] = array( UserProfileAppearanceAdminModule::PICTURE_STYLE_CIRCLE => __('Circle', 'ultra-community'), UserProfileAppearanceAdminModule::PICTURE_STYLE_SQUARE => __('Square', 'ultra-community'));
				return $arrFieldAttributes;
			}

			if($fieldKey === UserProfileAppearanceAdminModule::OPTION_USER_PROFILE_SECTIONS)
			{
				$arrFieldAttributes['multiple'] = 'multiple';
				$arrFieldAttributes['class'][]  = 'uc-select2-multiple';
				$arrFieldAttributes['value']    = $fieldValue;
				$arrFieldAttributes['options']  = array();

				$arrSections = UserProfileAppearanceAdminModule::getDefinedUserProfileSections();

				empty($arrSections[UserProfileAppearanceAdminModule::PROFILE_SECTION_FORUMS_TOPICS]) ?: $arrSections[UserProfileAppearanceAdminModule::PROFILE_SECTION_FORUMS_TOPICS] =  esc_html__('Forums', 'ultra-community');

				unset($arrSections[UserProfileAppearanceAdminModule::PROFILE_SECTION_FORUMS_FAVORITES]);
				unset($arrSections[UserProfileAppearanceAdminModule::PROFILE_SECTION_FORUMS_REPLIES]);

				foreach ((array)$arrFieldAttributes['value'] as $index => $savedSection)
				{
					if(!isset($arrSections[$savedSection])){
						unset($arrFieldAttributes['value'][$index]);
						continue;
					}

					$arrFieldAttributes['options'][$savedSection] =  $arrSections[$savedSection];
				}

				foreach ($arrSections as $counterKey => $sectionName)
				{
					if(isset($arrFieldAttributes['options'][$counterKey]))
						continue;

					$arrFieldAttributes['options'][$counterKey] =  $sectionName;
				}

			}


			if($fieldKey === UserProfileAppearanceAdminModule::OPTION_HEADER_STATS_COUNTERS)
			{
				$arrFieldAttributes['multiple'] = 'multiple';
				$arrFieldAttributes['class'][]  = 'uc-select2-multiple';
				$arrFieldAttributes['value']    = $fieldValue;
				$arrFieldAttributes['options']  = array();

				$arrSections = UserProfileAppearanceAdminModule::getDefinedUserStatsCounters();

				foreach ((array)$arrFieldAttributes['value'] as $index => $savedSection)
				{
					if(!isset($arrSections[$savedSection])){
						unset($arrFieldAttributes['value'][$index]);
						continue;
					}

					$arrFieldAttributes['options'][$savedSection] =  $arrSections[$savedSection];
				}

				foreach ($arrSections as $counterKey => $sectionName)
				{
					if(isset($arrFieldAttributes['options'][$counterKey]))
						continue;

					$arrFieldAttributes['options'][$counterKey] =  $sectionName;
				}

			}



			return $arrFieldAttributes;
		});

		 parent::renderModuleSettingsField($arrSettingsField);

	}

}