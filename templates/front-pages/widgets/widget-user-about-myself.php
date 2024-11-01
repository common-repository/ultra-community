<?php
use UltraCommunity\UltraCommHelper;
use UltraCommunity\UltraCommHooks;

defined('ABSPATH') || exit;

!empty($widgetItemInfo) ?: $widgetItemInfo = new \stdClass();
if(empty($widgetItemInfo->UserEntity))
	return;

$userAvatarUrl   = UltraCommHelper::getUserAvatarUrl($widgetItemInfo->UserEntity);
$userDisplayName = UltraCommHelper::getUserDisplayName($widgetItemInfo->UserEntity);

?>

<div class="uc-grid uc-grid--full uc-grid--center uc-grid--flex-cells uc-panel uc-sidebar-panel uc-user-about-myself">
	
	<h3 class="uc-grid-cell uc-grid uc-grid--center uc-grid--fit uc-grid--flex-cells uc-panel-head">
		<span class="uc-grid-cell uc-grid--center uc-grid--justify-center uc-grid-cell--autoSize"><i class="fa fa-user-circle"></i></span>
		<span class="uc-grid-cell"><?php echo $widgetItemInfo->Title;?></span>
	</h3>
	
	<div class="uc-grid-cell uc-panel-content">
		<div class="uc-bg-holder uc-circle-border" style="background-image:url(<?php echo $userAvatarUrl;?>)"></div>
  
		<div class="uc-grid uc-grid--fit uc-grid--center uc-user-about-meta">
            
            <?php do_action(UltraCommHooks::ACTION_WIDGET_ABOUT_MYSELF_BEFORE_DISPLAY_NAME, $widgetItemInfo->UserEntity->Id); ?>
			
            <h3 class="uc-grid-cell"><?php echo $userDisplayName;?></h3>
			
            <?php do_action(UltraCommHooks::ACTION_WIDGET_ABOUT_MYSELF_AFTER_DISPLAY_NAME, $widgetItemInfo->UserEntity->Id); ?>
            
		</div>
		
		<div class="uc-section-divider"></div>
  
		<p><?php echo UltraCommHelper::getUserShortDescription($widgetItemInfo->UserEntity, 60); ?></p>
	</div>
	
	<div class="uc-grid-cell uc-panel-footer"></div>

</div>
