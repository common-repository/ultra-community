<?php
use UltraCommunity\Controllers\FrontPageController;
use UltraCommunity\Controllers\GroupController;
use UltraCommunity\Controllers\UserController;
use UltraCommunity\Entities\GroupEntity;
use UltraCommunity\Entities\GroupUserEntity;
use UltraCommunity\FrontPages\UserSettingsPage;
use UltraCommunity\MchLib\Utils\MchWpUtils;
use UltraCommunity\Modules\Appearance\GroupProfile\GroupProfileAppearanceAdminModule;
use UltraCommunity\Modules\Appearance\GroupProfile\GroupProfileAppearancePublicModule;
use UltraCommunity\Modules\Appearance\UserProfile\UserProfileAppearanceAdminModule;
use UltraCommunity\Modules\Appearance\UserProfile\UserProfileAppearancePublicModule;
use UltraCommunity\UltraCommHelper;
use UltraCommunity\UltraCommHooks;

$profiledGroupEntity = GroupController::getProfiledGroup();
$loggedInUserEntity  = UserController::getLoggedInUser();

(null !== $profiledGroupEntity) || exit;

$groupAvatarUrl  = UltraCommHelper::getGroupPictureUrl($profiledGroupEntity);
$groupProfileUrl = UltraCommHelper::getGroupUrl($profiledGroupEntity);
$userCanSeeGroupName         = GroupController::userCanSeeGroupName($loggedInUserEntity, $profiledGroupEntity);
$userCanSeeGroupDescription  = GroupController::userCanSeeGroupDescription($loggedInUserEntity, $profiledGroupEntity);

$groupActiveMembers = (int)GroupController::countGroupUsers($profiledGroupEntity, GroupUserEntity::GROUP_USER_STATUS_ACTIVE);
$groupApprovedPosts = (int)12;

$userAvatarUrl   = UltraCommHelper::getUserAvatarUrl($loggedInUserEntity);
($userHasAvatar  = ( null !== $userAvatarUrl)) ?: $userAvatarUrl = UltraCommHelper::getUserDefaultAvatarUrl();
$userDisplayName = esc_html(UltraCommHelper::getUserDisplayName($loggedInUserEntity));

?>

<div class="uch uc-box-sizing-border-box">

	<div class="uc-group-profile-container uc-box-sizing-border-box">
		<div class="uc-g">

            <div class="uc-u-1-1" style="position: relative">
                <div class=" uc-flex-box-with-bg uc-group-cover-holder"></div>
            </div>

            <div class="uc-u-1 uc-u-lg-1-4 uc-group-left-sidebar">

                <div class="uc-group-card-holder uc-box-holder uc-box-sizing-border-box">

                    <div class="uc-g">
                        <div class="uc-u-1-1">
                            <div class="uc-group-card-avatar-wrapper">
                                <div class="uc-group-card-avatar-holder">
                                    <img class="uc-img" src="<?php echo $groupAvatarUrl;?>">
                                </div>
                            </div>

                        </div>
                    </div>


                    <div class="uc-u-1-1 uc-group-card-main-info">
                        <?php
                            if($userCanSeeGroupName)
                            {
	                            echo '<h1><a href="' . $groupProfileUrl . '">' . esc_html($profiledGroupEntity->Name) . '</a></h1>';
                            }
                        ?>

                        <div class="uc-u-1-1">
                            <ul class="uc-group-card-fields-holder uc-clear">
                                    <li>
                                        <span class="uc-group-card-field-value uc-text-primary-color uc-group-card-field-group-type">
                                            <?php
                                                echo '@ ', strtolower(GroupEntity::getGroupTypeDescription($profiledGroupEntity->GroupTypeId)), ' ' , esc_html__('group', 'ultra-community');
                                             ?>
                                        </span>
                                    </li>
                            </ul>
                        </div>


                        <?php

                            $joinGroupButtonClass = null;

                            switch (GroupController::getGroupUserStatusId($profiledGroupEntity, $loggedInUserEntity))
                            {
                                case GroupUserEntity::GROUP_USER_STATUS_ACTIVE :
                                    if(!GroupController::isUserGroupAdmin($loggedInUserEntity, $profiledGroupEntity)){
                                        $joinGroupButtonClass = 'uc-button-leave-group';
                                    }
                                    break;

                                case GroupUserEntity::GROUP_USER_STATUS_PENDING :
                                    $joinGroupButtonClass = 'uc-button-pending-group';
                                    break;

                                default :
		                            $joinGroupButtonClass = 'uc-button-join-group';
		                            break;

                            }

                            if(!empty($joinGroupButtonClass))
                            {
                                echo '<div class="uc-u-1">';

                                    echo '<button class="uc-button uc-button-action ' . sanitize_html_class($joinGroupButtonClass) . '" data-group-id = "'.esc_attr($profiledGroupEntity->Id).'">';
                                        switch ($joinGroupButtonClass)
                                        {
                                            case 'uc-button-join-group' :
                                                echo '<i class="fa fa-plus"></i> <span>'. esc_html__('join group', 'ultra-community') . '</span>';break;

	                                        case 'uc-button-leave-group' :
		                                        echo '<i class="fa fa-external-link"></i> <span>'. esc_html__('leave group', 'ultra-community') . '</span>';break;

	                                        case 'uc-button-pending-group' :
		                                        echo '<i class="fa fa-eye"></i> <span>'. esc_html__('pending request', 'ultra-community') . '</span>';break;

                                        }
                                    echo '</button>';

                                    echo '<div class="uc-ajax-loader"><div class="bounce-left"></div><div class="bounce-middle"></div><div class="bounce-right"></div></div>';

	                            echo '</div>';
                            }

                        ?>

                        <?php
                            if($userCanSeeGroupDescription)
                            {
                                echo '<div class="uc-u-1" style="margin-bottom: 0;"><div class="uc-section-divider"></div></div>';
                                echo '<div class="uc-u-1">';
                                    echo '<p class="uc-text-primary-color uc-text-center uc-group-card-description">',
                                            esc_html($profiledGroupEntity->Description),
                                          '</p>';
	                            echo '</div>';
                            }
                        ?>

                        <hr class="uc-divider-gradient" />
                        <div class="uc-g uc-group-card-stats-holder">
                            <div class="uc-u-1-2"><span class="uc-group-card-stats-counter-value"><?php echo $groupActiveMembers; ?></span><br />
                                <span class="uc-group-card-stats-counter-name">
                                    <?php echo (1 === $groupActiveMembers) ? esc_html__('member', 'ultra-community') : esc_html__('members', 'ultra-community'); ?>
                                </span>
                            </div>
                            <div class="uc-u-1-2"><span class="uc-group-card-stats-counter-value"><?php echo $groupApprovedPosts; ?></span><br />
                                <span class="uc-group-card-stats-counter-name">
                                    <?php echo (1 === $groupApprovedPosts) ? esc_html__('group post', 'ultra-community') : esc_html__('group posts', 'ultra-community'); ?>
                                </span>
                            </div>
                        </div>

                    </div>

                </div>





            </div>

            <!-- Content starts here -->
            <div class="uc-u-1 uc-u-lg-3-4 uc-group-content-holder">




	            <?php

	            if(null !== ($arrProfileSections = (array)GroupProfileAppearancePublicModule::getInstance()->getOption(GroupProfileAppearanceAdminModule::OPTION_GROUP_MENU_SECTIONS)))
	            {
		            echo '<div class="uc-menu uc-menu-horizontal uc-profile-navigation-holder uc-box-sizing-border-box uc-clear">',
		            '<div class="uc-mobile-navigation-holder">
                                        <a href="#" class="uc-mobile-menu-toggle"><span class="fa fa-navicon"></span></a>
                                    </div>',
		            '<ul class="uc-menu-list uc-profile-main-navigation">';
		            foreach ($arrProfileSections as $profileSectionSlug)
		            {

                        echo '<li class="uc-menu-item' . ( $profileSectionSlug === $this->getActiveSectionSlug() ? ' active-section">' : '">');
                            echo '<a href="' . UltraCommHelper::getGroupUrl($profiledGroupEntity, $profileSectionSlug) . '" class="uc-menu-link uc-hvr-overline-from-center">';
                                echo '<i class="fa ' . GroupProfileAppearancePublicModule::getGroupProfileSectionFontAwesomeIcon($profileSectionSlug) . '"></i>';
                                echo '<span>', GroupProfileAppearancePublicModule::getGroupProfileSectionNameBySlug($profileSectionSlug), '</span>';
                            echo '</a>';
                        echo '</li>';
                    }

		            echo '</ul>';

		            if(GroupController::userCanEditGroup($loggedInUserEntity, $profiledGroupEntity))
		            {
			            $logOutPageUrl   = esc_url(FrontPageController::getLogOutPageUrl());

			            $groupAdminUserEntity = UserController::getUserEntityBy(GroupController::getGroupAdminUserId($profiledGroupEntity));


			            $editGroupUrl   = FrontPageController::getUserSettingsPageUrlBySection(UserSettingsPage::SETTINGS_SECTION_EDIT_GROUP, $groupAdminUserEntity->NiceName, $profiledGroupEntity->Id, true);


			            $settingsPageUrl = empty($editGroupUrl) ? '' : $editGroupUrl;


			            echo '<div class="uc-menu uc-loggedin-user-actions-holder" style="height:100%">

                                <div class="uc-user-actions-toggle uc-menu-heading uc-menu-link uc-vertical-align-middle">

                                    <ul class="uc-user-actions-box-info">
                                        <li>
                                            <img class="uc-img" src="'.$userAvatarUrl.'">
                                        </li>
                                        <li>
                                            <span class="">'.$userDisplayName.'</span>
                                        </li>
                                        <li>
                                            <a><i class="fa fa-caret-square-o-down"></i></a>
                                        </li>
                                    </ul>

                                    <ul class="uc-menu-list uc-user-actions-dropdown uc-clear">
                                        <li class="uc-menu-item">
                                            <a href="'. $settingsPageUrl .'" class="uc-menu-link uc-hvr-underline-from-center">
                                                <i class="fa fa-cog uc-float-left"></i><span class="uc-float-left">', esc_html__('Edit Group Settings', 'ultra-community'), '</span>
                                            </a>
                                        </li>
                                        <li class="uc-menu-item">
                                            <a href="'. $logOutPageUrl .'" class="uc-menu-link uc-hvr-underline-from-center ">
                                                <i class="fa fa-power-off uc-float-left"></i><span class="uc-float-left">', esc_html__('Log out', 'ultra-community'), '</span>
                                            </a>
                                        </li>
                                    </ul>

                                </div>
                            </div>';
		            }

		            echo '</div>';

	            }
	            ?>

                <div class="uc-g">
                    <div class="uc-u-1">
                        <div class="uc-user-group-section-content-holder">

                            <?php \do_action(UltraCommHooks::ACTION_GROUP_PROFILE_SECTION_RENDER_CONTENT, $this->getActiveSectionSlug());?>

                        </div>
                    </div>
                </div>

            </div>
            <!-- END Content -->


		</div>

	</div>

</div>