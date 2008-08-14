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
require_once (api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php');

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
api_block_anonymous_users();
$interbreadcrumb[] = array ("url" => api_get_path(PLUGIN_PATH).'my_credits.php', "name" => get_lang('CreditsSystem'));
$tool_name = get_lang('BuyCredits'); // title of the page (should come from the language file) 
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
==============================================================================
		MAIN CODE
==============================================================================
*/ 

// Put your main code here. Keep this section short,
// it's better to use functions for any non-trivial code
$interbreadcrumb[] = array ("url" => api_get_path(PLUGIN_PATH).'my_credits.php', "name" => get_lang('CreditsSystem'));
$tool_name = get_lang('BuyCredits');

api_display_tool_title($tool_name);


$user_id = api_get_user_id();
$user_info = api_get_user_info($user_id);

//echo get_lang('Hello').' '.$user_info["firstName"].' <hr />';


$form = new FormValidator('creditsAmount');
$form->addElement('text', 'amount',get_lang('Amountofcreditstobuy').': ','size="1" maxLength="8"');

$form->addRule('amount','Enter a valid amount','required');
$form->addRule('amount','Enter a valid amount','nonzero');
$form->registerRule('valid_amount','regex','/^\d*\.{0,1}\d+$/');

$form->addRule('amount','Enter a positive number','valid_amount'); 

$form->addElement('submit','buy','buy');
if ($form->validate()){
	$amount = $form->exportValues();
	if((cs_pay($amount['amount']))&&(cs_set_user_credits(cs_get_user_credits()+$amount['amount'])))
	{
		Display::display_confirmation_message(get_lang('Thx').".<br>".get_lang('YouHave').' '.cs_get_user_credits().' '.get_lang('credits').'.');
	}else
	{
		Display::display_error_message(get_lang('Error during the operation'));
	}
}
$form->display();
//echo '<br/><input type=button value="Back" onClick="history.go(-2)">';
/*
==============================================================================
		FOOTER 
==============================================================================
*/ 
Display::display_footer();
?>