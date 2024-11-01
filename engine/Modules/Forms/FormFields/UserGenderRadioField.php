<?php

namespace UltraCommunity\Modules\Forms\FormFields;
use UltraCommunity\MchLib\Utils\MchUtils;
use UltraCommunity\UltraCommHooks;

class UserGenderRadioField extends BaseField
{
	public function __construct()
	{
		parent::__construct( parent::FIELD_TYPE_RADIO );

		$this->OptionsList =  array(
			'm' => __('Male', 'ultra-community'),
			'f' => __('Female', 'ultra-community')
		);

		$this->setOptionsList();

	}


	public function setOptionsList()
	{
		$this->OptionsList =  array(
			'm' => __('Male', 'ultra-community'),
			'f' => __('Female', 'ultra-community')
		);

		$this->OptionsList = (array)apply_filters(UltraCommHooks::FILTER_FORM_FIELD_GENDER_OPTIONS, $this->OptionsList);

	}

	public function __sleep()
	{
		$arrObjectProperties = get_object_vars(MchUtils::filterObjectEmptyProperties($this));
		unset(
				$arrObjectProperties['OptionsList']
		);

		return array_keys($arrObjectProperties);

	}
	public function __wakeup()
	{
		$this->setOptionsList();
	}
}