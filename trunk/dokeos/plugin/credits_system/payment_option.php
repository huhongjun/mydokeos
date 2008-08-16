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
// global settings initialisation 
// also provides access to main, database and display API libraries

$language_file = 'plugin_credits_system';
include("../../main/inc/global.inc.php");
require_once(dirname(__FILE__).'/inc/cs_functions.inc.php');
require_once (api_get_path(LIBRARY_PATH).'/formvalidator/FormValidator.class.php');
require_once (api_get_path(LIBRARY_PATH).'sortabletable.class.php');

/*
-----------------------------------------------------------
	Libraries
-----------------------------------------------------------
*/
include_once(dirname(__FILE__).'/inc/cs_database.lib.php');


/*
==============================================================================
		FUNCTIONS
==============================================================================
*/

//Get the number of payment options that exists in the database
function get_number_of_payment_option()
	{
		$payment_options_table = Database :: get_main_table(CS_TABLE_PAYMENT_OPTION);
		$sql = " SELECT COUNT(option_id) AS total_number_of_items FROM $payment_options_table";
		$res = api_sql_query($sql, __FILE__, __LINE__);
		$obj = mysql_fetch_object($res);
		return $obj->total_number_of_items;
	}

//Get the payment option data
function get_payment_option_data($from, $number_of_items, $column, $direction)
	{
		$payment_options_table = Database :: get_main_table(CS_TABLE_PAYMENT_OPTION);
		$sql = " SELECT option_id AS col0,amount  AS col1,name AS col2, option_id AS col3 FROM $payment_options_table";
		$sql .= " ORDER BY col$column $direction ";
		$sql .= " LIMIT $from,$number_of_items";
		$res = api_sql_query($sql, __FILE__, __LINE__);
		$options = array();
		while ($option = mysql_fetch_row($res))
		{
			$options[] = $option;
		}

		return $options;
	}


function modify_filter($code)
	{
		return
			'<a href="option_edit.php?&option_id='.$code.'"><img src="../../main/img/edit.gif" border="0" style="vertical-align: middle" title="'.get_lang('Edit').'" alt="'.get_lang('Edit').'"/></a>&nbsp;'.
			'<a href="credits_system_settings.php?select=cspaymentoptions&delete_option='.$code.'"  onclick="javascript:if(!confirm('."'".addslashes(htmlentities(get_lang("ConfirmYourChoice")))."'".')) return false;"><img src="../../main/img/delete.gif" border="0" style="vertical-align: middle" title="'.get_lang('Delete').'" alt="'.get_lang('Delete').'"/></a>';	
	}

if (isset($_GET['option']))
	{
		$option_code = $_GET['code'];
		if ($_GET['option']=='yes')
		{
			cs_update_platform_options_history($option_code);
			$payment_options_table = Database :: get_main_table(CS_TABLE_PAYMENT_OPTION);
			$sql = " DELETE FROM $payment_options_table WHERE $option_code = option_id";
			api_sql_query($sql,__FILE__,__LINE__);
			$course_credits_table = Database :: get_main_table(CS_TABLE_COURSE_CREDITS);
			$sql = " DELETE FROM $course_credits_table WHERE $option_code = option_id";
			api_sql_query($sql,__FILE__,__LINE__);
		}
		else if($_GET['option'] == 'no')
		{
			$default = $_GET['default'];
			$settings_current_table = Database :: get_main_table(TABLE_MAIN_SETTINGS_CURRENT);
			$sql = " UPDATE $settings_current_table SET selected_value = '0' WHERE variable = 'cs_default_payment_option'";
			api_sql_query($sql,__FILE__,__LINE__);
			cs_update_platform_options_history($option_code);
			$payment_options_table = Database :: get_main_table(CS_TABLE_PAYMENT_OPTION);
			$sql = " DELETE FROM $payment_options_table WHERE $option_code = option_id";
			api_sql_query($sql,__FILE__,__LINE__);
			$course_credits_table = Database :: get_main_table(CS_TABLE_COURSE_CREDITS);
			$sql = " DELETE FROM $course_credits_table WHERE $option_code = option_id";
			api_sql_query($sql,__FILE__,__LINE__);
		}
	}

if (isset ($_POST['action'])||isset($_GET['Payment_options_per_page']))
	{
		switch ($_POST['action'])
		{
			// Delete selected options
			case 'delete_options' :
				$option_id = $_POST['option'];
				if (count($option_id) > 0)
				{
					foreach ($option_id as $index => $option_code)
					{
						$payment_option_default = cs_get_current_settings('cs_default_payment_option');
						$payment_options_table = Database :: get_main_table(CS_TABLE_PAYMENT_OPTION);
						$course_credits_table = Database :: get_main_table(CS_TABLE_COURSE_CREDITS);
						$cs_course_credits_rel_user_credits_table = Database :: get_main_table(CS_TABLE_COURSE_CREDITS_REL_USER_CREDITS);
						$sql = " SELECT COUNT(*) FROM $course_credits_table";
						$sql .= " WHERE (option_id = $option_code)";
						$res = api_sql_query($sql,__FILE__,__LINE__);
						$number = mysql_fetch_row($res);
						$same_page = api_get_path(WEB_PATH).'plugin/credits_system/credits_system_settings.php?select=cspaymentoptions';
						$delete = api_get_path(WEB_PATH).'plugin/credits_system/credits_system_settings.php?select=cspaymentoptions&option=yes&code='.$option_code.'';
						$update = api_get_path(WEB_PATH).'plugin/credits_system/credits_system_settings.php?select=cspaymentoptions&option=no&code='.$option_code.'&default='.$payment_option_default.''; 
						$sql = "SELECT amount, name FROM $payment_options_table";
						$sql .= " WHERE option_id = $option_code";
						$res = api_sql_query($sql,__FILE__,__LINE__);
						while ($row = mysql_fetch_array($res))
						{
							$name = $row['name'];
							$amount = $row['amount'];
						}
						if (($number[0] == 0) && ($payment_option_default != $option_code)) //if the apyment option is not in use
						{
							cs_update_platform_options_history($option_code);
							$payment_options_table = Database :: get_main_table(CS_TABLE_PAYMENT_OPTION);
							$sql = " DELETE FROM $payment_options_table WHERE $option_code = option_id";
							api_sql_query($sql,__FILE__,__LINE__);
							$course_credits_table = Database :: get_main_table(CS_TABLE_COURSE_CREDITS);
							$sql = " DELETE FROM $course_credits_table WHERE $option_code = option_id";
							api_sql_query($sql,__FILE__,__LINE__);
						}
						else if (($number[0] != 0) && ($payment_option_default != $option_code))
						{	
							Display::display_warning_message(get_lang('ThePaymentOption').' '.$amount.' '.$name.' '.get_lang('IsInUse').', <a href="'.$same_page.'">'.get_lang('no').'</a> '.get_lang('or').' <a href="'.$delete.'">'.get_lang('yes').'</a>');
							//$sql = "UPDATE $payment_options_table SET enabled = '".get_lang('No')."' WHERE option_id = '".mysql_real_escape_string($option_code)."'";
							//api_sql_query($sql,__FILE__,__LINE__);
							//$apears = 1;
						}
						else if (($number[0] != 0) && ($payment_option_default == $option_code))
						{
							Display::display_warning_message(get_lang('ThePaymentOption').' '.$amount.' '.$name.' '.get_lang('IsInUseAndIsThePaymentOptionByDefault').', <a href="'.$same_page.'">'.get_lang('no').'</a> '.get_lang('or').' <a href="'.$update.'">'.get_lang('yes').'</a>');							
						}
						else if (($number[0] == 0) && ($payment_option_default == $option_code))
						{
							Display::display_warning_message(get_lang('ThePaymentOption').' '.$amount.' '.$name.' '.get_lang('IsThePaymentOptionByDefault').', <a href="'.$same_page.'">'.get_lang('no').'</a> '.get_lang('or').' <a href="'.$update.'">'.get_lang('yes').'</a>');							
						} 
					}
					
				}
				break;
		}
	}


if (isset ($_GET['delete_option']))
	{
		$payment_option_default = cs_get_current_settings('cs_default_payment_option');
		$option_id = $_GET['delete_option'];
		$payment_options_table = Database :: get_main_table(CS_TABLE_PAYMENT_OPTION);
		$course_credits_table = Database :: get_main_table(CS_TABLE_COURSE_CREDITS);
		$course_credits_rel_user_credits_table = Database :: get_main_table(CS_TABLE_COURSE_CREDITS_REL_USER_CREDITS);
		$sql = " SELECT COUNT(*) FROM $course_credits_table";
		$sql .= " WHERE (option_id = $option_id)";	
		//$sql .= " WHERE (option_id = $option_id) AND (end_date >= (SELECT CURDATE()))";
		$res = api_sql_query($sql,__FILE__,__LINE__);
		$number = mysql_fetch_row($res);
		$same_page = api_get_path(WEB_PATH).'plugin/credits_system/credits_system_settings.php?select=cspaymentoptions';
		$delete = api_get_path(WEB_PATH).'plugin/credits_system/credits_system_settings.php?select=cspaymentoptions&option=yes&code='.$option_id.'';
		$update = api_get_path(WEB_PATH).'plugin/credits_system/credits_system_settings.php?select=cspaymentoptions&option=no&code='.$option_id.'&default='.$payment_option_default.''; 
		$sql = "SELECT amount, name FROM $payment_options_table";
		$sql .= " WHERE option_id = $option_id";
		$res = api_sql_query($sql,__FILE__,__LINE__);
		while ($row = mysql_fetch_array($res))
		{
			$name = $row['name'];
			$amount = $row['amount'];
		}
		if (($number[0] == 0) && ($payment_option_default != $option_id)) //if the apyment option is not in use
		{
			cs_update_platform_options_history($option_code);
			$payment_options_table = Database :: get_main_table(CS_TABLE_PAYMENT_OPTION);
			$sql = " DELETE FROM $payment_options_table WHERE $option_id = option_id";
			api_sql_query($sql,__FILE__,__LINE__);
			$course_credits_table = Database :: get_main_table(CS_TABLE_COURSE_CREDITS);
			$sql = " DELETE FROM $course_credits_table WHERE $option_id = option_id";
			api_sql_query($sql,__FILE__,__LINE__);
		}
		else if (($number[0] != 0) && ($payment_option_default != $option_id))
		{	
			Display::display_warning_message(get_lang('ThePaymentOption').' '.$amount.' '.$name.' '.get_lang('IsInUse').', <a href="'.$same_page.'">'.get_lang('no').'</a> '.get_lang('or').' <a href="'.$delete.'">'.get_lang('yes').'</a>');
			//$sql = "UPDATE $payment_options_table SET enabled = '".get_lang('No')."' WHERE option_id = '".mysql_real_escape_string($option_code)."'";
		    //api_sql_query($sql,__FILE__,__LINE__);
			//$apears = 1;
		}
		else if (($number[0] != 0) && ($payment_option_default == $option_id))
		{
			Display::display_warning_message(get_lang('ThePaymentOption').' '.$amount.' '.$name.' '.get_lang('IsInUseAndIsThePaymentOptionByDefault').', <a href="'.$same_page.'">'.get_lang('no').'</a> '.get_lang('or').' <a href="'.$update.'">'.get_lang('yes').'</a>');							
		}
		else if (($number[0] == 0) && ($payment_option_default == $option_id))
		{
			Display::display_warning_message(get_lang('ThePaymentOption').' '.$amount.' '.$name.' '.get_lang('IsThePaymentOptionByDefault').', <a href="'.$same_page.'">'.get_lang('no').'</a> '.get_lang('or').' <a href="'.$update.'">'.get_lang('yes').'</a>');							
		} 
	}				
		

/*
==============================================================================
		MAIN CODE
==============================================================================
*/ 

// Put your main code here. Keep this section short,
// it's better to use functions for any non-trivial code

//Link to add a new payment option
echo '<a href = '.api_get_path(WEB_PLUGIN_PATH).'credits_system/add_payment_option.php>'.get_lang('AddPaymentOption').'</a>';

// Create a sortable table with the payment options
	$table = new SortableTable('payment_options', 'get_number_of_payment_option', 'get_payment_option_data',2);
	$parameters['select'] = $_GET['select'];
	$table->set_additional_parameters($parameters);
	$table->set_header(0,'', false);
	$table->set_header(1, get_lang('Length'));
	$table->set_header(2, get_lang('Option'));
	$table->set_header(3, '', false);
	$table->set_column_filter(3,'modify_filter');
	$table->set_form_actions(array ('delete_options' => get_lang('DeleteOptions')),'option');
	$table->display();



?>