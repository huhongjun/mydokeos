<?php
// $Id: payment_data.php 10920 2007-03-16 10:55:37Z ana $
/*
==============================================================================
	Dokeos - elearning and course management software

	Copyright (c) 2004 Dokeos S.A.
	Copyright (c) 2003 Ghent University (UGent)
	Copyright (c) 2001 Universite catholique de Louvain (UCL)
	Copyright (c) Olivier Brouckaert
	Copyright (c) Bart Mollet, Hogeschool Gent

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

// name of the language file that needs to be included 
$language_file = 'plugin_credits_system';
api_display_tool_title(get_lang('PaymentData'));
require ('../../main/inc/global.inc.php');
require_once (api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php');
require_once (api_get_path(LIBRARY_PATH).'sortabletable.class.php');

/*
-----------------------------------------------------------
	Libraries
-----------------------------------------------------------
*/
include_once(dirname(__FILE__).'/inc/cs_database.lib.php');
require_once (api_get_path(LIBRARY_PATH)."course.lib.php");

/*
==============================================================================
		FUNCTIONS
==============================================================================
*/

//Get the number of payments of the logged-in user
function get_number_of_payment()
{
	$user_id = api_get_user_id ();
	$payment = Database :: get_main_table(CS_TABLE_PAYMENT);
	$sql = "SELECT COUNT(payment_id) AS total_number_of_items FROM $payment" ;
	$sql .= "WHERE user_id = $user_id";
	$res = api_sql_query($sql, __FILE__, __LINE__);
	$obj = mysql_fetch_object($res);
	return $obj->total_number_of_items;
}

//Get the payment data of the logged-in user 
function get_historic_data($from, $number_of_items, $column, $direction)
{
	$user_id = api_get_user_id ();
	$payment = Database :: get_main_table(CS_TABLE_PAYMENT);
	$sql = " SELECT DATE_FORMAT(date, '%Y-%m-%d') AS col0, credits AS col1,amount AS col2, payment_method AS col3 FROM $payment";
	$sql .= " WHERE($user_id = user_id)";
	$sql .= " ORDER BY col$column $direction ";
	$sql .= " LIMIT $from,$number_of_items ";
	$res = api_sql_query($sql, __FILE__, __LINE__);
	$show = array ();
	while ($show2 = mysql_fetch_array($res))
		$show[] = $show2;
	echo "<div align=\"right\">";
	echo '<a href="menu_history.php?view=payment&code='.$user_id.'&action=export&type=csv&column='.$column.'&direction='.$direction.'"><img align="absbottom" src="../../main/img/file_xls.gif">'.get_lang('ExportAsCSV').'</a>';
	echo "</div>";
	return $show;
}

/*
==============================================================================
		MAIN CODE
==============================================================================
*/

//Menu
echo '<a href = '.api_get_path(WEB_PLUGIN_PATH).'credits_system/menu_history.php?view=subscription>'.get_lang('CoursesSubscription').'</a> | ';
echo '<a href = '.api_get_path(WEB_PLUGIN_PATH).'credits_system/menu_history.php?view=payment>'.get_lang('PaymentUser').'</a>';

// Create a sortable table with the payment data
$parameters['view'] = $_GET['view'];
$table = new SortableTable('payment_history', 'get_number_of_payment', 'get_historic_data',2);
$table->set_additional_parameters($parameters);
$table->set_header(0, get_lang('DatePayment'));
$table->set_header(1, get_lang('Amount'));
$table->set_header(2, get_lang('Price'));
$table->set_header(3, get_lang('PaymentMethod'));
$table->display();

/*
==============================================================================
		FOOTER 
==============================================================================
*/
Display :: display_footer();
?>