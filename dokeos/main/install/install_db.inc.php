<?php
/*
==============================================================================
	Dokeos - elearning and course management software

	Copyright (c) 2004-2005 Dokeos S.A.
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

	Contact address: Dokeos, 44 rue des palais, B-1030 Brussels, Belgium
	Mail: info@dokeos.com
==============================================================================
*/
/**
==============================================================================
*	Install the Dokeos database
*	Notice : This script has to be included by index.php
*
*	@package dokeos.install
==============================================================================
*/

require_once("install_upgrade.lib.php");

/*
==============================================================================
		MAIN CODE
==============================================================================
*/ 

//this page can only be access through including from the install script.

if( ! defined('DOKEOS_INSTALL'))
{
	echo 'You are not allowed here!';
	exit;
}

set_file_folder_permissions();

@mysql_connect($dbHostForm,$dbUsernameForm,$dbPassForm);

if(mysql_errno() > 0)
{
	$no=mysql_errno();
	$msg=mysql_error();

	echo '<hr />['.$no.'] &ndash; '.$msg.'<hr>
	The MySQL server doesn\'t work or login / pass is bad.<br /><br />
	Please check these values:<br /><br />
	<b>host</b> : '.$dbHostForm.'<br />
	<b>user</b> : '.$dbUsernameForm.'<br />
	<b>password</b> : '.$dbPassForm.'<br /><br />
	Please go back to step 3.
	<p><input type="submit" name="step3" value="&lt; Back" /></p>
	</td></tr></table></form></body></html>';

	exit();
}

if($urlForm[strlen($urlForm)-1] != '/')
{
	$urlForm=$urlForm.'/';
}

if($encryptPassForm)
{
	$passToStore=md5($passForm);
}
else
{
	$passToStore=($passForm);
}

$dbPrefixForm=eregi_replace('[^a-z0-9_-]','',$dbPrefixForm);

$dbNameForm=eregi_replace('[^a-z0-9_-]','',$dbNameForm);
$dbStatsForm=eregi_replace('[^a-z0-9_-]','',$dbStatsForm);
$dbUserForm=eregi_replace('[^a-z0-9_-]','',$dbUserForm);

if(!empty($dbPrefixForm) && !ereg('^'.$dbPrefixForm,$dbNameForm))
{
	$dbNameForm=$dbPrefixForm.$dbNameForm;
}

if(!empty($dbPrefixForm) && !ereg('^'.$dbPrefixForm,$dbStatsForm))
{
	$dbStatsForm=$dbPrefixForm.$dbStatsForm;
}

if(!empty($dbPrefixForm) && !ereg('^'.$dbPrefixForm,$dbUserForm))
{
	$dbUserForm=$dbPrefixForm.$dbUserForm;
}

$mysqlMainDb=$dbNameForm;
$mysqlStatsDb=$dbStatsForm;
$mysqlUserDb=$dbUserForm;

if(empty($mysqlMainDb) || $mysqlMainDb == 'mysql' || $mysqlMainDb == $dbPrefixForm)
{
	$mysqlMainDb=$dbPrefixForm.'main';
}

if(empty($mysqlStatsDb) || $mysqlStatsDb == 'mysql' || $mysqlStatsDb == $dbPrefixForm)
{
	$mysqlStatsDb=$dbPrefixForm.'stats';
}

if(empty($mysqlUserDb) || $mysqlUserDb == 'mysql' || $mysqlUserDb == $dbPrefixForm)
{
	$mysqlUserDb=$dbPrefixForm.'user';
}

$result=mysql_query("SHOW VARIABLES LIKE 'datadir'") or die(mysql_error());

$mysqlRepositorySys=mysql_fetch_array($result);
$mysqlRepositorySys=$mysqlRepositorySys['Value'];

if(!$singleDbForm)
{
	mysql_query("DROP DATABASE IF EXISTS `$mysqlMainDb`") or die(mysql_error());
}
mysql_query("CREATE DATABASE IF NOT EXISTS `$mysqlMainDb`") or die(mysql_error());

if($mysqlStatsDb == $mysqlMainDb && $mysqlUserDb == $mysqlMainDb)
{
	$singleDbForm=true;
}

/**
* CREATING THE STATISTICS DATABASE
*/
if($mysqlStatsDb != $mysqlMainDb)
{
	if(!$singleDbForm)
	{
		// multi DB mode AND tracking has its own DB so create it
		mysql_query("DROP DATABASE IF EXISTS `$mysqlStatsDb`") or die(mysql_error());
		mysql_query("CREATE DATABASE `$mysqlStatsDb`") or die(mysql_error());
	}
	else
	{
		// single DB mode so $mysqlStatsDb MUST BE the SAME than $mysqlMainDb
		$mysqlStatsDb=$mysqlMainDb;
	}
}

/**
* CREATING THE USER DATABASE
*/
if($mysqlUserDb != $mysqlMainDb)
{
	if(!$singleDbForm)
	{
		// multi DB mode AND user data has its own DB so create it
		mysql_query("DROP DATABASE IF EXISTS `$mysqlUserDb`") or die(mysql_error());
		mysql_query("CREATE DATABASE `$mysqlUserDb`") or die(mysql_error());
	}
	else
	{
		// single DB mode so $mysqlUserDb MUST BE the SAME than $mysqlMainDb
		$mysqlUserDb=$mysqlMainDb;
	}
}

include("../lang/english/create_course.inc.php");

if($languageForm != 'english')
{
	include("../lang/$languageForm/create_course.inc.php");
}

/**
* creating the tables of the main database
*/
mysql_select_db($mysqlMainDb) or die(mysql_error());

$installation_settings['{ORGANISATIONNAME}'] = $institutionForm;
$installation_settings['{ORGANISATIONURL}'] = $institutionUrlForm;
$installation_settings['{CAMPUSNAME}'] = $campusForm;
$installation_settings['{PLATFORMLANGUAGE}'] = $languageForm;
$installation_settings['{ALLOWSELFREGISTRATION}'] = trueFalse($allowSelfReg);
$installation_settings['{ALLOWTEACHERSELFREGISTRATION}'] = trueFalse($allowSelfRegProf);
$installation_settings['{ADMINLASTNAME}'] = $adminLastName;
$installation_settings['{ADMINFIRSTNAME}'] = $adminFirstName;
$installation_settings['{ADMINLOGIN}'] = $loginForm;
$installation_settings['{ADMINPASSWORD}'] = $passToStore;
$installation_settings['{ADMINEMAIL}'] = $emailForm;
$installation_settings['{ADMINPHONE}'] = $adminPhoneForm;
$installation_settings['{PLATFORM_AUTH_SOURCE}'] = PLATFORM_AUTH_SOURCE;

load_main_database($installation_settings);

/**
* creating the tables of the tracking database
*/

mysql_select_db($mysqlStatsDb) or die(mysql_error());

load_database_script('dokeos_stats.sql');

$track_countries_table = "track_c_countries";
fill_track_countries_table($track_countries_table);

/**
* creating the tables of the USER database
* this is where the personal agenda items are storen, the user defined course categories (sorting of my courses)
*/

mysql_select_db($mysqlUserDb) or die(mysql_error());

load_database_script('dokeos_user.sql');

?>