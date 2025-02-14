<?php
// $Id: course_list.php 15245 2008-05-08 16:53:52Z juliomontoya $
/*
==============================================================================
	Dokeos - elearning and course management software

	Copyright (c) 2008 Dokeos SPRL
	Copyright (c) 2003 Ghent University (UGent)
	Copyright (c) 2001 Universite catholique de Louvain (UCL)
	Copyright (c) Olivier Brouckaert
	Copyright (c) Bart Mollet, Hogeschool Gent

	For a full list of contributors, see "credits.txt".
	The full license can be read in "license.txt".

	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	See the GNU General Public License for more details.

	Contact address: Dokeos, rue du Corbeau, 108, B-1030 Brussels, Belgium
	Mail: info@dokeos.com
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

// name of the language file that needs to be included 
$language_file = 'admin';
$cidReset = true;
require ('../inc/global.inc.php');
$this_section = SECTION_PLATFORM_ADMIN;

api_protect_admin_script();
require_once (api_get_path(LIBRARY_PATH).'course.lib.php');
require_once (api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php');
require_once (api_get_path(LIBRARY_PATH).'sortabletable.class.php');
$get_keyword=trim($_GET['keyword']);
$get_keyword_code=trim($_GET['keyword_code']);
/**
 * Get the number of courses which will be displayed
 */
function get_number_of_courses()
{
	global $get_keyword,$get_keyword_code;
	$course_table = Database :: get_main_table(TABLE_MAIN_COURSE);
	$sql = "SELECT COUNT(code) AS total_number_of_items FROM $course_table";
	if (isset ($get_keyword))
	{
		$keyword = Database::escape_string($get_keyword);
		$sql .= " WHERE title LIKE '%".$keyword."%' OR code LIKE '%".$keyword."%'";
	}
	elseif (isset ($get_keyword_code))
	{
		$keyword_code = Database::escape_string($get_keyword_code);
		$keyword_title = Database::escape_string($_GET['keyword_title']);
		$keyword_category = Database::escape_string($_GET['keyword_category']);
		$keyword_language = Database::escape_string($_GET['keyword_language']);
		$keyword_visibility = Database::escape_string($_GET['keyword_visibility']);
		$keyword_subscribe = Database::escape_string($_GET['keyword_subscribe']);
		$keyword_unsubscribe = Database::escape_string($_GET['keyword_unsubscribe']);
		$sql .= " WHERE code LIKE '%".$keyword_code."%' AND title LIKE '%".$keyword_title."%' AND category_code LIKE '%".$keyword_category."%'  AND course_language LIKE '%".$keyword_language."%'   AND visibility LIKE '%".$keyword_visibility."%'    AND subscribe LIKE '".$keyword_subscribe."'AND unsubscribe LIKE '".$keyword_unsubscribe."'";
	}
	$res = api_sql_query($sql, __FILE__, __LINE__);
	$obj = Database::fetch_object($res);
	return $obj->total_number_of_items;
}
/**
 * Get course data to display
 */
function get_course_data($from, $number_of_items, $column, $direction)
{
	global $get_keyword,$get_keyword_code;
	$course_table = Database :: get_main_table(TABLE_MAIN_COURSE);
	$users_table = Database :: get_main_table(TABLE_MAIN_USER);
	$course_users_table = Database :: get_main_table(TABLE_MAIN_COURSE_USER);
	$main_category_table = Database :: get_main_table(TABLE_MAIN_CATEGORY);
	//edit by xiaoping
	$sql = "SELECT t1.code AS col0, visual_code AS col1, title AS col2, course_language AS col3, t2.name AS col4, subscribe AS col5, unsubscribe AS col6, t1.code AS col7, tutor_name as col8, t1.code AS col9, visibility AS col10 FROM $course_table t1 inner join $main_category_table t2 on t1.category_code=t2.code";
	if (isset ($get_keyword))
	{
		$keyword = Database::escape_string($get_keyword);
		$sql .= " WHERE title LIKE '%".$keyword."%' OR t1.code LIKE '%".$keyword."%'";
	}
	elseif (isset ($get_keyword_code))
	{
		$keyword_code = Database::escape_string($get_keyword_code);
		$keyword_title = Database::escape_string($_GET['keyword_title']);
		$keyword_category = Database::escape_string($_GET['keyword_category']);
		$keyword_language = Database::escape_string($_GET['keyword_language']);
		$keyword_visibility = Database::escape_string($_GET['keyword_visibility']);
		$keyword_subscribe = Database::escape_string($_GET['keyword_subscribe']);
		$keyword_unsubscribe = Database::escape_string($_GET['keyword_unsubscribe']);
		$sql .= " WHERE t1.code LIKE '%".$keyword_code."%' AND title LIKE '%".$keyword_title."%' AND category_code LIKE '%".$keyword_category."%'  AND course_language LIKE '%".$keyword_language."%'   AND visibility LIKE '%".$keyword_visibility."%'    AND subscribe LIKE '".$keyword_subscribe."'AND unsubscribe LIKE '".$keyword_unsubscribe."'";
	}
	$sql .= " ORDER BY col$column $direction ";
	$sql .= " LIMIT $from,$number_of_items";
	$res = api_sql_query($sql, __FILE__, __LINE__);
	$courses = array ();
	while ($course = Database::fetch_row($res))
	{
		//place colour icons in front of courses
		$course[1] = get_course_visibility_icon($course[10]).$course[1];
		$course[3] = $course[3] == 'simpl_chinese'?get_lang('simpl_chinese') : $course[3];//by xiaoping
		$course[5] = $course[5] == SUBSCRIBE_ALLOWED ? get_lang('yes') : get_lang('no');
		$course[6] = $course[6] == UNSUBSCRIBE_ALLOWED ? get_lang('yes') : get_lang('no');
		$course[7] = CourseManager :: is_virtual_course_from_system_code($course[7]) ? get_lang('yes') : get_lang('no');
		$course_rem = array($course[0],$course[1],$course[2],$course[3],$course[4],$course[5],$course[6],$course[7],$course[8],$course[9]);
		$courses[] = $course_rem;
	}
	return $courses;
}
/**
 * Filter to display the edit-buttons
 */
function modify_filter($code)
{
	global $charset;
	return
		'<a href="course_information.php?code='.$code.'"><img src="../img/synthese_view.gif" border="0" style="vertical-align: middle" title="'.get_lang('Info').'" alt="'.get_lang('Info').'"/></a>&nbsp;'.
		'<a href="../course_home/course_home.php?cidReq='.$code.'"><img src="../img/course_home.gif" border="0" style="vertical-align: middle" title="'.get_lang('CourseHomepage').'" alt="'.get_lang('CourseHomepage').'"/></a>&nbsp;'.
		'<a href="../tracking/courseLog.php?cidReq='.$code.'"><img src="../img/statistics.gif" border="0" style="vertical-align: middle" title="'.get_lang('Tracking').'" alt="'.get_lang('Tracking').'"/></a>&nbsp;'.
		'<a href="course_edit.php?course_code='.$code.'"><img src="../img/edit.gif" border="0" style="vertical-align: middle" title="'.get_lang('Edit').'" alt="'.get_lang('Edit').'"/></a>&nbsp;'.
		'<a href="course_list.php?delete_course='.$code.'"  onclick="javascript:if(!confirm('."'".addslashes(htmlspecialchars(get_lang("ConfirmYourChoice"),ENT_QUOTES,$charset))."'".')) return false;"><img src="../img/delete.gif" border="0" style="vertical-align: middle" title="'.get_lang('Delete').'" alt="'.get_lang('Delete').'"/></a>';	
}
/**
 * Return an icon representing the visibility of the course
 */
function get_course_visibility_icon($v)
{
	$path = api_get_path(REL_CODE_PATH);
	$style = 'style="margin-bottom:-5px;margin-right:5px;"';
	switch($v)
	{
		case 0:
			return '<img src="'.$path.'img/bullet_red.gif" title="'.get_lang('CourseVisibilityClosed').'" '.$style.' />';
			break;
		case 1:
			return '<img src="'.$path.'img/bullet_orange.gif" title="'.get_lang('Private').'" '.$style.' />';
			break;
		case 2:
			return '<img src="'.$path.'img/bullet_green.gif" title="'.get_lang('OpenToThePlatform').'" '.$style.' />';
			break;
		case 3:
			return '<img src="'.$path.'img/bullet_blue.gif" title="'.get_lang('OpenToTheWorld').'" '.$style.' />';
			break;
		default:
			return '';
	}
}

if (isset ($_POST['action']))
{
	switch ($_POST['action'])
	{
		// Delete selected courses
		case 'delete_courses' :
			$course_codes = $_POST['course'];
			if (count($course_codes) > 0)
			{
				foreach ($course_codes as $index => $course_code)
				{
					CourseManager :: delete_course($course_code);
				}
			}
			break;
	}
}
if (isset ($_GET['search']) && $_GET['search'] == 'advanced')
{
	// Get all course categories
	$table_course_category = Database :: get_main_table(TABLE_MAIN_CATEGORY);
	$sql = "SELECT code,name FROM ".$table_course_category." WHERE auth_course_child ='TRUE' ORDER BY tree_pos";
	$res = api_sql_query($sql, __FILE__, __LINE__);
	$categories['%'] = get_lang('All');
	while ($cat = Database::fetch_array($res))
	{
		$categories[$cat['code']] = '('.$cat['code'].') '.$cat['name'];
	}
	$interbreadcrumb[] = array ("url" => 'index.php', "name" => get_lang('PlatformAdmin'));
	$interbreadcrumb[] = array ("url" => 'course_list.php', "name" => get_lang('CourseList'));
	$tool_name = get_lang('SearchACourse');
	Display :: display_header($tool_name);
	//api_display_tool_title($tool_name);
	$form = new FormValidator('advanced_course_search', 'get');
	$form->add_textfield('keyword_code', get_lang('CourseCode'), false);
	$form->add_textfield('keyword_title', get_lang('Title'), false);
	$form->addElement('select', 'keyword_category', get_lang('CourseFaculty'), $categories);
	$el = & $form->addElement('select_language', 'keyword_language', get_lang('CourseLanguage'));
	$el->addOption(get_lang('All'), '%');
	$form->addElement('radio', 'keyword_visibility', get_lang("CourseAccess"), get_lang('OpenToTheWorld'), COURSE_VISIBILITY_OPEN_WORLD);
	$form->addElement('radio', 'keyword_visibility', null, get_lang('OpenToThePlatform'), COURSE_VISIBILITY_OPEN_PLATFORM);
	$form->addElement('radio', 'keyword_visibility', null, get_lang('Private'), COURSE_VISIBILITY_REGISTERED);
	$form->addElement('radio', 'keyword_visibility', null, get_lang('CourseVisibilityClosed'), COURSE_VISIBILITY_CLOSED);
	$form->addElement('radio', 'keyword_visibility', null, get_lang('All'), '%');
	$form->addElement('radio', 'keyword_subscribe', get_lang('Subscription'), get_lang('Allowed'), 1);
	$form->addElement('radio', 'keyword_subscribe', null, get_lang('Denied'), 0);
	$form->addElement('radio', 'keyword_subscribe', null, get_lang('All'), '%');
	$form->addElement('radio', 'keyword_unsubscribe', get_lang('Unsubscription'), get_lang('AllowedToUnsubscribe'), 1);
	$form->addElement('radio', 'keyword_unsubscribe', null, get_lang('NotAllowedToUnsubscribe'), 0);
	$form->addElement('radio', 'keyword_unsubscribe', null, get_lang('All'), '%');
	$form->addElement('submit', 'submit', get_lang('Ok'));
	$defaults['keyword_language'] = '%';
	$defaults['keyword_visibility'] = '%';
	$defaults['keyword_subscribe'] = '%';
	$defaults['keyword_unsubscribe'] = '%';
	$form->setDefaults($defaults);
	$form->display();
}
else
{
	$interbreadcrumb[] = array ("url" => 'index.php', "name" => get_lang('PlatformAdmin'));
	$tool_name = get_lang('CourseList');
	Display :: display_header($tool_name);
	//api_display_tool_title($tool_name);
	if (isset ($_GET['delete_course']))
	{
		CourseManager :: delete_course($_GET['delete_course']);
	}
	// Create a search-box
	$form = new FormValidator('search_simple','get','','',null,false);
	$renderer =& $form->defaultRenderer();
	$renderer->setElementTemplate('<span>{element}</span> ');
	$form->addElement('text','keyword',get_lang('keyword'));
	$form->addElement('submit','submit',get_lang('Search'));
	$form->addElement('static','search_advanced_link',null,'<a href="course_list.php?search=advanced">'.get_lang('AdvancedSearch').'</a>');
	$form->display();
	// Create a sortable table with the course data
	$table = new SortableTable('courses', 'get_number_of_courses', 'get_course_data',2);
	$parameters=array();
	$table->set_additional_parameters($parameters);
	$table->set_header(0, '', false);
	$table->set_header(1, get_lang('Code'));
	$table->set_header(2, get_lang('Title'));
	$table->set_header(3, get_lang('Language'));
	$table->set_header(4, get_lang('Category'));
	$table->set_header(5, get_lang('SubscriptionAllowed'));
	$table->set_header(6, get_lang('UnsubscriptionAllowed'));
	$table->set_header(7, get_lang('IsVirtualCourse'));
	$table->set_header(8, get_lang('Teacher'));
	$table->set_header(9, get_lang('actions'), false,'width="120px"');	
	$table->set_column_filter(9,'modify_filter');	
	$table->set_form_actions(array ('delete_courses' => get_lang('DeleteCourse')),'course');
	$table->display();
}
/*
==============================================================================
		FOOTER 
==============================================================================
*/
Display :: display_footer();
?>