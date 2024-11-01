<?php
/**
 * Copyright (c) 2016 Ultra Community (http://www.ultracommunity.com)
 */

namespace UltraCommunity\MchLib\Utils;

final class MchWpUtils
{
	public static function getSiteNameById($siteId)
	{
		return get_blog_option($siteId, 'blogname', null);
	}

	public static function isUserLoggedIn()
	{
		return is_user_logged_in(); //(bool)self::getCurrentUserId(); //
	}

	public static function isAdminLoggedIn()
	{
		$loggedInUser = wp_get_current_user();
		if(empty($loggedInUser->ID)) // user not logged in
			return false;

		if(\is_multisite() && self::isSuperAdminLoggedIn())
			return true;

		return \in_array('administrator', $loggedInUser->roles) ||  $loggedInUser->has_cap( 'delete_users' );

	}

	public static function isSuperAdminLoggedIn()
	{
		static $isLoggedIn = null;
		return null !== $isLoggedIn ? $isLoggedIn : $isLoggedIn = is_super_admin();
	}

	public static function isUserInDashboard()
	{
		return  ( ( !defined( 'DOING_AJAX' ) || !DOING_AJAX ) && is_admin() );
	}

	public static function isAdminInDashboard()
	{
		return self::isAdminLoggedIn() && self::isUserInDashboard();
	}


	public static function isUserInNetworkDashboard()
	{
		return  is_network_admin();
	}

	public static function isAdminInNetworkDashboard()
	{
		return self::isAdminLoggedIn() && self::isUserInNetworkDashboard();
	}

	public static function isAjaxRequest()
	{
		return ( \defined( 'DOING_AJAX' ) && DOING_AJAX /*&& is_admin()*/ );
	}
	public static function isXmlRpcRequest()
	{
		return defined('XMLRPC_REQUEST') && XMLRPC_REQUEST;
	}

	public static function isMultiSite()
	{
		return is_multisite();
	}

	public static function logOutCurrentUser($urlRedirectTo = null)
	{
		wp_logout();
		wp_set_current_user(0);

		if(empty($urlRedirectTo)){
			return;
		}

		headers_sent() ?: nocache_headers();

		isset($urlRedirectTo[1]) ? self::redirectToUrl($urlRedirectTo) : null;
	}

	public static function autoLogInUser($userId, $remember = false, $urlRedirectTo = null)
	{
		wp_set_current_user((int)$userId);
		self::addActionHook('set_logged_in_cookie', function ($loggedInUserCookie){$_COOKIE[LOGGED_IN_COOKIE] = $loggedInUserCookie;}, PHP_INT_MAX);
		wp_set_auth_cookie((int)$userId, $remember );

		if(empty($urlRedirectTo)){
			return;
		}

		isset($urlRedirectTo[1]) ? self::redirectToUrl($urlRedirectTo) : null;
	}

	public static function getAdminEmailAddress()
	{
		return get_bloginfo('admin_email');
	}

	public static function getAdminDisplayName()
	{
		if(! function_exists('get_user_by') )
			require_once(ABSPATH .'wp-includes/pluggable.php');

		$adminUser = get_user_by('email', get_bloginfo('admin_email')); //get_option( 'admin_email' );
		if(false === $adminUser)
			return null;

		return !empty($adminUser->display_name) ? $adminUser->display_name : null;
	}


	public static function getAdminFullName()
	{
		if(! function_exists('get_user_by') )
			require_once(ABSPATH .'wp-includes/pluggable.php');

		$adminUser = get_user_by('email', get_bloginfo('admin_email')); //get_option( 'admin_email' );
		if(false === $adminUser)
			return null;

		$adminFullName  = empty($adminUser->first_name) ? '' : $adminUser->first_name;
		$adminFullName .= empty($adminUser->last_name)  ? '' : ' ' . $adminUser->last_name;

		return trim($adminFullName);

	}


	public static function isPluginNetworkActivated($pluginFilePath)
	{
		if(!self::isMultiSite())
			return false;

		function_exists( 'is_plugin_active_for_network' ) || require_once( ABSPATH . '/wp-admin/includes/plugin.php' );

		return  !empty($pluginFilePath) ? is_plugin_active_for_network(plugin_basename($pluginFilePath)) : false;
	}

	public static function isPermaLinkActivated()
	{
		return (bool)(get_option('permalink_structure'));
	}


	public static function getAjaxUrl()
	{
		$ajaxUrl = admin_url('admin-ajax.php', self::isSslRequest() ? 'admin' : 'http');

		if(0 === strpos(self::getCurrentPageUrl(), 'https') && 0 !== strpos($ajaxUrl, 'https'))
			return  str_replace('http:', 'https:', $ajaxUrl);

		if(0 === strpos(self::getCurrentPageUrl(), 'http:') && 0 !== strpos($ajaxUrl, 'http:'))
			return str_replace('https:', 'http:', $ajaxUrl);

		return $ajaxUrl;
	}

	public static function isSslRequest()
	{
		static $isSsl = null;
		if(null !== $isSsl)
			return $isSsl;

		if (isset($_SERVER['HTTP_CF_VISITOR']) && false !== strpos($_SERVER['HTTP_CF_VISITOR'], 'https'))
			return $isSsl = true;

		if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && stripos($_SERVER['HTTP_X_FORWARDED_PROTO'], 'https') === 0)
			return $isSsl = true;

//		if (isset($_SERVER['SERVER_PORT']) && ($_SERVER['SERVER_PORT'] == 443)) # wp is_ssl() function is looking for port 443 as well
//			return $isSsl = true;

		if(isset($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on')
			return $isSsl = true;

		if(stripos(get_option('siteurl'), 'https') === 0)
			return $isSsl = true;

		return $isSsl = is_ssl();
	}

	public static function getCurrentPageUrl()
	{
		static $pageUrl = null;

		if(null !== $pageUrl)
			return $pageUrl;

//		if(is_front_page())
//			return $pageUrl = home_url('/', self::isSslRequest());

		$pageUrl = self::isSslRequest() ? 'https://' : 'http://';

		if(isset($_SERVER['SERVER_PORT']) && ($_SERVER['SERVER_PORT'] != 80))
			$pageUrl .= $_SERVER['SERVER_NAME' ]. ':' . $_SERVER['SERVER_PORT'] . $_SERVER['REQUEST_URI'];
		else
			$pageUrl .= $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];

		return $pageUrl = esc_url($pageUrl);

	}

	public static function getCurrentBlogLink()
	{
		return '<a href = "'. esc_url(get_bloginfo('url')) .'">' . get_bloginfo('name') . '</a>';
	}

	public static function getCurrentBlogName()
	{
		return get_bloginfo('name');
	}

	public static function getCurrentBlogId()
	{
		return get_current_blog_id();
	}

	public static function getAllBlogIds()
	{
		global $wpdb;

		if( empty($wpdb->blogs) )
			return array();

		return false === ( $arrBlogs = $wpdb->get_col(  "SELECT blog_id FROM $wpdb->blogs WHERE archived = '0' AND spam = '0' AND deleted = '0'" ) ) ? array() : $arrBlogs;

	}


	public static function getDirectoryPathForCache()
	{
		$arrPossibleDirectoryPath = array(
			//dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . '_temp',
			WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'cache',
			WP_CONTENT_DIR,
		);

		$arrUploadDirInfo = wp_upload_dir();
		if(MchWpUtils::isMultiSite()){
			switch_to_blog( 1 );
			$arrUploadDirInfo = wp_upload_dir();
			restore_current_blog();
		}

		(empty($arrUploadDirInfo['error']) && !empty($arrUploadDirInfo['basedir']))
		? $arrPossibleDirectoryPath[] = $arrUploadDirInfo['basedir'] : null;

		defined('WP_TEMP_DIR') ? $arrPossibleDirectoryPath[] = WP_TEMP_DIR : null;

		foreach($arrPossibleDirectoryPath as $directoryPath)
		{
			$tempDirPath = rtrim($directoryPath, '/\\');
			if(self::isDirectoryUsable($tempDirPath, false) )
				return $tempDirPath;
		}

		return null;
	}


	public static function getCurrentUserId()
	{
		return \get_current_user_id();
	}


	public static function getPageUrl($pageKey, $escaped = true)
	{
		return self::getPostUrl($pageKey, $escaped);
	}

	public static function getPostUrl($postKey, $escaped = true)
	{
		return $escaped ?  esc_url(\get_permalink($postKey)) : \get_permalink($postKey);
	}

	public static function getCommentUrl($commentKey, array $additionalArguments = array())
	{
		return MchWpUtils::isWPComment($wpCommentObject = \get_comment($commentKey)) ?  \get_comment_link($wpCommentObject, $additionalArguments) : null;
	}


	public static function getCommentType($commentKey) // for wp default comments this will return 'comment' as type. In DB the field is empty
	{
		return MchWpUtils::isWPComment($wpCommentObject = \get_comment($commentKey)) ?  \get_comment_type($wpCommentObject) : null;
	}


	public static function getCommentStatus($commentKey) // null|string) Status might be 'trash', 'approved', 'unapproved', 'spam'
	{
		return empty($commentStatus =  \wp_get_comment_status($commentKey)) ? null : $commentStatus;
	}

	public static function isPublicComment($commentKey)
	{
		return 'approved' === self::getCommentStatus($commentKey);
	}


	public static function getPostFormat($postId)
	{
		return empty($postFormat = get_post_format($postId)) ? 'standard' : $postFormat; /* aside, chat. gallery, link, image, quote, status, video, audio */

	}


	public static function getPostStatus($postKey)
	{
		return empty($postStatus = \get_post_status($postKey)) ? null : $postStatus; // can be: publish, future, draft, pending, private, trash, auto-draft, inherit - https://wordpress.org/support/article/post-status/
	}


	public static function isPublicPost($postKey)
	{
		return 'publish' === self::getPostStatus($postKey);
	}


	public static function sendAjaxSuccessMessage($message = null)
	{
		\is_array($message) ? wp_send_json_success($message) : wp_send_json_success(null === $message ?: array('message' => $message));
	}

	public static function sendAjaxErrorMessage($message = null, $allowHtml = false)
	{
		wp_send_json_error(null === $message ?: array('message' => $allowHtml ? $message : self::stripHtmlTags($message)));
	}


	public static function addActionHook($actionName,  $callback, $priority = 10, $numberOfArgumentsToPass = 1)
	{
		\add_action($actionName, $callback, $priority,  $numberOfArgumentsToPass);
	}

	public static function doAction($actionName, ...$args) // avoid using it
	{
		\do_action($actionName, ...$args);

		//$args = func_get_args();
		//call_user_func_array('do_action', $args);
	}

	public static function applyFilters($filterName, $value, ...$args)// avoid using it
	{
		return \apply_filters($filterName, $value, ...$args);

		//return call_user_func_array('apply_filters', func_get_args());
	}

	public static function addFilterHook($filterName, $callback, $priority = 10, $numberOfArgumentsToPass = 1)
	{
		return \add_filter($filterName, $callback, $priority,  $numberOfArgumentsToPass);
	}



	public static function isWPError($something)
	{
		return ( $something instanceof \WP_Error );
	}

	public static function isWPUser($something)
	{
		return ( $something instanceof \WP_User );
	}

	public static function isWPPost($something)
	{
		return ( $something instanceof \WP_Post );
	}

	public static function isWPComment($something)
	{
		return ( $something instanceof \WP_Comment );
	}


	public static function isAdminUser($userId, $blogId = null)
	{
		if(empty($userId) || !is_numeric($userId))
			return false;

		if( \is_super_admin($userId))
			return true;

		$wpUser = (null === $blogId || $blogId == get_current_blog_id()) ?  new \WP_User($userId): new \WP_User($userId, '', $blogId);


		//return $wpUser->exists() ? $wpUser->has_cap('administrator') : false;

		return $wpUser->exists() ? \in_array('administrator', (array)$wpUser->roles) : false;

	}


	public static function formatUrlPath($urlPathPart)
	{
		return \sanitize_title($urlPathPart);
	}

	public static function serializeData($data)
	{
//		return is_serialized($data) ? $data : \maybe_serialize($data);

		return  \maybe_serialize($data);
	}

	public static function unSerializeData($data)
	{
		return \maybe_unserialize($data);
	}

	public static function redirectToUrl($redirectUrl, $safe = false, $escapeUrl = true)
	{
		!$escapeUrl ?: $redirectUrl = esc_url($redirectUrl);
		
		($safe) ? wp_safe_redirect(($redirectUrl)) : wp_redirect(($redirectUrl));
		
		exit;
	}

	public static function redirectTo404()
	{
		global $wp_query;

		if(!empty($wp_query)){
			$wp_query->set_404();
		}

		status_header( 404 );
		get_template_part( 404 );

		exit;
	}


	public static function stripSlashes($textOrArray)
	{
		//function_exists('stripslashes_deep') || require_once( ABSPATH . WPINC . '/formatting.php' );

		return stripslashes_deep($textOrArray);
	}

//	public static function stripHtmlTags($str, $stripLineBreaks = false)
//	{
//		//function_exists('wp_strip_all_tags') || require_once( ABSPATH . WPINC . '/formatting.php' );
//
//		return wp_strip_all_tags($str, $stripLineBreaks);
//	}

	public static function stripHtmlTags($content, $removeLineBreaks = true)
	{
		return \wp_strip_all_tags($content, $removeLineBreaks);
	}

	public static function balanceHtmlTags($markup)
	{
		$domDocument = new \DOMDocument(); $domDocument->preserveWhiteSpace = TRUE; $domDocument->strictErrorChecking = FALSE;
		$domDocument->loadHTML( '<html><head><meta http-equiv="content-type" content="text/html; charset=utf-8"></head><body>' . $markup . '</body></html>');
		return \str_replace( array( '<body>', '</body>' ), '', $domDocument->saveHTML( $domDocument->getElementsByTagName( 'body' )->item( 0 ) ) );
	}

	public static function sanitizeText($strText)
	{
		//function_exists('sanitize_text_field') || require_once( ABSPATH . WPINC . '/formatting.php' );
		$strText = trim($strText);
		if(empty($strText))
			return $strText;

		return \sanitize_text_field($strText);
	}

	public static function sanitizeTextArea($strHtml, $arrAllowedTags = array())
	{
		$strHtml = self::balanceHtmlTags($strHtml);

		if(empty($arrAllowedTags))
		{
			$strHtml = self::stripHtmlTags($strHtml, false);
		}

		if(empty($arrAllowedTags) && function_exists('sanitize_textarea_field')){
			return sanitize_textarea_field($strHtml);
		}

		//function_exists('wp_kses') || require_once( ABSPATH . WPINC . '/kses.php' );

		return \wp_kses( $strHtml, $arrAllowedTags );
	}


	public static function sanitizeEmail($strText)
	{
		//function_exists('sanitize_email') || require_once( ABSPATH . WPINC . '/formatting.php' );

		return \sanitize_email($strText);
	}

	public static function sanitizeUserName($strUserName, $strict = false)
	{
		//function_exists('sanitize_user') || require_once( ABSPATH . WPINC . '/formatting.php' );

		return \sanitize_user($strUserName, $strict);
		
		//return \str_replace(' ', '', \sanitize_user($strUserName, $strict));
		
	}

	public static function sanitizeFileName($strFileName)
	{
		//function_exists('sanitize_file_name') || require_once( ABSPATH . WPINC . '/formatting.php' );

		return \sanitize_file_name($strFileName);
	}

	public static function sanitizeKey($strKeyValue)
	{
		//function_exists('sanitize_key') || require_once( ABSPATH . WPINC . '/formatting.php' );

		return \sanitize_key($strKeyValue);
	}

//	public static function getEscapedTranslatedText($text, $domain)
//	{
//		return esc_html( translate( $text, $domain ) );
//	}
//
//	public static function echoEscapedTranslatedText($text, $domain)
//	{
//		echo esc_html( translate( $text, $domain ) );
//	}

	public static function sendNoCacheHeaders()
	{
		\headers_sent() ?: \nocache_headers();
	}

	public static function isHookRegistered($hookName, $callBackFunction = null)
	{
		return (false !== \has_filter($hookName, (null === $callBackFunction) ? false : $callBackFunction));
	}

	public static function installedVersionIs( $operator, $versionToCompareWith)
	{
		global $wp_version;
		list( $formattedWPVersion ) = \explode( '-', $wp_version );

		return \version_compare($formattedWPVersion, $versionToCompareWith, $operator );
	}


	public static function getAllUserRoles()
	{
		global $wp_roles;
		isset( $wp_roles ) ?: $wp_roles = new \WP_Roles();

		$arrEditableRoles = array_reverse( (array)apply_filters('editable_roles', $wp_roles->roles ) );

		foreach($arrEditableRoles as $roleKey => &$arrRoleInfo){
			$arrRoleInfo = translate_user_role($arrRoleInfo['name'] );
		}

		return 	$arrEditableRoles;
	}

	public static function getThemeSupportedPostFormats()
	{
		$arrPostFormats = get_theme_support( 'post-formats' );
		if(empty($arrPostFormats[0]) || !is_array($arrPostFormats[0]))
			return array();

		return $arrPostFormats[0];
	}

	public static function getShortCodesInfo($strContent, array $arrShortCodeTags)
	{

		$arrShortCodes = array(
			'detected' => array(),
			'attributes' => array(),
			'content' => array()
		);

		if(!isset($strContent[0]))
			return $arrShortCodes;

		preg_match_all( '/'. get_shortcode_regex( $arrShortCodeTags ) .'/s', \stripslashes($strContent), $matches );


		if(empty($matches[2]))
			return $arrShortCodes;

		$arrShortCodes['detected'] = $matches[2];

		if(!empty($matches[3]))
		{
			foreach ( (array) $matches[3] as $shortCodeAttrs ) {
				$shortCodeAttrs                = trim( $shortCodeAttrs );
				$arrShortCodes['attributes'][] = empty( $shortCodeAttrs ) ? array() : (array) shortcode_parse_atts( $shortCodeAttrs );
			}
		}

		if(!empty($matches[5])){
			$arrShortCodes['content'] = $matches[5];
		}

		return $arrShortCodes;

	}


	public static function getSiteCurrentTimestamp()
	{
		return time();

//		static $currentTimeStamp = null;
//		return (null !== $currentTimeStamp) ? $currentTimeStamp : $currentTimeStamp = (int)current_time( 'timestamp' );
	}

	public static function getSiteCurrentDateTime($mySqlFormat = 'Y-m-d H:i:s', $asDateTimeObject = false)
	{
		return current_time('mysql', 0);
		
		$timezone =  wp_timezone();

		$dateTimeObject = new \DateTime('now', $timezone );

		return  $asDateTimeObject ? $dateTimeObject : $dateTimeObject->format($mySqlFormat);

	}

	public static function getSiteDateTimeFromTimestamp($unixTimestamp, $mySqlFormat = 'Y-m-d H:i:s', $asDateTimeObject = false)
	{
		if(!MchValidator::isPositiveInteger($unixTimestamp))
			return null;

		$siteDateTime = self::getSiteCurrentDateTime($mySqlFormat, true);

		$siteDateTime->setTimestamp($unixTimestamp);

		return  $asDateTimeObject ? $siteDateTime : $siteDateTime->format($mySqlFormat);
	}


	public static function getWPEditorOutputContent($initialContent, $editorTextAreaId, array $arrEditorSettings)
	{
		return MchUtils::captureOutputBuffer(function () use($initialContent, $editorTextAreaId, $arrEditorSettings){

			$arrEditorDefaultSettings = array(
				'wpautop'          => true,  // enable rich text editor
				'media_buttons'    => true,  // enable add media button
				'textarea_name'    => $editorTextAreaId, // name
				'textarea_rows'    => '20',  // number of textarea rows
				'tabindex'         => '',    // tabindex
				'editor_css'       => '',    // extra CSS
				'editor_class'     => '', // class
				'teeny'            => false, // output minimal editor config
				'dfw'              => false, // replace fullscreen with DFW
				'tinymce'          => true,  // enable TinyMCE
				'quicktags'        => true,  // enable quicktags
				'drag_drop_upload' => true,  // enable drag-drop

				'editor_height' => 0
			);

			$arrEditorSettings =  wp_parse_args( $arrEditorSettings, $arrEditorDefaultSettings );

			wp_editor($initialContent,$editorTextAreaId, $arrEditorSettings);

		});
	}




	public static function setPostFeaturedImage($postId, $fileUrl = null, $desc = null )
	{
		// Set variables for storage, fix file filename for query strings.
//		preg_match( '/[^\?]+\.(jpe?g|jpe|gif|png)\b/i', $file, $matches );
//		if ( ! $matches ) {
//			return null;
//			//return new WP_Error( 'image_sideload_failed', __( 'Invalid image URL' ) );
//		}

		add_filter( 'https_local_ssl_verify', '__return_false' );
		add_filter( 'https_ssl_verify', '__return_false' );

		if(null === ($wpPost = get_post($postId)))
			return null;

		if(empty($fileUrl)){
			//$fileUrl = 'https://unsplash.it/200/300';
			$fileUrl = 'https://unsplash.it/' . mt_rand (600, 1400) . '/' . mt_rand (400, 800) . '/random';
		}

		$file_array = array();
		$file_array['name'] = basename( $wpPost->post_name . '.jpg' );

		// Download file to temp location.
		$file_array['tmp_name'] = download_url( $fileUrl );

		// If error storing temporarily, return the error.
		if ( is_wp_error( $file_array['tmp_name'] ) ) {
			return null;
		}

		// Do the validation and storage stuff.
		$id = media_handle_sideload( $file_array, $postId, $desc );

		// If error storing permanently, unlink.
		if ( is_wp_error( $id ) ) {
			@unlink( $file_array['tmp_name'] );
			return null;
		}


		return set_post_thumbnail( $postId, $id );

	}


	public static function applyClonedFilter($filterName, $value)
	{

		global $wp_filter;

		if ( ! isset( $wp_filter[ $filterName ] ) ) {
			return $value;
		}

		$newFilterName = $filterName . __FUNCTION__;

		$wp_filter[ $newFilterName ] = new \WP_Hook();

		foreach ($wp_filter[ $filterName ]->callbacks as $priority => $arrCallBackInfo)
		{
			if( !isset($arrCallBackInfo['function']) || !isset($arrCallBackInfo['accepted_args']) )
				continue;

			$wp_filter[ $newFilterName ]->add_filter($newFilterName, $arrCallBackInfo['function'], $priority, $arrCallBackInfo['accepted_args']);
				//add_filter($newFilterName, $arrCallBackInfo['function'], $priority, $arrCallBackInfo['accepted_args']);
		}

		$value = \call_user_func_array( 'apply_filters',  \func_get_args() );

		unset($wp_filter[$newFilterName]);

		return ('the_content' === $filterName) ? \str_replace( ']]>', ']]&gt;', $value ) : $value;

	}

	private function __construct(){}

}