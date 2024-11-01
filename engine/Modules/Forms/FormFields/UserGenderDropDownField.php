<?php
/**
 * Copyright (c) 2016 Ultra Community (http://www.ultracommunity.com)
 */

namespace UltraCommunity\Modules\Forms\FormFields;


class UserGenderDropDownField extends BaseField
{
	public function __construct()
	{
		parent::__construct( parent::FIELD_TYPE_SELECT );

		$this->OptionsList =  array(
			'm' => __('Male', 'ultra-community'),
			'f' => __('Female', 'ultra-community')
		);
	}
}