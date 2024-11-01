<?php
/**
 * Copyright (c) 2016 Ultra Community (http://www.ultracommunity.com)
 */

namespace UltraCommunity\Modules\Forms\FormFields;


use UltraCommunity\MchLib\Utils\MchUtils;
use UltraCommunity\MchLib\Utils\MchWpUtils;
use UltraCommunity\UltraCommHooks;

class CountryField  extends BaseField
{
	public function __construct()
	{
		parent::__construct( parent::FIELD_TYPE_SELECT );
		$this->setOptionsList();
	}

	public function setOptionsList()
	{
		$this->OptionsList =  array(
			'AF' => __( 'Afghanistan', 'ultra-community' ),
			'AX' => __( 'Aland Islands', 'ultra-community' ),
			'AL' => __( 'Albania', 'ultra-community' ),
			'DZ' => __( 'Algeria', 'ultra-community' ),
			'AS' => __( 'American Samoa', 'ultra-community' ),
			'AD' => __( 'Andorra', 'ultra-community' ),
			'AO' => __( 'Angola', 'ultra-community' ),
			'AI' => __( 'Anguilla', 'ultra-community' ),
			'AQ' => __( 'Antarctica', 'ultra-community' ),
			'AG' => __( 'Antigua and Barbuda', 'ultra-community' ),
			'AR' => __( 'Argentina', 'ultra-community' ),
			'AM' => __( 'Armenia', 'ultra-community' ),
			'AW' => __( 'Aruba', 'ultra-community' ),
			'AU' => __( 'Australia', 'ultra-community' ),
			'AT' => __( 'Austria', 'ultra-community' ),
			'AZ' => __( 'Azerbaijan', 'ultra-community' ),
			'BS' => __( 'Bahamas', 'ultra-community' ),
			'BH' => __( 'Bahrain', 'ultra-community' ),
			'BD' => __( 'Bangladesh', 'ultra-community' ),
			'BB' => __( 'Barbados', 'ultra-community' ),
			'BY' => __( 'Belarus', 'ultra-community' ),
			'BE' => __( 'Belgium', 'ultra-community' ),
			'PW' => __( 'Belau', 'ultra-community' ),
			'BZ' => __( 'Belize', 'ultra-community' ),
			'BJ' => __( 'Benin', 'ultra-community' ),
			'BM' => __( 'Bermuda', 'ultra-community' ),
			'BT' => __( 'Bhutan', 'ultra-community' ),
			'BO' => __( 'Bolivia', 'ultra-community' ),
			'BQ' => __( 'Bonaire, Saint Eustatius and Saba', 'ultra-community' ),
			'BA' => __( 'Bosnia and Herzegovina', 'ultra-community' ),
			'BW' => __( 'Botswana', 'ultra-community' ),
			'BV' => __( 'Bouvet Island', 'ultra-community' ),
			'BR' => __( 'Brazil', 'ultra-community' ),
			'IO' => __( 'British Indian Ocean Territory', 'ultra-community' ),
			'VG' => __( 'British Virgin Islands', 'ultra-community' ),
			'BN' => __( 'Brunei', 'ultra-community' ),
			'BG' => __( 'Bulgaria', 'ultra-community' ),
			'BF' => __( 'Burkina Faso', 'ultra-community' ),
			'BI' => __( 'Burundi', 'ultra-community' ),
			'KH' => __( 'Cambodia', 'ultra-community' ),
			'CM' => __( 'Cameroon', 'ultra-community' ),
			'CA' => __( 'Canada', 'ultra-community' ),
			'CV' => __( 'Cape Verde', 'ultra-community' ),
			'KY' => __( 'Cayman Islands', 'ultra-community' ),
			'CF' => __( 'Central African Republic', 'ultra-community' ),
			'TD' => __( 'Chad', 'ultra-community' ),
			'CL' => __( 'Chile', 'ultra-community' ),
			'CN' => __( 'China', 'ultra-community' ),
			'CX' => __( 'Christmas Island', 'ultra-community' ),
			'CC' => __( 'Cocos (Keeling) Islands', 'ultra-community' ),
			'CO' => __( 'Colombia', 'ultra-community' ),
			'KM' => __( 'Comoros', 'ultra-community' ),
			'CG' => __( 'Congo (Brazzaville)', 'ultra-community' ),
			'CD' => __( 'Congo (Kinshasa)', 'ultra-community' ),
			'CK' => __( 'Cook Islands', 'ultra-community' ),
			'CR' => __( 'Costa Rica', 'ultra-community' ),
			'HR' => __( 'Croatia', 'ultra-community' ),
			'CU' => __( 'Cuba', 'ultra-community' ),
			'CW' => __( 'Cura&ccedil;ao', 'ultra-community' ),
			'CY' => __( 'Cyprus', 'ultra-community' ),
			'CZ' => __( 'Czech Republic', 'ultra-community' ),
			'DK' => __( 'Denmark', 'ultra-community' ),
			'DJ' => __( 'Djibouti', 'ultra-community' ),
			'DM' => __( 'Dominica', 'ultra-community' ),
			'DO' => __( 'Dominican Republic', 'ultra-community' ),
			'EC' => __( 'Ecuador', 'ultra-community' ),
			'EG' => __( 'Egypt', 'ultra-community' ),
			'SV' => __( 'El Salvador', 'ultra-community' ),
			'GQ' => __( 'Equatorial Guinea', 'ultra-community' ),
			'ER' => __( 'Eritrea', 'ultra-community' ),
			'EE' => __( 'Estonia', 'ultra-community' ),
			'ET' => __( 'Ethiopia', 'ultra-community' ),
			'FK' => __( 'Falkland Islands', 'ultra-community' ),
			'FO' => __( 'Faroe Islands', 'ultra-community' ),
			'FJ' => __( 'Fiji', 'ultra-community' ),
			'FI' => __( 'Finland', 'ultra-community' ),
			'FR' => __( 'France', 'ultra-community' ),
			'GF' => __( 'French Guiana', 'ultra-community' ),
			'PF' => __( 'French Polynesia', 'ultra-community' ),
			'TF' => __( 'French Southern Territories', 'ultra-community' ),
			'GA' => __( 'Gabon', 'ultra-community' ),
			'GM' => __( 'Gambia', 'ultra-community' ),
			'GE' => __( 'Georgia', 'ultra-community' ),
			'DE' => __( 'Germany', 'ultra-community' ),
			'GH' => __( 'Ghana', 'ultra-community' ),
			'GI' => __( 'Gibraltar', 'ultra-community' ),
			'GR' => __( 'Greece', 'ultra-community' ),
			'GL' => __( 'Greenland', 'ultra-community' ),
			'GD' => __( 'Grenada', 'ultra-community' ),
			'GP' => __( 'Guadeloupe', 'ultra-community' ),
			'GU' => __( 'Guam', 'ultra-community' ),
			'GT' => __( 'Guatemala', 'ultra-community' ),
			'GG' => __( 'Guernsey', 'ultra-community' ),
			'GN' => __( 'Guinea', 'ultra-community' ),
			'GW' => __( 'Guinea-Bissau', 'ultra-community' ),
			'GY' => __( 'Guyana', 'ultra-community' ),
			'HT' => __( 'Haiti', 'ultra-community' ),
			'HM' => __( 'Heard Island and McDonald Islands', 'ultra-community' ),
			'HN' => __( 'Honduras', 'ultra-community' ),
			'HK' => __( 'Hong Kong', 'ultra-community' ),
			'HU' => __( 'Hungary', 'ultra-community' ),
			'IS' => __( 'Iceland', 'ultra-community' ),
			'IN' => __( 'India', 'ultra-community' ),
			'ID' => __( 'Indonesia', 'ultra-community' ),
			'IR' => __( 'Iran', 'ultra-community' ),
			'IQ' => __( 'Iraq', 'ultra-community' ),
			'IE' => __( 'Republic of Ireland', 'ultra-community' ),
			'IM' => __( 'Isle of Man', 'ultra-community' ),
			'IL' => __( 'Israel', 'ultra-community' ),
			'IT' => __( 'Italy', 'ultra-community' ),
			'CI' => __( 'Ivory Coast', 'ultra-community' ),
			'JM' => __( 'Jamaica', 'ultra-community' ),
			'JP' => __( 'Japan', 'ultra-community' ),
			'JE' => __( 'Jersey', 'ultra-community' ),
			'JO' => __( 'Jordan', 'ultra-community' ),
			'KZ' => __( 'Kazakhstan', 'ultra-community' ),
			'KE' => __( 'Kenya', 'ultra-community' ),
			'KI' => __( 'Kiribati', 'ultra-community' ),
			'KW' => __( 'Kuwait', 'ultra-community' ),
			'KG' => __( 'Kyrgyzstan', 'ultra-community' ),
			'LA' => __( 'Laos', 'ultra-community' ),
			'LV' => __( 'Latvia', 'ultra-community' ),
			'LB' => __( 'Lebanon', 'ultra-community' ),
			'LS' => __( 'Lesotho', 'ultra-community' ),
			'LR' => __( 'Liberia', 'ultra-community' ),
			'LY' => __( 'Libya', 'ultra-community' ),
			'LI' => __( 'Liechtenstein', 'ultra-community' ),
			'LT' => __( 'Lithuania', 'ultra-community' ),
			'LU' => __( 'Luxembourg', 'ultra-community' ),
			'MO' => __( 'Macao S.A.R., China', 'ultra-community' ),
			'MK' => __( 'Macedonia', 'ultra-community' ),
			'MG' => __( 'Madagascar', 'ultra-community' ),
			'MW' => __( 'Malawi', 'ultra-community' ),
			'MY' => __( 'Malaysia', 'ultra-community' ),
			'MV' => __( 'Maldives', 'ultra-community' ),
			'ML' => __( 'Mali', 'ultra-community' ),
			'MT' => __( 'Malta', 'ultra-community' ),
			'MH' => __( 'Marshall Islands', 'ultra-community' ),
			'MQ' => __( 'Martinique', 'ultra-community' ),
			'MR' => __( 'Mauritania', 'ultra-community' ),
			'MU' => __( 'Mauritius', 'ultra-community' ),
			'YT' => __( 'Mayotte', 'ultra-community' ),
			'MX' => __( 'Mexico', 'ultra-community' ),
			'FM' => __( 'Micronesia', 'ultra-community' ),
			'MD' => __( 'Moldova', 'ultra-community' ),
			'MC' => __( 'Monaco', 'ultra-community' ),
			'MN' => __( 'Mongolia', 'ultra-community' ),
			'ME' => __( 'Montenegro', 'ultra-community' ),
			'MS' => __( 'Montserrat', 'ultra-community' ),
			'MA' => __( 'Morocco', 'ultra-community' ),
			'MZ' => __( 'Mozambique', 'ultra-community' ),
			'MM' => __( 'Myanmar', 'ultra-community' ),
			'NA' => __( 'Namibia', 'ultra-community' ),
			'NR' => __( 'Nauru', 'ultra-community' ),
			'NP' => __( 'Nepal', 'ultra-community' ),
			'NL' => __( 'Netherlands', 'ultra-community' ),
			'NC' => __( 'New Caledonia', 'ultra-community' ),
			'NZ' => __( 'New Zealand', 'ultra-community' ),
			'NI' => __( 'Nicaragua', 'ultra-community' ),
			'NE' => __( 'Niger', 'ultra-community' ),
			'NG' => __( 'Nigeria', 'ultra-community' ),
			'NU' => __( 'Niue', 'ultra-community' ),
			'NF' => __( 'Norfolk Island', 'ultra-community' ),
			'MP' => __( 'Northern Mariana Islands', 'ultra-community' ),
			'KP' => __( 'North Korea', 'ultra-community' ),
			'NO' => __( 'Norway', 'ultra-community' ),
			'OM' => __( 'Oman', 'ultra-community' ),
			'PK' => __( 'Pakistan', 'ultra-community' ),
			'PS' => __( 'Palestinian Territory', 'ultra-community' ),
			'PA' => __( 'Panama', 'ultra-community' ),
			'PG' => __( 'Papua New Guinea', 'ultra-community' ),
			'PY' => __( 'Paraguay', 'ultra-community' ),
			'PE' => __( 'Peru', 'ultra-community' ),
			'PH' => __( 'Philippines', 'ultra-community' ),
			'PN' => __( 'Pitcairn', 'ultra-community' ),
			'PL' => __( 'Poland', 'ultra-community' ),
			'PT' => __( 'Portugal', 'ultra-community' ),
			'PR' => __( 'Puerto Rico', 'ultra-community' ),
			'QA' => __( 'Qatar', 'ultra-community' ),
			'RE' => __( 'Reunion', 'ultra-community' ),
			'RO' => __( 'Romania', 'ultra-community' ),
			'RU' => __( 'Russia', 'ultra-community' ),
			'RW' => __( 'Rwanda', 'ultra-community' ),
			'BL' => __( 'Saint Barth&eacute;lemy', 'ultra-community' ),
			'SH' => __( 'Saint Helena', 'ultra-community' ),
			'KN' => __( 'Saint Kitts and Nevis', 'ultra-community' ),
			'LC' => __( 'Saint Lucia', 'ultra-community' ),
			'MF' => __( 'Saint Martin (French part)', 'ultra-community' ),
			'SX' => __( 'Saint Martin (Dutch part)', 'ultra-community' ),
			'PM' => __( 'Saint Pierre and Miquelon', 'ultra-community' ),
			'VC' => __( 'Saint Vincent and the Grenadines', 'ultra-community' ),
			'SM' => __( 'San Marino', 'ultra-community' ),
			'ST' => __( 'S&atilde;o Tom&eacute; and Pr&iacute;ncipe', 'ultra-community' ),
			'SA' => __( 'Saudi Arabia', 'ultra-community' ),
			'SN' => __( 'Senegal', 'ultra-community' ),
			'RS' => __( 'Serbia', 'ultra-community' ),
			'SC' => __( 'Seychelles', 'ultra-community' ),
			'SL' => __( 'Sierra Leone', 'ultra-community' ),
			'SG' => __( 'Singapore', 'ultra-community' ),
			'SK' => __( 'Slovakia', 'ultra-community' ),
			'SI' => __( 'Slovenia', 'ultra-community' ),
			'SB' => __( 'Solomon Islands', 'ultra-community' ),
			'SO' => __( 'Somalia', 'ultra-community' ),
			'ZA' => __( 'South Africa', 'ultra-community' ),
			'GS' => __( 'South Georgia/Sandwich Islands', 'ultra-community' ),
			'KR' => __( 'South Korea', 'ultra-community' ),
			'SS' => __( 'South Sudan', 'ultra-community' ),
			'ES' => __( 'Spain', 'ultra-community' ),
			'LK' => __( 'Sri Lanka', 'ultra-community' ),
			'SD' => __( 'Sudan', 'ultra-community' ),
			'SR' => __( 'Suriname', 'ultra-community' ),
			'SJ' => __( 'Svalbard and Jan Mayen', 'ultra-community' ),
			'SZ' => __( 'Swaziland', 'ultra-community' ),
			'SE' => __( 'Sweden', 'ultra-community' ),
			'CH' => __( 'Switzerland', 'ultra-community' ),
			'SY' => __( 'Syria', 'ultra-community' ),
			'TW' => __( 'Taiwan', 'ultra-community' ),
			'TJ' => __( 'Tajikistan', 'ultra-community' ),
			'TZ' => __( 'Tanzania', 'ultra-community' ),
			'TH' => __( 'Thailand', 'ultra-community' ),
			'TL' => __( 'Timor-Leste', 'ultra-community' ),
			'TG' => __( 'Togo', 'ultra-community' ),
			'TK' => __( 'Tokelau', 'ultra-community' ),
			'TO' => __( 'Tonga', 'ultra-community' ),
			'TT' => __( 'Trinidad and Tobago', 'ultra-community' ),
			'TN' => __( 'Tunisia', 'ultra-community' ),
			'TR' => __( 'Turkey', 'ultra-community' ),
			'TM' => __( 'Turkmenistan', 'ultra-community' ),
			'TC' => __( 'Turks and Caicos Islands', 'ultra-community' ),
			'TV' => __( 'Tuvalu', 'ultra-community' ),
			'UG' => __( 'Uganda', 'ultra-community' ),
			'UA' => __( 'Ukraine', 'ultra-community' ),
			'AE' => __( 'United Arab Emirates', 'ultra-community' ),
			'GB' => __( 'United Kingdom', 'ultra-community' ),
			'US' => __( 'United States', 'ultra-community' ),
			'UM' => __( 'United States - Minor Outlying Islands', 'ultra-community' ),
			'VI' => __( 'United States - Virgin Islands', 'ultra-community' ),
			'UY' => __( 'Uruguay', 'ultra-community' ),
			'UZ' => __( 'Uzbekistan', 'ultra-community' ),
			'VU' => __( 'Vanuatu', 'ultra-community' ),
			'VA' => __( 'Vatican', 'ultra-community' ),
			'VE' => __( 'Venezuela', 'ultra-community' ),
			'VN' => __( 'Vietnam', 'ultra-community' ),
			'WF' => __( 'Wallis and Futuna', 'ultra-community' ),
			'EH' => __( 'Western Sahara', 'ultra-community' ),
			'WS' => __( 'Samoa', 'ultra-community' ),
			'YE' => __( 'Yemen', 'ultra-community' ),
			'ZM' => __( 'Zambia', 'ultra-community' ),
			'ZW' => __( 'Zimbabwe', 'ultra-community' ),
		);

		$this->OptionsList = (array)apply_filters(UltraCommHooks::FILTER_FORM_FIELD_COUNTRY_OPTIONS, $this->OptionsList);

	}

	public function __sleep()
	{
		$arrObjectProperties = get_object_vars(MchUtils::filterObjectEmptyProperties($this));
		unset(
			$arrObjectProperties['OptionsList']
		);

		return array_keys($arrObjectProperties);

	}
	public function __wakeup()
	{
		$this->setOptionsList();
	}
}