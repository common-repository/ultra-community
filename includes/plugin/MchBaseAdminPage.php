<?php
/**
 * Copyright (c) 2016 Ultra Community (http://www.ultracommunity.com)
 */

namespace  UltraCommunity\MchLib\Plugin;

use UltraCommunity\MchLib\Modules\MchBaseAdminModule;
use UltraCommunity\MchLib\Modules\MchGroupedModules;
use UltraCommunity\MchLib\Modules\MchModulesController;
use UltraCommunity\MchLib\Utils\MchHtmlUtils;
use UltraCommunity\MchLib\Utils\MchUtils;
use UltraCommunity\MchLib\Utils\MchValidator;
use UltraCommunity\MchLib\Utils\MchWpUtils;

abstract class MchBaseAdminPage
{
	CONST ACTION_BEFORE_SETTINGS_FORM               = 'mch-before-settings-form';
	CONST ACTION_SETTINGS_FORM_BEFORE_FIELDS        = 'mch-settings-form-before-fields';
	CONST ACTION_BEFORE_SETTINGS_FORM_SUBMIT_BUTTON = 'mch-before-settings-form-submit-button';
	CONST ACTION_AFTER_SETTINGS_FORM_SUBMIT_BUTTON  = 'mch-after-settings-form-submit-button';
	CONST ACTION_AFTER_SETTINGS_FORM                = 'mch-after-settings-form';

	CONST ACTION_BEFORE_RENDER_SUBTABS = 'mch-before-render-subtabs';

	CONST FILTER_MODULE_SUBTAB_NAME = 'mch-admin-module-subtab-name';
	CONST FILTER_MODULE_SUBTAB_URL  = 'mch-admin-module-subtab-url';


	private $pageBrowserTitle     = null;
	private $pageMenuTitle        = null;
	//private $pluginSlug           = null;
	private $adminScreenId        = null;
	private $groupModulesList     = null;
	private $pageLayoutColumns    = null;
	private $pageBadgeCounter     = 0;
	private $renderModulesInSubTabs = null;

	private $arrActiveAdminModules = null;

	public function __construct($pageMenuTitle, /*$pageBrowserTitle,*/ /*$pluginSlug,*/ $renderModulesInSubTabs = false)
	{
		$this->renderModulesInSubTabs = !!$renderModulesInSubTabs;
		//$this->pageBrowserTitle       = $pageBrowserTitle;
		$this->pageMenuTitle          = $pageMenuTitle;
		//$this->pluginSlug             = $pluginSlug;
		$this->groupModulesList       = array();
		$this->arrActiveAdminModules     = array();
		$this->pageLayoutColumns      = 1;


		add_action('current_screen', array($this, 'registerModulesSettingsSections'), 10);

		add_action('current_screen', array($this, 'saveModulesNetworkSettingsOptions'), 11);

		add_action('admin_notices',  array($this, 'displayAdminNotices'));
	}

	protected function setPageLayoutColumns($pageLayoutColumns)
	{
		$this->pageLayoutColumns = $pageLayoutColumns;
	}
	protected function getPageLayoutColumns()
	{
		return $this->pageLayoutColumns;
	}
	
	public function setPageBadgeCounter($pageBadgeCounter)
	{
		$this->pageBadgeCounter = (int)$pageBadgeCounter;
	}
	public function getPageBadgeCounter()
	{
		return $this->pageBadgeCounter;
	}
	
	public function shouldRenderModulesInSubTabs()
	{
		return $this->renderModulesInSubTabs;
	}

	public function getActiveAdminModules()
	{
		return $this->arrActiveAdminModules;
	}

	public function displayAdminNotices()
	{
		global $wp_settings_errors;
		if(empty($wp_settings_errors))
			return;

		if(!$this->isActive())
			return;

		$wp_settings_errors = array_unique((array)$wp_settings_errors, SORT_REGULAR);

		foreach($this->groupModulesList as $groupIndex => $groupedModules)
		{
			foreach ( ((array)$groupedModules->getGroupedModules()) as $moduleIndex => $adminModuleInstance )
			{
				if ( ! ( $adminModuleInstance instanceof MchBaseAdminModule ) )
					continue;

				settings_errors( $adminModuleInstance->getSettingKey(), false, false );
			}
		}
	}

	protected function displayGroupModulesMessages($groupIndex)
	{
		static $arrMessages = array();
		if(isset($arrMessages[$groupIndex]))
			return;
		foreach($this->groupModulesList as $registeredGroupIndex => $groupedModules)
		{
			if($registeredGroupIndex != $groupIndex)
				continue;

			foreach ( ((array)$groupedModules->getGroupedModules()) as $moduleIndex => $adminModuleInstance )
			{
				if ( ! ( $adminModuleInstance instanceof MchBaseAdminModule ) )
					continue;

				$message = $adminModuleInstance->getFormattedMessagesForDisplay();
				if(empty($message))
					continue;

				$arrMessages[$groupIndex] = true;
				echo $message;
				break;
			}
		}


	}

	public function registerGroupedModules(array $groupedModulesList)
	{
		foreach((array) $groupedModulesList as $groupedModules) {
			if (($groupedModules instanceof MchGroupedModules) && $groupedModules->hasModules())
				$this->groupModulesList[] = $groupedModules;
		}

		return  (false !== end($this->groupModulesList)) ? key($this->groupModulesList) : -1;
	}

	public function getGroupedModules()
	{
		return $this->groupModulesList;
	}

	public function hasRegisteredModules()
	{
		return isset($this->groupModulesList[0]);
	}


	public function saveModulesNetworkSettingsOptions()
	{
		if( empty($_REQUEST['action']) || strcasecmp($_REQUEST['action'], 'update') !== 0 || empty($_REQUEST['_wpnonce'])  || empty($_REQUEST['option_page']))
			return;

		if( ! MchWpUtils::isAdminInNetworkDashboard() || !$this->isActive() )
			return;

		foreach($this->groupModulesList as $groupIndex => $groupedModules)
		{
			$settingsGroup = $this->getSettingGroupId($groupIndex);
			if(0 !== strcmp($settingsGroup, $_REQUEST['option_page']))
				continue;

			if( ! wp_verify_nonce($_REQUEST['_wpnonce'], "$settingsGroup-options") )
				continue;

			foreach( ((array)$groupedModules->getGroupedModules()) as $moduleIndex => $adminModuleInstance )
			{
				if(empty($_POST['mch-module-key']) || $_POST['mch-module-key'] !== $adminModuleInstance->getSettingKey()) {
					continue;
				}

				$moduleNetworkOptions = isset($_REQUEST[$adminModuleInstance->getSettingKey()]) ? (array)$_REQUEST[$adminModuleInstance->getSettingKey()] : array();
				$adminModuleInstance->saveNetworkSettingOptions($moduleNetworkOptions);
			}

		}

	}

	protected function getPostedModuleKey()
	{
		return empty($_POST['mch-module-key']) ? null : $_POST['mch-module-key'];
	}



	protected function getSubTabModuleKey($groupModuleIndex = 0)
	{
		if(!$this->shouldRenderModulesInSubTabs())
			return null;

		$subTabModuleKey = isset( $_GET['modulekey'] ) ? $_GET['modulekey'] : null;
		$firstModuleKey  = null;
		$keyFound        = false;

		foreach ( $this->groupModulesList as $index => $groupedModules )
		{
			if($index != $groupModuleIndex)
				continue;

			foreach ( (array)$groupedModules->getGroupedModules() as $moduleIndex => $adminModuleInstance )
			{
				if ( ! ( $adminModuleInstance instanceof MchBaseAdminModule ) ) {
					continue;
				}

				!empty($firstModuleKey) ?: $firstModuleKey = $adminModuleInstance->getSettingKey();

				if($keyFound = ($subTabModuleKey === $adminModuleInstance->getSettingKey()))
					break;

			}
			if(!empty($keyFound))
				break;
		}

		return $keyFound ? $subTabModuleKey : $firstModuleKey;

	}

	public function registerModulesSettingsSections()
	{

		add_action('load-' . $this->adminScreenId, array($this, 'registerPageMetaBoxes'));

		foreach($this->groupModulesList as $groupIndex => $groupedModules)
		{
			$settingsGroup = $this->getSettingGroupId($groupIndex);
			$subTabModuleKey = $this->getSubTabModuleKey($groupIndex);

			foreach(((array)$groupedModules->getGroupedModules()) as $moduleIndex => $adminModuleInstance)
			{
				if(! ( $adminModuleInstance instanceof MchBaseAdminModule) )
					continue;


				if(empty($_POST) && $this->shouldRenderModulesInSubTabs() && $subTabModuleKey !== $adminModuleInstance->getSettingKey())
					continue;

				if(!empty($_POST))
				{
					if(empty($_POST['mch-module-key']) || $_POST['mch-module-key'] !== $adminModuleInstance->getSettingKey()) {
						continue;
					}
				}

				/**
				 * Avoid calling the sanitize function twice - WP Bug still present
				 * This must me placed before register_setting function
				 */
				if(MchBasePlugin::isNetworkActivated())
				{
					if(null === get_site_option($adminModuleInstance->getSettingKey(), null)){
						update_site_option($adminModuleInstance->getSettingKey(), array());
					}
				}
				else
				{
					if(null === get_option($adminModuleInstance->getSettingKey(), null)){
						update_option($adminModuleInstance->getSettingKey(), array());
					}
				}



				register_setting($settingsGroup, $adminModuleInstance->getSettingKey(), array( $adminModuleInstance, 'validateModuleSettingsFields' ) );

				//$sectionTitle = !empty($this->arrGroupSectionTitle[$groupIndex]) ? (string)$this->arrGroupSectionTitle[$groupIndex] : '';

				add_settings_section($adminModuleInstance->getSettingKey(), null,
									array($adminModuleInstance, 'renderModuleSettingsSectionHeader'), $settingsGroup );

				foreach((array)$adminModuleInstance->getDefaultOptions() as $optionName => $arrOptionInfo)
				{
					if(empty($arrOptionInfo['LabelText']) && empty($arrOptionInfo['InputType']))
						continue;

					add_settings_field($optionName,
						empty($arrOptionInfo['LabelText']) ? '' : esc_html($arrOptionInfo['LabelText']),
						array($adminModuleInstance, 'renderModuleSettingsField'),
						$settingsGroup,
						$adminModuleInstance->getSettingKey(),
						array($optionName => $arrOptionInfo)
					);

				}

				add_action('shutdown', array($adminModuleInstance, 'saveRegisteredAdminMessages'));

			}
		}

	}

	public function renderGroupModulesSettings(MchBaseAdminPage $pageObject = null, $groupIndex = null)
	{

		if(!is_numeric($groupIndex))
		{
			foreach ( func_get_args() as $receivedArgument )
			{
				if ( !is_array($receivedArgument) || !isset( $receivedArgument['args'] ) || !is_numeric($receivedArgument['args']))
					continue;

				$groupIndex = $receivedArgument['args'];
				break;
			}
		}

		if(empty($this->groupModulesList[$groupIndex]))
			return;

		$this->displayGroupModulesMessages($groupIndex);

		$subTabModuleKey = $this->getSubTabModuleKey($groupIndex);
		$activeAdminModuleInstance = null;

		if(!empty($subTabModuleKey))
		{
			//MchWpUtils::doAction(self::ACTION_BEFORE_RENDER_SUBTABS, $this);

			echo '<ul id="" class="mch-module-tabs">';

			foreach ( $this->groupModulesList as $index => $groupedModules )
			{
				if($groupIndex != $index)
					continue;

				foreach ( (array)$groupedModules->getGroupedModules() as $moduleIndex => $adminModuleInstance )
				{
					if ( ! ( $adminModuleInstance instanceof MchBaseAdminModule ) ) {
						continue;
					}

					$arrClasses = array();
					if ( $subTabModuleKey === $adminModuleInstance->getSettingKey() ) {
						$arrClasses[] = 'active';
						$activeAdminModuleInstance = $adminModuleInstance;
					}

					$classAttribute = implode( ' ', $arrClasses );

					$moduleSettingsUrl = ( add_query_arg( array(
							'modulekey' => $adminModuleInstance->getSettingKey(),
					), $this->getAdminUrl() ) );

					$moduleSettingsUrl = apply_filters(self::FILTER_MODULE_SUBTAB_URL, $moduleSettingsUrl, $adminModuleInstance, $this);

					$subTabName = MchModulesController::getModuleDisplayNameByInstance($adminModuleInstance);

					$subTabName = apply_filters(self::FILTER_MODULE_SUBTAB_NAME, $subTabName, $adminModuleInstance, $this);

					echo '<li class="' . $classAttribute . '"> <a href="' . esc_url($moduleSettingsUrl) . '">' . $subTabName;

					echo '</a><span></span></li>';

				}
			}

			echo '</ul>';
		}


		if ( ! ( $activeAdminModuleInstance instanceof MchBaseAdminModule ) ) {
			return;
		}

		$this->arrActiveAdminModules[] = $activeAdminModuleInstance;

		$arrClasses = array('mch-module-settings-form', $subTabModuleKey);

		$this->shouldRenderModulesInSubTabs() ? $arrClasses[] = 'tabbed' : null;

		$classAttribute = implode(' ', $arrClasses );

		do_action(self::ACTION_BEFORE_SETTINGS_FORM);

		echo '<form class = "'.$classAttribute.'" method="post" action="' . (MchWpUtils::isAdminInNetworkDashboard() ? '' : 'options.php') . '">';

		do_action(self::ACTION_SETTINGS_FORM_BEFORE_FIELDS, $this, $activeAdminModuleInstance);


		echo '<input type="hidden" name="mch-module-key" value="' . esc_attr($activeAdminModuleInstance->getSettingKey()) . '">';


		settings_fields( $this->getSettingGroupId($groupIndex) );

		$this->do_settings_sections( $this->getSettingGroupId($groupIndex) );
		//do_settings_sections( $this->getSettingGroupId($groupIndex) );

		echo $this->shouldRenderModulesInSubTabs() ? '' : '<hr />';


		do_action(self::ACTION_BEFORE_SETTINGS_FORM_SUBMIT_BUTTON, $this, $subTabModuleKey);

		submit_button();

		do_action(self::ACTION_AFTER_SETTINGS_FORM_SUBMIT_BUTTON, $this, $subTabModuleKey, $activeAdminModuleInstance);

		echo $this->shouldRenderModulesInSubTabs() ? '' : '<hr />';

		echo  '</form>';


		//do_action(self::ACTION_AFTER_SETTINGS_FORM, $this, $subTabModuleKey);

	}

	public function registerPageMetaBoxes()
	{

		foreach($this->groupModulesList as $groupIndex => $groupedModules)
		{

			add_meta_box(
				$this->getSettingGroupId($groupIndex),
				$groupedModules->getGroupTitle(),
				array( $this, 'renderGroupModulesSettings' ),
				$this->adminScreenId,
				'advanced',
				'core',
				$groupIndex
			);
		}

	}

	public function renderPageContent()
	{
		wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
		wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );

		$code = '<div id="poststuff">';

		$code .= '<div id="post-body" class="metabox-holder columns-'. $this->pageLayoutColumns .'">';
		$code .= '<div id="postbox-container-2" class="postbox-container mch-left-side-holder">';

		ob_start();

			do_action( 'mch-admin-page-top', $this );

			do_meta_boxes($this->adminScreenId, 'top', $this );
			do_meta_boxes($this->adminScreenId, 'normal', $this );

			do_action( 'mch-admin-page-middle', $this );

			do_meta_boxes($this->adminScreenId, 'advanced', $this ); // $this is sent as first argument to add_meta_box callback function (renderGroupModulesSettings in my case)

			do_meta_boxes($this->adminScreenId, 'bottom', $this );

			do_action( 'mch-admin-page-bottom', $this );

		$code .= ob_get_clean();
		$code .= '</div>';

		$code .= '<div id="postbox-container-1" class="postbox-container mch-right-side-holder">';

		ob_start();

			do_meta_boxes($this->adminScreenId, 'side', null );

		$code .= ob_get_clean();

		$code .= '</div>';

		$code .= '</div>';
		$code .= '</div>';

		echo $code;
	}

	protected function getSettingGroupId($moduleListGroupIndex)
	{
		return $this->getAdminScreenId() . "-group-{$moduleListGroupIndex}";
	}

	public function setAdminScreenId($adminScreenId)
	{
		$this->adminScreenId = $adminScreenId;
	}

	public function getAdminScreenId()
	{
		return $this->adminScreenId;
	}

	public function getAdminUrl()
	{
		if ( ! MchWpUtils::isUserInNetworkDashboard() )
			return menu_page_url($this->getPageMenuSlug(), false);

		global $_parent_pages;
		$url = null;
		if ( isset( $_parent_pages[$this->getPageMenuSlug()] ) ) {
			$parent_slug = $_parent_pages[ $this->getPageMenuSlug() ];
			if ( $parent_slug && ! isset( $_parent_pages[ $parent_slug ] ) ) {
				$url = self_admin_url( add_query_arg( 'page', $this->getPageMenuSlug(), $parent_slug ) );
			} else {
				$url = self_admin_url( 'admin.php?page=' . $this->getPageMenuSlug() );
			}
		}

		return null === $url ? '' : esc_url($url);
	}

	public function isActive()
	{
		$currentScreen = get_current_screen();
		return (!empty($currentScreen->id) && $this->adminScreenId === str_replace('-network', '', $currentScreen->id));
	}

	public function getPageBrowserTitle()
	{
		return $this->pageBrowserTitle;
	}

	public function getPageMenuTitle()
	{
		return $this->pageMenuTitle;
	}

	public function getPageMenuSlug()
	{
		return MchUtils::replaceNonAlphaNumericCharacters(strtolower(MchBasePlugin::getPluginAbbr() . '-' . $this->pageMenuTitle . '-' . MchUtils::getClassShortNameFromNameSpace($this)), '-');
	}

	private function do_settings_sections( $page )
	{
		global $wp_settings_sections, $wp_settings_fields;
		if ( ! isset( $wp_settings_sections[$page] ) )
			return;

		foreach ( (array) $wp_settings_sections[$page] as $section )
		{
			if ( $section['title'] )
				echo "<h2>{$section['title']}</h2>\n";

			if ( $section['callback'] )
				call_user_func( $section['callback'], $section );

			if ( ! isset( $wp_settings_fields ) || !isset( $wp_settings_fields[$page] ) || !isset( $wp_settings_fields[$page][$section['id']] ) )
				continue;

			echo '<table class="form-table">';

			$this->do_settings_fields( $page, $section['id'] );

			echo '</table>';
		}
	}

	private function do_settings_fields($page, $section)
	{
		global $wp_settings_fields;
		if ( ! isset( $wp_settings_fields[$page][$section] ) )
			return;


		foreach ( (array) $wp_settings_fields[$page][$section] as $field )
		{

			//print_r($field);


			$class = '';
			$fieldRowHTML = '';

			if ( ! empty( $field['args']['class'] ) )
			{
				$class = ' class="' . esc_attr( $field['args']['class'] ) . '"';
			}

			//echo "<tr{$class}>";
			$fieldRowHTML .= "<tr{$class}>";

			if ( ! empty( $field['args']['label_for'] ) )
			{
				//echo '<th scope="row"><label for="' . esc_attr( $field['args']['label_for'] ) . '">' . $field['title'] . '</label></th>';
				$fieldRowHTML .= '<th scope="row"><label for="' . esc_attr( $field['args']['label_for'] ) . '">' . $field['title'] . '</label></th>';
			}
			else
			{
				//echo '<th scope="row">' . $field['title'] . '</th>';
				$fieldRowHTML .= '<th scope="row">' . $field['title'] . '</th>';
			}


			//echo '<td>';
			$fieldRowHTML .= '<td>';

			ob_start();

			call_user_func($field['callback'], $field['args']);

			$fieldOutput = ob_get_clean();


			$fieldRowHTML .= $fieldOutput;

			//echo '</td>';
			//echo '</tr>';

			$fieldRowHTML .= '</td>';
			$fieldRowHTML .= '</tr>';

			if(!empty($field['args'][$field['id']]['InputType']) && $field['args'][$field['id']]['InputType'] === MchHtmlUtils::FORM_ELEMENT_INPUT_HIDDEN)
			{
				$fieldRowHTML = $fieldOutput;
			}

			echo apply_filters(MchBaseAdminModule::FILTER_FIELD_SETTINGS_TABLE_ROW_OUTPUT, $fieldRowHTML, $field['id']);

		}
	}

}
