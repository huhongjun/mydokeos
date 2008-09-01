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
$tool_name = get_lang('EditPaymentOption');

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

//Array with the different payment options
$different_payment_options = array(1 => '天',2 =>'周',3 => '月',4 =>'年' );//edit by xiaoping

if (isset ($_GET['option_id']))
{
	$option_id = $_GET['option_id'];
	$payment_options_table = Database :: get_main_table(CS_TABLE_PAYMENT_OPTION);
	$sql = "SELECT name, amount FROM $payment_options_table WHERE option_id = $option_id";
	$res = api_sql_query($sql, __FILE__, __LINE__);
	//edit by xiaoping
	$options = mysql_fetch_array($res);
	switch ($options['name'])
	{
		case 'day':$opti = '天';break;
		case 'week':$opti = '周';break;
		case 'month':$opti = '月';break;
		case 'year':$opti = '年';
	}
	$leng = $options['amount'];

}

for($i=1; $i <= sizeof($different_payment_options); $i++)
{
	if ( $different_payment_options[$i] == $opti )
	{
		$num = $i;
	}
}

$defaults['options'] = $num;
$defaults['optionlength'] = $leng;


$form = new FormValidator('edit_payment_options');
$form -> addElement('select', 'options', get_lang('DifferentOptions'), $different_payment_options);
$form -> add_textfield('optionlength', get_lang('Length'),false, array ('size' => '3','maxlength' => '3'));
$form -> addRule('optionlength',get_lang('TheMaxLengthIs3'),'maxlength',3);
$form -> addElement('hidden','option_code',$option_id);
$form -> setDefaults($defaults);
$form -> addElement('submit', 'submit', get_lang('Ok'));

if( $form->validate())
{
	$edit_option = $form -> exportValues();
	$option_code = $edit_option['option_code'];
	$length = $edit_option['optionlength'];
	$option = $edit_option['options'];
	$payment_options_table = Database :: get_main_table(CS_TABLE_PAYMENT_OPTION);
	if (($length >= "1") AND ($length <= "1000000000"))
	{
		$sql = " SELECT COUNT(*) FROM $payment_options_table";
		$sql .= " WHERE (name = $option) AND (amount = $length)";
		$res = api_sql_query($sql,__FILE__,__LINE__);
		$number = mysql_fetch_row($res);
		if ($number[0] == 0)//if the apyment option doesn't exist
		{
			cs_update_platform_options_history($option_code);
			$result_option = $different_payment_options[$option];
			//edit by xiaoping
			switch ($result_option)
			{
				case '天':$result_option = 'day';break;
				case '周':$result_option = 'week';break;
				case '月':$result_option = 'month';break;
				case '年':$result_option = 'year';
			}
			$payment_options_table = Database :: get_main_table(CS_TABLE_PAYMENT_OPTION);
			$sql = "UPDATE $payment_options_table SET name = '".mysql_real_escape_string($result_option)."', amount = '".mysql_real_escape_string($length)."' WHERE option_id = '".mysql_real_escape_string($option_code)."'";
			api_sql_query($sql, __FILE__, __LINE__);
			header('Location: credits_system_settings.php?select=cspaymentoptions');
		}

		else //if the apyment option exist in the database
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
	exit();
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