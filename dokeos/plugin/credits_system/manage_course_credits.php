<?php
/*
==============================================================================
	Dokeos - elearning and course management software

	Copyright (c) 2007 E.U.I. Universidad Politï¿?cnica de Madrid (Spain)
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
/**
==============================================================================
*	This is the 'manage course payment options menu' script.
*	Here, a platform admin or a course admin can change the status of a course:
*
*	Free Course to Credit Course:
*
* 		- The status of one or several Free Courses can be changed to Credit Courses.
* 		  The new Credit Courses will be created with the default payment option that
* 		  the platform admin configures.
*		
*	Credit Course to Free Course:
*
*		- The status of one or several Credit Courses can be changed to Free Courses.
*		  All the payment options of these courses will be deleted.
*	
*	@package dokeos.plugin.credits_system
==============================================================================
*/
/*
==============================================================================
		INIT SECTION
==============================================================================
*/ 

// Reset the current course to show all the possible courses.
$language_file = "plugin_credits_system";
unset($_GET['cidReq']);
$cidReset = true; 
include_once('../../main/inc/global.inc.php');
require_once(api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php');
require_once(api_get_path(LIBRARY_PATH).'course.lib.php');
require_once(dirname(__FILE__).'/inc/cs_functions.inc.php');
require_once (api_get_path(LIBRARY_PATH).'sortabletable.class.php');

api_block_anonymous_users();



/*
-----------------------------------------------------------
	Header
	include the HTTP, HTML headers plus the top banner
-----------------------------------------------------------
*/

$interbreadcrumb[] = array ("url" => 'my_credits.php', "name" => get_lang('CreditsSystem'));
$nameTools = get_lang('ManageCourseCredits');


Display :: display_header($nameTools);

/*
-----------------------------------------------------------
	Title:
	Title and 'add link' of the page
-----------------------------------------------------------
*/


//First we make sure the admin gave the teacher permission to manage course credits,
//and if there are payment options available.

$settings = cs_get_current_settings('cs_allow_teacher'); 
if (!cs_get_payment_options() ||(!api_is_platform_admin() && ($settings == 'no')))
{
	Display :: display_error_message(get_lang('ManageCourseCreditsAccessDenied'));
	Display :: display_footer();
	exit;
}
	
$default_po = cs_get_current_settings('cs_default_payment_option');
if ((isset($_GET['default_po'])|| $_GET['action']=='enabled') && !isset($_GET['my_courses_direction'])||$_POST['action']=='enable')//edit by xiaoping
{
	$payment_options = cs_get_payment_options(); 
	foreach ($payment_options as $key => $value)
	{
		$select_options[$key]=$value['amount'].' '.$value['name'];
	}
	
	$title = 'AddDefaultPaymentOption';
	if ($_GET['action'] != 'enabled')
	{
		api_display_tool_title(get_lang($title));
		Display :: display_normal_message (get_lang('SetUpDefaultPaymentOption'));
	}
	
	$form_new_options = new FormValidator('course_deafault_po','post','?action=enabled&view='.$_GET['view']);
	$form_new_options -> registerRule('valid_amount','regex','/^\d*\.{0,1}\d+$/');

	$group[] = $form_new_options->createElement('static','','',get_lang('Set'));
	$group[] = $form_new_options->createElement('select','payment_option','payment_option',$select_options);
	$group[] = $form_new_options->createElement('text','credits','number_of_credits[]',array('size'=>'8','maxlength'=>'8'));
	$group[] = $form_new_options->createElement('static','','',get_lang('CreditsPer'));	
	
	$form_new_options -> addGroup ($group,'add_option_group');
	$form_new_options -> addGroupRule('add_option_group',array(array(),array(array(get_lang('CreditAmountRequired'),'required'),array(get_lang('Creditsmustbepositive'),'valid_amount'),$nonzero_rule),array(),array(array(get_lang('SelectValidPaymentOption'),'nonzero'))));
	
	//Client side validation disabled.
	//$form_new_options -> addGroupRule('add_option_group',array(array(),array(array(get_lang('CreditAmountRequired'),'required',null,'client'),array(get_lang('CreditAmountPositive'),'valid_amount',null,'client'),$nonzero_rule),array(),array(array(get_lang('SelectValidPaymentOption'),'nonzero',null,'client'))));
	
	if (isset($_POST['courses_num']))
	{
		for ($i=1;$i<=$_POST['courses_num'];$i++)
		{
			$form_new_options -> addElement ('hidden','code'.$i,$_POST['code'.$i]);
		}
		$form_new_options -> addElement ('hidden','courses_num',$i);
		$form_new_options -> addElement ('hidden','multiple','yes');
	}
			
	$form_new_options -> addElement ('hidden','action','enabled');
	
	$form_new_options -> addElement ('hidden','code',$_GET['code']);
	
	$form_new_options -> addElement ('submit','submit',get_lang('Ok'));
}

//Show the Pay Courses as default view

if (!isset($_GET['default_po']) || isset($_GET['my_courses_direction']))
{
	if (!isset($_GET['table']) || ($_GET['table']!='pay' && $_GET['table']!='free'))
	{
		$_GET['table']='pay';	
	}
		
	$table_type = $_GET['table'];

	if ($_GET['view'] == 'admin' && (api_is_platform_admin(api_get_user_id)))
	{
			$parameters['view']='admin';
	}
	else $parameters['view']='teacher';
	
	if ($table_type == 'free')
	{
		$pay_course = false;
		$parameters['table'] = 'free';
		if (!$default_po)
		{
			$parameters['default_po']= 'true';
		}
		$select = array('enable'=> get_lang('EnableCreditCourse'));
		$title = 'FreeCourses';
	} 
	else if (($_GET['table']=='pay') && (!isset($_SESSION['_course'])))
	{
		$pay_course = true;
		$parameters['table'] = 'pay';
		$select = array('disable'=> get_lang('DisableCreditCourse'));
		$title = 'PayCourses';
	}
	
	if ($_GET['action'] != 'enabled')
	{
		api_display_tool_title(get_lang($title));
		echo '<a href="?table=pay&view='.$_GET['view'].'">'.get_lang('ShowCreditCourses').'</a> &nbsp;';
		echo ' | ';
		echo ' &nbsp; <a href="?table=free&view='.$_GET['view'].'">'.get_lang('ShowFreeCourses').'</a>';
	}
}

/*
==============================================================================
		FUNCTIONS
==============================================================================
*/ 

/**
 * Get the number of credit courses which will be displayed
 */
function get_number_of_pay_courses()
{
	$complete_name = $_SESSION['_user']['lastName'].$_SESSION['_user']['firstName'];
	$course_table = Database :: get_main_table(TABLE_MAIN_COURSE);
	$course_credits_table = Database :: get_main_table(CS_TABLE_COURSE_CREDITS);
	$course_rel_user = Database :: get_main_table(TABLE_MAIN_COURSE_USER);
	
	$sql = "SELECT COUNT(code) AS total_number_of_items FROM $course_table WHERE code IN (SELECT code FROM $course_credits_table)";
	if (!($_GET['view'] == 'admin' && api_is_platform_admin()))
	{
		$sql.= 'AND '.$course_table.'.code IN (SELECT course_code FROM '.$course_rel_user.' WHERE user_id="'.api_get_user_id().'" AND status = 1)';	
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
	$complete_name = $_SESSION['_user']['lastName'].$_SESSION['_user']['firstName'];
	$course_table = Database :: get_main_table(TABLE_MAIN_COURSE);
	$course_credits_table = Database :: get_main_table(CS_TABLE_COURSE_CREDITS);
	$course_rel_user = Database :: get_main_table(TABLE_MAIN_COURSE_USER);
	
	$sql = "SELECT COUNT(code) AS total_number_of_items FROM $course_table WHERE code NOT IN (SELECT code FROM $course_credits_table)";
	if (!($_GET['view'] == 'admin' && api_is_platform_admin()))
	{
		$sql.= 'AND '.$course_table.'.code IN (SELECT course_code FROM '.$course_rel_user.' WHERE user_id="'.api_get_user_id().'" AND status = 1)';	
	}
	$res = api_sql_query($sql, __FILE__, __LINE__);
	$obj = mysql_fetch_object($res);
	return $obj->total_number_of_items;
}

/**
 * Get free courses data to display
 */
function get_free_course_data($from, $number_of_items, $column, $direction)
{
	$complete_name = $_SESSION['_user']['lastName'].$_SESSION['_user']['firstName'];
	$course_table = Database :: get_main_table(TABLE_MAIN_COURSE);
	$course_rel_user = Database :: get_main_table(TABLE_MAIN_COURSE_USER);
	$course_credits_table = Database :: get_main_table(CS_TABLE_COURSE_CREDITS);
	$main_category_table = Database :: get_main_table(TABLE_MAIN_CATEGORY);
	$sql = "SELECT t1.code AS col0, visual_code AS col1, title AS col2, course_language AS col3, t2.name AS col4, subscribe AS col5, unsubscribe AS col6, t1.code AS col7 FROM $course_table t1 inner join $main_category_table t2 on t1.category_code=t2.code WHERE t1.code NOT IN (SELECT code FROM $course_credits_table)";
	
	if (!($_GET['view'] == 'admin' && api_is_platform_admin()))
	{
		$sql.= 'AND '.$course_table.'.t1.code IN (SELECT course_code FROM '.$course_rel_user.' WHERE user_id="'.api_get_user_id().'" AND status = 1)';	
	}
	$sql .= " ORDER BY col$column $direction ";
	$sql .= " LIMIT $from,$number_of_items";
	$res = api_sql_query($sql, __FILE__, __LINE__);
	$courses = array ();
	while ($course = mysql_fetch_row($res))
	{
		$course[3] = $course[3] == 'simpl_chinese'?get_lang('simpl_chinese') : $course[3];//by xiaoping
		$course[5] = $course[5] == SUBSCRIBE_ALLOWED ? get_lang('yes') : get_lang('no');
		$course[6] = $course[6] == UNSUBSCRIBE_ALLOWED ? get_lang('yes') : get_lang('no');
		$courses[] = $course;
	}
	return $courses;
}

/**
 * Get creedit courses data to display
 */
function get_pay_course_data($from, $number_of_items, $column, $direction)
{
	$complete_name = $_SESSION['_user']['lastName'].$_SESSION['_user']['firstName'];
	$course_table = Database :: get_main_table(TABLE_MAIN_COURSE);
	$course_rel_user = Database :: get_main_table(TABLE_MAIN_COURSE_USER);
	$course_credits_table = Database :: get_main_table(CS_TABLE_COURSE_CREDITS);
	$main_category_table = Database :: get_main_table(TABLE_MAIN_CATEGORY);
	$sql = "SELECT t1.code AS col0, visual_code AS col1, title AS col2, course_language AS col3, t2.name AS col4, subscribe AS col5, unsubscribe AS col6, t1.code AS col7 FROM $course_table t1 inner join $main_category_table t2 on t1.category_code=t2.code WHERE t1.code IN (SELECT code FROM $course_credits_table)";
	if (!($_GET['view'] == 'admin' && api_is_platform_admin()))
	{
		$sql.= 'AND '.$course_table.'.t1.code IN (SELECT course_code FROM '.$course_rel_user.' WHERE user_id="'.api_get_user_id().'" AND status = 1)';	
	}
	$sql .= " ORDER BY col$column $direction ";
	$sql .= " LIMIT $from,$number_of_items";
	$res = api_sql_query($sql, __FILE__, __LINE__);
	$courses = array ();
	while ($course = mysql_fetch_row($res))
	{
		$course[3] = $course[3] == 'simpl_chinese'?get_lang('simpl_chinese') : $course[3];//by xiaoping
		$course[5] = $course[5] == SUBSCRIBE_ALLOWED ? get_lang('yes') : get_lang('no');
		$course[6] = $course[6] == UNSUBSCRIBE_ALLOWED ? get_lang('yes') : get_lang('no');
		$courses[] = $course;
	}
	return $courses;
}

/**
 * Options links. Different links depending on the kind of courses shown(free or credit courses).
 */
function modify_filter($code)
{
	
	if ($_GET['table']=='pay')
	{

		$links = '<a href="edit_pay_course.php?action=current&view='.$_GET['view'].'&code='.$code.'"><img src="../../main/img/synthese_view.gif" border="0" style="vertical-align: middle" title="'.get_lang('CurrentPaymentOptions').'" alt="'.get_lang('CurrentPaymentOptions').'"/></a>&nbsp;'.
				'<a href="?table=pay&view='.$_GET['view'].'&action=disable&code='.$code.'" onclick="javascript:if(!confirm('."'".addslashes(htmlspecialchars(get_lang("ConfirmYourChoice")))."'".')) return false; "><img src="img/no_coins.gif" border="0" style="vertical-align: middle" title="'.get_lang('DisablePaymentOption').'" alt="'.get_lang('DisablePaymentOption').'"/></a>&nbsp;';
	}
	else 
	{
		$default_PO = cs_get_current_settings('cs_default_payment_option');
		if (!$default_PO)
		{
			$link_default_po = 'default_po=true';
		}
		else $link_default_po='';
		
		$links = '<a href="?table=free&view='.$_GET['view'].'&action=enable&code='.$code.'&'.$link_default_po.'"><img src="img/coins.gif" border="0" style="vertical-align: middle" title="'.get_lang('EnablePaymentOption').'" alt="'.get_lang('EnablePaymentOption').'"/></a>&nbsp;';
	}
	$course_info = CourseManager::get_course_information($code);
	$links.= '<a href="'.api_get_path(WEB_COURSE_PATH).$course_info['directory'].'"><img src="../../main/img/course_home.gif" border="0" style="vertical-align: middle" title="'.get_lang('CourseHomepage').'" alt="'.get_lang('CourseHomepage').'"/></a>&nbsp;';
	return ($links);
}

/*	
==============================================================================
		MAIN CODE
==============================================================================
*/

$error_default_po = false;
global $charset;	
//Edit several courses.

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
				Display :: display_normal_message (get_lang('YouHaveDone').count($course_codes).get_lang('LetCoursesFree'));//edit by xiaoping
			}
			break;
			
		// Enable payment options for the courses selected.
		case 'enable' :
		
			if (!$default_po)
			{
				
				if (count($course_codes) > 0)
				{
					$i=0;
					foreach ($course_codes as $index => $course_code)
					{
						$i++;
						$form_new_options -> addElement ('hidden','code'.$i,$course_code);
					}
					$form_new_options -> addElement ('hidden','courses_num',$i);
				}
				
				$form_new_options -> addElement ('hidden','multiple','yes');
				$form_new_options -> display();
			}
			else
			{
				if (count($course_codes) > 0)
				{
					foreach ($course_codes as $index => $course_code)
					{
						$error = !cs_enable_payment_options($course_code);
					}
					if(!$error)
					{
						Display :: display_normal_message (get_lang('EnabledCreditCourses'),false);	
					}
					else Display :: display_error_message ('EnableCreditCourseError');
				}
			}
			break;
	}
}

//Edit only one course.
if (isset ($_GET['action']))
{
	switch ($_GET['action'])
	{
		// Make course selected, free.
		case 'disable' :
			if (isset ($_GET['code']))
			{
				$error = !cs_delete_course_payment_option($_GET['code']);
				if (!$error)
				{
					Display :: display_normal_message(get_lang('DisabledCreditCourse'));	
				}
				else Display :: display_error_message(get_lang('DisableCreditCourseError'));
			}
			break;
			
		// Enable payment options for the course selected.
		case 'enable' :
			if ($default_po)
			{
				if (isset ($_GET['code']))
				{
					$error = !cs_enable_payment_options($_GET['code']);
					if(!$error)
					{
						Display :: display_normal_message (get_lang('EnabledCreditCourse'));	
					}
					else Display :: display_error_message ('EnableCreditCourseError');
				}
			}
			else 
			{
				$form_new_options -> display();
			}
			
			break;
			
		case 'enabled' :
		
			if ($form_new_options -> validate())
			{
				api_display_tool_title(get_lang($title));
				echo '<a href="?table=pay&view='.$_GET['view'].'">'.get_lang('ShowCreditCourses').'</a> &nbsp;';
				echo ' | ';
				echo ' &nbsp; <a href="?table=free&view='.$_GET['view'].'">'.get_lang('ShowFreeCourses').'</a>';
				
				$option = $form_new_options->exportValues();
				if (!isset($_POST['multiple']))
				{
					$error = !cs_set_course_payment_option($_POST['code'],$option['add_option_group']['credits'],$option['add_option_group']['payment_option']);
				}	
				else
				{
					for ($i=1;$i<=$_POST['courses_num'];$i++)
					{
						$option['add_option_group']['payment_option'];
						$error_default_po = !cs_set_course_payment_option($_POST['code'.$i],$option['add_option_group']['credits'],$option['add_option_group']['payment_option']);
					}
				}
				
			}
			else
			{
				$title = 'AddDefaultPaymentOption';
				api_display_tool_title(get_lang($title));
				$error_default_po = true;
				$form_new_options -> display();
			}			
	}
}
	
//Show the list of the selected kind of courses.
if (!$error_default_po  && ($default_po || ($_GET['action']!='enable' && $_POST['action']!='enable')))
{
	$table = new SortableTable('my_courses','get_number_of_'.$table_type.'_courses', 'get_'.$table_type.'_course_data',4,10);
	$table->set_additional_parameters($parameters);
	$table->set_header(0, '', false);
	$table->set_header(1, get_lang('Code'));
	$table->set_header(2, get_lang('Title'));
	$table->set_header(3, get_lang('Language'));
	$table->set_header(4, get_lang('Category'));
	$table->set_header(5, get_lang('SubscriptionAllowed'));
	$table->set_header(6, get_lang('UnsubscriptionAllowed'));
	$table->set_header(7, '', false);
	$table->set_column_filter(7,'modify_filter');
	$table->set_form_actions($select,'selected_courses');
	$table->display();
}	
/*
==============================================================================
		FOOTER
==============================================================================
*/
Display :: display_footer();

?>