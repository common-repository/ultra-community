<?php

/**
 * @package   UltraCommunity
 * @link      https://www.ultracommunity.com
 *
 */

use UltraCommunity\Controllers\PluginVersionController;

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

class_exists('UltraCommunity') || require_once 'ultra-community.php';

class UltraCommunityUninstaller
{
	public function __construct()
	{
		if(!current_user_can('delete_plugins')){
			exit;
		}

		PluginVersionController::uninstall();
	}

}

new UltraCommunityUninstaller();
