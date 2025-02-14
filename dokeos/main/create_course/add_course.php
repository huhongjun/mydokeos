<?php
// $Id: add_course.php 15568 2008-06-12 05:23:42Z yannoo $
/*
==============================================================================
	Dokeos - elearning and course management software

	Copyright (c) 2004-2005 Dokeos S.A.
	Copyright (c) 2003 Ghent University (UGent)
	Copyright (c) 2001 Universite catholique de Louvain (UCL)
	Copyright (c) 2005 Bart Mollet, Hogeschool Gent

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
* This script allows professors and administrative staff to create course sites.
* @author Patrick Cool <patrick.cool@UGent.be>, Ghent University
* @author Roan Embrechts, refactoring
* @package dokeos.create_course
==============================================================================
*/

// name of the language file that needs to be included 
$language_file = "create_course";

// including the global file
include ('../inc/global.inc.php');

// section for the tabs
$this_section=SECTION_COURSES;

// include configuration file
include (api_get_path(CONFIGURATION_PATH).'add_course.conf.php');

// include additional libraries
include_once (api_get_path(LIBRARY_PATH).'add_course.lib.inc.php');
include_once (api_get_path(LIBRARY_PATH).'course.lib.php');
include_once (api_get_path(LIBRARY_PATH).'debug.lib.inc.php');
include_once (api_get_path(LIBRARY_PATH).'fileManage.lib.php');
include_once (api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php');
include_once (api_get_path(CONFIGURATION_PATH).'course_info.conf.php');

// Displaying the header
$tool_name = get_lang('CreateSite');
Display :: display_header($tool_name);

// Displaying the tool title
api_display_tool_title($tool_name);
// Check access rights
if (!api_is_allowed_to_create_course())
{
	Display :: display_error_message(get_lang("NotAllowed"));
	Display::display_footer();
	exit;
}
// Get all course categories
$table_course_category = Database :: get_main_table(TABLE_MAIN_CATEGORY);
$table_course = Database :: get_main_table(TABLE_MAIN_COURSE);

// Build the form
$categories = array();
$form = new FormValidator('add_course');
$form->add_textfield('title',get_lang('Title'),true,array('size'=>'60'));
$form->addElement('static',null,null,get_lang('Ex'));
$categories_select = $form->addElement('select', 'category_code', get_lang('Fac'), $categories);
CourseManager::select_and_sort_categories($categories_select);
$form->addElement('static',null,null, get_lang('TargetFac'));
$form->add_textfield('wanted_code', get_lang('Code'),false,array('size'=>'20','maxlength'=>20));
$form->addRule('wanted_code',get_lang('Max'),'maxlength',20);
$titular= &$form->add_textfield('tutor_name', get_lang('Professors'),true,array('size'=>'60'));
$form->addElement('select_language', 'course_language', get_lang('Ln'));
$form->addElement('submit', null, get_lang('Ok'));
$form->add_progress_bar();

// Set default values
if(isset($_user["language"]) && $_user["language"]!="")
{
	$values['course_language'] = $_user["language"];
}
else
{
	$values['course_language'] = get_setting('platformLanguage');
}

$values['tutor_name'] = $_user['lastName']." ".$_user['firstName'];
$form->setDefaults($values);
// Validate the form
if($form->validate())
{
	$course_values = $form->exportValues();
	$wanted_code = $course_values['wanted_code'];
	$tutor_name = $course_values['tutor_name'];
	$category_code = $course_values['category_code'];
	$title = $course_values['title'];
	$course_language = $course_values['course_language'];
	
	if(trim($wanted_code) == ''){
		$wanted_code = generate_course_code(substr($title,0,20));
	}
	
	$keys = define_course_keys($wanted_code, "", $_configuration['db_prefix']);
	
	$sql_check = sprintf('SELECT * FROM '.$table_course.' WHERE visual_code = "%s"',Database :: escape_string($wanted_code));
	//$result_check = mysql_query($sql_check);
	$result_check = api_sql_query($sql_check,__FILE__,__LINE__); //I don't know why this api function doesn't work...
	if(Database::num_rows($result_check)<1)
	{
		if (sizeof($keys))
		{
			$visual_code = $keys["currentCourseCode"];
			$code = $keys["currentCourseId"];
			$db_name = $keys["currentCourseDbName"];
			$directory = $keys["currentCourseRepository"];
			$expiration_date = time() + $firstExpirationDelay;
			prepare_course_repository($directory, $code);
			update_Db_course($db_name);
			$pictures_array=fill_course_repository($directory);
			fill_Db_course($db_name, $directory, $course_language,$pictures_array);
			register_course($code, $visual_code, $directory, $db_name, $tutor_name, $category_code, $title, $course_language, api_get_user_id(), $expiration_date);
		}
		$message = get_lang('JustCreated');
		$message .= " <strong>".$visual_code."</strong>";
		$message .= "<br /><br /><br />";
		$message .= '<a class="bottom-link" href="'.api_get_path(WEB_PATH).'user_portal.php">'.get_lang('Enter').'</a>';
		Display :: display_confirmation_message($message,false);
	}
	else
	{
		Display :: display_error_message(get_lang('CourseCodeAlreadyExists'),false);
		$form->display();
		echo '<p>'.get_lang('CourseCodeAlreadyExistExplained').'</p>';
	}
		
}
else
{
	// Display the form
	$form->display();
	echo '<p>'.get_lang('Explanation').'</p>';
}
/*
==============================================================================
		FOOTER
==============================================================================
*/
Display :: display_footer();
?>
