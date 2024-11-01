<?php
/**
 * Copyright (c) 2016 Ultra Community (http://www.ultracommunity.com)
 */

namespace UltraCommunity\Controllers;

use UltraCommunity\FrontPages\BasePage;
use UltraCommunity\FrontPages\GroupProfilePage;
use UltraCommunity\MchLib\Utils\MchUtils;
use UltraCommunity\MchLib\Utils\MchWpUtils;
use UltraCommunity\MchLib\WordPress\CustomPostType;
use UltraCommunity\MchLib\WordPress\ShortCodeEntity;
use UltraCommunity\Modules\Appearance\General\GeneralAppearancePublicModule;
use UltraCommunity\PostsType\ForgotPasswordFormPostType;
use UltraCommunity\PostsType\GroupPostType;
use UltraCommunity\PostsType\LoginFormPostType;
use UltraCommunity\PostsType\RegisterFormPostType;
use UltraCommunity\PublicEngine;
use UltraCommunity\UltraCommHooks;

final class ShortCodesController
{
	CONST ULTRA_COMMUNITY_FORM_SHORTCODE              = 'ultracomm_form';
	CONST ULTRA_COMMUNITY_USER_PROFILE_SHORTCODE      = 'ultracomm_user_profile';
	//CONST ULTRA_COMMUNITY_USER_SETTINGS_SHORTCODE     = 'ultracomm_user_settings';
	CONST ULTRA_COMMUNITY_MEMBERS_DIRECTORY_SHORTCODE = 'ultracomm_members_directory';
	CONST ULTRA_COMMUNITY_SOCIAL_CONNECT_SHORTCODE    = 'ultracomm_social_connect';
	CONST ULTRA_COMMUNITY_GROUPS_DIRECTORY_SHORTCODE  = 'ultracomm_groups_directory';

	private static $arrShortCodes = array(
		self::ULTRA_COMMUNITY_FORM_SHORTCODE              => true,
		self::ULTRA_COMMUNITY_USER_PROFILE_SHORTCODE      => true,
		self::ULTRA_COMMUNITY_MEMBERS_DIRECTORY_SHORTCODE => true,
		self::ULTRA_COMMUNITY_SOCIAL_CONNECT_SHORTCODE    => true,
		self::ULTRA_COMMUNITY_GROUPS_DIRECTORY_SHORTCODE  => true,
	);


	private static $arrDetectedShortCodes = array();

	private function __construct(){}

	public static function isUltraCommunityShortCode($shortCodeTag)
	{
		return isset(self::$arrShortCodes[$shortCodeTag]);
	}

	public static function initShortCodesHooks()
	{
		\add_filter('do_shortcode_tag', function ($outputContent, $shortCodeTag, $arrAttributes){

			if(ShortCodesController::isUltraCommunityShortCode($shortCodeTag))
			{
				PublicEngine::getInstance()->enqueuePublicScriptsAndStyles();
			}

			return $outputContent;

		}, 10, 3);
	}


	public static function getAvailableShortCodes()
	{
		return \array_keys(self::$arrShortCodes);
	}

	public static function registerDetectedShortCodes()
	{
		
		if(MchWpUtils::isAjaxRequest() || MchWpUtils::isUserInDashboard()) {
			return;
		}
		
		
		global $wp_registered_widgets;
		$queriedObject = get_queried_object();
		
		self::$arrDetectedShortCodes['post']    = array();
		self::$arrDetectedShortCodes['widgets'] = array();

		$arrSideBarWidgets = get_option( 'sidebars_widgets', array() );
		unset($arrSideBarWidgets['wp_inactive_widgets'], $arrSideBarWidgets['array_version']);

		foreach($arrSideBarWidgets as $sideBarKey => $sideBar)
		{
			if (empty($sideBar) || 0 === strpos($sideBarKey, 'orphaned_widgets')) {
				continue;
			}

			foreach ( $sideBar as $widgetKey )
			{
				if ( empty($wp_registered_widgets[$widgetKey]['callback']) ) {
					continue;
				}

				if(empty($wp_registered_widgets[$widgetKey]['callback'][0]) || !($wp_registered_widgets[$widgetKey]['callback'][0] instanceof \WP_Widget_Text))
					continue;

				$instance = get_option( 'widget_' . $wp_registered_widgets[$widgetKey]['callback'][0]->id_base, array());
				if ( !empty($instance['_multiwidget']))
				{
					$number = $wp_registered_widgets[$widgetKey]['params'][0]['number'];
					if ( !isset($instance[$number]) ) {
						continue;
					}
					$instance = $instance[$number];
				}

				if(empty($instance['text']))
					continue;

				$arrShortCodesInfo = MchWpUtils::getShortCodesInfo( $instance['text'], self::getAvailableShortCodes() );

				foreach ( $arrShortCodesInfo['detected'] as $widgetIndex => $shortCodeTag ) {
					$shortCodeEntity = new ShortCodeEntity( $shortCodeTag );

					empty( $arrShortCodesInfo['content'][ $widgetIndex ] ) ?: $shortCodeEntity->Content = $arrShortCodesInfo['content'][ $widgetIndex ];
					empty( $arrShortCodesInfo['attributes'][ $widgetIndex ] ) ?: $shortCodeEntity->Attributes = $arrShortCodesInfo['attributes'][ $widgetIndex ];

					self::$arrDetectedShortCodes['widgets'][] = $shortCodeEntity;

				}

			}
		}


		if(!empty($queriedObject->post_content))
		{
			$content = $queriedObject->post_content;

			$arrShortCodesInfo = MchWpUtils::getShortCodesInfo( $content, self::getAvailableShortCodes() );
			
			foreach ( $arrShortCodesInfo['detected'] as $widgetIndex => $shortCodeTag ) {
				$shortCodeEntity = new ShortCodeEntity( $shortCodeTag );

				empty( $arrShortCodesInfo['content'][ $widgetIndex ] ) ?: $shortCodeEntity->Content = $arrShortCodesInfo['content'][ $widgetIndex ];
				empty( $arrShortCodesInfo['attributes'][ $widgetIndex ] ) ?: $shortCodeEntity->Attributes = $arrShortCodesInfo['attributes'][ $widgetIndex ];

				self::$arrDetectedShortCodes['post'][] = $shortCodeEntity;

			}

		}


	}

	/**
	 * @return ShortCodeEntity[]
	 */
	public static function getAllDetectedShortCodes()
	{
		$arrDetectedShortCodes = array();
		foreach(self::$arrDetectedShortCodes as $key => $arrValues)
		{
			$arrDetectedShortCodes = \array_merge($arrDetectedShortCodes, $arrValues);
		}

		return $arrDetectedShortCodes;
	}

	public static function hasDetectedShortCodes()
	{
		foreach (self::$arrDetectedShortCodes as $arrShortCodes){
			if(!empty($arrShortCodes))
				return true;
		}

		return false;
	}
	
	/**
	 * @param        $tagName
	 * @param string $section
	 *
	 * @return ShortCodeEntity[]
	 */
	public static function getDetectedShortCodesByTagName($tagName, $section = 'post') // $section can be post, widgets, null for all sections
	{
		$arrDetectedShortCodes = array();

		if(!empty($section) && !empty(self::$arrDetectedShortCodes[$section]))
		{
			foreach(self::$arrDetectedShortCodes[$section] as $detectedShortCode){
				($tagName !== $detectedShortCode->TagName) ?: $arrDetectedShortCodes[] = $detectedShortCode;
			}

			return $arrDetectedShortCodes;
		}

		if(empty($section))
		{
			foreach(self::$arrDetectedShortCodes as $arrSectionShortCodes)
			{
				foreach((array)$arrSectionShortCodes as $detectedShortCode){
					($tagName !== $detectedShortCode->TagName) ?: $arrDetectedShortCodes[] = $detectedShortCode;
				}
			}
		}


		return $arrDetectedShortCodes;
	}

	public static function handleShortCodeOutput($arrAttributes, $shortCodeText, $shortCodeName)
	{
		
		$outputContent = null;

		$activePage = FrontPageController::getActivePage();

		switch ($shortCodeName)
		{
			case self::ULTRA_COMMUNITY_USER_PROFILE_SHORTCODE :

				if(FrontPageController::getActivePageTypeId() !== FrontPageController::PAGE_USER_PROFILE)
					break;

				$outputContent = MchUtils::captureOutputBuffer(function (){
					FrontPageController::getActivePage()->renderMarkup();
				});

			break;

			case self::ULTRA_COMMUNITY_FORM_SHORTCODE              :
			case self::ULTRA_COMMUNITY_MEMBERS_DIRECTORY_SHORTCODE :
			case self::ULTRA_COMMUNITY_GROUPS_DIRECTORY_SHORTCODE  :
			

				if(empty($arrAttributes['id']))
					break;

				if(null !== $activePage && $activePage->getModuleCustomPostType())
				{
					if( $arrAttributes['id'] == $activePage->getModuleCustomPostType()->PostId )
					{
						$pageOutputContent =  $activePage->getShortCodeContent( $arrAttributes, $shortCodeText, $shortCodeName );
						
						MchWpUtils::addFilterHook(UltraCommHooks::FILTER_PAGE_CONTENT_OUTPUT_HTML, function($renderedOutput, $pageInstance) use($pageOutputContent, $activePage){
							if( !($pageInstance instanceof BasePage) || !$activePage->getModuleCustomPostType() || !$pageInstance->getModuleCustomPostType())
								return $renderedOutput;
							
							if($activePage->getModuleCustomPostType()->PostId != $pageInstance->getModuleCustomPostType()->PostId)
								return $renderedOutput;
							
							if($activePage->PageId !== $pageInstance->PageId)
								return $renderedOutput;
							
							return $pageOutputContent;
							
						}, -1, 2);
						
						$outputContent = MchUtils::captureOutputBuffer(function (){
							FrontPageController::getActivePage()->renderMarkup();
						});
						
						
						break;
					}
					
					
					if( ($activePage->getModuleCustomPostType() instanceof GroupPostType) && ($shortCodeName === self::ULTRA_COMMUNITY_GROUPS_DIRECTORY_SHORTCODE)  )
					{
						$outputContent = MchUtils::captureOutputBuffer(function (){
							FrontPageController::getActivePage()->renderMarkup();
						});

						break;
						
					}
					
				}

				if(null === ($activePage = FrontPageController::getPageInstanceByCustomPostTypeId($arrAttributes['id'])))
					break;
				
				$pageOutputContent =  $activePage->getShortCodeContent( $arrAttributes, $shortCodeText, $shortCodeName );

				MchWpUtils::addFilterHook(UltraCommHooks::FILTER_PAGE_CONTENT_OUTPUT_HTML, function($renderedOutput, $pageInstance) use($pageOutputContent, $activePage){
					
					if( !($pageInstance instanceof BasePage) || !$activePage->getModuleCustomPostType() || !$pageInstance->getModuleCustomPostType())
						return $renderedOutput;
					
					if($activePage->getModuleCustomPostType()->PostId != $pageInstance->getModuleCustomPostType()->PostId)
						return $renderedOutput;
					
					if($activePage->PageId !== $pageInstance->PageId)
						return $renderedOutput;
					
					return $pageOutputContent;
	
				}, -1, 2);

				$outputContent = MchUtils::captureOutputBuffer(function () use($activePage){
					$activePage->renderMarkup();
				});
				
				break;

				
		}

		
		return $outputContent;
		
//		if(null === $outputContent)
//			return null;
//
//		if(FrontPageController::hasActivePage() && FrontPageController::canUseGlobalTemplate())
//			return $outputContent;
//
//		$pageColorSchemeClassName = GeneralAppearancePublicModule::getPageColorSchemeClassName();
//
//
//		if(FrontPageController::getActivePageTypeId() !== FrontPageController::PAGE_USER_PROFILE && !FrontPageController::canUseGlobalTemplate())
//		{
//			$outputContent = '<div class="uch-body">' . '<div class = "uch-content">' . $outputContent . '</div>' . '</div>';
//		}
//
//		return "<div class=\"uch uc-transparent-bg $pageColorSchemeClassName\">" . $outputContent . '</div>';


	}


	public static function getEmbeddableShortCode(CustomPostType $customPostType)
	{
		switch (true)
		{
			case $customPostType->PostType === PostTypeController::POST_TYPE_USER_PROFILE_FORM:
				return '[' . self::ULTRA_COMMUNITY_USER_PROFILE_SHORTCODE . ']';

			case $customPostType->PostType === PostTypeController::POST_TYPE_MEMBERS_DIRECTORY:
				return '[' . self::ULTRA_COMMUNITY_MEMBERS_DIRECTORY_SHORTCODE . ' id="' . $customPostType->PostId . '"]';

			case $customPostType->PostType === PostTypeController::POST_TYPE_GROUPS_DIRECTORY:
				return '[' . self::ULTRA_COMMUNITY_GROUPS_DIRECTORY_SHORTCODE . ' id="' . $customPostType->PostId . '"]';

			case $customPostType->PostType === PostTypeController::POST_TYPE_SOCIAL_CONNECT:
				return '[' . self::ULTRA_COMMUNITY_SOCIAL_CONNECT_SHORTCODE . ' id="' . $customPostType->PostId . '"]';

			default :
				return '[' . self::ULTRA_COMMUNITY_FORM_SHORTCODE . ' id="' . $customPostType->PostId . '"]';
		}


		return null;
	}

	public static function getLoginFormEmbeddableShortCode()
	{
		$arrLoginFormPostType = PostTypeController::getPublishedPosts(PostTypeController::POST_TYPE_LOGIN_FORM);
		$loginFormPostType    = reset($arrLoginFormPostType);
		if(empty($loginFormPostType))
			return null;

		return self::getEmbeddableShortCode($loginFormPostType);

	}
}