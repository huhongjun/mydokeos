<?php // $Id: template.php,v 1.2 2006/03/15 14:34:45 pcool Exp $
/*
==============================================================================
	Dokeos - elearning and course management software
	
	Copyright (c) 2007 E.U.I. Universidad Politécnica de Madrid (Spain)
	Copyright (c) 2004-2006 Dokeos S.A.
	
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
*	This file is the payment confirmation script.
*
*	Shows a payment cofirmation independent of the payment method used.
*	Redirects to the page where the user begun the payment
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
include_once("../../main/inc/global.inc.php");

api_block_anonymous_users(); 

/*
-----------------------------------------------------------
	Libraries
-----------------------------------------------------------
*/ 
//the main_api.lib.php, database.lib.php and display.lib.php
//libraries are included by default

require_once ('./inc/cs_functions.inc.php');
require_once (api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php');

/*
-----------------------------------------------------------
	Header
-----------------------------------------------------------
*/ 

if (isset($_POST['go_back']) && $_POST['go_back']!='' )
{
	$go_back = $_POST['go_back'];
	//fill interbreadcrumb
	if (ereg('subscribe=1', $_POST['go_back']))
	{
		$interbreadcrumb[] = array ("url" => api_get_path(REL_CLARO_PATH).'auth/courses.php?action=subscribe&category='.$_GET['category'].'&up=', "name" => get_lang('CourseManagement'));
		$interbreadcrumb[] = array ("url" => $_POST['go_back'], "name" => get_lang('Subscribe'));
	}
	else
	{
		$interbreadcrumb[] = array ("url" => api_get_path(WEB_PATH).'user_portal.php', "name" => get_lang('MyCourses'));
		$interbreadcrumb[] = array ("url" => $_POST['go_back'], "name" => get_lang('RenewSubscription'));
	}
}
else
{
	$interbreadcrumb[] = array ("url" => api_get_path(PLUGIN_PATH).'my_credits.php', "name" => get_lang('CreditsSystem'));
	$go_back = api_get_path(WEB_PLUGIN_PATH).'credits_system/my_credits.php';
}


$tool_name = get_lang('BuyCredits'); 
Display::display_header($tool_name);

/*
-----------------------------------------------------------
	Title
-----------------------------------------------------------
*/
	
$tool_name = get_lang('PaymentInfo'); 
api_display_tool_title($tool_name);
	
	
/*
==============================================================================
		MAIN CODE
==============================================================================
*/ 

if (!isset($_POST['error']))
{
	$settings = cs_get_current_settings();
	$cost_per_credit = $settings['cs_cost_per_credit'][0]['selected_value'];


	$message = get_lang('PaymentDone').': <br /><br />';
	$message .= get_lang('AccountFirstName').': '.$_POST['first_name'].'<br />';
	$message .= get_lang('AccountLastName').': '.$_POST['last_name'].'<br />';
	$message .= get_lang('CreditsBought').': '.$_POST['credits'].'<br />';
	$message .= get_lang('PaymentAmount').': '.$_POST['credits']*$cost_per_credit.' '.get_lang($settings['cs_currency'][0]['selected_value'].'Symbol').'<br />';


	$message .= '<br /><a href="'.$go_back.'">'.get_lang('Continue').'</a>';

	Display::display_normal_message($message);
}
else
{
	Display::display_error_message(get_lang('PaymentMethodError'));
}


/*
==============================================================================
		FOOTER 
==============================================================================
*/ 
Display::display_footer();
?>