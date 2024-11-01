<?php

namespace UltraCommunity\FrontPages;

use UltraCommunity\Controllers\ModulesController;
use UltraCommunity\Controllers\PostTypeController;
use UltraCommunity\Controllers\TemplatesController;
use UltraCommunity\Controllers\UserController;
use UltraCommunity\Entities\UserEntity;
use UltraCommunity\MchLib\Utils\MchUtils;
use UltraCommunity\MchLib\Utils\MchWpUtils;
use UltraCommunity\Modules\Appearance\Directories\DirectoriesAppearancePublicModule;
use UltraCommunity\Modules\Appearance\UserProfile\UserProfileAppearanceAdminModule;
use UltraCommunity\Modules\Appearance\UserProfile\UserProfileAppearancePublicModule;
use UltraCommunity\Modules\Forms\BaseForm\BaseFormAdminModule;
use UltraCommunity\Modules\Forms\FormFields\BaseField;
use UltraCommunity\Modules\Forms\FormFields\UserNameField;
use UltraCommunity\Modules\MembersDirectory\MembersDirectoryAdminModule;
use UltraCommunity\Modules\MembersDirectory\MembersDirectoryPublicModule;
use UltraCommunity\Modules\UserReviews\UserReviewsPublicModule;
use UltraCommunity\Modules\UserRole\UserRolePublicModule;
use UltraCommunity\Repository\UserRepository;
use UltraCommunity\UltraCommException;
use UltraCommunity\UltraCommHelper;
use UltraCommunity\UltraCommHooks;
use UltraCommunity\UltraCommUtils;

class MembersDirectoryPage extends BasePage
{
	private $directoryPageNumber = null;
	private $directoryPublicModuleInstance = null;

	public function __construct($pageId = null, $directoryPageNumber = 1)
	{
		parent::__construct($pageId);

		$this->directoryPageNumber  = (int)$directoryPageNumber;
		!empty($this->directoryPageNumber) ?: $this->directoryPageNumber = 1;

	}

	public function processRequest()
	{}

	public function isAuthenticationRequired()
	{}

	public function getShortCodeContent($arrAttributes, $shortCodeText = null, $shortCodeName = null)
	{

		$customPostType = empty($arrAttributes['id']) ? null : PostTypeController::getPostTypeInstanceByPostId($arrAttributes['id']);

		(null !== $customPostType) ?: $customPostType = $this->getModuleCustomPostType();

		if(null === $customPostType)
		{ // fallback to default
			foreach(PostTypeController::getPublishedPosts(PostTypeController::POST_TYPE_MEMBERS_DIRECTORY) as $directoryPostType)
			{
				if(null === ($directoryPublicModuleInstance =  PostTypeController::getAssociatedPublicModuleInstance($directoryPostType)) || !$directoryPublicModuleInstance->getOption(MembersDirectoryAdminModule::OPTION_IS_DEFAULT_MEMBERS_DIRECTORY))
					continue;

				$this->directoryPublicModuleInstance = $directoryPublicModuleInstance;
				$customPostType = $directoryPostType;
				break;
			}
		}

		if(null === $customPostType)
			return null;

		$this->setModuleCustomPostType($customPostType);

		$this->directoryPublicModuleInstance = PostTypeController::getAssociatedPublicModuleInstance($customPostType);

		return TemplatesController::getTemplateOutputContent('directories/members-directory.php', $this->getTemplateArguments());
	}


	public function getContentMarkup()
	{
		return $this->getShortCodeContent(array());
	}

	private function getTemplateArguments() //$pageNumber = 1, MembersDirectoryPublicModule $directoryPublicModuleInstance = null
	{

		if(!$this->directoryPublicModuleInstance instanceof MembersDirectoryPublicModule){
			throw new UltraCommException('Invalid request for Members Directory');
		}

		$pageNumber = $this->getDirectoryPageNumber();

		$arrExcludedMemberIds        = (array)apply_filters(UltraCommHooks::FILTER_MEMBERS_DIRECTORY_EXCLUDE_MEMBER_IDS, array(), $this->directoryPublicModuleInstance);
		$arrIncludedMemberIds        = (array)apply_filters(UltraCommHooks::FILTER_MEMBERS_DIRECTORY_INCLUDE_MEMBER_IDS, array(), $this->directoryPublicModuleInstance);
		$arrQueryAdditionalArguments = (array)apply_filters(UltraCommHooks::FILTER_MEMBERS_DIRECTORY_QUERY_ARGUMENTS,    array(), $this->directoryPublicModuleInstance);

		$membersPerPage = DirectoriesAppearancePublicModule::getRecordsPerPage(DirectoriesAppearancePublicModule::DIRECTORY_TYPE_MEMBERS);

		$arrUserRoleIds = (array)$this->directoryPublicModuleInstance->getOption(MembersDirectoryAdminModule::OPTION_LIST_USER_ROLES);

		$arrMembers = UserRepository::getUsersForDirectory($pageNumber, $membersPerPage, $arrUserRoleIds, $arrExcludedMemberIds, $arrIncludedMemberIds, $arrQueryAdditionalArguments);

		$totalUsers = empty($arrMembers[UserRepository::TOTAL_FOUND_ROWS]) ? 0 : $arrMembers[UserRepository::TOTAL_FOUND_ROWS];
		unset($arrMembers[UserRepository::TOTAL_FOUND_ROWS]);

		$arrArguments = array(
			'pageNumber'           => $pageNumber,
			'totalPages'           => ceil($totalUsers / $membersPerPage),
			'paginationType'       => DirectoriesAppearancePublicModule::getPaginationType(DirectoriesAppearancePublicModule::DIRECTORY_TYPE_MEMBERS),
			'showStatsIcons'       => DirectoriesAppearancePublicModule::showStatsAsIcons(DirectoriesAppearancePublicModule::DIRECTORY_TYPE_MEMBERS),
			'showCoverImage'       => DirectoriesAppearancePublicModule::showCoverImage(DirectoriesAppearancePublicModule::DIRECTORY_TYPE_MEMBERS),
			'showStatsHighlighted' => DirectoriesAppearancePublicModule::showStatsHighlighted(DirectoriesAppearancePublicModule::DIRECTORY_TYPE_MEMBERS),
			'showSquarePicture'    => DirectoriesAppearancePublicModule::showSquarePicture(DirectoriesAppearancePublicModule::DIRECTORY_TYPE_MEMBERS),
			'showOnlineStatus'     => DirectoriesAppearancePublicModule::showMemberOnlineStatus(),
			//'showRatings'          => DirectoriesAppearancePublicModule::showMemberRatings(),
//			'showSocialNetworks'   => $showSocialNetworks,
		);

		if($arrArguments['totalPages'] < $pageNumber)
			return array();

		$arrItemsList = array();

		$showSocialNetworks = DirectoriesAppearancePublicModule::showMembersSocialNetworks();
		$showMemberRatings  = DirectoriesAppearancePublicModule::showMemberRatings() && ModulesController::isModuleRegistered(ModulesController::MODULE_USER_REVIEWS);
		if($showMemberRatings) {
			UserRepository::getUsersReviews(array_map(function($userEntity){return $userEntity->Id;}, $arrMembers));
		}

		$postsStatsText    = esc_html__('Posts', 'ultra-community');
		$commentsStatsText = esc_html__('Comments', 'ultra-community');

		foreach ($arrMembers as $userEntity)
		{
			$directoryItem = new \stdClass();

			$directoryItem->CoverUrl    = esc_url(UltraCommHelper::getUserProfileCoverUrl($userEntity));
			$directoryItem->PictureUrl  = esc_url(UltraCommHelper::getUserAvatarUrl($userEntity, 150));

			$directoryItem->HeadLineUrl = UltraCommHelper::getUserProfileUrl($userEntity);
			$directoryItem->HeadLine    = esc_html(UltraCommHelper::getUserDisplayName($userEntity));

			$directoryItem->IsOnline       = UserController::isUserOnline($userEntity);
			$directoryItem->RatingsOutput  = ($showMemberRatings ? UserReviewsPublicModule::getUserRatingsOutputContent($userEntity) : null);
			$directoryItem->SocialNetworks = array();
			$directoryItem->Stats          = array();

			if($showSocialNetworks)
			{
				foreach (UltraCommHelper::getUserSocialNetworksProfileFields($userEntity) as $socialNetworkField)
				{
					$socialNetwork = new \stdClass();
					$socialNetwork->Icon = $socialNetworkField->getFontAwesomeClass($socialNetworkField->NetworkId);
					if(empty($socialNetworkField->Value)) continue;
					$socialNetwork->Url  = esc_url($socialNetworkField->Value);
					$directoryItem->SocialNetworks[] = $socialNetwork;
				}
			}

			foreach ((array)$this->directoryPublicModuleInstance->getOption(MembersDirectoryAdminModule::OPTION_USER_ACTIVITY_STATS_COUNTERS) as $statsKey)
			{
				$userStats = new \stdClass();
				switch ($statsKey)
				{
					case MembersDirectoryAdminModule::USER_STATS_COUNTER_POSTS :
						$userStats->Text = $postsStatsText;
						$userStats->Icon = 'fa-pencil';
						$userStats->Url  = UltraCommHelper::getUserProfileUrl($userEntity, UserProfileAppearanceAdminModule::PROFILE_SECTION_POSTS);
						break;

					case MembersDirectoryAdminModule::USER_STATS_COUNTER_COMMENTS :
						$userStats->Text = $commentsStatsText;
						$userStats->Icon = 'fa-commenting';
						$userStats->Url  = UltraCommHelper::getUserProfileUrl($userEntity, UserProfileAppearanceAdminModule::PROFILE_SECTION_COMMENTS);
						break;
				}

				if(empty($userStats->Text))
					continue;

				$userStats->Number = UserController::getUserStats($userEntity, $statsKey);

				$directoryItem->Stats[] = $userStats;
			}


			$arrMainTagLineFields  = $this->getTagLineFields($userEntity,  true);
			$arrBelowTagLineFields = $this->getTagLineFields($userEntity,  false);


			$directoryItem->MainTagLineItems   = array();
			$directoryItem->SecondTagLineItems = array();

			foreach($arrMainTagLineFields as $tagLineFieldValue)
			{
				$tagLineItem = new \stdClass();
				$tagLineItem->Text = $tagLineFieldValue;

				$directoryItem->MainTagLineItems[] = $tagLineItem;
			}

			foreach($arrBelowTagLineFields as $tagLineFieldValue)
			{
				$tagLineItem = new \stdClass();
				$tagLineItem->Text = $tagLineFieldValue;

				$directoryItem->SecondTagLineItems[] = $tagLineItem;
			}


			$directoryItem->Actions = array();


			$arrItemsList[] = $directoryItem;
		}

		$arrArguments['directoryItems'] = $arrItemsList;

		return array('arrMembersDirectory' => $arrArguments);
	}


	private function getTagLineFields(UserEntity $userEntity, $isPrimary = true)
	{
		$arrFields = (array)$this->directoryPublicModuleInstance->getOption( $isPrimary ? MembersDirectoryAdminModule::OPTION_USER_CARD_TAGLINE_FIELDS : MembersDirectoryAdminModule::OPTION_USER_CARD_BELOW_TAGLINE_FIELDS );
		if(empty($arrFields))
			return array();

		$arrProfileFormFields = UserController::getUserProfileFormFields($userEntity);

		$arrInfoToRender = array();
		foreach($arrFields as $fieldToDisplay)
		{
			$fieldInstance = null;
			if(isset($arrProfileFormFields[$fieldToDisplay]))
			{
				$fieldInstance = $arrProfileFormFields[$fieldToDisplay];
			}
			elseif( ! MchUtils::isNullOrEmpty($fieldInstance = BaseFormAdminModule::getFieldInstanceByShortClassName($fieldToDisplay)) )
			{
				foreach ($arrProfileFormFields as $fieldId => $profileFormField)
				{
					if( !is_a($profileFormField, get_class($fieldInstance)) )
						continue;

					$fieldInstance = $profileFormField;
					break;
				}
			}

			if(! ($fieldInstance instanceof BaseField) )
				continue;

			$fieldInstance->Value = UserController::getUserProfileFormFieldValue($userEntity, $fieldInstance, false);

			if(empty($fieldInstance->Value))
				continue;

			if($isPrimary && ($fieldInstance instanceof UserNameField))
			{
				$fieldInstance->Value = '@' . $userEntity->NiceName;
			}

			$fieldInstance->Value = wp_kses_decode_entities(html_entity_decode(htmlentities(wp_specialchars_decode(\stripslashes($fieldInstance->Value), \ENT_QUOTES))));

			$fieldInstance->Value = \wp_trim_words($fieldInstance->Value, 25, '');


			$arrInfoToRender[] = $fieldInstance->Value;

		}

		return $arrInfoToRender;
	}



	public function getDirectoryPageNumber()
	{
		return $this->directoryPageNumber;
	}

	public function setDirectoryPageNumber($pageNumber)
	{
		$this->directoryPageNumber = (int)$pageNumber;
	}


	public function getHeaderMarkup()
	{
		return null;
	}

	public function getNavBarMarkup()
	{
		//return '<h1>Nav Bar here</h1>';
	}

	public function getSideBarMarkup()
	{
		return null;
	}

	public function getPageCustomCss()
	{
		return parent::getPageCustomCss();
	}

	public function getSubMenuTemplateArguments()
	{
		return array();
	}

}