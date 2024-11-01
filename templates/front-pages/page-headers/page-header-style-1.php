<?php
defined('ABSPATH') || exit;

echo <<<PageHeader

<div class="uc-grid uc-grid--center uc-grid--full  uc-grid--flex-cells uc-grid--justify-center uc-header-holder  uc-darken-bg" style="background-image: url({$coverUrl})">
	$changeCoverOutput
    <div class="uc-grid-cell uc-grid-cell--center uc-grid-cell--autosize uc-header-content">

        <div class="uc-grid uc-grid--full uc-grid--justify-center  uc-grid-large--fit  uc-grid--center uc-grid--flex-cells">

            <div class="uc-grid-cell uc-grid-cell--autoSize uc-header-picture-holder {$pictureHolderAdditionalClass}" style="background-image: url({$pictureUrl})">$changeAvatarOutput</div>

            <div class="uc-grid-cell uc-grid uc-grid--full uc-grid-large--fit  uc-grid--center uc-grid--justify-center uc-header-info-holder">

                <div class="uc-grid-cell">
                
					<h2>$headLineOutput</h2>
     
					$beforeMetaListOutput
					
                    	<ul class="uc-grid-cell uc-header-meta-list-holder">$metaListOutput</ul>
                    
					$afterMetaListOutput
					
                </div>

                <div class="uc-grid-cell uc-grid uc-grid--full uc-grid--center uc-grid-cell--autoSize uc-grid-large--fit">

                    <div class="uc-grid-cell uc-header-stats-holder">
                        <ul>$statsListOutput</ul>
                    </div>

                </div>

            </div>
        </div>
    </div>

</div>

PageHeader;







