<?php // $Id: user_list.php 15137 2008-04-26 01:34:17Z yannoo $
/*
==============================================================================
	Dokeos - elearning and course management software

	Copyright (c) 2004-2008 Dokeos SPRL
	Copyright (c) 2003 Ghent University (UGent)
	Copyright (c) 2001 Universite catholique de Louvain (UCL)
	Copyright (c) Olivier Brouckaert

	For a full list of contributors, see "credits.txt".
	The full license can be read in "license.txt".

	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	See the GNU General Public License for more details.

	Contact: Dokeos, rue du Corbeau, 108, B-1030 Brussels, Belgium, info@dokeos.com
==============================================================================
*/
/**
==============================================================================
	@author Bart Mollet
*	@package dokeos.admin
==============================================================================
*/

// name of the language file that needs to be included
$language_file = array ('registration','admin');
$cidReset = true;
require ('../inc/global.inc.php');
require_once (api_get_path(LIBRARY_PATH).'sortabletable.class.php');
require_once (api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php');
require_once (api_get_path(LIBRARY_PATH).'security.lib.php');
require_once(api_get_path(LIBRARY_PATH).'xajax/xajax.inc.php');
require_once (api_get_path(LIBRARY_PATH).'usermanager.lib.php');

// xajax
$xajax = new xajax();
$xajax->registerFunction('courses_of_user');
//$xajax->registerFunction('empty_courses_of_user');
$xajax->processRequests();
function courses_of_user($arg)
{
	// do some stuff based on $arg like query data from a database and
	// put it into a variable like $newContent
    //$newContent = 'werkt het? en met een beetje meer text, wordt dat goed opgelost? ';    
    $personal_course_list = UserManager::get_personal_session_course_list($arg);
    $newContent = '';
    if(count($personal_course_list)>0)
    {
	    foreach ($personal_course_list as $key=>$course)
	    {
	    	$newContent .= $course['i'].'<br />';
	    }
    }
    else
    {
    	$newContent .= '- '.get_lang('None').' -<br />';
    }    
	// Instantiate the xajaxResponse object
	$objResponse = new xajaxResponse();
        
	// add a command to the response to assign the innerHTML attribute of
	// the element with id="SomeElementId" to whatever the new content is
	$objResponse->addAssign("user".$arg,"innerHTML", $newContent);
	$objResponse->addReplace("coursesofuser".$arg,"alt", $newContent);
	$objResponse->addReplace("coursesofuser".$arg,"title", $newContent);
        
	//return the  xajaxResponse object
	return $objResponse;
}
function empty_courses_of_user($arg)
{
	// do some stuff based on $arg like query data from a database and
	// put it into a variable like $newContent
    $newContent = '';    
	// Instantiate the xajaxResponse object
	$objResponse = new xajaxResponse();
        
	// add a command to the response to assign the innerHTML attribute of
	// the element with id="SomeElementId" to whatever the new content is
	$objResponse->addAssign("user".$arg,"innerHTML", $newContent);
	
        
	//return the  xajaxResponse object
	return $objResponse;
}


$htmlHeadXtra[] = $xajax->getJavascript('../inc/lib/xajax/');
$htmlHeadXtra[] = '		<style>
		.tooltipLinkInner {
			color:blue;
			text-decoration:none;												
		}
		
		#tooltip .toolbox a:hover span {
		  display: block! important;
		  color: black;
		  position: absolute;
		}
		
		.tooltipInner {
			margin:7px;
			font-size:8pt;
			float:right;
		}
		
		</style>';

$this_section = SECTION_PLATFORM_ADMIN;

api_protect_admin_script();
$get_keyword=trim($_GET['keyword']);
$get_keyword_firstname=trim($_GET['keyword_firstname']);
$get_keyword_lastname=trim($_GET['keyword_lastname']);
$get_keyword_email=trim($_GET['keyword_email']);
$get_keyword_username=trim($_GET['keyword_username']);
$get_keyword_status=trim($_GET['keyword_status']);
$get_keyword_officialcode=trim($_GET['keyword_officialcode']);
/**
*	Make sure this function is protected
*	because it does NOT check password!
*
*	This function defines globals.
*	@author Roan Embrechts
*/
function login_user($user_id)
{	
	//init ---------------------------------------------------------------------
	global $uidReset, $loginFailed, $_configuration;

	$main_user_table = Database :: get_main_table(TABLE_MAIN_USER);
	$main_admin_table = Database :: get_main_table(TABLE_MAIN_ADMIN);
	$track_e_login_table = Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_LOGIN);

	//logic --------------------------------------------------------------------
	//unset($_user['user_id']); // uid not in session ? prevent any hacking
	if (!isset ($user_id))
	{
		$uidReset = true;
		return;
	}

	$sql_query = "SELECT * FROM $main_user_table WHERE user_id='$user_id'";
	$sql_result = api_sql_query($sql_query, __FILE__, __LINE__);
	$result = Database :: fetch_array($sql_result);

	$firstname = $result["firstname"];
	$lastname = $result["lastname"];
	$user_id = $result["user_id"];

	//$message = "Attempting to login as ".$firstname." ".$lastname." (id ".$user_id.")";
	$message = sprintf(get_lang('AttemptingToLoginAs'),$firstname,$lastname,$user_id);

	$loginFailed = false;
	$uidReset = false;

	if ($user_id) // a uid is given (log in succeeded)
	{
		if ($_configuration['tracking_enabled'])
		{
			$sql_query = "SELECT user.*, a.user_id is_admin,
				UNIX_TIMESTAMP(login.login_date) login_date
				FROM $main_user_table
				LEFT JOIN $main_admin_table a
				ON user.user_id = a.user_id
				LEFT JOIN $track_e_login_table login
				ON user.user_id = login.login_user_id
				WHERE user.user_id = '".$user_id."'
				ORDER BY login.login_date DESC LIMIT 1";
		}
		else
		{
			$sql_query = "SELECT user.*, a.user_id is_admin
				FROM $main_user_table
				LEFT JOIN $main_admin_table a
				ON user.user_id = a.user_id
				WHERE user.user_id = '".$user_id."'";
		}

		$sql_result = api_sql_query($sql_query, __FILE__, __LINE__);

		if (Database::num_rows($sql_result) > 0)
		{
			// Extracting the user data

			$user_data = Database::fetch_array($sql_result);

            //Delog the current user
			 
			LoginDelete($_SESSION["_user"]["user_id"], $_configuration['statistics_database']);

			// Cleaning session variables
			unset($_SESSION['_user']);
			unset($_SESSION['is_platformAdmin']);
			unset($_SESSION['is_allowedCreateCourse']);
			unset($_SESSION['_uid']);


			$_user['firstName'] 	= $user_data['firstname'];
			$_user['lastName'] 		= $user_data['lastname'];
			$_user['mail'] 			= $user_data['email'];
			$_user['lastLogin'] 	= $user_data['login_date'];
			$_user['official_code'] = $user_data['official_code'];
			$_user['picture_uri'] 	= $user_data['picture_uri'];
			$_user['user_id']		= $user_data['user_id'];

			$is_platformAdmin = (bool) (!is_null($user_data['is_admin']));
			$is_allowedCreateCourse = (bool) ($user_data['status'] == 1);

			// Filling session variables with new data
			$_SESSION['_uid'] = $user_id;
			$_SESSION['_user'] = $_user;
			$_SESSION['is_platformAdmin'] = $is_platformAdmin;
			$_SESSION['is_allowedCreateCourse'] = $is_allowedCreateCourse;
			$_SESSION['login_as'] = true; // will be usefull later to know if the user is actually an admin or not (example reporting)s

			$target_url = api_get_path(WEB_PATH)."user_portal.php";
			//$message .= "<br/>Login successful. Go to <a href=\"$target_url\">$target_url</a>";
			$message .= '<br />'.sprintf(get_lang('LoginSuccessfulGoToX'),'<a href="'.$target_url.'">'.$target_url.'</a>');
			Display :: display_header(get_lang('UserList'));
			Display :: display_normal_message($message,false);
			Display :: display_footer();
			exit;
		}
		else
		{
			exit ("<br/>WARNING UNDEFINED UID !! ");
		}
	}
}
/**
 * Get the total number of users on the platform
 * @see SortableTable#get_total_number_of_items()
 */
function get_number_of_users()
{
	global $get_keyword,
	       $get_keyword_firstname,
	       $get_keyword_lastname,
	       $get_keyword_email,
	       $get_keyword_username,
	       $get_keyword_status,
	       $get_keyword_officialcode;
	$user_table = Database :: get_main_table(TABLE_MAIN_USER);
	$sql = "SELECT COUNT(u.user_id) AS total_number_of_items FROM $user_table u";
	if (isset($get_keyword))
	{
		$keyword = Database::escape_string($get_keyword);
		$sql .= " WHERE u.firstname LIKE '%".$keyword."%' OR u.lastname LIKE '%".$keyword."%'  OR u.email LIKE '%".$keyword."%'  OR u.official_code LIKE '%".$keyword."%'";
	}
	elseif (isset($get_keyword_firstname)||isset($get_keyword_lastname)||isset($get_keyword_email)||isset($get_keyword_officialcode)||isset($get_keyword_status)||isset($_GET['keyword_active'])||isset($_GET['keyword_inactive']))
	{
		$admin_table = Database :: get_main_table(TABLE_MAIN_ADMIN);
		$keyword_firstname = Database::escape_string($get_keyword_firstname);
		$keyword_lastname = Database::escape_string($get_keyword_lastname);
		$keyword_email = Database::escape_string($get_keyword_email);
		$keyword_username = Database::escape_string($get_keyword_username);
		$keyword_status = Database::escape_string($get_keyword_status);
		$keyword_officialcode = Database::escape_string($get_keyword_officialcode);
		$query_admin_table = '';
		$keyword_admin = '';
		if($keyword_status == 10)
		{
			$keyword_status = '%';
			$query_admin_table = " , $admin_table a ";
			$keyword_admin = ' AND a.user_id = u.user_id ';
		}
		$keyword_active = isset($_GET['keyword_active']);
		$keyword_inactive = isset($_GET['keyword_inactive']);
		$sql .= $query_admin_table .
				" WHERE u.firstname LIKE '%".$keyword_firstname."%' " .
				"AND u.lastname LIKE '%".$keyword_lastname."%' " .
				"AND u.username LIKE '%".$keyword_username."%'  " .
				"AND u.email LIKE '%".$keyword_email."%'   " .
				"AND u.official_code LIKE '%".$keyword_officialcode."%'    " .
				"AND u.status LIKE '".$keyword_status."'" .
				$keyword_admin;
		if($keyword_active && !$keyword_inactive)
		{
			$sql .= " AND u.active='1'";
		}
		elseif($keyword_inactive && !$keyword_active)
		{
			$sql .= " AND u.active='0'";
		}
	}
	$res = api_sql_query($sql, __FILE__, __LINE__);
	$obj = Database::fetch_object($res);
	return $obj->total_number_of_items;
}
/**
 * Get the users to display on the current page.
 * @see SortableTable#get_table_data($from)
 */
function get_user_data($from, $number_of_items, $column, $direction)
{
	global $get_keyword,
		   $get_keyword_firstname,
		   $get_keyword_lastname,
	       $get_keyword_email,
	       $get_keyword_username,
	       $get_keyword_status,
	       $get_keyword_officialcode;
	$user_table = Database :: get_main_table(TABLE_MAIN_USER);
	$sql = "SELECT
                 u.user_id			AS col0,
                 u.official_code		AS col1,
				 u.lastname 			AS col2,
                 u.firstname 			AS col3,
                 u.username			AS col4,
                 u.email				AS col5,
                 u.status				AS col6,
                 u.active				AS col7,
                 u.user_id			AS col8

             FROM
                 $user_table u";
	
	if (!empty($get_keyword_firstname)||!empty($get_keyword_lastname)||!empty($get_keyword_email)||!empty($get_keyword_officialcode)||!empty($get_keyword_status)||!empty($_GET['keyword_active'])||!empty($_GET['keyword_inactive']))
	{
		$admin_table = Database :: get_main_table(TABLE_MAIN_ADMIN);
		$keyword_firstname = Database::escape_string($get_keyword_firstname);
		$keyword_lastname = Database::escape_string($get_keyword_lastname);
		$keyword_email = Database::escape_string($get_keyword_email);
		$keyword_username = Database::escape_string($get_keyword_username);
		$keyword_status = Database::escape_string($get_keyword_status);
		$keyword_officialcode = Database::escape_string($get_keyword_officialcode);
		$query_admin_table = '';
		$keyword_admin = '';
		if($keyword_status == 10)
		{
			$keyword_status = '%';
			$query_admin_table = " , $admin_table a ";
			$keyword_admin = ' AND a.user_id = u.user_id ';
		}
		$keyword_active = isset($_GET['keyword_active']);
		$keyword_inactive = isset($_GET['keyword_inactive']);
		$sql .= $query_admin_table;	
		$where_conditioin = (($keyword_firstname)?"AND	u.firstname LIKE '%".$keyword_firstname."%' ":"").
							(($keyword_lastname)?"AND u.lastname LIKE '%".$keyword_lastname."%' ":"").
							(($keyword_username)?"AND u.username LIKE '%".$keyword_username."%'  ": "").
							(($keyword_email)?"AND u.email LIKE '%".$keyword_email."%'   ":""). 
							(($keyword_officialcode)?"AND u.official_code LIKE '%".$keyword_officialcode."%' ":"").
							(($keyword_status!="%")?("AND u.status LIKE '".$keyword_status."'" ):" ");							
		$sql.=" WHERE 1 ".$where_conditioin;	
		if($keyword_active && !$keyword_inactive)
		{
			$sql .= " AND u.active='1'";
		}
		elseif($keyword_inactive && !$keyword_active)
		{
			$sql .= " AND u.active='0'";
		}
	}
	else
	{		
		$keyword = Database::escape_string($get_keyword);
		$sql .= " WHERE u.firstname LIKE '%".$keyword."%' OR u.lastname LIKE '%".$keyword."%'  OR u.username LIKE '%".$keyword."%'  OR u.official_code LIKE '%".$keyword."%'";
	}
	
	$sql .= " ORDER BY col$column $direction ";
	$sql .= " LIMIT $from,$number_of_items";
	//echo $get_keyword.'<br/>'.$sql;
	$res = api_sql_query($sql, __FILE__, __LINE__);
	$users = array ();
	while ($user = Database::fetch_row($res))
	{
		$users[] = $user;
	}
	
	return $users;
}
/**
* Returns a mailto-link
* @param string $email An email-address
* @return string HTML-code with a mailto-link
*/
function email_filter($email)
{
	return Display :: encrypted_mailto_link($email, $email);
}
/**
 * Build the modify-column of the table
 * @param int $user_id The user id
 * @param string $url_params
 * @return string Some HTML-code with modify-buttons
 */
function modify_filter($user_id,$url_params,$row)
{
	global $charset;

//zml edit	
//	$result .= '<span id="tooltip">
//				<span class="toolbox">
//				<a style="position: relative;" class="tooltipLinkInner" href="#">
//				<img src="../img/courses.gif" id="coursesofuser'.$user_id.'" onmouseover="xajax_courses_of_user('.$user_id.');" style="vertical-align:middle;"/>
//				<span id="user'.$user_id.'" style="margin-left: -100px; border:1px solid black; width: 200px; background-color:white; z-index:99; padding: 3px; display: none; margin-right:inherit;">
//				<div style="text-align:center;"><img src="../img/anim-loader.gif" height="20" /></div>
//				</span></a></span></span>';	
	$result .= '<a href="user_information.php?user_id='.$user_id.'"><img src="../img/synthese_view.gif" border="0" style="vertical-align: middle;" title="'.get_lang('Info').'" alt="'.get_lang('Info').'"/></a>&nbsp;';
	$result .= '<a href="user_list.php?action=login_as&amp;user_id='.$user_id.'&amp;sec_token='.$_SESSION['sec_token'].'"><img src="../img/login_as.gif" border="0" style="vertical-align: middle;" alt="'.get_lang('LoginAs').'" title="'.get_lang('LoginAs').'"/></a>&nbsp;';

	$statusname = api_get_status_langvars();
	if ($row['6'] != $statusname[STUDENT])
	{
		$result .= '<img src="../img/statistics_na.gif" border="0" style="vertical-align: middle;" title="'.get_lang('Reporting').'" alt="'.get_lang('Reporting').'"/>&nbsp;';
	}
	else
	{
		$result .= '<a href="../mySpace/myStudents.php?student='.$user_id.'"><img src="../img/statistics.gif" border="0" style="vertical-align: middle;" title="'.get_lang('Reporting').'" alt="'.get_lang('Reporting').'"/></a>&nbsp;';
	}

	$result .= '<a href="user_edit.php?user_id='.$user_id.'"><img src="../img/edit.gif" border="0" style="vertical-align: middle;" title="'.get_lang('Edit').'" alt="'.get_lang('Edit').'"/></a>&nbsp;';
	$result .= '<a href="user_list.php?action=delete_user&amp;user_id='.$user_id.'&amp;'.$url_params.'&amp;sec_token='.$_SESSION['sec_token'].'"  onclick="javascript:if(!confirm('."'".addslashes(htmlspecialchars(get_lang("ConfirmYourChoice"),ENT_QUOTES,$charset))."'".')) return false;"><img src="../img/delete.gif" border="0" style="vertical-align: middle;" title="'.get_lang('Delete').'" alt="'.get_lang('Delete').'"/></a>';
	return $result;
}


/**
 * Build the active-column of the table to lock or unlock a certain user
 * lock = the user can no longer use this account
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University
 * @param int $active the current state of the account
 * @param int $user_id The user id
 * @param string $url_params
 * @return string Some HTML-code with the lock/unlock button
 */
function active_filter($active, $url_params, $row)
{
	global $_user;

	if ($active=='1')
	{
		$action='lock';
		$image='right';
	}
	if ($active=='0')
	{
		$action='unlock';
		$image='wrong';
	}

	if ($row['0']<>$_user['user_id']) // you cannot lock yourself out otherwise you could disable all the accounts including your own => everybody is locked out and nobody can change it anymore.
	{
		$result = '<a href="user_list.php?action='.$action.'&amp;user_id='.$row['0'].'&amp;'.$url_params.'&amp;sec_token='.$_SESSION['sec_token'].'"><img src="../img/'.$image.'.gif" border="0" style="vertical-align: middle;" alt="'.get_lang(ucfirst($action)).'" title="'.get_lang(ucfirst($action)).'"/></a>';
	}
	return $result;
}

/**
 * lock or unlock a user
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University
 * @param int $status, do we want to lock the user ($status=lock) or unlock it ($status=unlock)
 * @param int $user_id The user id
 * @return language variable
 */
function lock_unlock_user($status,$user_id)
{
	$user_table = Database :: get_main_table(TABLE_MAIN_USER);
	if ($status=='lock')
	{
		$status_db='0';
		$return_message=get_lang('UserLocked');
	}
	if ($status=='unlock')
	{
		$status_db='1';
		$return_message=get_lang('UserUnlocked');
	}

	if(($status_db=='1' OR $status_db=='0') AND is_numeric($user_id))
	{
		$sql="UPDATE $user_table SET active='".Database::escape_string($status_db)."' WHERE user_id='".Database::escape_string($user_id)."'";
		$result = api_sql_query($sql, __FILE__, __LINE__);
	}

	if ($result)
	{
		return $return_message;
	}
}

/**
 * instead of displaying the integer of the status, we give a translation for the status
 *
 * @param integer $status
 * @return string translation
 * 
 * @version march 2008
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University, Belgium
 */
function status_filter($status)
{
	$statusname = api_get_status_langvars();
	return $statusname[$status];
}


/**
==============================================================================
		INIT SECTION
==============================================================================
*/
$action = $_GET["action"];
$login_as_user_id = $_GET["user_id"];

// Login as ...
if ($_GET['action'] == "login_as" && isset ($login_as_user_id))
{
	login_user($login_as_user_id);
}

if (isset ($_GET['search']) && $_GET['search'] == 'advanced')
{
	$interbreadcrumb[] = array ("url" => 'index.php', "name" => get_lang('PlatformAdmin'));
	$interbreadcrumb[] = array ("url" => 'user_list.php', "name" => get_lang('UserList'));
	$tool_name = get_lang('SearchAUser');
	Display :: display_header($tool_name);
	//api_display_tool_title($tool_name);
	$form = new FormValidator('advanced_search','get');
	$form->add_textfield('keyword_lastname',get_lang('LastName'),false);
	$form->add_textfield('keyword_firstname',get_lang('FirstName'),false);
	
	$form->add_textfield('keyword_username',get_lang('LoginName'),false);
	$form->add_textfield('keyword_email',get_lang('Email'),false);
	$form->add_textfield('keyword_officialcode',get_lang('OfficialCode'),false);
	$status_options = array();
	$status_options['%'] = get_lang('All');
	$status_options[STUDENT] = get_lang('Student');
	$status_options[COURSEMANAGER] = get_lang('Teacher');
	$status_options[10] = get_lang('Administrator');
	$form->addElement('select','keyword_status',get_lang('Status'),$status_options);
	$active_group = array();
	$active_group[] = $form->createElement('checkbox','keyword_active','',get_lang('Active'));
	$active_group[] = $form->createElement('checkbox','keyword_inactive','',get_lang('Inactive'));
	$form->addGroup($active_group,'',get_lang('ActiveAccount'),'<br/>',false);
	$form->addElement('submit','submit',get_lang('Ok'));
	$defaults['keyword_active'] = 1;
	$defaults['keyword_inactive'] = 1;
	$form->setDefaults($defaults);
	$form->display();
}
else
{
	$interbreadcrumb[] = array ("url" => 'index.php', "name" => get_lang('PlatformAdmin'));
	$tool_name = get_lang('UserList');
	Display :: display_header($tool_name, "");
	//api_display_tool_title($tool_name);
	if (isset ($_GET['action']))
	{
		$check = Security::check_token('get');
		if($check)
		{
			switch ($_GET['action'])
			{
				case 'show_message' :
					Display :: display_normal_message(stripslashes($_GET['message']));
					break;
				case 'delete_user' :
					if ($user_id != $_user['user_id'] && UserManager :: delete_user($_GET['user_id']))
					{
						Display :: display_normal_message(get_lang('UserDeleted'));
					}
					else
					{
						Display :: display_error_message(get_lang('CannotDeleteUser'));
					}
					break;
				case 'lock' :
					$message=lock_unlock_user('lock',$_GET['user_id']);
					Display :: display_normal_message($message);
					break;
				case 'unlock';
					$message=lock_unlock_user('unlock',$_GET['user_id']);
					Display :: display_normal_message($message);
					break;
	
			}
			Security::clear_token();
		}
	}
	if (isset ($_POST['action']))
	{
		$check = Security::check_token('get');
		if($check)
		{
			switch ($_POST['action'])
			{
				case 'delete' :
					$number_of_selected_users = count($_POST['id']);
					$number_of_deleted_users = 0;
					foreach ($_POST['id'] as $index => $user_id)
					{
						if($user_id != $_user['user_id'])
						{
							if(UserManager :: delete_user($user_id))
							{
								$number_of_deleted_users++;
							}
						}
					}
					if($number_of_selected_users == $number_of_deleted_users)
					{
						Display :: display_normal_message(get_lang('SelectedUsersDeleted'));
					}
					else
					{
						Display :: display_error_message(get_lang('SomeUsersNotDeleted'));
					}
					break;
			}
			Security::clear_token();
		}
	}
	// Create a search-box
	$form = new FormValidator('search_simple','get','','',null,false);
	$renderer =& $form->defaultRenderer();
	$renderer->setElementTemplate('<span>{element}</span> ');
	$form->addElement('text','keyword',get_lang('keyword'));
	$form->addElement('submit','submit',get_lang('Search'));
	$form->addElement('static','search_advanced_link',null,'<a href="user_list.php?search=advanced">'.get_lang('AdvancedSearch').'</a>');
	$form->display();
	if (isset($get_keyword))
	{
		$parameters = array ('keyword' => $get_keyword);
	}
	elseif(isset($get_keyword_firstname)||isset($get_keyword_lastname)||isset($get_keyword_email)||isset($get_keyword_officialcode)||isset($get_keyword_status)||isset($_GET['keyword_active'])||isset($_GET['keyword_inactive']))
	{
		$parameters['keyword_firstname'] = $get_keyword_firstname;
		$parameters['keyword_lastname'] = $get_keyword_lastname;
		$parameters['keyword_email'] = $get_keyword_email;
		$parameters['keyword_officialcode'] = $get_keyword_officialcode;
		$parameters['keyword_status'] = $get_keyword_status;
		$parameters['keyword_active'] = trim($_GET['keyword_active']);
		$parameters['keyword_inactive'] = trim($_GET['keyword_inactive']);
	}
	// Create a sortable table with user-data
	$parameters['sec_token'] = Security::get_token();
	$table = new SortableTable('users', 'get_number_of_users', 'get_user_data',2);
	$table->set_additional_parameters($parameters);
	$table->set_header(0, '', false);
	$table->set_header(1, get_lang('OfficialCode'));
	$table->set_header(2, get_lang('LastName'));
	$table->set_header(3, get_lang('FirstName'));
	$table->set_header(4, get_lang('LoginName'));
	$table->set_header(5, get_lang('Email'));
	$table->set_header(6, get_lang('Status'));
	$table->set_header(7, get_lang('Active'));
	$table->set_header(8, get_lang('Modify'));
	$table->set_column_filter(5, 'email_filter');
	$table->set_column_filter(6, 'status_filter');
	$table->set_column_filter(7, 'active_filter');
	$table->set_column_filter(8, 'modify_filter');
	$table->set_form_actions(array ('delete' => get_lang('DeleteFromPlatform')));
	$table->display();
}
/*
==============================================================================
		FOOTER
==============================================================================
*/
Display :: display_footer();
?>