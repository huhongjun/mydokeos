<?php
/*
==============================================================================
	Dokeos - elearning and course management software

	Copyright (c) 2004 Dokeos S.A.
	Copyright (c) 2003 University of Ghent (UGent)
	Copyright (c) 2001 Universite catholique de Louvain (UCL)

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
// Reset the current course to show all the possible courses.
$language_file = "plugin_credits_system";
if ($_GET['cidReset'] == true)
	$cidReset = true; 


//require_once(api_get_path(WEB_PLUGIN_PATH).'credits_system/inc/cs_database.lib.php');

/*
-----------------------------------------------------------
	Header
	include the HTTP, HTML headers plus the top banner
-----------------------------------------------------------
*/
include_once('../../main/inc/global.inc.php');
require_once(api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php');
require_once(dirname(__FILE__).'/inc/cs_functions.inc.php');
require_once (api_get_path(LIBRARY_PATH).'sortabletable.class.php');

api_block_anonymous_users();


$interbreadcrumb[] = array ("url" => 'my_credits.php', "name" => get_lang('CreditsSystem'));
$nameTools = get_lang('ManageCourseCredits');

//require_once(dirname(__FILE__).'/header2_and_3.php');

Display :: display_header($nameTools);

//Show the Pay Courses as default view
if (!isset($_GET['table']))
	$_GET['table']='pay';	
$table_type = $_GET['table'];

if (($table_type == 'free') AND (!isset($_SESSION['_course'])))
{
	$pay_course = false;
	$table_link = '<a href="?table=pay&view='.$_GET['view'].'">'.get_lang('ShowCreditCourses').'</a>';
	$parameters['table'] = 'free';
	$select = array('enable'=> get_lang('EnableCreditCourse'));
	$title = 'Courses';

} 
else if (($_GET['table']='pay') && (!isset($_SESSION['_course'])))
{
	$pay_course = true;
	$table_link = '<a href="?table=free&view='.$_GET['view'].'">'.get_lang('ShowFreeCourses').'</a>';
	$parameters['table'] = 'pay';
	$select = array('disable'=> get_lang('DisableCreditCourse'));
	$title = 'Courses';
}
else if (isset($_SESSION['_course']))
{	
	$table_type = 'current';
	$title = 'Course';	
}
	
api_display_tool_title(get_lang(ucfirst($table_type).''.$title));

$all_courses_link = (isset($_SESSION['_course']))?'<a href="?table=pay&view='.$_GET['view'].'&cidReset=true">'.get_lang('ShowAllCourses').'</a>':'';
echo $table_link;
echo $all_courses_link;

/**
 * Get the number of pay courses which will be displayed
 */
function get_number_of_pay_courses()
{
	$complete_name = $_SESSION['_user']['lastName'].' '.$_SESSION['_user']['firstName'];
	$course_table = Database :: get_main_table(TABLE_MAIN_COURSE);
	$course_credits_table = Database :: get_main_table(CS_TABLE_COURSE_CREDITS);
	$sql = "SELECT COUNT(code) AS total_number_of_items FROM $course_table WHERE code IN (SELECT code FROM $course_credits_table)";
	if (!($_GET['view'] == 'admin' && api_is_platform_admin()))
	{
		$sql.= 'AND tutor_name="'.$complete_name.'"';	
	}
	//echo $sql;
	$res = api_sql_query($sql, __FILE__, __LINE__);
	$obj = mysql_fetch_object($res);
	return $obj->total_number_of_items;
}

/**
 * Get the number of free courses which will be displayed
 */
function get_number_of_free_courses()
{
	$complete_name = $_SESSION['_user']['lastName'].' '.$_SESSION['_user']['firstName'];
	$course_table = Database :: get_main_table(TABLE_MAIN_COURSE);
	$course_credits_table = Database :: get_main_table(CS_TABLE_COURSE_CREDITS);
	$sql = "SELECT COUNT(code) AS total_number_of_items FROM $course_table WHERE tutor_name='".$complete_name."' AND code NOT IN (SELECT code FROM $course_credits_table)";
	if (!($_GET['view'] == 'admin' && api_is_platform_admin()))
	{
		$sql.= 'AND tutor_name="'.$complete_name.'"';	
	}
	$res = api_sql_query($sql, __FILE__, __LINE__);
	$obj = mysql_fetch_object($res);
	return $obj->total_number_of_items;
}

/**
 * Get free course data to display
 */
function get_free_course_data($from, $number_of_items, $column, $direction)
{
	$complete_name = $_SESSION['_user']['lastName'].' '.$_SESSION['_user']['firstName'];
	//echo $complete_name;
	$course_table = Database :: get_main_table(TABLE_MAIN_COURSE);
	$course_credits_table = Database :: get_main_table(CS_TABLE_COURSE_CREDITS);
	$sql = "SELECT code AS col0, visual_code AS col1, title AS col2, course_language AS col3, category_code AS col4, subscribe AS col5, unsubscribe AS col6, code AS col7 FROM $course_table WHERE code NOT IN (SELECT code FROM $course_credits_table)";
	if (!($_GET['view'] == 'admin' && api_is_platform_admin()))
	{
		$sql.= 'AND tutor_name="'.$complete_name.'"';	
	}
	$sql .= " ORDER BY col$column $direction ";
	$sql .= " LIMIT $from,$number_of_items";
	$res = api_sql_query($sql, __FILE__, __LINE__);
	$courses = array ();
	while ($course = mysql_fetch_row($res))
	{
		$course[5] = $course[5] == SUBSCRIBE_ALLOWED ? get_lang('Yes') : get_lang('No');
		$course[6] = $course[6] == UNSUBSCRIBE_ALLOWED ? get_lang('Yes') : get_lang('No');
		//$course[7] = (cs_get_course_payment_options($course[col0])>0)?get_lang('Yes'):get_lang('No');
		$courses[] = $course;
	}
	return $courses;
}

/**
 * Get pay course data to display
 */
function get_pay_course_data($from, $number_of_items, $column, $direction)
{
	$complete_name = $_SESSION['_user']['lastName'].' '.$_SESSION['_user']['firstName'];
	//echo $complete_name;
	$course_table = Database :: get_main_table(TABLE_MAIN_COURSE);
	$course_credits_table = Database :: get_main_table(CS_TABLE_COURSE_CREDITS);
	$sql = "SELECT code AS col0, visual_code AS col1, title AS col2, course_language AS col3, category_code AS col4, subscribe AS col5, unsubscribe AS col6, code AS col7 FROM $course_table WHERE code IN (SELECT code FROM $course_credits_table)";
	if (!($_GET['view'] == 'admin' && api_is_platform_admin()))
	{
		$sql.= 'AND tutor_name="'.$complete_name.'"';	
	}
	$sql .= " ORDER BY col$column $direction ";
	$sql .= " LIMIT $from,$number_of_items";
	$res = api_sql_query($sql, __FILE__, __LINE__);
	$courses = array ();
	while ($course = mysql_fetch_row($res))
	{
		$course[5] = $course[5] == SUBSCRIBE_ALLOWED ? get_lang('Yes') : get_lang('No');
		$course[6] = $course[6] == UNSUBSCRIBE_ALLOWED ? get_lang('Yes') : get_lang('No');
		//$course[7] = (cs_get_course_payment_options($course[col0])>0)?get_lang('Yes'):get_lang('No');
		$courses[] = $course;
	}
	return $courses;
}

function get_current_course_data($from, $number_of_items, $column, $direction)
{
	$complete_name = $_SESSION['_user']['lastName'].' '.$_SESSION['_user']['firstName'];
	//echo $complete_name;
	$course_table = Database :: get_main_table(TABLE_MAIN_COURSE);
	$course_credits_table = Database :: get_main_table(CS_TABLE_COURSE_CREDITS);
	$sql = "SELECT code AS col0, visual_code AS col1, title AS col2, course_language AS col3, category_code AS col4, subscribe AS col5, unsubscribe AS col6, code AS col7 FROM $course_table WHERE code ='".$_SESSION['_course']['sysCode']."'";
	if (!($_GET['view'] == 'admin' && api_is_platform_admin()))
	{
		$sql.= 'AND tutor_name="'.$complete_name.'"';	
	}
	$sql .= " ORDER BY col$column $direction ";
	$sql .= " LIMIT $from,$number_of_items";
	$res = api_sql_query($sql, __FILE__, __LINE__);
	$courses = array ();
	while ($course = mysql_fetch_row($res))
	{
		
		$course[0] = '';
		$course[5] = $course[5] == SUBSCRIBE_ALLOWED ? get_lang('Yes') : get_lang('No');
		$course[6] = $course[6] == UNSUBSCRIBE_ALLOWED ? get_lang('Yes') : get_lang('No');
		//$course[7] = (cs_get_course_payment_options($course[col0])>0)?get_lang('Yes'):get_lang('No');
		$courses[] = $course;
	}
	return $courses;
}

function get_number_of_current_courses()
{
	return 1;
}

function modify_filter($code)
{
	
	$current_pay = false;
	if (isset($_SESSION['_course']))
	{
		$current_pay = (cs_course_payment_options_number($_SESSION['_course']['sysCode']))>0;
	}
	$links = ((($_GET['table']=='pay')&&(!isset($_SESSION['_course'])))||$current_pay)?'<a href="edit_pay_course.php?action=current&code='.$code.'"><img src="../../main/img/synthese_view.gif" border="0" style="vertical-align: middle" title="'.get_lang('CurrentPaymentOptions').'" alt="'.get_lang('CurrentPaymentOptions').'"/></a>&nbsp;'.
			'<a href="?table=pay&view='.$_GET['view'].'&action=disable&code='.$code.'" onclick="javascript:if(!confirm('."'".addslashes(htmlspecialchars(get_lang("ConfirmYourChoice")))."'".')) return false; "><img src="img/note_cancelled.gif" border="0" style="vertical-align: middle" title="'.get_lang('DisablePaymentOption').'" alt="'.get_lang('DisablePaymentOption').'"/></a>&nbsp;'
			:'<a href="?table=free&view='.$_GET['view'].'&action=enable&code='.$code.'"><img src="img/note.gif" border="0" style="vertical-align: middle" title="'.get_lang('EnablePaymentOption').'" alt="'.get_lang('EnablePaymentOption').'"/></a>&nbsp;';
	$links.= '<a href="../course_home/course_home.php?cidReq='.$code.'"><img src="../../main/img/course_home.gif" border="0" style="vertical-align: middle" title="'.get_lang('CourseHomepage').'" alt="'.get_lang('CourseHomepage').'"/></a>&nbsp;';
	return ($links);
				//'<a href="edit_pay_course.php?action=edit&code='.$code.'"><img src="../../main/img/edit.gif" border="0" style="vertical-align: middle" title="'.get_lang('Edit').'" alt="'.get_lang('Edit').'"/></a>&nbsp;'.
				//'<a href="edit_pay_course.php?action=delete&code='.$code.'"><img src="../../main/img/delete.gif" border="0" style="vertical-align: middle" title="'.get_lang('Delete').'" alt="'.get_lang('Delete').'"/></a>');
}

if (isset ($_POST['action']))
{
	$course_codes = $_POST['selected_courses'];	
	switch ($_POST['action'])
	{
		// Make courses selected, free.
		case 'disable' :
			if (count($course_codes) > 0)
			{
				foreach ($course_codes as $index => $course_code)
				{
					$error = cs_delete_course_payment_option($course_code);
				}
				Display :: display_normal_message ('You made '.count($course_codes).' courses free');
			}
			break;
		// Enable payment options for the courses selected.
		case 'enable' :
			if (count($course_codes) > 0)
			{
				foreach ($course_codes as $index => $course_code)
				{
					$error = cs_enable_payment_options($course_code);
				}
				Display :: display_normal_message ('You enable payment options for '.count($course_codes).' courses');
			}
			break;			
		
	}
}
//echo
if (isset ($_GET['action']))
{
	switch ($_GET['action'])
	{
		// Make course selected, free.
		case 'disable' :
			if (isset ($_GET['code']))
			{
				$error = !cs_delete_course_payment_option($_GET['code']);
				!$error?Display :: display_normal_message(get_lang('DisabledCreditCourse')):Display :: display_normal_message(get_lang('DisableCreditCourseError'));
			}
			break;
		// Enable payment options for the courses selected.
		case 'enable' :
			if (isset ($_GET['code']))
			{
				$error = !cs_enable_payment_options($_GET['code']);
				!$error?Display :: display_normal_message (get_lang('EnabledCreditCourse')):Display :: display_normal_message ('EnableCreditCourseError');
			}
			break;		
	}
}
	//$options_number= count(cs_get_course_payment_options($_SESSION['_course']['sysCode']));
	$table = new SortableTable('my_courses','get_number_of_'.$table_type.'_courses', 'get_'.$table_type.'_course_data',4);
	$table->set_additional_parameters($parameters);
	$table->set_header(0, '', false);
	$table->set_header(1, get_lang('Code'));
	$table->set_header(2, get_lang('Title'));
	$table->set_header(3, get_lang('Language'));
	$table->set_header(4, get_lang('Category'));
	$table->set_header(5, get_lang('SubscriptionAllowed'));
	$table->set_header(6, get_lang('UnsubscriptionAllowed'));
	$table->set_header(7, '', false);
	/*if (isset($_SESSION['_course']))
	{
		$table->set_column_filter(7,'hide_column');
	}*/
	$table->set_column_filter(7,'modify_filter');
	$table->set_form_actions($select,'selected_courses');
	$table->display();
	
//End Course Settings Menu



/*
==============================================================================
		FOOTER
==============================================================================
*/
Display :: display_footer();

?>
