<?php // $Id: credits_system_settings.php,v 1.0 2007/04/19 14:34:45 poty Exp $
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
*	This script display the settings menu and load by default
*	the credits system general settings.
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
include("../../main/inc/global.inc.php");
api_protect_admin_script(); 
	
/*
-----------------------------------------------------------
	Header
-----------------------------------------------------------
*/ 

$interbreadcrumb[] = array ("url" => api_get_path(PLUGIN_PATH).'my_credits.php', "name" => get_lang('CreditsSystem'));
$tool_name = get_lang('Settings'); // title of the page (should come from the language file) 
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
function right_menu()
{
echo "<div class=\"menu\">";
echo "<div class=\"menusection\">";
echo "<span class=\"menusectioncaption\">".get_lang("CreditsSystem").' '.get_lang('settings')."</span>";
echo "<ul class=\"menulist\">";

$user_navigation=array();

//Link to set payment methods options
$user_navigation['cspaymentmethodsoptions']['url'] = api_get_path(WEB_PLUGIN_PATH).'credits_system/credits_system_settings.php?select=cspaymentmethodsoptions';
$user_navigation['cspaymentmethodsoptions']['title'] = get_lang('PaymentMethods');

// Link to payment options settings
$user_navigation['cspaymentoptions']['url'] = api_get_path(WEB_PLUGIN_PATH).'credits_system/credits_system_settings.php?select=cspaymentoptions';
$user_navigation['cspaymentoptions']['title'] = get_lang('PaymentOptions');

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

right_menu();
if(isset($_GET['select']))
{
	switch ($_GET['select']) {

		case 'cspaymentoptions':
			api_display_tool_title(get_lang('PaymentOptions'));
			//Including the script that will show and save the payments options.
			echo '<div style="width:79%">';
			require_once(dirname(__FILE__).'/payment_option.php');
			echo '</div>';
			break;
		case 'csgeneraloptions':
			api_display_tool_title(get_lang('GeneralOptions'));
			//Including the script that will show and save General credit system options.
			echo '<div style="width:79%">';
			require_once(dirname(__FILE__).'/general_options.php');
			echo'</div>';
			break;
		case 'cspaymentmethodsoptions':
			api_display_tool_title(get_lang('PaymentMethods'));
			//Including the script that will show and save payment methods options.
			echo '<div style="width:79%">';
			require_once(dirname(__FILE__).'/payment_methods_options.php');
			echo '</div>';
			break;	
		default:
   			api_display_tool_title(get_lang('OptionNotFound'));
	}
}	
else
{
	api_display_tool_title($tool_name);
}


/*
==============================================================================
		FOOTER 
==============================================================================
*/ 
Display::display_footer();
?>
