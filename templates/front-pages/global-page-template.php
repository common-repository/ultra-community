<?php
/*
 * Template Name: Full Width
 * Description: UltraCommunity Full Width Template
 */

use UltraCommunity\Controllers\FrontPageController;
use UltraCommunity\Modules\Appearance\General\GeneralAppearancePublicModule;



get_header();



if( have_posts() )
{
	the_post();
	(!FrontPageController::hasActivePage()) ?: FrontPageController::getActivePage()->renderMarkup();
}

//$pageColorSchemeClassName = GeneralAppearancePublicModule::getPageColorSchemeClassName();
//echo "<div class=\"uch $pageColorSchemeClassName\">";
//	while ( have_posts() ) :
//
//
//
//		(!FrontPageController::hasActivePage()) ?: FrontPageController::getActivePage()->renderMarkup();
//
//	endif;

//echo '</div>';

get_footer();