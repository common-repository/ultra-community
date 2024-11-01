<?php
/**
 * Copyright (c) 2016 Ultra Community (http://www.ultracommunity.com)
 */

namespace UltraCommunity\Modules\Forms\FormFields;

use UltraCommunity\MchLib\Utils\MchWpUtils;

class DividerField extends BaseField
{
	public $LineHeight = '1px';
	public $LineStyle  = 'solid';
	public $LineColor  = '#e4e4e4';

	public $Text = null;

	public $MarginTop    = '10px';
	public $MarginBottom = '10px';

	public $TextAlign = 'center';
	public $TextColor = '#e4e4e4';
	
	//public $TextTransform = '';

	public function __construct()
	{
		parent::__construct(null);
	}

	public function toHtmlOutput(array $arrAdditionalAttributes = array(), $useFloatingLabel = false)
	{
		$this->MarginTop    = (int)$this->MarginTop  . 'px';
		$this->MarginBottom = (int)$this->MarginBottom . 'px';
		$this->LineHeight   = (int)$this->LineHeight . 'px';
		$this->Text         = MchWpUtils::stripHtmlTags($this->Text);

		$containerStyle  = 'style="';
		$containerStyle .= "margin: $this->MarginTop 0 $this->MarginBottom;";
		$containerStyle .= "border-top:$this->LineHeight $this->LineStyle $this->LineColor;";
		$containerStyle .= "text-align:$this->TextAlign;";
		$containerStyle .= '" ';

		$dividerStyle  = 'style="';

		//empty($this->TextTransform) ?: $dividerStyle .= "text-transform: $this->TextTransform;";
		empty($this->TextColor)     ?: $dividerStyle .= "color: $this->TextColor;";

		//$dividerStyle .= "text-transform: none;";


		switch($this->TextAlign)
		{
			case 'left'   : $dividerStyle .= "padding:0 15px 0 0;"; break;
			case 'center' : $dividerStyle .= "padding:0 15px 0;"; break;
			case 'right'  : $dividerStyle .= "padding:0 0 0 15px;"; break;

		}

		$dividerStyle .= '" ';

		$fieldOutput  = "<div $containerStyle class=\"uc-section-divider\">";

		empty($this->Text)	?: $fieldOutput .= "<span $dividerStyle  class='uc-vertical-align-middle'>" . $this->Text . '</span>';

		$fieldOutput .= '</div>';

		return $fieldOutput;
	}

//	public function getSettingsContentForAdminModal()
//	{
//
//	}

}