<?php
/*
 * Created on 24/04/2007
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 include("../../main/inc/global.inc.php"); 
//Create form to submit payment data to the payment method platform.
	$table_settings_current = Database :: get_main_table(TABLE_MAIN_SETTINGS_CURRENT);
	$sql = "SELECT selected_value FROM $table_settings_current WHERE variable = 'cs_paypal' AND subkey = 'submit_server'";
	$result = api_sql_query($sql, __FILE__, __LINE__);
	$submit_server = mysql_fetch_array($result);
//header('Location: '.$submit_server[0]);
echo'<SCRIPT LANGUAGE="JavaScript"><!--
		document.location="'.$submit_server[0].'";
//--></script>';
?>
