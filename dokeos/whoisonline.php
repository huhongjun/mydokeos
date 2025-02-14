<?php // $Id: whoisonline.php 14958 2008-04-20 18:17:02Z yannoo $
/*
==============================================================================
	Dokeos - elearning and course management software

	Copyright (c) 2004-2008 Dokeos SPRL
	Copyright (c) various contributors

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
 * @todo use the correct api_get_path instead of $clarolineRepositoryWeb
 */


/**
==============================================================================
* Who is online list
==============================================================================
*/
// name of the language file that needs to be included
$language_file = array('index','registration');

// including necessary files
require_once('./main/inc/global.inc.php');
require_once (api_get_path(LIBRARY_PATH).'fileManage.lib.php');
require_once (api_get_path(LIBRARY_PATH).'usermanager.lib.php');

// table definitions
$track_user_table = Database::get_main_table(TABLE_MAIN_USER);


if ($_GET['chatid'] != '')
{
	//send out call request
	$time = time();
	$time = date("Y-m-d H:i:s", $time);
	$chatid = addslashes($_GET['chatid']);
	$sql="update $track_user_table set chatcall_user_id = '".mysql_real_escape_string($_user['user_id'])."', chatcall_date = '".mysql_real_escape_string($time)."', chatcall_text = '' where (user_id = ".mysql_real_escape_string($chatid).")";
	$result=api_sql_query($sql,__FILE__,__LINE__);

	//redirect caller to chat
	header("Location: ".$_configuration['code_append']."chat/chat.php?".api_get_cidreq()."&origin=whoisonline&target=".Security::remove_XSS($chatid));
	exit();
}


/**
 * Displays a sortable table with the list of online users.
 * @param array $user_list
 */
function display_user_list($user_list, $_plugins)
{
	global $charset;
	if ($_GET["id"]=='')
	{
		$extra_params = array();
		$course_url = '';
		if(strlen($_GET['cidReq']) > 0)
		{
			$extra_params['cidReq'] = Database::escape_string($_GET['cidReq']);
			$course_url = '&amp;cidReq='.Security::remove_XSS($_GET['cidReq']);
		}
		foreach($user_list as $user)
		{
			$uid=$user[0];
			$user_info = api_get_user_info($uid);
			$table_row = array();
			$url = '?id='.$uid.$course_url;
			if(strlen($user_info['picture_uri']) > 0)
			{
				$table_row[] = '<span style="display:none;">1</span><a href="'.$url.'"><img src="'.api_get_path(WEB_CODE_PATH).'upload/users/'.$uid."/".$user_info['picture_uri'].'" alt="'.htmlspecialchars($user_info['firstName'],ENT_QUOTES,$charset).'" width="40" border="0"/></a>';
			}
			else
			{
				$table_row[] = '<span style="display:none;">0</span>';
			}
			$table_row[] = '<a href="'.$url.'">'.$user_info['lastName'].'</a>';
			$table_row[] = '<a href="'.$url.'">'.$user_info['firstName'].'</a>';
			
			if (api_get_setting("show_email_addresses") == "true")
			{
				$table_row[] = Display::encrypted_mailto_link($user_info['mail']);
			}
			if ( api_is_plugin_installed($_plugins, 'messages') && isset($_SESSION['_user']) )
			{
				$table_row[] = '<a href="' . api_get_path(WEB_PLUGIN_PATH).'messages/new_message.php?send_to_user=' . $uid. '"><img src="./main/img/forum.gif" alt="'.get_lang("ComposeMessage").'" align="middle"></img></a>';
			}
			$table_data[] = $table_row;
		}
		$table_header[] = array(get_lang('UserPicture'),true,'width="50"');
		$table_header[] = array(get_lang('LastName'),true);
		$table_header[] = array(get_lang('FirstName'),true);
		
		if (api_get_setting("show_email_addresses") == "true")
		{
			$table_header[] = array(get_lang('Email'),true);
		}
		if ( api_is_plugin_installed($_plugins, 'messages') && isset($_SESSION['_user']))
		{
			$table_header[] = array(get_lang('SendMessage'),true);
		}
		$sorting_options['column'] = (isset ($_GET['column']) ? (int)$_GET['column'] : 2);
		Display::display_sortable_table($table_header,$table_data,$sorting_options,array('per_page_default'=>count($table_data)),$extra_params);
	}
}
/**
 * Displays the information of an individual user
 * @param int $user_id
 */
function display_individual_user($user_id)
{
	global $interbreadcrumb;
	$safe_user_id = Database::escape_string($user_id);

	// to prevent a hacking attempt: http://www.dokeos.com/forum/viewtopic.php?t=5363
	$user_table=Database::get_main_table(TABLE_MAIN_USER);
	$sql = "SELECT * FROM $user_table WHERE user_id='".$safe_user_id."'";
	$result=api_sql_query($sql,__FILE__,__LINE__);
	if (Database::num_rows($result)==1)
	{
		$user_object = Database::fetch_object($result);
		$name = GetFullUserName($user_id).($_SESSION['_uid'] == $user_id ? '&nbsp;<b>('.get_lang('Me').')</b>' : '' );
		$alt = GetFullUserName($user_id).($_SESSION['_uid'] == $user_id ? '&nbsp;('.get_lang('Me').')' : '');
		$status = ($user_object->status == COURSEMANAGER ? get_lang('Teacher') : get_lang('Student'));
		$interbreadcrumb[]=array("url" => "whoisonline.php","name" => get_lang('UsersOnLineList'));
		Display::display_header($alt);
		api_display_tool_title($alt);
		echo '<div style="text-align: center">';
		if (strlen(trim($user_object->picture_uri)) > 0)
		{
			$sysdir_array = UserManager::get_user_picture_path_by_id($safe_user_id,'system');
			$sysdir = $sysdir_array['dir'];
			$webdir_array = UserManager::get_user_picture_path_by_id($safe_user_id,'web');
			$webdir = $webdir_array['dir'];
			$fullurl=$webdir.$user_object->picture_uri;
			$system_image_path=$sysdir.$user_object->picture_uri;
			list($width, $height, $type, $attr) = getimagesize($system_image_path);
			$resizing = (($height > 200) ? 'height="200"' : '');
			$height += 30;
			$width += 30;
			$window_name = 'window'.uniqid('');
			$onclick = $window_name."=window.open('".$fullurl."','".$window_name."','alwaysRaised=yes, alwaysLowered=no,alwaysOnTop=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=no,width=".$width.",height=".$height.",left=200,top=20'); return false;";
			echo '<a href="#" onclick="'.$onclick.'" ><img src="'.$fullurl.'" '.$resizing.' alt="'.$alt.'"/></a><br />';
		}
		if (api_get_setting("show_email_addresses") == "true")
		{
			echo Display::encrypted_mailto_link($user_object->email,$user_object->email).'<br />';
		}
		echo $status.'<br />';
		echo '</div>';
		if ($user_object->competences)
		{
			echo '<dt><strong>'.get_lang('Competences').'</strong></dt>';
			echo '<dd>'.$user_object->competences.'</dd>';
		}
		if ($user_object->diplomas)
		{
			echo '<dt><strong>'.get_lang('Diplomas').'</strong></dt>';
			echo '<dd>'.$user_object->diplomas.'</dd>';
		}
		if ($user_object->teach) {
			echo '<dt><strong>'.get_lang('Teach').'</strong></dt>';
			echo '<dd>'.$user_object->teach.'</dd>';;
		}
		display_productions($user_object->user_id);
		if ($user_object->openarea) {
			echo '<dt><strong>'.get_lang('Openarea').'</strong></dt>';
			echo '<dd>'.$user_object->openarea.'</dd>';
		}
	}
	else
	{
		Display::display_header(get_lang('UsersOnLineList'));
		api_display_tool_title(get_lang('UsersOnLineList'));
	}
}
/**
 * Display productions in whoisonline
 * @param int $user_id User id
 * @todo use the correct api_get_path instead of $clarolineRepositoryWeb
 */
function display_productions($user_id)
{
	global $clarolineRepositoryWeb, $disabled_output;
	$sysdir_array = UserManager::get_user_picture_path_by_id($user_id,'system');
	$sysdir = $sysdir_array['dir'];
	$webdir_array = UserManager::get_user_picture_path_by_id($user_id,'web');
	$webdir = $webdir_array['dir'];
	if( !is_dir($sysdir))
	{
		mkpath($sysdir);
	}
	$handle = opendir($sysdir);
	$productions = array();
	while ($file = readdir($handle))
	{
		if ($file == '.' || $file == '..' || $file == '.htaccess')
		{
			continue;						// Skip current and parent directories
		}
		$productions[] = $file;
	}
	if(count($productions) > 0)
	{
		echo '<dt><strong>'.get_lang('Productions').'</strong></dt>';
		echo '<dd><ul>';
		foreach($productions as $index => $file)
		{
			// Only display direct file links to avoid browsing an empty directory
			if(is_file($sysdir.$file) && $file != $webdir_array['file']){
				echo '<li><a href="'.$webdir.urlencode($file).'" target=_blank>'.$file.'</a></li>';
			}
			// Real productions are under a subdirectory by the User's id
			if(is_dir($sysdir.$file)){
				$subs = scandir($sysdir.$file);
				foreach($subs as $my => $sub)
				{
					if(substr($sub,0,1) != '.' && is_file($sysdir.$file.'/'.$sub))
					{
						echo '<li><a href="'.$webdir.urlencode($file).'/'.urlencode($sub).'" target=_blank>'.$sub.'</a></li>';						
					}
				}
			}
		}
		echo '</ul></dd>';
	}
}



// This if statement prevents users accessing the who's online feature when it has been disabled.
if ((api_get_setting('showonline','world') == 'true' AND !$_user['user_id']) OR (api_get_setting('showonline','users') == 'true' AND $_user['user_id']))
{
	if(isset($_GET['cidReq']) && strlen($_GET['cidReq']) > 0)
	{
		$user_list = Who_is_online_in_this_course($_user['user_id'],api_get_setting('time_limit_whosonline'),$_GET['cidReq']);
	}
	else
	{
		$user_list = WhoIsOnline($_user['user_id'],$_configuration['statistics_database'],api_get_setting('time_limit_whosonline'));
	}


	$total=count($user_list);
	if (!isset($_GET['id']))
	{
		Display::display_header(get_lang('UsersOnLineList'));
		api_display_tool_title(get_lang('UsersOnLineList'));
		echo '<b>'.get_lang('TotalOnLine').' : '.$total.'</b>';
		if ($_GET['id']=='')
		{
			echo '<p><a href="javascript:window.location.reload()">'.get_lang('Refresh').'</a></p>';
		}
		else
		{
			if(0) // if ($_user['user_id'] && $_GET["id"] != $_user['user_id'])
			{
				echo '<a href="'.api_get_self().'?chatid='.Security::remove_XSS($_GET['id']).'">'.get_lang('SendChatRequest').'</a>';
			}
		}
	}

	if ($user_list!=false)
	{
		if (!isset($_GET['id']))
		{
			display_user_list($user_list, $_plugins);
		}
		else   //individual user information - also displays header info
		{
			display_individual_user($_GET['id']);
		}
	}
	elseif(isset($_GET['id']))
	{
		Display::display_header(get_lang('UsersOnLineList'));
		api_display_tool_title(get_lang('UsersOnLineList'));
	}
}
else
{
	Display::display_header(get_lang('UsersOnLineList'));
	Display::display_error_message(get_lang('AccessNotAllowed'));
}
$referer = empty($_GET['referer'])?'index.php':htmlspecialchars(strip_tags($_GET['referer']),ENT_QUOTES,$charset);
echo '<a href="'.(Security::remove_XSS($_GET['id'])?'javascript:window.history.back();':$referer).'">&lt; '.get_lang('Back').'</a>';

/*
==============================================================================
		FOOTER
==============================================================================
*/
Display::display_footer();
?>