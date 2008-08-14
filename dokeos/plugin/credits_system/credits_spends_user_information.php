<?php
// $Id: credits_spends_user_information.php 10926 2007-03-25 11:30:47Z ana $
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
		$payment_options_table ='cs_payment_option';
		//$payment_options_table = Database :: get_main_table(CS_TABLE_PAYMENT_OPTION);
		$table_course_credits = 'cs_course_credits';
		//$table_course_credits = Database :: get_main_table(CS_TABLE_COURSE_CREDITS);		
		$course_credits_rel_user_credits_table = 'cs_subscriptions';
		//$course_credits_rel_user_credits_table = Database :: get_main_table(CS_TABLE_COURSE_CREDITS_REL_USER_CREDITS);
		$course_table = Database :: get_main_table(TABLE_MAIN_COURSE);
		$table_options_history = 'cs_options_history';
		//$table_options_history = Database :: get_main_table(CS_TABLE_OPTIONS_HISTORY);
		$sql = "SELECT title AS col0, IF(T5.credits IS NULL, T3.credits,T5.credits) AS col1,DATE_FORMAT(init_date, '%Y-%m-%d') AS col2,DATE_FORMAT(end_date, '%Y-%m-%d') AS col3 FROM $course_credits_rel_user_credits_table T2";
		$sql .= " LEFT JOIN $table_options_history T5 ON T5.options_history_id = T2.options_history_id LEFT JOIN $table_course_credits T3 ON T3.code = T2.code AND T3.option_id = T2.option_id LEFT JOIN $course_table T1 ON T1.code = T2.code";
		$sql .= " WHERE $user_id = T2.user_id";
		$sql .= " ORDER BY col$column $direction";
		$res = api_sql_query($sql, __FILE__, __LINE__);
		$num_rows = mysql_num_rows($res);
		if ($num_rows >= 1)
		{
			$alldata[]= array (
						'Title',
						'Credits',
						'Init date',
						'Final date'
					);
			while ($result = mysql_fetch_array($res, MYSQL_ASSOC))
				{
					$alldata[] = $result;
				}
			$filename = 'Credits_Spends_Per_User'.'_'.date('Y-m-d_H-i-s');
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
$interbreadcrumb[] = array ("url" => api_get_path(PLUGIN_PATH).'admin_menu_historics.php?view=subscriptionuser', "name" => get_lang('PlatformHistory'));
$tool_name = get_lang('CreditsSpends');
Display::display_header($tool_name);

if( isset ($_GET['action']))
{
	if ($_GET['type']=='csv')
	{
		$column = $_GET['column'];
		$user_id = $_GET['code'];
		$direction = $_GET['direction'];
		$payment_options_table ='cs_payment_option';
		//$payment_options_table = Database :: get_main_table(CS_TABLE_PAYMENT_OPTION);
		$table_course_credits = 'cs_course_credits';
		//$table_course_credits = Database :: get_main_table(CS_TABLE_COURSE_CREDITS);		
		$course_credits_rel_user_credits_table = 'cs_subscriptions';
		//$course_credits_rel_user_credits_table = Database :: get_main_table(CS_TABLE_COURSE_CREDITS_REL_USER_CREDITS);
		$course_table = Database :: get_main_table(TABLE_MAIN_COURSE);
		$table_options_history = 'cs_options_history';
		//$table_options_history = Database :: get_main_table(CS_TABLE_OPTIONS_HISTORY);
		$sql = "SELECT title AS col0, IF(T5.credits IS NULL, T3.credits,T5.credits) AS col1,DATE_FORMAT(init_date, '%Y-%m-%d') AS col2,DATE_FORMAT(end_date, '%Y-%m-%d') AS col3 FROM $course_credits_rel_user_credits_table T2";
		$sql .= " LEFT JOIN $table_options_history T5 ON T5.options_history_id = T2.options_history_id LEFT JOIN $table_course_credits T3 ON T3.code = T2.code AND T3.option_id = T2.option_id LEFT JOIN $course_table T1 ON T1.code = T2.code";
		$sql .= " WHERE $user_id = T2.user_id";
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

api_display_tool_title(get_lang('CreditsSpends'));

/*
==============================================================================
		FUNCTIONS
==============================================================================
*/

//Get number of subscriptions of one user
function get_number_of_subscription()
	{
		if (isset ($_GET['code']))
		{
			$user_id = $_GET['code'];
		}
		$course_table = Database :: get_main_table(TABLE_MAIN_COURSE);
		$payment_options_table = Database :: get_main_table(CS_TABLE_PAYMENT_OPTION);
		$table_course_credits = Database :: get_main_table(CS_TABLE_COURSE_CREDITS);
		$course_credits_rel_user_credits_table = Database :: get_main_table(CS_TABLE_COURSE_CREDITS_REL_USER_CREDITS);
		$sql = " SELECT COUNT(T2.user_id) AS total_number_of_items FROM $course_credits_rel_user_credits_table T2";
		$sql .= " WHERE (user_id = '".$user_id."')";
		$res = api_sql_query($sql, __FILE__, __LINE__);
		$obj = mysql_fetch_object($res);
		return $obj->total_number_of_items;
	}

//Get the subscription data of one user	
function get_subscription_data($from, $number_of_items, $column, $direction)
	{
		if (isset ($_GET['code']))
		{
			$user_id = $_GET['code'];
		}
		$payment_options_table = Database :: get_main_table(CS_TABLE_PAYMENT_OPTION);
		$table_course_credits = Database :: get_main_table(CS_TABLE_COURSE_CREDITS);		
		$course_credits_rel_user_credits_table = Database :: get_main_table(CS_TABLE_COURSE_CREDITS_REL_USER_CREDITS);
		$course_table = Database :: get_main_table(TABLE_MAIN_COURSE);
		$table_options_history = Database :: get_main_table(CS_TABLE_OPTIONS_HISTORY);
		$sql = "SELECT title AS col0, IF(T5.credits IS NULL, T3.credits,T5.credits) AS col1,DATE_FORMAT(init_date, '%Y-%m-%d') AS col2,DATE_FORMAT(end_date, '%Y-%m-%d') AS col3 FROM $course_credits_rel_user_credits_table T2";
		$sql .= " LEFT JOIN $table_options_history T5 ON T5.options_history_id = T2.options_history_id LEFT JOIN $table_course_credits T3 ON T3.code = T2.code AND T3.option_id = T2.option_id LEFT JOIN $course_table T1 ON T1.code = T2.code";
		$sql .= " WHERE $user_id = T2.user_id";
		$sql .= " ORDER BY col$column $direction ";
		$sql .= " LIMIT $from,$number_of_items";
		$res = api_sql_query($sql, __FILE__, __LINE__);
		$subscritions = array();
		while ($subscription = mysql_fetch_row($res))
		{
			$subscriptions[] = $subscription;
		}
		echo "<div align=\"right\">";
		echo '<a href="credits_spends_user_information.php?code='.$user_id.'&action=export&type=csv&column='.$column.'&direction='.$direction.'"><img align="absbottom" src="../../main/img/file_xls.gif">'.get_lang('ExportAsCSV').'</a>';
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

// Create a sortable table with the credits spent per user
		$table = new SortableTable('subscritions', 'get_number_of_subscription', 'get_subscription_data',2);
		$parameters['code'] = $_GET['code'];
		$table->set_additional_parameters($parameters);
		$table->set_header(0,get_lang('Title'));
		$table->set_header(1, get_lang('Credits'));
		$table->set_header(2, get_lang('InitDate'));
		$table->set_header(3, get_lang('FinalDate'));
		$table->display();


/*
==============================================================================
		FOOTER 
==============================================================================
*/ 
Display::display_footer();

?>