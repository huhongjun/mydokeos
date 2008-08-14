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
*	This file is the script wich stores the payment information of a payment
*	made by PAYPAL.
* 
*	The script validates the payment data received openning a connection with PAYPAL,
*	then stores the data in the plugin database.
*
*	@package dokeos.plugin.credits_system
==============================================================================
*/
	
/*
==============================================================================
		COMUNICATION WITH THE PAYMENT METHOD PLATFORM
	
	Receive the payment data from the payment method platform.
	Verify and store data received.
==============================================================================
-----------------------------------------------------------
	Payment Data
-----------------------------------------------------------
*/ 
include_once("../../main/inc/global.inc.php");
include_once("../../main/inc/lib/mail.lib.inc.php");
require_once(dirname(__FILE__).'/inc/cs_functions.inc.php');

$data['first_name'] = $_POST['first_name'];
$data['last_name'] = $_POST['last_name'];
$data['payer_business_name'] = $_POST['payer_business_name'];
$data['payer_email'] = $_POST['payer_email'];
$data['payer_id'] = $_POST['payer_id'];
$data['business'] = $_POST['business'];
$data['reciver_email'] = $_POST['reciver_email'];
$data['reciver_id'] = $_POST['reciver_id'];
$data['street'] = $_POST['address_street'];
$data['city'] = $_POST['address_city'];
$data['state'] = $_POST['address_state'];
$data['zip'] = $_POST['address_zip'];
$data['country'] = $_POST['address_country'];
$data['payment_date'] = $_POST['payment_date'];
$data['txn_id'] = $_POST['txn_id'];
$data['currency'] = $_POST['mc_currency'];
$data['fee'] = $_POST['mc_fee'];
$data['tax'] = $_POST['tax'];

$credits = $_POST['quantity'];
$user_id = $_POST['item_number'];
$amount = $_POST['mc_gross'];

/*
-----------------------------------------------------------
	Verify and store payment data
-----------------------------------------------------------
*/ 


// read the post from PayPal system and add 'cmd'
$req = 'cmd=_notify-validate';
foreach ($_POST as $key => $value) {
$value = urlencode(stripslashes($value));
$req .= "&$key=$value";
$data_text .= $key.' = '.$value.'\r\n';
}
// post back to PayPal system to validate
$header = "POST /cgi-bin/webscr HTTP/1.0\r\n";
$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
$serverlist = gethostbynamel ("www.sandbox.paypal.com");
   foreach ($serverlist as $server) {
    $fp = fsockopen($server, 80, &$errno, &$errstr, 3);
    if ($fp != FALSE) {
     break;
    }
    }

if (!$fp) 
{
	// HTTP ERROR
	//Fill the error log file.
	$df = fopen(dirname(__FILE__)."/logs/paypal_log.txt","a");
	if($df)
	{
		fputs($df,date("d-m-y G:i:s").' => HTTP ERROR. '.$data_text);
		fclose($df);
	}
	//Mail to platform admin.
				$recipient_name = "Paypal Error Log";
				$recipient_email = "";
				$subject = "Paypal Error: HTTP ERROR.";
				$message = "Data:\r\n".$data_text;
				api_mail($recipient_name, $recipient_email, $subject, $message, "", "", "");	
} 
else 
{
	fputs ($fp, $header . $req);
	while (!feof($fp)) 
	{	
		$result = fgets ($fp, 1024);
		if (strcmp ($result, "VERIFIED") == 0) 
		{

			//check if transaction ID has been processed before
			$sql = 'SELECT txn_id FROM '.Database :: get_main_table(CS_TABLE_PAYPAL_PAYMENT_INFO).' WHERE txn_id = "'.$_POST['txn_id'].'"';
			$res = api_sql_query($sql, __FILE__, __LINE__);
			if (mysql_num_rows($res) == 0)
			{
			//Store the payment.
				cs_payment_method($data, 'paypal', $user_id, $credits, $amount);
			}
			else 
			{// TRANSACTION ALREADY EXISTS				
				//Fill the error log file.
				$df = fopen(dirname(__FILE__)."/logs/paypal_log.txt","a");
				if($df)
				{
					fputs($df,date("d-m-y G:i:s").' => Transaction '.$_POST['txn_id'].' already exists. '.$data_text);
					fclose($df);
				}	
				//Mail to platform admin.
				$recipient_name = "Paypal Error Log";
				$recipient_email = "";
				$subject = "Paypal Error: Transaction already exists.";
				$message = "Data:\r\n".$data_text;
				api_mail($recipient_name, $recipient_email, $subject, $message, "", "", "");		 
			}
		}
		else if (strcmp ($result, "INVALID") == 0) 
		{
			// LOG FOR MANUAL INVESTIGATION
			//Mail to platform admin.
				$recipient_name = "Paypal Error Log";
				$recipient_email = "";
				$subject = "Paypal Error: Invalid.";
				$message = "Data:\r\n".$data_text;
				api_mail($recipient_name, $recipient_email, $subject, $message, "", "", "");
			//Fill error log file.
			$message = "Data:\r\n".$data_text;
			$df = fopen(dirname(__FILE__)."/logs/paypal_log.txt","a");
			fputs($df,date("d-m-y G:i:s").' => INVALID. '.$data_text);
			fclose($df);
		}
	}
	fclose ($fp);
}
?>