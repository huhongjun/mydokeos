<?php // $Id: my_credits.php,v 1.2 2006/03/15 14:34:45 pcool Exp $
/*
==============================================================================
	Dokeos - elearning and course management software
	
	Copyright (c) 2004-2006 Dokeos S.A.
	Copyright (c) E.U.I. Universidad Politécnica de Madrid (Spain)
	Copyright (c) Borja Nuñez Salinas - Programmer (bns@alumnos.upm.es)
	
	For a full list of contributors, see "credits.txt".
	The full license can be read in "license.txt".
	
	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.
	
	See the GNU General Public License for more details.
	
	Contact address: Dokeos, 44 rue des palais, B-1030 Brussels, Belgium
	Mail: info@dokeos.com
==============================================================================
*/
/**
==============================================================================
*	This script display the credits system menu, the current user credits and
*	the current subscriptions to credits courses.
*
*	@package dokeos.plugin
==============================================================================
*/
	
/*
==============================================================================
		INIT SECTION
==============================================================================
*/ 

$language_file = 'plugin_credits_system';

unset($_GET['cidReq']);
$cidReset = true;
 
include_once("../../main/inc/global.inc.php");
api_block_anonymous_users();

/*
-----------------------------------------------------------
	Libraries
-----------------------------------------------------------
*/ 
include_once('../../main/inc/lib/course.lib.php');
include_once('../../main/inc/lib/display.lib.php');

	
/*
-----------------------------------------------------------
	Header
-----------------------------------------------------------
*/ 

$tool_name = get_lang('CreditsSystem'); 
Display::display_header($tool_name);

	
/*
==============================================================================
		FUNCTIONS
==============================================================================
*/ 
	
/*
-----------------------------------------------------------
		RIGHT MENU
-----------------------------------------------------------
*/

function show_right_menu()
{
echo "<div class=\"menu\">";
echo "<div class=\"menusection\">";
echo "<span class=\"menusectioncaption\">".get_lang("CurrentCredits")."</span>";
echo "<ul class=\"menulist\">";
echo '<li>';
echo '<h3';
if (!cs_get_user_credits()){
	echo ' style="color:red;"';
}
echo'>'.get_lang('YouHave').' '.cs_get_user_credits().' '.get_lang('Credits').'.</h3>';
echo '</li>';
echo "</ul>";
echo "</div>";

echo "<div class=\"menusection\">";
echo "<span class=\"menusectioncaption\">".get_lang("CreditsSystem")."</span>";
echo "<ul class=\"menulist\">";



$user_navigation=array();

//Check the number of payment methods enabled by the admin.
$settings = cs_get_current_settings();
$payment_methods_num = 0;
for($i=0; $i<count($settings['cs_payment_methods']); $i++){
	if ($settings['cs_payment_methods'][$i]['selected_value'] == 'true')
	{
		$payment_methods_num++;
	}
}
//if there is at least one payment method enabled add buy credits link else just admin could add credits (Cash).
if ($payment_methods_num)
{
	// Link to Buy Credit
	$user_navigation['buycredits']['url'] = api_get_path(WEB_PLUGIN_PATH).'credits_system/buy_credits.php';
	$user_navigation['buycredits']['title'] = get_lang('BuyCredits');
}
else
{
	// Link to Buy Credit
	$user_navigation['buycredits']['url'] = false;
	$user_navigation['buycredits']['title'] = get_lang('BuyCredits');
}

// Link to payment / Subscription history
$user_navigation['history']['url'] = api_get_path(WEB_PLUGIN_PATH).'credits_system/menu_history.php?view=subscription';
$user_navigation['history']['title'] = get_lang('History');

foreach($user_navigation as $section => $user_navigation_info)
{
	$current = ($section == $GLOBALS['this_section'] ? ' id="current"' : '');
	echo '<li'.$current.'>';
	if ($user_navigation_info['url'])
	{
		echo '<a href="'.$user_navigation_info['url'].'" target="_top">'.$user_navigation_info['title'].'</a>';
	}
	else
	{
		echo $user_navigation_info['title'];
	}
	echo '</li>';
	echo "\n";
}

echo "</ul>";
echo "</div>";

$user_info = api_get_user_info(api_get_user_id());
if($user_info['status'] == 1)
{
	echo "<div class=\"menusection\">";
	echo "<span class=\"menusectioncaption\">".get_lang("TeacherMenu")."</span>";
	echo "<ul class=\"menulist\">";
	
	$teacher_navigation=array();

	$settings = cs_get_current_settings('cs_allow_teacher');
	if(api_is_platform_admin() || ($settings == 'yes'))
	{
		// Link to manage course credits
		$teacher_navigation['cscourse']['url'] = api_get_path(WEB_PLUGIN_PATH).'credits_system/manage_course_credits.php?table=pay&view=teacher';
		$teacher_navigation['cscourse']['title'] = get_lang('ManageCourseCredits');
	}
	else
	{
		// Link to manage course credits
		$teacher_navigation['cscourse']['url'] = false;
		$teacher_navigation['cscourse']['title'] = get_lang('ManageCourseCredits');
	}

	// Link to payment / Subscription history
	$teacher_navigation['cshistory']['url'] = api_get_path(WEB_PLUGIN_PATH).'credits_system/courses_historics_teacher.php';
	$teacher_navigation['cshistory']['title'] = get_lang('MyCoursesHistory');
	
	foreach($teacher_navigation as $section => $teacher_navigation_info)
	{
		$current = ($section == $GLOBALS['this_section'] ? ' id="current"' : '');
		echo '<li'.$current.'>';
		if ($teacher_navigation_info['url'])
		{
			echo '<a href="'.$teacher_navigation_info['url'].'" target="_top">'.$teacher_navigation_info['title'].'</a>';
		}
		else
		{
			echo $teacher_navigation_info['title'];
		}
		echo '</li>';
		echo "\n";
	}

	echo "</ul>";
	echo "</div>";
}

if(api_is_platform_admin())
{
	echo "<div class=\"menusection\">";
	echo "<span class=\"menusectioncaption\">".get_lang("AdminMenu")."</span>";
	echo "<ul class=\"menulist\">";
	
	$admin_navigation=array();
	// Link to Credit system settings
	$admin_navigation['cssettings']['url'] = api_get_path(WEB_PLUGIN_PATH).'credits_system/credits_system_settings.php?select=csgeneraloptions';
	$admin_navigation['cssettings']['title'] = get_lang('Settings');
	// Link to manage user credits
	$admin_navigation['csuser']['url'] = api_get_path(WEB_PLUGIN_PATH).'credits_system/manage_user_credits.php';
	$admin_navigation['csuser']['title'] = get_lang('ManageUserCredits');
	// Link to manage course credits
	$admin_navigation['cscourse']['url'] = api_get_path(WEB_PLUGIN_PATH).'credits_system/manage_course_credits.php?table=pay&view=admin';
	$admin_navigation['cscourse']['title'] = get_lang('ManageCourseCredits');
	// Link to payment / Subscription history
	$admin_navigation['cshistory']['url'] = api_get_path(WEB_PLUGIN_PATH).'credits_system/admin_menu_historics.php?view=course';
	$admin_navigation['cshistory']['title'] = get_lang('PlatformHistory');
	// Link to Statistics
	$admin_navigation['csstatistics']['url'] = api_get_path(WEB_PLUGIN_PATH).'credits_system/menu_statistics.php';
	$admin_navigation['csstatistics']['title'] = get_lang('Statistics');
	
	foreach($admin_navigation as $section => $admin_navigation_info)
	{
	$current = ($section == $GLOBALS['this_section'] ? ' id="current"' : '');
	echo '<li'.$current.'>';
	echo '<a href="'.$admin_navigation_info['url'].'" target="_top">'.$admin_navigation_info['title'].'</a>';
	echo '</li>';
	echo "\n";
	}

	echo "</ul>";
	echo "</div>";
}
echo "</div>";
}

/*
==============================================================================
		MAIN CODE
==============================================================================
*/ 

$tool_name = get_lang('MyCreditsCourses').':';
api_display_tool_title($tool_name);

show_right_menu();
echo '<div style="width:79%">';
include_once('./my_credits_courses_info.php');
echo '</div>';

/*
==============================================================================
		FOOTER 
==============================================================================
*/ 
Display::display_footer();
?>