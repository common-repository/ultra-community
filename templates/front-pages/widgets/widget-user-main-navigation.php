<?php
if(empty($widgetItemInfo->UserEntity))
    return;

$widgetItemInfo->ArrMenuSections  = empty($widgetItemInfo->ArrMenuSections) ? array() : (array)$widgetItemInfo->ArrMenuSections;

$logOutButtonOutput = null;
$userAvatarUrl      = uc_get_user_avatar_url($widgetItemInfo->UserEntity);
$userDisplayName    = uc_get_user_display_name($widgetItemInfo->UserEntity);
$greetingText       = esc_html__('Hello!', 'ultra-community');



//print_r($widgetItemInfo->ArrMenuSections);exit;

if(!empty($widgetItemInfo->ShowLogOutButton))
{
    $logOutText = esc_html__('Sign out', 'ultra-community');
    $logOutUrl  = uc_get_logout_url();

//	$logOutButtonOutput = <<<Output
//        <a href = "$logOutUrl" class="">
//            <svg id="aaa" xmlns="http://www.w3.org/2000/svg" version="1.1" enable-background="new 0 0 100 100" xml:space="preserve" viewBox="-5.10 -5.13 122.73 110.25">
//                <path fill="none" stroke="currentColor" stroke-width="8" stroke-miterlimit="10" d="M71.31,71.294  c-11.761,11.761-30.828,11.761-42.589,0s-11.761-30.828,0-42.589s30.828-11.761,42.589,0"></path>
//                <line fill="none" stroke="#000000" stroke-width="6" stroke-miterlimit="10" x1="90" y1="50" x2="50.015" y2="50"></line>
//                <polyline fill="none" stroke="#000000" stroke-width="6" stroke-miterlimit="10" points="81.957,60.647 92.604,50 81.957,39.353 "></polyline>
//                <circle fill="#000000" stroke="#000000" stroke-width="2" stroke-miterlimit="10" cx="50.015" cy="50" r="12.485"></circle>
//            </svg>
//            <em>$logOutText</em>
//        </a>
//Output;

	$logOutButtonOutput = <<<Output
        <a href = "$logOutUrl" class="">
            <svg class = "uc-svg" version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M255.786 0.010c0.112 0 0.245 0 0.377 0 63.553 0 121.67 23.276 166.291 61.768l-0.329-0.278c2.296 1.968 3.742 4.872 3.742 8.113 0 3.505-1.69 6.615-4.3 8.559l-0.028 0.020-35.199 26.132c-1.785 1.394-4.061 2.235-6.533 2.235s-4.748-0.841-6.557-2.252l0.024 0.017c-32.141-25.112-73.122-40.265-117.642-40.265-106.034 0-191.992 85.958-191.992 191.991s85.958 191.992 191.992 191.992c44.521 0 85.502-15.154 118.067-40.585l-0.425 0.32c1.781-1.382 4.046-2.214 6.506-2.214s4.726 0.834 6.531 2.233l-0.024-0.018 35.412 26.293c2.655 1.964 4.357 5.083 4.357 8.601 0 3.229-1.435 6.125-3.704 8.080l-0.014 0.012c-44.411 38.058-102.562 61.224-166.123 61.224-141.379 0-255.989-114.61-255.989-255.989 0-141.227 114.365-255.744 255.535-255.988h0.024z"></path><path d="M391.514 375.46l116.262-111.142c2.442-1.971 3.991-4.964 3.991-8.32s-1.55-6.349-3.971-8.303l-0.020-0.016-116.262-111.141c-1.808-1.458-4.135-2.339-6.666-2.339-5.889 0-10.663 4.772-10.666 10.659v79.144h-211.831c-0.010 0-0.021 0-0.032 0-2.739 0-4.995 2.065-5.298 4.722l-0.002 0.024c-0.951 8.178-1.494 17.651-1.494 27.252s0.542 19.076 1.598 28.394l-0.106-1.142c0.305 2.682 2.561 4.747 5.3 4.747 0.011 0 0.023 0 0.034 0h211.828v79.143c0.004 5.888 4.778 10.659 10.666 10.659 2.532 0 4.858-0.882 6.687-2.356l-0.021 0.017z"></path></svg>
            <em>$logOutText</em>
        </a>
Output;

}

$widgetMenuOutput = null;
foreach ($widgetItemInfo->ArrMenuSections as $sectionKey => $menuSection)
{
	if(empty($menuSection->ArrNavItems))
		continue;

	$sectionClassName  = (!empty($sectionKey) && !is_numeric($sectionKey)) ? sanitize_html_class($sectionKey) : sanitize_html_class($menuSection->Name);

	$menuNavItemsOutput = null;
	foreach ($menuSection->ArrNavItems as $navItem)
	{
		$menuNavItemsOutput .= '<li class="uc-grid-cell">';
			$menuNavItemsOutput .= "<a href=\"{$navItem->Url}\">";
				$menuNavItemsOutput .= empty($navItem->IconClass) ? null : "<i class=\"{$navItem->IconClass}\"></i>";
				$menuNavItemsOutput .= "<small>$navItem->Name</small>";
				$menuNavItemsOutput .= empty($navItem->Counter)   ? null : "<b>$navItem->Counter</b>";
			$menuNavItemsOutput .= "</a>";
		$menuNavItemsOutput .= '</li>';
	}


	$widgetMenuOutput .= <<<SectionOutput
		<dl class="uc-grid uc-grid--full uc-grid--flex-cells $sectionClassName">
			<dt class="uc-grid-cell">$menuSection->Name</dt>
			<dd class="uc-grid-cell">
				<ul class="uc-grid uc-grid--full uc-grid--flex-cells">
					$menuNavItemsOutput
				</ul>
			</dd>
		</dl>
SectionOutput;

}

echo <<<WidgetOutput

<div class="uc-grid uc-grid--full uc-grid--center uc-grid--flex-cells uc-panel uc-sidebar-panel uc-user-main-widget">

	<div class="uc-grid-cell uc-panel-header">

		<div class="uc-grid uc-grid--fit uc-grid--flex-cells uc-widget-header">

			<div class="uc-grid-cell uc-grid-cell--autoSize uc-user-avatar-holder">
				<span class="uc-bg-holder" style="background-image:url($userAvatarUrl)"></span>
			</div>

			<div class="uc-grid-cell uc-user-meta">
				<span>$greetingText</span>
				<b>$userDisplayName</b>
			</div>

			<div class="uc-grid-cell uc-grid-cell--autoSize uc-arrow-holder">
				<svg  version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1024 1024"><path stroke-width="49.152" stroke-miterlimit="4" stroke-linecap="round" stroke-linejoin="round" d="M320.563 975.981l519.623-463.981-519.623-463.981-136.741 122.101 382.879 341.879-382.879 341.878 136.741 122.103z"></path></svg>
			</div>

		</div>

	</div>

	<div class="uc-grid-cell uc-panel-content">

$widgetMenuOutput

	</div>


<div class="uc-grid-cell uc-panel-footer">$logOutButtonOutput</div>

</div>


WidgetOutput;

//	<a href = "" class="">
//            <svg class = "uc-svg" version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
//                <path fill="currentColor" d="M255.786 0.010c0.112 0 0.245 0 0.377 0 63.553 0 121.67 23.276 166.291 61.768l-0.329-0.278c2.296 1.968 3.742 4.872 3.742 8.113 0 3.505-1.69 6.615-4.3 8.559l-0.028 0.020-35.199 26.132c-1.785 1.394-4.061 2.235-6.533 2.235s-4.748-0.841-6.557-2.252l0.024 0.017c-32.141-25.112-73.122-40.265-117.642-40.265-106.034 0-191.992 85.958-191.992 191.991s85.958 191.992 191.992 191.992c44.521 0 85.502-15.154 118.067-40.585l-0.425 0.32c1.781-1.382 4.046-2.214 6.506-2.214s4.726 0.834 6.531 2.233l-0.024-0.018 35.412 26.293c2.655 1.964 4.357 5.083 4.357 8.601 0 3.229-1.435 6.125-3.704 8.080l-0.014 0.012c-44.411 38.058-102.562 61.224-166.123 61.224-141.379 0-255.989-114.61-255.989-255.989 0-141.227 114.365-255.744 255.535-255.988h0.024z"></path>
//                <path fill="currentColor" d="M391.514 375.46l116.262-111.142c2.442-1.971 3.991-4.964 3.991-8.32s-1.55-6.349-3.971-8.303l-0.020-0.016-116.262-111.141c-1.808-1.458-4.135-2.339-6.666-2.339-5.889 0-10.663 4.772-10.666 10.659v79.144h-211.831c-0.010 0-0.021 0-0.032 0-2.739 0-4.995 2.065-5.298 4.722l-0.002 0.024c-0.951 8.178-1.494 17.651-1.494 27.252s0.542 19.076 1.598 28.394l-0.106-1.142c0.305 2.682 2.561 4.747 5.3 4.747 0.011 0 0.023 0 0.034 0h211.828v79.143c0.004 5.888 4.778 10.659 10.666 10.659 2.532 0 4.858-0.882 6.687-2.356l-0.021 0.017z"></path>
//            </svg>
//            <em>Log me out</em>
//        </a>

/*
 * 	<div class="uc-grid-cell uc-panel-content">

		<dl class="uc-grid uc-grid--full uc-grid--flex-cells">
			<dt class="uc-grid-cell">Blog Posts</dt>
			<dd class="uc-grid-cell">
				<ul class="uc-grid uc-grid--full uc-grid--flex-cells">

					<li class="uc-grid-cell">
						<a href=""><i class="fa fa-plus"></i><small>New Post</small><b>123</b></a>
					</li>
					<li class="uc-grid-cell">
						<a href=""><i class="fa fa-wrench"></i><small>Draft Posts</small><b>123</b></a>
					</li>
					<li class="uc-grid-cell">
						<a href=""><i class="fa fa-eye"></i><small>Pending Posts</small><b>123</b></a>
					</li>
					<li class="uc-grid-cell">
						<a href=""><i class="fa fa-check"></i><small>Published Posts</small><b>123</b></a>
					</li>

				</ul>
			</dd>
		</dl>

		<dl class="uc-grid uc-grid--full uc-grid--flex-cells">
			<dt class="uc-grid-cell">Settings</dt>
			<dd class="uc-grid-cell">
				<ul class="uc-grid uc-grid--full uc-grid--flex-cells">

					<li class="uc-grid-cell">
						<a href=""><i class="fa fa-user-circle"></i><small>Profile Settings</small><b>123</b></a>
					</li>
					<li class="uc-grid-cell">
						<a href=""><i class="fa fa-cogs"></i><small>Account Settings</small><b>123</b></a>
					</li>
				</ul>
			</dd>
		</dl>


	</div>

 */