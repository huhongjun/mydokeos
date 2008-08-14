<?php // $Id: my_credits_courses_info.php,v 1.0 2007/04/19 14:34:45 poty Exp $
/*
==============================================================================
	Dokeos - elearning and course management software
	
	Copyright (c) 2004-2006 Dokeos S.A.
	Copyright (c) E.U.I. Universidad Politécnica de Madrid (Spain)
	Copyright (c) Borja Nuñez Salinas - Programmer (bns@alumnos.upm.es)
	
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
*	This script display the credits courses that user have paid.
*
*	@package dokeos.plugin
==============================================================================
*/
/*
==============================================================================
		INIT SECTION
==============================================================================
*/ 

$language_file = 'plugin_credits_system';
include("../../main/inc/global.inc.php");
api_block_anonymous_users();
/*
-----------------------------------------------------------
	Libraries
-----------------------------------------------------------
*/ 

require_once(api_get_path(LIBRARY_PATH).'/sortabletable.class.php');
require_once(api_get_path(LIBRARY_PATH).'/course.lib.php');
include_once(dirname(__FILE__).'\inc\cs_database.lib.php');

/*
==============================================================================
		FUNCTIONS
==============================================================================
*/ 
/**
 * Build the Tittle-column of the table
 * @param varchar $code The course id
 * @return link to course homepage.
 */
function title_filter($code)
{
	$course_info = CourseManager::get_course_information($code);
	//foreach ($course_info as $key => $value){echo $key.'>'.$value;}echo'<br/>';
	$link = '<a href="';
	$link .= api_get_path(WEB_COURSE_PATH);
	$link .= $course_info['directory'];
	$link .= '">';
	$link .= $course_info['title'];
	$link .= '</a>';

	return $link;
}

/**
 * Get the total number of users on the platform
 * @see SortableTable#get_total_number_of_items()
 */
function get_number_of_credit_courses()
{
	$table_COURSE_CREDITS_REL_USER_CREDITS = Database :: get_main_table(CS_TABLE_COURSE_CREDITS_REL_USER_CREDITS);
	$sql = 'SELECT * FROM '.$table_COURSE_CREDITS_REL_USER_CREDITS.
		   ' WHERE end_date >= CURRENT_TIMESTAMP AND user_id='.api_get_user_id();
	$res = api_sql_query($sql, __FILE__, __LINE__);
	return mysql_num_rows($res);
}

/**
 * Get the users to display on the current page.
 * @see SortableTable#get_table_data($from)
 */
function get_credit_course_data($from, $number_of_items, $column, $direction)
{
	$table_COURSE_CREDITS_REL_USER_CREDITS = Database :: get_main_table(CS_TABLE_COURSE_CREDITS_REL_USER_CREDITS);
	$table_course = Database :: get_main_table(TABLE_MAIN_COURSE);
	$sql = 'SELECT '.$table_course.'.visual_code as col0'.
				', '.$table_course.'.code as col1'.
				', '.$table_course.'.tutor_name as col2'.
				', '.$table_COURSE_CREDITS_REL_USER_CREDITS.'.init_date as col3'.
				', '.$table_COURSE_CREDITS_REL_USER_CREDITS.'.end_date as col4'.
		   ' FROM '.$table_course.', '.$table_COURSE_CREDITS_REL_USER_CREDITS.
		   ' WHERE '.$table_course.'.code = '.$table_COURSE_CREDITS_REL_USER_CREDITS.'.code'.
					' AND end_date >= CURRENT_TIMESTAMP AND user_id='.api_get_user_id();
	$sql .= " ORDER BY col$column $direction ";
	$sql .= " LIMIT $from,$number_of_items";
	$res = api_sql_query($sql, __FILE__, __LINE__);
	$users = array ();
	while ($user = mysql_fetch_row($res))
	{
		$users[] = $user;
	}
	return $users;
}

/*
==============================================================================
		MAIN CODE
==============================================================================
*/ 

if (get_number_of_credit_courses())
{
	$table = new SortableTable('my_curses', 'get_number_of_credit_courses', 'get_credit_course_data',2);
	$table->set_header(0, get_lang('visual_code'));	
	$table->set_header(1, get_lang('Title'));
	$table->set_header(2, get_lang('tutor_name'));
	$table->set_header(3, get_lang('SubscriptionDate'));
	$table->set_header(4, get_lang('SubscriptionEndDate'));
	$table->set_column_filter(1, 'title_filter');
	$table->display();
}
else
{
	echo get_lang("NotValidSub");
}

?>
