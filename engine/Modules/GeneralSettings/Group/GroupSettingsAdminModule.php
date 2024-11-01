<?php


namespace UltraCommunity\Modules\GeneralSettings\Group;
use UltraCommunity\Controllers\PostTypeController;
use UltraCommunity\Controllers\UserRoleController;
use UltraCommunity\MchLib\Utils\MchHtmlUtils;
use UltraCommunity\MchLib\Utils\MchWpUtils;
use UltraCommunity\Modules\BaseAdminModule;
use UltraCommunity\Modules\UserRole\UserRoleAdminModule;

class GroupSettingsAdminModule  extends BaseAdminModule
{
	CONST OPTION_GROUPS_ENABLED         = 'IsEnabled';
	CONST OPTION_CREATE_USER_ROLES_ALLOWED  = 'CreateAllowed';

	protected function __construct()
	{
		parent::__construct();
	}

	public function getDefaultOptions()
	{
		static $arrDefaultSettingOptions = null;
		if(null !== $arrDefaultSettingOptions)
			return $arrDefaultSettingOptions;

		$arrDefaultSettingOptions = array(

			self::OPTION_GROUPS_ENABLED => array(
				'Value'      => true,
				'LabelText'  => __('Enable Groups Module', 'ultra-community'),
				'HelpText'   => __('Disabling this option the Groups Module will be turned off!', 'ultra-community'),
				'InputType'  => MchHtmlUtils::FORM_ELEMENT_INPUT_CHECKBOX
			),

		);

		return $arrDefaultSettingOptions;

	}

	public  function validateModuleSettingsFields($arrSettingOptions)
	{
		return $arrSettingOptions;
	}

	public function renderModuleSettingsField(array $arrSettingsField)
	{
		$fieldKey   = key( $arrSettingsField );
		$fieldValue = $this->getOption( $fieldKey );


//		MchWpUtils::addFilterHook($this->getFieldAttributesFilterName($fieldKey), function( $arrFieldAttributes ) use ($fieldKey, $fieldValue){
//
//		});


		return parent::renderModuleSettingsField($arrSettingsField);
	}
}