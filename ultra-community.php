<?php
/**
 * @package   UltraCommunity
 * @link      https://ultracommunity.com
 *
 * @wordpress-plugin
 * Plugin Name: Ultra Community
 * Plugin URI: https://www.ultracommunity.com
 * Description: An extremely powerful community plugin.
 * Version: 2.1.2
 * Author: UltraCommunity
 * Text Domain: ultra-community
 * Domain Path: /languages
 */

use UltraCommunity\RequestHandler;
use UltraCommunity\MchLib\Plugin\MchBasePlugin;

final class UltraCommunity
{
	CONST PLUGIN_VERSION    = '2.1.2';
	CONST PLUGIN_ABBR       = 'uc';
	CONST PLUGIN_SLUG       = 'ultra-community';
	CONST PLUGIN_NAME       = 'Ultra Community';
	CONST PLUGIN_SITE_URL   = 'https://ultracommunity.com';

	CONST PLUGIN_MAIN_FILE  = __FILE__;


	private function __construct()
	{}

	public static function init()
	{
		MchBasePlugin::setPluginInfo(array(
			'PLUGIN_MAIN_FILE'   => self::PLUGIN_MAIN_FILE,
			'PLUGIN_VERSION'     => self::PLUGIN_VERSION,
			'PLUGIN_SLUG'        => self::PLUGIN_SLUG,
			'PLUGIN_ABBR'        => self::PLUGIN_ABBR,
			'PLUGIN_NAME'        => self::PLUGIN_NAME,
		));

		RequestHandler::handleRequest();
		
		//add_filter('wp_php_error_message', function ($message, $arrPhpError) {print_r($arrPhpError);exit;}, 10, 2);
		
	}

}

require __DIR__ . '/includes/MchLibAutoloader.php';
require __DIR__ . '/engine/uc-functions.php';

class_alias('\UltraCommunity\UltraCommHooks', '\UltraCommHooks');

!defined('ABSPATH') || UltraCommunity::init();
