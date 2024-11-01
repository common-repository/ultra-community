<?php
/**
 * Copyright (c) 2016 Ultra Community (http://www.ultracommunity.com)
 */

namespace UltraCommunity\Modules\Forms\FormFields;


class CheckBoxField extends BaseField
{
	public function __construct()
	{
		parent::__construct( parent::FIELD_TYPE_CHECKBOX );
	}

}