<?php
/**
 * Copyright (c) 2016 Ultra Community (http://www.ultracommunity.com)
 */

namespace UltraCommunity\Modules\Forms\FormFields;

use UltraCommunity\MchLib\Utils\MchWpUtils;

class SocialNetworkUrlField extends BaseField
{
	public $NetworkId = null;

	CONST SOCIAL_NETWORK_BITBUCKET      = 1;
	CONST SOCIAL_NETWORK_BITCOIN        = 2;
	CONST SOCIAL_NETWORK_DRIBBBLE       = 3;
	CONST SOCIAL_NETWORK_DROPBOX        = 4;
	CONST SOCIAL_NETWORK_FACEBOOK       = 5;
	CONST SOCIAL_NETWORK_FLICKR         = 6;
	CONST SOCIAL_NETWORK_FOURSQUARE     = 7;
	CONST SOCIAL_NETWORK_GITHUB         = 8;
	CONST SOCIAL_NETWORK_GOOGLE_PLUS    = 9;
	CONST SOCIAL_NETWORK_INSTAGRAM      = 10;
	CONST SOCIAL_NETWORK_LINKEDIN       = 11;
	CONST SOCIAL_NETWORK_LINUX          = 12;
	CONST SOCIAL_NETWORK_PAGELINES      = 13;
	CONST SOCIAL_NETWORK_PINTEREST      = 14;
	CONST SOCIAL_NETWORK_RENREN         = 15;
	CONST SOCIAL_NETWORK_SKYPE          = 16;
	CONST SOCIAL_NETWORK_STACK_EXCHANGE = 17;
	CONST SOCIAL_NETWORK_STACK_OVERFLOW = 18;
	CONST SOCIAL_NETWORK_TRELLO         = 19;
	CONST SOCIAL_NETWORK_TUMBLR         = 20;
	CONST SOCIAL_NETWORK_TWITTER        = 21;
	CONST SOCIAL_NETWORK_VIMEO          = 22;
	CONST SOCIAL_NETWORK_VK             = 23;
	CONST SOCIAL_NETWORK_WEIBO          = 24;
	CONST SOCIAL_NETWORK_WINDOWS        = 25;
	CONST SOCIAL_NETWORK_XING           = 26;
	CONST SOCIAL_NETWORK_YOUTUBE        = 27;
	CONST SOCIAL_NETWORK_WORDPRESS      = 28;
	CONST SOCIAL_NETWORK_SLACK          = 29;
	CONST SOCIAL_NETWORK_AMAZON         = 30;
	CONST SOCIAL_NETWORK_ITUNES         = 31;

	public function __construct()
	{
		parent::__construct(parent::FIELD_TYPE_TEXT);
	}


	public function getNetworkName($networkId)
	{
		$arrNetworks = self::getAllNetworks();
		return isset($arrNetworks[$networkId]) ? $arrNetworks[$networkId]->Name : null;
	}

	public function getFontAwesomeClass($networkId)
	{
		$arrNetworks = self::getAllNetworks();

		$networkId = (int)$networkId;

		if(!isset($arrNetworks[$networkId]) || empty($arrNetworks[$networkId]->Name))
			return null;

		if($networkId === self::SOCIAL_NETWORK_GOOGLE_PLUS)
		{
			return 'google-plus';
		}

		if($networkId === self::SOCIAL_NETWORK_ITUNES)
		{
			return 'music';
		}

		return MchWpUtils::formatUrlPath($arrNetworks[$networkId]->Name);

	}

	public function getIconColor($networkId)
	{
		$arrNetworks = self::getAllNetworks();
		if(!isset($arrNetworks[$networkId]) || empty($arrNetworks[$networkId]->IconColor))
			return null;

		return $arrNetworks[$networkId]->IconColor;

	}

	public function getIconBackgroundColor($networkId)
	{
		$arrNetworks = self::getAllNetworks();
		if(!isset($arrNetworks[$networkId]) || empty($arrNetworks[$networkId]->IconBgColor))
			return null;

		return $arrNetworks[$networkId]->IconBgColor;

	}

	public function getAllNetworks()
	{
		static $arrNetworks = null;
		if(null !== $arrNetworks)
			return $arrNetworks;

		$arrNetworks = array(
			self::SOCIAL_NETWORK_AMAZON         => array('Name' => 'Amazon',         'IconBgColor' => '#146eb4'),
			self::SOCIAL_NETWORK_BITBUCKET      => array('Name' => 'BitBucket',      'IconBgColor' => '#003366'),
			self::SOCIAL_NETWORK_BITCOIN        => array('Name' => 'BitCoin',        'IconBgColor' => '#F7931A'),
			self::SOCIAL_NETWORK_DRIBBBLE       => array('Name' => 'Dribbble',       'IconBgColor' => '#ec4a89'),
			self::SOCIAL_NETWORK_DROPBOX        => array('Name' => 'Dropbox',        'IconBgColor' => '#018BD3'),
			self::SOCIAL_NETWORK_FACEBOOK       => array('Name' => 'Facebook',       'IconBgColor' => '#3b5998'),
			self::SOCIAL_NETWORK_FLICKR         => array('Name' => 'Flickr',         'IconBgColor' => '#FF0084'),
			self::SOCIAL_NETWORK_FOURSQUARE     => array('Name' => 'FourSquare',     'IconBgColor' => '#0086BE'),
			self::SOCIAL_NETWORK_GITHUB         => array('Name' => 'Github',         'IconBgColor' => '#070709'),
			self::SOCIAL_NETWORK_GOOGLE_PLUS    => array('Name' => 'Google+',        'IconBgColor' => '#c03121'),
			self::SOCIAL_NETWORK_INSTAGRAM      => array('Name' => 'Instagram',      'IconBgColor' => '#fda93b'),
			self::SOCIAL_NETWORK_ITUNES         => array('Name' => 'iTunes',         'IconBgColor' => '#da1884'),
			self::SOCIAL_NETWORK_LINKEDIN       => array('Name' => 'LinkedIn',       'IconBgColor' => '#0085AE'),
			self::SOCIAL_NETWORK_LINUX          => array('Name' => 'Linux',          'IconBgColor' => '#FBC002'),
			self::SOCIAL_NETWORK_PAGELINES      => array('Name' => 'PageLines',      'IconBgColor' => '#3984EA'),
			self::SOCIAL_NETWORK_PINTEREST      => array('Name' => 'Pinterest',      'IconBgColor' => '#CC2127'),
			self::SOCIAL_NETWORK_RENREN         => array('Name' => 'RenRen',         'IconBgColor' => '#025DAC'),
			self::SOCIAL_NETWORK_SKYPE          => array('Name' => 'Skype',          'IconBgColor' => '#01AEF2'),
			self::SOCIAL_NETWORK_STACK_EXCHANGE => array('Name' => 'Stack Exchange', 'IconBgColor' => '#245590'),
			self::SOCIAL_NETWORK_STACK_OVERFLOW => array('Name' => 'Stack Overflow', 'IconBgColor' => '#FF7300'),
			self::SOCIAL_NETWORK_TRELLO         => array('Name' => 'Trello',         'IconBgColor' => '#265A7F'),
			self::SOCIAL_NETWORK_TUMBLR         => array('Name' => 'Tumblr',         'IconBgColor' => '#314E6C'),
			self::SOCIAL_NETWORK_TWITTER        => array('Name' => 'Twitter',        'IconBgColor' => '#01bbf7'), //#009AD5
			self::SOCIAL_NETWORK_VIMEO          => array('Name' => 'Vimeo',          'IconBgColor' => '#229ACC'),
			self::SOCIAL_NETWORK_VK             => array('Name' => 'VK',             'IconBgColor' => '#375474'),
			self::SOCIAL_NETWORK_WEIBO          => array('Name' => 'Weibo',          'IconBgColor' => '#D72B2B'),
			self::SOCIAL_NETWORK_WINDOWS        => array('Name' => 'Windows',        'IconBgColor' => '#12B6F3'),
			self::SOCIAL_NETWORK_XING           => array('Name' => 'Xing',           'IconBgColor' => '#00555C'),
			self::SOCIAL_NETWORK_YOUTUBE        => array('Name' => 'YouTube',        'IconBgColor' => '#C52F30'),
			self::SOCIAL_NETWORK_WORDPRESS      => array('Name' => 'WordPress',      'IconBgColor' => '#0087BE'),
			self::SOCIAL_NETWORK_SLACK          => array('Name' => 'Slack',          'IconBgColor' => '#2ab27b'),

		);

		foreach ($arrNetworks as $networkId => &$networkInfo){
			$networkInfo = (object)$networkInfo;
			$networkInfo->Id = $networkId;
			$networkInfo->IconBgColor = strtolower($networkInfo->IconBgColor);
			switch ($networkId)
			{
				case self::SOCIAL_NETWORK_LINUX :
					$networkInfo->IconColor = '#333'; break;

				case self::SOCIAL_NETWORK_PAGELINES :
					$networkInfo->IconColor = '#241E20'; break;

//				default :
//					$networkInfo->IconColor = '#fff';
//					break;
			}
		}

		return $arrNetworks;
	}



}