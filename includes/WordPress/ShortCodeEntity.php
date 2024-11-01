<?php
/**
 * Created by PhpStorm.
 * User: 4over
 * Date: 4/13/17
 * Time: 11:22 AM
 */

namespace UltraCommunity\MchLib\WordPress;


class ShortCodeEntity
{
	public $TagName    = null;
	public $Attributes = null;
	public $Content    = null;

	public function __construct($tagName = null, $arrAttrinutes = null, $content = null)
	{
		$this->TagName = $tagName;
		$this->Attributes = $arrAttrinutes;
		$this->Content = $content;
	}
}