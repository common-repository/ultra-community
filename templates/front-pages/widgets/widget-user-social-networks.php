<?php
defined('ABSPATH') || exit;

$widgetTitle       = empty($widgetTitle)  ? esc_html__("Let's Connect", 'ultra-community') : esc_html($widgetTitle);
$arrSocialNetworks = empty($arrSocialNetworks) ? array() : (array)$arrSocialNetworks;

if(empty($arrSocialNetworks))
    return;

$socialNetworksList = '';
foreach ($arrSocialNetworks as $socialNetwork)
{
	$socialNetworksList .= <<<SN
<a class="uc-sn-{$socialNetwork->Icon} uc-social-network-colored" href="{$socialNetwork->Url}"><i class="fa fa-{$socialNetwork->Icon}"></i><span>{$socialNetwork->Name}</span></a>
SN;

}

?>

<div class="uc-grid uc-grid--full uc-grid--center uc-grid--flex-cells uc-panel uc-sidebar-panel uc-user-networks">
	
	<h3 class="uc-grid-cell uc-grid uc-grid--center uc-grid--fit uc-grid--flex-cells uc-panel-head">
		<span class="uc-grid-cell uc-grid--center uc-grid--justify-center uc-grid-cell--autoSize"><i class="fa fa-share-alt"></i></span>
		<span class="uc-grid-cell"><?php echo $widgetTitle;?></span>
	</h3>
	
	<div class="uc-grid-cell uc-panel-content"><?php echo $socialNetworksList;?></div>
	
</div>