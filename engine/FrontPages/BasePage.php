<?php

namespace UltraCommunity\FrontPages;

use UltraCommunity\Controllers\ShortCodesController;
use UltraCommunity\Controllers\TemplatesController;
use UltraCommunity\Controllers\UserController;
use UltraCommunity\MchLib\Utils\MchWpUtils;
use UltraCommunity\MchLib\WordPress\CustomPostType;
use UltraCommunity\Modules\Appearance\General\GeneralAppearancePublicModule;
use UltraCommunity\UltraCommHooks;
use UltraCommunity\UltraCommUtils;

abstract class BasePage
{
	public $PageId = null;

	private $customPostType   = null;


//	private $redirectToUrl = null;

	public abstract function getShortCodeContent($arrAttributes, $shortCodeText = null, $shortCodeName = null);
	public abstract function isAuthenticationRequired();
	public abstract function processRequest();


	public abstract function getHeaderMarkup();
	public abstract function getNavBarMarkup();
	public abstract function getSideBarMarkup();
	public abstract function getContentMarkup();

	public abstract function getSubMenuTemplateArguments();

	public function __construct($pageId = null)
	{
		$this->PageId = (int)$pageId;
	}

	private function getSubMenuMarkup()
	{
		//array('profileSectionKey' => null, 'arrNavBarMenuItems' => [])

		$arrSubMenuTemplateArguments = MchWpUtils::applyFilters(UltraCommHooks::FILTER_PAGE_SUB_MENU_TEMPLATE_ARGUMENTS, (array)$this->getSubMenuTemplateArguments(), $this);

		return empty($arrSubMenuTemplateArguments) ? null : TemplatesController::getTemplateOutputContent(TemplatesController::USER_PROFILE_NAV_BAR_TEMPLATE, (array)$arrSubMenuTemplateArguments);
		
	}

	public function renderMarkup()
	{


		MchWpUtils::doAction(UltraCommHooks::ACTION_BEFORE_START_RENDERING_PAGE, $this);


		$pageColorSchemeClassName = GeneralAppearancePublicModule::getPageColorSchemeClassName();

		$pageSectionOutput = null;

		$arrSectionsClasses = array(
				'holder'   => \implode(' ', \array_map('sanitize_html_class', (array)\apply_filters(UltraCommHooks::FILTER_PAGE_HOLDER_HTML_CLASSES,  array('uch')        , $this))),
				'header'   => \implode(' ', \array_map('sanitize_html_class', (array)\apply_filters(UltraCommHooks::FILTER_PAGE_HEADER_HTML_CLASSES,  array('uch-header') , $this))),
				'navbar'   => \implode(' ', \array_map('sanitize_html_class', (array)\apply_filters(UltraCommHooks::FILTER_PAGE_NAVBAR_HTML_CLASSES,  array('uch-navbar') , $this))),
				'content'  => \implode(' ', \array_map('sanitize_html_class', (array)\apply_filters(UltraCommHooks::FILTER_PAGE_CONTENT_HTML_CLASSES, array('uch-content'), $this))),
				'sidebar'  => \implode(' ', \array_map('sanitize_html_class', (array)\apply_filters(UltraCommHooks::FILTER_PAGE_SIDEBAR_HTML_CLASSES, array('uch-sidebar'), $this))),
		);

		echo "<div class=\"{$arrSectionsClasses['holder']} $pageColorSchemeClassName\">";

			do_action(UltraCommHooks::ACTION_BEFORE_RENDER_PAGE_HEADER, $this);

				$pageSectionOutput = apply_filters(UltraCommHooks::FILTER_PAGE_HEADER_OUTPUT_HTML, $this->getHeaderMarkup(), $this);

				empty($pageSectionOutput) ?: print('<div class="' . $arrSectionsClasses['header'] . '">' . $pageSectionOutput . '</div>');

			do_action(UltraCommHooks::ACTION_AFTER_PAGE_HEADER_RENDERED, $this);


			do_action(UltraCommHooks::ACTION_BEFORE_RENDER_PAGE_NAV_BAR, $this);

				$pageSectionOutput = apply_filters(UltraCommHooks::FILTER_PAGE_NAVBAR_OUTPUT_HTML, $this->getNavBarMarkup(), $this);

				empty($pageSectionOutput) ?: print('<div class="' . $arrSectionsClasses['navbar'] . '">' . $pageSectionOutput  . '</div>');

			do_action(UltraCommHooks::ACTION_AFTER_PAGE_NAV_BAR_RENDERED, $this);

			echo '<div class="uch-body">';

				do_action(UltraCommHooks::ACTION_BEFORE_RENDER_PAGE_CONTENT, $this);
					echo '<div class="', $arrSectionsClasses['content'], '">',
						apply_filters(UltraCommHooks::FILTER_PAGE_SUB_MENU_OUTPUT_HTML, $this->getSubMenuMarkup(), $this),
						apply_filters(UltraCommHooks::FILTER_PAGE_CONTENT_OUTPUT_HTML , $this->getContentMarkup(), $this),
					'</div>';
				do_action(UltraCommHooks::ACTION_AFTER_PAGE_CONTENT_RENDERED, $this);

				do_action(UltraCommHooks::ACTION_BEFORE_RENDER_PAGE_SIDE_BAR, $this);
					echo '<div class="', $arrSectionsClasses['sidebar'], '">', apply_filters(UltraCommHooks::FILTER_PAGE_SIDEBAR_OUTPUT_HTML, $this->getSideBarMarkup(), $this), '</div>';
				do_action(UltraCommHooks::ACTION_AFTER_PAGE_SIDE_BAR_RENDERED, $this);

			echo '</div>';

		echo '</div>';


	}

	protected function getPageFooterContent()
	{
		return null;
	}

	protected function getPageHiddenContent()
	{
		$pageHiddenContent = null;

		if(!UserController::isUserLoggedIn())
		{
			$arrFilters = array(UltraCommHooks::FILTER_PAGE_HEADER_OUTPUT_HTML, UltraCommHooks::FILTER_PAGE_NAVBAR_OUTPUT_HTML, UltraCommHooks::FILTER_PAGE_SUB_MENU_OUTPUT_HTML);
			
			foreach ($arrFilters as $filter)
			{
				MchWpUtils::addFilterHook($filter, function ($outputContent){
					
					
					
					return null;
					
				}, 1, PHP_INT_MAX);
				
			}
			
			$pageHiddenContent .=  UltraCommUtils::getWrappedPopupHolderContent('uc-modal-login-popup', '', do_shortcode(ShortCodesController::getLoginFormEmbeddableShortCode()));
		}
		

		return \apply_filters(UltraCommHooks::FILTER_PAGE_FOOTER_HIDDEN_CONTENT, $pageHiddenContent, $this);

	}

	public function renderPageFooterContent()
	{
		echo $this->getPageFooterContent();
		echo '<div style="display:none !important;">'  . $this->getPageHiddenContent() . '</div>';
	}

	public function getPageCustomCss()
	{
		$pageCustomCss = GeneralAppearancePublicModule::getCustomCss();

		return $pageCustomCss;
	}

	public function setModuleCustomPostType(CustomPostType $customPostType = null)
	{
		$this->customPostType = $customPostType;
	}

	/**
	 * @return null|CustomPostType
	 */
	public function getModuleCustomPostType()
	{
		return $this->customPostType;
	}

	public function hasCustomPostType()
	{
		return (null !== $this->customPostType);
	}

	public function getPostRequestActionKey()
	{
		return \UltraCommunity::PLUGIN_ABBR . '-'. md5(get_class($this));
	}


	public function getPageUrl()
	{
		return empty($this->PageId) ? null : get_permalink($this->PageId);
	}


	public function isPostRequest()
	{
		return !empty($_POST);
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