<?php
// $Id: my_credits.php,v 1.2 2007/04/02 14:34:45  Ana
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
include_once("../../main/inc/global.inc.php");

api_block_anonymous_users();

/*
-----------------------------------------------------------
	Libraries
-----------------------------------------------------------
*/
include_once(dirname(__FILE__).'/inc/cs_database.lib.php');
require_once (api_get_path(LIBRARY_PATH)."debug.lib.inc.php");
require_once (api_get_path(LIBRARY_PATH)."events.lib.inc.php");
require_once (api_get_path(LIBRARY_PATH)."export.lib.inc.php");
require_once (api_get_path(LIBRARY_PATH)."course.lib.php");

if( isset ($_GET['action']))
	{
		if ((($_GET['view'] == 'course') || isset ($_GET['course_per_page']))&&$_GET['type']=='csv')
		{
				$column = $_GET['column'];	
				$direction = $_GET['direction'];		
				$course_credits_table = 'cs_course_credits';
				//$course_credits_table = Database :: get_main_table(CS_TABLE_COURSE_CREDITS);
				$course_table = Database :: get_main_table(TABLE_MAIN_COURSE);
				$course_credits_rel_user_credits_table = 'cs_subscriptions';
				//$course_credits_rel_user_credits_table = Database :: get_main_table(CS_TABLE_COURSE_CREDITS_REL_USER_CREDITS);
				$table_options_history = 'cs_options_history';
				//$table_options_history = Database :: get_main_table(CS_TABLE_OPTIONS_HISTORY);
				$sql = "SELECT T3.code AS col0,title AS col1,tutor_name AS col2, SUM(IF(T2.credits IS NULL, 0,T2.credits))+SUM(IF(T4.credits IS NULL, 0, T4.credits)) AS col13 FROM $course_table T1,$course_credits_rel_user_credits_table T3";
				$sql .= " LEFT JOIN $course_credits_table T2 ON T3.code = T2.code AND T3.option_id = T2.option_id LEFT JOIN $table_options_history T4 ON (T3.options_history_id = T4.options_history_id)";
				$sql .= " WHERE (T1.code = T3.code)";
				$sql .= " GROUP BY T3.code";
				$sql .= " ORDER BY col$column $direction";
				$res = api_sql_query($sql, __FILE__, __LINE__);
				$num_rows = mysql_num_rows($res);
				if ($num_rows >= 1)
				{
					$alldata[]= array (
						'Code',
						'Course title',
						'Tutor name',
						'Credits spent'
					);
					while ($result = mysql_fetch_array($res, MYSQL_ASSOC))
						{
							$alldata[] = $result;
						}
					$filename = 'Credits_Spent_Per_Course'.'_'.date('Y-m-d_H-i-s');
					Export::export_table_csv($alldata,$filename);
				}
			}
		else if ((($_GET['view'] == 'paymentuser') || isset ($_GET['users_per_page']))&& $_GET['type']=='csv')
		{
			$column = $_GET['column'];	
			$direction = $_GET['direction'];
			$table_payment = 'cs_payment';
			//$table_payment = Database :: get_main_table(CS_TABLE_PAYMENT);
			$table_user = Database::get_main_table(TABLE_MAIN_USER);
			$sql = " SELECT lastname AS col0, firstname AS col1, SUM(credits) AS col2, SUM(amount) AS col3 FROM $table_user T1,$table_payment T2";
			$sql .= " WHERE (T1.user_id = T2.user_id)";
			$sql .= "GROUP BY T1.user_id";
			$sql .= " ORDER BY col$column $direction";
			$res = api_sql_query($sql, __FILE__, __LINE__);
			$num_rows = mysql_num_rows($res);
			if ($num_rows >= 1)
			{
				$alldata[]= array (
						'Last name',
						'First name',
						'Amount of credits',
						'Price'
					);
				while ($result = mysql_fetch_array($res, MYSQL_ASSOC))
					{
						$alldata[] = $result;
					}
				$filename = 'Payment_Of_Users'.'_'.date('Y-m-d_H-i-s');
				Export::export_table_csv($alldata,$filename);
			}
		}
		else if ((($_GET['view'] == 'subscriptionuser') || isset ($_GET['subscription_user_per_page']))&& $_GET['type']=='csv')
		{
			$column = $_GET['column'];
			$direction = $_GET['direction'];
			$payment_options_table = 'cs_payment_option';
			//$payment_options_table = Database :: get_main_table(CS_TABLE_PAYMENT_OPTION);
			$course_credits_table = 'cs_course_credits';
			//$course_credits_table = Database :: get_main_table(CS_TABLE_COURSE_CREDITS);
			$table_user = Database::get_main_table(TABLE_MAIN_USER);
			$course_credits_rel_user_credits_table = 'cs_subscriptions';
			//$course_credits_rel_user_credits_table = Database :: get_main_table(CS_TABLE_COURSE_CREDITS_REL_USER_CREDITS);
			$table_options_history = 'cs_options_history';
			//$table_options_history = Database :: get_main_table(CS_TABLE_OPTIONS_HISTORY);
			$sql = "SELECT lastname AS col0, firstname AS col1, SUM(IF(T1.credits IS NULL, 0,T1.credits))+SUM(IF(T5.credits IS NULL, 0, T5.credits)) AS col2 FROM $course_credits_rel_user_credits_table T3";
			$sql .= " LEFT JOIN $table_options_history T5 ON T5.options_history_id = T3.options_history_id LEFT JOIN $course_credits_table T1 ON T3.code = T1.code AND T3.option_id = T1.option_id LEFT JOIN $table_user T2 ON T2.user_id = T3.user_id";
			$sql .= " GROUP BY T3.user_id";
			$sql .= " ORDER BY col$column $direction";
			$res = api_sql_query($sql, __FILE__, __LINE__);
			$num_rows = mysql_num_rows($res);
			if ($num_rows >= 1)
			{
				$alldata[]= array (
						'Last name',
						'First name',
						'Credits spent'
					);
				while ($result = mysql_fetch_array($res, MYSQL_ASSOC))
					{
						$alldata[] = $result;
					}
				$filename = 'Credits_Spent_Per_User'.'_'.date('Y-m-d_H-i-s');
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
$tool_name = get_lang("PlatformHistory"); // title of the page (should come from the language file) 
Display::display_header($tool_name);

if( isset ($_GET['action']))
	{
		if ((($_GET['view'] == 'course') || isset ($_GET['course_per_page']))&&$_GET['type']=='csv')
		{
				$column = $_GET['column'];	
				$direction = $_GET['direction'];		
				$course_credits_table = 'cs_course_credits';
				//$course_credits_table = Database :: get_main_table(CS_TABLE_COURSE_CREDITS);
				$course_table = Database :: get_main_table(TABLE_MAIN_COURSE);
				$course_credits_rel_user_credits_table = 'cs_subscriptions';
				//$course_credits_rel_user_credits_table = Database :: get_main_table(CS_TABLE_COURSE_CREDITS_REL_USER_CREDITS);
				$table_options_history = 'cs_options_history';
				//$table_options_history = Database :: get_main_table(CS_TABLE_OPTIONS_HISTORY);
				$sql = "SELECT T3.code AS col0,title AS col1,tutor_name AS col2, SUM(IF(T2.credits IS NULL, 0,T2.credits))+SUM(IF(T4.credits IS NULL, 0, T4.credits)) AS col13 FROM $course_table T1,$course_credits_rel_user_credits_table T3";
				$sql .= " LEFT JOIN $course_credits_table T2 ON T3.code = T2.code AND T3.option_id = T2.option_id LEFT JOIN $table_options_history T4 ON (T3.options_history_id = T4.options_history_id)";
				$sql .= " WHERE (T1.code = T3.code)";
				$sql .= " GROUP BY T3.code";
				$sql .= " ORDER BY col$column $direction";
				$res = api_sql_query($sql, __FILE__, __LINE__);
				$num_rows = mysql_num_rows($res);
				if ($num_rows == 0)
				{
					$message = 	get_lang('ThereIsNotData');
					Display :: display_normal_message($message);
				}
			}
		else if ((($_GET['view'] == 'paymentuser') || isset ($_GET['users_per_page']))&& $_GET['type']=='csv')
		{
			$column = $_GET['column'];	
			$direction = $_GET['direction'];
			$table_payment = 'cs_payment';
			//$table_payment = Database :: get_main_table(CS_TABLE_PAYMENT);
			$table_user = Database::get_main_table(TABLE_MAIN_USER);
			$sql = " SELECT lastname AS col0, firstname AS col1, SUM(credits) AS col2, SUM(amount) AS col3 FROM $table_user T1,$table_payment T2";
			$sql .= " WHERE (T1.user_id = T2.user_id)";
			$sql .= "GROUP BY T1.user_id";
			$sql .= " ORDER BY col$column $direction";
			$res = api_sql_query($sql, __FILE__, __LINE__);
			$num_rows = mysql_num_rows($res);
			if ($num_rows == 0)
			{
				$message = 	get_lang('ThereIsNotData');
				Display :: display_normal_message($message);
			}
		}
		else if ((($_GET['view'] == 'subscriptionuser') || isset ($_GET['subscription_user_per_page']))&& $_GET['type']=='csv')
		{
			$column = $_GET['column'];
			$direction = $_GET['direction'];
			$payment_options_table = 'cs_payment_option';
			//$payment_options_table = Database :: get_main_table(CS_TABLE_PAYMENT_OPTION);
			$course_credits_table = 'cs_course_credits';
			//$course_credits_table = Database :: get_main_table(CS_TABLE_COURSE_CREDITS);
			$table_user = Database::get_main_table(TABLE_MAIN_USER);
			$course_credits_rel_user_credits_table = 'cs_subscriptions';
			//$course_credits_rel_user_credits_table = Database :: get_main_table(CS_TABLE_COURSE_CREDITS_REL_USER_CREDITS);
			$table_options_history = 'cs_options_history';
			//$table_options_history = Database :: get_main_table(CS_TABLE_OPTIONS_HISTORY);
			$sql = "SELECT lastname AS col0, firstname AS col1, SUM(IF(T1.credits IS NULL, 0,T1.credits))+SUM(IF(T5.credits IS NULL, 0, T5.credits)) AS col2 FROM $course_credits_rel_user_credits_table T3";
			$sql .= " LEFT JOIN $table_options_history T5 ON T5.options_history_id = T3.options_history_id LEFT JOIN $course_credits_table T1 ON T3.code = T1.code AND T3.option_id = T1.option_id LEFT JOIN $table_user T2 ON T2.user_id = T3.user_id";
			$sql .= " GROUP BY T3.user_id";
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


/*
==============================================================================
		MAIN CODE
==============================================================================
*/ 

echo "<div class = 'dummy'>";
 if(isset($_GET['view']) || isset($_GET['course_per_page']) || isset($_GET['users_per_page']) || isset($_GET['subcription_user_per_page']))
{
	if (($_GET['view'] == 'course') || isset ($_GET['course_per_page']))
	{
		require_once(dirname(__FILE__).'/course_historic.php');
	}
	else if (($_GET['view'] == 'paymentuser') || isset ($_GET['users_per_page']))
	{
		require_once(dirname(__FILE__).'/users_historic.php');
	}
	else if (($_GET['view'] == 'subscriptionuser') || isset ($_GET['subcription_user_per_page']))
	{
		require_once(dirname(__FILE__).'/subscription_users_historic.php');	
	}
	else
	{
		$message = 	get_lang('YouDoNotSelectAHistory');
		Display :: display_normal_message($message);
		Display::display_footer();
	}
}

?>