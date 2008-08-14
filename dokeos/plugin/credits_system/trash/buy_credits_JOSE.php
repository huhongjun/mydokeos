<?php // $Id: my_credits.php,v 1.2 2006/03/15 14:34:45 pcool Exp $
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
$language_file = 'plugin_credits_system';
include("../../main/inc/global.inc.php"); 

api_block_anonymous_users();

/*
-----------------------------------------------------------
	Libraries
-----------------------------------------------------------
*/ 
//the main_api.lib.php, database.lib.php and display.lib.php
//libraries are included by default
require_once (api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php');
	
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
$interbreadcrumb[] = array ("url" => 'my_credits.php', "name" => get_lang('CreditsSystem'));

if ($_GET['action'] != 'submitted')
{
	$nameTools = get_lang('BuyCredits');
	Display::display_header($nameTools);	
	
	$tool_name = "Buy Credits";
	api_display_tool_title($tool_name);	
}






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


$user_id = api_get_user_id();


$form = new FormValidator('creditsAmount','post','?action=submitted');
$form -> registerRule('valid_amount','regex','/^\d*\.{0,1}\d+$/');

$group = array();
$group[] = $form->createElement('static','','',get_lang('Buy').' ');
$group[] = $form->createElement('text','creditAmount',null,'size="8"');
$group[] = $form->createElement('static','','',get_lang('credits').' ');

$form -> addGroup($group, 'payment', get_lang('CreditsAndPayMethods').': ');
$form -> addGroupRule('payment',array(array(),array(array(get_lang('CreditAmountRequired'),'required',null,'client'),array(get_lang('CreditAmountPositive'),'valid_amount',null,'client'),array(get_lang('CreditAmountRequired'),'nonzero',null,'client'))));

$form -> addElement('radio', 'payMethod', null, get_lang('PAYMENT_METHOD_PAYPAL'), PAYMENT_METHOD_PAYPAL);
$form -> addElement('radio', 'payMethod', null, get_lang('PAYMENT_METHOD_VISA'), PAYMENT_METHOD_VISA);
$form -> addElement('radio', 'payMethod', null, get_lang('PAYMENT_METHOD_MASTERCARD'), PAYMENT_METHOD_MASTERCARD);

$form->addElement('submit','submit',get_lang('Ok'));

$values['payMethod'] = PAYMENT_METHOD_PAYPAL;
$values['payment'] = array('creditAmount'=>'0');
$form->setDefaults($values);
	


if ($form->validate())
{
	$payment = $form->exportValues();
	
	$interbreadcrumb[] = array ("url" => 'buy_credits.php', "name" => get_lang('BuyCredits'));
	$nameTools = get_lang($payment['payMethod']);
	Display::display_header($nameTools);	
	
	$tool_name = "Confirm Choice";
	api_display_tool_title($tool_name);
		
	$default_option = cs_get_current_settings();
	
	$cost_per_credit = $default_option['cs_cost_per_credit'][0]['selected_value'];
	
	$total_amount = $cost_per_credit * $payment['payment']['creditAmount'];
		
	Display::display_normal_message('You have chosen to buy '.$payment['payment']['creditAmount'].' credits by '.get_lang($payment['payMethod']).'.<br />The total amount to pay is: '.$total_amount);
	
	$validated_form = new FormValidator('BuyCredits','post','https://www.sandbox.paypal.com/cgi-bin/webscr');
	
/*	$table_pay_meth_settings = Database :: get_main_table(CS_TABLE_PAYMENT_METHODS_SETTINGS);
	$sql = "SELECT * FROM ".$table_pay_meth_settings." WHERE method_name ='".$payment['payMethod']."' ORDER BY field_type";
	$res = api_sql_query($sql, __FILE__, __LINE__);
	while ($option = mysql_fetch_array($res))
	{
		$validated_form. addElement($option[field_type],$option[field_name], $option[value]);
	}*/
	
	
	
	//$validated_form ->addElement('submit','pay',get_lang('ok'));
	$validated_form -> addElement('hidden','cmd','_xclick');
	$validated_form -> addElement('hidden','business','josehueso@alumnos.upm.es');
	$validated_form -> addElement('hidden','item_name','Credits');
	$validated_form -> addElement('hidden','item_number',$_SESSION['_user']['user_id']);
	$validated_form -> addElement('hidden','amount',$cost_per_credit);
	$validated_form -> addElement('hidden','no_shipping','2');
	$validated_form -> addElement('hidden','no_note','1');
	$validated_form -> addElement('hidden','quantity',$payment['payment']['creditAmount']);
	$validated_form -> addElement('hidden','currency_code','EUR');
	$validated_form -> addElement('hidden','lc','en');
	$validated_form -> addElement('hidden','bn','paypal_button');
	$validated_form -> addElement('image','paypal_button','img/buy_paypal.gif');
	$validated_form -> display();
}

if (!isset($_GET['action']))
{
	/*echo'
	<form name="input" action="https://www.sandbox.paypal.com/cgi-bin/webscr">
	<input type="hidden" name="cmd" value="_xclick">
	<input type="hidden" name="business" value="josehueso@alumnos.upm.es">
	<input type="hidden" name="item_name" value="Credits">
	<input type="hidden" name="item_number" value="'.$user_id.'">
	<input type="hidden" name="quantity" value="2">
	<input type="hidden" name="amount" value="5.00">
	<input type="hidden" name="no_shipping" value="2">
	<input type="hidden" name="no_note" value="1">
	<input type="hidden" name="currency_code" value="EUR">
	<input type="hidden" name="lc" value="nl">
	<input type="hidden" name="bn" value="PP-BuyNowBF">
	<input type="image" src="../../main/img/PaymentMethods/BuyNowButton.gif" border="0" name="submit" alt="Make payments with PayPal - its fast, free and secure!">
	<img alt="" border="0" src="https://www.sandbox.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
	</form>';*/
	$form->display();
	

//echo 'submitted: '.$_GET['submitted'].'<br />';
}

else if ($_GET['action'] == 'paid')  
	{

		$firstname = $_GET['first_name'];
		$lastname = $_GET['last_name'];
		//$itemname = $_GET['item_name'];
		$user_id = $_GET['user_id'];
		$quantity = $_GET['quantity'];
		$amount = $_GET['amount'];
	
		echo ("<p><h3>Thank you for your purchase!</h3></p>");
		
		echo ("<b>Payment Details</b><br>\n");
		echo ("<li>Name: $firstname $lastname</li>\n");
		echo ("<li>Item: $itemname</li>\n");
		echo ("<li>You bought $quantity credits for $amount euros</li>\n");
		echo ("<li>Your User ID is: $user_id</li>\n");
	}
	else echo $_GET['action'];
/*
==============================================================================
		FOOTER 
==============================================================================
*/ 
Display::display_footer();
?>