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
require_once (api_get_path(LIBRARY_PATH).'sortabletable.class.php');
$interbreadcrumb[] = array ("url" => api_get_path(PLUGIN_PATH).'my_credits.php', "name" => get_lang('CreditsSystem'));
api_display_tool_title(get_lang('CreditsSpentPerCourse'));
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

/*
==============================================================================
		FUNCTIONS
==============================================================================
*/

	
function get_number_of_courses()
	{
		$course_credits_table = Database :: get_main_table(CS_TABLE_COURSE_CREDITS);
		$course_table = Database :: get_main_table(TABLE_MAIN_COURSE);
		$course_credits_rel_user_credits_table = Database :: get_main_table(CS_TABLE_COURSE_CREDITS_REL_USER_CREDITS);
		$sql = "SELECT COUNT(DISTINCT code) AS total_number_of_items FROM $course_credits_rel_user_credits_table T3";
		
		//$sql = " SELECT COUNT(DISTINCT T2.code) AS total_number_of_items FROM $course_credits_rel_user_credits_table T3";
		//$sql .= " WHERE ((T1.code = T2.code) AND (T1.code = T3.code))";
		/*if (isset ($_GET['keyword']))
		{
			$keyword = mysql_real_escape_string($_GET['keyword']);
			$sql .= "AND ((T1.code LIKE '%".$keyword."%' OR option_id LIKE '%".$keyword."%' OR credits LIKE '%".$keyword."%' OR tutor_name LIKE '%".$keyword."%' OR title LIKE '%".$keyword."%'))";
		}*/
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
		$course_credits_table = Database :: get_main_table(CS_TABLE_COURSE_CREDITS);
		$course_table = Database :: get_main_table(TABLE_MAIN_COURSE);
		$course_credits_rel_user_credits_table = Database :: get_main_table(CS_TABLE_COURSE_CREDITS_REL_USER_CREDITS);
		$table_options_history = Database :: get_main_table(CS_TABLE_OPTIONS_HISTORY);
		$sql = "SELECT T3.code AS col0,title AS col1,tutor_name AS col2, SUM(IF(T2.credits IS NULL, 0,T2.credits))+SUM(IF(T4.credits IS NULL, 0, T4.credits)) AS col13,T3.code AS col14 FROM $course_table T1,$course_credits_rel_user_credits_table T3";
		$sql .= " LEFT JOIN $course_credits_table T2 ON T3.code = T2.code AND T3.option_id = T2.option_id LEFT JOIN $table_options_history T4 ON T3.options_history_id = T4.options_history_id";
		$sql .= " WHERE T1.code = T3.code";
		$sql .= " GROUP BY T3.code";
		$sql .= " ORDER BY col$column $direction ";
		$sql .= " LIMIT $from,$number_of_items";
		$res = api_sql_query($sql, __FILE__, __LINE__);
		$courses = array();
		while ($course = mysql_fetch_row($res))
		{
			$courses[] = $course;
		}
		echo "<div align=\"right\">";
		echo '<a href="admin_menu_historics.php?view=course&action=export&type=csv&column='.$column.'"><img align="absbottom" src="../../main/img/file_xls.gif">'.get_lang('ExportAsCSV').'</a>';
		echo "</div>";
		return $courses;
		
	}


function get_total_credits_data($from, $number_of_items, $column, $direction)
	{
		$course_credits_table = Database :: get_main_table(CS_TABLE_COURSE_CREDITS);
		$course_credits_rel_user_credits_table = Database :: get_main_table(CS_TABLE_COURSE_CREDITS_REL_USER_CREDITS);
		$table_options_history = Database :: get_main_table(CS_TABLE_OPTIONS_HISTORY);
		$sql = " SELECT COUNT(DISTINCT T2.code) AS col0,SUM(IF(T1.credits IS NULL, 0,T1.credits))+SUM(IF(T3.credits IS NULL, 0, T3.credits)) AS col1 FROM $course_credits_rel_user_credits_table T2";
		$sql .= " LEFT JOIN $table_options_history T3 ON T3.options_history_id = T2.options_history_id LEFT JOIN $course_credits_table T1 ON T2.code = T1.code AND T2.option_id = T1.option_id ";
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
			'<a href="course_information.php?code='.$code.'"><img src="../../main/img/synthese_view.gif" border="0" style="vertical-align: middle" title="'.get_lang('Info').'" alt="'.get_lang('Info').'"/></a>&nbsp;';
	}


/*
==============================================================================
		MAIN CODE
==============================================================================
*/ 

// Put your main code here. Keep this section short,
// it's better to use functions for any non-trivial code
echo '<a href = '.api_get_path(WEB_PLUGIN_PATH).'credits_system/admin_menu_historics.php?view=course>'.get_lang('CreditsSpentPerCourse').'</a> | ';
echo '<a href = '.api_get_path(WEB_PLUGIN_PATH).'credits_system/admin_menu_historics.php?view=paymentuser>'.get_lang('PaymentOfUsers').'</a> | ';
echo '<a href = '.api_get_path(WEB_PLUGIN_PATH).'credits_system/admin_menu_historics.php?view=subscriptionuser>'.get_lang('CreditsSpendsPerUser').'</a>';
	
// Create a search-box
	$form = new FormValidator('search_simple','get','','',null,false);
	$renderer =& $form->defaultRenderer();
	$renderer->setElementTemplate('<span>{element}</span> ');
	$form->addElement('text','keyword',get_lang('keyword'));
	$form->addElement('submit','submit',get_lang('Search'));

//Create a sortable table with the total number of credits of every course
	$table2 = new SortableTable('number_credits','get_number_of_credits' , 'get_total_credits_data',2,1);
	$table2->set_header(0, get_lang('TotalNumberOfCourses'));
	$table2->set_header(1, get_lang('TotalNumberOfCredits'));
	$table2->display();



// Create a sortable table with the payment courses' data
	$table = new SortableTable('course', 'get_number_of_courses', 'get_course_data',2);
	$parameters['view'] = $_GET['view'];
	$table->set_additional_parameters($parameters);
	$table->set_header(0, get_lang('Code'));
	$table->set_header(1, get_lang('Title'));
	$table->set_header(2, get_lang('TutorName'));
	$table->set_header(3, get_lang('CreditsSpendsUsers'));
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