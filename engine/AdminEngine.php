<?php
/**
 * Copyright (c) 2016 Ultra Community (http://www.ultracommunity.com)
 */

namespace UltraCommunity;

use UltraCommunity\Admin\Pages\AddOnsAdminPage;
use UltraCommunity\Admin\Pages\DirectoriesAdminPage;
use UltraCommunity\Admin\Pages\FormsAdminPage;
use UltraCommunity\Admin\Pages\GeneralSettingsAdminPage;
use UltraCommunity\Admin\Pages\ManageUsersAdminPage;
use UltraCommunity\Admin\Pages\CustomTabsAdminPage;
use UltraCommunity\Admin\Pages\PostSubmissionsAdminPage;
use UltraCommunity\Admin\Pages\SocialConnectAdminPage;
use UltraCommunity\Admin\Pages\ExtensionsAdminPage;
use UltraCommunity\Admin\Pages\UserRoleAdminPage;
use UltraCommunity\Controllers\PluginVersionController;
use UltraCommunity\Controllers\PostTypeController;
use UltraCommunity\Controllers\UserRoleController;
use UltraCommunity\Entities\UserMetaEntity;
use UltraCommunity\MchLib\Plugin\MchBaseAdminPlugin;
use UltraCommunity\MchLib\Utils\MchHttpRequest;
use UltraCommunity\MchLib\Utils\MchUtils;
use UltraCommunity\MchLib\Utils\MchWpUtils;
use UltraCommunity\Admin\Pages\AppearanceAdminPage;
use UltraCommunity\Modules\GeneralSettings\FrontPage\FrontPageSettingsAdminModule;
use UltraCommunity\Modules\GeneralSettings\Plugin\PluginSettingsAdminModule;
use UltraCommunity\Repository\UserRepository;


final class AdminEngine extends MchBaseAdminPlugin
{
	protected function __construct()
	{
		parent::__construct();

		MchWpUtils::addActionHook('_admin_menu', function(){

			PluginVersionController::handleVersionChanges();

			AdminEngine::getInstance()->registerAdminPage(new GeneralSettingsAdminPage(__('General Settings', 'ultra-community')));
			AdminEngine::getInstance()->registerAdminPage(new UserRoleAdminPage(__('User Roles', 'ultra-community')));
			AdminEngine::getInstance()->registerAdminPage(new FormsAdminPage(__('Forms', 'ultra-community')));
			AdminEngine::getInstance()->registerAdminPage(new AppearanceAdminPage(__('Appearance', 'ultra-community')));
			AdminEngine::getInstance()->registerAdminPage(new DirectoriesAdminPage(__('Directories', 'ultra-community')));
			
			AdminEngine::getInstance()->registerAdminPage(new ManageUsersAdminPage(__('Manage Users', 'ultra-community')));
			
			AdminEngine::getInstance()->registerAdminPage(new SocialConnectAdminPage(__('Social Connect', 'ultra-community')));
			AdminEngine::getInstance()->registerAdminPage(new CustomTabsAdminPage(__('Custom Tabs', 'ultra-community')));
			//AdminEngine::getInstance()->registerAdminPage(new PostSubmissionsAdminPage(__('Post Submissions', 'ultra-community')));
			AdminEngine::getInstance()->registerAdminPage(new AddOnsAdminPage(__('Add-Ons Settings', 'ultra-community')));

			AdminEngine::getInstance()->registerAdminPage(new ExtensionsAdminPage(__('Extensions', 'ultra-community')));
			
			
		}, 20);

	}

	public function initializeAdminPlugin()
	{
		parent::initializeAdminPlugin();

		UserRoleController::handleUserRoles();

		MchWpUtils::addFilterHook('display_post_states', function($arrPostStates, $wpPost){

			if(empty($wpPost->ID) || empty($wpPost->post_type) || 'page' !== $wpPost->post_type)
				return $arrPostStates;

			!empty($arrPostStates) ?: $arrPostStates = array();

			switch($wpPost->ID)
			{
				case FrontPageSettingsAdminModule::getInstance()->getOption(FrontPageSettingsAdminModule::LOGIN_PAGE_ID) :
					$arrPostStates[] = __('Ultra Community Login Page', 'ultra-community');
					break;

				case FrontPageSettingsAdminModule::getInstance()->getOption(FrontPageSettingsAdminModule::REGISTER_PAGE_ID) :
					$arrPostStates[] = __('Ultra Community Registration Page', 'ultra-community');
					break;

				case FrontPageSettingsAdminModule::getInstance()->getOption(FrontPageSettingsAdminModule::USER_PROFILE_PAGE_ID) :
					$arrPostStates[] = __('Ultra Community User Profile Page', 'ultra-community');
					break;

				case FrontPageSettingsAdminModule::getInstance()->getOption(FrontPageSettingsAdminModule::FORGOT_PASSWORD_PAGE_ID) :
					$arrPostStates[] = __('Ultra Community Forgot Password Page', 'ultra-community');
					break;

				case FrontPageSettingsAdminModule::getInstance()->getOption(FrontPageSettingsAdminModule::MEMBERS_DIRECTORY_PAGE_ID) :
					$arrPostStates[] = __('Ultra Community Members Directory Page', 'ultra-community');
					break;

				case FrontPageSettingsAdminModule::getInstance()->getOption(FrontPageSettingsAdminModule::GROUPS_DIRECTORY_PAGE_ID) :
					$arrPostStates[] = __('Ultra Community Groups Directory Page', 'ultra-community');
					break;

			}

			return $arrPostStates;

		}, 10, 2);


		foreach(array('updated_option', 'update_site_option') as $updatedOptionAction){
			MchWpUtils::addActionHook($updatedOptionAction, function($updatedOption){
				if($updatedOption === 'active_plugins' || $updatedOption === 'active_sitewide_plugins' || MchUtils::stringStartsWith($updatedOption, \UltraCommunity::PLUGIN_ABBR . '-')){
					PluginSettingsAdminModule::getInstance()->saveOption(PluginSettingsAdminModule::SETTINGS_LAST_UPDATED, MchHttpRequest::getServerRequestTime());
				}
			});
		}

		foreach(array('switch_theme', 'after_switch_theme') as $wpSwitchThemeHook){
			MchWpUtils::addActionHook($wpSwitchThemeHook, function(){
					PluginSettingsAdminModule::getInstance()->saveOption(PluginSettingsAdminModule::SETTINGS_LAST_UPDATED, MchHttpRequest::getServerRequestTime());
			});
		}

		PluginVersionController::checkExtensionsUpdates();

	}


	public function enqueueAdminScriptsAndStyles()
	{

		if(!$this->getActivePage()){
			return;
		}


		wp_enqueue_style('ultracomm-font-roboto', '//fonts.googleapis.com/css?family=Roboto:400,500,600,700', array());

		wp_enqueue_style (self::$PLUGIN_SLUG . '-font-awesome', '//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css', array(), self::$PLUGIN_VERSION);

		wp_enqueue_style (self::$PLUGIN_SLUG . '-tooltipster',  self::$PLUGIN_URL . '/assets/admin/scripts/tooltipster/tooltipster.bundle.min.css', array(), self::$PLUGIN_VERSION);
		wp_enqueue_style (self::$PLUGIN_SLUG . '-magnific-popup', self::$PLUGIN_URL . '/assets/admin/scripts/magnific-popup/magnific-popup.css', array(), self::$PLUGIN_VERSION);

		wp_enqueue_style (self::$PLUGIN_SLUG . '-select2', self::$PLUGIN_URL .  '/assets/admin/scripts/select2/select2.min.css', array(), self::$PLUGIN_VERSION);
		wp_enqueue_style (self::$PLUGIN_SLUG . '-select2-theme', self::$PLUGIN_URL .  '/assets/admin/scripts/select2/select2-uc-theme.css', array(self::$PLUGIN_SLUG . '-select2'), self::$PLUGIN_VERSION);
		//wp_enqueue_style (self::$PLUGIN_SLUG . '-icon-picker', self::$PLUGIN_URL .  '/assets/admin/scripts/icon-picker/css/asIconPicker.css', array(self::$PLUGIN_SLUG . '-select2'), self::$PLUGIN_VERSION);
		wp_enqueue_style (self::$PLUGIN_SLUG . '-simple-icon-picker', self::$PLUGIN_URL .  '/assets/admin/scripts/simple-icon-picker/simple-iconpicker.css', array(self::$PLUGIN_SLUG . '-select2'), self::$PLUGIN_VERSION);

		wp_enqueue_style (self::$PLUGIN_SLUG . '-ultracomm-base',    self::$PLUGIN_URL . '/assets/admin/styles/ultracomm-base.css', array(), self::$PLUGIN_VERSION);
		wp_enqueue_style (self::$PLUGIN_SLUG . '-ultracomm-buttons', self::$PLUGIN_URL . '/assets/admin/styles/ultracomm-buttons.css', array(self::$PLUGIN_SLUG . '-ultracomm-base'), self::$PLUGIN_VERSION);
		wp_enqueue_style (self::$PLUGIN_SLUG . '-ultracomm-forms',   self::$PLUGIN_URL . '/assets/admin/styles/ultracomm-forms.css', array(self::$PLUGIN_SLUG . '-ultracomm-buttons'), self::$PLUGIN_VERSION);
		wp_enqueue_style (self::$PLUGIN_SLUG . '-ultracomm-tables',  self::$PLUGIN_URL . '/assets/admin/styles/ultracomm-tables.css', array(self::$PLUGIN_SLUG . '-ultracomm-base'), self::$PLUGIN_VERSION);


		wp_enqueue_style (self::$PLUGIN_SLUG . '-ultracomm-common', self::$PLUGIN_URL . '/assets/admin/styles/ultracomm-common.css', array(self::$PLUGIN_SLUG . '-ultracomm-forms'), self::$PLUGIN_VERSION);

		wp_enqueue_style (self::$PLUGIN_SLUG . '-base-admin-style', self::$PLUGIN_URL . '/assets/admin/styles/admin-base-style.css', array(), self::$PLUGIN_VERSION);


		wp_enqueue_style( 'wp-color-picker' );

		//wp_enqueue_script(self::$PLUGIN_SLUG . '-iconpicker',  self::$PLUGIN_URL .  '/assets/admin/scripts/icon-picker/jquery-asIconPicker.js', array('wp-color-picker'), self::$PLUGIN_VERSION);
		wp_enqueue_script(self::$PLUGIN_SLUG . '-simple-icon-picker', self::$PLUGIN_URL .  '/assets/admin/scripts/simple-icon-picker/simple-iconpicker.js', array(), self::$PLUGIN_VERSION);

		wp_enqueue_script(self::$PLUGIN_SLUG . '-tooltipster',  self::$PLUGIN_URL .  '/assets/admin/scripts/tooltipster/tooltipster.bundle.min.js', array('wp-color-picker'), self::$PLUGIN_VERSION);
		wp_enqueue_script(self::$PLUGIN_SLUG . '-select2',      self::$PLUGIN_URL .  '/assets/admin/scripts/select2/select2.min.js', array('jquery'), self::$PLUGIN_VERSION);
		wp_enqueue_script(self::$PLUGIN_SLUG . '-magnific-popup', self::$PLUGIN_URL .  '/assets/admin/scripts/magnific-popup/jquery.magnific-popup.min.js', array('jquery'), self::$PLUGIN_VERSION);
		wp_enqueue_script(self::$PLUGIN_SLUG . '-admin-script', self::$PLUGIN_URL . '/assets/admin/scripts/ultracomm-admin.js', array('jquery'), self::$PLUGIN_VERSION);
		wp_enqueue_script(self::$PLUGIN_SLUG . '-admin-script-ff', self::$PLUGIN_URL . '/assets/admin/scripts/ultracomm-admin-form-fields.js', array(self::$PLUGIN_SLUG . '-admin-script'), self::$PLUGIN_VERSION);

		wp_enqueue_script( 'jquery-ui-sortable' ); wp_enqueue_script( 'jquery-ui-datepicker' );

		wp_localize_script(self::$PLUGIN_SLUG . '-admin-script', 'UltraCommAdmin', array(
			'ajaxUrl' => MchWpUtils::getAjaxUrl(),
			'ajaxRequestNonce' => AjaxHandler::getAjaxNonce(),
			'generalErrorMessage' => __('An error has occurred while processing your request', 'ultra-community'),
		));

	}

	public function getMenuPosition()
	{
		return '41.8213';
	}

}