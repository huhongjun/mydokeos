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
include("../../main/inc/global.inc.php"); 
require_once(api_get_path(LIBRARY_PATH).'/formvalidator/FormValidator.class.php');

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

$form = new FormValidator('cost_per_credit','post',api_get_path(PLUGIN_PATH).'credits_system_settings.php?select=cscostpercredit','_SELF');

$form->addElement('text','cost','Set the amount of euros per credit as:','size="1", maxlength="3"');
$form->addElement('submit','submit','submit');

if( $form->validate())
{
	Display::display_confirmation_message('Cost per credit updated.');
}

$form->display();

?>