<?php

if(empty($pageNotice) || empty($pageNotice->Message))
	return;

$noticeType = empty($pageNotice->Type)  ? 'info' : sanitize_html_class($pageNotice->Type);

$pageNoticeOutput = <<<PageNoticeOutput

<div class="uc-grid uc-grid--full uc-grid--center">
	<div class="uc-grid-cell">
		<p class="uc-notice uc-content-notice uc-notice-$noticeType">{$pageNotice->Message}</p>
	</div>
</div>

PageNoticeOutput;

echo $pageNoticeOutput;
unset($pageNotice, $pageNoticeOutput);
