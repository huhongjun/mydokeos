<?php
// $Id: lost_password.lib.php 14021 2007-12-18 19:48:14Z yannoo $
/*
==============================================================================
	Dokeos - elearning and course management software

	Copyright (c) 2004 Dokeos S.A.
	Copyright (c) 2003 Ghent University (UGent)
	Copyright (c) 2001 Universite catholique de Louvain (UCL)
	Copyright (c) various contributors

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
 * Enter description here...
 *
 * @return unknown
 * @author Olivier Cauberghe <olivier.cauberghe@UGent.be>, Ghent University
 */
function get_email_headers()
{//edit by xiaoping
	global $charset;
	$emailHeaders = "From:".get_setting('emailAdministrator')."\r\n";//	$emailHeaders = "From: \"".addslashes(get_setting('administratorSurname')." ".get_setting('administratorName'))."\" <".get_setting('emailAdministrator').">\r\n";
	$emailHeaders .= "Reply-To:".get_setting('emailAdministrator')."\r\n";
	//$emailHeaders .= "X-Sender:".get_setting('emailAdministrator')."\r\n";
	$emailHeaders .= "X-Mailer:PHP/".phpversion();
	//$emailHeaders .= "Content-Type: text/plain;charset=\"".$charset."\"\r\n";
	//$emailHeaders .= "Mime-Version: 1.0";
	return $emailHeaders;
}
/**
 * Enter description here...
 *
 * @param unknown_type $user
 * @param unknown_type $reset
 * @return unknown
 * @author Olivier Cauberghe <olivier.cauberghe@UGent.be>, Ghent University
 */
function get_user_account_list($user, $reset = false)
{
	global $_configuration;
	foreach ($user as $thisUser)
	{
		$secretword = get_secret_word($thisUser["email"]);
		if ($reset)
		{
			$reset_link = $_configuration['root_web']."main/auth/lostPassword.php?reset=".$secretword."&id=".$thisUser['uid'];
		}
		else
		{
			$reset_link = get_lang('Pass')." : $thisUser[password]";
		}
		$userAccountList[] = get_lang('YourRegistrationData')." : \n".get_lang('UserName').' : '.$thisUser["loginName"]."\n".get_lang('ResetLink').' : '.$reset_link.'';
	}
	if ($userAccountList)
	{
		$userAccountList = implode("\n------------------------\n", $userAccountList);
	}
	return $userAccountList;
}
/**
 * This function sends the actual password to the user
 *
 * @param unknown_type $user
 * @author Olivier Cauberghe <olivier.cauberghe@UGent.be>, Ghent University
 */
function send_password_to_user($user)
{
	global $charset;
	global $_configuration;

	$emailHeaders = get_email_headers(); // Email Headers
	$emailSubject = "[".get_setting('siteName')."] ".get_lang('LoginRequest'); // SUBJECT
	$userAccountList = get_user_account_list($user); // BODY
	$emailBody = get_lang('YourAccountParam')." ".$_configuration['root_web']."\n\n$userAccountList";
	// SEND MESSAGE
	$emailTo = $user[0]["email"];
	//echo '$emailTo:'.$emailTo.' $emailSubject'.$emailSubject.' $emailBody:'.$emailBody.' $emailHeaders:'.$emailHeaders;exit;
	if (@ api_send_mail($emailTo, $emailSubject, $emailBody, $emailHeaders))
	{
		Display::display_confirmation_message(get_lang('YourPasswordHasBeenEmailed'));
	}
	else
	{
		$message = get_lang('SystemUnableToSendEmailContact') . Display :: encrypted_mailto_link(get_setting('emailAdministrator'), get_lang('PlatformAdmin')).".</p>";
		Display::display_error_message($message, false);
	}
}
/**
 * Enter description here...
 *
 * @param unknown_type $user
 * @return unknown
 *
 * @author Olivier Cauberghe <olivier.cauberghe@UGent.be>, Ghent University
 */
function handle_encrypted_password($user)
{
	global $charset;
	global $_configuration;
	$emailHeaders = get_email_headers(); // Email Headers
	$emailSubject = "[".get_setting('siteName')."] ".get_lang('LoginRequest'); // SUBJECT
	$userAccountList = get_user_account_list($user, true); // BODY
	$emailTo = $user[0]["email"];
	$secretword = get_secret_word($emailTo);
	//$emailBody = get_lang("password_request")."\n\n\n".get_lang("YourAccountParam")." ".$_configuration['root_web']."\n\n".$userAccountList;
	$emailBody = get_lang('DearUser')." :\n".get_lang("password_request")."\n\n";
	$emailBody .= "-----------------------------------------------\n".$userAccountList."\n-----------------------------------------------\n\n";
	$emailBody .=get_lang('PasswordEncryptedForSecurity');
	$emailBody .="\n\n".get_lang('Formula').",\n".get_lang('PlataformAdmin');
	//echo '$emailTo:'.$emailTo.' $emailSubject'.$emailSubject.' $emailBody:'.$emailBody.' $emailHeaders:'.$emailHeaders;exit;
	if (@ api_send_mail($emailTo, $emailSubject, $emailBody, $emailHeaders))
	{
		Display::display_confirmation_message(get_lang('YourPasswordHasBeenEmailed'));
	}
	else
	{
		$message = get_lang('SystemUnableToSendEmailContact') . Display :: encrypted_mailto_link(get_setting('emailAdministrator'), get_lang('PlatformAdmin')).".</p>";
		Display::display_error_message($message, false);
	}
}
/**
 * Enter description here...
 * @author Olivier Cauberghe <olivier.cauberghe@UGent.be>, Ghent University
 */
function get_secret_word($add)
{
	global $_configuration;
	return $secretword = md5($_configuration['security_key'].$add);
}
/**
 * Enter description here...
 * @author Olivier Cauberghe <olivier.cauberghe@UGent.be>, Ghent University
 */
function reset_password($secret, $id)
{
	global $your_password_has_been_reset,$userPasswordCrypted;
	$tbl_user = Database::get_main_table(TABLE_MAIN_USER);
	$id = (int) $id;
	$sql = "SELECT user_id AS uid, lastname AS lastName, firstname AS firstName, username AS loginName, password, email FROM ".$tbl_user." WHERE user_id=$id";
	$result = api_sql_query($sql,__FILE__,__LINE__);
	if ($result && mysql_num_rows($result))
	{
		$user[] = mysql_fetch_array($result);
	}
	else
	{
		return "Could not reset password.";
	}
	if (get_secret_word($user[0]["email"]) == $secret) // OK, secret word is good. Now change password and mail it.
	{
		$user[0]["password"] = api_generate_password();
		$crypted = $user[0]["password"];
		if( $userPasswordCrypted)
		{
			$crypted = md5($crypted);
		}
		api_sql_query("UPDATE ".$tbl_user." SET password='$crypted' WHERE user_id=$id");
		return send_password_to_user($user, $your_password_has_been_reset);
	}
	else
	{
		return "Not allowed.";
	}
}
?>