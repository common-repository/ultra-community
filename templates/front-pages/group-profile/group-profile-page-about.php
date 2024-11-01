<?php
use UltraCommunity\Entities\GroupEntity;

/*
 * @var $groupEntity GroupEntity
 */
if(empty($groupEntity))
	return;

$groupEntity->Description = wp_kses_data($groupEntity->Description);

$panelTitle = esc_html__('Group Information', 'ultra-community');

echo <<<OutputContent

<div class="uc-group-about-section">

	<div class="uc-grid uc-grid--full uc-grid--flex-cells uc-panel">
		
		<h3 class="uc-grid-cell uc-grid uc-grid--center uc-grid--fit uc-grid--flex-cells uc-panel-head">
			<span class="uc-grid-cell uc-grid--center uc-grid--justify-center uc-grid-cell--autoSize">
				<i class="fa fa-info-circle"></i>
			</span>
			<span class="uc-grid-cell">$panelTitle</span>
		</h3>
		
		<div class="uc-grid-cell uc-grid-cell--center uc-grid  uc-grid--full uc-panel-content">
			<div class="uc-grid-cell uc-grid  uc-grid--fit uc-grid--flex-cells">
				<ul class="uc-grid-cell">
					<li>{$groupEntity->Description}</li>
				</ul>
			</div>
		</div>
		
	</div>

</div>

OutputContent;

