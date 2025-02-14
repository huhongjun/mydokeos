<?php // $Id: user_edit.php 15105 2008-04-25 08:38:20Z elixir_inter $
/*
==============================================================================
	Dokeos - elearning and course management software

	Copyright (c) 2004 Dokeos S.A.
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

	Contact: Dokeos, 181 rue Royale, B-1000 Brussels, Belgium, info@dokeos.com
==============================================================================
*/
/**
==============================================================================
*	@package dokeos.admin
==============================================================================
*/
// name of the language file that needs to be included
$language_file=array('admin','registration');
$cidReset=true;
include('../inc/global.inc.php');
$this_section=SECTION_PLATFORM_ADMIN;

api_protect_admin_script();


$htmlHeadXtra[] = '
<script language="JavaScript" type="text/JavaScript">
<!--
function enable_expiration_date() { //v2.0
	document.user_add.radio_expiration_date[0].checked=false;
	document.user_add.radio_expiration_date[1].checked=true;
}

function display_drh_list(){
	if(document.getElementById("status_select").value=='.STUDENT.')
	{
		document.getElementById("drh_list").style.display="block";
	}
	else
	{ 
		document.getElementById("drh_list").style.display="none";
		document.getElementById("drh_select").options[0].selected="selected";
	}
}
//-->
</script>';


include(api_get_path(LIBRARY_PATH).'fileManage.lib.php');
include(api_get_path(LIBRARY_PATH).'fileUpload.lib.php');
include(api_get_path(LIBRARY_PATH).'usermanager.lib.php');
require_once (api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php');
$user_id=isset($_GET['user_id']) ? intval($_GET['user_id']) : intval($_POST['user_id']);
$noPHP_SELF=true;
$tool_name=get_lang('ModifyUserInfo');

$interbreadcrumb[]=array('url' => 'index.php',"name" => get_lang('PlatformAdmin'));
$interbreadcrumb[]=array('url' => "user_list.php","name" => get_lang('UserList'));

$table_user = Database::get_main_table(TABLE_MAIN_USER);
$table_admin = Database::get_main_table(TABLE_MAIN_ADMIN);
$sql = "SELECT u.*, a.user_id AS is_admin FROM $table_user u LEFT JOIN $table_admin a ON a.user_id = u.user_id WHERE u.user_id = '".$user_id."'";
$res = api_sql_query($sql,__FILE__,__LINE__);
if(mysql_num_rows($res) != 1)
{
	header('Location: user_list.php');
	exit;
}
$user_data = mysql_fetch_array($res,MYSQL_ASSOC);
$user_data['platform_admin'] = is_null($user_data['is_admin']) ? 0 : 1;
$user_data['send_mail'] = 0;
$user_data['old_password'] = $user_data['password'];
unset($user_data['password']);

$user_data = array_merge($user_data, Usermanager :: get_extra_user_data($user_id,true));

// Create the form
$form = new FormValidator('user_add','post','','',array('style' => 'width: 60%; float: '.($text_dir=='rtl'?'right;':'left;')));
$form->addElement('hidden','user_id',$user_id);

// Lastname
$form->addElement('text','lastname',get_lang('LastName'));
$form->applyFilter('lastname','html_filter');
$form->applyFilter('lastname','trim');
$form->addRule('lastname', get_lang('ThisFieldIsRequired'), 'required');

// Firstname
$form->addElement('text','firstname',get_lang('FirstName'));
$form->applyFilter('firstname','html_filter');
$form->applyFilter('firstname','trim');
$form->addRule('firstname', get_lang('ThisFieldIsRequired'), 'required');

// Official code
$form->addElement('text', 'official_code', get_lang('OfficialCode'),array('size' => '40'));
$form->applyFilter('official_code','html_filter');
$form->applyFilter('official_code','trim');

// Email
$form->addElement('text', 'email', get_lang('Email'),array('size' => '40'));
$form->addRule('email', get_lang('EmailWrong'), 'email');
$form->addRule('email', get_lang('EmailWrong'), 'required');

// OpenID
if(api_get_setting('openid_authentication')=='true')
{
	$form->addElement('text', 'openid', get_lang('OpenIDURL'),array('size' => '40'));
}

// Phone
$form->addElement('text','phone',get_lang('PhoneNumber'));

// Picture
$form->addElement('file', 'picture', get_lang('AddPicture'));
$allowed_picture_types = array ('jpg', 'jpeg', 'png', 'gif');
$form->addRule('picture', get_lang('OnlyImagesAllowed').' ('.implode(',', $allowed_picture_types).')', 'filetype', $allowed_picture_types);
if (strlen($user_data['picture_uri']) > 0 )
{
	$form->addElement('checkbox', 'delete_picture', '', get_lang('DelImage'));
}

// Username
$form->addElement('text', 'username', get_lang('LoginName'),array('maxlength'=>20));
$form->addRule('username', get_lang('ThisFieldIsRequired'), 'required');
$form->addRule('username', get_lang('OnlyLettersAndNumbersAllowed'), 'username');
$form->addRule('username', '', 'maxlength',20);
$form->addRule('username', get_lang('UserTaken'), 'username_available', $user_data['username']);

// Password
$form->addElement('radio','reset_password',get_lang('Password'),get_lang('DontResetPassword'),0);
if(count($extAuthSource) > 0)
{
	$group[] =& HTML_QuickForm::createElement('radio','reset_password',null,get_lang('ExternalAuthentication').' ',3);
	$auth_sources = array();
	foreach($extAuthSource as $key => $info)
	{
		$auth_sources[$key] = $key;
	}
	$group[] =& HTML_QuickForm::createElement('select','auth_source',null,$auth_sources);
	$group[] =& HTML_QuickForm::createElement('static','','','<br />');
	$form->addGroup($group, 'password', null, '',false);
}
$form->addElement('radio','reset_password',null,get_lang('AutoGeneratePassword'),1);
$group = array();
$group[] =& HTML_QuickForm::createElement('radio', 'reset_password',null,null,2);
$group[] =& HTML_QuickForm::createElement('password', 'password',null,null);
$form->addGroup($group, 'password', null, '',false);

// Status
$status = api_get_status_langvars();
$form->addElement('select','status',get_lang('Status'),$status,'id="status_select" onchange="display_drh_list()"');

$display = $user_data['status'] == STUDENT || $_POST['status'] == STUDENT ? 'block' : 'none';
$form->addElement('html','<div id="drh_list" style="display:'.$display.';">');
$drh_select = $form->addElement('select','hr_dept_id',get_lang('Drh'),array(),'id="drh_select"');
$drh_list = UserManager :: get_user_list(array('status'=>DRH),array('lastname','firstname'));
$drh_select->addOption('---',0);
foreach($drh_list as $drh)
{
	$drh_select->addOption($drh['lastname'].' '.$drh['firstname'],$drh['user_id']);
}
$form->addElement('html', '</div>');

// Platform admin
// Only when changing another user!
if($user_id != $_SESSION['_uid'])
{
	$group = array();
	$group[] =& HTML_QuickForm::createElement('radio', 'platform_admin',null,get_lang('Yes'),1);
	$group[] =& HTML_QuickForm::createElement('radio', 'platform_admin',null,get_lang('No'),0);
	$form->addGroup($group, 'admin', get_lang('PlatformAdmin'), '&nbsp;',false);
}

// Send email
$group = array();
$group[] =& HTML_QuickForm::createElement('radio', 'send_mail',null,get_lang('Yes'),1);
$group[] =& HTML_QuickForm::createElement('radio', 'send_mail',null,get_lang('No'),0);
$form->addGroup($group, 'mail', get_lang('SendMailToNewUser'), '&nbsp;',false);

// Registration Date
$form->addElement('static','registration_date', get_lang('RegistrationDate'), $user_data['registration_date']);

if(! $user_data['platform_admin'] )
{
	// Expiration Date
	$form->addElement('radio', 'radio_expiration_date', get_lang('ExpirationDate'), get_lang('NeverExpires'), 0);
	$group = array ();
	$group[] = & $form->createElement('radio', 'radio_expiration_date', null, get_lang('On'), 1);
	$group[] = & $form->createElement('datepicker', 'expiration_date',null, array ('form_name' => $form->getAttribute('name'), 'onChange'=>'enable_expiration_date()'));
	$form->addGroup($group, 'max_member_group', null, '', false);

	// Active account or inactive account
	$form->addElement('radio','active',get_lang('ActiveAccount'),get_lang('Active'),1);
	$form->addElement('radio','active','',get_lang('Inactive'),0);
}


// EXTRA FIELDS
$extra = UserManager::get_extra_fields(0,50,5,'ASC');
foreach($extra as $id => $field_details)
{
	switch($field_details[2])
	{
		case USER_FIELD_TYPE_TEXT:
			$form->addElement('text', 'extra_'.$field_details[1], $field_details[3], array('size' => 40));
			$form->applyFilter('extra_'.$field_details[1], 'stripslashes');
			$form->applyFilter('extra_'.$field_details[1], 'trim');
			break;
		case USER_FIELD_TYPE_TEXTAREA:
			$form->add_html_editor('extra_'.$field_details[1], $field_details[3], false);
			//$form->addElement('textarea', 'extra_'.$field_details[1], $field_details[3], array('size' => 80));
			$form->applyFilter('extra_'.$field_details[1], 'stripslashes');
			$form->applyFilter('extra_'.$field_details[1], 'trim');
			break;
		case USER_FIELD_TYPE_RADIO:
			$group = array();
			foreach($field_details[8] as $option_id => $option_details)
			{
				$options[$option_details[1]] = $option_details[2];
				$group[] =& HTML_QuickForm::createElement('radio', 'extra_'.$field_details[1], $option_details[1],$option_details[2].'<br />',$option_details[1]);
			}
			$form->addGroup($group, 'extra_'.$field_details[1], $field_details[3], '');
			break;
		case USER_FIELD_TYPE_SELECT:
			$options = array();
			foreach($field_details[8] as $option_id => $option_details)
			{
				$options[$option_details[1]] = $option_details[2];
			}
			$form->addElement('select','extra_'.$field_details[1],$field_details[3],$options,'');			
			break;
		case USER_FIELD_TYPE_SELECT_MULTIPLE:
			$options = array();
			foreach($field_details[8] as $option_id => $option_details)
			{
				$options[$option_details[1]] = $option_details[2];
			}
			$form->addElement('select','extra_'.$field_details[1],$field_details[3],$options,array('multiple' => 'multiple'));
			break;
		case USER_FIELD_TYPE_DATE:
			$form->addElement('datepickerdate', 'extra_'.$field_details[1], $field_details[3]);
			$form->_elements[$form->_elementIndex['extra_'.$field_details[1]]]->setLocalOption('minYear',1900);
			$form->applyFilter('theme', 'trim');
			break;
		case USER_FIELD_TYPE_DATETIME:
			$form->addElement('datepicker', 'extra_'.$field_details[1], $field_details[3]);
			$form->applyFilter('theme', 'trim');
			break;
	}
}


// Submit button
$form->addElement('submit', 'submit', get_lang('OK'));

// Set default values
$expiration_date=$user_data['expiration_date'];
if ($expiration_date=='0000-00-00 00:00:00')
{
	$user_data['radio_expiration_date']=0;
	$user_data['expiration_date']=array();
	$user_data['expiration_date']['d']=date('d');
	$user_data['expiration_date']['F']=date('m');
	$user_data['expiration_date']['Y']=date('Y');
}
else
{
	$user_data['radio_expiration_date']=1;
	$user_data['expiration_date']=array();
	$user_data['expiration_date']['d']=substr($expiration_date,8,2);
	$user_data['expiration_date']['F']=substr($expiration_date,5,2);
	$user_data['expiration_date']['Y']=substr($expiration_date,0,4);
}
$form->setDefaults($user_data);


// Validate form
if( $form->validate())
{
	$user = $form->exportValues();
	$picture_element = & $form->getElement('picture');
	$picture = $picture_element->getValue();
	$picture_uri = '';
	
	if (strlen($picture['name']) > 0)
	{
		$picture_uri = uniqid('').'_'.replace_dangerous_char($picture['name']);
		$picture_location = api_get_path(SYS_CODE_PATH).'upload/users/'.$picture_uri;
		move_uploaded_file($picture['tmp_name'], $picture_location);
	}
	elseif(isset($user['delete_picture']))
	{
		@unlink('../upload/users/'.$user_data['picture_uri']);
	}
	
	if (strlen($picture['name']) == 0){
		$picture_uri = $user_data['picture_uri'];
	}
	
	$lastname = $user['lastname'];
	$firstname = $user['firstname'];
	$official_code = $user['official_code'];
	$email = $user['email'];
	$phone = $user['phone'];
	$username = $user['username'];
	$status = intval($user['status']);
	$picture = $_FILES['picture'];
	$platform_admin = intval($user['platform_admin']);
	$send_mail = intval($user['send_mail']);
	$reset_password = intval($user['reset_password']);
	$hr_dept_id = intval($user['hr_dept_id']);
	if ($user['radio_expiration_date']=='1' && ! $user_data['platform_admin'] )
	{
		$expiration_date=$user['expiration_date'];
	}
	else
	{
		$expiration_date='0000-00-00 00:00:00';
	}
	$active = $user_data['platform_admin'] ? 1 : intval($user['active']);

	if( $reset_password == 0)
	{
		$password = null;
		$auth_source = $user_data['auth_source'];
	}
	elseif($reset_password == 1)
	{
		$password = api_generate_password();
		$auth_source = PLATFORM_AUTH_SOURCE;
	}
	elseif($reset_password == 2)
	{
		$password = $user['password'];
		$auth_source = PLATFORM_AUTH_SOURCE;
	}
	elseif($reset_password == 3)
	{
		$password = $user['password'];
		$auth_source = $user['auth_source'];		
	}
	UserManager::update_user($user_id,$firstname,$lastname,$username,$password,$auth_source,$email,$status,$official_code,$phone,$picture_uri,$expiration_date, $active, null, $hr_dept_id);
	if(api_get_setting('openid_authentication')=='true' && !empty($user['openid']))
	{
		$up = UserManager::update_openid($user_id,$user['openid']);
	}
	if($user_id != $_SESSION['_uid'])
	{
		if($platform_admin == 1)
		{
			$sql = "INSERT IGNORE INTO $table_admin SET user_id = '".$user_id."'";
			api_sql_query($sql,__FILE__,__LINE__);
		}
		else
		{
			$sql = "DELETE FROM $table_admin WHERE user_id = '".$user_id."'";
			api_sql_query($sql,__FILE__,__LINE__);
		}
	}
	
	$extras = array();
	foreach($user as $key => $value)
	{
		if(substr($key,0,6)=='extra_') //an extra field
		{
			$myres = UserManager::update_extra_field_value($user_id,substr($key,6),$value);
		}
	}
	
	if (!empty ($email) && $send_mail)
	{
		$emailto = '"'.$firstname.' '.$lastname.'" <'.$email.'>';
		$emailsubject = '['.get_setting('siteName').'] '.get_lang('YourReg').' '.get_setting('siteName');
		$emailheaders = 'From: '.get_setting('administratorName').' '.get_setting('administratorSurname').' <'.get_setting('emailAdministrator').">\n";
		$emailheaders .= 'Reply-To: '.get_setting('emailAdministrator');
		$emailbody = get_lang('Dear')." ".stripslashes("$firstname $lastname").",\n\n".get_lang('YouAreReg')." ". get_setting('siteName') ." ".get_lang('Settings')." ". $username;
		if($reset_password != 0 && !$userPasswordCrypted )
		{
			$emailbody .= "\n".get_lang('Pass')." : ".stripslashes($password);
		}
		$emailbody .= "\n\n" .get_lang('Address') ." ". get_setting('siteName') ." ". get_lang('Is') ." : ". $_configuration['root_web'] ."\n\n". get_lang('Problem'). "\n\n". get_lang('Formula').",\n\n".get_setting('administratorName')." ".get_setting('administratorSurname')."\n". get_lang('Manager'). " ".get_setting('siteName')."\nT. ".get_setting('administratorTelephone')."\n" .get_lang('Email') ." : ".get_setting('emailAdministrator');
		@api_send_mail($emailto, $emailsubject, $emailbody, $emailheaders);
	}
	header('Location: user_list.php?action=show_message&message='.urlencode(get_lang('UserUpdated')));
	exit();
}

Display::display_header($tool_name);
//api_display_tool_title($tool_name);
// Show the users picture
//edit by xiaoping
$image_path = UserManager::get_user_picture_path_by_id($user_data['user_id'],'web');
if (strlen($user_data['picture_uri']) > 0)
{
	$picture_url = $image_path['dir'].$image_path['file'];
}
else
{
	$picture_url = api_get_path(WEB_CODE_PATH)."img/unknown.jpg";
}
$image_size = @getimagesize($picture_url);
$img_attributes = 'src="'.$picture_url.'?rand='.time().'" '
	.'alt="'.$user_data['lastname'].' '.$user_data['firstname'].'" '
	.'style="float:'.($text_dir == 'rtl' ? 'left' : 'right').'; padding:5px;" ';
if ($image_size[0] > 200) //limit display width to 300px
	$img_attributes .= 'width="200" ';
echo '<img '.$img_attributes.'/>';
// Display form
$form->display();
/*
==============================================================================
		FOOTER
==============================================================================
*/
Display::display_footer();
?>
