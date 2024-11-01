<?php

use UltraCommunity\UltraCommUtils;

if(empty($arrMembersDirectory['directoryItems']))
    return;

$pageNumber     = empty($arrMembersDirectory['pageNumber'])     ? 1 : (int)$arrMembersDirectory['pageNumber'];
$totalPages     = empty($arrMembersDirectory['totalPages'])     ? 0 : (int)$arrMembersDirectory['totalPages'];
$directoryId    = empty($arrMembersDirectory['directoryId'])    ? 0 : (int)$arrMembersDirectory['directoryId'];

$paginationType = empty($arrMembersDirectory['paginationType']) ? 'scroll' : $arrMembersDirectory['paginationType'];

$containerHtmlClasses = array('uc-members-directory', "uc-members-directory-$directoryId", "uc-directory-pagination-$paginationType");

!empty($arrMembersDirectory['showCoverImage'])       ?: $containerHtmlClasses[] = 'uc-directory-no-cover';

empty($arrMembersDirectory['showSquarePicture'])    ?: $containerHtmlClasses[] = 'uc-directory-square-picture';

//print_r($arrMembersDirectory);exit;

$containerHtmlClasses = implode(' ', $containerHtmlClasses);

if((1 === $pageNumber && $paginationType === 'scroll') || $paginationType !== 'scroll')
{
	echo "<div class=\"uc-grid uc-grid--gutters-xl  uc-grid--full  uc-grid--center  uc-directory-holder $containerHtmlClasses \">";
}


foreach ($arrMembersDirectory['directoryItems'] as $directoryItem)
{
	$directoryItem->MainTagLineItems   = empty($directoryItem->MainTagLineItems)   ? array() : (array)$directoryItem->MainTagLineItems;
	$directoryItem->SecondTagLineItems = empty($directoryItem->SecondTagLineItems) ? array() : (array)$directoryItem->SecondTagLineItems;

	$directoryItem->Actions            = empty($directoryItem->Actions)            ? array() : (array)$directoryItem->Actions;

    $onlineIndicator = empty($directoryItem->IsOnline) || empty($arrMembersDirectory['showOnlineStatus']) ? null : 'uc-online-indicator';

    $mainTagLineOutputList = $secondTagLineOutputList = '';
    foreach ($directoryItem->MainTagLineItems as $tagLineItem)
    {
        if(empty($tagLineItem->Text))
            continue;

	    $mainTagLineOutputList .= '<li><p>' . esc_html($tagLineItem->Text) . '</p></li>';
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

	$ratingsOutput = '';
	if(!empty($directoryItem->RatingsOutput))
	{
		$ratingsOutput = "<li>$directoryItem->RatingsOutput</li>";
	}

	$itemSocialNetworksListOutput = '';
	foreach ($directoryItem->SocialNetworks as $socialNetwork)
    {
	    $itemSocialNetworksListOutput .= "<li><a class=\"uc-grid-cell uc-grid-cell--autoSize uc-grid--justify-center uc-social-network uc-sn-{$socialNetwork->Icon} uc-social-network-colored\" href=\"{$socialNetwork->Url}\"><i class=\"uc-grid-cell--center fa fa-{$socialNetwork->Icon}\"></i></a></li>";
    }

	$coverHolderOutput = '';
	if(!empty($arrMembersDirectory['showCoverImage']) && !empty($directoryItem->CoverUrl))
	{
		$coverHolderOutput = "<div class=\"uc-grid-cell uc-grid-cell--autoSize uc-directory-item-cover\" style=\"background-image:url({$directoryItem->CoverUrl})\"></div>";
	}

	$itemActionsOutput = null;
	if(!empty($directoryItem->Actions))
	{
		$itemActionsOutput .= '<ul class="uc-grid-cell uc-directory-item-actions-holder">';

			foreach($directoryItem->Actions as $itemAction)
			{
				// build action here
			}


		$itemActionsOutput .= '</ul>';

	}

    $itemOutput = <<<ItemOutput

<div class="uc-grid-cell uc-directory-item-holder">

    <div class="uc-grid uc-grid uc-grid--full uc-grid--flex-cells uc-grid--center uc-grid--justify-center uc-panel">

       $coverHolderOutput

        <a class="uc-grid-cell uc-directory-item-picture uc-grid-cell--autoSize $onlineIndicator" href="{$directoryItem->HeadLineUrl}" style="background-image:url({$directoryItem->PictureUrl})"></a>

        <h3 class="uc-grid-cell uc-directory-item-headline"><a href="{$directoryItem->HeadLineUrl}">{$directoryItem->HeadLine}</a></h3>

        <ul class="uc-grid-cell uc-directory-item-tagline-holder uc-directory-item-main-tagline">$mainTagLineOutputList</ul>

        <ul class="uc-grid-cell uc-directory-item-ratings-holder">$ratingsOutput</ul>

        <ul class="uc-grid-cell uc-directory-item-tagline-holder">$secondTagLineOutputList</ul>

        <ul class="uc-grid-cell uc-directory-item-socialicons-holder">$itemSocialNetworksListOutput</ul>

        <ul class="$statsHolderClasses">$statsListOutput</ul>

		$itemActionsOutput

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
	echo '<div class="uc-page-load-status" style="padding:1.5em 0"><div class="uc-ajax-loader infinite-scroll-request"><div class="bounce-left"></div><div class="bounce-middle"></div><div class="bounce-right"></div></div></div>';
}

echo ($totalPages > 1 && $paginationType !== 'scroll') ? UltraCommUtils::getPaginationOutputContent($pageNumber, $totalPages) : null;
