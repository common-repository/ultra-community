<?php
namespace UltraCommunity\MchLib\Plugin;

abstract class MchBaseAdminPlugin extends MchBasePlugin
{
	/**
	 * @var MchBaseAdminPage[]
	 */
	private $adminPagesList = array();

	public abstract function enqueueAdminScriptsAndStyles();
	public abstract function getMenuPosition();

	protected function __construct()
	{
		parent::__construct();

		add_action('admin_init', array($this, 'initializeAdminPlugin'));

		add_action( self::isNetworkActivated() ? 'network_admin_menu' : 'admin_menu', array( $this, 'buildPluginMenu' ), 10);

		add_action('admin_enqueue_scripts', array( $this, 'enqueueAdminScriptsAndStyles' ));

	}

	public function registerAdminPage(MchBaseAdminPage $adminPage)
	{
		\do_action(self::$PLUGIN_ABBR . '-before-register-admin-page', $adminPage, $this);

		$this->adminPagesList[] = $adminPage;

		\do_action(self::$PLUGIN_ABBR . '-after-admin-page-registered', $adminPage, $this);

	}

	public function getRegisteredAdminPages()
	{
		return $this->adminPagesList;
		//return  apply_filters(self::$PLUGIN_ABBR . '-' . 'admin-pages', $this->adminPagesList);
	}

	public function renderPluginActiveAdminPage()
	{
		$activeAdminPage = $this->getActivePage();

		$arrPageHolderClasses = array('wrap', 'container-fluid', $activeAdminPage->getPageMenuSlug());

		$adminPageHtmlCode  = '<div class="' . implode(' ', $arrPageHolderClasses) . '">';

		$adminPageHtmlCode .= '<h2 class="nav-tab-wrapper">';

		foreach ($this->getRegisteredAdminPages() as $adminPage) {
			
			$adminPageHtmlCode .= '<a class="nav-tab' . (($adminPage->isActive()) ? ' nav-tab-active' : '') . '" href="?page=' . $adminPage->getPageMenuSlug() . '">';
			
			$adminPageHtmlCode .= $adminPage->getPageMenuTitle();
			
			!$adminPage->getPageBadgeCounter() ?:  $adminPageHtmlCode .= '<span class="uc-badge-counter"><span>' . $adminPage->getPageBadgeCounter() .  '</span></span>';
			
			$adminPageHtmlCode .= '</a>';
			
		}

		$adminPageHtmlCode .= '</h2>';

		echo $adminPageHtmlCode;


		if(null !== $activeAdminPage)
		{
			$activeAdminPage->renderPageContent();
		}

		echo '</div>';
	}


	public function buildPluginMenu()
	{
		$arrRegisteredPages = $this->getRegisteredAdminPages();
		$adminFirstPage = reset($arrRegisteredPages);
		if(false === $adminFirstPage)
			return;

		$totalBadgeCounters = 0;
		foreach ($arrRegisteredPages as $registeredPage)
		{
			$totalBadgeCounters += $registeredPage->getPageBadgeCounter();
		}
		
		$totalBadgeCounters = min($totalBadgeCounters, 10);
		$counterIconUrl = is_file(self::$PLUGIN_DIRECTORY_PATH . "/assets/admin/images/svg-numbers/{$totalBadgeCounters}.svg") ? self::$PLUGIN_URL ."/assets/admin/images/svg-numbers/{$totalBadgeCounters}.svg" : null;
		
		$pageAdminScreenId = add_menu_page(
			$adminFirstPage->getPageBrowserTitle() ,
			self::$PLUGIN_NAME ,
			'manage_options',
			$adminFirstPage->getPageMenuSlug(),
			array($this, 'renderPluginActiveAdminPage'),
			$counterIconUrl ? $counterIconUrl : 'dashicons-groups',
			$this->getMenuPosition()
		);

		$this->adminPagesList[0]->setAdminScreenId($pageAdminScreenId);

		$arrSize = count($this->adminPagesList);
		if(1 === $arrSize)
			return;

		add_submenu_page(
			$adminFirstPage->getPageMenuSlug(),
			$adminFirstPage->getPageBrowserTitle(),
			$adminFirstPage->getPageMenuTitle() . (  $adminFirstPage->getPageBadgeCounter() ? sprintf('<span class="update-plugins"><span class="plugin-count">%d</span></span>', $adminFirstPage->getPageBadgeCounter()) : null),
			'manage_options',
			$adminFirstPage->getPageMenuSlug()
		);


		for($i = 1; $i < $arrSize; ++$i)
		{
			if(!$this->adminPagesList[$i]->hasRegisteredModules())
			{
				unset($this->adminPagesList[$i]);
				continue;
			}

			$pageMenuTitle = $this->adminPagesList[$i]->getPageMenuTitle();
			if(strpos($pageMenuTitle, 'Extensions') !== false) {
				$pageMenuTitle = '<span style="color:#f16600">' . $pageMenuTitle . '</span>';
			}
			
			$pageMenuTitle .= (  $this->adminPagesList[$i]->getPageBadgeCounter() ? sprintf(' <span class="update-plugins"><span class="plugin-count">%d</span></span>', $this->adminPagesList[$i]->getPageBadgeCounter()) : null);
			
			$pageAdminScreenId = add_submenu_page(
				$adminFirstPage->getPageMenuSlug(),
				$this->adminPagesList[$i]->getPageBrowserTitle(),
				$pageMenuTitle,
				'manage_options',
				$this->adminPagesList[$i]->getPageMenuSlug(),
				array($this, 'renderPluginActiveAdminPage')
			);

			$this->adminPagesList[$i]->setAdminScreenId($pageAdminScreenId);
		}

	}

	/**
	 * @return MchBaseAdminPage | null
	 */
	public function getActivePage()
	{
		foreach($this->getRegisteredAdminPages() as $adminPage)
			if($adminPage->isActive())
				return $adminPage;

		return null;
	}

	public function initializeAdminPlugin()
	{
		$classInstance = $this;

		add_action('admin_enqueue_scripts', function() use ($classInstance){

			//$classInstance = $className::getInstance();
			
			wp_add_inline_style( 'wp-admin',
			'
				#adminmenu .wp-menu-image img[src*="svg-numbers"]
				{
				    padding: 8px 0 0 0;
				    width: 20px;
				    height: 20px;
				    opacity: 1;
				}
			');
			
			
			if(!$classInstance->getActivePage())
				return;

			wp_enqueue_style('dashboard');
			wp_enqueue_script('dashboard');

			wp_add_inline_style( 'wp-admin',
				'
						.clearfix:after, div.mch-left-side-holder div.inside:after
						{
						    content: ".";
						    display: block;
						    height: 0;
						    clear: both;
						    visibility: hidden;
						    zoom: 1
						}
						div.mch-left-side-holder
						{
							width:100% !important;
						}

						div.mch-left-side-holder .wp-picker-container
						{
							display:inline-block;
						}
						div.mch-left-side-holder #normal-sortables:empty, div.mch-left-side-holder #advanced-sortables:empty, div.mch-left-side-holder #bottom-sortables:empty
						{
						    display:none;
						}

						.uc-badge-counter{
							display: inline-block;
						    vertical-align: top;
						    margin: 1px 0 0 5px;
						    padding: 0 5px;
						    min-width: 7px;
						    height: 17px;
						    border-radius: 10px;
						    background-color: #ca4a1f;
						    color: #fff;
						    font-size: 9px;
						    line-height: 17px;
						    text-align: center;
						    z-index: 26;
						}

					'
			);

			if($classInstance->getActivePage()->shouldRenderModulesInSubTabs())
			{
				wp_add_inline_style( 'wp-admin',
					'
						ul.mch-module-tabs
						{
						    width: 150px;
						    float: left;
						    margin:0;
						}

						ul.mch-module-tabs a
						{
						    padding: 6px 0;
						    text-decoration: none;
						    display: block;
						    border: 1px solid #fff;
						    font-size: 14px;
						}

						ul.mch-module-tabs li.active a {
						    line-height: 25px;
						    z-index: 50 !important;
						    background-color: #F6FBFD;
						    border: 1px solid #E1E1E1;
						    border-right-color: #F6FBFD;
						    border-left: 2px solid #2EA2CC;
						    margin: 0 -1px 0 0;
						    width: 138px;
						    padding: 6px 0 6px 10px !important;
						}

						form.mch-module-settings-form.tabbed{
							margin-left:150px;
							border:1px solid #E1E1E1;
							padding:15px;
							padding-top:0;
						}

						form.mch-module-settings-form.tabbed .uc-settings-section-header{
							position:relative;
							margin:0 !important;
							padding:0 !important;
							line-height:37px;height:37px;
							border-bottom:1px solid #E1E1E1;
						}

						form.mch-module-settings-form table.form-table{
							clear:right !important;
							margin-top:0 !important;
						}
					'
				);

			}



		}, PHP_INT_MAX);

	}




}