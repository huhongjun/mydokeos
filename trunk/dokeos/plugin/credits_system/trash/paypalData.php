<?php
/*
 * Created on 13/04/2007
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 // read the post from PayPal system and add 'cmd'
	$req = 'cmd=_notify-validate';
	foreach ($_POST as $key => $value) 
	{
		$value = urlencode(stripslashes($value));
		$req .= "&$key=$value";
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


// assign posted variables to local variables
	$item_name = $_POST['item_name'];
	$business = $_POST['business'];
	$item_number = $_POST['item_number'];
	$payment_status = $_POST['payment_status'];
	$mc_gross = $_POST['mc_gross'];
	$payment_currency = $_POST['mc_currency'];
	$txn_id = $_POST['txn_id'];
	$receiver_email = $_POST['receiver_email'];
	$receiver_id = $_POST['receiver_id'];
	$quantity = $_POST['quantity'];
	$payment_date = $_POST['payment_date'];
	$first_name = $_POST['first_name'];
	$last_name = $_POST['last_name'];
	$payment_type = $_POST['payment_type'];
	$payment_status = $_POST['payment_status'];
	$payment_gross = $_POST['payment_gross'];
	$payment_fee = $_POST['payment_fee'];
	$payer_email = $_POST['payer_email'];
	$txn_type = $_POST['txn_type'];
	$payer_status = $_POST['payer_status'];
	$address_street = $_POST['address_street'];
	$address_city = $_POST['address_city'];
	$address_state = $_POST['address_state'];
	$address_zip = $_POST['address_zip'];
	$address_country = $_POST['address_country'];
	$address_status = $_POST['address_status'];
	$item_number = $_POST['item_number'];
	$tax = $_POST['tax'];
	$custom = $_POST['custom'];
	$notify_version = $_POST['notify_version'];
	$verify_sign = $_POST['verify_sign'];
	$payer_business_name = $_POST['payer_business_name'];
	$payer_id =$_POST['payer_id'];
	$mc_currency = $_POST['mc_currency'];
	$mc_fee = $_POST['mc_fee'];
	$exchange_rate = $_POST['exchange_rate'];
	$settle_currency  = $_POST['settle_currency'];
	$parent_txn_id  = $_POST['parent_txn_id'];
	
//DB connect creds and email 
	//$notify_email =  "dokeosproject@gmail.com";         //email address to which debug emails are sent to
	//$DB_Server = "mysql1.100ws.com"; //your MySQL Server
	//$DB_Username = "zelweb_dokeos"; //your MySQL User Name
	//$DB_Password = "dokeos"; //your MySQL Password
	//$DB_DBName = "zelweb_dokeos"; //your MySQL Database Name

	if (!$fp) 
	{
	// HTTP ERROR
	} 
	else 
	{
		fputs ($fp, $header . $req);
		while (!feof($fp)) 
		{
			echo $fp.'<br />';
			$res = fgets ($fp, 1024);
			if (strcmp ($res, "VERIFIED") == 0) 
			{
				echo $res.'<br />';
			//create MySQL connection
				//$Connect = @mysql_connect($DB_Server, $DB_Username, $DB_Password)
				//or die("Couldn't connect to MySQL:<br>" . mysql_error() . "<br>" . mysql_errno());


			//select database
				//$Db = @mysql_select_db($DB_DBName, $Connect)
				//or die("Couldn't select database:<br>" . mysql_error(). "<br>" . mysql_errno());


				//$fecha = date("m")."/".date("d")."/".date("Y");
				//$fecha = date("Y").date("m").date("d");

			//check if transaction ID has been processed before
				//$checkquery = "select txnid from paypal_payment_info where txnid='".$txn_id."'";
				//$sihay = mysql_query($checkquery) or die("Duplicate txn id check query failed:<br>" . mysql_error() . "<br>" . mysql_errno());
				//$nm = mysql_num_rows($sihay);
				//if ($nm == 0)
				//{
				//execute query
					//mail($notify_email, "VERIFIED IPN", "$res\n $req\n $strQuery\n $struery\n  $strQuery2");
					//echo'<script type="text/javascript">document.location.href="http://localhost/dokeos/plugin/credits_system/update_payment_DB.php?paymentstatus='.$payment_status.'&buyer_email='.$payer_email.'&firstname='.$first_name.'&lastname='.$last_name.'&street='.$address_street.'&city='.$address_city.'&state='.$address_state.'&zipcode='.$address_zip.'&country='.$address_country.'&mc_gross='.$mc_gross.'&mc_fee='.$mc_fee.'&itemnumber='.$item_number.'&quantity='.$quantity.'&paymentdate='.$payment_date.'&txnid='.$txn_id.'&tax='.$tax.'";</script>';
				
					//$strQuery = "insert into paypal_payment_info(paymentstatus,buyer_email,firstname,lastname,street,city,state,zipcode,country,mc_gross,mc_fee,itemnumber,itemname,os0,on0,os1,on1,quantity,memo,paymenttype,paymentdate,txnid,pendingreason,reasoncode,tax,datecreation) values ('".$payment_status."','".$payer_email."','".$first_name."','".$last_name."','".$address_street."','".$address_city."','".$address_state."','".$address_zip."','".$address_country."','".$mc_gross."','".$mc_fee."','".$item_number."','".$item_name."','".$option_name1."','".$option_selection1."','".$option_name2."','".$option_selection2."','".$quantity."','".$memo."','".$payment_type."','".$payment_date."','".$txn_id."','".$pending_reason."','".$reason_code."','".$tax."','".$fecha."')";
     				//$result = mysql_query("insert into paypal_payment_info(paymentstatus,buyer_email,firstname,lastname,street,city,state,zipcode,country,mc_gross,mc_fee,itemnumber,itemname,os0,on0,os1,on1,quantity,memo,paymenttype,paymentdate,txnid,pendingreason,reasoncode,tax,datecreation) values ('".$payment_status."','".$payer_email."','".$first_name."','".$last_name."','".$address_street."','".$address_city."','".$address_state."','".$address_zip."','".$address_country."','".$mc_gross."','".$mc_fee."','".$item_number."','".$item_name."','".$option_name1."','".$option_selection1."','".$option_name2."','".$option_selection2."','".$quantity."','".$memo."','".$payment_type."','".$payment_date."','".$txn_id."','".$pending_reason."','".$reason_code."','".$tax."','".$fecha."')") or die("Default - paypal_payment_info, Query failed:<br>" . mysql_error() . "<br>" . mysql_errno());
    				//}
		    	// send an email in any case
				//}
				//else 
				//{
				// send an email
				//	mail($notify_email, "VERIFIED DUPLICATED TRANSACTION", "$res\n $req \n $strQuery\n $struery\n  $strQuery2");
				
				

			}
			else echo 'Fallo';
		//else if (strcmp ($res, "INVALID") == 0) 
			//	{
				//log for manual investigation

				//	mail($notify_email, "INVALID IPN", "$res\n $req");
				//}
		}
	fclose ($fp);
	}

?>
