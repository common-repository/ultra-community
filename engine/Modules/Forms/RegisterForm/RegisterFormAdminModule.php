<?php
/**
 * Copyright (c) 2016 Ultra Community (http://www.ultracommunity.com)
 */

namespace UltraCommunity\Modules\Forms\RegisterForm;

use UltraCommunity\MchLib\Utils\MchHtmlUtils;
use UltraCommunity\MchLib\Utils\MchUtils;
use UltraCommunity\MchLib\Utils\MchWpUtils;
use UltraCommunity\MchLib\WordPress\Repository\WpPostRepository;
use UltraCommunity\Modules\Forms\BaseForm\BaseFormAdminModule;
use UltraCommunity\Controllers\PostTypeController;
use UltraCommunity\Modules\GeneralSettings\FrontPage\FrontPageSettingsAdminModule;
use UltraCommunity\UltraCommUtils;

class RegisterFormAdminModule extends BaseFormAdminModule
{
//	CONST REGISTRATION_IDENTIFIER = 'uc-';


	CONST OPTION_ASSIGNED_PAGE_ID   = 'API';
	CONST OPTION_ASSIGNED_PAGE_SLUG = 'APS';

	protected function __construct()
	{
		parent::__construct();
	}

	public function getDefaultOptions()
	{
		$arrDefaultOptions = parent::getDefaultOptions();

		$arrDefaultOptions[parent::OPTION_FORM_TITLE]['Value']               = __('New Register Form', 'ultra-community');
		$arrDefaultOptions[parent::OPTION_FORM_HEADER_TITLE]['Value']        = __('Sign up', 'ultra-community');
		$arrDefaultOptions[parent::OPTION_FORM_PRIMARY_BUTTON_TEXT]['Value'] = __('Sign up', 'ultra-community');


		unset($arrDefaultOptions[parent::OPTION_FORM_FORGOT_PASSWORD_LABEL]);
		unset($arrDefaultOptions[parent::OPTION_FORM_REMEMBER_ME_LABEL]);
		unset($arrDefaultOptions[parent::OPTION_FORM_MAX_WIDTH]);
		unset($arrDefaultOptions[parent::OPTION_ASSIGNED_USER_ROLES]);

		$arrDefaultOptions = MchUtils::addArrayKeyValueAfterSpecificKey(parent::OPTION_FORM_ALIGNMENT, $arrDefaultOptions, self::OPTION_ASSIGNED_PAGE_SLUG, array(
				'Value' => NULL,
				'LabelText'  => __('Assigned Page Permalink', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_TEXT
		));

		$arrDefaultOptions = MchUtils::addArrayKeyValueAfterSpecificKey(parent::OPTION_FORM_ALIGNMENT, $arrDefaultOptions, self::OPTION_ASSIGNED_PAGE_ID, array(
			'Value' => NULL,
			'LabelText'  => __('Assigned Page', 'ultra-community'),
			'InputType'  => MchHtmlUtils::FORM_ELEMENT_SELECT
		));


		return $arrDefaultOptions;
	}

	public  function validateModuleSettingsFields($arrSettingOptions)
	{
		$arrSettingOptions = $this->sanitizeModuleSettings($arrSettingOptions);

		if(empty($arrSettingOptions[self::OPTION_FORM_TITLE]))
		{
			$this->registerErrorMessage(__('Please provide a Name for this form!', 'ultra-community'));
			return $this->getAllSavedOptions();
		}

//		if(empty($arrSettingOptions[self::OPTION_FORM_MAX_WIDTH]))
//		{
//			$this->registerErrorMessage(__('Please provide the Maximum Width of this form !', 'ultra-community'));
//			return $this->getAllSavedOptions();
//		}

		if(empty($arrSettingOptions[self::OPTION_FORM_PRIMARY_BUTTON_TEXT]))
		{
			$this->registerErrorMessage(__('Please provide the Primary Button Text for this form !', 'ultra-community'));
			return $this->getAllSavedOptions();
		}

		if( ($wpPost = WpPostRepository::findByPostId($arrSettingOptions[self::OPTION_ASSIGNED_PAGE_ID]) )){
			if($wpPost->post_name !== $arrSettingOptions[self::OPTION_ASSIGNED_PAGE_SLUG]){
				$wpPost->post_name = $arrSettingOptions[self::OPTION_ASSIGNED_PAGE_SLUG];
				WpPostRepository::save($wpPost->to_array());
				$wpPost = WpPostRepository::findByPostId($wpPost->ID);
				$arrSettingOptions[self::OPTION_ASSIGNED_PAGE_SLUG] = $wpPost->post_name;
			}
		}


		$customPostType = (null !== $this->getCustomPostType()) ? $this->getCustomPostType() : PostTypeController::getPostTypeInstance(PostTypeController::POST_TYPE_REGISTER_FORM);

		$customPostType->PostTitle = $arrSettingOptions[self::OPTION_FORM_TITLE];

		PostTypeController::publishPostType($customPostType);

		$this->setCustomPostType($customPostType);

		return parent::validateModuleSettingsFields($arrSettingOptions);

	}


	public function renderModuleSettingsField(array $arrSettingsField)
	{
		$fieldKey   = key( $arrSettingsField );
		$fieldValue = $this->getOption( $fieldKey );


		if($fieldKey === self::OPTION_ASSIGNED_PAGE_ID)
		{
			MchWpUtils::addFilterHook($this->getFieldAttributesFilterName($fieldKey), function( $arrFieldAttributes ) use ($fieldKey, $fieldValue){

				$arrFieldAttributes['class'][] = 'uc-select2';
				$arrFieldAttributes['value']   = $fieldValue;

				$arrFieldAttributes['options'] = array(
						$fieldValue => null
				);

				if( $wpPost = WpPostRepository::findByPostId($fieldValue) ){
					$arrFieldAttributes['options'][$fieldValue] = esc_html($wpPost->post_title);
				}

				return $arrFieldAttributes;
			});

			MchWpUtils::addFilterHook($this->getFieldAttributesFilterName($fieldKey) .  '-output-html', function($fieldOutputHtml) use ($fieldKey, $fieldValue) {

				$previewUrl = UltraCommUtils::getPreviewRequestUrl($fieldValue);
				if(null === $previewUrl)
					return $fieldOutputHtml;

				return $fieldOutputHtml . '<a style = "display:inline-block; vertical-align:top; margin-left:25px;" target="__blank" href="'. $previewUrl. '">Preview Registration Page</a>';
			});

		}


		if( in_array($fieldKey, array(self::OPTION_ASSIGNED_PAGE_SLUG)) )
		{

			MchWpUtils::addFilterHook($this->getFieldAttributesFilterName($fieldKey) .  '-output-html', function($fieldOutputHtml) use($fieldKey) {

				return esc_url( home_url('/') ) . $fieldOutputHtml;

			});
		}


		return parent::renderModuleSettingsField($arrSettingsField);

	}



}