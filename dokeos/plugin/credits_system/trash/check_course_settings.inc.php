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
$language_file = 'plugin_credits_system';
include_once("../../../main/inc/global.inc.php");  
require_once (api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php');

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

api_block_anonymous_users();
$course_settings_page = api_get_path(REL_CLARO_PATH).'course_info/infocours.php';
$admin_course_settings_page = api_get_path(REL_CLARO_PATH).'admin/course_edit.php';

if($_SERVER['SCRIPT_NAME'] == $course_settings_page || ( $_SERVER['SCRIPT_NAME'] == $admin_course_settings_page ) )
{
	//User want to set up the settings of a course.
	
	if (isset($_GET['course_code']))
	{
		$code = $_GET['course_code'];
	}
	else
	{
		$code = $_SESSION['_course']['sysCode'];
	}

	if(isset($_POST['payment_options']) or isset($_GET['payment_options']))
	{
		if ($_POST['payment_options'] == 'manage' or $_GET['payment_options'] == 'manage')
		{
			//Include the form to capture new payment options or to delete current payment options.
			require_once('header2_and_3.php');
			//Display::display_normal_message('Edit Payment Options');
			include_once('cs_add_course_options.inc.php');
			Display::display_footer();
			exit;
		}
		else
		{
			if ($_POST['payment_options'] == 'delete')
			{
				//Delete all payment options of a course.
				cs_delete_course_payment_option($code);
			}
			//else ($_POST['payment_options'] == 'finished') Continue to dokeos course edit options.
		}
	}
	else
	{
		if ($_SERVER['SCRIPT_NAME'] == $course_settings_page)
		{
			$course_type_yes = new FormValidator('course_type_yes');
			$course_type_no = new FormValidator('course_type_no');
			//$code = $_SESSION['_course']['sysCode'];
		}
		else
		{
			$course_type_yes = new FormValidator('course_type_yes','post',$admin_course_settings_page.'?course_code='.$_GET['course_code']);
			$course_type_no = new FormValidator('course_type_no','post',$admin_course_settings_page.'?course_code='.$_GET['course_code']);
			//$code = $_GET['course_code'];
		}
		
		
		if (cs_course_payment_options_number($code) == 0)
		{
			require_once('header2_and_3.php');			
			Display::display_normal_message('This is a free course. Want to change it to a pay course?');
			
			$course_type_yes->addElement('hidden','payment_options','manage');
			$course_type_no->addElement('hidden','payment_options','finish');
			$course_type_yes->addElement('submit','payment_options_submit_yes','Yes');
			$course_type_no->addElement('submit','payment_options_submit_no','No');
			//$course_type->addElement('button','pay_course','yes',array('onclick' => "javascript:document.location.href=$_SERVER['SELF']?pay_course=true"));
			//$course_type->addElement('button','pay_course','no',array('onclick' => "javascript:document.course_type.submit()"));
			$course_type_yes->display();
			$course_type_no->display();
			exit;
		}
		else
		{
			require_once('header2_and_3.php');
						
			Display::display_normal_message('This is a pay course. Want to change it to a free course?');
			
			$course_type_yes->addElement('hidden','payment_options','delete');
			$course_type_no->addElement('hidden','payment_options','manage');
			$course_type_yes->addElement('submit','pay_course_submit_yes','Yes');
			$course_type_no->addElement('submit','payment_options_submit_yes_no','No');
			//$course_type->addElement('button','pay_course','yes',array('onclick' => "javascript:document.course_type.submit()"));
			//$course_type->addElement('button','pay_course','no',array('onclick' => "javascript:document.course_type.submit()"));
			$course_type_yes->display();
			$course_type_no->display();
			Display::display_footer();
			exit;
		}
		require_once('header2_and_3.php');
		Display::display_footer();
		exit;
	}
}

/*
==============================================================================
		FOOTER 
==============================================================================
*/ 
?>