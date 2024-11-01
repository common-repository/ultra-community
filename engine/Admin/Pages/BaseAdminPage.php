<?php
/**
 * Copyright (c) 2016 Ultra Community (http://www.ultracommunity.com)
 */
namespace UltraCommunity\Admin\Pages;



use UltraCommunity\MchLib\Utils\MchWpUtils;
use UltraCommunity\UltraCommUtils;

abstract class BaseAdminPage extends \UltraCommunity\MchLib\Plugin\MchBaseAdminPage
{
	public abstract function getPageHiddenContent();

	public function __construct($pageMenuTitle, $renderModulesInSubTabs = true)
	{
		parent::__construct($pageMenuTitle,  $renderModulesInSubTabs);

		$this->setPageLayoutColumns(2);

	}

	public function renderPageContent()
	{
		parent::renderPageContent();

		$selfPageInstance = $this;
		MchWpUtils::addActionHook('admin_footer',function() use ($selfPageInstance){
			echo '<div id="uc-admin-page-hidden-content" style="display:none !important;">' . $selfPageInstance->getPageHiddenContent() . '</div>';
		}, 10, 1);


	}


	public function registerPageMetaBoxes()
	{

		parent::registerPageMetaBoxes();

		if ( $this->getPageLayoutColumns() <= 1 ) {
			return;
		}

		add_meta_box(
			"ultracomm-help-metabox",
			__( 'Need help? Have questions...?', 'invisible-recaptcha' ),
			array( $this, 'renderNeedHelpMetaBox' ),
			$this->getAdminScreenId(),
			'side',
			'core',
			null
		);

	}

	public function renderNeedHelpMetaBox()
	{
		echo '<div><img class="logo-help" src="https://ps.w.org/ultra-community/assets/icon-128x128.png" /></div>';
		echo '<p class="contact-help"> <a class = "uc-button uc-button-primary" href="https://ultracommunity.com/forums/forum/ultracommunity/" target="_blank">Get In Touch With Us</a></p>';
	}




	public function getAdminUrl($appendAddNewQueryString = false)
	{
		if(!$appendAddNewQueryString) {
			return parent::getAdminUrl();
		}

		return esc_url(add_query_arg(array('add-new' => 1), parent::getAdminUrl()));
	}

	protected function getConfirmationPopupContent($title, $text, $wrapperId = null)
	{
		!empty($wrapperId) ?: $wrapperId = 'uc-confirmation-popup';

		$outputContent = "<p style=\"font-size: 1.1em;font-weight: 500;text-align: center;margin: 15px 0;\">$text</p>";
		$buttonYes  = __('Yes', 'ultra-community');
		$buttonNo   = __('No' , 'ultra-community');

		$footerContent = <<<FC

		<div class = "uc-popup-footer-confirmation uc-g">
			<div class = "uc-u-1-2">
				<button class = "uc-button uc-button-primary">$buttonYes</button>
			</div>
			<div class = "uc-u-1-2">
				<button class = "uc-button uc-button-danger">$buttonNo</button>
			</div>
		</div>

FC;

		return UltraCommUtils::getWrappedPopupHolderContent($wrapperId, $title, $outputContent, $footerContent);
	}



}