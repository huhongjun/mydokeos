<?php // $Id: my_credits.php,v 1.2 2006/03/15 14:34:45 pcool Exp $
/*
==============================================================================
	Dokeos - elearning and course management software
	
	Copyright (c) 2004-2006 Dokeos S.A.
	Copyright (c) Sally "Example" Programmer (sally@somewhere.net)
	//add your name + the name of your organisation - if any - to this list
	
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
*	This file is a code template; 
*	copy the code and paste it in a new file to begin your own work.
*
*	@package dokeos.plugin
==============================================================================
*/
	
/*
==============================================================================
		INIT SECTION
==============================================================================
*/ 
// global settings initialisation 
// also provides access to main, database and display API libraries
$language_file = 'plugin_credits_system';
include("../../main/inc/global.inc.php");
//$language_interface = 'english';
api_protect_admin_script(); 

/*
-----------------------------------------------------------
	Libraries
-----------------------------------------------------------
*/ 
//the main_api.lib.php, database.lib.php and display.lib.php
//libraries are included by default

	
/*
-----------------------------------------------------------
	Header
-----------------------------------------------------------
*/ 

//	Optional extra http or html header
//	If you need to add some HTTP/HTML headers code 
//	like JavaScript functions, stylesheets, redirects, put them here.

// $httpHeadXtra[] = ""; 
// $httpHeadXtra[] = ""; 
//    ... 
// 
// $htmlHeadXtra[] = ""; 
// $htmlHeadXtra[] = ""; 
//    ... 

$interbreadcrumb[] = array ("url" => api_get_path(PLUGIN_PATH).'my_credits.php', "name" => get_lang('CreditsSystem'));
$tool_name = "Settings"; // title of the page (should come from the language file) 
Display::display_header($tool_name);

	
/*
==============================================================================
		FUNCTIONS
==============================================================================
*/ 

// put your functions here
// if the list gets large, divide them into different sections:
// display functions, tool logic functions, database functions	
// try to place your functions into an API library or separate functions file - it helps reuse

/*
------------------------------------------------------------------------------
		RIGHT MENU
------------------------------------------------------------------------------
*/
function right_menu()
{
echo "<div class=\"menu\">";
echo "<div class=\"menusection\">";
echo "<span class=\"menusectioncaption\">".get_lang("CreditsSystem").' '.get_lang('settings')."</span>";
echo "<ul class=\"menulist\">";

$user_navigation=array();
// Link to payment options settings
$user_navigation['cspaymentoptions']['url'] = api_get_path(WEB_PLUGIN_PATH).'credits_system/credits_system_settings.php?select=cspaymentoptions';
$user_navigation['cspaymentoptions']['title'] = get_lang('PaymentOptions');


// Link to set payment methods
//$user_navigation['cspaymentmethods']['url'] = api_get_path(WEB_PLUGIN_PATH).'credits_system/credits_system_settings.php?select=cspaymentmethods';
//$user_navigation['cspaymentmethods']['title'] = get_lang('PaymentMethods');

// Link to set cost per credit
//$user_navigation['cscostpercredit']['url'] = api_get_path(WEB_PLUGIN_PATH).'credits_system/credits_system_settings.php?select=cscostpercredit';
//$user_navigation['cscostpercredit']['title'] = get_lang('CostPerCredit');

// Link to set cost per credit
//$user_navigation['csallowteacher']['url'] = api_get_path(WEB_PLUGIN_PATH).'credits_system/credits_system_settings.php?select=csallowteacher';
//$user_navigation['csallowteacher']['title'] = get_lang('AllowTeacher');

// Link to set default options
//$user_navigation['csdefaultoptions']['url'] = api_get_path(WEB_PLUGIN_PATH).'credits_system/credits_system_settings.php?select=csdefaultoptions';
//$user_navigation['csdefaultoptions']['title'] = get_lang('DefaultOptions');

// Link to set general options
$user_navigation['csgeneraloptions']['url'] = api_get_path(WEB_PLUGIN_PATH).'credits_system/credits_system_settings.php?select=csgeneraloptions';
$user_navigation['csgeneraloptions']['title'] = get_lang('GeneralOptions');

$current=0;
foreach($user_navigation as $section => $user_navigation_info)
{
	echo '<li>';
	echo '<a href="'.$user_navigation_info['url'].'" target="_top">'.$user_navigation_info['title'].'</a>';
	echo '</li>';
	echo "\n";
}

echo "</ul>";
echo "</div>";
echo "</div>";
}
/**
 * Set up and Shows right menu
 * 
 */
 
/*
==============================================================================
		MAIN CODE
==============================================================================
*/ 

// Put your main code here. Keep this section short,
// it's better to use functions for any non-trivial code

right_menu();
if(isset($_GET['select']))
{
	switch ($_GET['select']) {
		case 'cspaymentoptions':
			api_display_tool_title(get_lang('PaymentOptions'));
			//Including the script that will show and save the payments options.
			require_once(dirname(__FILE__).'/payment_option.php');
			break;
		case 'csdefaultoptions':
			api_display_tool_title(get_lang('DefaultOptions'));
			//Including the script that will show and save the default options.
			require_once(dirname(__FILE__).'/default_options.php');
			break;
		case 'cspaymentmethods':
			api_display_tool_title(get_lang('PaymentMethods'));
			//Including the script that will show and save the payments Methods.
			require_once(dirname(__FILE__).'/payment_methods.php');
			break;
		case 'cscostpercredit':
			api_display_tool_title(get_lang('CostPerCredit'));
			//Including the script that will show and save the cost per credit.
			require_once(dirname(__FILE__).'/cost_per_credit.php');
			break;
		case 'csallowteacher':
			api_display_tool_title(get_lang('Teacheroptions'));
			//Including the script that will show and save if teacher is allowed to manage credits on his courses.
			require_once(dirname(__FILE__).'/allow_teacher.php');
			break;
		case 'csgeneraloptions':
			api_display_tool_title(get_lang('GeneralOptions'));
			//Including the script that will show and save if teacher is allowed to manage credits on his courses.
			require_once(dirname(__FILE__).'/general_options.php');
			break;	
		default:
   			api_display_tool_title('Option not found');
	}
}	
else
{
	/*$interbreadcrumb[] = array ("url" => 'index.php', "name" => get_lang('PlatformAdmin'));
	$interbreadcrumb[] = array ("url" => 'user_list.php', "name" => get_lang('UserList'));
	$tool_name = get_lang('SearchAUser');
	Display :: display_header($tool_name);
	$interbreadcrumb[] = array ("url" => 'index.php', "name" => get_lang('CreditsSystem'));
	$tool_name = 'Credits System Settings';*/
	api_display_tool_title($tool_name);
}


/*
==============================================================================
		FOOTER 
==============================================================================
*/ 
Display::display_footer();
?>
