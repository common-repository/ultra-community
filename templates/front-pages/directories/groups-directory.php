<?php

use UltraCommunity\Entities\PageActionEntity;
use UltraCommunity\UltraCommUtils;

if(empty($arrMembersDirectory['directoryItems']))
	return;

$pageNumber     = empty($arrMembersDirectory['pageNumber'])  ? 1 : (int)$arrMembersDirectory['pageNumber'];
$totalPages     = empty($arrMembersDirectory['totalPages'])  ? 0 : (int)$arrMembersDirectory['totalPages'];
$directoryId    = empty($arrMembersDirectory['directoryId']) ? 0 : (int)$arrMembersDirectory['directoryId'];

$paginationType = empty($arrMembersDirectory['paginationType']) ? 'scroll' : $arrMembersDirectory['paginationType'];


$containerHtmlClasses = array('uc-groups-directory', "uc-groups-directory-$directoryId", "uc-directory-pagination-$paginationType");

//!empty($arrMembersDirectory['showStatsIcons'])       ?: $containerHtmlClasses[] = 'uc-directory-icons-stats';
!empty($arrMembersDirectory['showCoverImage'])       ?: $containerHtmlClasses[] = 'uc-directory-no-cover';


empty($arrMembersDirectory['showSquarePicture'])    ?: $containerHtmlClasses[] = 'uc-directory-square-picture';


$containerHtmlClasses = implode(' ', $containerHtmlClasses);


if((1 === $pageNumber && $paginationType === 'scroll') || $paginationType !== 'scroll')
{
	echo "<div class=\"uc-grid uc-grid--gutters-xl  uc-grid--full  uc-grid--center  uc-directory-holder $containerHtmlClasses \">";
}


foreach ($arrMembersDirectory['directoryItems'] as $directoryItem)
{
	$directoryItem->MainTagLineItems   = empty($directoryItem->MainTagLineItems)   ? array() : (array)$directoryItem->MainTagLineItems;
	$directoryItem->SecondTagLineItems = empty($directoryItem->SecondTagLineItems) ? array() : (array)$directoryItem->SecondTagLineItems;
	$directoryItem->SocialNetworks     = empty($directoryItem->SocialNetworks)     ? array() : (array)$directoryItem->SocialNetworks;
	$directoryItem->ActionsList        = empty($directoryItem->ActionsList)        ? array() : (array)$directoryItem->ActionsList;

	$onlineIndicator = empty($directoryItem->IsOnline) || empty($arrMembersDirectory['showOnlineStatus']) ? null : 'uc-online-indicator';

	$mainTagLineOutputList = $secondTagLineOutputList = '';
	foreach ($directoryItem->MainTagLineItems as $tagLineItem)
	{
		if(empty($tagLineItem->Text))
			continue;
		$mainTagLineOutputList .= '<li>';
		$mainTagLineOutputList .= empty($tagLineItem->Icon) ? '' : "<i class=\"{$tagLineItem->Icon}\"></i>";
		$mainTagLineOutputList .= '<span>' . esc_html($tagLineItem->Text) . '</span>';
		$mainTagLineOutputList .= '</li>';
	}

	foreach ($directoryItem->SecondTagLineItems as $tagLineItem)
	{
		if(empty($tagLineItem->Text))
			continue;

		$secondTagLineOutputList .= '<li><p>' . esc_html($tagLineItem->Text) . '</p></li>';
	}

	$statsHolderClasses = 'uc-grid-cell uc-directory-item-statslist-holder';
	$statsListOutput    = '';
	if(!empty($directoryItem->Stats))
	{
		empty($arrMembersDirectory['showStatsHighlighted']) ?: $statsHolderClasses .= ' uc-directory-highlighted-stats';
		if(!empty($arrMembersDirectory['showStatsIcons']))
		{
			$statsHolderClasses .= ' uc-directory-icons-stats';
			foreach ($directoryItem->Stats as $itemStats)
			{
				$toolTipText = $itemStats->Number . ' ' . strtolower($itemStats->Text);
				$statsListOutput .= "<li><a  href=\"{$itemStats->Url}\" uc-tooltip=\"$toolTipText\" uc-tooltip-flow=\"down\"><i class=\"fa {$itemStats->Icon}\"></i></a></li>";
			}
		}
		else
		{
			foreach ($directoryItem->Stats as $itemStats)
			{
				$statsListOutput .= "<li><span>{$itemStats->Number}</span><span>{$itemStats->Text}</span></li>";
			}
		}

	}

	$actionsListOutput = null;
	foreach ($directoryItem->ActionsList as $groupPageAction)
    {
        if(!$groupPageAction instanceof PageActionEntity)
            continue;
        $actionOutput = $groupPageAction->getOutputContent();
        empty($actionOutput) ?: $actionsListOutput .= "<li>$actionOutput</li>";
    }

	$coverHolderOutput = '';
	if(!empty($arrMembersDirectory['showCoverImage']) && !empty($directoryItem->CoverUrl))
	{
		$coverHolderOutput = "<div class=\"uc-grid-cell uc-grid-cell--autoSize uc-directory-item-cover\" style=\"background-image:url({$directoryItem->CoverUrl})\"></div>";
	}

	$itemOutput = <<<ItemOutput

<div class="uc-grid-cell uc-directory-item-holder">

    <div class="uc-grid uc-grid uc-grid--full uc-grid--flex-cells uc-grid--center uc-grid--justify-center uc-panel">

        $coverHolderOutput

        <a class="uc-grid-cell uc-directory-item-picture uc-grid-cell--autoSize $onlineIndicator" href="{$directoryItem->HeadLineUrl}" style="background-image:url({$directoryItem->PictureUrl})"></a>

        <h3 class="uc-grid-cell uc-directory-item-headline"><a href="{$directoryItem->HeadLineUrl}">{$directoryItem->HeadLine}</a></h3>

        <ul class="uc-grid-cell uc-directory-item-tagline-holder uc-directory-item-main-tagline">$mainTagLineOutputList</ul>

        <ul class="uc-grid-cell uc-directory-item-tagline-holder">$secondTagLineOutputList</ul>

        <ul class="$statsHolderClasses">$statsListOutput</ul>

		<ul class="uc-grid-cell uc-directory-item-actions-holder">$actionsListOutput</ul>

    </div>

</div>

ItemOutput;

	echo $itemOutput;
}



if((1 === $pageNumber && $paginationType === 'scroll') || $paginationType !== 'scroll')
{
	echo '</div>';
}

if((1 === $pageNumber && $paginationType === 'scroll'))
{
	echo '<div class="uc-page-load-status"><div class="uc-ajax-loader infinite-scroll-request"><div class="bounce-left"></div><div class="bounce-middle"></div><div class="bounce-right"></div></div></div>';
}

echo ($totalPages > 1 && $paginationType !== 'scroll') ? UltraCommUtils::getPaginationOutputContent($pageNumber, $totalPages) : null;
