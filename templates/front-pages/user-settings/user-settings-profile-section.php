<?php
use UltraCommunity\Controllers\UserController;

if(empty($arrProfileSections))
	return;

$optionalText   = esc_html__('optional', 'ultra-community');
$buttonSaveText = esc_html__('Save Changes', 'ultra-community');

$formsOutput = null;

//print_r($arrProfileSections);exit;

foreach ($arrProfileSections as $profileSection)
{
    if(empty($profileSection->IsActive) || empty($profileSection->Fields))
        continue;

	$formFieldsOutput = null;

	foreach ($profileSection->Fields as $sectionField)
	{
		$arrAdditionalAttributes = array();
		if(!UserController::currentUserCanEditProfileFormField($sectionField)){
			$arrAdditionalAttributes['disabled'] = 'disabled';
		}

//		if(! $sectionField->IsRequired )
//		{
//			//$arrAdditionalAttributes['placeholder'] =  sprintf('(%s)%s%s', $optionalText, empty($sectionField->PlaceHolder) ? '' : ' - ' , $sectionField->PlaceHolder);
//		}

		$sectionField->FontAwesomeIcon = null;

		$fieldOutputHtml = $sectionField->toHtmlOutput($arrAdditionalAttributes);
		$fieldOutputHtml = str_replace('uc-form-grouped-controls', 'uc-form-grouped-controls uc-grid-cell', $fieldOutputHtml);

		if(!empty($profileSection->IsSocialNetworks))
		{
			if(!method_exists($sectionField, 'getFontAwesomeClass'))
				continue;
			
			$networkIconKey  = $sectionField->getFontAwesomeClass( $sectionField->NetworkId );
			$fontAwesomeIcon = $sectionField->getFontAwesomeClass($sectionField->NetworkId);
			$fontAwesomeIcon = "fa fa-$fontAwesomeIcon";
			
//			$networkIconKey = $sectionField->getFontAwesomeClass( $sectionField->NetworkId );
//			$fontAwesomeIcon = "fa fa-$networkIconKey";

			$socialNetworkOutput = "<a class=\"uc-grid-cell uc-grid-cell--autoSize uc-grid--justify-center uc-social-network uc-sn-$networkIconKey uc-social-network-colored uc-circle-border\"><i class=\"uc-grid-cell--center $fontAwesomeIcon\"></i></a>";

			$fieldOutputHtml = $socialNetworkOutput . $fieldOutputHtml;

			$formFieldsOutput .= '<div class = "uc-grid uc-grid--center uc-grid--fit">' . $fieldOutputHtml . '</div>';
		}
		else
		{
			$formFieldsOutput .= '<div class = "uc-grid uc-grid--center uc-grid--full">' . $fieldOutputHtml . '</div>';
		}


	}

	if(empty($formFieldsOutput))
		continue;
	
	!empty($profileSection->IconOutput) ?: $profileSection->IconOutput = "<i class=\"fa fa-align-left\"></i>";
	
	$formAdditionalClass = empty($profileSection->IsSocialNetworks) ? 'uc-settings-form' : 'uc-settings-form uc-settings-sn-form';


echo <<<Output
	<div class="uc-user-settings-profile-form-section">
        <div class="uc-grid uc-grid--full uc-grid--flex-cells uc-panel">

            <h3 class="uc-grid-cell uc-grid uc-grid--center uc-grid--fit uc-grid--flex-cells uc-panel-head">
                <span class="uc-grid-cell uc-grid--center uc-grid--justify-center uc-grid-cell--autoSize">$profileSection->IconOutput</span>
                <span class="uc-grid-cell">{$profileSection->Title}</span>
            </h3>

            <form autocomplete="off" class="uc-grid-cell uc-grid-cell--center uc-panel-content uc-form $formAdditionalClass" method="post">

                <div class="uc-form-body">$formFieldsOutput</div>
                <div class="uc-form-footer">
                    <div class="uc-grid uc-grid--fit uc-grid--flex-cells">

                        <div class="uc-grid-cell uc-grid-cell--autoSize ">
                            <button class="uc-button uc-button-primary">$buttonSaveText</button>
                        </div>

                        <div class="uc-grid-cell uc-grid-cell--autoSize uc-grid-cell--center">
                            <div class="uc-ajax-loader uc-hidden">
                                <div class="bounce-left"></div>
                                <div class="bounce-middle"></div>
                                <div class="bounce-right"></div>
                            </div>
                        </div>

                        <div class="uc-grid-cell uc-notice-holder"></div>

                    </div>
                </div>
            </form>

        </div>
    </div>
Output;


}