<?php // $Id: check_subscription.inc.php,v 1.0 2006/03/15 14:34:45 poty $
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
*	This script checks if user wants to subscribe into a credits course or
*	wants to renew his subscription to redirects him to subscription.php.
*
*	@package dokeos.plugin
==============================================================================
*/
	
/*
==============================================================================
		INIT SECTION
==============================================================================
*/ 
api_block_anonymous_users();
$language_file = 'plugin_credits_system';
include_once(api_get_path('INCLUDE_PATH')."global.inc.php");

/*
-----------------------------------------------------------
	Libraries
-----------------------------------------------------------
*/ 
require_once (api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php');

/*
==============================================================================
		MAIN CODE
==============================================================================
*/ 

if (isset($_GET['action']) && $_GET['action'] == 'subscribe' && isset($_POST['subscribe']) && !api_is_platform_admin())
//If an Admin could not access a credit course (check_access.inc.php) delete here: && !api_is_platform_admin()
{
		//Get the course payment options and credits.
		$option = cs_get_course_payment_options($_POST['subscribe']);

		$user_dont_pay = ! cs_can_user_access(api_get_user_id(),$_POST['subscribe']);
		
		if ($option && $user_dont_pay && !(isset($_GET['paycourse']) && $_GET['paycourse'] == 'paid'))
		{//This is a pay course and user dont pay any subscription at current date.
		
			//set current page with $_GET variables to continue to next page.
			foreach ($_GET as $key => $value)
			{
				$Get_variables .= $key.'='.$value.'&';
			}
			
			//check if want to pay a course which is already subscribed or want to subscribe to a course.
			$subscribe = $_SERVER['SCRIPT_NAME'] == api_get_path(REL_CLARO_PATH).'auth/courses.php';
			
			//load the credit course subscription page.
			echo'<script type="text/javascript">document.location.href="'.api_get_path(WEB_PLUGIN_PATH).'credits_system/subscription.php?paycourse='.$_POST['subscribe'].'&subscribe='.$subscribe.'&'.$Get_variables.'";</script>';
			exit;
		}
}
?>
