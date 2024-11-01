<?php
namespace  UltraCommunity\Modules\Appearance\General;


use UltraCommunity\MchLib\Utils\MchHtmlUtils;
use UltraCommunity\MchLib\Utils\MchValidator;
use UltraCommunity\MchLib\Utils\MchWpUtils;
use UltraCommunity\Modules\BaseAdminModule;

class GeneralAppearanceAdminModule extends BaseAdminModule
{

	CONST PAGE_COLOR_SCHEME_BLUE = 'blue';
	CONST PAGE_COLOR_SCHEME_GREY = 'grey';
	CONST PAGE_COLOR_SCHEME_GREEN = 'green';
	CONST PAGE_COLOR_SCHEME_PURPLE = 'purple';

	CONST OPTION_PAGE_BACKGROUND_COLOR     = 'PageBgColor';
	CONST OPTION_PAGE_MAXIMUM_WIDTH        = 'PageMaxWidth';
	CONST OPTION_PAGE_COLOR_SCHEME         = 'PageColorScheme';
	CONST OPTION_PAGE_COLOR_CUSTOM_SCHEME  = 'PageColorCustomScheme';
	CONST OPTION_PAGE_CUSTOM_CSS           = 'PageCustomCss';


	CONST OPTION_NAV_BAR_SHOW_ICONS        = "UserNavBarShowIcons";
	CONST OPTION_NAV_BAR_ICONS_POSITION    = "UserNavBarIconsPos";
	CONST OPTION_NAV_BAR_ICONS_COLOR       = 'UserNavBarIconsColor';
	CONST OPTION_NAV_BAR_ICONS_HOVER_COLOR = 'UserNavBarIconsHColor';
	CONST OPTION_NAV_BAR_TABS_COLOR        = 'UserNavBarTabsColor';
	CONST OPTION_NAV_BAR_TABS_HOVER_COLOR  = 'UserNavBarTabsHColor';
	CONST OPTION_NAV_BAR_BACKGROUND_COLOR  = 'UserNavBarBgColor';



	public function getDefaultOptions()
	{
		static $arrDefaultSettingOptions = null;
		if(null !== $arrDefaultSettingOptions)
			return $arrDefaultSettingOptions;

		$arrDefaultSettingOptions =  array(

			self::OPTION_PAGE_MAXIMUM_WIDTH =>array(
				'Value'      => '1170px',
				'LabelText'  => __('Page Maximum Width', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_TEXT,
				'HelpText'   => __('The maximum width of the page! The value must be entered in pixels (e.g 1170px) or percentages (e.g 92%)', 'ultra-community')
			),

			self::OPTION_PAGE_BACKGROUND_COLOR =>array(
				'Value'      => '#f9f9f9',
				'LabelText'  => __('Page Background Color', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_TEXT,
				'HelpText'   => __('The background color of the page!', 'ultra-community')
			),

			self::OPTION_PAGE_COLOR_SCHEME =>array(
					'Value'      => self::PAGE_COLOR_SCHEME_GREY,
					'LabelText'  => __('Page Color Scheme', 'ultra-community'),
					'InputType'  => MchHtmlUtils::FORM_ELEMENT_SELECT,
					'HelpText'   => __('The page color scheme!', 'ultra-community')
			),

//			self::OPTION_PAGE_COLOR_CUSTOM_SCHEME =>array(
//					'Value'      => null,
//					'LabelText'  => __('Page Custom Color Scheme', 'ultra-community'),
//					'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_TEXT,
//					'HelpText'   => __('Define the page custom color scheme. If this option is not empty, the custom color will be used insted of the predefined Page Color Scheme!', 'ultra-community')
//			),

			self::OPTION_NAV_BAR_SHOW_ICONS =>array(
				'Value'      => true,
				'LabelText'  => __('Show Navigation Icons', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_CHECKBOX,
				'HelpText'   => __('Show the Font Awesome icons for each navigation tab', 'ultra-community')
			),

			self::OPTION_NAV_BAR_ICONS_POSITION =>array(
				'Value'      => 1,
				'LabelText'  => __('Navigation Icons Position', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_SELECT,
				'HelpText'   => __('Show the Font Awesome icons for each navigation tab', 'ultra-community')
			),

			self::OPTION_NAV_BAR_ICONS_COLOR =>array(
				'Value'      => null,
				'LabelText'  => __('Navigation Bar Icons Color', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_TEXT,
				'HelpText'   => __('The color of the navigation bar icons!', 'ultra-community')
			),

			self::OPTION_NAV_BAR_ICONS_HOVER_COLOR =>array(
				'Value'      => null,
				'LabelText'  => __('Navigation Bar Icons Hover Color', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_TEXT,
				'HelpText'   => __('The color of the navigation bar icons on hover!', 'ultra-community')
			),


			self::OPTION_NAV_BAR_TABS_COLOR =>array(
				'Value'      => null,
				'LabelText'  => __('Navigation Bar Tabs Color', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_TEXT,
				'HelpText'   => __('The color of the navigation bar tabs link!', 'ultra-community')
			),

			self::OPTION_NAV_BAR_TABS_HOVER_COLOR =>array(
				'Value'      => null,
				'LabelText'  => __('Navigation Bar Tabs Hover Color', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_TEXT,
				'HelpText'   => __('The color of the navigation bar tabs link on hover!', 'ultra-community')
			),


			self::OPTION_NAV_BAR_BACKGROUND_COLOR =>array(
				'Value'      => null,
				'LabelText'  => __('Navigation Bar Background Color', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_TEXT,
				'HelpText'   => __('The background color of the navigation bar!', 'ultra-community')
			),


			self::OPTION_PAGE_CUSTOM_CSS => array(
					'Value'      => null,
					'LabelText'  => __('Page Custom CSS', 'ultra-community'),
					'HelpText'   => __('Add page custom CSS. This CSS will be loaded on every UltraCommunity page', 'ultra-community'),
					'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_TEXTAREA,

			),


		);

		return $arrDefaultSettingOptions;

	}

	public static function getPageColorSchemes()
	{
//		static $arrColorSchemes = array(
//				self::PAGE_COLOR_SCHEME_BLUE => array('name' => __('Blue', 'ultra-community'), 'color' => '#6b6b6b',  'light-color' => '', 'dark-clor' => ''),
//				self::PAGE_COLOR_SCHEME_GREY => array('name' => __('Grey', 'ultra-community'), 'color' => '#6b6b6b',  'light-color' => '', 'dark-clor' => ''),
//		);

		return array(

				self::PAGE_COLOR_SCHEME_GREY   => __('Grey', 'ultra-community'),
				self::PAGE_COLOR_SCHEME_BLUE   => __('Blue', 'ultra-community'),
				self::PAGE_COLOR_SCHEME_GREEN  => __('Green', 'ultra-community'),
				self::PAGE_COLOR_SCHEME_PURPLE => __('Purple', 'ultra-community'),
		);

//		return $arrColorSchemes;
	}


	public function validateModuleSettingsFields($arrSettingOptions)
	{
		$arrSettingOptions = $this->sanitizeModuleSettings($arrSettingOptions);

		if(!empty($arrSettingOptions[self::OPTION_PAGE_BACKGROUND_COLOR])){
			if($arrSettingOptions[self::OPTION_PAGE_BACKGROUND_COLOR][0] !== '#'){
				$arrSettingOptions[self::OPTION_PAGE_BACKGROUND_COLOR] = '#' . $arrSettingOptions[self::OPTION_PAGE_BACKGROUND_COLOR];
			}
			if(!MchValidator::isHexColor($arrSettingOptions[self::OPTION_PAGE_BACKGROUND_COLOR])){
				$this->registerErrorMessage(__('Invalid HEX color received for Page Background Color', 'ultra-community') );
				return $this->getAllSavedOptions();
			}
		}

		if(!empty($arrSettingOptions[self::OPTION_PAGE_COLOR_CUSTOM_SCHEME])){
			if($arrSettingOptions[self::OPTION_PAGE_COLOR_CUSTOM_SCHEME][0] !== '#'){
				$arrSettingOptions[self::OPTION_PAGE_COLOR_CUSTOM_SCHEME] = '#' . $arrSettingOptions[self::OPTION_PAGE_COLOR_CUSTOM_SCHEME];
			}
			if(!MchValidator::isHexColor($arrSettingOptions[self::OPTION_PAGE_COLOR_CUSTOM_SCHEME])){
				$this->registerErrorMessage(__('Invalid HEX color received for Page Background Color', 'ultra-community') );
				return $this->getAllSavedOptions();
			}
		}

		if(!empty($arrSettingOptions[self::OPTION_PAGE_MAXIMUM_WIDTH])){

			$arrSettingOptions[self::OPTION_PAGE_MAXIMUM_WIDTH] = ( (int)$arrSettingOptions[self::OPTION_PAGE_MAXIMUM_WIDTH] ) . (strpos($arrSettingOptions[self::OPTION_PAGE_MAXIMUM_WIDTH], '%') ? '%' : 'px');
		}

		$this->registerSuccessMessage(__('Your changes were successfully saved!', 'ultra-community'));
		return $arrSettingOptions;
	}

	public  function renderModuleSettingsSectionHeader(array $arrSectionInfo)
	{
		echo '<div class="uc-settings-section-header uc-clear"><h3>' . __('General Settings', 'ultra-community') . '</h3></div>';

		MchWpUtils::addFilterHook(self::FILTER_FIELD_SETTINGS_TABLE_ROW_OUTPUT, function ($fieldTableRowOutput, $fieldKey){

			if($fieldKey === GeneralAppearanceAdminModule::OPTION_NAV_BAR_SHOW_ICONS)
			{
				return $this->addSettingsSectionDivider(esc_html__('Navigation Bar Settings', 'ultra-community'), $fieldTableRowOutput, true);
			}


			if($fieldKey === GeneralAppearanceAdminModule::OPTION_PAGE_CUSTOM_CSS)
			{
				return $this->addSettingsSectionDivider(esc_html__('Page Custom CSS', 'ultra-community'), $fieldTableRowOutput, true);
			}


			return 	$fieldTableRowOutput;

		}, 10, 2);


	}


	public function renderModuleSettingsField(array $arrSettingsField)
	{
		$fieldKey   = key( $arrSettingsField );
		$fieldValue = $this->getOption( $fieldKey );

		MchWpUtils::addFilterHook($this->getFieldAttributesFilterName($fieldKey), function( $arrFieldAttributes ) use ( $fieldValue, $fieldKey) {

			if(in_array($fieldKey, array(
				GeneralAppearanceAdminModule::OPTION_PAGE_BACKGROUND_COLOR,
				GeneralAppearanceAdminModule::OPTION_PAGE_COLOR_CUSTOM_SCHEME,
				GeneralAppearanceAdminModule::OPTION_NAV_BAR_BACKGROUND_COLOR,
				GeneralAppearanceAdminModule::OPTION_NAV_BAR_ICONS_COLOR,
				GeneralAppearanceAdminModule::OPTION_NAV_BAR_ICONS_HOVER_COLOR, GeneralAppearanceAdminModule::OPTION_NAV_BAR_TABS_COLOR, GeneralAppearanceAdminModule::OPTION_NAV_BAR_TABS_HOVER_COLOR,
			))){
				$arrFieldAttributes['class'][] = 'uc-color-picker';
				return $arrFieldAttributes;

			}

			if($fieldKey === GeneralAppearanceAdminModule::OPTION_PAGE_COLOR_SCHEME)
			{
				$arrFieldAttributes['class'][] = 'uc-select2';
				$arrFieldAttributes['value']   = $fieldValue;
				$arrFieldAttributes['options'] = GeneralAppearanceAdminModule::getPageColorSchemes();
				return $arrFieldAttributes;
			}


			if($fieldKey === GeneralAppearanceAdminModule::OPTION_NAV_BAR_ICONS_POSITION)
			{
				$arrFieldAttributes['class'][] = 'uc-select2';
				$arrFieldAttributes['value']   = $fieldValue;
				$arrFieldAttributes['options'] = array( 1 => __('Left', 'ultra-community'), 2 => __('Top', 'ultra-community'));
				return $arrFieldAttributes;
			}



			return $arrFieldAttributes;


		});

		return parent::renderModuleSettingsField($arrSettingsField);

	}

}