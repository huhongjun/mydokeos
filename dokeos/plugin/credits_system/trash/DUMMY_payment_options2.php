<?php
// $Id: add_payment_option.php 10926 2007-03-19 14:34:47Z ana $
/*
==============================================================================
	Dokeos - elearning and course management software

	Copyright (c) 2004 Dokeos S.A.
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

	Contact: Dokeos, 181 rue Royale, B-1000 Brussels, Belgium, info@dokeos.com
==============================================================================
*/
/**
==============================================================================
*	@package dokeos.admin
==============================================================================
*/

include("../../main/inc/global.inc.php");
include_once(dirname(__FILE__).'\cs_database.lib.php');
$language_file = 'plugin_credits_system';
require_once (api_get_path(LIBRARY_PATH).'/formvalidator/FormValidator.class.php');


// Build the form
$different_payment_options = array( 1 => 'day',2 => 'week',3 => 'month',4 => 'year' );

$form = new FormValidator('payment_options');
$form->addElement('select', 'options', get_lang('DifferentOptions'), $different_payment_options);
$form->add_textfield('optionlength', get_lang('Length'),false, array ('size' => '6'));
$form->addElement('submit', null, get_lang('Ok'));
// Validate form
if( $form->validate())
{
	$add_option = $form -> exportValues();
	$form->freeze();
	$length = $add_option['optionlength'];
	$opt = $add_option['options'];
	$payment_options_table = Database :: get_main_table(CS_TABLE_PAYMENT_OPTION);
	$sql = "INSERT INTO $payment_options_table (name, amount) VALUES ($opt,$length)";
	api_sql_query($sql,__FILE__,__LINE__);
	header('Location: add_payment_option.php');
	exit ();
}

/*
-----------------------------------------------------------
	Header
-----------------------------------------------------------
*/
	
$tool_name = get_lang('AddPaymentOptions');
Display::display_header($tool_name);

// Display the form
$form->display();


/*
==============================================================================
		FOOTER
==============================================================================
*/

Display :: display_footer();
?>