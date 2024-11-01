<?php

defined('ABSPATH') || exit;

!empty($mainMenuAdditionalHtmlClasses) ?: $mainMenuAdditionalHtmlClasses = null;
!empty($afterMainMenuOutputContent)    ?: $afterMainMenuOutputContent    = null;

$arrNavBarMenuItems = empty($arrNavBarMenuItems) ? array() : (array) $arrNavBarMenuItems;

$sectionsListOutput = null;
foreach ($arrNavBarMenuItems as $navBarMenuItem)
{

	!empty($navBarMenuItem->Url)        ?:  $navBarMenuItem->Url        = null;
	!empty($navBarMenuItem->Name)       ?:  $navBarMenuItem->Name       = null;
	!empty($navBarMenuItem->IsActive)   ?:  $navBarMenuItem->IsActive   = null;
	!empty($navBarMenuItem->IconClass)  ?:  $navBarMenuItem->IconClass  = null;
	!empty($navBarMenuItem->IconOutput) ?:  $navBarMenuItem->IconOutput = null;

	$sectionUrlClass    = $navBarMenuItem->IsActive ? 'uc-hvr-underline-from-center uc-hvr-underline-active' : 'uc-hvr-underline-from-center';
	$sectionHolderClass = $navBarMenuItem->IsActive ? 'active-section' : null;

	//$arrNavBarArguments['arrNavBarActiveSection']

	$sectionsListOutput .= "<li class=\"uc-grid-cell uc-grid-cell--autoSize $sectionHolderClass\">";
	$sectionsListOutput .= "<a class=\"$sectionUrlClass\" href=\"{$navBarMenuItem->Url}\">";

	if($navBarMenuItem->IconOutput)
	{
		$sectionsListOutput .= $navBarMenuItem->IconOutput;
	}
	elseif ($navBarMenuItem->IconClass)
	{
		$sectionsListOutput .=  "<i class=\"$navBarMenuItem->IconClass\"></i>";
	}

	$sectionsListOutput .= "<span>{$navBarMenuItem->Name}</span>";

	$sectionsListOutput .= '</a>';
	$sectionsListOutput .= '</li>';

}

if(empty($sectionsListOutput))
	return null;


$navBarHolderKey = empty($profileSectionKey) ? null : sanitize_html_class($profileSectionKey);


echo <<<NavigationOutput
<div class="uc-navbar-holder uc-panel uc-grid uc-grid--fit uc-grid--center uc-grid--flex-cells $navBarHolderKey">

	<div class="uc-grid-cell uc-mobile-navigation-holder">
		<a href="#" class="uc-mobile-menu-button"><span class="uc-burger-icon"></span></a>
	</div>

	<div class="uc-grid-cell uc-main-menu-holder">

		<ul class="uc-grid uc-grid-medium--fit uc-grid--full uc-grid--center uc-grid--flex-cells uc-main-menu $mainMenuAdditionalHtmlClasses">
            $sectionsListOutput
		</ul>
	</div>
    <div class="uc-grid-cell uc-grid-cell--autoSize">
	    $afterMainMenuOutputContent
    </div>
</div>
NavigationOutput;

