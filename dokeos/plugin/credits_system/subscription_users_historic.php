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
include_once(dirname(__FILE__).'/cs_database.lib.php');
require_once(dirname(__FILE__).'/inc/cs_functions.inc.php');
require_once (api_get_path(LIBRARY_PATH).'sortabletable.class.php');

$interbreadcrumb[] = array ("url" => api_get_path(PLUGIN_PATH).'my_credits.php', "name" => get_lang('CreditsSystem'));
api_display_tool_title(get_lang('CreditsSpentPerUser'));
echo '<a href = '.api_get_path(WEB_PLUGIN_PATH).'credits_system/admin_menu_historics.php?view=course>'.get_lang('CreditsSpentPerCourse').'</a> | ';
echo '<a href = '.api_get_path(WEB_PLUGIN_PATH).'credits_system/admin_menu_historics.php?view=paymentuser>'.get_lang('PaymentOfUsers').'</a> | ';
echo '<a href = '.api_get_path(WEB_PLUGIN_PATH).'credits_system/admin_menu_historics.php?view=subscriptionuser>'.get_lang('CreditsSpendsPerUser').'</a>';

/*
==============================================================================
		MAIN CODE
==============================================================================
*/ 

// Put your main code here. Keep this section short,
// it's better to use functions for any non-trivial code

function get_number_of_subscritions()
	{
		//$course_credits_table = Database :: get_main_table(CS_TABLE_COURSE_CREDITS);
		//$table_user = Database::get_main_table(TABLE_MAIN_USER);
		$course_credits_rel_user_credits_table = Database :: get_main_table(CS_TABLE_COURSE_CREDITS_REL_USER_CREDITS);
		$sql = " SELECT COUNT(DISTINCT user_id) AS total_number_of_items FROM $course_credits_rel_user_credits_table";
		$res = api_sql_query($sql, __FILE__, __LINE__);
		$obj = mysql_fetch_object($res); 
		return $obj->total_number_of_items;
	}
	
function get_number_of_credits()
	{
		
		return 1;
	}

function get_course_data($from, $number_of_items, $column, $direction)
	{
		$payment_options_table = Database :: get_main_table(CS_TABLE_PAYMENT_OPTION);
		$course_credits_table = Database :: get_main_table(CS_TABLE_COURSE_CREDITS);
		$table_user = Database::get_main_table(TABLE_MAIN_USER);
		$course_credits_rel_user_credits_table = Database :: get_main_table(CS_TABLE_COURSE_CREDITS_REL_USER_CREDITS);
		$table_options_history = Database :: get_main_table(CS_TABLE_OPTIONS_HISTORY);
		$sql = "SELECT lastname AS col0, firstname AS col1, SUM(IF(T1.credits IS NULL, 0,T1.credits))+SUM(IF(T5.credits IS NULL, 0, T5.credits)) AS col2, T3.user_id AS col3 FROM $course_credits_rel_user_credits_table T3";
		$sql .= " LEFT JOIN $table_options_history T5 ON T5.options_history_id = T3.options_history_id LEFT JOIN $course_credits_table T1 ON T3.code = T1.code AND T3.option_id = T1.option_id LEFT JOIN $table_user T2 ON T2.user_id = T3.user_id";
		$sql .= " GROUP BY T3.user_id";
		$sql .= " ORDER BY col$column $direction ";
		$sql .= " LIMIT $from,$number_of_items";
		$res = api_sql_query($sql, __FILE__, __LINE__);
		$users = array();
		while ($user = mysql_fetch_row($res))
		{
			$users[] = $user;
		}
		echo "<div align=\"right\">";
		echo '<a href="admin_menu_historics.php?view=subscriptionuser&action=export&type=csv&column='.$column.'&direction='.$direction.'"><img align="absbottom" src="../../main/img/file_xls.gif">'.get_lang('ExportAsCSV').'</a>';
		echo "</div>";
		return $users;
	}


function get_total_credits_data($from, $number_of_items, $column, $direction)
	{
		$course_credits_table = Database :: get_main_table(CS_TABLE_COURSE_CREDITS);
		$course_credits_rel_user_credits_table = Database :: get_main_table(CS_TABLE_COURSE_CREDITS_REL_USER_CREDITS);
		$table_options_history = Database :: get_main_table(CS_TABLE_OPTIONS_HISTORY);
		$sql = " SELECT COUNT(DISTINCT T2.user_id) AS col0,SUM(IF(T1.credits IS NULL, 0,T1.credits))+SUM(IF(T3.credits IS NULL, 0, T3.credits)) AS col1 FROM $course_credits_rel_user_credits_table T2";
		$sql .= " LEFT JOIN $table_options_history T3 ON T3.options_history_id = T2.options_history_id LEFT JOIN $course_credits_table T1 ON T2.code = T1.code AND T2.option_id = T1.option_id ";
		$sql .= " ORDER BY col0 $direction ";
		$sql .= " LIMIT $from,$number_of_items";
		$res = api_sql_query($sql, __FILE__, __LINE__);
		$numbers = array();
		while ($number = mysql_fetch_row($res))
		{
			$numbers[] = $number;
		}

		return $numbers;
	}

function modify_filter($code)
	{
		return
			'<a href="credits_spends_user_information.php?code='.$code.'"><img src="../../main/img/synthese_view.gif" border="0" style="vertical-align: middle" title="'.get_lang('Info').'" alt="'.get_lang('Info').'"/></a>&nbsp;';
	}

//Create a sortable table with the total number of credits of every course
		$table2 = new SortableTable('number_credits','get_number_of_credits' , 'get_total_credits_data',2,1);
		//$table2->set_additional_parameters($parameters);
		$table2->set_header(0, get_lang('TotalNumberOfUsers'));
		$table2->set_header(1, get_lang('TotalNumberOfCredits'));
		$table2->display();

// Create a sortable table with the course data
		$table = new SortableTable('subcription_user', 'get_number_of_subscritions', 'get_course_data',2);
		$parameters['code'] = $_GET['code'];
		$parameters['view'] = $_GET['view'];
		$table->set_additional_parameters($parameters);
		$table->set_header(0, get_lang('LastName'));
		$table->set_header(1, get_lang('FirstName'));
		$table->set_header(2, get_lang('CreditsSpendsUsers'));
		$table->set_header(3, '', false);
		$table->set_column_filter(3,'modify_filter');
		$table->display();

/*
==============================================================================
		FOOTER 
==============================================================================
*/ 
Display::display_footer();


?>