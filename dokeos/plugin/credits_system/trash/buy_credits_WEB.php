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
//$language_file = 'plugin_credits_system';
//include("../../main/inc/global.inc.php"); 
//require_once (api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php');

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

// read the post from PayPal system and add 'cmd'
$req = 'cmd=_notify-synch';

$tx_token = $_GET['tx'];

$auth_token = "NjRoM4Xrjerw63574NMU9LkbhonBEx6UYSFA9-KKNfR0uBA79pMhf0713ty";

$req .= "&tx=$tx_token&at=$auth_token";


// post back to PayPal system to validate
$header .= "POST /cgi-bin/webscr HTTP/1.0\r\n";
$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
//$fp = fsockopen ('www.sandbox.paypal.com', 80, $errno, $errstr, 30);
// If possible, securely post back to paypal using HTTPS
// Your PHP server will need to be SSL enabled
 
$serverlist = gethostbynamel ("www.sandbox.paypal.com");
   foreach ($serverlist as $server) {
    $fp = fsockopen($server, 80, &$errno, &$errstr, 3);
    if ($fp != FALSE) {
     break;
    }
   }
  if( !$fp )
  {
   //$this->error_out("PHP fsockopen() error: " . $errstr , "");
   echo 'error<br />';
  }

if (!$fp) {
// HTTP ERROR
echo 'error<br />';
} else {
fputs ($fp, $header . $req);
// read the body data
$res = '';
$headerdone = false;
while (!feof($fp)) {
$line = fgets ($fp, 1024);
if (strcmp($line, "\r\n") == 0) {
// read the header
$headerdone = true;
}
else if ($headerdone)
{
// header has been read. now read the contents
$res .= $line;
}
}

// parse the data
$lines = explode("\n", $res);
$keyarray = array();
if (strcmp ($lines[0], "SUCCESS") == 0) 
{
	for ($i=1; $i<count($lines);$i++)
	{
		list($key,$val) = explode("=", $lines[$i]);
		$keyarray[urldecode($key)] = urldecode($val);
	}	
// check the payment_status is Completed
// check that txn_id has not been previously processed
// check that receiver_email is your Primary PayPal email
// check that payment_amount/payment_currency are correct
// process payment
	$first_name = $keyarray['first_name'];
	$last_name = $keyarray['last_name'];
	$item_name = $keyarray['item_name'];
	$user_id = $keyarray['item_number'];
	$credits = $keyarray['quantity'];
	$amount = $keyarray['mc_gross'];
	$go_back = $keyarray['custom'];
	
	fclose ($fp);
	
	echo '<SCRIPT LANGUAGE="JavaScript"><!--
	setTimeout("document.pay.submit()",1);
	//--></SCRIPT>'; 
	
	
	//echo'<script type="text/javascript">document.location.href="http://localhost/dokeos/plugin/credits_system/payment_done.php?go_back='.$go_back.'&user_id='.$user_id.'&first_name='.$first_name.'&last_name='.$last_name.'&quantity='.$quantity.'&amount='.$amount.'&'.$extra_info.'";</script>';
	echo '<form name="pay" action="http://localhost/plugin/credits_system/payment_done.php" method="post">';
	echo '<input type="hidden" name="first_name" value="'.$first_name.'">';
	echo '<input type="hidden" name="last_name" value="'.$last_name.'">';
	echo '<input type="hidden" name="user_id" value="'.$user_id.'">';
	echo '<input type="hidden" name="credits" value="'.$credits.'">';
	echo '<input type="hidden" name="go_back" value="'.$go_back.'">';
	echo '</form>';
	
}	
else if (strcmp ($lines[0], "FAIL") == 0) 
	{
		// log for manual investigation
		fclose ($fp);
	}

}


/*
==============================================================================
		FOOTER 
==============================================================================
*/ 
//Display::display_footer();
?>