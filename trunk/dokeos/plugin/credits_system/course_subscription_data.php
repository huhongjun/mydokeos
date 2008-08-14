<?php
// $Id: course_subscription_data.php 10926 2007-03-25 11:30:47Z ana $
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
		$code = $_GET['code'];
		$direction = $_GET['direction'];
		$payment_option_table = Database :: get_main_table(CS_TABLE_PAYMENT_OPTION);
		$user_table = Database :: get_main_table(TABLE_MAIN_USER);
		$course_credits_rel_user_credits_table = Database :: get_main_table(CS_TABLE_COURSE_CREDITS_REL_USER_CREDITS);
		$table_course_credits = Database :: get_main_table(CS_TABLE_COURSE_CREDITS);
		$table_options_history = Database :: get_main_table(CS_TABLE_OPTIONS_HISTORY);
		$sql = "SELECT lastname AS col0,firstname AS col1,DATE_FORMAT(init_date, '%Y-%m-%d') AS col2,DATE_FORMAT(end_date, '%Y-%m-%d') AS col3,IF(T5.amount IS NULL, T3.amount,T5.amount) AS col4,IF(T5.name IS NULL, T3.name,T5.name) AS col5,IF(T5.credits IS NULL, T4.credits,T5.credits) AS col6  FROM $course_credits_rel_user_credits_table T2";
		$sql .= " LEFT JOIN $table_options_history T5 ON (T2.options_history_id = T5.options_history_id) LEFT JOIN $table_course_credits T4 ON (T4.code = T2.code) AND (T4.option_id = T2.option_id) LEFT JOIN $payment_option_table T3 ON T2.option_id = T3.option_id";
		$sql .= " LEFT JOIN $user_table T1 ON T1.user_id = T2.user_id";
		$sql .= " WHERE T2.code = '$code'";
		$sql .= " ORDER BY col$column $direction";
		$res = api_sql_query($sql, __FILE__, __LINE__);
		$num_rows = mysql_num_rows($res);
		if ($num_rows >= 1)
		{
			$alldata[]= array (
				'Last name',
				'First name',
				'Init date',
				'Final date',
				'Length',
				'Option',
				'Amount of credits'
			);
			while ($result = mysql_fetch_array($res, MYSQL_ASSOC))
				{
					$alldata[] = $result;
				}
			$filename = 'Subscription_Data_Of_My_Courses'.'_'.date('Y-m-d_H-i-s');
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
$interbreadcrumb[] = array ("url" => api_get_path(PLUGIN_PATH).'courses_historics_teacher.php', "name" => get_lang('MyCoursesHistory'));
$tool_name = get_lang('SubscriptionDataOfMyCourses');
Display :: display_header($tool_name);

if( isset ($_GET['action']))
{
	if ($_GET['type']=='csv')
	{
		$column = $_GET['column'];
		$code = $_GET['code'];
		$direction = $_GET['direction'];
		$payment_option_table = Database :: get_main_table(CS_TABLE_PAYMENT_OPTION);
		$user_table = Database :: get_main_table(TABLE_MAIN_USER);
		$course_credits_rel_user_credits_table = Database :: get_main_table(CS_TABLE_COURSE_CREDITS_REL_USER_CREDITS);
		$table_course_credits = Database :: get_main_table(CS_TABLE_COURSE_CREDITS);
		$table_options_history = Database :: get_main_table(CS_TABLE_OPTIONS_HISTORY);
		$sql = "SELECT lastname AS col0,firstname AS col1,DATE_FORMAT(init_date, '%Y-%m-%d') AS col2,DATE_FORMAT(end_date, '%Y-%m-%d') AS col3,IF(T5.amount IS NULL, T3.amount,T5.amount) AS col4,IF(T5.name IS NULL, T3.name,T5.name) AS col5,IF(T5.credits IS NULL, T4.credits,T5.credits) AS col6  FROM $course_credits_rel_user_credits_table T2";
		$sql .= " LEFT JOIN $table_options_history T5 ON (T2.options_history_id = T5.options_history_id) LEFT JOIN $table_course_credits T4 ON (T4.code = T2.code) AND (T4.option_id = T2.option_id) LEFT JOIN $payment_option_table T3 ON T2.option_id = T3.option_id";
		$sql .= " LEFT JOIN $user_table T1 ON T1.user_id = T2.user_id";
		$sql .= " WHERE T2.code = '$code'";
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

api_display_tool_title(get_lang('SubscriptionDataOfMyCourses'));

/*
==============================================================================
		FUNCTIONS
==============================================================================
*/

//Get the number of subscriptions of one course
function get_number_of_subscription_for_a_course()
	{
		if (isset ($_GET['code']))
		{
			$code = $_GET['code'];
		}
		$course_credits_rel_user_credits_table = Database :: get_main_table(CS_TABLE_COURSE_CREDITS_REL_USER_CREDITS);
		$sql = " SELECT COUNT(DISTINCT subscription_id) AS total_number_of_items FROM $course_credits_rel_user_credits_table";
		$sql .= " WHERE code = '$code'";
		$res = api_sql_query($sql, __FILE__, __LINE__);
		$obj = mysql_fetch_object($res);
		return $obj->total_number_of_items;
	}

//Get the subscription data of one course	
function get_subscription_data($from, $number_of_items, $column, $direction)
	{
		if (isset ($_GET['code']))
		{
			$code = $_GET['code'];
		}
		$payment_option_table = Database :: get_main_table(CS_TABLE_PAYMENT_OPTION);
		$user_table = Database :: get_main_table(TABLE_MAIN_USER);
		$course_credits_rel_user_credits_table = Database :: get_main_table(CS_TABLE_COURSE_CREDITS_REL_USER_CREDITS);
		$table_course_credits = Database :: get_main_table(CS_TABLE_COURSE_CREDITS);
		$table_options_history = Database :: get_main_table(CS_TABLE_OPTIONS_HISTORY);
		$sql = "SELECT lastname AS col0,firstname AS col1,DATE_FORMAT(init_date, '%Y-%m-%d') AS col2,DATE_FORMAT(end_date, '%Y-%m-%d') AS col3,IF(T5.amount IS NULL, T3.amount,T5.amount) AS col4,IF(T5.name IS NULL, T3.name,T5.name) AS col5,IF(T5.credits IS NULL, T4.credits,T5.credits) AS col6  FROM $course_credits_rel_user_credits_table T2";
		$sql .= " LEFT JOIN $table_options_history T5 ON (T2.options_history_id = T5.options_history_id) LEFT JOIN $table_course_credits T4 ON (T4.code = T2.code) AND (T4.option_id = T2.option_id) LEFT JOIN $payment_option_table T3 ON T2.option_id = T3.option_id";
		$sql .= " LEFT JOIN $user_table T1 ON T1.user_id = T2.user_id";
		$sql .= " WHERE T2.code = '$code'";
		$sql .= " ORDER BY col$column $direction ";
		$sql .= " LIMIT $from,$number_of_items";
		$res = api_sql_query($sql, __FILE__, __LINE__);
		$subscritions = array();
		while ($subscription = mysql_fetch_row($res))
		{
			$subscriptions[] = $subscription;
		}
		echo "<div align=\"right\">";
		echo '<a href="course_subscription_data.php?code='.$code.'&action=export&type=csv&column='.$column.'&direction='.$direction.'"><img align="absbottom" src="../../main/img/file_xls.gif">'.get_lang('ExportAsCSV').'</a>';
		echo "</div>";

		return $subscriptions;
	}

/*
==============================================================================
		MAIN CODE
==============================================================================
*/ 

// Put your main code here. Keep this section short,
// it's better to use functions for any non-trivial code

// Create a sortable table with the course's subscription data
		$table = new SortableTable('subscritions', 'get_number_of_subscription_for_a_course', 'get_subscription_data',2);
		$parameters['code'] = $_GET['code'];
		$table->set_additional_parameters($parameters);
		$table->set_header(0, get_lang('LastName'));
		$table->set_header(1, get_lang('FirstName'));
		$table->set_header(2, get_lang('InitDate'));
		$table->set_header(3, get_lang('FinalDate'));
		$table->set_header(4, get_lang('Length'));
		$table->set_header(5, get_lang('Option'));
		$table->set_header(6, get_lang('AmountOfCredits'));
		$table->display();

/*
==============================================================================
		FOOTER
==============================================================================
*/
Display :: display_footer();
?>