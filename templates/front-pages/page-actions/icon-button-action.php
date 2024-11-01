<?php
use UltraCommunity\Entities\PageActionEntity;

if(empty($pageAction) || !($pageAction instanceof PageActionEntity))
	return;

$actionIcon = null;
if(empty($pageAction->ElementSvgIcon))
{
	$actionIcon = "<i class=\"{$pageAction->ElementIconClasses}\"></i>";
}
else
{
	$actionIcon = $pageAction->ElementSvgIcon;
}

echo <<<PageAction
	<button $pageAction->ElementDataAction class="{$pageAction->ElementClasses}">
		$actionIcon<span>{$pageAction->ActionText}</span>
	</button>
PageAction;
