<?php
defined('ABSPATH') || exit;

isset($changeAvatarUrl)  ?: $changeAvatarUrl  = '';
isset($changeAvatarText) ?: $changeAvatarText = '';

echo <<<ChangeImage

<a class="uc-grid uc-grid--full uc-grid--flex-cells uc-grid--center uc-circle-border uc-profile-header-change-avatar" href="$changeAvatarUrl">
	<i class="uc-grid-cell uc-grid-cell--autoSize uc-grid--center uc-grid--justify-center uc-circle-border fa fa-camera"></i>
	<span>$changeAvatarText</span>
</a>


ChangeImage;
