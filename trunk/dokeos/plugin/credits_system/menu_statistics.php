<?php
// $Id: index.php 8216 2007-4-3 18:03:15 ana $
/*
==============================================================================
	Dokeos - elearning and course management software

	Copyright (c) 2006 Bart Mollet <bart.mollet@hogent.be>

	For a full list of contributors, see "credits.txt".
	The full license can be read in "license.txt".

	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	See the GNU General Public License for more details.

	Contact: Dokeos, 181 rue Royale, B-1000 Brussels, Belgium, info@dokeos.com
==============================================================================
*/
/**
==============================================================================
* This tool allows the use statistics
* @package dokeos.statistics
==============================================================================
*/

/*
==============================================================================
		INIT SECTION
==============================================================================
*/
// name of the language file that needs to be included
$language_file = 'plugin_credits_system';
include("../../main/inc/global.inc.php");
api_protect_admin_script();

/*
-----------------------------------------------------------
	Libraries
-----------------------------------------------------------
*/
include_once(dirname(__FILE__).'/cs_database.lib.php');
require_once ('cs_statistics.lib.php');

/*
-----------------------------------------------------------
	Header
-----------------------------------------------------------
*/ 
$interbreadcrumb[] = array ("url" => api_get_path(PLUGIN_PATH).'my_credits.php', "name" => get_lang('CreditsSystem'));
$tool_name = get_lang('Statistics');
Display::display_header($tool_name);
api_display_tool_title($tool_name);

/*
==============================================================================
		MAIN CODE
==============================================================================
*/ 
$strCredits  = get_lang('credits');

	
$tools[$strCredits]['action=credits&amp;type=month'] = get_lang('Credits').' ('.get_lang('AmountCreditsMonth').')'; 
$tools[$strCredits]['action=credits&amp;type=year'] = get_lang('Credits').' ('.get_lang('AmountCreditsYear').')';


echo '<table><tr>';
foreach($tools as $section => $items)
{
	echo '<td valign="top">';
	echo '<b>'.$section.'</b>';
	echo '<ul>';
	foreach($items as $key => $value)
	{		
			echo '<li><a href="menu_statistics.php?'.$key.'">'.$value.'</a></li>';
	}
	echo '</ul>';
	echo '</td>';
}
echo '</tr></table>';

switch($_GET['action'])
{
	case 'credits':
		CreditSystem::print_credits_stats($_GET['type']);
		break;
	case 'users':
		CreditSystem::print_credits_users_stats($_GET['type']);
		break;
}

/*
==============================================================================
		FOOTER 
==============================================================================
*/
Display::display_footer();
?>