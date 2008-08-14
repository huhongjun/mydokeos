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

//require_once("inc/credits_database.lib.php");
include("../../main/inc/global.inc.php");
require_once(api_get_path(LIBRARY_PATH).'/formvalidator/FormValidator.class.php');
$table_payment_options = Database :: get_main_table(CS_TABLE_PAYMENT_OPTIONS);

/*
-----------------------------------------------------------
	Libraries
-----------------------------------------------------------
*/ 
//the main_api.lib.php, database.lib.php and display.lib.php
//libraries are included by default

	

	
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

/* Display::display_normal_message('probando...');
$sql = "SELECT * FROM ".$table_payment_options;
$res = api_sql_query($sql, __FILE__, __LINE__);
while ($cat = mysql_fetch_array($res))
{
	echo 'Cada: '.$cat['number'].$cat['type']."\n";
}*/

$form_current = new FormValidator('delete_options','post',api_get_path(PLUGIN_PATH).'credits_system_settings.php?select=cspaymentoptions&action=delete','_SELF');

$group = array();
$group[] = $form_current->createElement('static','','Current Options: ','User is now able to pay every: ');
//build array by db query (cs_payment_options table).
$select = array('1'=> '1 session',
				'2'=> '1 day', 
				'3'=> '1 week', 
				'4'=> '6 month', 
				'5'=> '1 year');
$group[] = $form_current->createElement('select','','',$select,array('size' => count($select),'multiple' => 'multiple'));

$group[] = $form_current->createElement('submit','submit',get_lang('Delete'));
$form_current->addGroup($group,'','Current Options: ');

if( $_GET['action']=='delete' && $form_current->validate())
{
	Display::display_confirmation_message('Option/s deleted.');
}

$form_current->display();

echo "<br><br><br>";

$form_new = new FormValidator('delete_options','post',api_get_path(PLUGIN_PATH).'credits_system_settings.php?select=cspaymentoptions&action=add','_SELF');

$group = array();
$group[] = $form_new->createElement('static','','','User would be able to pay every ');
$group[] = $form_new->createElement('text','num_temp','new_option[]','size="1", maxlength="3"');
$group[] = $form_new->createElement('select','','new_option[]',array('session', 'day', 'week', 'month', 'year'));

$group[] = $form_new->createElement('submit','submit',get_lang('Add'));
$form_new->addGroup($group,'','New Option: ');

if( $_GET['action']=='add' && $form_new->validate())
{
	Display::display_confirmation_message('New option added.');
}

$form_new->display();

?>