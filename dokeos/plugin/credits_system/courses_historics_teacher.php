<?php
// $Id: courses_historics_teacher.php 10926 2007-03-25 11:30:47Z ana $
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
include_once(dirname(__FILE__).'/inc/cs_database.lib.php');
require_once (api_get_path(LIBRARY_PATH)."debug.lib.inc.php");
require_once (api_get_path(LIBRARY_PATH)."events.lib.inc.php");
require_once (api_get_path(LIBRARY_PATH)."export.lib.inc.php");
require_once (api_get_path(LIBRARY_PATH)."course.lib.php");

if( isset ($_GET['action']))
{
	if ($_GET['type']=='csv')
	{
		$column = $_GET['column'];
		$direction = $_GET['direction'];
		$user_id = api_get_user_id ();
		$course_rel_user_table = Database :: get_main_table(TABLE_MAIN_COURSE_USER);
		$course_credits = Database :: get_main_table(CS_TABLE_COURSE_CREDITS);
		$course_table = Database :: get_main_table(TABLE_MAIN_COURSE);
		$course_credits_rel_user_credits = Database :: get_main_table(CS_TABLE_COURSE_CREDITS_REL_USER_CREDITS);
		$table_options_history = Database :: get_main_table(CS_TABLE_OPTIONS_HISTORY);
		$sql = " SELECT T3.code AS col0, title AS col1, SUM(IF(T2.credits IS NULL, 0,T2.credits))+SUM(IF(T4.credits IS NULL, 0, T4.credits)) AS col2 FROM $course_credits_rel_user_credits T3";
		$sql .= " LEFT JOIN $table_options_history T4 ON T4.options_history_id = T3.options_history_id LEFT JOIN $course_credits T2 ON T3.code = T2.code AND T3.option_id = T2.option_id LEFT JOIN $course_table T1 ON T1.code = T3.code";
		$sql .= " WHERE T1.code IN (SELECT course_code FROM $course_rel_user_table WHERE (role like 'Professor') AND (user_id=$user_id))";	
		if (isset ($_GET['keyword']))
		{
			$keyword = mysql_real_escape_string($_GET['keyword']);
			$sql .= "AND ((T1.code LIKE '%".$keyword."%' OR if (T4.credits IS NULL,T2.credits,T4.credits) LIKE '%".$keyword."%' OR title LIKE '%".$keyword."%'))";
		}
		$sql .= " GROUP BY T3.code";
		$sql .= " ORDER BY col$column $direction";
		$res = api_sql_query($sql, __FILE__, __LINE__);
		$num_rows = mysql_num_rows($res);
		if ($num_rows >= 1)
		{
			$alldata[]= array (
				'Course code',
				'Title',
				'Credits spent'
			);
			while ($result = mysql_fetch_array($res, MYSQL_ASSOC))
				{
					$alldata[] = $result;
				}
			$filename = 'My_Courses_History'.'_'.date('Y-m-d_H-i-s');
			Export::export_table_csv($alldata,$filename);
		}
	}
}

/*
-----------------------------------------------------------
	Header
-----------------------------------------------------------
*/ 
$tool_name = get_lang('MyCoursesHistory');
$interbreadcrumb[] = array ("url" => api_get_path(PLUGIN_PATH).'my_credits.php', "name" => get_lang('CreditsSystem'));
if (isset ($_GET['keyword']))
{
	$interbreadcrumb[] = array ("url" => api_get_path(PLUGIN_PATH).'courses_historics_teacher.php', "name" => get_lang('MyCoursesHistory'));
	$tool_name = get_lang('Search');
}

Display :: display_header($tool_name);

if( isset ($_GET['action']))
{
	if ($_GET['type']=='csv')
	{
		$column = $_GET['column'];
		$direction = $_GET['direction'];
		$user_id = api_get_user_id ();
		$course_rel_user_table = Database :: get_main_table(TABLE_MAIN_COURSE_USER);
		$course_credits = Database :: get_main_table(CS_TABLE_COURSE_CREDITS);
		$course_table = Database :: get_main_table(TABLE_MAIN_COURSE);
		$course_credits_rel_user_credits = Database :: get_main_table(CS_TABLE_COURSE_CREDITS_REL_USER_CREDITS);
		$table_options_history = Database :: get_main_table(CS_TABLE_OPTIONS_HISTORY);
		$sql = " SELECT T3.code AS col0, title AS col1, SUM(IF(T2.credits IS NULL, 0,T2.credits))+SUM(IF(T4.credits IS NULL, 0, T4.credits)) AS col2 FROM $course_credits_rel_user_credits T3";
		$sql .= " LEFT JOIN $table_options_history T4 ON T4.options_history_id = T3.options_history_id LEFT JOIN $course_credits T2 ON T3.code = T2.code AND T3.option_id = T2.option_id LEFT JOIN $course_table T1 ON T1.code = T3.code";
		$sql .= " WHERE T1.code IN (SELECT course_code FROM $course_rel_user_table WHERE (role like 'Professor') AND (user_id=$user_id))";	
		if (isset ($_GET['keyword']))
		{
			$keyword = mysql_real_escape_string($_GET['keyword']);
			$sql .= "AND ((T1.code LIKE '%".$keyword."%' OR if (T4.credits IS NULL,T2.credits,T4.credits) LIKE '%".$keyword."%' OR title LIKE '%".$keyword."%'))";
		}
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
}

api_display_tool_title($tool_name);

/*
==============================================================================
		FUNCTIONS
==============================================================================
*/

//Get the number of courses of the logged-in teacher 
function get_number_of_courses()
	{
	$user_id = api_get_user_id ();
	$course_rel_user_table = Database :: get_main_table(TABLE_MAIN_COURSE_USER);
	$course_table = Database :: get_main_table(TABLE_MAIN_COURSE);
	$course_credits_table = Database :: get_main_table(CS_TABLE_COURSE_CREDITS);
	$course_credits_rel_user_credits = Database :: get_main_table(CS_TABLE_COURSE_CREDITS_REL_USER_CREDITS);
	$table_options_history = Database :: get_main_table(CS_TABLE_OPTIONS_HISTORY);
	$sql = " SELECT COUNT(DISTINCT T3.code ) AS total_number_of_items FROM $course_credits_rel_user_credits T3";	
	$sql .= " LEFT JOIN $table_options_history T4 ON T3.options_history_id = T4.options_history_id LEFT JOIN $course_credits_table T2 ON T2.code = T3.code AND T2.option_id = T3.option_id LEFT JOIN $course_table T1 ON T1.code = T3.code";
	$sql .= " WHERE T1.code IN (SELECT course_code FROM $course_rel_user_table WHERE (role like 'Professor') AND (user_id=$user_id))";
	if (isset ($_GET['keyword']))
		{
			$keyword = mysql_real_escape_string($_GET['keyword']);
			$sql .= "AND ((T1.code LIKE '%".$keyword."%' OR if (T4.credits IS NULL,T2.credits,T4.credits) LIKE '%".$keyword."%' OR title LIKE '%".$keyword."%'))";
		}
	$res = api_sql_query($sql, __FILE__, __LINE__);
	$obj = mysql_fetch_object($res);
	return $obj->total_number_of_items;
	}


function get_number_of_rows()
	{
		
		return 1;
	}

//Get the courses's data of the logged-in teacher 
function get_courses_data_of_a_teacher($from, $number_of_items, $column, $direction)
{
	
	$user_id = api_get_user_id ();
	$course_credits = Database :: get_main_table(CS_TABLE_COURSE_CREDITS);
	$course_rel_user_table = Database :: get_main_table(TABLE_MAIN_COURSE_USER);
	$course_table = Database :: get_main_table(TABLE_MAIN_COURSE);
	$course_credits_rel_user_credits = Database :: get_main_table(CS_TABLE_COURSE_CREDITS_REL_USER_CREDITS);
	$table_options_history = Database :: get_main_table(CS_TABLE_OPTIONS_HISTORY);
	$sql = " SELECT T3.code AS col0, title AS col1, SUM(IF(T2.credits IS NULL, 0,T2.credits))+SUM(IF(T4.credits IS NULL, 0, T4.credits)) AS col2, T3.code AS col3 FROM $course_credits_rel_user_credits T3";
	$sql .= " LEFT JOIN $table_options_history T4 ON T4.options_history_id = T3.options_history_id LEFT JOIN $course_credits T2 ON T3.code = T2.code AND T3.option_id = T2.option_id LEFT JOIN $course_table T1 ON T1.code = T3.code";
	$sql .= " WHERE T1.code IN (SELECT course_code FROM $course_rel_user_table WHERE (role like 'Professor') AND (user_id=$user_id))";	
	if (isset ($_GET['keyword']))
		{
			$keyword = mysql_real_escape_string($_GET['keyword']);
			$sql .= "AND ((T1.code LIKE '%".$keyword."%' OR if (T4.credits IS NULL,T2.credits,T4.credits) LIKE '%".$keyword."%' OR title LIKE '%".$keyword."%'))";
		}
	$sql .= " GROUP BY T3.code";
	$sql .= " ORDER BY col$column $direction ";
	$sql .= " LIMIT $from,$number_of_items ";
	$res = api_sql_query($sql, __FILE__, __LINE__);
	$show = array ();
	while ($show2 = mysql_fetch_array($res))
		$show[] = $show2;
	echo "<div align=\"right\">";
	echo '<a href="courses_historics_teacher.php?name='.$complete_name.'&action=export&type=csv&column='.$column.'&keyword='.$keyword.'&direction='.$direction.'"><img align="absbottom" src="../../main/img/file_xls.gif">'.get_lang('ExportAsCSV').'</a>';
	echo "</div>";
	return $show;
}

//Get the total number of payment courses and the total number of credits spent in all the teacher's courses
function get_total_credits_data($from, $number_of_items, $column, $direction)
	{
		$user_id = api_get_user_id ();
		$course_rel_user_table = Database :: get_main_table(TABLE_MAIN_COURSE_USER);
		$course_credits_table = Database :: get_main_table(CS_TABLE_COURSE_CREDITS);
		$course_credits_rel_user_credits_table = Database :: get_main_table(CS_TABLE_COURSE_CREDITS_REL_USER_CREDITS);
		$table_options_history = Database :: get_main_table(CS_TABLE_OPTIONS_HISTORY);
		$course_table = Database :: get_main_table(TABLE_MAIN_COURSE);
		$sql .= " SELECT COUNT(DISTINCT T2.code) AS col0,SUM(IF(T4.credits IS NULL, 0,T4.credits))+SUM(IF(T1.credits IS NULL, 0, T1.credits)) AS col1 FROM $course_table T3,$course_credits_rel_user_credits_table T2";
		$sql .= " LEFT JOIN $table_options_history T4 ON T2.options_history_id = T4.options_history_id LEFT JOIN $course_credits_table T1 ON T2.code = T1.code AND T2.option_id = T1.option_id";
		$sql .= " WHERE T3.code = T2.code AND T3.code IN (SELECT course_code FROM $course_rel_user_table WHERE (role like 'Professor') AND (user_id=$user_id))";
		$sql .= " LIMIT $from,$number_of_items";
		mysql_query($sql);
		echo mysql_error();
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
			'<a href="course_subscription_data.php?code='.$code.'"><img src="../../main/img/synthese_view.gif" border="0" style="vertical-align: middle" title="'.get_lang('Info').'" alt="'.get_lang('Info').'"/></a>&nbsp;';
	}

/*
==============================================================================
		MAIN CODE
==============================================================================
*/ 

// Put your main code here. Keep this section short,
// it's better to use functions for any non-trivial code


//Create a sortable table with the total number of credits spent in all courses and the total number of payment courses 
	$table2 = new SortableTable('number_credits','get_number_of_rows' , 'get_total_credits_data',2,1);
	$table2->set_header(0, get_lang('TotalNumberOfCourses'));
	$table2->set_header(1, get_lang('TotalNumberOfCredits'));
	$table2->display();

// Create a search-box
	$form = new FormValidator('search_simple','get','','',null,false);
	$renderer =& $form->defaultRenderer();
	$renderer->setElementTemplate('<span>{element}</span> ');
	$form->addElement('text','keyword',get_lang('keyword'));
	$form->addElement('submit','submit',get_lang('Search'));
	$form->display();
	
//Create a sortable with the payment course's data
	$table = new SortableTable('courses_teacher', 'get_number_of_courses', 'get_courses_data_of_a_teacher',2);
	$table->set_additional_parameters($parameters);
	$table->set_header(0, get_lang('CourseCode'));
	$table->set_header(1, get_lang('TitleCourse'));
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