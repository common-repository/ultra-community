<?php
namespace  UltraCommunity\Modules\Appearance\General;


use UltraCommunity\MchLib\Utils\MchHexColor;
use UltraCommunity\Modules\BasePublicModule;

class GeneralAppearancePublicModule extends BasePublicModule
{
	protected function __construct()
	{
		parent::__construct();
	}


	public static function getPageColorSchemeClassName()
	{
		return 'uch-scheme-' . ((self::getOptionValue(GeneralAppearanceAdminModule::OPTION_PAGE_COLOR_CUSTOM_SCHEME)) ? 'custom' : \sanitize_html_class(self::getOptionValue(GeneralAppearanceAdminModule::OPTION_PAGE_COLOR_SCHEME)));
	}

	public static function showNavigationIconsOnTop()
	{
		return 2 === ((int)self::getOptionValue(GeneralAppearanceAdminModule::OPTION_NAV_BAR_ICONS_POSITION));
	}


	public static function getPageBackgroundColor($fallBackToDefault = true)
	{
		$optionValue = self::getInstance()->getOption(GeneralAppearanceAdminModule::OPTION_PAGE_BACKGROUND_COLOR);
		return (!empty($optionValue) || !$fallBackToDefault) ? $optionValue : self::getDefaultOptionValue(GeneralAppearanceAdminModule::OPTION_PAGE_BACKGROUND_COLOR);
	}


	private static function getDefaultOptionValue($optionName)
	{
		static $arrDefaultOptionsValues = null;
		null !== $arrDefaultOptionsValues ?: $arrDefaultOptionsValues = GeneralAppearanceAdminModule::getInstance()->getDefaultOptionsValues();

		return isset($arrDefaultOptionsValues[$optionName]) ? $arrDefaultOptionsValues[$optionName] : null;

	}

	private static function optionRequiresInlineStyle($optionName)
	{

		switch ($optionName)
		{
			case GeneralAppearanceAdminModule::OPTION_PAGE_BACKGROUND_COLOR :
				return 0 !== strcasecmp(self::getPageBackgroundColor(), self::getDefaultOptionValue($optionName));
		}

		return false;
	}

	private static function getOptionValue($optionKey)
	{
		static $arrAllOptionValues = null;
		(null !== $arrAllOptionValues) ?: $arrAllOptionValues = self::getInstance()->getAllSavedOptions();
		return isset($arrAllOptionValues[$optionKey]) ? $arrAllOptionValues[$optionKey] : null;
	}

	public static function getCustomCss()
	{
		$customCss = null;

		if($optionValue = self::getOptionValue(GeneralAppearanceAdminModule::OPTION_PAGE_COLOR_CUSTOM_SCHEME))
		{
			$colorObj = new MchHexColor($optionValue);
			$color      = $optionValue;
			$lightColor = '#' . $colorObj->lighten(10);
			$darkColor  = '#' . $colorObj->darken(10);
			$customCss = <<<CustomSchemeCss

.uch a:hover
{
	color:$color;
}

a.uc-button.uc-button-left-icon
{
	background-color:$lightColor;
}

.uc-hvr-overline-from-center:before,
.uc-hvr-underline-from-center:before
{
	background-color:$lightColor;
}

.uc-main-menu li.active-section a,
.uc-main-menu li.active-section a:hover
{
	color:$color;
}


CustomSchemeCss;

		}

		if(self::optionRequiresInlineStyle(GeneralAppearanceAdminModule::OPTION_PAGE_BACKGROUND_COLOR)){
			$customCss .= 'div.uch{background-color:' . self::getPageBackgroundColor() . '}';
		}

		if(!self::getOptionValue(GeneralAppearanceAdminModule::OPTION_NAV_BAR_SHOW_ICONS)){
			$customCss .= 'ul.uc-main-menu li i.fa, ul.uc-main-menu li svg{display:none} .uc-navbar-holder{min-height:5em;}';
		}

		if($optionValue = self::getOptionValue(GeneralAppearanceAdminModule::OPTION_NAV_BAR_BACKGROUND_COLOR)){
			$customCss .= ".uc-navbar-holder{background-color:$optionValue}";
		}

		if($optionValue = self::getOptionValue(GeneralAppearanceAdminModule::OPTION_NAV_BAR_ICONS_COLOR)){
			$customCss .= ".uc-navbar-holder i.fa{color:$optionValue}";
		}

		if($optionValue = self::getOptionValue(GeneralAppearanceAdminModule::OPTION_NAV_BAR_ICONS_HOVER_COLOR)){
			$customCss .= ".uc-navbar-holder a:hover i.fa{color:$optionValue}";
		}

		if($optionValue = self::getOptionValue(GeneralAppearanceAdminModule::OPTION_NAV_BAR_TABS_COLOR)){
			$customCss .= ".uc-navbar-holder a{color:$optionValue}";
		}


		$customCss .= self::getOptionValue(GeneralAppearanceAdminModule::OPTION_PAGE_CUSTOM_CSS);



		return $customCss;
	}

}