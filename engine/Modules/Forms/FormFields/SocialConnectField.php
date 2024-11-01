<?php

namespace UltraCommunity\Modules\Forms\FormFields;

class SocialConnectField extends BaseField
{
	public $SocialConfigPostTypeId = null;

	public function __construct()
	{
		parent::__construct();
	}
	
	public function toHtmlOutput(array $arrAdditionalAttributes = array(), $useFloatingLabel = false)
	{
		return null;
	}
	
}