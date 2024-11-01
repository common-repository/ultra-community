<?php
namespace UltraCommunity\Modules\GeneralSettings\Plugin;

use UltraCommunity\MchLib\Utils\MchHttpRequest;
use UltraCommunity\Modules\BasePublicModule;
use UltraCommunity\MchLib\Utils\MchWpUtils;

class PluginSettingsPublicModule extends BasePublicModule
{
	protected function __construct()
	{
		parent::__construct();
	}

	public static function handleRewriteRulesFlush()
	{
		if(MchHttpRequest::getServerRequestTime() - (int)self::getInstance()->getOption(PluginSettingsAdminModule::SETTINGS_LAST_UPDATED) < 3600){


//			add_action( 'shutdown', 'flush_rewrite_rules' );

			MchWpUtils::addActionHook('shutdown', function (){

				PluginSettingsAdminModule::getInstance()->saveOption(PluginSettingsAdminModule::SETTINGS_LAST_UPDATED, MchHttpRequest::getServerRequestTime() - 3600);

				flush_rewrite_rules(true);

				//echo "Rules flushed";

			});

		}
	}
}