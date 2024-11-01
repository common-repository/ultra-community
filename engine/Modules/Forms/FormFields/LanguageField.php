<?php
/**
 * Copyright (c) 2016 Ultra Community (http://www.ultracommunity.com)
 */

namespace UltraCommunity\Modules\Forms\FormFields;


use UltraCommunity\MchLib\Utils\MchUtils;
use UltraCommunity\MchLib\Utils\MchWpUtils;
use UltraCommunity\UltraCommHooks;

class LanguageField extends BaseField
{
	public function __construct()
	{
		parent::__construct( parent::FIELD_TYPE_SELECT );
		$this->setOptionsList();
	}

	public function setOptionsList()
	{
		$this->OptionsList =  array(
			"aa" => __("Afar", 'ultra-community'),
			"ab" => __("Abkhazian", 'ultra-community'),
			"ae" => __("Avestan", 'ultra-community'),
			"af" => __("Afrikaans", 'ultra-community'),
			"ak" => __("Akan", 'ultra-community'),
			"am" => __("Amharic", 'ultra-community'),
			"an" => __("Aragonese", 'ultra-community'),
			"ar" => __("Arabic", 'ultra-community'),
			"as" => __("Assamese", 'ultra-community'),
			"av" => __("Avaric", 'ultra-community'),
			"ay" => __("Aymara", 'ultra-community'),
			"az" => __("Azerbaijani", 'ultra-community'),
			"ba" => __("Bashkir", 'ultra-community'),
			"be" => __("Belarusian", 'ultra-community'),
			"bg" => __("Bulgarian", 'ultra-community'),
			"bh" => __("Bihari", 'ultra-community'),
			"bi" => __("Bislama", 'ultra-community'),
			"bm" => __("Bambara", 'ultra-community'),
			"bn" => __("Bengali", 'ultra-community'),
			"bo" => __("Tibetan", 'ultra-community'),
			"br" => __("Breton", 'ultra-community'),
			"bs" => __("Bosnian", 'ultra-community'),
			"ca" => __("Catalan", 'ultra-community'),
			"ce" => __("Chechen", 'ultra-community'),
			"ch" => __("Chamorro", 'ultra-community'),
			"co" => __("Corsican", 'ultra-community'),
			"cr" => __("Cree", 'ultra-community'),
			"cs" => __("Czech", 'ultra-community'),
			"cu" => __("Church Slavic", 'ultra-community'),
			"cv" => __("Chuvash", 'ultra-community'),
			"cy" => __("Welsh", 'ultra-community'),
			"da" => __("Danish", 'ultra-community'),
			"de" => __("German", 'ultra-community'),
			"dv" => __("Divehi", 'ultra-community'),
			"dz" => __("Dzongkha", 'ultra-community'),
			"ee" => __("Ewe", 'ultra-community'),
			"el" => __("Greek", 'ultra-community'),
			"en" => __("English", 'ultra-community'),
			"eo" => __("Esperanto", 'ultra-community'),
			"es" => __("Spanish", 'ultra-community'),
			"et" => __("Estonian", 'ultra-community'),
			"eu" => __("Basque", 'ultra-community'),
			"fa" => __("Persian", 'ultra-community'),
			"ff" => __("Fulah", 'ultra-community'),
			"fi" => __("Finnish", 'ultra-community'),
			"fj" => __("Fijian", 'ultra-community'),
			"fo" => __("Faroese", 'ultra-community'),
			"fr" => __("French", 'ultra-community'),
			"fy" => __("Western Frisian", 'ultra-community'),
			"ga" => __("Irish", 'ultra-community'),
			"gd" => __("Scottish Gaelic", 'ultra-community'),
			"gl" => __("Galician", 'ultra-community'),
			"gn" => __("Guarani", 'ultra-community'),
			"gu" => __("Gujarati", 'ultra-community'),
			"gv" => __("Manx", 'ultra-community'),
			"ha" => __("Hausa", 'ultra-community'),
			"he" => __("Hebrew", 'ultra-community'),
			"hi" => __("Hindi", 'ultra-community'),
			"ho" => __("Hiri Motu", 'ultra-community'),
			"hr" => __("Croatian", 'ultra-community'),
			"ht" => __("Haitian", 'ultra-community'),
			"hu" => __("Hungarian", 'ultra-community'),
			"hy" => __("Armenian", 'ultra-community'),
			"hz" => __("Herero", 'ultra-community'),
			"ia" => __("Interlingua" , 'ultra-community'),
			"id" => __("Indonesian", 'ultra-community'),
			"ie" => __("Interlingue", 'ultra-community'),
			"ig" => __("Igbo", 'ultra-community'),
			"ii" => __("Sichuan Yi", 'ultra-community'),
			"ik" => __("Inupiaq", 'ultra-community'),
			"io" => __("Ido", 'ultra-community'),
			"is" => __("Icelandic", 'ultra-community'),
			"it" => __("Italian", 'ultra-community'),
			"iu" => __("Inuktitut", 'ultra-community'),
			"ja" => __("Japanese", 'ultra-community'),
			"jv" => __("Javanese", 'ultra-community'),
			"ka" => __("Georgian", 'ultra-community'),
			"kg" => __("Kongo", 'ultra-community'),
			"ki" => __("Kikuyu", 'ultra-community'),
			"kj" => __("Kwanyama", 'ultra-community'),
			"kk" => __("Kazakh", 'ultra-community'),
			"kl" => __("Kalaallisut", 'ultra-community'),
			"km" => __("Khmer", 'ultra-community'),
			"kn" => __("Kannada", 'ultra-community'),
			"ko" => __("Korean", 'ultra-community'),
			"kr" => __("Kanuri", 'ultra-community'),
			"ks" => __("Kashmiri", 'ultra-community'),
			"ku" => __("Kurdish", 'ultra-community'),
			"kv" => __("Komi", 'ultra-community'),
			"kw" => __("Cornish", 'ultra-community'),
			"ky" => __("Kirghiz", 'ultra-community'),
			"la" => __("Latin", 'ultra-community'),
			"lb" => __("Luxembourgish", 'ultra-community'),
			"lg" => __("Ganda", 'ultra-community'),
			"li" => __("Limburgish", 'ultra-community'),
			"ln" => __("Lingala", 'ultra-community'),
			"lo" => __("Lao", 'ultra-community'),
			"lt" => __("Lithuanian", 'ultra-community'),
			"lu" => __("Luba-Katanga", 'ultra-community'),
			"lv" => __("Latvian", 'ultra-community'),
			"mg" => __("Malagasy", 'ultra-community'),
			"mh" => __("Marshallese", 'ultra-community'),
			"mi" => __("Maori", 'ultra-community'),
			"mk" => __("Macedonian", 'ultra-community'),
			"ml" => __("Malayalam", 'ultra-community'),
			"mn" => __("Mongolian", 'ultra-community'),
			"mr" => __("Marathi", 'ultra-community'),
			"ms" => __("Malay", 'ultra-community'),
			"mt" => __("Maltese", 'ultra-community'),
			"my" => __("Burmese", 'ultra-community'),
			"na" => __("Nauru", 'ultra-community'),
			"nb" => __("Norwegian Bokmal", 'ultra-community'),
			"nd" => __("North Ndebele", 'ultra-community'),
			"ne" => __("Nepali", 'ultra-community'),
			"ng" => __("Ndonga", 'ultra-community'),
			"nl" => __("Dutch", 'ultra-community'),
			"nn" => __("Norwegian Nynorsk", 'ultra-community'),
			"no" => __("Norwegian", 'ultra-community'),
			"nr" => __("South Ndebele", 'ultra-community'),
			"nv" => __("Navajo", 'ultra-community'),
			"ny" => __("Chichewa", 'ultra-community'),
			"oc" => __("Occitan", 'ultra-community'),
			"oj" => __("Ojibwa", 'ultra-community'),
			"om" => __("Oromo", 'ultra-community'),
			"or" => __("Oriya", 'ultra-community'),
			"os" => __("Ossetian", 'ultra-community'),
			"pa" => __("Panjabi", 'ultra-community'),
			"pi" => __("Pali", 'ultra-community'),
			"pl" => __("Polish", 'ultra-community'),
			"ps" => __("Pashto", 'ultra-community'),
			"pt" => __("Portuguese", 'ultra-community'),
			"qu" => __("Quechua", 'ultra-community'),
			"rm" => __("Raeto-Romance", 'ultra-community'),
			"rn" => __("Kirundi", 'ultra-community'),
			"ro" => __("Romanian", 'ultra-community'),
			"ru" => __("Russian", 'ultra-community'),
			"rw" => __("Kinyarwanda", 'ultra-community'),
			"sa" => __("Sanskrit", 'ultra-community'),
			"sc" => __("Sardinian", 'ultra-community'),
			"sd" => __("Sindhi", 'ultra-community'),
			"se" => __("Northern Sami", 'ultra-community'),
			"sg" => __("Sango", 'ultra-community'),
			"si" => __("Sinhala", 'ultra-community'),
			"sk" => __("Slovak", 'ultra-community'),
			"sl" => __("Slovenian", 'ultra-community'),
			"sm" => __("Samoan", 'ultra-community'),
			"sn" => __("Shona", 'ultra-community'),
			"so" => __("Somali", 'ultra-community'),
			"sq" => __("Albanian", 'ultra-community'),
			"sr" => __("Serbian", 'ultra-community'),
			"ss" => __("Swati", 'ultra-community'),
			"st" => __("Southern Sotho", 'ultra-community'),
			"su" => __("Sundanese", 'ultra-community'),
			"sv" => __("Swedish", 'ultra-community'),
			"sw" => __("Swahili", 'ultra-community'),
			"ta" => __("Tamil", 'ultra-community'),
			"te" => __("Telugu", 'ultra-community'),
			"tg" => __("Tajik", 'ultra-community'),
			"th" => __("Thai", 'ultra-community'),
			"ti" => __("Tigrinya", 'ultra-community'),
			"tk" => __("Turkmen", 'ultra-community'),
			"tl" => __("Tagalog", 'ultra-community'),
			"tn" => __("Tswana", 'ultra-community'),
			"to" => __("Tonga", 'ultra-community'),
			"tr" => __("Turkish", 'ultra-community'),
			"ts" => __("Tsonga", 'ultra-community'),
			"tt" => __("Tatar", 'ultra-community'),
			"tw" => __("Twi", 'ultra-community'),
			"ty" => __("Tahitian", 'ultra-community'),
			"ug" => __("Uighur", 'ultra-community'),
			"uk" => __("Ukrainian", 'ultra-community'),
			"ur" => __("Urdu", 'ultra-community'),
			"uz" => __("Uzbek", 'ultra-community'),
			"ve" => __("Venda", 'ultra-community'),
			"vi" => __("Vietnamese", 'ultra-community'),
			"vo" => __("Volapuk", 'ultra-community'),
			"wa" => __("Walloon", 'ultra-community'),
			"wo" => __("Wolof", 'ultra-community'),
			"xh" => __("Xhosa", 'ultra-community'),
			"yi" => __("Yiddish", 'ultra-community'),
			"yo" => __("Yoruba", 'ultra-community'),
			"za" => __("Zhuang", 'ultra-community'),
			"zh" => __("Chinese", 'ultra-community'),
			"zu" => __("Zulu", 'ultra-community'),
		);

		$this->OptionsList = (array)apply_filters(UltraCommHooks::FILTER_FORM_FIELD_LANGUAGE_OPTIONS, $this->OptionsList);

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