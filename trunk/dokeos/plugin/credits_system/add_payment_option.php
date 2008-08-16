<?php
// $Id: add_payment_option.php 10926 2007-03-25 11:30:47Z ana $
/*
==============================================================================
	Dokeos - elearning and course management software

	Copyright (c) 2004 Dokeos S.A.
	Copyright (c) 2003 Ghent University (UGent)
	Copyright (c) 2001 Universite catholique de Louvain (UCL)
	Copyright (c) Olivier Brouckaert

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
*	@package dokeos.admin
==============================================================================
*/

/*
==============================================================================
		INIT SECTION
==============================================================================
*/
$language_file = 'plugin_credits_system';
include("../../main/inc/global.inc.php");
require_once(dirname(__FILE__).'/inc/cs_functions.inc.php');
require_once (api_get_path(LIBRARY_PATH).'/formvalidator/FormValidator.class.php');
$interbreadcrumb[] = array ("url" => api_get_path(PLUGIN_PATH).'my_credits.php', "name" => get_lang('CreditsSystem'));
$interbreadcrumb[] = array ("url" => api_get_path(PLUGIN_PATH).'credits_system_settings.php?select=cspaymentoptions', "name" => get_lang('Settings'));
$tool_name = "Add Payment Option"; // title of the page (should come from the language file) 

/*
-----------------------------------------------------------
	Libraries
-----------------------------------------------------------
*/
include_once(dirname(__FILE__).'/inc/cs_database.lib.php');

/*
==============================================================================
		MAIN CODE
==============================================================================
*/ 

// Put your main code here. Keep this section short,
// it's better to use functions for any non-trivial code

$different_payment_options = array(1 => 'day',2 =>'week',3 => 'month',4 =>'year' );
$form = new FormValidator('add_payment_options');
$form->addElement('select', 'options', get_lang('DifferentOptions'), $different_payment_options);
$form->add_textfield('optionlength', get_lang('Length'),false, array ('size' => '4','maxlength' => '3'));
$form->addElement('submit', null, get_lang('Ok'));
// Validate form
if( $form->validate())
{
	$add_option = $form -> exportValues();
	$length = $add_option['optionlength'];
	$opt = $add_option['options'];
	$payment_options_table = Database :: get_main_table(CS_TABLE_PAYMENT_OPTION);
	if (($length != '') AND (($length >= "1") AND ($length <= "1000000000")) ) 
	{
		$sql = " SELECT COUNT(*) FROM $payment_options_table";
		$sql .= " WHERE (name = $opt) AND (amount = $length)";
		$res = api_sql_query($sql,__FILE__,__LINE__);
		$number = mysql_fetch_row($res);
		if ($number[0] == 0)//if the payment option doesn't exit
		{
			$sql = "INSERT INTO $payment_options_table (name, amount) VALUES ($opt,$length)";
			api_sql_query($sql,__FILE__,__LINE__);
			header('Location: credits_system_settings.php?select=cspaymentoptions');
		}
		else //the payment option exists
		{
			Display::display_header($tool_name);
			api_display_tool_title($tool_name);
			$message = 	get_lang('PaymentOptionExist');
			Display :: display_normal_message($message);
			$form->display();
			Display::display_footer();
		}
	}
	else
		{
			Display::display_header($tool_name);
			api_display_tool_title($tool_name);
			$message = 	get_lang('Incorrect');
			Display :: display_normal_message($message);
			$form->display();
			Display::display_footer();
		}
	exit;
}

Display::display_header($tool_name);

api_display_tool_title($tool_name);
$form->display();

/*
==============================================================================
		FOOTER 
==============================================================================
*/ 
Display::display_footer();

?>