<?php
/**
 * Copyright (c) 2016 Ultra Community (http://www.ultracommunity.com)
 */

namespace UltraCommunity\Modules\Forms\FormFields;


class UserNameOrEmailField extends BaseField
{
	public function __construct()
	{
		parent::__construct( parent::FIELD_TYPE_TEXT );
		$this->Label = __('Username or E-mail', 'ultra-community');
	}

}