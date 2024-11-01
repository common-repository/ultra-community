<?php
/**
 * Copyright (c) 2016 Ultra Community (http://www.ultracommunity.com)
 */

namespace UltraCommunity\Modules\Forms\FormFields;


class DropDownField extends BaseField
{
	public function __construct()
	{
		parent::__construct( parent::FIELD_TYPE_SELECT );
	}
	public function getSettingsContentForAdminModal()
	{
		
	}

}