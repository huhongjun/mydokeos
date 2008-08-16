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
include_once(dirname(__FILE__).'/inc/cs_database.lib.php');
require_once(dirname(__FILE__).'/inc/cs_functions.inc.php');
require_once (api_get_path(LIBRARY_PATH).'sortabletable.class.php');


$interbreadcrumb[] = array ("url" => api_get_path(PLUGIN_PATH).'my_credits.php', "name" => get_lang('CreditsSystem'));
api_display_tool_title(get_lang('PaymentOfUsers'));
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

	
function get_number_of_users()
	{
		$table_payment = Database :: get_main_table(CS_TABLE_PAYMENT);
		$table_user = Database::get_main_table(TABLE_MAIN_USER);
		$sql = " SELECT COUNT(DISTINCT T1.user_id) AS total_number_of_items FROM $table_payment T1,$table_user T2";
		$sql .= " WHERE (T1.user_id = T2.user_id)";
		//if (isset ($_GET['keyword']))
		//{
			//$keyword = mysql_real_escape_string($_GET['keyword']);
			//$sql .= "AND ((T1.code LIKE '%".$keyword."%' OR option_id LIKE '%".$keyword."%' OR credits LIKE '%".$keyword."%' OR tutor_name LIKE '%".$keyword."%' OR title LIKE '%".$keyword."%'))";
		//}
		$res = api_sql_query($sql, __FILE__, __LINE__);
		$obj = mysql_fetch_object($res);
		return $obj->total_number_of_items;
	}
	
function get_number_of_credits()
	{
		
		return 1;
	}

function get_payment_data($from, $number_of_items, $column, $direction)
	{
		$table_payment = Database :: get_main_table(CS_TABLE_PAYMENT);
		$table_user = Database::get_main_table(TABLE_MAIN_USER);
		$sql = " SELECT lastname AS col0, firstname AS col1, SUM(credits) AS col2,SUM(amount) AS col3,T1.user_id AS col4 FROM $table_user T1,$table_payment T2";
		$sql .= " WHERE (T1.user_id = T2.user_id)";
		//if (isset ($_GET['keyword']))
		//{
			//$keyword = mysql_real_escape_string($_GET['keyword']);
			//$sql .= "AND( T1.code LIKE '%".$keyword."%' OR title LIKE '%".$keyword."%' OR credits LIKE '%".$keyword."%' OR tutor_name LIKE '%".$keyword."%')";
		//}
		$sql .= "GROUP BY T1.user_id";
		$sql .= " ORDER BY col$column $direction ";
		$sql .= " LIMIT $from,$number_of_items";
		$res = api_sql_query($sql, __FILE__, __LINE__);
		$users = array();
		while ($user = mysql_fetch_row($res))
		{
			$users[] = $user;
		}
		echo "<div align=\"right\">";
		echo '<a href="admin_menu_historics.php?view=paymentuser&action=export&type=csv&column='.$column.'&direction='.$direction.'"><img align="absbottom" src="../../main/img/file_xls.gif">'.get_lang('ExportAsCSV').'</a>';
		echo "</div>";

		return $users;
	}
	
function get_total_credits_data($from, $number_of_items, $column, $direction)
	{
		$table_payment = Database :: get_main_table(CS_TABLE_PAYMENT);
		$table_user = Database::get_main_table(TABLE_MAIN_USER);
		$sql = " SELECT COUNT(DISTINCT T1.user_id) AS col0,SUM(credits) AS col1,SUM(amount) AS col2 FROM $table_user T1, $table_payment T2";
		$sql .= " WHERE (T1.user_id = T2.user_id)";
		//$sql .= " ORDER BY col$column $direction ";
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
		global $origin;	
		return
			'<a href="payment_user_information.php?code='.$code.'"><img src="../../main/img/synthese_view.gif" border="0" style="vertical-align: middle" title="'.get_lang('Info').'" alt="'.get_lang('Info').'"/></a>&nbsp';
			//'<a href="../../main/user/userInfo.php?origin='.$origin.'&amp;uInfo='.$code.'"><img border="0" alt="'.get_lang('Info').'" src="../../main/img/user_info.gif" /></a>';
			
	}

//Create a sortable table with the total number of credits of every course
		$table2 = new SortableTable('number_credits','get_number_of_credits' , 'get_total_credits_data',2,1);
		//$table2->set_additional_parameters($parameters);
		$table2->set_header(0, get_lang('TotalNumberOfUsers'));
		$table2->set_header(1, get_lang('TotalNumberOfCredits'));
		$table2->set_header(2, get_lang('TheQuantityOfMoneySpent'));
		$table2->display();


// Create a sortable table with the course data
		$table = new SortableTable('users', 'get_number_of_users', 'get_payment_data',2);
		$parameters['view'] = $_GET['view'];
		$table->set_additional_parameters($parameters);
		$table->set_header(0, get_lang('LastName'));
		$table->set_header(1, get_lang('FirstName'));
		$table->set_header(2, get_lang('AmountOfCredits'));
		$table->set_header(3, get_lang('Price'));
		$table->set_header(4, '', false);
		$table->set_column_filter(4,'modify_filter');
		$table->display();

/*
==============================================================================
		FOOTER 
==============================================================================
*/ 
Display::display_footer();
	

?>