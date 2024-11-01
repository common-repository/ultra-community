<?php
$arrSections = empty($arrSections) ? array() : (array)$arrSections;
if(empty($arrSections))
return;

$profileSectionsList = '';

//print_r($arrSections);exit;

foreach ($arrSections as $section)
{
    if(empty($section->Fields))
        continue;

	$sectionFieldsList = '';
    foreach ($section->Fields as $sectionField)
    {

	    if($sectionField instanceof \UltraCommunity\Modules\Forms\FormFields\DividerField)
	    {
		    $sectionFieldsList .= '<div class="uc-grid-cell uc-grid  uc-grid--fit uc-grid--flex-cells">';
		    $sectionFieldsList .= $sectionField->toHtmlOutput(array());
		    $sectionFieldsList .= '</div>';
		    continue;
	    }

	    $sectionFieldsList .= '<div class="uc-grid-cell uc-grid  uc-grid--fit uc-grid--flex-cells uc-grid--center">';
	
		if($sectionField instanceof \UltraCommunity\Modules\Forms\FormFields\SocialNetworkUrlField)
		{
			$networkIconKey  = $sectionField->getFontAwesomeClass( $sectionField->NetworkId );
			$fontAwesomeIcon = $sectionField->getFontAwesomeClass($sectionField->NetworkId);
			$fontAwesomeIcon = "fa fa-$fontAwesomeIcon";
			
			$networkUrl = wp_strip_all_tags($sectionField->Value);
			
			$sectionFieldsList .= "<a target=\"_blank\" href=\"$networkUrl\" class=\"uc-grid-cell uc-grid-cell--autoSize uc-grid--justify-center uc-social-network uc-sn-$networkIconKey uc-social-network-colored uc-circle-border\"><i class=\"uc-grid-cell--center $fontAwesomeIcon\"></i></a>";
			
			$sectionFieldsList .= '<p class="uc-grid-cell uc-sn-label">'  . $sectionField->Label . '</p>';
		}
	    else
	    {
		    $sectionFieldsList .= '<p class="uc-grid-cell">'  . $sectionField->Label . '</p>';
	    }
	    
	    $sectionFieldsList .= '<ul class="uc-grid-cell">' . implode('', array_map(function($fieldValue){return "<li>$fieldValue</li>";}, (array)$sectionField->Value)) . '</ul>';

	    $sectionFieldsList .= '</div>';
    }


	$sectionIcon = empty($section->FontAwesomeIcon) ? 'fa fa-align-left' : $section->FontAwesomeIcon;

	$profileSectionsList .= '<div class="uc-grid uc-grid--full uc-grid--flex-cells uc-panel">';
	    $profileSectionsList .= '<h3 class="uc-grid-cell uc-grid uc-grid--center uc-grid--fit uc-grid--flex-cells uc-panel-head"><span class="uc-grid-cell uc-grid--center uc-grid--justify-center uc-grid-cell--autoSize"><i class="'.$sectionIcon.'"></i></span><span class="uc-grid-cell">' . $section->Title . '</span></h3>';
	    $profileSectionsList .= '<div class="uc-grid-cell uc-grid-cell--center uc-grid  uc-grid--full uc-panel-content">' . $sectionFieldsList . '</div>';
	$profileSectionsList .= '</div>';
}

echo '<div class="uc-user-about-section">', $profileSectionsList, '</div>';
