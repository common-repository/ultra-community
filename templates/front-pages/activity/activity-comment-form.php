<?php
use UltraCommunity\Controllers\FrontPageController;
use UltraCommunity\Repository\ActivityRepository;
use UltraCommunity\UltraCommHooks;

/** @var $activityEntity UltraCommunity\Entities\ActivityEntity */
if(empty($activityEntity->ActivityId))
	return;

?>


<ul class="uc-activity-comments-holder">

	<li>

		<div class = "uc-grid uc-grid--center uc-grid--fit uc-grid--flex-cells">

			<div class="uc-grid-cell uc-grid-cell--autoSize uc-grid-cell--top" style="">
				<div class="uc-grid-cell-image-holder">
					<a  href="" class="uc-grid-cell-image" style="background-image: url(https://staging1.demo.ultracommunity.com/wp-content/uploads/ultra-comm/uploads/15/avatar/photo-1477814670986-8d8dccc5640d-1.jpeg)"></a>
				</div>
			</div>

			<div class="uc-grid-cell uc-grid-cell--top uc-grid">

				<div class="uc-grid uc-grid--fit uc-grid--flex-cells uc-grid--center" style="height: 42px">

					<div class="uc-grid-cell uc-grid-cell--autoSize">
						<p class="uc-text-primary-color">
							<a class="uc-text-primary-color" href=""> Mihai Chelaru </a>
						</p>
					</div>
					<div class="uc-grid-cell"></div>
					<div class="uc-grid-celluc-grid-cell--autoSize">
						<p class="uc-text-primary-color uc-activity-date"><i class="fa fa-clock-o"></i>few minutes ago</p>
					</div>

				</div>

				<div class="uc-grid uc-grid--center uc-grid--full">
					<div class="uc-grid-cell">
						<p style="line-height: 1.5em;">Duis cursus est at orci sodales, ac placerat sapien eleifend. Donec pharetra fringilla tincidunt. Praesent dignissim aliquam nunc, Etiam pharetra vel enim a vestibulum. sit amet accumsan libero. Nullam faucibus orci id mi tempus iaculis. Proin tristique ut tincidunt enim.</p>
					</div>
				</div>


				<div class="uc-grid uc-grid--center uc-grid--fit uc-comment-actions">
					<div class="uc-grid-cell">
						<p><a href="#"><i class="fa fa-reply" aria-hidden="true"></i>Reply</a></p>
					</div>
				</div>

			</div>

		</div>

		<ul>
			<li>

				<div class = "uc-grid uc-grid--center uc-grid--fit uc-grid--flex-cells">

					<div class="uc-grid-cell uc-grid-cell--autoSize uc-grid-cell--top" style="">
						<div class="uc-grid-cell-image-holder">
							<a  href="" class="uc-grid-cell-image" style="background-image: url(https://staging1.demo.ultracommunity.com/wp-content/uploads/ultra-comm/uploads/15/avatar/photo-1477814670986-8d8dccc5640d-1.jpeg)"></a>
						</div>
					</div>
					<div class="uc-grid-cell uc-grid-cell--top uc-grid">

						<div class="uc-grid uc-grid--fit uc-grid--flex-cells uc-grid--center" style="height: 42px">

							<div class="uc-grid-cell uc-grid-cell--autoSize">
								<p class="uc-text-primary-color">
									<a class="uc-text-primary-color" href=""> Mihai Chelaru </a>
								</p>
							</div>
							<div class="uc-grid-cell"></div>
							<div class="uc-grid-celluc-grid-cell--autoSize">
								<p class="uc-text-primary-color uc-activity-date"><i class="fa fa-clock-o"></i>few minutes ago</p>
							</div>

						</div>

						<div class="uc-grid uc-grid--center uc-grid--full">
							<div class="uc-grid-cell">
								<p style="line-height: 1.5em;">Duis cursus est at orci sodales, ac placerat sapien eleifend. Donec pharetra fringilla tincidunt. Praesent dignissim aliquam nunc, Etiam pharetra vel enim a vestibulum. sit amet accumsan libero. Nullam faucibus orci id mi tempus iaculis. Proin tristique ut tincidunt enim.</p>
							</div>
						</div>


						<div class="uc-grid uc-grid--center uc-grid--fit uc-comment-actions">
							<div class="uc-grid-cell">
								<a href="#"><i class="fa fa-reply" aria-hidden="true"></i>Reply</a>
							</div>
						</div>

					</div>

				</div>

			</li>
			<li>

				<div class = "uc-grid uc-grid--center uc-grid--fit uc-grid--flex-cells">

					<div class="uc-grid-cell uc-grid-cell--autoSize uc-grid-cell--top" style="">
						<div class="uc-grid-cell-image-holder">
							<a  href="" class="uc-grid-cell-image" style="background-image: url(https://staging1.demo.ultracommunity.com/wp-content/uploads/ultra-comm/uploads/15/avatar/photo-1477814670986-8d8dccc5640d-1.jpeg)"></a>
						</div>
					</div>
					<div class="uc-grid-cell uc-grid-cell--top uc-grid">

						<div class="uc-grid uc-grid--fit uc-grid--flex-cells uc-grid--center" style="height: 42px">

							<div class="uc-grid-cell uc-grid-cell--autoSize">
								<p class="uc-text-primary-color">
									<a class="uc-text-primary-color" href=""> Mihai Chelaru </a>
								</p>
							</div>
							<div class="uc-grid-cell"></div>
							<div class="uc-grid-celluc-grid-cell--autoSize">
								<p class="uc-text-primary-color uc-activity-date"><i class="fa fa-clock-o"></i>few minutes ago</p>
							</div>

						</div>

						<div class="uc-grid uc-grid--center uc-grid--full">
							<div class="uc-grid-cell">
								<p style="line-height: 1.5em;">Duis cursus est at orci sodales, ac placerat sapien eleifend. Donec pharetra fringilla tincidunt. Praesent dignissim aliquam nunc, Etiam pharetra vel enim a vestibulum. sit amet accumsan libero. Nullam faucibus orci id mi tempus iaculis. Proin tristique ut tincidunt enim.</p>
							</div>
						</div>


						<div class="uc-grid uc-grid--center uc-grid--fit uc-comment-actions">
							<div class="uc-grid-cell">
								<a href="#"><i class="fa fa-reply" aria-hidden="true"></i>Reply</a>
							</div>
						</div>

					</div>

				</div>

			</li>

		</ul>


	</li>
	<li>

		<div class = "uc-grid uc-grid--center uc-grid--fit uc-grid--flex-cells">

			<div class="uc-grid-cell uc-grid-cell--autoSize uc-grid-cell--top" style="">
				<div class="uc-grid-cell-image-holder">
					<a  href="" class="uc-grid-cell-image" style="background-image: url(https://staging1.demo.ultracommunity.com/wp-content/uploads/ultra-comm/uploads/15/avatar/photo-1477814670986-8d8dccc5640d-1.jpeg)"></a>
				</div>
			</div>

			<div class="uc-grid-cell uc-grid-cell--top uc-grid">

				<div class="uc-grid uc-grid--fit uc-grid--flex-cells uc-grid--center" style="height: 42px">

					<div class="uc-grid-cell uc-grid-cell--autoSize">
						<p class="uc-text-primary-color">
							<a class="uc-text-primary-color" href=""> Mihai Chelaru </a>
						</p>
					</div>
					<div class="uc-grid-cell"></div>
					<div class="uc-grid-celluc-grid-cell--autoSize">
						<p class="uc-text-primary-color uc-activity-date"><i class="fa fa-clock-o"></i>few minutes ago</p>
					</div>

				</div>

				<div class="uc-grid uc-grid--center uc-grid--full">
					<div class="uc-grid-cell">
						<p style="line-height: 1.5em;">Duis cursus est at orci sodales, ac placerat sapien eleifend. Donec pharetra fringilla tincidunt. Praesent dignissim aliquam nunc, Etiam pharetra vel enim a vestibulum. sit amet accumsan libero. Nullam faucibus orci id mi tempus iaculis. Proin tristique ut tincidunt enim.</p>
					</div>
				</div>


				<div class="uc-grid uc-grid--center uc-grid--fit uc-comment-actions">
					<div class="uc-grid-cell">
						<a href="#"><i class="fa fa-reply" aria-hidden="true"></i>Reply</a>
					</div>
				</div>


			</div>

		</div>

	</li>



</ul>






<div class="uc-box-sizing-border-box uc-activity-comments-form-holder uc-no-corners">

	<form class="uc-form uc-form-stacked uc-activity-comments-form uc-grid uc-grid--fit uc-grid--flex-cells" method="post" autocomplete="off">

		<input type="hidden" name="activityId"  value = "<?php echo $activityEntity->ActivityId;?>" />

		<div class="uc-grid-cell uc-grid-cell--autoSize uc-grid-cell--top" style="">
			<div class="uc-grid-cell-image-holder">
				<a  href="" class="uc-grid-cell-image" style="background-image: url(https://staging1.demo.ultracommunity.com/wp-content/uploads/ultra-comm/uploads/15/avatar/photo-1477814670986-8d8dccc5640d-1.jpeg)"></a>
			</div>
		</div>

		<div class="uc-grid-cell uc-grid-cell--top uc-grid uc-grid--full">
            <div class="uc-grid-cell">
	            <textarea name="txtActivityCommentContent" class="uc-input-1" type="textarea" placeholder="<?php esc_attr_e('Write a Comment...', 'ultra-community'); ?>"></textarea>
            </div>

			<div class="uc-grid-cell uc-grid uc-grid--center uc-grid--flex-cells uc-activity-comments-form-footer">
				<div class="uc-grid-cell uc-grid-cell--autoSize">
					<button class="uc-button"><span>Post</span></button>
				</div>

				<div class="uc-grid-cell uc-grid-cell--autoSize uc-hidden">
					<div class="uc-ajax-loader"><div class="bounce-left"></div><div class="bounce-middle"></div><div class="bounce-right"></div></div>
				</div>

				<div class="uc-grid-cell uc-notice-holder"><p class=" uc-notice uc-notice-error"> This is the notice </p></div>

			</div>

		</div>


	</form>

</div>
