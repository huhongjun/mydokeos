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
		COMUNICATION WITH THE PAYMENT METHOD PLATFORM
	
	Receive the payment data from the payment method platform.
	Verify and store data received.
==============================================================================
-----------------------------------------------------------
	Payment Data
-----------------------------------------------------------
*/ 

$data['business'] = $_POST['business'];
$data['mc_gross'] = $_POST['mc_gross'];
$data['mc_currency'] = $_POST['mc_currency'];
$data['txn_id'] = $_POST['txn_id'];
$data['payment_date'] = $_POST['payment_date'];
$data['first_name'] = $_POST['first_name'];
$data['last_name'] = $_POST['last_name'];
$data['payer_email'] = $_POST['payer_email'];
$data['address_street'] = $_POST['address_street'];
$data['address_city'] = $_POST['address_city'];
$data['address_state'] = $_POST['address_state'];
$data['address_zip'] = $_POST['address_zip'];
$data['address_country'] = $_POST['address_country'];
$data['tax'] = $_POST['tax'];
$data['payer_id'] = $_POST['payer_id'];
$data['mc_fee'] = $_POST['mc_fee'];
$data['payer_business_name'] = $_POST['payer_business_name'];

$credits = $_POST['quantity'];
$user_id = $_POST['item_number'];

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
}
// post back to PayPal system to validate
$header = "POST /cgi-bin/webscr HTTP/1.0\r\n";
$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
$fp = fsockopen ('www.sandbox.paypal.com', 80, $errno, $errstr, 30);

if (!$fp) 
{
	// HTTP ERROR
} 
else 
{
	fputs ($fp, $header . $req);
	while (!feof($fp)) 
	{
		$res = fgets ($fp, 1024);
		if (strcmp ($res, "VERIFIED") == 0) 
		{
			//check if transaction ID has been processed before
			$error = cs_paypal_transaction_exists($txn_id);
			if (!$error)
			{
				cs_payment_method($data, 'paypal', $user_id, $credits);
			}
			else 
			{
				// TRANSACTION ALREADY EXISTS
			}
		}
		else if (strcmp ($res, "INVALID") == 0) 
		{
			// LOG FOR MANUAL INVESTIGATION
		}
	}
	fclose ($fp);
}
?>