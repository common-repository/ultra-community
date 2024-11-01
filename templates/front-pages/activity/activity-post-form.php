<?php
use UltraCommunity\Controllers\FrontPageController;
use UltraCommunity\Controllers\UserController;
use UltraCommunity\Repository\ActivityRepository;
use UltraCommunity\UltraCommHelper;
use UltraCommunity\UltraCommHooks;

!empty($activityTargetType) ?: $activityTargetType = 'site';
!empty($formDisplayHint)    ?: $formDisplayHint    = '';

$userAvatarUrl = UltraCommHelper::getUserAvatarUrl(UserController::getLoggedInUser());

?>

<div class="uc-grid uc-grid--full uc-panel uc-activity-post-form-holder">

        <?php do_action(UltraCommHooks::ACTION_ACTIVITY_BEFORE_NEW_POST_FORM, $activityTargetType); ?>


	<form class="uc-form uc-grid-cell uc-grid-cell--center uc-grid uc-grid--fit uc-panel-content uc-activity-post-form" method="post">

		<input type="hidden" name="activityPostTarget" id = "uc-activity-post-target" value = "<?php echo $activityTargetType;?>"/>

		<div class="uc-form-body uc-grid uc-grid--center uc-grid--full uc-grid-medium--fit uc-grid--flex-cells ">

			<label for = "txtActivityPostContent" class="uc-grid-cell uc-grid-cell--top uc-grid-cell--autoSize uc-bg-holder uc-user-avatar-holder" style="background-image: url(<?php echo $userAvatarUrl;?>)"></label>

			<div class="uc-grid-cell uc-grid-cell--top" style="">
				<textarea name = "txtActivityPostContent" id = "txtActivityPostContent" class="uc-input-1" type="textarea" placeholder="<?php echo $formDisplayHint; ?>"></textarea>
			</div>

		</div>


        <div class="uc-grid uc-grid--center uc-grid--justify-center uc-grid--flex-cells uc-activity-uploads-holder"></div>


        <div class="uc-grid uc-grid--full uc-activity-quote-fields-holder">

<!--            <div class="uc-form-grouped-controls uc-grid-cell"></div>-->

            <div class="uc-form-grouped-controls uc-grid-cell">
                <div class="uc-form-item"><i class="fa fa-user"></i></div>
                <input name="txtQuoteAuthor" placeholder="Add Quote Author" value="" class="uc-input-1" type="text">
            </div>

            <div class="uc-form-grouped-controls uc-grid-cell">
                <div class="uc-form-item uc-form-item-icon-top"><i class="fa fa-quote-left"></i></div>
                <textarea  name = "txtQuoteText" placeholder="Add Quote Text" class="uc-input-1" type="textarea"></textarea>
            </div>

        </div>



        <div class="uc-grid uc-grid--full uc-activity-link-fields-holder">

<!--            <div class="uc-form-grouped-controls uc-grid-cell"></div>-->

            <div class="uc-form-grouped-controls uc-grid-cell">
                <div class="uc-form-item"><i class="fa fa-link"></i></div>
                <input name="txtLinkUrl" placeholder="Add Link Url" value="" class="uc-input-1" type="text">
            </div>

            <div class="uc-form-grouped-controls uc-grid-cell">
                <div class="uc-form-item"><i class="fa fa-header"></i></div>
                <input name="txtLinkTitle" placeholder="Add Link Title" value="" class="uc-input-1" type="text">
            </div>

            <div class="uc-form-grouped-controls uc-grid-cell">
                <div class="uc-form-item uc-form-item-icon-top" ><i class="fa fa-file-text"></i></div>
                <textarea  name = "txtLinkDescription" placeholder="Add Brief Description" class="uc-input-1" type="textarea"></textarea>
            </div>

        </div>


		<div class="uc-form-footer">

			<div class="uc-grid uc-grid--flex-cells uc-grid--full uc-grid-medium--fit">

				<div class="uc-grid-cell uc-grid-cell--autoSize">
					<label for="uc-activity-fileupload" class="uc-button uc-button-action uc-button-action-transparent">
						<input id="uc-activity-fileupload" name="ucTempFile" type="file" style="display: none">
						<i class="fa fa-upload" style="margin-right: 5px"></i><?php  esc_html_e('Upload', 'ultra-community'); ?>
					</label>
				</div>

				<div class="uc-grid-cell uc-notice-holder"></div>

                <div class="uc-grid-cell uc-grid-cell--autoSize uc-grid-cell--center"><div class="uc-ajax-loader uc-hidden"><div class="bounce-left"></div><div class="bounce-middle"></div><div class="bounce-right"></div></div></div>

                <div class="uc-grid-cell uc-grid-cell--autoSize">
					<button class="uc-button uc-button-primary uc-button-add-activity-post" data-uc-trigger-login="submitNewActivityPost">
						<span><?php  esc_html_e('Post', 'ultra-community'); ?></span>
					</button>
				</div>


			</div>

		</div>


	</form>

</div>

