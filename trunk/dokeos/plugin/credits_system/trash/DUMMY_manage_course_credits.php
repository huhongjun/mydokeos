<?php // $Id: template.php,v 1.2 2006/03/15 14:34:45 pcool Exp $
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
include("../../main/inc/global.inc.php");
$language_file = 'plugin_credits_system';
//api_protect_admin_script(); ENABLE!!JUST FOR TEST 

/*
-----------------------------------------------------------
	Libraries
-----------------------------------------------------------
*/ 
//the main_api.lib.php, database.lib.php and display.lib.php
//libraries are included by default

	
/*
-----------------------------------------------------------
	Header
-----------------------------------------------------------
*/ 

//	Optional extra http or html header
//	If you need to add some HTTP/HTML headers code 
//	like JavaScript functions, stylesheets, redirects, put them here.

// $httpHeadXtra[] = ""; 
// $httpHeadXtra[] = ""; 
//    ... 
// 
// $htmlHeadXtra[] = ""; 
// $htmlHeadXtra[] = ""; 
//    ... 
$interbreadcrumb[] = array ("url" => api_get_path(PLUGIN_PATH).'my_credits.php', "name" => 'Credits System');
$tool_name = "Manage Course Credits"; // title of the page (should come from the language file) 
Display::display_header($tool_name);

	
/*
==============================================================================
		FUNCTIONS
==============================================================================
*/ 

// put your functions here
// if the list gets large, divide them into different sections:
// display functions, tool logic functions, database functions	
// try to place your functions into an API library or separate functions file - it helps reuse
	
/*
==============================================================================
		MAIN CODE
==============================================================================
*/ 

// Put your main code here. Keep this section short,
// it's better to use functions for any non-trivial code

api_display_tool_title($tool_name);

//DELETE!! JUST TEMP.
require_once (api_get_path(LIBRARY_PATH)."course.lib.php");
require_once (api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php');
require_once (api_get_path(LIBRARY_PATH).'sortabletable.class.php');
/**
 * Get the number of courses which will be displayed
 */
function get_number_of_courses()
{
	$course_table = Database :: get_main_table(TABLE_MAIN_COURSE);
	$sql = "SELECT COUNT(code) AS total_number_of_items FROM $course_table";
	if (isset ($_GET['keyword']))
	{
		$keyword = mysql_real_escape_string($_GET['keyword']);
		$sql .= " WHERE title LIKE '%".$keyword."%' OR code LIKE '%".$keyword."%'";
	}
	elseif (isset ($_GET['keyword_code']))
	{
		$keyword_code = mysql_real_escape_string($_GET['keyword_code']);
		$keyword_title = mysql_real_escape_string($_GET['keyword_title']);
		$keyword_category = mysql_real_escape_string($_GET['keyword_category']);
		$keyword_language = mysql_real_escape_string($_GET['keyword_language']);
		$keyword_visibility = mysql_real_escape_string($_GET['keyword_visibility']);
		$keyword_subscribe = mysql_real_escape_string($_GET['keyword_subscribe']);
		$keyword_unsubscribe = mysql_real_escape_string($_GET['keyword_unsubscribe']);
		$sql .= " WHERE code LIKE '%".$keyword_code."%' AND title LIKE '%".$keyword_title."%' AND category_code LIKE '%".$keyword_category."%'  AND course_language LIKE '%".$keyword_language."%'   AND visibility LIKE '%".$keyword_visibility."%'    AND subscribe LIKE '".$keyword_subscribe."'AND unsubscribe LIKE '".$keyword_unsubscribe."'";
	}
	$res = api_sql_query($sql, __FILE__, __LINE__);
	$obj = mysql_fetch_object($res);
	return $obj->total_number_of_items;
}
/**
 * Get course data to display
 */
function get_course_data($from, $number_of_items, $column, $direction)
{
	$course_table = Database :: get_main_table(TABLE_MAIN_COURSE);
	$sql = "SELECT code AS col0, visual_code AS col1, title AS col2, course_language AS col3, category_code AS col4, subscribe AS col5, unsubscribe AS col6, code AS col7, code AS col8 FROM $course_table";
	if (isset ($_GET['keyword']))
	{
		$keyword = mysql_real_escape_string($_GET['keyword']);
		$sql .= " WHERE title LIKE '%".$keyword."%' OR code LIKE '%".$keyword."%'";
	}
	elseif (isset ($_GET['keyword_code']))
	{
		$keyword_code = mysql_real_escape_string($_GET['keyword_code']);
		$keyword_title = mysql_real_escape_string($_GET['keyword_title']);
		$keyword_category = mysql_real_escape_string($_GET['keyword_category']);
		$keyword_language = mysql_real_escape_string($_GET['keyword_language']);
		$keyword_visibility = mysql_real_escape_string($_GET['keyword_visibility']);
		$keyword_subscribe = mysql_real_escape_string($_GET['keyword_subscribe']);
		$keyword_unsubscribe = mysql_real_escape_string($_GET['keyword_unsubscribe']);
		$sql .= " WHERE code LIKE '%".$keyword_code."%' AND title LIKE '%".$keyword_title."%' AND category_code LIKE '%".$keyword_category."%'  AND course_language LIKE '%".$keyword_language."%'   AND visibility LIKE '%".$keyword_visibility."%'    AND subscribe LIKE '".$keyword_subscribe."'AND unsubscribe LIKE '".$keyword_unsubscribe."'";
	}
	$sql .= " ORDER BY col$column $direction ";
	$sql .= " LIMIT $from,$number_of_items";
	$res = api_sql_query($sql, __FILE__, __LINE__);
	$courses = array ();
	while ($course = mysql_fetch_row($res))
	{
		$course[5] = $course[5] == SUBSCRIBE_ALLOWED ? get_lang('Yes') : get_lang('No');
		$course[6] = $course[6] == UNSUBSCRIBE_ALLOWED ? get_lang('Yes') : get_lang('No');
		$course[7] = CourseManager :: is_virtual_course_from_system_code($course[7]) ? get_lang('Yes') : get_lang('No');
		$courses[] = $course;
	}
	return $courses;
}
/**
 * Filter to display the edit-buttons
 */
function modify_filter($code)
{
	return
		/*'<a href="course_information.php?code='.$code.'"><img src="../img/synthese_view.gif" border="0" style="vertical-align: middle" title="'.get_lang('Info').'" alt="'.get_lang('Info').'"/></a>&nbsp;'.
		'<a href="../course_home/course_home.php?cidReq='.$code.'"><img src="../img/course_home.gif" border="0" style="vertical-align: middle" title="'.get_lang('CourseHomepage').'" alt="'.get_lang('CourseHomepage').'"/></a>&nbsp;'.
		'<a href="../tracking/courseLog.php?cidReq='.$code.'"><img src="../img/statistics.gif" border="0" style="vertical-align: middle" title="'.get_lang('Tracking').'" alt="'.get_lang('Tracking').'"/></a>&nbsp;'.
		'<a href="course_edit.php?course_code='.$code.'"><img src="../img/edit.gif" border="0" style="vertical-align: middle" title="'.get_lang('Edit').'" alt="'.get_lang('Edit').'"/></a>&nbsp;'.
		'<a href="course_list.php?delete_course='.$code.'"  onclick="javascript:if(!confirm('."'".addslashes(htmlentities(get_lang("ConfirmYourChoice")))."'".')) return false;"><img src="../img/delete.gif" border="0" style="vertical-align: middle" title="'.get_lang('Delete').'" alt="'.get_lang('Delete').'"/></a>';	*/
		'<select name="[]">
			<option value="1">1 session = 10c</option>
			<option value="2">1 day = 15c</option>
			<option value="3">1 week = 75c</option>
			<option value="4">6 month = 1250c</option>
			<option value="5">1 year = 2050c</option>
		</select>';
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
		case 'mod_course' :
			Display :: display_confirmation_message('Course Credits has been modified.');
	}
}
if (isset ($_GET['search']) && $_GET['search'] == 'advanced')
{
	// Get all course categories
	$table_course_category = Database :: get_main_table(TABLE_MAIN_CATEGORY);
	$sql = "SELECT code,name FROM ".$table_course_category." WHERE auth_course_child ='TRUE' ORDER BY tree_pos";
	$res = api_sql_query($sql, __FILE__, __LINE__);
	$categories['%'] = get_lang('All');
	while ($cat = mysql_fetch_array($res))
	{
		$categories[$cat['code']] = '('.$cat['code'].') '.$cat['name'];
	}
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
	$table->set_additional_parameters($parameters);
	$table->set_header(0, '', false);
	$table->set_header(1, get_lang('Code'));
	$table->set_header(2, get_lang('Title'));
	$table->set_header(3, get_lang('Language'));
	$table->set_header(4, get_lang('Category'));
	$table->set_header(5, get_lang('SubscriptionAllowed'));
	$table->set_header(6, get_lang('UnsubscriptionAllowed'));
	$table->set_header(7, get_lang('IsVirtualCourse'));
	$table->set_header(8, 'Credits', false);
	$table->set_column_filter(8,'modify_filter');
	$table->set_form_actions(array ('mod_course' => 'Modify course credits', 'delete_courses' => get_lang('DeleteCourse')),'course');
	$table->display();
	
	$form_credits = new FormValidator('mod_cred','get','','',null,false);
	$renderer =& $form_credits->defaultRenderer();
	$renderer->setElementTemplate('<span>{element}</span> ');
	$form_credits->addElement('static','','','Amount of credits to add/quit (+/- num) or to set (num): ');
	$form_credits->addElement('text','credits','credits','size="1", maxlength="4"');
	$form_credits->display();
}

//END DELETE


/*
==============================================================================
		FOOTER 
==============================================================================
*/ 
Display::display_footer();
?>
