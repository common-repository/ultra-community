<?php
namespace UltraCommunity\Admin\Pages;


use UltraCommunity\Controllers\ModulesController;
use UltraCommunity\MchLib\Modules\MchModulesController;
use UltraCommunity\MchLib\Plugin\MchBasePlugin;

class ExtensionsAdminPage extends BaseAdminPage
{
	public function __construct( $pageMenuTitle )
	{
		parent::__construct( $pageMenuTitle);
	}


	public function renderPageContent()
	{

		$premiumExtensionsAdminUrl = esc_attr($this->getAdminUrl());
		$premiumExtensionsText     = esc_html__('Premium Extensions', 'ultra-community');


		$extensionsListCode = null;


		foreach(array_reverse(ModulesController::getLicensedModuleNames()) as $licensedModuleName)
		{

			$extensionUrl      = 'https://ultracommunity.com/extensions/';
			$extensionThumbUrl = null;
			$extensionDescr    = null;
			$extensionName     = $licensedModuleName;


			if(!ModulesController::getModuleIdByName($licensedModuleName))
				continue;

			switch($licensedModuleName)
			{
				case ModulesController::MODULE_BBPRESS :
					$extensionName     = 'bbPress';
					$extensionThumbUrl = 'bbpress.png';
					$extensionDescr    = esc_html__('With the bbPress extension you can integrate Ultra Community with bbPress', 'ultra-community');
					break;

				case ModulesController::MODULE_USER_FOLLOWERS :
					$extensionName     = 'User Followers';
					$extensionThumbUrl = 'user-followers.png';
					$extensionDescr    = esc_html__('Increase user engagement on your website site by allowing users to follow each other', 'ultra-community');
					break;


				case ModulesController::MODULE_USER_FRIENDS :
					$extensionName     = 'User Friends';
					$extensionThumbUrl = 'user-friends.png';
					$extensionDescr    = esc_html__('Increase user engagement on your website site by allowing users to become friends', 'ultra-community');
					break;

				case ModulesController::MODULE_USER_REVIEWS :
					$extensionName     = 'User Reviews';
					$extensionThumbUrl = 'user-reviews.png';
					$extensionDescr    = esc_html__('Increase user engagement on your website site by allowing users to rate/review each other', 'ultra-community');
					break;


				case ModulesController::MODULE_SOCIAL_CONNECT :
					$extensionName     = 'Social Login';
					$extensionThumbUrl = 'social-login.png';
					$extensionDescr    = esc_html__('Allows your website users to register/login to the website using one of their favorite social website accounts', 'ultra-community');
					break;

				case ModulesController::MODULE_EXTENDED_ACTIVITY :
					$extensionName     = 'Extended Activity';
					$extensionThumbUrl = 'extended-activity.png';
					$extensionDescr    = esc_html__('Allow users to publish different post types or to embed other content on user profile and group walls', 'ultra-community');
					break;
				
				case ModulesController::MODULE_CUSTOM_TABS :
					$extensionName     = 'Custom Tabs';
					$extensionThumbUrl = 'custom-tabs.png';
					$extensionDescr    = esc_html__('Easily add custom navigation tabs to user profiles and groups profiles', 'ultra-community');
					break;
				
				case ModulesController::MODULE_SOCIAL_SHARE :
					$extensionName     = 'Social Share';
					$extensionThumbUrl = 'social-share.png';
					$extensionDescr    = esc_html__('Allow users to share profiles and activities to different social networks', 'ultra-community');
					break;
				
				case ModulesController::MODULE_POST_SUBMISSIONS :
					$extensionName     = 'User Post Submissions';
					$extensionThumbUrl = 'user-post-submissions.png';
					$extensionDescr    = esc_html__('Allows your users to submit blog posts directly from their profile page without logging into the admin area.', 'ultra-community');
					break;
				
				case ModulesController::MODULE_USER_LETTER_AVATAR :
					$extensionName     = 'User Letters Avatar';
					$extensionThumbUrl = 'user-letters-avatar.png';
					$extensionDescr    = esc_html('Enables custom initials avatars for users without any uploaded profile image');
					break;
				
				case ModulesController::MODULE_USER_NOTIFICATIONS :
					$extensionName     = 'User Notifications';
					$extensionThumbUrl = 'user-notifications.png';
					$extensionDescr    = esc_html('Adds a notification system so users can receive updates and notifications directly on your website');
					break;
					
			}
			
			
			$extensionThumbUrl = MchBasePlugin::getPluginBaseUrl() . '/assets/admin/images/' . $extensionThumbUrl;

			$extensionsListCode .= '<div class="plugin-card">';
			$extensionsListCode .= '<div class="plugin-card-top">';
			$extensionsListCode .= '<div class="name column-name">';
			$extensionsListCode .= "<h3><a href=\"{$extensionUrl}\">{$extensionName}<img class=\"plugin-icon\" src=\"{$extensionThumbUrl}\"></a></h3>";
			$extensionsListCode .= '</div>';
			$extensionsListCode .= '<div class="desc column-description">';
			$extensionsListCode .= "<p>{$extensionDescr}</p>";
			//$extensionsListCode .= "<p class=\"authors\"><cite>Category: <a href=\"{$arrExtensionInfo['category-url']}\">{$arrExtensionInfo['category-name']}</a></cite></p>";
			$extensionsListCode .= '</div>';
			$extensionsListCode .= "<div class=\"action-links\"><ul class=\"plugin-action-buttons\"><li><a href=\"{$extensionUrl}\" class=\"install-now button\">Get this Extension</a></li></ul></div>";
			$extensionsListCode .= '</div>';
			$extensionsListCode .= '</div>';

		}


		echo <<<OutputContent

<div class="wp-filter">
    <ul class="filter-links">
        <li><a href="$premiumExtensionsAdminUrl" class="current">$premiumExtensionsText</a></li>
    </ul>
</div>

<div class="wp-list-table widefat plugin-install">
    <div id="the-list">
			$extensionsListCode
    </div>
</div>


OutputContent;

	}

	public function hasRegisteredModules()
	{
		return true;
	}


	public function getPageHiddenContent()
	{
		return null;
	}

}