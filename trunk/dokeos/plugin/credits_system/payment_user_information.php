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
require_once (api_get_path(LIBRARY_PATH).'sortabletable.class.php');
/*
-----------------------------------------------------------
	Libraries
-----------------------------------------------------------
*/
include_once(dirname(__FILE__).'/cs_database.lib.php');
require_once (api_get_path(LIBRARY_PATH)."debug.lib.inc.php");
require_once (api_get_path(LIBRARY_PATH)."events.lib.inc.php");
require_once (api_get_path(LIBRARY_PATH)."export.lib.inc.php");
require_once (api_get_path(LIBRARY_PATH)."course.lib.php");

if( isset ($_GET['action']))
{
	if ($_GET['type']=='csv')
	{
		$column = $_GET['column'];
		$user_id = $_GET['code'];
		$direction = $_GET['direction'];
		$table_payment = 'cs_payment';
		//$table_payment = Database :: get_main_table(CS_TABLE_PAYMENT);
		$sql = " SELECT DATE_FORMAT(date, '%Y-%m-%d') AS col0, credits AS col1,amount AS col2, payment_method AS col3 FROM $table_payment";
		$sql .= " WHERE(user_id = '".$user_id."')";
		$sql .= " ORDER BY col$column $direction";
		$res = api_sql_query($sql, __FILE__, __LINE__);
		$num_rows = mysql_num_rows($res);
		if ($num_rows >= 1)
		{
			$alldata[]= array (
						'Date',
						'Amount of credits',
						'Price',
						'Payment method'
					);
			while ($result = mysql_fetch_array($res, MYSQL_ASSOC))
				{
					$alldata[] = $result;
				}
			$filename = 'Payment_Data_Per_User'.'_'.date('Y-m-d_H-i-s');
			Export::export_table_csv($alldata,$filename);
		}
	}
}

/*
-----------------------------------------------------------
	Header
-----------------------------------------------------------
*/ 
$interbreadcrumb[] = array ("url" => api_get_path(PLUGIN_PATH).'my_credits.php', "name" => get_lang('CreditsSystem'));
$interbreadcrumb[] = array ("url" => api_get_path(PLUGIN_PATH).'admin_menu_historics.php?view=paymentuser', "name" => get_lang('PlatformHistory'));
$tool_name = get_lang('PaymentDataPerUser');
Display::display_header($tool_name);

if( isset ($_GET['action']))
{
	if ($_GET['type']=='csv')
	{
		$column = $_GET['column'];
		$user_id = $_GET['code'];
		$direction = $_GET['direction'];
		$table_payment = 'cs_payment';
		//$table_payment = Database :: get_main_table(CS_TABLE_PAYMENT);
		$sql = " SELECT DATE_FORMAT(date, '%Y-%m-%d') AS col0, credits AS col1,amount AS col2, payment_method AS col3 FROM $table_payment";
		$sql .= " WHERE(user_id = '".$user_id."')";
		$sql .= " ORDER BY col$column $direction";
		$res = api_sql_query($sql, __FILE__, __LINE__);
		$num_rows = mysql_num_rows($res);
		if ($num_rows == 0)
		{
			$message = 	get_lang('ThereIsNotData');
			Display :: display_normal_message($message);
		}
	}
}

api_display_tool_title(get_lang('PaymentDataPerUser'));

/*
==============================================================================
		FUNCTIONS
==============================================================================
*/

//Get the number of payment data of an user
function get_number_of_payment_for_an_user()
	{
	if (isset ($_GET['code']))
		{
			$user_id = $_GET['code'];
		}
	$table_payment = Database :: get_main_table(CS_TABLE_PAYMENT);
	$sql = " SELECT COUNT(payment_id) AS total_number_of_items FROM $table_payment";
	$sql .=	" WHERE user_id = '".$user_id."'";
	$res = api_sql_query($sql, __FILE__, __LINE__);
	$obj = mysql_fetch_object($res);
	return $obj->total_number_of_items;
	}

//Get the payment data of an user
function get_payment_data($from, $number_of_items, $column, $direction)
{
	if (isset ($_GET['code']))
		{
			$user_id = $_GET['code'];
		}
	$table_payment = Database :: get_main_table(CS_TABLE_PAYMENT);
	$sql = " SELECT DATE_FORMAT(date, '%Y-%m-%d') AS col0, credits AS col1,amount AS col2, payment_method AS col3 FROM $table_payment";
	$sql .= " WHERE(user_id = '".$user_id."')";
	$sql .= " ORDER BY col$column $direction ";
	$sql .= " LIMIT $from,$number_of_items ";
	$res = api_sql_query($sql, __FILE__, __LINE__);
	$show = array ();
	while ($show2 = mysql_fetch_array($res))
		$show[] = $show2;
	echo "<div align=\"right\">";
	echo '<a href="payment_user_information.php?code='.$user_id.'&action=export&type=csv&column='.$column.'&direction='.$direction.' "><img align="absbottom" src="../../main/img/file_xls.gif">'.get_lang('ExportAsCSV').'</a>';
	echo "</div>";
	return $show;
}

/*
==============================================================================
		MAIN CODE
==============================================================================
*/ 

// Put your main code here. Keep this section short,
// it's better to use functions for any non-trivial code

// Create a sortable table with the payment data per course
		$table = new SortableTable('payment_user','get_number_of_payment_for_an_user', 'get_payment_data' ,2);
		$parameters['code'] = $_GET['code'];
		$table->set_additional_parameters($parameters);
		$table->set_header(0,get_lang('Date'));
		$table->set_header(1, get_lang('AmountOfCredits'));
		$table->set_header(2, get_lang('Price'));
		$table->set_header(3, get_lang('PaymentMethod'));
		$table->display();


/*
==============================================================================
		FOOTER 
==============================================================================
*/ 
Display::display_footer();



?>