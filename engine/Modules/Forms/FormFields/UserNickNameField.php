<?php

namespace UltraCommunity\Modules\Forms\FormFields;


class UserNickNameField extends BaseField
{
	public function __construct()
	{
		parent::__construct( parent::FIELD_TYPE_TEXT );
		$this->Label = __('Display Name', 'ultra-community');
	}

}