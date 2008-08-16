<?php
/**
==============================================================================
*	This is the 'check access' script.
*
*	It detects when a user wants to enter a credit course:
*		- If the user has access, proceed.
*		- If the user do not has access, call the main 'check access' script.
*
*	Admins can always enter to any credit course.
==============================================================================
*/

// User is inside a course, it is a credit course and he/she is not an admin.
if(isset($_SESSION['_course']) AND (cs_course_payment_options_number($_SESSION['_course']['sysCode'])>0) AND !(api_is_course_admin()) ) {
	
/*
-----------------------------------------------------------
	Included libraries
-----------------------------------------------------------
*/

include_once('../../main/inc/global.inc.php');

require_once(dirname(__FILE__).'/cs_functions.inc.php');

api_block_anonymous_users();

/*
==============================================================================
		MAIN CODE
==============================================================================
*/ 

	if (!cs_can_user_access(api_get_user_id(),$_SESSION['_course']['sysCode']))
	{
		echo'<script type="text/javascript">document.location.href="'.api_get_path(WEB_PLUGIN_PATH).'credits_system/check_access.php?code='.$_SESSION['_course']['sysCode'].'";</script>';
		exit;
	}
}
?>
