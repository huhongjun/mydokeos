<?php // $Id: menu_history.php,v 1.2 2007/04/16 14:34:45
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
	if (($_GET['view'] == 'payment' || isset($_GET['payment_history_per_page']))&&$_GET['type']=='csv')
	{
		$user_id = $_GET['code'];
		$column = $_GET['column'];
		$direction = $_GET['direction'];
		$payment = 'cs_payment';
		//$payment = Database :: get_main_table(CS_TABLE_PAYMENT);
		$sql = " SELECT DATE_FORMAT(date, '%Y-%m-%d') AS col0, credits AS col1,amount AS col2, payment_method AS col3 FROM $payment";
		$sql .= " WHERE($user_id = user_id)";
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
			$filename = 'Payment_Data'.'_'. date('Y-m-d_H-i-s');
			Export::export_table_csv($alldata,$filename);
		}
	}
	else if (($_GET['view'] == 'subscription' || isset($_GET['ubscription_history_per_page']))&&$_GET['type']=='csv')
	{
		$column = $_GET['column'];
		$user_id = $_GET['code'];
		$direction = $_GET['direction'];
		$table_options_history = 'cs_options_history';
		//$table_options_history = Database :: get_main_table(CS_TABLE_OPTIONS_HISTORY);
		$payment = 'cs_payment';
		//$payment = Database :: get_main_table(CS_TABLE_PAYMENT);
		$course_credits_rel_user_credits = 'cs_subscriptions';
		//$course_credits_rel_user_credits = Database :: get_main_table(CS_TABLE_COURSE_CREDITS_REL_USER_CREDITS);
		$course_credits = 'cs_course_credits';
		//$course_credits = Database :: get_main_table(CS_TABLE_COURSE_CREDITS);
		$payment_options = 'cs_payment_option';
		//$payment_options = Database :: get_main_table(CS_TABLE_PAYMENT_OPTION);
		$course_table = Database :: get_main_table(TABLE_MAIN_COURSE);
		$sql = "SELECT T4.code AS col0,title AS col1,DATE_FORMAT(init_date, '%Y-%m-%d') AS col2,DATE_FORMAT(end_date, '%Y-%m-%d'),IF(T5.amount IS NULL, T3.amount,T5.amount) AS col4,IF(T5.name IS NULL, T3.name,T5.name) AS col5,IF(T5.credits IS NULL, T2.credits,T5.credits) AS col6 FROM $course_credits_rel_user_credits T1";
		$sql .= " LEFT JOIN $table_options_history T5 ON (T1.options_history_id = T5.options_history_id) LEFT JOIN $course_credits T2 ON T1.code = T2.code AND T1.option_id = T2.option_id LEFT JOIN $payment_options T3 ON T1.option_id = T3.option_id ";
		$sql .= "LEFT JOIN $course_table T4 ON T1.code = T4.code";
		$sql .= " WHERE ($user_id = T1.user_id)";
		$sql .= " ORDER BY col$column $direction";
		$res = api_sql_query($sql, __FILE__, __LINE__);
		$num_rows = mysql_num_rows($res);
		if ($num_rows >= 1)
		{
			$alldata[]= array (
				'Code',
				'Title',
				'Init date',
				'Final date',
				'Length',
				'Option',
				'Credits amount'
			);
			while ($result = mysql_fetch_array($res, MYSQL_ASSOC))
				{
					$alldata[] = $result;
				}
			
			$filename = 'Courses_Subscription_'. date('Y-m-d_H-i-s');
			Export::export_table_csv($alldata,$filename);
		}
	}
}

	
/*
-----------------------------------------------------------
	Header
-----------------------------------------------------------
*/ 

//	Optional extra http or html header
//	If you need to add some HTTP/HTML headers code 
//	like JavaScript functions, stylesheets, redirects, put them here.
global $charset;
$interbreadcrumb[] = array ("url" => api_get_path(PLUGIN_PATH).'my_credits.php', "name" => get_lang('CreditsSystem'));
$tool_name = mb_convert_encoding('记录',$charset,'utf-8'); // title of the page (should come from the language file) 
Display::display_header($tool_name);

if( isset ($_GET['action']))
{
	if (($_GET['view'] == 'payment' || isset($_GET['payment_history_per_page']))&&$_GET['type']=='csv')
	{
		$user_id = $_GET['code'];
		$column = $_GET['column'];
		$direction = $_GET['direction'];
		$payment = 'cs_payment';
		$payment = Database :: get_main_table(CS_TABLE_PAYMENT);
		$sql = " SELECT DATE_FORMAT(date, '%Y-%m-%d') AS col0, credits AS col1,amount AS col2, payment_method AS col3 FROM $payment";
		$sql .= " WHERE($user_id = user_id)";
		$sql .= " ORDER BY col$column $direction";
		$res = api_sql_query($sql, __FILE__, __LINE__);
		$num_rows = mysql_num_rows($res);
		if ($num_rows == 0)
		{
			$message = 	get_lang('ThereIsNotData');
			Display :: display_normal_message($message);
		}
	}
	else if (($_GET['view'] == 'subscription' || isset($_GET['ubscription_history_per_page']))&&$_GET['type']=='csv')
	{
		$column = $_GET['column'];
		$user_id = $_GET['code'];
		$direction = $_GET['direction'];
		$table_options_history = 'cs_options_history';
		//$table_options_history = Database :: get_main_table(CS_TABLE_OPTIONS_HISTORY);
		$payment = 'cs_payment';
		//$payment = Database :: get_main_table(CS_TABLE_PAYMENT);
		$course_credits_rel_user_credits = 'cs_subscriptions';
		//$course_credits_rel_user_credits = Database :: get_main_table(CS_TABLE_COURSE_CREDITS_REL_USER_CREDITS);
		$course_credits = 'cs_course_credits';
		//$course_credits = Database :: get_main_table(CS_TABLE_COURSE_CREDITS);
		$payment_options = 'cs_payment_option';
		//$payment_options = Database :: get_main_table(CS_TABLE_PAYMENT_OPTION);
		$course_table = Database :: get_main_table(TABLE_MAIN_COURSE);
		$sql = "SELECT T4.code AS col0,title AS col1,DATE_FORMAT(init_date, '%Y-%m-%d') AS col2,DATE_FORMAT(end_date, '%Y-%m-%d'),IF(T5.amount IS NULL, T3.amount,T5.amount) AS col4,IF(T5.name IS NULL, T3.name,T5.name) AS col5,IF(T5.credits IS NULL, T2.credits,T5.credits) AS col6 FROM $course_credits_rel_user_credits T1";
		$sql .= " LEFT JOIN $table_options_history T5 ON (T1.options_history_id = T5.options_history_id) LEFT JOIN $course_credits T2 ON T1.code = T2.code AND T1.option_id = T2.option_id LEFT JOIN $payment_options T3 ON T1.option_id = T3.option_id ";
		$sql .= "LEFT JOIN $course_table T4 ON T1.code = T4.code";
		$sql .= " WHERE ($user_id = T1.user_id)";
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

// Put your main code here. Keep this section short,
// it's better to use functions for any non-trivial code
 
 echo "<div class = 'dummy'>";
 if(isset($_GET['view']) || isset($_GET['subscription_history_per_page']) || isset($_GET['payment_history_per_page']))
{
	if ($_GET['view'] == 'payment' || isset($_GET['payment_history_per_page']))
	{
		require_once(dirname(__FILE__).'/payment_data.php');
	}
	else if($_GET['view'] == 'subscription' || isset($_GET['subscription_history_per_page']))
	{
		require_once(dirname(__FILE__).'/subscription_data.php');
	}
	else
	{
		$message = 	get_lang('YouDoNotSelectAHistory');
		Display :: display_normal_message($message);
		Display::display_footer();
	}
}
?>