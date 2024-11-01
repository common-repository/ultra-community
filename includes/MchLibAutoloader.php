<?php

\spl_autoload_register(function($className){

	static $arrClassMap = array(

			'UltraCommunity\MchLib\Modules\MchBaseModule'        => 'modules/MchBaseModule.php',
			'UltraCommunity\MchLib\Modules\MchBasePublicModule'  => 'modules/MchBasePublicModule.php',
			'UltraCommunity\MchLib\Modules\MchBaseAdminModule'   => 'modules/MchBaseAdminModule.php',
			'UltraCommunity\MchLib\Modules\MchGroupedModules'    => 'modules/MchGroupedModules.php',
			'UltraCommunity\MchLib\Modules\MchModulesController' => 'modules/MchModulesController.php',

			'UltraCommunity\MchLib\Plugin\MchBasePlugin'        => 'plugin/MchBasePlugin.php',
			'UltraCommunity\MchLib\Plugin\MchBaseAdminPlugin'   => 'plugin/MchBaseAdminPlugin.php',
			'UltraCommunity\MchLib\Plugin\MchBasePublicPlugin'  => 'plugin/MchBasePublicPlugin.php',
			'UltraCommunity\MchLib\Plugin\MchBaseAdminPage'     => 'plugin/MchBaseAdminPage.php',
			'UltraCommunity\MchLib\Plugin\MchPluginUpdater'     => 'plugin/MchPluginUpdater.php',

			'UltraCommunity\MchLib\Utils\MchUtils'          => 'utils/MchUtils.php',
			'UltraCommunity\MchLib\Utils\MchWpUtils'        => 'utils/MchWpUtils.php',
			'UltraCommunity\MchLib\Utils\MchHtmlUtils'      => 'utils/MchHtmlUtils.php',
			'UltraCommunity\MchLib\Utils\MchIPUtils'        => 'utils/MchIPUtils.php',
			'UltraCommunity\MchLib\Utils\MchImageUtils'     => 'utils/MchImageUtils.php',
			'UltraCommunity\MchLib\Utils\MchFileUtils'      => 'utils/MchFileUtils.php',
			'UltraCommunity\MchLib\Utils\MchDirectoryUtils' => 'utils/MchDirectoryUtils.php',
			'UltraCommunity\MchLib\Utils\MchValidator'      => 'utils/MchValidator.php',
			'UltraCommunity\MchLib\Utils\MchMinifier'       => 'utils/MchMinifier.php',
			'UltraCommunity\MchLib\Utils\MchHttpRequest'    => 'utils/MchHttpRequest.php',
			
			'UltraCommunity\MchLib\Utils\FontAwesomeIconParser' => 'utils/FontAwesomeIconParser.php',
			'UltraCommunity\MchLib\Utils\MchHexColor'           => 'utils/MchHexColor.php',
			'UltraCommunity\MchLib\Utils\MchEmailSender'        => 'utils/MchEmailSender.php',
			'UltraCommunity\MchLib\Utils\MchSessionStorage'     => 'utils/MchSessionStorage.php',

			'UltraCommunity\MchLib\WordPress\Repository\WpPostRepository'    => 'WordPress/Repository/WpPostRepository.php',
			'UltraCommunity\MchLib\WordPress\Repository\WpUserRepository'    => 'WordPress/Repository/WpUserRepository.php',
			'UltraCommunity\MchLib\WordPress\Repository\WpCommentRepository' => 'WordPress/Repository/WpCommentRepository.php',

			//'UltraCommunity\MchLib\WordPress\Routing\Router'            => 'WordPress/Routing/Router.php',
			'UltraCommunity\MchLib\WordPress\CustomPostType'              => 'WordPress/CustomPostType.php',
			'UltraCommunity\MchLib\WordPress\ShortCodeEntity'             => 'WordPress/ShortCodeEntity.php',
			'UltraCommunity\MchLib\WordPress\NavigationMenuEditWalker'    => 'WordPress/NavigationMenuEditWalker.php',

			'UltraCommunity\MchLib\WordPress\Uploader'               => 'WordPress/Uploader.php',

			'UltraCommunity\MchLib\Tasks\MchWpTask'                  => 'tasks/MchWpTask.php',
			'UltraCommunity\MchLib\Tasks\MchWpTaskScheduler'         => 'tasks/MchWpTaskScheduler.php',

			'UltraCommunity\MchLib\Exceptions\MchLibException' => 'exceptions/MchLibException.php',
	);


	if (!isset($arrClassMap[$className]))
		return null;

	$filePath = __DIR__ . DIRECTORY_SEPARATOR . $arrClassMap[$className];

	unset($arrClassMap[$className]);

	return \file_exists($filePath) ? require $filePath : null;

}, false, true );

