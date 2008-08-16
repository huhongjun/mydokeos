<?php // $Id: template.php,v 1.2 2006/03/15 14:34:45 pcool Exp $
/*
==============================================================================
	Dokeos - elearning and course management software
	
	Copyright (c) 2004-2006 Dokeos S.A.
	Copyright (c) E.U.I. Universidad Politécnica de Madrid (Spain)
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
*	This is the 'check access' main script.
*
*	The user has no access to enter the credit course. Two possibilities:
*
*		- User´s last paid subscription expired. 
*			* Show Course-renew-subscription-page link
*
*		- User tried to access a credit course he never paid for
*			* Show Course-subscription-page link.
*
==============================================================================
*/
	
/*
==============================================================================
		INIT SECTION
==============================================================================
*/ 

$cidReset = true;

$language_file = "plugin_credits_system";

include("../../main/inc/global.inc.php"); 
require_once(dirname(__FILE__).'/inc/cs_functions.inc.php');
require_once(api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php');
require_once (api_get_path(LIBRARY_PATH)."/course.lib.php");
	
 
$course_info = CourseManager :: get_course_information($_GET['code']);

$tool_name = $course_info['title'];
Display::display_header($tool_name);

/*	
==============================================================================
		MAIN CODE
==============================================================================
*/ 


$tool_name = "Access Denied";
api_display_tool_title($tool_name);


			
$dates = cs_user_last_sub_paid (api_get_user_id(),$_GET['code']);

//User tried to access a credit course wuthout subscription.
if (!CourseManager :: is_user_subscribed_in_course(api_get_user_id(),$_GET['code']))
{
	$message = get_lang('YouHaveNoAccess').'<br /> <a href="javascript:document.subscribe.submit()">'.get_lang('PlzSubscribe').'</a>';
	$go_to = api_get_path(REL_CLARO_PATH).'auth/courses.php?action=subscribe';
}

else 
{
	$go_to = api_get_path(WEB_PATH).'user_portal.php?action=subscribe';
	
//User never spent credits to access this course.
	if ($dates == false)
	{
		$message = get_lang('AccessNotAllowed').'<br /> <a href="javascript:document.subscribe.submit()">'.get_lang('RenewSubscription').'</a>';		
	}
//User last paid subscription expired.
	else 
	{
		$message = get_lang('AccessExpiredOn').' '.$dates['end_date'].'.<br /> <a href="javascript:document.subscribe.submit()">'.get_lang('RenewSubscription').'</a>';
	}	
}

$form = new FormValidator('subscribe','post',$go_to);

$form -> addElement ('hidden','subscribe',$_GET['code']);
		
$form -> display();
		
Display::display_normal_message($message,false);
				

/*
==============================================================================
		FOOTER 
==============================================================================
*/ 
Display::display_footer();
?>