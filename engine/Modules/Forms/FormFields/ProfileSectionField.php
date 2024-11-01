<?php
/**
 * Created by PhpStorm.
 * User: 4over
 * Date: 3/22/18
 * Time: 1:49 PM
 */

namespace UltraCommunity\Modules\Forms\FormFields;


class ProfileSectionField extends BaseField
{
	public $Title = null;

	public function __construct()
	{
		parent::__construct();
	}

	public function toHtmlOutput(array $arrAdditionalAttributes = array(), $useFloatingLabel = false)
	{
		return null;
	}

}