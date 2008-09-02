<?php // $Id: buy_credits.php,v 1.0 2006/03/15 14:34:45 $
/*
==============================================================================
	Dokeos - elearning and course management software
	
	Copyright (c) 2007 E.U.I. Universidad Polit�cnica de Madrid (Spain)
	Copyright (c) 2004-2006 Dokeos S.A.
	
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
*	This file is the main 'buy credits' script.
*
*	In this script, User fills the payment form in with:
*		- Amount of credits to buy
*		- Payment method selected to pay with
*
*	The script fills the payment data form which will be sent automatically 
*	to the payment-method-selected platform.
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
include_once("../../main/inc/global.inc.php");
require_once ('./inc/cs_functions.inc.php');
require_once (api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php');

api_block_anonymous_users();

$form = new FormValidator('cs_buy','post');

if (isset($_POST['go_back']))
{
	//Save the go_back page.
	$form->addElement('hidden','go_back',$_POST['go_back']);
	
	//URL where payment method platform will return after the payment is made
	$return_url = $_POST['go_back'];
	
	//fill interbreadcrumb
	if (ereg('subscribe=1', $_POST['go_back']))
	{
		$interbreadcrumb[] = array ("url" => api_get_path(REL_CLARO_PATH).'auth/courses.php?action=subscribe&category='.$_GET['category'].'&up=', "name" => get_lang('CourseManagement'));
		$interbreadcrumb[] = array ("url" => $_POST['go_back'], "name" => get_lang('Subscribe'));
	}
	else
	{
		$interbreadcrumb[] = array ("url" => api_get_path(WEB_PATH).'user_portal.php', "name" => get_lang('MyCourses'));
		$interbreadcrumb[] = array ("url" => $_POST['go_back'], "name" => get_lang('RenewSubscription'));
	}
}
else
{
	$interbreadcrumb[] = array ("url" => api_get_path(PLUGIN_PATH).'my_credits.php', "name" => get_lang('CreditsSystem'));
	
	//Return URL for Paypal payement
	$return_url = '';
}

//Display Header
$tool_name = get_lang('BuyCredits'); 
Display::display_header($tool_name);
api_display_tool_title($tool_name);

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

/*
-----------------------------------------------------------
	Payment Form
-----------------------------------------------------------
*/
//If the User has the maximum amount of credits, he can not buy more.

if (cs_get_user_credits(api_get_user_id())< '999999.9')
{
	
	//Get Current Settings.
	$settings = cs_get_current_settings();

//Check the number of payment methods enabled by the admin.
	$payment_methods_num = 0;
	for($i=0; $i<count($settings['cs_payment_methods']); $i++){
		if ($settings['cs_payment_methods'][$i]['selected_value'] == 'true')
		{
			$payment_methods_num++;
		}
	}
//if there is not payment method enabled user cant buy credits just admin could add credits (Cash).
	if (!$payment_methods_num)
	{
		Display::display_error_message(get_lang('AccessDenied').'.<br />'.get_lang('NoPM').'.');
		Display::display_footer();
		exit;
	}
	$form->addElement('static', 'cost_per_credit',get_lang('CostPerCredit'),$settings['cs_cost_per_credit'][0]['selected_value'].' '.get_lang($settings['cs_currency'][0]['selected_value'].'Symbol'));
	
	//Add Amount of credits to buy.	
	$form->addElement('text', 'amount',get_lang('AmountCreditsToBuy'),'size="9" maxlenght="9"');
	
	
	//Add Available payment methods.
	$group = array ();
	
	for ($i=0; $i <= count($settings['cs_payment_methods']); $i++)
	{
		if ($settings['cs_payment_methods'][$i]['selected_value'] == 'true')
		{
			$group[] = $form->createElement('radio','payment_method','',$settings['cs_payment_methods'][$i]['subkeytext'],$settings['cs_payment_methods'][$i]['subkey']);
		}
	}
	$form->addGroup($group, 'payment_methods', get_lang('SelectPaymentMethod').':', '<br />', false);
	
	$form->addElement('submit','submit_buy',get_lang('Buy'));
	
	//Validation Rules
	$form->registerRule('valid_amount','regex','/^\d*\.{0,1}\d+$/');
	$form->addRule('amount',get_lang('UseNumericValuesPlz'),'valid_amount',null,'client');
	$form->addRule('amount',get_lang('EnterAnAmount'),'nonzero',null,'client');
	$form->addRule('amount',get_lang('EnterAnAmount'),'required',null,'client');
	$form->addRule('payment_methods',get_lang('SelectPaymentMethod'),'required',null,'client');
	
	
/*
---------------------------------------------------------------
		Validate form and send it to the payment method platform 
---------------------------------------------------------------
*/

	if ($form->validate())
	{

	//Save buy data.
		$buy_input = $form->exportValues();
		
		if ($buy_input['amount']+cs_get_user_credits(api_get_user_id())<='999999.9')
		{
		
		// Submit payment form to the payment method platform
			echo '<SCRIPT LANGUAGE="JavaScript"><!--
			setTimeout("document.cs_payment_method.submit()",5000);
			//--></SCRIPT>';	
		//Create form to submit payment data to the payment method platform.
			$table_settings_current = Database :: get_main_table(TABLE_MAIN_SETTINGS_CURRENT);
			$sql = "SELECT selected_value FROM $table_settings_current WHERE variable = 'cs_".$buy_input['payment_method']."' AND subkey = 'submit_server'";
			$result = api_sql_query($sql, __FILE__, __LINE__);
			$submit_server = mysql_fetch_array($result);
		
			$purchase_form = new FormValidator('cs_payment_method','post',$submit_server[0]);
		
		//Add payment method fields.
			$table_settings_current = Database :: get_main_table(TABLE_MAIN_SETTINGS_CURRENT);
			$sql = "SELECT * FROM $table_settings_current WHERE variable = 'cs_".$buy_input['payment_method']."' AND type IS NOT NULL";
			$result = api_sql_query($sql, __FILE__, __LINE__);
			
			while ($row = mysql_fetch_array($result))
			{
			//Fill payment form�s variable fields of the payment method.
				if (ereg("^GET:", $row['selected_value']))
				{
					list($get, $variable) = split(':',$row['selected_value']);
					switch ($variable)
					{
						case 'cs_cost_per_credit' :						
							$purchase_form->addElement($row['type'],$row['subkey'],$settings['cs_cost_per_credit'][0]['selected_value']);
							break;
						case 'cs_currency' :					
							$purchase_form->addElement($row['type'],$row['subkey'],$settings['cs_currency'][0]['selected_value']);						
							break;
						case 'amount' :
							$purchase_form->addElement($row['type'],$row['subkey'],$buy_input['amount']);
							break;
						case 'language' :
							$purchase_form->addElement($row['type'],$row['subkey'],Database::get_language_isocode($language_interface));
							break;
					//Information about the return URL
						case 'go_back' :
							$purchase_form->addElement($row['type'],$row['subkey'],$return_url);
							break;
						case 'user_id' :
							$purchase_form->addElement($row['type'],$row['subkey'],api_get_user_id());
					}
				}
			//Fixed fields of the payment method.
				else
				{
					$purchase_form->addElement($row['type'],$row['subkey'],$row['selected_value']);
				}
			}
	
			$purchase_form->display();
		
			Display::display_confirmation_message(get_lang('Redirected').'<b>'.$buy_input['payment_method'].'</b>');
		}
		else
		{
			Display::display_normal_message(get_lang('TooManyCredits'));
			$form->display();
		}
	}
	else
	{
	//Display payment form.
		$form->display();	
	}
}
else
{
	Display::display_normal_message(get_lang('MaxAmountOfCredits'));
	
}
/*
==============================================================================
		FOOTER 
==============================================================================
*/ 
Display::display_footer();
?>