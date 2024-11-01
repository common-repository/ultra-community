<?php
/**
 * Copyright (c) 2016 Ultra Community (http://www.ultracommunity.com)
 */

namespace UltraCommunity;

use UltraCommunity\Controllers\UserRoleController;
use UltraCommunity\Entities\ActivityEntity;
use UltraCommunity\Entities\GroupEntity;
use UltraCommunity\Entities\UserEntity;
use UltraCommunity\MchLib\Exceptions\MchLibException;
use UltraCommunity\MchLib\Plugin\MchBasePlugin;
use UltraCommunity\MchLib\Utils\MchFileUtils;
use UltraCommunity\MchLib\Utils\MchImageUtils;
use UltraCommunity\MchLib\Utils\MchUtils;
use UltraCommunity\MchLib\Utils\MchValidator;
use UltraCommunity\MchLib\Utils\MchWpUtils;
use UltraCommunity\Modules\Appearance\General\GeneralAppearancePublicModule;

final class UltraCommUtils
{

//	public static function getUserSlugFromCurrentPageUrl($checkReferrerForAjaxRequest = false)
//	{
//		$currentPageUrl = null;
//		if( $checkReferrerForAjaxRequest && MchWpUtils::isAjaxRequest() ){
//			if( MchUtils::isNullOrEmpty($currentPageUrl = \wp_get_referer()) ){
//				return null;
//			}
//		}
//
//		if(empty($currentPageUrl) && MchUtils::isNullOrEmpty($currentPageUrl = MchWpUtils::getCurrentPageUrl()))
//			return null;
//
//		if(MchUtils::isNullOrEmpty($urlPath = @parse_url($currentPageUrl, PHP_URL_PATH)))
//			return null;
//
//		$arrPathParts = (array)explode('/', \untrailingslashit($urlPath));
//
//		if(MchUtils::isNullOrEmpty($userSlug = end($arrPathParts)))
//			return null;
//
//		return \sanitize_user( $userSlug, true );
//
//	}


	public static function getUserAvatarFilePath(UserEntity $userEntity, $size = 0, $createBySizeIfNotExists = false)
	{
//		if(empty($userEntity->UserMetaEntity->AvatarFileName))
//			return null;

		$filePath = empty($userEntity->UserMetaEntity->AvatarFileName) ? null: self::getAvatarBaseDirectoryPath($userEntity) . '/' . $userEntity->UserMetaEntity->AvatarFileName;
		
		$filePath = apply_filters(UltraCommHooks::FILTER_USER_AVATAR_FILE_PATH, $filePath, $userEntity, $size);
		
		if(empty($filePath) || !is_file($filePath))
			return null;

		$size = (int)$size;
		if($size === 0 || 200 >= $size)
			return $filePath; // returns the original uploaded avatar file

		$arrFileParts = \pathinfo($filePath);

		$avatarFilePath =  self::getAvatarBaseDirectoryPath($userEntity) . "/{$arrFileParts['filename']}-$size-$size" . '.' . $arrFileParts['extension'];
		if(file_exists($avatarFilePath))
			return $avatarFilePath;

		if(!$createBySizeIfNotExists)
			return null;

		try
		{
			MchImageUtils::resize($filePath, $avatarFilePath, $size, $size, true);
		}
		catch(MchLibException $ex)
		{
			$message = $ex->getMessage();
			return $filePath;
		}

		return $avatarFilePath;

	}

	public static function getUserProfileCoverFilePath(UserEntity $userEntity)
	{
		if(empty($userEntity->UserMetaEntity->ProfileCoverFileName))
			return null;

		$filePath = UltraCommUtils::getProfileCoverBaseDirectoryPath($userEntity) . '/' . $userEntity->UserMetaEntity->ProfileCoverFileName;

//		return MchFileUtils::fileExists($filePath) ? $filePath : null;

		return file_exists($filePath) ? $filePath : null;
	}

	public static function getGroupPictureFilePath(GroupEntity $groupEntity = null)
	{
		if(empty($groupEntity->Id) || empty($groupEntity->PictureFileName))
			return null;

		return file_exists($filePath = self::getGroupPictureBaseDirectoryPath($groupEntity->Id) . '/' . $groupEntity->PictureFileName) ? $filePath : null;
	}

	public static function getGroupCoverFilePath(GroupEntity $groupEntity = null)
	{
		if(empty($groupEntity->Id) || empty($groupEntity->CoverFileName))
			return null;

		return file_exists($filePath = self::getGroupCoverBaseDirectoryPath($groupEntity->Id) . '/' . $groupEntity->CoverFileName) ? $filePath : null;
	}




	public static function getAvatarBaseDirectoryPath(UserEntity $userEntity)
	{
		return self::getUploadsBaseDirectoryPath() . "/{$userEntity->Id}/avatar";
	}

	public static function getAvatarBaseUrl(UserEntity $userEntity)
	{
		return self::getUploadsBaseUrl() . "/{$userEntity->Id}/avatar";
	}

	public static function getProfileCoverBaseDirectoryPath(UserEntity $userEntity)
	{
		return self::getUploadsBaseDirectoryPath() . "/{$userEntity->Id}/profile-cover";
	}

	public static function getProfileCoverBaseUrl(UserEntity $userEntity)
	{
		return self::getUploadsBaseUrl() . "/{$userEntity->Id}/profile-cover";
	}

	public static function getGroupPictureBaseDirectoryPath($groupId)
	{
		return self::getUploadsBaseDirectoryPath() . "/groups/{$groupId}/picture";
	}

	public static function getGroupPictureBaseUrl($groupId)
	{
		return self::getUploadsBaseUrl() . "/groups/{$groupId}/picture";
	}

	public static function getGroupCoverBaseDirectoryPath($groupId)
	{
		return self::getUploadsBaseDirectoryPath() . "/groups/{$groupId}/cover";
	}

	public static function getGroupCoverBaseUrl($groupId)
	{
		return self::getUploadsBaseUrl() . "/groups/{$groupId}/cover";
	}

	public static function getActivityAttachmentsBaseDirectoryPath(ActivityEntity $activityEntity)
	{
		return self::getUploadsBaseDirectoryPath() . "/activity/{$activityEntity->ActivityId}";
	}

	public static function getActivityAttachmentsBaseUrl(ActivityEntity $activityEntity)
	{
		return self::getUploadsBaseUrl() . "/activity/{$activityEntity->ActivityId}";
	}


	public static function getAssetsBaseUrl()
	{
		return MchBasePlugin::getPluginBaseUrl() . '/assets';
	}



	public static function getUploadsBaseDirectoryPath()
	{
		static $uploadsDirectoryPath = null;
		if(null !== $uploadsDirectoryPath)
			return $uploadsDirectoryPath;

		$arrUploadDirInfo = null;
		if(is_main_site())
		{
			$arrUploadDirInfo = wp_upload_dir();
		}
		else // is in network
		{
			switch_to_blog(1);
			$arrUploadDirInfo = wp_upload_dir();
			restore_current_blog();
		}


		if(empty($arrUploadDirInfo['error']) && !empty($arrUploadDirInfo['basedir'])){
			return $uploadsDirectoryPath = $arrUploadDirInfo['basedir']  . '/ultra-comm/uploads';
		}
		return null;
	}


	public static function getUploadsBaseUrl()
	{
		static $uploadsDirectoryUrl = null;
		if(null !== $uploadsDirectoryUrl)
			return $uploadsDirectoryUrl;

		$arrUploadDirInfo = null;
		if(is_main_site())
		{
			$arrUploadDirInfo = wp_upload_dir();
		}
		else // is in network
		{
			switch_to_blog(1);
			$arrUploadDirInfo = wp_upload_dir();
			restore_current_blog();
		}

		if(empty($arrUploadDirInfo['error']) && !empty($arrUploadDirInfo['baseurl'])){
			return $uploadsDirectoryUrl = $arrUploadDirInfo['baseurl']  . '/ultra-comm/uploads';
		}
		return null;
	}


	public static function getPostDefaultThumbnailUrl($postId = 0)
	{
		return esc_url(	apply_filters(UltraCommHooks::FILTER_POST_DEFAULT_THUMB_URL, '', $postId) );
	}

	public static function getCountryIdByCode($countryCode)
	{
		$countryCode = trim(strtoupper($countryCode));

		foreach (self::getCountryDataSource() as $key => $value)
			if ($countryCode === $value[1])
				return $key;

		return 0;
	}

	public static function getCountryCodeById($countryId)
	{
		$arrCountry = self::getCountryDataSource();
		return isset($arrCountry[$countryId][1]) ? $arrCountry[$countryId][1] : null;
	}

	public static function getCountryNameById($countryId)
	{
		$arrCountry = self::getCountryDataSource();
		return isset($arrCountry[$countryId][0]) ? $arrCountry[$countryId][0] : null;
	}


	public static function getCountryDataSource()
	{
		return array(
			1 => array("Afghanistan", "AF"),
			2 => array("Aland Islands", "AX"),
			3 => array("Albania", "AL"),
			4 => array("Algeria", "DZ"),
			5 => array("American Samoa", "AS"),
			6 => array("Andorra", "AD"),
			7 => array("Angola", "AO"),
			8 => array("Anguilla", "AI"),
			9 => array("Antarctica", "AQ"),
			10 => array("Antigua and Barbuda", "AG"),
			11 => array("Argentina", "AR"),
			12 => array("Armenia", "AM"),
			13 => array("Aruba", "AW"),
			14 => array("Australia", "AU"),
			15 => array("Austria", "AT"),
			16 => array("Azerbaijan", "AZ"),
			17 => array("Bahamas", "BS"),
			18 => array("Bahrain", "BH"),
			19 => array("Bangladesh", "BD"),
			20 => array("Barbados", "BB"),
			21 => array("Belarus", "BY"),
			22 => array("Belgium", "BE"),
			23 => array("Belize", "BZ"),
			24 => array("Benin", "BJ"),
			25 => array("Bermuda", "BM"),
			26 => array("Bhutan", "BT"),
			27 => array("Bolivia", "BO"),
			28 => array("Bosnia and Herzegovina", "BA"),
			29 => array("Botswana", "BW"),
			30 => array("Bouvet island", "BV"),
			31 => array("Brazil", "BR"),
			32 => array("British Indian Ocean", "IO"),
			33 => array("Brunei Darussalam", "BN"),
			34 => array("Bulgaria", "BG"),
			35 => array("Burkina Faso", "BF"),
			36 => array("Burundi", "BI"),
			37 => array("Cambodia", "KH"),
			38 => array("Cameroon", "CM"),
			39 => array("Canada", "CA"),
			40 => array("Cape Verde", "CV"),
			41 => array("Cayman Islands", "KY"),
			42 => array("Central African Republic", "CF"),
			43 => array("Chad", "TD"),
			44 => array("Chile", "CL"),
			45 => array("China", "CN"),
			46 => array("Christmas Island", "CX"),
			47 => array("Cocos Islands", "CC"),
			48 => array("Colombia", "CO"),
			49 => array("Comoros", "KM"),
			50 => array("Congo", "CG"),
			51 => array("Congo", "CD"),
			52 => array("Cook Islands", "CK"),
			53 => array("Costa Rica", "CR"),
			54 => array("Cote d'Ivoire", "CI"),
			55 => array("Croatia", "HR"),
			56 => array("Cuba", "CU"),
			57 => array("Cyprus", "CY"),
			58 => array("Czech Republic", "CZ"),
			59 => array("Denmark", "DK"),
			60 => array("Djibouti", "DJ"),
			61 => array("Dominica", "DM"),
			62 => array("Dominican republic", "DO"),
			63 => array("Ecuador", "EC"),
			64 => array("Egypt", "EG"),
			65 => array("El Salvador", "SV"),
			66 => array("Equatorial Guinea", "GQ"),
			67 => array("Eritrea", "ER"),
			68 => array("Estonia", "EE"),
			69 => array("Ethiopia", "ET"),
			70 => array("Falkland Islands", "FK"),
			71 => array("Faroe Islands", "FO"),
			72 => array("Fiji", "FJ"),
			73 => array("Finland", "FI"),
			74 => array("France", "FR"),
			75 => array("French Guiana", "GF"),
			76 => array("French Polynesia", "PF"),
			77 => array("French Southern Territories", "TF"),
			78 => array("Gabon", "GA"),
			79 => array("Gambia", "GM"),
			80 => array("Georgia", "GE"),
			81 => array("Germany", "DE"),
			82 => array("Ghana", "GH"),
			83 => array("Gibraltar", "GI"),
			84 => array("Greece", "GR"),
			85 => array("Greenland", "GL"),
			86 => array("Grenada", "GD"),
			87 => array("Guadeloupe", "GP"),
			88 => array("Guam", "GU"),
			89 => array("Guatemala", "GT"),
			90 => array("Guernsey", "GG"),
			91 => array("Guinea", "GN"),
			92 => array("Guinea-Bissau", "GW"),
			93 => array("Guyana", "GY"),
			94 => array("Haiti", "HT"),
			95 => array("Heard and Mcdonald Islands", "HM"),
			96 => array("Vatican", "VA"),
			97 => array("Honduras", "HN"),
			98 => array("Hong Kong", "HK"),
			99 => array("Hungary", "HU"),
			100 => array("Iceland", "IS"),
			101 => array("India", "IN"),
			102 => array("Indonesia", "ID"),
			103 => array("Iran", "IR"),
			104 => array("Iraq", "IQ"),
			105 => array("Ireland", "IE"),
			106 => array("Isle of Man", "IM"),
			107 => array("Israel", "IL"),
			108 => array("Italy", "IT"),
			109 => array("Jamaica", "JM"),
			110 => array("Japan", "JP"),
			111 => array("Jersey", "JE"),
			112 => array("Jordan", "JO"),
			113 => array("Kazakhstan", "KZ"),
			114 => array("Kenya", "KE"),
			115 => array("Kiribati", "KI"),
			116 => array("Korea", "KR"),
			117 => array("Korea - North", "KP"),
			118 => array("Kuwait", "KW"),
			119 => array("Kyrgyzstan", "KG"),
			120 => array("Lao Republic", "LA"),
			121 => array("Latvia", "LV"),
			122 => array("Lebanon", "LB"),
			123 => array("Lesotho", "LS"),
			124 => array("Liberia", "LR"),
			125 => array("Libyan Arab Jamahiriya", "LY"),
			126 => array("Liechtenstein", "LI"),
			127 => array("Lithuania", "LT"),
			128 => array("Luxembourg", "LU"),
			129 => array("Macao", "MO"),
			130 => array("Macedonia", "MK"),
			131 => array("Madagascar", "MG"),
			132 => array("Malawi", "MW"),
			133 => array("Malaysia", "MY"),
			134 => array("Maldives", "MV"),
			135 => array("Mali", "ML"),
			136 => array("Malta", "MT"),
			137 => array("Marshall Islands", "MH"),
			138 => array("Martinique", "MQ"),
			139 => array("Mauritania", "MR"),
			140 => array("Mauritius", "MU"),
			141 => array("Mayotte", "YT"),
			142 => array("Mexico", "MX"),
			143 => array("Micronesia", "FM"),
			144 => array("Moldova", "MD"),
			145 => array("Monaco", "MC"),
			146 => array("Mongolia", "MN"),
			147 => array("Montenegro", "ME"),
			148 => array("Montserrat", "MS"),
			149 => array("Morocco", "MA"),
			150 => array("Mozambique", "MZ"),
			151 => array("Myanmar", "MM"),
			152 => array("Namibia", "NA"),
			153 => array("Nauru", "NR"),
			154 => array("Nepal", "NP"),
			155 => array("Netherlands", "NL"),
			156 => array("Netherlands Antilles", "AN"),
			157 => array("New Caledonia", "NC"),
			158 => array("New Zealand", "NZ"),
			159 => array("Nicaragua", "NI"),
			160 => array("Niger", "NE"),
			161 => array("Nigeria", "NG"),
			162 => array("Niue", "NU"),
			163 => array("Norfolk Island", "NF"),
			164 => array("Northern Mariana Islands", "MP"),
			165 => array("Norway", "NO"),
			166 => array("Oman", "OM"),
			167 => array("Pakistan", "PK"),
			168 => array("Palau", "PW"),
			169 => array("Palestinian Territory Occupied", "PS"),
			170 => array("Panama", "PA"),
			171 => array("Papua New Guinea", "PG"),
			172 => array("Paraguay", "PY"),
			173 => array("Peru", "PE"),
			174 => array("Philippines", "PH"),
			175 => array("Pitcairn", "PN"),
			176 => array("Poland", "PL"),
			177 => array("Portugal", "PT"),
			178 => array("Puerto rico", "PR"),
			179 => array("Qatar", "QA"),
			180 => array("Reunion", "RE"),
			181 => array("Romania", "RO"),
			182 => array("Russian Federation", "RU"),
			183 => array("Rwanda", "RW"),
			184 => array("Saint Barthelemy", "BL"),
			185 => array("Saint Helena", "SH"),
			186 => array("Saint Kitts and Nevis", "KN"),
			187 => array("Saint Lucia", "LC"),
			188 => array("Saint Martin", "MF"),
			189 => array("Saint Pierre and Miquelon", "PM"),
			190 => array("Saint Vincent", "VC"),
			191 => array("Samoa", "WS"),
			192 => array("San Marino", "SM"),
			193 => array("Sao Tome and Principe", "ST"),
			194 => array("Saudi Arabia", "SA"),
			195 => array("Senegal", "SN"),
			196 => array("Serbia", "RS"),
			197 => array("Seychelles", "SC"),
			198 => array("Sierra Leone", "SL"),
			199 => array("Singapore", "SG"),
			200 => array("Slovakia", "SK"),
			201 => array("Slovenia", "SI"),
			202 => array("Solomon Islands", "SB"),
			203 => array("Somalia", "SO"),
			204 => array("South Africa", "ZA"),
			205 => array("South Georgia and Islands", "GS"),
			206 => array("Spain", "ES"),
			207 => array("Sri Lanka", "LK"),
			208 => array("Sudan", "SD"),
			209 => array("Suriname", "SR"),
			210 => array("Svalbard and Jan Mayen", "SJ"),
			211 => array("Swaziland", "SZ"),
			212 => array("Sweden", "SE"),
			213 => array("Switzerland", "CH"),
			214 => array("Syrian Arab Republic", "SY"),
			215 => array("Taiwan", "TW"),
			216 => array("Tajikistan", "TJ"),
			217 => array("Tanzania", "TZ"),
			218 => array("Thailand", "TH"),
			219 => array("Timor-Leste", "TL"),
			220 => array("Togo", "TG"),
			221 => array("Tokelau", "TK"),
			222 => array("Tonga", "TO"),
			223 => array("Trinidad and Tobago", "TT"),
			224 => array("Tunisia", "TN"),
			225 => array("Turkey", "TR"),
			226 => array("Turkmenistan", "TM"),
			227 => array("Turks and Caicos Islands", "TC"),
			228 => array("Tuvalu", "TV"),
			229 => array("Uganda", "UG"),
			230 => array("Ukraine", "UA"),
			231 => array("United Arab Emirates", "AE"),
			232 => array("United Kingdom", "GB"),
			233 => array("United States", "US"),
			234 => array("United States Minor Islands", "UM"),
			235 => array("Uruguay", "UY"),
			236 => array("Uzbekistan", "UZ"),
			237 => array("Vanuatu", "VU"),
			238 => array("Venezuela", "VE"),
			239 => array("Vietnam", "VN"),
			240 => array("Virgin Islands British", "VG"),
			241 => array("Virgin Islands U.S.", "VI"),
			242 => array("Wallis and Futuna", "WF"),
			243 => array("Western Sahara", "EH"),
			244 => array("Yemen", "YE"),
			245 => array("Zambia", "ZM"),
			246 => array("Zimbabwe", "ZW"),
			247 => array("South Sudan", "SS"),
			248 => array("Sint Maarten", "SX"),
			249 => array("Curacao", "CW"),
			250 => array("Bonaire", "BQ")
		);
	}


	private static function getSupportedCurrencies()
	{
		static $arrCurrencies = null;
		return null !== $arrCurrencies ? $arrCurrencies : $arrCurrencies = array(

				'USD' => array(__( 'US Dollars', 'ultra-community' )         , '&#36;'),
				'EUR' => array(__( 'Euros', 'ultra-community' )              , '&#8364;'),
				'GBP' => array(__( 'Pounds Sterling', 'ultra-community' )    , '&#163;'),
				'AUD' => array(__( 'Australian Dollars', 'ultra-community' ) , '&#36;'),
				'BRL' => array(__( 'Brazilian Real', 'ultra-community' )     , '&#82;&#36;'),
				'CAD' => array(__( 'Canadian Dollars', 'ultra-community' )   , '&#36;'),
				'CZK' => array(__( 'Czech Koruna', 'ultra-community' )       , '&#75;&#269;'),
				'DKK' => array(__( 'Danish Krone', 'ultra-community' )       , '&#107;&#114;'),
				'HKD' => array(__( 'Hong Kong Dollar', 'ultra-community' )   , '&#36;'),
				'HUF' => array(__( 'Hungarian Forint', 'ultra-community' )   , '&#70;&#116;'),
				'IRR' => array(__( 'Iranian Rial', 'ultra-community' )       , '&#65020;'),
				'ILS' => array(__( 'Israeli Shekel', 'ultra-community' )     , '&#8362;'),
				'JPY' => array(__( 'Japanese Yen', 'ultra-community' )       , '&#165;'),
				'MYR' => array(__( 'Malaysian Ringgits', 'ultra-community' ) , '&#82;&#77;'),
				'MXN' => array(__( 'Mexican Peso', 'ultra-community' )       , '&#36;'),
				'NZD' => array(__( 'New Zealand Dollar', 'ultra-community' ) , '&#36;'),
				'NOK' => array(__( 'Norwegian Krone', 'ultra-community' )    , '&#107;&#114;'),
				'PHP' => array(__( 'Philippine Pesos', 'ultra-community' )   , '&#8369;'),
				'PLN' => array(__( 'Polish Zloty', 'ultra-community' )       , '&#122;&#322;'),
				'RUB' => array(__( 'Russian Rubles', 'ultra-community' )     , '&#1088;&#1091;&#1073;'),
				'SGD' => array(__( 'Singapore Dollar', 'ultra-community' )   , '&#36;'),
				'SEK' => array(__( 'Swedish Krona', 'ultra-community' )      , '&#107;&#114;'),
				'CHF' => array(__( 'Swiss Franc', 'ultra-community' )        , '&#67;&#72;&#70;'),
				'TWD' => array(__( 'Taiwan New Dollars', 'ultra-community' ) , '&#78;&#84;&#36;'),
				'THB' => array(__( 'Thai Baht', 'ultra-community' )          , '&#3647;'),
				'TRY' => array(__( 'Turkish Lira', 'ultra-community' )       , '&#8356;'),

		);

	}

	
	public static function getCurrencies($showSymbol = false)
	{
		$arrCurrencies = array();
		foreach(self::getSupportedCurrencies() as $currency => $arrCurrencyInfo)
		{
			$arrCurrencies[$currency] = $showSymbol ? $arrCurrencyInfo[1] . ' - ' . $arrCurrencyInfo[0]  : $arrCurrencyInfo[0];
		}

		return $arrCurrencies;


	}

	public static function getCurrencySymbol($currency)
	{
		$arrCurrencies = self::getSupportedCurrencies();
		return isset($arrCurrencies[$currency][1]) ? $arrCurrencies[$currency][1] : null;
	}

	public static function currencyAllowsDecimals($currency)
	{
		return !in_array(strtoupper(trim($currency)),  array( 'BIF', 'CLP', 'DJF', 'GNF', 'JPY', 'KMF', 'KRW', 'MGA', 'PYG', 'RWF', 'VND', 'VUV', 'XAF', 'XOF', 'XPF' ) );
	}

	public static function getFormattedAmountForDisplay($amount, $currency)
	{
		global $wp_locale;

		$thousandsSeparator = ! empty( $wp_locale->number_format['thousands_sep'] ) ? $wp_locale->number_format['thousands_sep'] : ',';
		$decimalSeparator   = ! empty( $wp_locale->number_format['decimal_point'] ) ? $wp_locale->number_format['decimal_point'] : '.';

		if ( $decimalSeparator === ',' && false !== ( $sep_found = strpos( $amount, $decimalSeparator ) ) ) {
			$amount = substr( $amount, 0, $sep_found ) . '.' . substr( $amount, $sep_found + 1, ( strlen( $amount ) - 1 ) );
		}

		if ( $thousandsSeparator === ',' && false !== ( $found = strpos( $amount, $thousandsSeparator ) ) ) {
			$amount = str_replace( ',', '', $amount );
		}

		if ( $thousandsSeparator === ' ' && false !== ( $found = strpos( $amount, $thousandsSeparator ) ) ) {
			$amount = str_replace( ' ', '', $amount );
		}

		$amount = trim($amount);

		!empty($amount) ?: $amount = 0;

		return number_format_i18n($amount, self::currencyAllowsDecimals($currency) ? 2 : 0);

	}








	public static function fullNameToFirstLast($strFullName)
	{
		$strFullName = trim($strFullName);
		if(empty($strFullName)){
			return array();
		}

		$arrName = array('first' => null, 'last' => null);
		preg_match('#^(\w+\.)?\s*([\'\’\w]+)\s+([\'\’\w]+)\s*(\w+\.?)?$#', $strFullName, $results);

		empty($results[2]) ?: $arrName['first'] = trim($results[2]);
		empty($results[3]) ?: $arrName['last']  = trim($results[3]);

		return $arrName;
	}


	public static function getValidUserNameFromString($someString)
	{
		if(MchValidator::isEmail($someString))
		{
			$userName = sanitize_user(strstr($someString, '@', true));
			if(empty($userName))
				return null;

			if((validate_username($userName) && !username_exists($userName)))
				return $userName;

			for ( $i = 1; $i < 10; ++ $i )
			{
				$tmpUserName = 	sanitize_user($userName . $i);
				if((validate_username($tmpUserName) && !username_exists($tmpUserName))){
					return $tmpUserName;
				}
			}

			return null;
		}


		$someString = str_replace('-', '', MchWpUtils::formatUrlPath($someString));
		$userNameLength = mb_strlen($someString);
		$userNameLength < 50 ?: $userNameLength = 50;

		$userName = null;
		if($userNameLength > 3)
		{
			$userName = mb_substr($someString, 0, 3);
			for($i = 3; $i < $userNameLength; ++$i)
			{
				$userName = sanitize_user( $userName . $someString[$i], true );
				if(validate_username($userName) && !username_exists($userName)){
					break;
				}
			}

			isset($userName[3]) ?:  $userName = null;
		}

		return $userName;
	}


	public static function getRedirectScript($location, $delayMilliSeconds)
	{
		return "<script>setTimeout(function(){window.location.href='$location';}, $delayMilliSeconds);</script>";
	}

	public static function getPreviewRequestUrl($postId)
	{
		if(!($url =  get_permalink($postId)))
			return null;

		$url = add_query_arg('uc-action', 'uc-preview', $url);

		return esc_url(wp_nonce_url($url, 'uc-preview-page', 'uc-preview-nonce'));
	}

	public static function isPreviewRequest()
	{
		if(empty($_GET['uc-preview-nonce']) || empty($_GET['uc-action']) || $_GET['uc-action'] !== 'uc-preview' || !UserRoleController::currentUserCanManageUltraCommunity())
			return false;

		return (bool)wp_verify_nonce($_GET['uc-preview-nonce'], 'uc-preview-page');

	}


	public static function getWrappedPopupHolderContent($wrapperId, $popupTitle, $htmlContent, $footerContent = null, $popupSizeClass = 'uc-modal-md')
	{

		
		$pageColorSchemeClassName = GeneralAppearancePublicModule::getPageColorSchemeClassName();

		$wrapperId = sanitize_html_class($wrapperId);

		$outputContent = <<<UCMODAL
<div id = "$wrapperId" class = "uc-popup-wrapper $popupSizeClass">

<div class = "uch $pageColorSchemeClassName">
	<div class = "uc-g">

		<div class = "uc-u-1-1 uc-popup-title"><h5>$popupTitle</h5></div>

		<div class = "uc-u-1-1 uc-popup-content">$htmlContent</div>

		<div class = "uc-u-1-1 uc-popup-footer">$footerContent</div>

	</div>
</div>

</div>
UCMODAL;

		return $outputContent;
	}


	public static function getPaginationOutputContent($activePageNumber, $totalPages)
	{
		$arrPaginationArgs = array(
			'base'            		=> trailingslashit (get_pagenum_link( 1 ) ) . '%_%',
			'format'          		=> 'page/%#%',
			'total'           		=> $totalPages,
			'current'         		=> $activePageNumber,
			'show_all'        		=> False,
			'end_size'        		=> 1,
			'mid_size'        		=> 2,
			'prev_next'       		=> true,
			'prev_text'       		=> '<i class="fa fa-angle-double-left"></i>',
			'next_text'       		=> '<i class="fa fa-angle-double-right"></i>',
			'type'            		=> 'plain',
			'add_args'        		=> false,
			'add_fragment'    		=> '',
			'before_page_number' 	=> '',
			'after_page_number' 	=> '',
		);

		$paginationLinks = paginate_links( $arrPaginationArgs );

		$outputContent  = '<div class="uc-grid uc-grid--fit uc-grid--center uc-grid--flex-cells uc-pagination">';
			$outputContent .= '<div class="uc-grid-cell uc-grid-cell--autoSize uc-pagination-pages">';
				$outputContent .= sprintf( __( 'Page %d of %d' , 'ultra-community' ), $activePageNumber, $totalPages );
			$outputContent .= '</div>';
			$outputContent .= "<div class=\"uc-grid-cell uc-grid-cell--center\">$paginationLinks</div>";
		$outputContent .= '</div>';

		return	$outputContent;

	}

	public static function getPostThumbnailUrl($postId, $thumbSize = 'large')
	{

// 'thumbnail' // Thumbnail (default 150px x 150px max)
// 'medium'    // Medium resolution (default 300px x 300px max)
// 'large'     // Large resolution (default 640px x 640px max)
// 'full'      // Original image resolution (unmodified)

		$wpPost = get_post($postId);
		if(empty($wpPost))
			return null;

		$postThumbSrc = null;
		if($postThumbId = get_post_thumbnail_id($wpPost))
		{
			$arrImageInfo           = wp_get_attachment_image_src( $postThumbId , $thumbSize );
			empty($arrImageInfo[0]) ?: $postThumbSrc = $arrImageInfo[0];
		}

		if(null === $postThumbSrc)
		{
			foreach ((array)get_posts( array('post_parent' => $postId, 'post_type' => 'attachment', 'posts_per_page' => 1, 'post_status' =>'any') ) as $postAttachment)
			{
				$arrImageInfo           = wp_get_attachment_image_src( $postAttachment->ID , $thumbSize );
				empty($arrImageInfo[0]) ?: $postThumbSrc = $arrImageInfo[0];
				break;
			}
		}

		if(null === $postThumbSrc && !empty($wpPost->post_content))
		{
			$domDocument = new \DOMDocument();

			libxml_use_internal_errors(true);

			$domDocument->loadHTML( MchWpUtils::applyClonedFilter( 'the_content', get_the_content('', false, $wpPost) ) );

			foreach ($domDocument->getElementsByTagName('img') as $imageElement)
			{
				$postThumbSrc = $imageElement->getAttribute('src');
				if(!empty($postThumbSrc) && stripos($postThumbSrc, 'data:') !== 0)
				{
					$postThumbSrc = esc_url_raw( $postThumbSrc );
					break;
				}

				$postThumbSrc = null;
			}

			libxml_clear_errors();
		}

		!empty($postThumbSrc) ?: $postThumbSrc = trim(self::getPostDefaultThumbnailUrl($postId));

		return empty($postThumbSrc) ? null : $postThumbSrc;
	}


	public static function getTempUploadsDirectoryPath()
	{
		self::registerTempUploadsDirectoryFilter();
		$arrTempUploadsDirInfo = wp_upload_dir();
		self::removeTempUploadsDirectoryFilter();

		if(empty($arrTempUploadsDirInfo['path']))
			return null;

		return wp_mkdir_p($arrTempUploadsDirInfo['path']) ? $arrTempUploadsDirInfo['path'] : null;
	}
	public static function registerTempUploadsDirectoryFilter()
	{
		\add_filter('upload_dir', 'uc_filter_temp_uploads_directory', PHP_INT_MAX);
	}

	public static function removeTempUploadsDirectoryFilter()
	{
		\remove_filter('upload_dir', 'uc_filter_temp_uploads_directory', PHP_INT_MAX);
	}

	
	public static function isWPMLActive()
	{
		if ( !defined( 'ICL_SITEPRESS_VERSION' ) )
			return false;
		
		global $sitepress;
		
		if (!isset($sitepress) || !($sitepress instanceof \SitePress))
			return false;
		
		return $sitepress->get_setting( 'setup_complete' );
	}
	
	
	public static function getQueriedObjectId()
	{
		if(0 === ($queriedObjectId = (int)get_queried_object_id()))
			return 0;
		
		if(self::isWPMLActive())
		{
			global $sitepress;
			$originalPostId = (int)apply_filters( 'wpml_object_id', $queriedObjectId, 'any', false, $sitepress->get_default_language() );
			empty($originalPostId) ?: $queriedObjectId = $originalPostId;
		}
		
		return $queriedObjectId;
	}
	
	
}
