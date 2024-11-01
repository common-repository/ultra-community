<?php
/**
 * Created by PhpStorm.
 * User: 4over
 * Date: 5/29/19
 * Time: 12:09 PM
 */

namespace UltraCommunity\Modules\Forms\FormFields;


class SubscriptionLevelsField extends BaseField
{

	public $SubscriptionLevels = array();

	public function __construct()
	{
		parent::__construct();
	}

	public function toHtmlOutput(array $arrAdditionalAttributes = array(), $useFloatingLabel = false)
	{
		return null;
	}

}