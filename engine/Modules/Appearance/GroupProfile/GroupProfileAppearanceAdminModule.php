<?php


namespace UltraCommunity\Modules\Appearance\GroupProfile;


use UltraCommunity\Controllers\ModulesController;
use UltraCommunity\Controllers\PostTypeController;
use UltraCommunity\Controllers\WidgetsController;
use UltraCommunity\MchLib\Utils\MchHtmlUtils;
use UltraCommunity\MchLib\Utils\MchHttpRequest;
use UltraCommunity\MchLib\Utils\MchWpUtils;
use UltraCommunity\Modules\BaseAdminModule;
use UltraCommunity\Modules\CustomTabs\CustomTabsAdminModule;
use UltraCommunity\Modules\GeneralSettings\Plugin\PluginSettingsAdminModule;
use UltraCommunity\UltraCommHooks;

class GroupProfileAppearanceAdminModule extends BaseAdminModule
{
	CONST PROFILE_SECTION_ACTIVITY = 'activity';
	CONST PROFILE_SECTION_MEMBERS  = 'members';
	CONST PROFILE_SECTION_ABOUT    = 'about';


	CONST PICTURE_STYLE_CIRCLE  = 1;
	CONST PICTURE_STYLE_SQUARE  = 2;

	CONST GROUP_STATS_COUNTER_POSTS   = 'posts';
	CONST GROUP_STATS_COUNTER_MEMBERS = 'members';


	CONST OPTION_GROUP_PROFILE_SECTIONS = 'ProfileSections';

	CONST OPTION_HEADER_PICTURE_STYLE    = 'HeaderPictureStyle';
	CONST OPTION_HEADER_STATS_COUNTERS   = 'HeaderStatsCounters';

	CONST OPTION_SIDE_BAR_POSITION = 'SideBarPosition';
	CONST OPTION_SIDE_BAR_WIDGETS  = 'SideBarWidgets';

	public function __construct()
	{
		parent::__construct();
	}

	public static function getDefinedMenuSections()
	{
		/**
		 * @var $arrProfileSections array - the key is the section slug.
		 */
		$arrProfileSections = array(
			self::PROFILE_SECTION_ACTIVITY => __('Activity', 'ultra-community'),
			self::PROFILE_SECTION_MEMBERS  => __('Members', 'ultra-community'),
			self::PROFILE_SECTION_ABOUT    => __('About', 'ultra-community'),
		);

		foreach(self::getCustomTabsGroupProfileSections() as $sectionSlug => $customTabPostType)
		{
			$arrProfileSections[$sectionSlug] = $customTabPostType->PostTitle;
		}

		return $arrProfileSections;

	}


	public static function getCustomTabsGroupProfileSections()
	{

		if(!ModulesController::isModuleRegistered(ModulesController::MODULE_CUSTOM_TABS))
			return array();

		static $arrProfileSections = array();

		foreach ( PostTypeController::getPublishedPosts( PostTypeController::POST_TYPE_CUSTOM_TAB ) as $customTabPostType )
		{
			if ( ! ($customTabPublicModule = PostTypeController::getAssociatedPublicModuleInstance( $customTabPostType ) ) ) {
				continue;
			}

			if ( (int)$customTabPublicModule->getOption( CustomTabsAdminModule::OPTION_CUSTOM_TAB_TARGET ) !== CustomTabsAdminModule::CUSTOM_TAB_TARGET_GROUP_PROFILE_SECTION ) {
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

	public static function getDefinedGroupStatsCounters()
	{
		return array(
			self::GROUP_STATS_COUNTER_POSTS    => esc_html__('Posts', 'ultra-community'),
			self::GROUP_STATS_COUNTER_MEMBERS  => esc_html__('Members', 'ultra-community'),
		);

	}

	public function getDefaultOptions()
	{

		static $arrDefaultSettingOptions = null;
		if(null !== $arrDefaultSettingOptions)
			return $arrDefaultSettingOptions;

		$arrDefaultSettingOptions = array(

			self::OPTION_GROUP_PROFILE_SECTIONS => array(
				'LabelText'  => __('Group Profile Menu Tabs', 'ultra-community'),
				'Value'      => array_keys(self::getDefinedMenuSections()),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_SELECT,
				'HelpText'   => __('Defines the available sections for Group Profile. For each available section a menu tab will be created. Each menu tab will be displayed in the same order you add them over here.', 'ultra-community')
			),



			self::OPTION_HEADER_PICTURE_STYLE =>array(
				'Value'      => self::PICTURE_STYLE_CIRCLE,
				'LabelText'  => __('Group Picture Style', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_SELECT,
				'HelpText'   => __('Defines how the picture of the group will be styled', 'ultra-community')
			),

			self::OPTION_HEADER_STATS_COUNTERS =>array(
				'Value'      => array_keys(self::getDefinedGroupStatsCounters()),
				'LabelText'  => __('Group Statistics Counters', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_SELECT,
				'HelpText'   => __('Select group statistics to be shown in the header', 'ultra-community')
			),


			################################## Sidebar Settings ###########################################



			self::OPTION_SIDE_BAR_POSITION =>array(
				'Value'      => 'left',
				'LabelText'  => __('Page Sidebar Position', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_SELECT,
				'HelpText'   => __('The position of the sidebar.', 'ultra-community')
			),

			self::OPTION_SIDE_BAR_WIDGETS =>array(
				'Value'      => array_keys(WidgetsController::getGroupAvailableWidgets()),
				'LabelText'  => __('Page Sidebar Widgets', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_SELECT,
				'HelpText'   => __('Choose which widgets to add in the profile page sidebar. They will be added in the same order you select them over here!', 'ultra-community')
			),

		);

		return $arrDefaultSettingOptions;

	}

	public function validateModuleSettingsFields($arrSettingOptions)
	{
		$arrSettingOptions = $this->sanitizeModuleSettings($arrSettingOptions);


		if(isset($arrSettingOptions[self::OPTION_GROUP_PROFILE_SECTIONS]) && !empty($_POST[ 'ordered-' . $this->getSettingKey() ][self::OPTION_GROUP_PROFILE_SECTIONS][0]))
		{
			$arrSettingOptions[self::OPTION_GROUP_PROFILE_SECTIONS] = explode(',', $_POST[ 'ordered-' . $this->getSettingKey() ][self::OPTION_GROUP_PROFILE_SECTIONS][0]);

			$arrSettingOptions[self::OPTION_GROUP_PROFILE_SECTIONS] = array_filter($arrSettingOptions[self::OPTION_GROUP_PROFILE_SECTIONS]);

			$arrSettingOptions[self::OPTION_GROUP_PROFILE_SECTIONS] = array_map('sanitize_text_field', $arrSettingOptions[self::OPTION_GROUP_PROFILE_SECTIONS]);

			$arrSettingOptions[self::OPTION_GROUP_PROFILE_SECTIONS] = array_values($arrSettingOptions[self::OPTION_GROUP_PROFILE_SECTIONS]);

		}

		if(isset($arrSettingOptions[self::OPTION_HEADER_STATS_COUNTERS]) && !empty($_POST[ 'ordered-' . $this->getSettingKey() ][self::OPTION_HEADER_STATS_COUNTERS][0]))
		{
			$arrSettingOptions[self::OPTION_HEADER_STATS_COUNTERS] = explode(',', $_POST[ 'ordered-' . $this->getSettingKey() ][self::OPTION_HEADER_STATS_COUNTERS][0]);

			$arrSettingOptions[self::OPTION_HEADER_STATS_COUNTERS] = array_filter($arrSettingOptions[self::OPTION_HEADER_STATS_COUNTERS]);

			$arrSettingOptions[self::OPTION_HEADER_STATS_COUNTERS] = array_map('sanitize_text_field', $arrSettingOptions[self::OPTION_HEADER_STATS_COUNTERS]);

			$arrSettingOptions[self::OPTION_HEADER_STATS_COUNTERS] = array_values($arrSettingOptions[self::OPTION_HEADER_STATS_COUNTERS]);

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
		echo '<div class="uc-settings-section-header uc-clear"><h3>' . __('Group Profile Appearance', 'ultra-community') . '</h3></div>';


		MchWpUtils::addFilterHook(self::FILTER_FIELD_SETTINGS_TABLE_ROW_OUTPUT, function ($fieldTableRowOutput, $fieldKey){

			if($fieldKey === GroupProfileAppearanceAdminModule::OPTION_GROUP_PROFILE_SECTIONS)
			{
				return $this->addSettingsSectionDivider(esc_html__('Page Header Settings', 'ultra-community'), $fieldTableRowOutput, false);
			}

			if($fieldKey === GroupProfileAppearanceAdminModule::OPTION_SIDE_BAR_POSITION)
			{
				return $this->addSettingsSectionDivider(esc_html__('Page Sidebar Settings', 'ultra-community'), $fieldTableRowOutput, true);
			}

			return 	$fieldTableRowOutput;

		}, 10, 2);



	}



	public function renderModuleSettingsField(array $arrSettingsField)
	{
		$fieldKey   = key( $arrSettingsField );
		$fieldValue = $this->getOption( $fieldKey );


		MchWpUtils::addFilterHook($this->getFieldAttributesFilterName($fieldKey) . '-output-html', function( $outputHtml, $arrAttr ) use ($fieldKey, $fieldValue){

			if(!in_array($fieldKey, array(
				GroupProfileAppearanceAdminModule::OPTION_SIDE_BAR_WIDGETS,
				GroupProfileAppearanceAdminModule::OPTION_GROUP_PROFILE_SECTIONS,
				GroupProfileAppearanceAdminModule::OPTION_HEADER_STATS_COUNTERS)))
			{
				return $outputHtml;
			}

			$hiddenElementOutputHtml = '<input type="hidden" name = "' . 'ordered-' . $arrAttr['name'] . '" value="' . implode(',', (array)$fieldValue) . '" />';

			return $hiddenElementOutputHtml . $outputHtml;

		}, 10, 2);



		MchWpUtils::addFilterHook($this->getFieldAttributesFilterName($fieldKey), function( $arrFieldAttributes ) use ( $fieldValue, $fieldKey) {


			if($fieldKey === GroupProfileAppearanceAdminModule::OPTION_SIDE_BAR_WIDGETS)
			{
				$arrFieldAttributes['multiple'] = 'multiple';
				$arrFieldAttributes['class'][]  = 'uc-select2-multiple';
				$arrFieldAttributes['value']    = $fieldValue;
				$arrFieldAttributes['options']  = array();

//				$arrAvailableWidgets = array(
//					WidgetsController::WIDGET_USER_LATEST_POSTS    => esc_html__('Latest Posts', 'ultra-community'),
//					WidgetsController::WIDGET_USER_SOCIAL_NETWORKS => esc_html__('Social Networks', 'ultra-community'),
//				);

				$arrAvailableWidgets = WidgetsController::getGroupAvailableWidgets();

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


			if($fieldKey === GroupProfileAppearanceAdminModule::OPTION_SIDE_BAR_POSITION){

				$arrFieldAttributes['class'][] = 'uc-select2';
				$arrFieldAttributes['value']   = $fieldValue;
				$arrFieldAttributes['options'] = array( 'left' => __('Left', 'ultra-community'), 'right' => __('Right', 'ultra-community'));
				return $arrFieldAttributes;
			}



			if($fieldKey === GroupProfileAppearanceAdminModule::OPTION_HEADER_PICTURE_STYLE){

				$arrFieldAttributes['class'][] = 'uc-select2';
				$arrFieldAttributes['value']   = $fieldValue;
				$arrFieldAttributes['options'] = array( GroupProfileAppearanceAdminModule::PICTURE_STYLE_CIRCLE => __('Circle', 'ultra-community'), GroupProfileAppearanceAdminModule::PICTURE_STYLE_SQUARE => __('Square', 'ultra-community'));
				return $arrFieldAttributes;
			}

			if($fieldKey === GroupProfileAppearanceAdminModule::OPTION_GROUP_PROFILE_SECTIONS)
			{
				$arrFieldAttributes['multiple'] = 'multiple';
				$arrFieldAttributes['class'][]  = 'uc-select2-multiple';
				$arrFieldAttributes['value']    = $fieldValue;
				$arrFieldAttributes['options']  = array();

				$arrSections = GroupProfileAppearanceAdminModule::getDefinedMenuSections();

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


			if($fieldKey === GroupProfileAppearanceAdminModule::OPTION_HEADER_STATS_COUNTERS)
			{
				$arrFieldAttributes['multiple'] = 'multiple';
				$arrFieldAttributes['class'][]  = 'uc-select2-multiple';
				$arrFieldAttributes['value']    = $fieldValue;
				$arrFieldAttributes['options']  = array();

				$arrSections = GroupProfileAppearanceAdminModule::getDefinedGroupStatsCounters();

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