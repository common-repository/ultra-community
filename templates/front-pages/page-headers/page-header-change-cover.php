<?php
defined('ABSPATH') || exit;
isset($changeCoverText) ?: $changeCoverText = '';
isset($changeCoverUrl)  ?: $changeCoverUrl  = '';

echo <<<ChangeImage
<a class="uc-grid uc-grid--full uc-grid--flex-cells uc-grid--center uc-profile-header-change-cover" href="$changeCoverUrl">
	<i class="uc-grid-cell uc-grid-cell--autoSize uc-grid--center uc-grid--justify-center uc-circle-border fa fa-camera"></i>
	<span>$changeCoverText</span>
</a>
ChangeImage;


