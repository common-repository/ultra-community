<?php

namespace UltraCommunity\Modules\Forms\FormFields;


class EmailField extends BaseField
{
	public function __construct()
	{
		parent::__construct( parent::FIELD_TYPE_TEXT );
	}
}