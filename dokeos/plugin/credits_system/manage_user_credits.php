<?php // $Id: manage_user_credits.php,v 1.0 2006/03/15 14:34:45 poty $
/*
==============================================================================
	Dokeos - elearning and course management software
	
	Copyright (c) 2004-2006 Dokeos S.A.
	Copyright (c) E.U.I. Universidad Polit�cnica de Madrid (Spain)
	Copyright (c) Borja Nu�ez Salinas - Programmer (bns@alumnos.upm.es)
	
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
*	This script allow the admin manage user credits.
*	(Add, Substract and edit one or multiple users credits)
*
*	@package dokeos.plugin
==============================================================================
*/

/*
==============================================================================
		INIT SECTION
==============================================================================
*/ 
	
$language_file = 'plugin_credits_system';
include("../../main/inc/global.inc.php"); 
api_protect_admin_script();
$action = $_GET["action"];
$show_cs_user_table = true;

//To allow to sort the edit table.
if (isset($_GET['selected0']))
{
	$_POST['action'] = 'edit';
	for ($i=0; isset($_GET['selected'.$i]); $i++)
	{
		$_POST['selected'][$i] = $_GET['selected'.$i];
	}
}

/*
-----------------------------------------------------------
	Libraries
-----------------------------------------------------------
*/ 

require_once (api_get_path(LIBRARY_PATH).'usermanager.lib.php');
include_once(dirname(__FILE__).'/inc/cs_database.lib.php');
include_once(dirname(__FILE__).'/inc/cs_functions.inc.php');
require_once(api_get_path(LIBRARY_PATH).'/formvalidator/FormValidator.class.php');

/*
==============================================================================
		FUNCTIONS
==============================================================================
*/ 

	/**
* Returns a mailto-link
* @param string $email An email-address
* @return string HTML-code with a mailto-link
*/
function email_filter($email)
{
	return Display :: encrypted_mailto_link($email, $email);
}

/**
 * Build the modify-column of the table
 * @param int $user_id The user id
 * @param string $url_params
 * @return string Some HTML-code with modify-buttons
 */
function modify_filter($user_id,$url_params)
{
	$result .= '<a href="'.api_get_path('WEB_PLUGIN_PATH').'credits_system/manage_user_credits.php?action=add&selected='.$user_id.'"><img src="./img/add.gif" border="0" style="vertical-align: middle;" title="'.get_lang('Add').'" alt="'.get_lang('Add').'"/></a>&nbsp;';
	if (cs_get_user_credits($user_id) > 0)
	{
		$result .= '<a href="'.api_get_path('WEB_PLUGIN_PATH').'credits_system/manage_user_credits.php?action=substract&selected='.$user_id.'"><img src="./img/substract.gif" border="0" style="vertical-align: middle;" title="'.get_lang('Substract').'" alt="'.get_lang('Substract').'"/></a>&nbsp;';
	}
	else
	{
		$result .= '<img src="./img/substract_disabled.gif" border="0" style="vertical-align: middle;" title="'.get_lang('Substract').'" alt="'.get_lang('Substract').'"/>';
	}
	$result .= '<a href="'.api_get_path('WEB_PLUGIN_PATH').'credits_system/manage_user_credits.php?action=edit&selected='.$user_id.'"><img src="./img/edit.gif" border="0" style="vertical-align: middle;" title="'.get_lang('Edit').'" alt="'.get_lang('Edit').'"/></a>&nbsp;';
	return $result;
}

/**
 * Get the total number of users on the platform
 * @see SortableTable#get_total_number_of_items()
 */
function get_number_of_users()
{
	if (isset($_POST['action']) && $_POST['action'] == 'edit')
	{
		return count($_POST['selected']);
	}
	else
	{
		$user_table = Database :: get_main_table(TABLE_MAIN_USER);
		$sql = "SELECT COUNT(user_id) AS total_number_of_items FROM $user_table";
		if (isset ($_GET['keyword']))
		{
			$keyword = mysql_real_escape_string($_GET['keyword']);
			$sql .= " WHERE firstname LIKE '%".$keyword."%' OR lastname LIKE '%".$keyword."%'  OR email LIKE '%".$keyword."%'  OR official_code LIKE '%".$keyword."%'";
		}
		elseif (isset ($_GET['keyword_firstname']))
		{
			$keyword_firstname = mysql_real_escape_string($_GET['keyword_firstname']);
			$keyword_lastname = mysql_real_escape_string($_GET['keyword_lastname']);
			$keyword_email = mysql_real_escape_string($_GET['keyword_email']);
			$keyword_username = mysql_real_escape_string($_GET['keyword_username']);
			$keyword_status = mysql_real_escape_string($_GET['keyword_status']);
			$keyword_active = isset($_GET['keyword_active']);
			$keyword_inactive = isset($_GET['keyword_inactive']);
			$sql .= " WHERE firstname LIKE '%".$keyword_firstname."%' AND lastname LIKE '%".$keyword_lastname."%' AND username LIKE '%".$keyword_username."%'  AND email LIKE '%".$keyword_email."%'   AND official_code LIKE '%".$keyword_officialcode."%'    AND status LIKE '".$keyword_status."'";
			if($keyword_active && !$keyword_inactive)
			{
				$sql .= " AND active='1'";
			}
			elseif($keyword_inactive && !$keyword_active)
			{
				$sql .= " AND active='0'";
			}
		}
		$res = api_sql_query($sql, __FILE__, __LINE__);
		$obj = mysql_fetch_object($res);
		return $obj->total_number_of_items;
	}
}

/**
 * Get the users to display on the current page.
 * @see SortableTable#get_table_data($from)
 */
function get_user_data($from, $number_of_items, $column, $direction)
{
	$user_table = Database :: get_main_table(TABLE_MAIN_USER);
	$cs_user_credits_table = Database :: get_main_table(CS_TABLE_USER_CREDITS);
	if (isset($_POST['action']) && $_POST['action'] == 'edit')
	{
		$column_number = -1;
	}
	else
	{
		$column_number = 0;
	}
	$sql = 'SELECT ' .
				 $user_table.'.user_id				AS col'.($column_number==-1?'':0).',' .
				 $user_table.'.official_code    	AS col'.($column_number+1).','.
                 $user_table.'.lastname 			AS col'.($column_number+2).',' .
                 $user_table.'.firstname 			AS col'.($column_number+3).','.
                 $user_table.'.username				AS col'.($column_number+4).','.
                 $user_table.'.email				AS col'.($column_number+5).','.
                 ' IF('.$user_table.'.status=1,"'.get_lang('Teacher').'","'.get_lang('Student').'")	 AS col'.($column_number+6).','.
                 $cs_user_credits_table.'.credits	AS col'.($column_number+7).','.
                 $user_table.'.user_id				AS col'.($column_number+8).
             ' FROM'.
                 $user_table.' LEFT JOIN '.$cs_user_credits_table.' ON '.$cs_user_credits_table.'.user_id = '.$user_table.'.user_id';
	if (isset ($_GET['keyword']))
	{
		$keyword = mysql_real_escape_string($_GET['keyword']);
		$sql .= " WHERE firstname LIKE '%".$keyword."%' OR lastname LIKE '%".$keyword."%'  OR username LIKE '%".$keyword."%'  OR official_code LIKE '%".$keyword."%'";
	}
	elseif (isset ($_GET['keyword_firstname']))
	{
		$keyword_firstname = mysql_real_escape_string($_GET['keyword_firstname']);
		$keyword_lastname = mysql_real_escape_string($_GET['keyword_lastname']);
		$keyword_email = mysql_real_escape_string($_GET['keyword_email']);
		$keyword_username = mysql_real_escape_string($_GET['keyword_username']);
		$keyword_status = mysql_real_escape_string($_GET['keyword_status']);
		$sql .= " WHERE firstname LIKE '%".$keyword_firstname."%' AND lastname LIKE '%".$keyword_lastname."%' AND username LIKE '%".$keyword_username."%'  AND email LIKE '%".$keyword_email."%'   AND official_code LIKE '%".$keyword_officialcode."%'    AND status LIKE '".$keyword_status."'";
	}
	//edit by xiaoping;
	/*elseif($_POST['action']=='edit')
	{
		$sql .= " WHERE $user_table.user_id=".intval($_POST['selected'][0]);			
	}*/
	
	$sql .= " ORDER BY col$column $direction ";
	if($_POST['action']!='edit')$sql .= " LIMIT $from,$number_of_items";//edit by xiaoping;
	$res = api_sql_query($sql, __FILE__, __LINE__);	

	$users = array ();
	while ($user = mysql_fetch_row($res))
	{		
		if (isset($_POST['action']) && $_POST['action'] == 'edit')
		{
			//Change Credits column to edit and dont show user_id.
			if (in_array($user[0], $_POST['selected'], true))
			{
				$user[7] = '<input type="text" name="credits['.$user[0].']" value="'.$user[7].'" size="9" maxlength="9">';
				for ($i=0; count($user) > $i+2; $i++)
				{
					$user2[$i] = $user[$i+1];
				}
				$users[] = $user2;
			}			
		}
		else
		{
			$users[] = $user;
		}
	}
	return $users;
}
	
/*
==============================================================================
		MAIN CODE
==============================================================================
*/ 

//TO DO: SPLIT EDIT and NORMAL TABLE to get clear code and to fix order table when edit.

//Redirection for modify links
if (isset($_GET['action']) and isset($_GET['selected']))
{
	Display::display_header($tool_name);
	echo get_lang('PleaseWait').'...';
	echo '<form action="'.api_get_path('WEB_PLUGIN_PATH').'credits_system/manage_user_credits.php" method="post" name="modify_form">';
	echo '<input type="hidden" name="action" value="'.$_GET['action'].'">';
	echo '<input type="hidden" name="selected[]" value="'.$_GET['selected'].'">';
    echo '</form>';	
    echo '<script language="JavaScript">document.modify_form.submit()</script>';
	echo '<br />';
	echo get_lang('IfDontLoad').' ';
	echo '<a href="javascript:document.modify_form.submit()">'.get_lang('here').'</a>';
	Display::display_footer();
	exit;
}

if (isset ($_GET['search']) && $_GET['search'] == 'advanced')
{
	$interbreadcrumb[] = array ("url" => api_get_path(PLUGIN_PATH).'my_credits.php', "name" => get_lang('CreditsSystem'));
	$interbreadcrumb[] = array ("url" => $_SERVER['PHP_SELF'], "name" => get_lang('ManageUserCredits'));
	$tool_name = get_lang('SearchAUser'); 
	Display::display_header($tool_name);
	
	$form = new FormValidator('advanced_search','get');
	$form->add_textfield('keyword_firstname',get_lang('FirstName'),false);
	$form->add_textfield('keyword_lastname',get_lang('LastName'),false);
	$form->add_textfield('keyword_username',get_lang('LoginName'),false);
	$form->add_textfield('keyword_email',get_lang('Email'),false);
	$form->add_textfield('keyword_officialcode',get_lang('OfficialCode'),false);
	$status_options = array();
	$status_options['%'] = get_lang('All');
	$status_options[STUDENT] = get_lang('Student');
	$status_options[COURSEMANAGER] = get_lang('Teacher');
	$form->addElement('select','keyword_status',get_lang('Status'),$status_options);
	$form->addElement('submit','submit',get_lang('Ok'));
	$defaults['keyword_active'] = 1;
	$defaults['keyword_inactive'] = 1;
	$form->setDefaults($defaults);
	$form->display();
}
else
{
	$interbreadcrumb[] = array ("url" => api_get_path(PLUGIN_PATH).'my_credits.php', "name" => get_lang('CreditsSystem'));
	$tool_name = get_lang("ManageUserCredits"); 
	if (isset($_GET['submit']))
	{
		$interbreadcrumb[] = array ("url" => $_SERVER['PHP_SELF'], "name" => get_lang('ManageUserCredits'));
		$tool_name = get_lang('Search');
		if (isset($_GET['keyword']))
		{
			$tool_name .= ': '.$_GET['keyword'];
		} 
	}
	$show_cs_user_table = true;
	if (isset ($_POST['action']))
	{
		switch ($_POST['action'])
		{
			case 'add' :									
						if (isset($_POST['add_amount']) && isset($_POST['users_to_add']))
						{
							Display::display_header($tool_name);
							//Save added credits in DB.							
							for ($i=0; $i < count($_POST['users_to_add']); $i++)
							{
								cs_set_user_credits(cs_get_user_credits($_POST['users_to_add'][$i])+abs($_POST['add_amount']),$_POST['users_to_add'][$i]);
								$cost_per_credit = cs_get_current_settings('cs_cost_per_credit');
								cs_pay(abs($_POST['add_amount']),abs($_POST['add_amount'])*$cost_per_credit,$_POST['users_to_add'][$i],get_lang('Admin_payment'));
							}
							//show table again.				
						}elseif(count($_POST['selected']) > 0)
						{
						$interbreadcrumb[] = array ("url" => $_SERVER['PHP_SELF'], "name" => get_lang('ManageUserCredits'));
						$tool_name = get_lang("AddCredits"); 
						Display::display_header($tool_name);
						
						$show_cs_user_table = false;
						$show_selected = false;
						if (count($_POST['selected'])<=10)
						{
							$show_selected = true;
						}
						//javascript validate function (not quickform to get the form inside the message)
						$add_form = '<script language="JavaScript">'.
								'function submitenter(e)
								{
								var keycode;
								if(window.event) keycode = window.event.keyCode;
								else if(e) keycode = e.which;
								else return true;
								
								if (keycode == 13)
								   {
								   validateadd();
								   return false;
								   }
								else
								   return true;
								}'.
								'function validateadd()' .
								'{
									if(isNaN(document.add_form.add_amount.value))' .
									'{
										alert("'.get_lang("Creditsmustbenumeric").'");'.       //重新定义资源文件 by xiaoping        
										'document.add_form.add_amount.focus();
									}' .
									'else' .
									'{
										if (document.add_form.add_amount.value <= 0)' .
										'{
											alert("'.get_lang("Creditsmustbepositive").'");'.  //重新定义资源文件 by xiaoping          
											'document.add_form.add_amount.focus();
										}' .
										'else' .
										'{
											document.add_form.submit();
										}
									}
								 }' .
								 '</script>';				 
						//end javascritpt
						$add_form .= get_lang('addCredits');					
						$add_form .= $show_selected?':':'.';				
						$add_form .= '<br />';
						foreach ($_POST['selected'] as $key => $value)
						{
							$user_info = api_get_user_info($value);
							if ($show_selected)
							{
								$add_form .= $user_info['official_code'].'<br />';
							}							
							$add_form_users .= '<input type="hidden" name="users_to_add[]" value="'.$value.'">';
						}						
						$add_form .= '<form action="'.$_SERVER['PHP_SELF'].'" method="post" name="add_form">';
						$add_form .= get_lang('PlzCredits').' '.get_lang('toAdd').': ';
						$add_form .= '<input type="text" name="add_amount" size="9" maxlength="9" onKeyPress="return submitenter(event)"><input type="hidden" name="action" value="add">'.$add_form_users;						
						$add_form .= '&nbsp;<a href="javascript:validateadd();"> '.get_lang('Continue').'</a>';
						$add_form .= '</form>';
						Display :: display_normal_message($add_form,false);
						}else
						{//no users selected
							Display::display_header($tool_name);
							Display :: display_error_message(get_lang('AnyUsersSelected'));
						}						
				break;
			case 'edit' :
						$interbreadcrumb[] = array ("url" => $_SERVER['PHP_SELF'], "name" => get_lang('ManageUserCredits'));
						$tool_name = get_lang("EditCredits"); 
						Display::display_header($tool_name);
						if (count($_POST['selected']) == 0)
						{
							Display :: display_error_message(get_lang('AnyUsersSelected').'<br/>'.'<a href="javascript:history.go(-1)">'.get_lang('GoBack').'</a>',false);
						}
				break;
			case 'save_edit' :
							Display::display_header($tool_name);
							$error_msg=get_lang('AtLeastOneError');
							$num_errors = 0;
							foreach ($_POST['credits'] as $key => $value)
							{
								if ($value == '')
								{
									$value = 0;
								}
								if (!is_numeric($value) or $value < 0){
									$user_info = api_get_user_info($key);
									$errors.='>'.$user_info['official_code'].' Error: '.$value.' '.get_lang('IsNotAValidValue').'.<br/>';
									$num_errors++;
								}
								else
								{																											
									$final_payment = $value - cs_get_user_credits($key);
									if ($final_payment != 0)
									{
										$cost_per_credit = cs_get_current_settings('cs_cost_per_credit');
										cs_pay($final_payment,$final_payment*$cost_per_credit,$key,get_lang('Admin_payment'));
										cs_set_user_credits($value,$key);
									}
								}
							}
							if ($errors)
							{								
								if ($num_errors < 3)
								{
									$error_msg .= ':<br/>'.$errors;
								}
								else
								{
									$errors = str_replace('<br/>','\n',$errors);
									$error_msg .= '.<br/><a href="javascript:alert(\''.$errors.'\')">'.get_lang('Details').'</a>';
								}
								Display :: display_error_message($error_msg,false);
							}
				break;
			case 'substract' :						
						if (isset($_POST['sub_amount']) && isset($_POST['users_to_sub']))
						{
							Display::display_header($tool_name);
							//Save added credits in DB.
							for ($i=0; $i < count($_POST['users_to_sub']); $i++)
							{
								$last_amount = cs_get_user_credits($_POST['users_to_sub'][$i]);
								cs_set_user_credits(cs_get_user_credits($_POST['users_to_sub'][$i])-abs($_POST['sub_amount']),$_POST['users_to_sub'][$i]);
								$payment = cs_get_user_credits($_POST['users_to_sub'][$i]) - $last_amount;
								$cost_per_credit = cs_get_current_settings('cs_cost_per_credit');
								if ($payment != 0)
								{
									cs_pay($payment,$payment*$cost_per_credit,$_POST['users_to_sub'][$i],get_lang('Admin_payment'));
								}
							}
							//Show table again				
						}elseif(count($_POST['selected']) > 0)
						{
						$interbreadcrumb[] = array ("url" => $_SERVER['PHP_SELF'], "name" => get_lang('ManageUserCredits'));
						$tool_name = get_lang('SubtractCredits'); 
						Display::display_header($tool_name);
						
						$show_cs_user_table = false;
						$show_selected = false;						
						if (count($_POST['selected']) <= 10)
						{
							$show_selected = true;
						}
						
						//javascript validate function (not quickform to get the form inside the message)
						$sub_form = '<script language="JavaScript">'.
								'function submitenter(e)
								{
								var keycode;
								if(window.event)keycode = window.event.keyCode;
								else if(e)keycode = e.which;
								else return true;
								
								if(keycode == 13)
								   {
								   validatesub();
								   return false;
								   }
								else
								   return true;
								}'.
								'function validatesub()' .
								'{
									if(isNaN(document.sub_form.sub_amount.value))' .
									'{
										alert("'.get_lang("Creditsmustbenumeric").'");' . //redefine the 'Creditsmustbenumeric' in lang-file:by xiaoping
										'document.sub_form.sub_amount.focus();
									}' .
									'else' .
									'{
										if (document.sub_form.sub_amount.value <= 0)'.
										'{
											alert("'.get_lang("Creditsmustbepositive").'");' .//redefine the 'Creditsmustbepositive' in lang-file:by xiaoping
											'document.sub_form.sub_amount.focus();
										}' .
										'else' .
										'{
											document.sub_form.submit();
										}
									}
								 }' .
								 '</script>';		 
						//end javascritpt
						
						$sub_form .= get_lang('subCredits');
						$sub_form .= $show_selected?':':'.';
						$sub_form .= '<br />';
						foreach ($_POST['selected'] as $key => $value)
						{
							$user_info = api_get_user_info($value);
							if ($show_selected)
							{
								$sub_form .= $user_info['official_code'].'<br />';
							}							
							$sub_form_users .= '<input type="hidden" name="users_to_sub[]" value="'.$value.'">';
						}
						$sub_form .= '<form action="'.$_SERVER['PHP_SELF'].'" method="post" name="sub_form">';
						$sub_form .= get_lang('PlzCredits').' '.get_lang('toSub').': ';
						$sub_form .= '<input type="text" name="sub_amount" size="9" maxlength="9" onKeyPress="return submitenter(event)"><input type="hidden" name="action" value="substract">'.$sub_form_users;
						$sub_form .= '&nbsp;<a href="javascript:validatesub();"> '.get_lang('Continue').'</a>';//Delete an unwanted tag 'get_lang('And')':by xiaoping
						$sub_form .= '</form>';
						Display :: display_normal_message($sub_form,false); //Add a argument 'false' to not convert all applicable characters to HTML entities :by xiaoping
						}else
						{//No users selected
							Display::display_header($tool_name);
							Display :: display_error_message(get_lang('AnyUsersSelected'));
						}		
				break;
		}
	}
	else
	{
		Display::display_header($tool_name);
	}
	if ($show_cs_user_table)
	{
		// Create a search-box
		$form = new FormValidator('search_simple','get','','',null,false);
		$renderer =& $form->defaultRenderer();
		$renderer->setElementTemplate('<span>{element}</span> ');
		$form->addElement('text','keyword',get_lang('keyword'));
		$form->addElement('submit','submit',get_lang('Search'));
		$form->addElement('static','search_advanced_link',null,'<a href="?search=advanced">'.get_lang('AdvancedSearch').'</a>');
		$form->display();
		if (isset ($_GET['keyword']))
		{
			$parameters = array ('keyword' => $_GET['keyword']);
		}
		elseif (isset ($_GET['keyword_firstname']))
		{
			$parameters['keyword_firstname'] = $_GET['keyword_firstname'];
			$parameters['keyword_lastname'] = $_GET['keyword_lastname'];
			$parameters['keyword_email'] = $_GET['keyword_email'];
			$parameters['keyword_officialcode'] = $_GET['keyword_officialcode'];
			$parameters['keyword_status'] = $_GET['keyword_status'];
			$parameters['keyword_active'] = $_GET['keyword_active'];
			$parameters['keyword_inactive'] = $_GET['keyword_inactive'];
		}
		
		if (isset($_POST['action']) && $_POST['action'] == 'edit')
		{	//Edit form
			echo '<form name="edit_form" action="'.$_SERVER['PHP_SELF'].'" method="post">';
			echo '<input type="hidden" name="action" value="save_edit">';
		}
		
		// Create a sortable table with user-data
		$column_number = 0;		
		$table = new SortableTable('users', 'get_number_of_users', 'get_user_data',2);
		if ( !(isset($_POST['action']) && $_POST['action'] == 'edit') )
		{
		$table->set_form_actions(array ('add' => get_lang('AddCredits'),
									'edit' => get_lang('EditCredits'),
									'substract' => get_lang('SubtractCredits')),'selected');
		$table->set_header($column_number++, '', false);
		}
		else
		{
			foreach ($_POST['selected'] as $key => $value)
			{
				$parameters['selected'.$key] = $value;
			}
			$table->set_additional_parameters($parameters);
		}
		$table->set_header($column_number++, get_lang('OfficialCode'));
		$table->set_header($column_number++, get_lang('LastName'));
		$table->set_header($column_number++, get_lang('FirstName'));
		$table->set_header($column_number++, get_lang('LoginName'));
		$table->set_header($column_number++, get_lang('Email'));
		$table->set_header($column_number++, get_lang('Status'));
		$table->set_header($column_number++, get_lang('Credits'));
		if ( !(isset($_POST['action']) && $_POST['action'] == 'edit') )
		{
		$table->set_header($column_number, get_lang('Modify'));
		$table->set_column_filter($column_number, 'modify_filter');
		}
		$column_number++;
		$table->set_column_filter($column_number-4, 'email_filter');
		$table->display();
				
		if (isset($_POST['action']) && $_POST['action'] == 'edit')
		{	//End of edit form
			echo '<input type="submit" value="'.get_lang('Save').'" />';
			echo'</form>';
		}
	}
}
/*
==============================================================================
		FOOTER 
==============================================================================
*/ 
Display::display_footer();
?>