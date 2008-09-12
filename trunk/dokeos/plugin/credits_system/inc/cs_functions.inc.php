<?php
$language_file = "plugin_credits_system";
include_once(dirname(__FILE__).'/cs_database.lib.php');

/**
 * Get current credits system settings. If variable is provided just get that setting.
 * @author Borja Nuï¿?ez
 * @param integer $variable - The name of the variable.
 * @return array $option - All info about every setting with scope="cs" :
 * 				If there is no $variable or that variable have more than one row:
 * 				 ['variable'][num] = array of values for the same variable. Values:
 * 				 				  ['id']
 * 				 				  ['subkey']
 * 				 				  ['type']
 * 				 				  ['selected_value']
 * 				 				  ['title']
 * 				 				  ['comment']
 * 				 				  ['scope']
 * 				 				  ['subkeytext']
 * 				If there is $variable and that variable have just one row:
 * 				 selected_value = the selected value of the variable.
 */
function cs_get_current_settings($variable=0)
{
	$table_settings_current = Database :: get_main_table(TABLE_MAIN_SETTINGS_CURRENT);
	$sql = 'SELECT * FROM '.$table_settings_current.' WHERE scope="cs"';
	if($variable)
	{
		$sql .= ' AND variable = "'.$variable.'"';
	}

	$res = api_sql_query($sql, __FILE__, __LINE__) or die(mysql_error());
	$num_rows = mysql_num_rows($res);

	if($num_rows > 1 )
	{
		$rows = api_store_result($res);

		for($i=0; $i < sizeof($rows); $i++)
		{
			$options[$rows[$i]['variable']][] = array("id" => $rows[$i]['id'], "subkey" => $rows[$i]['subkey'], "type" => $rows[$i]['type'], "selected_value" => $rows[$i]['selected_value'], "title" => $rows[$i]['title'], "comment" => $rows[$i]['comment'], "scope" => $rows[$i]['scope'], "subkeytext" => $rows[$i]['subkeytext']);
		}
		return $options;
	}
	elseif($num_rows)
	{
		$rows = api_store_result($res);
		return $rows[0]['selected_value'];
	}
	else
	{
		return false;
	}
}

/**
 * Check:
 * 		  - if Credits System Tables are set and if not, it set up the tables.
 * 		  - if initial settings are set and if not, it set up those rows.
 * @author Borja Nuï¿?ez
 */
function cs_check_database()
{
	//Check current settings fields.
	$current_settings = cs_get_current_settings();
	
	//Get the number of payment methods enabled
	$payment_methods_num = 0;
	for ($i=0; $i < count($current_settings['cs_payment_methods']); $i++)
	{
		if ($current_settings['cs_payment_methods'][$i]['selected_value'] == 'true')
		{
			$payment_methods_num++;
		}
	}
	
	//Check Tables.
	//Get current credits system tables.
	$sql = 'SHOW TABLES FROM '.Database :: get_main_database().' LIKE "cs_%"';
	$tables = api_sql_query($sql, __FILE__, __LINE__) or die(mysql_error());
	
	if( mysql_num_rows($tables) != 6 + $payment_methods_num)
	{
		$sql_script = file_get_contents(dirname(__FILE__).'/../DB_scripts/DB_tables');
		$query = split(';', $sql_script);
		for ($i = 0; $i+1 < count($query); $i++)
		{
			$res = api_sql_query($query[$i], __FILE__, __LINE__) or die(mysql_error());
		}
	}
	
	//Check Settings.
	if ( count($current_settings) != 7 )
	{//some fields are missing. set as default.
		$sql_script = file_get_contents(dirname(__FILE__).'/../DB_scripts/DB_current_settings');
		$sql_script .= file_get_contents(dirname(__FILE__).'/../DB_scripts/DB_options_settings');
		$query = split(';', $sql_script);
		for ($i = 0; $i+1 < count($query); $i++)
		{
			$res = api_sql_query($query[$i], __FILE__, __LINE__) or die(mysql_error());
		}
	}
}

/**
 *	Update subscription table when a payment option of a course is going
 *	to get changed.
 *	Also save the previous values of the payment option on options_history table.
 * @author Borja Nuï¿?ez
 * @param integer $option_id - Id of the payment option that is going to change.
 * @param integer $code - Id of the course that have the payment option that is going
 * 							to change.
 */
function cs_update_course_options_history($option_id=false, $code=false)
{
	if($option_id && $code)
	{
	//Get payment option info.
	$table_payment_option = Database :: get_main_table(CS_TABLE_PAYMENT_OPTION);
	$table_course_credits = Database :: get_main_table(CS_TABLE_COURSE_CREDITS);
	$sql = "SELECT amount, name, credits FROM $table_course_credits, $table_payment_option WHERE $table_course_credits.code = '$code' AND $table_course_credits.option_id=$option_id AND $table_payment_option.option_id=$option_id";
	$res = api_sql_query($sql, __FILE__, __LINE__) or die(mysql_error());
	$option = mysql_fetch_array($res);

	//Someone paid for this payment option before it changes so it will be stored on cs_options_history table
	$table_options_history = Database :: get_main_table(CS_TABLE_OPTIONS_HISTORY);
	$sql = "INSERT INTO ".$table_options_history." (amount, name, credits) VALUES (".$option['amount'].",'".$option['name']."',".$option['credits'].")";
	$res = api_sql_query($sql, __FILE__, __LINE__);
	$options_history_id = mysql_insert_id();
	
	
	//Set the option_id of the subscription affected to null and link it to the correct row on cs_options_history (options_history_id)
	$table_course_credits_rel_user_credits = Database :: get_main_table(CS_TABLE_COURSE_CREDITS_REL_USER_CREDITS);
	$sql = "UPDATE ".$table_course_credits_rel_user_credits." SET option_id = NULL, options_history_id =".$options_history_id." WHERE option_id=".$option_id." AND code ='".$code."'";
	$res = api_sql_query($sql, __FILE__, __LINE__);
	}
	else
	{
		return false;
	}
}

/**
 *	Update subscription table when a payment option is going
 *	to get changed for the entire platform.
 *	Also save the previous values of the payment option on options_history table.
 * @author Borja Nuï¿?ez
 * @param integer $option_id - Id of the payment option that is going to change.
 */
function cs_update_platform_options_history($option_id=false)
{
	if ($option_id)
	{
		//Get payment option info.
		$table_payment_option = Database :: get_main_table(CS_TABLE_PAYMENT_OPTION);
		$sql = "SELECT amount, name FROM $table_payment_option WHERE $table_payment_option.option_id=$option_id";
		$res = api_sql_query($sql, __FILE__, __LINE__) or die(mysql_error());
		$option = mysql_fetch_array($res);	

	
		//Get courses with affected Subscriptions. (Code and the credits for this option)
		$table_course_credits_rel_user_credits = Database :: get_main_table(CS_TABLE_COURSE_CREDITS_REL_USER_CREDITS);
		$table_course_credits = Database :: get_main_table(CS_TABLE_COURSE_CREDITS);
		$sql="SELECT credits, $table_course_credits_rel_user_credits.code
				FROM $table_course_credits_rel_user_credits , $table_course_credits
				WHERE $table_course_credits_rel_user_credits.option_id =$option_id
				AND $table_course_credits.option_id =$option_id
				GROUP BY code
				ORDER BY $table_course_credits.credits ASC";

		$affected_courses = api_sql_query($sql, __FILE__, __LINE__) or die(mysql_error());
		$last_credits = -1;
		while($row = mysql_fetch_array($affected_courses))
		{
			if ($last_credits != $row['credits'])
			{
				$last_credits = $row['credits'];
				
				//Someone paid for this payment option before it changes so it will be stored on cs_options_history table
				$table_options_history = Database :: get_main_table(CS_TABLE_OPTIONS_HISTORY);
				$sql = "INSERT INTO ".$table_options_history." (amount, name, credits) VALUES (".$option['amount'].",'".$option['name']."',".$row['credits'].")";			
				$res = api_sql_query($sql, __FILE__, __LINE__);
				$options_history_id = mysql_insert_id();
			}
			//Set the option_id of the subscription affected to null and link it to the correct row on cs_options_history (options_history_id)
			$table_course_credits_rel_user_credits = Database :: get_main_table(CS_TABLE_COURSE_CREDITS_REL_USER_CREDITS);
			$sql = "UPDATE ".$table_course_credits_rel_user_credits." SET option_id = NULL, options_history_id =".$options_history_id." WHERE option_id=".$option_id." AND code ='".$row['code']."'";
			$res = api_sql_query($sql, __FILE__, __LINE__);
		}
	}
	else
	{
		return false;
	}
}
	
/**
 * Delete a course payment option. If there is no option_id, all payment options are deleted for this course.
 * @author Jose C. Hueso Vazquez
 * @param integer $code - Id of the Course.
 * @param integer $option_id - Optional, Id of the payment option that will be deleted.
 * @return boolean - True if all was ok. False if not.  
 */
function cs_delete_course_payment_option($code,$option_id = -1)
{
	$table_course_credits = Database :: get_main_table(CS_TABLE_COURSE_CREDITS);
	$affected_rows = 0;
	
	if ($option_id == -1)
	{
		$sql_select = 'SELECT option_id FROM '.$table_course_credits.' WHERE code="'.$code.'"';
		$res_select = api_sql_query($sql_select, __FILE__, __LINE__) or die(mysql_error());

		while($row = mysql_fetch_array($res_select))
		{
			cs_update_course_options_history($row['option_id'], $code);
			$sql_delete = 'DELETE FROM '.$table_course_credits.' WHERE code="'.$code.'" AND option_id='.$row['option_id'];
			$res_delete = api_sql_query($sql_delete, __FILE__, __LINE__) or die(mysql_error());;
			$affected_rows += mysql_affected_rows();
		}
	}
	else
	{
		cs_update_course_options_history($option_id, $code);
		$sql_delete = 'DELETE FROM '.$table_course_credits.' WHERE code="'.$code.'" AND option_id='.$option_id;
		$res_delete = api_sql_query($sql_delete, __FILE__, __LINE__) or die(mysql_error());
		$affected_rows = mysql_affected_rows();
	}
	return ($affected_rows>0);
}
/**
 * Set a course payment option 
 * @author Jose C. Hueso Vazquez
 * @param integer $code - Id of the Course.
 * @param integer $credits - Number of credits for this payment option.
 * @param integer $option_id - Id of the payment option that will be set.
 * @return boolean - True if all was ok. False if not.  
 */
function cs_set_course_payment_option($code,$credits,$option_id)
{
	$table_course_credits = Database :: get_main_table(CS_TABLE_COURSE_CREDITS);
	$sql = 'INSERT INTO '.$table_course_credits.' (code,option_id,credits) VALUES ("'.$code.'", "'.$option_id.'","'.$credits.'")';
	$res = api_sql_query($sql, __FILE__, __LINE__) or die(mysql_error());
	return (mysql_affected_rows()==1?true:false);
}

/**
 * Check the payment options number of a course.
 * @author Jose C. Hueso Vazquez
 * @param integer $code - Id of the Course.
 * @return integer - Number of payment options of the course
 */
function cs_course_payment_options_number($code)
{
	$table_course_credits = Database :: get_main_table(CS_TABLE_COURSE_CREDITS);
	$sql = 'SELECT count(code) as num FROM '.$table_course_credits.' WHERE code="'.$code.'"'; 

	$res = api_sql_query($sql, __FILE__, __LINE__) or die(mysql_error());
	$rows = api_store_result($res);
	return $rows[0]['num'];
}

/**
 * Get the payment options of the course.
 * @author Borja Nuï¿?ez
 * @param integer $code - Id of the Course.
 * @return array $option - All info about every payment option of the course:
 * 				 ['option_id']
 * 				 ['name'] = day | week | month | year
 * 				 ['amount'] = amount of payment option (3 days, 6 months...)
 * 				 ['credits'] = amount of credits of this course payment option 
 */
function cs_get_course_payment_options($code)
{
	$table_payment_option = Database :: get_main_table(CS_TABLE_PAYMENT_OPTION);
	$table_course_credits = Database :: get_main_table(CS_TABLE_COURSE_CREDITS);
	$sql = 'SELECT '.$table_course_credits.'.option_id, '.$table_payment_option.'.name, '.$table_payment_option.'.amount, '.$table_course_credits.'.credits  FROM '.$table_payment_option.', '.$table_course_credits.' WHERE code="'.$code.'" AND '.$table_course_credits.'.option_id = '.$table_payment_option.'.option_id ORDER BY '.$table_payment_option.'.name, '.$table_payment_option.'.amount ASC';
	$res = api_sql_query($sql, __FILE__, __LINE__) or die(mysql_error());

	if(mysql_num_rows($res) > 0)
	{
		$rows = api_store_result($res);
					
		$option = '(';
		for($i=0; $i < sizeof($rows); $i++)
		{
			if($rows[$i]['name'] == 'day'){
				$rows[$i]['name'] = get_lang("day");
			}
			if($rows[$i]['name'] == 'week'){
				$rows[$i]['name'] = get_lang("week");
			}
			if($rows[$i]['name'] == 'month'){
				$rows[$i]['name'] = get_lang("month");
			}
			if($rows[$i]['name'] == 'year'){
				$rows[$i]['name'] = get_lang("year");
			}
			
			$option .= '"'.$rows[$i]['option_id'].'" => array("amount" => "'.$rows[$i]['amount'].'", "name" => "'.$rows[$i]['name'].'", "credits" => "'.$rows[$i]['credits'].'")';
			if ($i+1 < sizeof($rows))
			{
				$option .= ', ';
			}
		}
		$option .= ')';
		
		//built array: $option = array(option_id => array(amount, credits))
		eval( '$option = array'.$option.';' );
		return $option;
	}
	else
	{
		return false;
	}
}

/**
 * Get the possible payment options of a course: 
 * All the payment options except those that the course already have.
 * @author Jose C. Hueso Vazquez
 * @param integer $code - Id of the Course.
 * @return array $option - Info of all possible payment options for a course.
 * 				 ['option_id']
 * 				 ['name'] = day | week | month | year
 * 				 ['amount'] = amount of payment option (3 days, 6 months...)
 */
function cs_get_course_possible_payment_options($code)
{
	//$currentOptions = cs_get_course_payment_options($code);
	
	$table_payment_option = Database :: get_main_table(CS_TABLE_PAYMENT_OPTION);
	$table_course_credits = Database :: get_main_table(CS_TABLE_COURSE_CREDITS);
	$sql = 'SELECT '.$table_payment_option.'.option_id, '.$table_payment_option.'.name, '.$table_payment_option.'.amount FROM '.$table_payment_option.' WHERE '.$table_payment_option.'.option_id NOT IN (SELECT '.$table_course_credits.'.option_id FROM '.$table_course_credits.' WHERE code = "'.$code.'")';
	$res = api_sql_query($sql, __FILE__, __LINE__) or die(mysql_error());

	if(mysql_num_rows($res) > 0)
	{
		$rows = api_store_result($res);
		for($i=0; $i < sizeof($rows); $i++)
		{
			$fields[amount]=$rows[$i]['amount'];
			$fields[name]=$rows[$i]['name'];
			if($fields[name] == 'day'){
				$fields[name] = get_lang("day");
			}
			if($fields[name] == 'week'){
				$fields[name] = get_lang("week");
			}
			if($fields[name] == 'month'){
				$fields[name] = get_lang("month");
			}
			if($fields[name] == 'year'){
				$fields[name] = get_lang("year");
			}
			$fields[credits]=$rows[$i]['credits'];
			$option [$rows[$i]['option_id']]= $fields;
		}
		return $option;
	}
	else
	{
		return false;
	}
}

/**
 * Subscribe a user to a pay course.
 * @author Borja Nuï¿?ez
 * @param integer $code - Id of he Course.
 * @param $option_id
 * @param $option_amount
 * @param $option_name
 * @param integer $user_id - Optional, User id to look for. If there is not, uses id of the current user.
 * @return $res - Result of de SQL query.
 */
function cs_subscribe_user($code, $option_id, $option_amount, $option_name, $user_id = 0)
{
	$table_course_credits_rel_user_credits = Database :: get_main_table(CS_TABLE_COURSE_CREDITS_REL_USER_CREDITS);
	if(!$user_id)
		{
			$user_id = api_get_user_id();
		}
	$end_date = strtotime('+'.$option_amount.' '.$option_name);
	$sql = 'INSERT INTO '.$table_course_credits_rel_user_credits.' (code, option_id, user_id, init_date, end_date) VALUES ("'.$code.'", '.$option_id.', '.$user_id.', "'.date('Y-m-d-H:i',time()).'", "'.date('Y-m-d-H:i',$end_date).'")';
	return $res = api_sql_query($sql, __FILE__, __LINE__) or die(mysql_error());
}
	


/**
 * Get the credits of the user.
 * @author Borja Nuï¿?ez
 * @param integer $user_id - Optional, User id to look for. If there is not, uses id of the current user.
 * @return integer $credits - Amount of credits of the user.
 */
function cs_get_user_credits($user_id = 0)
	{
		$table_user_credits = Database :: get_main_table(CS_TABLE_USER_CREDITS);
		if(!$user_id)
		{
			$user_id = api_get_user_id();
		}
		$sql = "SELECT credits FROM ".$table_user_credits." WHERE user_id=".$user_id;
		$res = api_sql_query($sql, __FILE__, __LINE__);

		$user_credits = mysql_fetch_array($res);
		if($user_credits['credits'])
		{
			if ($user_credits['credits'] - floor($user_credits['credits']))
			{
				return $user_credits['credits'];
			}
			else
			{
				return floor($user_credits['credits']);
			}
		}else
		{
			return 0;
		}
	}
	
	/**
 * Set the credits of the user.
 * @author Borja Nuï¿?ez
 * @param integer $user_id - Optional, User id to look for. If there is not, uses id of the current user.
 * @param integer $amount - Optional, amount of credits to set. If there is not, uses 0.
 * @return integer $amount - Amount of credits of the user.
 */
function cs_set_user_credits($amount=0, $user_id = 0)
	{
		$table_user_credits = Database :: get_main_table(CS_TABLE_USER_CREDITS);
		$amount = $amount<0?0:$amount;
		if (is_numeric($amount))
		{
			if(!$user_id)
			{
				$user_id = api_get_user_id();
			}
			if (cs_is_new_user($user_id))
			{
				cs_new_user($user_id);
			}
	
			$sql = "UPDATE ".$table_user_credits." SET credits = ".$amount." WHERE user_id=".$user_id;		
			$res = api_sql_query($sql, __FILE__, __LINE__);
			return get_lang('CreditsSaved');
		}
		else
		{
			return get_lang('NotValidValue');
		}
	}

/**	
 * Checks if an user is already stored on the Credits System Database.
 * @author Jose C. Hueso Vï¿?zquez
 * @param integer $user_id - Optional, User id to look for. If there is not, uses id of the current user.
 * @return boolean - False if User does not exist on the Credit System. 
 */
function cs_is_new_user($user_id = 0)
	{
		if(!$user_id)
		{
			$user_id = api_get_user_id();
		}

		$table_user_credits = Database :: get_main_table(CS_TABLE_USER_CREDITS);
		$sql = "SELECT * FROM ".$table_user_credits." WHERE user_id=".$user_id;		

		return (mysql_num_rows($res = api_sql_query($sql, __FILE__, __LINE__))==0);
	}

	/**	
 * Creates a new User on the Credits System Database.
 * @author Jose C. Hueso Vï¿?zquez
 * @param integer $user_id - Optional, User id to look for. If there is not, uses id of the current user.
 * @return integer - Result of the SQL Query. 
 */
function cs_new_user($user_id = 0)
	{
		if(!$user_id)
		{
			$user_id = api_get_user_id();
		}

		$table_user_credits = Database :: get_main_table(CS_TABLE_USER_CREDITS);
		$sql = "INSERT INTO ".$table_user_credits." (user_id, credits) VALUES ('".$user_id."','0')";		
		return $res = api_sql_query($sql, __FILE__, __LINE__);
	}
	

		/**
 * Save payment's data on payment table.
 * @author Borja Nuï¿?ez
 * @param integer $user_id - Optional, User id to look for. If there is not, uses id of the current user.
 * @param integer $amount -  Amount of credits to set. If there is not, uses 0
 * @return integer $payment_id - Id of the payment inserted.
 */
function cs_pay($credits, $amount, $user_id = 0, $method=0)
	{
		$table_payment = Database :: get_main_table(CS_TABLE_PAYMENT);
		
		if(!$user_id)
		{
			$user_id = api_get_user_id();
		}
		if ($method)
		{
			$sql = "INSERT INTO ".$table_payment." (user_id, credits, payment_method, amount) VALUES (".$user_id.",".$credits.",'".$method."', ".$amount.")";
		}else
		{
			$sql = "INSERT INTO ".$table_payment." (user_id, credits, amount) VALUES (".$user_id.", ".$credits.", ".$amount.")";
		}
		$res = api_sql_query($sql, __FILE__, __LINE__);
		return mysql_insert_id();
	}
 
 		/**
 * Save payment's data on payment method table.
 * @author Borja Nuï¿?ez
 * @param integer $user_id - Optional, User id to look for. If there is not, uses id of the current user.
 * @param integer $amount -  Amount of credits to set. If there is not, uses 0
 * @return integer $payment_id - Id of the payment inserted.
 */
function cs_payment_method($data, $method, $user_id, $credits, $amount)
	{
		$payment_id = cs_pay($credits, $amount, $user_id, $method);
		cs_set_user_credits(cs_get_user_credits($user_id)+$credits, $user_id);
		//TO DO: Insert the payment data on payment mathod table.
		$table_payment_info = 'CS_TABLE_'.strtoupper($method).'_PAYMENT_INFO';
		$table_payment_info = Database :: get_main_table(constant($table_payment_info));
		$sql = 'INSERT INTO '.$table_payment_info.' ';
		$fields = '( payment_id, ';
		$values = '( '.$payment_id.', ';
		foreach ($data as $field => $value)
		{
			$fields .= $field.', ';
			$values .= '"'.$value.'", ';
		}
		$fields[strlen($fields)-2] = ')';
		$values[strlen($values)-2] = ')';
		
		$sql .= $fields.' VALUES '.$values;

		$res = api_sql_query($sql, __FILE__, __LINE__);
	}
 
/**
 * Returns the dates of the last paid subscription for a course.
 * @author Jose C. Hueso
 * @param integer $user_id - Id of the User to check. 
 * @param string $course_id - Id of he Course which the user may be subscribed.
 * @return string array. 
 * 				-> Initial and End date of the last paid subscription	
 * 				-> False, if user never paid a subscription for this course. 
 */
function cs_user_last_sub_paid($user_id,$course_id)
	{
		$table_user_course = Database :: get_main_table(CS_TABLE_COURSE_CREDITS_REL_USER_CREDITS); 
		$sql = "SELECT code,user_id,init_date,end_date FROM ".$table_user_course." WHERE user_id=".$user_id." AND code='".$course_id."' order by end_date desc";
		$res = api_sql_query($sql, __FILE__, __LINE__);	

		if ($user_sub = mysql_fetch_array($res)) 
		{
			$date['init_date'] = $user_sub["init_date"];
			$date['end_date'] = $user_sub["end_date"];
			return ($date);
		}
		else 
		{
			return false;
		}
		
	}
	
/**
 * Check if a user has paid his/her subscription to a course.
 * @author Jose C. Hueso
 * @param integer $user_id - Id of the User to check. 
 * @param string $course_id - Id of he Course which the user may be subscribed.
 * @return boolean. 'False' if the Userï¿?s subscriptions has expired. True if the user still has access.
 */
function cs_can_user_access($user_id,$course_id)
	{
		$current_date = strtotime (date('Y-m-d H:i:s'));
		$date = cs_user_last_sub_paid($user_id,$course_id);
		$end_date = strtotime ($date["end_date"]);	
		
		return ($end_date > $current_date);
	}
/**
 *	Get the credit courses that user have paid.
 * @author Borja Nuï¿?ez Salinas
 * @param integer $user_id - Id of the User to check. 
 * @return array $rows - all rows as result of sql query.
 */
 
function cs_user_pay_courses($user_id = 0)
{
	if (!$user_id)
	{
		$user_id = api_get_user_id();
	}
	$table_COURSE_CREDITS_REL_USER_CREDITS = Database :: get_main_table(CS_TABLE_COURSE_CREDITS_REL_USER_CREDITS);
	$sql = 'SELECT * FROM '.$table_COURSE_CREDITS_REL_USER_CREDITS.' WHERE end_date >= CURRENT_TIMESTAMP AND user_id='.$user_id;
	$res = api_sql_query($sql, __FILE__, __LINE__);
	$rows = api_store_result($res);
	return $rows;
}

/**
 * Get payment options 
 * @author Ana
 * @param integer $code - Id of the Course.
 * @return array $option - All info about payment option:
 * 				 ['option_id']
 * 				 ['name'] = day | week | month | year
 * 				 ['amount'] = amount of payment option (3 days, 6 months...)
 * 				  
 */
function cs_get_payment_options()
{
	$table_payment_option = Database :: get_main_table(CS_TABLE_PAYMENT_OPTION);
	$sql = " SELECT option_id, name, amount FROM $table_payment_option".' ORDER BY '.$table_payment_option.'.name, '.$table_payment_option.'.amount ASC';
	//$sql = 'SELECT '.$table_payment_option.'.option_id, '.$table_payment_option.'.name, '.$table_payment_option.'.amount FROM '.$table_payment_option;
	$res = api_sql_query($sql, __FILE__, __LINE__) or die(mysql_error());
	if(mysql_num_rows($res) > 0)
	{
		$rows = api_store_result($res);
		$option = '(';
		for($i=0; $i < sizeof($rows); $i++)
		{
			if($rows[$i]['name'] == 'day'){
				$rows[$i]['name'] = get_lang("day");
			}
			if($rows[$i]['name'] == 'week'){
				$rows[$i]['name'] = get_lang("week");
			}
			if($rows[$i]['name'] == 'month'){
				$rows[$i]['name'] = get_lang("month");
			}
			if($rows[$i]['name'] == 'year'){
				$rows[$i]['name'] = get_lang("year");
			}
			$option .= '"'.$rows[$i]['option_id'].'" => array("name" => "'.$rows[$i]['name'].'", "amount" => "'.$rows[$i]['amount'].'")';
			if ($i+1 < sizeof($rows))
			{
				$option .= ', ';
			}
		}
		$option .= ')';
		eval( '$option = array'.$option.';' ); //built array: $option = array(option_id => array(amount, credits))
		return $option;
	}
	else
	{
		return false;
	}
}

/**
 * 	NOT USED! there is no more enable/disable.
 * Get enabled payment options 
 * @author Borja Nuï¿?ez Salinas
 * @return array $option - All info about every payment option of the course:
 * 				 $option['option_id']['name'] = day | week | month | year
 * 				 $option['option_id']['amount'] = amount of payment option (3 days, 6 months...)
 * 				  
 */
 /*
function cs_get_enabled_payment_options()
{
	$table_payment_option = Database :: get_main_table(CS_TABLE_PAYMENT_OPTION);
	$sql = 'SELECT option_id, name, amount FROM '.$table_payment_option.' ORDER BY '.$table_payment_option.'.name, '.$table_payment_option.'.amount ASC';
	$res = api_sql_query($sql, __FILE__, __LINE__) or die(mysql_error());
	if(mysql_num_rows($res) > 0)
	{
		$rows = api_store_result($res);
		$option = '(';
		for($i=0; $i < sizeof($rows); $i++)
		{
			$option .= '"'.$rows[$i]['option_id'].'" => array("amount" => "'.$rows[$i]['amount'].'", "name" => "'.$rows[$i]['name'].'")';
			if ($i+1 < sizeof($rows))
			{
				$option .= ', ';
			}
		}
		$option .= ')';
		
		eval( '$option = array'.$option.';' ); //built array: $option = array(option_id => array(amount, name))
		
		return $option;
	}
	else
	{
		return false;
	}
}
*/

/**
 * Set the default payment options of a course
 * @author Jose C. Hueso Vï¿?zquez
 * @param integer $code - Id of the Course.
 * @return boolean. True if there was no problem
 */
function cs_enable_payment_options($code)
{
	$table_course_credits = Database :: get_main_table(CS_TABLE_COURSE_CREDITS);
	$table_current_settings = Database :: get_main_table(TABLE_MAIN_SETTINGS_CURRENT);

	$default_option = cs_get_current_settings();
	$option_id = $default_option['cs_default_payment_option'][0]['selected_value'];
	$credits = $default_option['cs_default_payment_option_credits'][0]['selected_value'];

	$sql = "INSERT INTO ".$table_course_credits." (code, option_id, credits) VALUES ('".$code."','".$option_id."','".$credits."')";	
	$res = api_sql_query($sql, __FILE__, __LINE__) or die(mysql_error());
	
	return (mysql_affected_rows()==1?true:false);

}

/**
 * Updates a payment option from a course.
 * @author Jose C. Hueso Vazquez
 * @param integer $code - Id of the Course.
 * @param integer $option_id - Optional, Id of the payment option that will be deleted.
 * @param integer $credits - New credits amount for this payment option 
 * @return boolean - True if all was ok. False if not.  
 */
function cs_update_course_payment_option($code,$option_id,$credits)
{
	$table_course_credits = Database :: get_main_table(CS_TABLE_COURSE_CREDITS);
	
	cs_update_course_options_history($option_id, $code);
	$sql = 'UPDATE '.$table_course_credits.' SET credits='.$credits.' WHERE code="'.$code.'" AND option_id='.$option_id.'';
	//echo $sql;
	$res = api_sql_query($sql, __FILE__, __LINE__) or die(mysql_error());

	return (mysql_affected_rows()>0);
}
/**
 * Check if a course has a test payment option set up
 * @author Jose C. Hueso Vazquez
 * @param integer $code - Id of the Course.
 * @return boolean  true - The course has already a test payment option
 * 					false - The course has not a test payment option.
 * 				 
 */
function cs_course_have_test_option($code)
{
	$table_course_credits = Database :: get_main_table(CS_TABLE_COURSE_CREDITS);
	$sql = 'SELECT  '.$table_course_credits.'.credits  FROM '.$table_course_credits.' WHERE code="'.$code.'" AND '.$table_course_credits.'.credits = 0';
	$res = api_sql_query($sql, __FILE__, __LINE__) or die(mysql_error());

	if(mysql_num_rows($res) > 0)
	{
		return true;
	}
	else
	{
		return false;
	}
}
?>