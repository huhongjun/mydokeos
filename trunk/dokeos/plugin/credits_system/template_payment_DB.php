<?php // $Id: template_payment_DB.php,v 1.2 2006/03/15 14:34:45 nick $
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

//Here will appear all the possible variables received from the payment method platform

//$data['field_name']= 'field_value';
//$data['field_name2']= 'field_value2';
//$data['field_name3']= 'field_value3';

/*
-----------------------------------------------------------
	Verify data
-----------------------------------------------------------
*/ 

//Validate data recived

/*
-----------------------------------------------------------
	Store data
-----------------------------------------------------------
*/ 

// Store the validated data in the plugin database
// Use the function below to store data in both generic payment data table
// and specific payment method data table

//$payment_method = xxxxxx;

cs_payment_method($data, $payment_method, $user_id, $credits);


?>