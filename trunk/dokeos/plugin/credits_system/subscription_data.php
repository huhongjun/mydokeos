<?php
// $Id: subscription_data.php 10920 2007-03-16 10:55:37Z ana $
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
api_display_tool_title(get_lang('CoursesSubscription'));
require ('../../main/inc/global.inc.php');
require_once (api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php');
require_once (api_get_path(LIBRARY_PATH).'sortabletable.class.php');

/*
-----------------------------------------------------------
	Libraries
-----------------------------------------------------------
*/
include_once('./inc/cs_database.lib.php');
require_once (api_get_path(LIBRARY_PATH)."course.lib.php");

/*
==============================================================================
		FUNCTIONS
==============================================================================
*/

//Get the number of subscriptions of the logged-in user
function get_number_of_subscriptions()
{
	$user_id = api_get_user_id ();
	$course_credits_rel_user_credits = Database :: get_main_table(CS_TABLE_COURSE_CREDITS_REL_USER_CREDITS);;
	$sql = " SELECT COUNT(subscription_id) AS total_number_of_items FROM $course_credits_rel_user_credits";
	$sql .= " WHERE (user_id = $user_id)";
	$res = api_sql_query($sql, __FILE__, __LINE__);
	$obj = mysql_fetch_object($res);
	return $obj->total_number_of_items;
}

//Get the subscription data of the logged-in user 
function get_subscription_data($from, $number_of_items, $column, $direction)
{
	$user_id = api_get_user_id ();
	$payment = Database :: get_main_table(CS_TABLE_PAYMENT);
	$course_credits_rel_user_credits = Database :: get_main_table(CS_TABLE_COURSE_CREDITS_REL_USER_CREDITS);
	$course_credits = Database :: get_main_table(CS_TABLE_COURSE_CREDITS);
	$payment_options = Database :: get_main_table(CS_TABLE_PAYMENT_OPTION);
	$course_table = Database :: get_main_table(TABLE_MAIN_COURSE);
	$table_options_history = Database :: get_main_table(CS_TABLE_OPTIONS_HISTORY);
	$sql = "SELECT T4.code AS col0,title AS col1,DATE_FORMAT(init_date, '%Y-%m-%d') AS col2,DATE_FORMAT(end_date, '%Y-%m-%d') AS col3,IF(T5.amount IS NULL, T3.amount,T5.amount) AS col4,IF(T5.name IS NULL, T3.name,T5.name) AS col5,IF(T5.credits IS NULL, T2.credits,T5.credits) AS col6 FROM $course_credits_rel_user_credits T1";
	$sql .= " LEFT JOIN $table_options_history T5 ON (T1.options_history_id = T5.options_history_id) LEFT JOIN $course_credits T2 ON T1.code = T2.code AND T1.option_id = T2.option_id LEFT JOIN $payment_options T3 ON T1.option_id = T3.option_id";
	$sql .= " LEFT JOIN $course_table T4 ON T1.code = T4.code";
	$sql .= " WHERE ($user_id = T1.user_id)";
	$sql .= " ORDER BY col$column $direction ";
	$sql .= " LIMIT $from,$number_of_items ";
	$res = api_sql_query($sql, __FILE__, __LINE__);
	$show = array ();
	while ($show2 = mysql_fetch_array($res))
		$show[] = $show2;
	echo "<div align=\"right\">";
	echo '<a href="menu_history.php?view=subscription&code='.$user_id.'&action=export&type=csv&column='.$column.'&direction='.$direction.'"><img align="absbottom" src="../../main/img/file_xls.gif">'.get_lang('ExportAsCSV').'</a>';
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
	
//Create a sortable table with the subscription data
$table = new SortableTable('subscription_history', 'get_number_of_subscriptions', 'get_subscription_data',2);
$parameters['view'] = $_GET['view'];
$table->set_additional_parameters($parameters);
$table->set_header(0, get_lang('CourseCode'));
$table->set_header(1, get_lang('TitleCourse'));
$table->set_header(2, get_lang('InitDate'));
$table->set_header(3, get_lang('FinalDate'));
$table->set_header(4, get_lang('Length'));
$table->set_header(5, get_lang('Option'));
$table->set_header(6, get_lang('CreditsAmount'));
$table->display();


/*
==============================================================================
		FOOTER 
==============================================================================
*/
Display :: display_footer();
?>