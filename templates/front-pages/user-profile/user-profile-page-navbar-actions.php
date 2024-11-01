<?php

defined('ABSPATH') || exit;

$arrNavBarActions = empty($arrNavBarActions) ? array() : (array)$arrNavBarActions;
$actionsListOutput = null;

/**
 * @var $navBarAction \UltraCommunity\Entities\PageActionEntity[]
 */
foreach($arrNavBarActions as $navBarAction)
{
	$actionOutput = $navBarAction->getOutputContent();
	empty($actionOutput) ?: $actionsListOutput .= '<li class="uc-grid-cell uc-grid-cell--autoSize">' . $actionOutput . '</li>';
}

echo '<ul class="uc-grid uc-grid--fit uc-grid--center uc-grid--flex-cells uc-navbar-actions">', $actionsListOutput, '</ul>';



