<?php

namespace UltraCommunity\Modules\Forms\FormFields;
use UltraCommunity\MchLib\Utils\MchUtils;
use UltraCommunity\UltraCommHooks;

class RadioButtonField extends BaseField
{
	public function __construct()
	{
		parent::__construct( parent::FIELD_TYPE_RADIO );
	}
	
}