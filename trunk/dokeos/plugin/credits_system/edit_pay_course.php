<?php
/*
==============================================================================
	Dokeos - elearning and course management software

	Copyright (c) 2007 E.U.I. Universidad Polit锟?cnica de Madrid (Spain)
	Copyright (c) 2004 Dokeos S.A.
	Copyright (c) 2003 University of Ghent (UGent)
	Copyright (c) 2001 Universite catholique de Louvain (UCL)

	For a full list of contributors, see "credits.txt".
	The full license can be read in "license.txt".

	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	See the GNU General Public License for more details.

	Contact: Dokeos, 181 rue Royale, B-1000 Brussels, Belgium, info@dokeos.com
==============================================================================
*/
/**
==============================================================================
*	This script manages the payment options of an specific course selected.
*	Here, a platform admin or a course admin can manage the current payment options of 
*	the course selected.
*
*	Add option:
*
*		- You can add one or more payment options for the selected course. 
*		- You can only add payment options enabled by the platform admin
*
*	Edit option:
*

**		- You can edit one or more payment options for the selected course.
*		- You can only edit the amount of credits for the payment options selected.
*
*	Delete option:
*
*		- You can delete one or more payment options for the selected course. 
*		
*
*	@package dokeos.plugin.credits_system
==============================================================================
*/

/*
==============================================================================
		INIT SECTION
==============================================================================
*/ 

$language_file = "plugin_credits_system";
if (isset($_GET['code']))
{
	$cidReq = $_GET['code'];
}

include_once('../../main/inc/global.inc.php');
require_once(api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php');
require_once(dirname(__FILE__).'/inc/cs_functions.inc.php');
require_once (api_get_path(LIBRARY_PATH).'sortabletable.class.php');
require_once (api_get_path(LIBRARY_PATH).'course.lib.php');


api_block_anonymous_users();

/*
-----------------------------------------------------------
	Header
	include the HTTP, HTML headers plus the top banner
-----------------------------------------------------------
*/

$interbreadcrumb[] = array ("url" => 'my_credits.php', "name" => get_lang('CreditsSystem'));
$interbreadcrumb[] = array ("url" => 'manage_course_credits.php?table=pay&view='.$_GET['view'], "name" => get_lang('ManageCourseCredits'));


// The add and edit options have an adittional page.

if(($_GET['action'] == 'add') || ($_GET['action'] == 'edit') || ($_POST['action'] == 'edit'))
{
	$interbreadcrumb[] = array ("url" => 'edit_pay_course.php?action=current&view='.$_GET['view'], "name" => get_lang('PaymentOptions'));
	if (!isset($_POST['action']))
	{
		$nameTools = get_lang($_GET['action']);	
	}
	else if ((isset($_POST['payment_options_selected'])) && (count($_POST['payment_options_selected'])>0))
		{
			$nameTools = get_lang($_POST['action']);		
		}
	//User selected no option to edit
		else 
		{
			unset($_POST['action']);
			$_GET['action'] = 'current';
		}
}

else $nameTools = get_lang('PaymentOptions');

if ($_POST['action'] != 'add' && $_GET['action'] != 'edited')
{
	Display :: display_header($nameTools);
}

/*
-----------------------------------------------------------
	Title:
	Title and 'add link' of the page
-----------------------------------------------------------
*/

//First we make sure the admin gave the teacher permission to edit payment options.

$settings = cs_get_current_settings('cs_allow_teacher'); 
if ($settings == 'no' && !api_is_platform_admin())
{
	Display :: display_error_message(get_lang('EditPayCOurseAccessDenied'));
	Display :: display_footer();
	exit;
}

/*if (($_GET['action'] == 'delete')||($_GET['action'] == 'edited'))
{
	$title = get_lang('currentMenu');
}
else if ($_POST['action'] == 'edit')
	{
		$title = get_lang('editMenu');
	}
	else $title = get_lang($_GET['action'].'Menu');*/

if ($_POST['action'] == 'edit')
{
	$title = get_lang('editMenu');
}
else if ($_GET['action'] == 'add' || $_GET['action'] == 'edit')
	{
		$title = get_lang($_GET['action'].'Menu');
	}
	else 
	{
		if ($_GET['action']!= 'edited' && $_GET['action']!= 'edited' && $_POST['action']!= 'delete' && $_GET['action']!= 'delete')
		{
			$_GET['action']= 'current';
		}
		$title = get_lang('currentMenu');
	} 
	
if ($_POST['action'] != 'add' && $_GET['action'] != 'edited')
{
	api_display_tool_title($title);
}

if($_GET['action'] != 'add' && $_GET['action'] != 'edit' && $_POST['action'] != 'edit' && $_POST['action'] != 'add' && $_GET['action'] != 'edited')
{
	echo '<img src="img/new_coins.gif" border="0" style="vertical-align: middle" title="'.get_lang("Edit").'" alt="'.get_lang("Edit").'"/> ';
	echo ' <a href="?action=add&view='.$_GET['view'].'">'.get_lang('AddPaymentOption').'</a>';
}

/*
==============================================================================
		FUNCTIONS
==============================================================================
*/ 

/**
 * Get the number of payment options of the course selected
 */
function get_number_of_options()
{
	$complete_name = $_SESSION['_user']['lastName'].' '.$_SESSION['_user']['firstName'];
	$course_credits_table = Database :: get_main_table(CS_TABLE_COURSE_CREDITS);
	$sql = "SELECT COUNT(code) AS total_number_of_items FROM $course_credits_table WHERE code='".$_SESSION['_course']['sysCode']."'";
	$res = api_sql_query($sql, __FILE__, __LINE__);
	$obj = mysql_fetch_object($res);
	return $obj->total_number_of_items;
}

/**
 * Get the payment options's data of the course selected
 */
function get_payment_data($from, $number_of_items, $column, $direction)
{
	$payment_option_table = Database :: get_main_table(CS_TABLE_PAYMENT_OPTION);
	$course_credits_table = Database :: get_main_table(CS_TABLE_COURSE_CREDITS);
	$course_table = Database :: get_main_table(TABLE_MAIN_COURSE);
	
	$sql = "SELECT ".$course_credits_table.".option_id AS col0, amount AS col1, name AS col2, credits AS col3 FROM $course_credits_table, $payment_option_table, $course_table WHERE ".$course_table.".code = ".$course_credits_table.".code AND ".$course_credits_table.".code='".$_SESSION['_course']['sysCode']."' AND ".$course_credits_table.".option_id = ".$payment_option_table.".option_id";
	$sql .= " ORDER BY col$column $direction ";
	$sql .= " LIMIT $from,$number_of_items";
	$res = api_sql_query($sql, __FILE__, __LINE__);
	$courses = array ();
	while ($course = mysql_fetch_row($res))
	{//edit by xiaoping
		switch ($course[2])
		{
			case 'day':$course[2] ='天';break;
			case 'month':$course[2] ='月';break;
			case 'week':$course[2] ='周';break;
			case 'year':$course[2] ='年';
		}
		$courses[] = $course;
	}
	
	return $courses;
}

/**
 * Options links
 */
function modify_filter($active,$url_params,$row,$column)
{
	
	$option_id = $row[0];
	
	return ('<a href="edit_pay_course.php?action=edit&view='.$_GET['view'].'&option_id='.$option_id.'"><img src="../../main/img/edit.gif" border="0" style="vertical-align: middle" title="'.get_lang('Edit').'" alt="'.get_lang('Edit').'"/></a>&nbsp;'.
			'<a href="edit_pay_course.php?action=delete&view='.$_GET['view'].'&option_id='.$option_id.'"  onclick="javascript:if(!confirm('."'".addslashes(htmlspecialchars(get_lang("ConfirmYourChoice")))."'".')) return false;"><img src="../../main/img/delete.gif" border="0" style="vertical-align: middle" title="'.get_lang('Delete').'" alt="'.get_lang('Delete').'"/></a>');
				
}
function get_language_unit($unit)
{
	$value='unknown';
	switch ($unit)
	{
		case 'day':$value=get_lang('day');break;
		case 'week':$value=get_lang('week');break;
		case 'month':$value=get_lang('month');break;
		case 'year':$value=get_lang('year');							
	}
	return $value;
}

/*	
==============================================================================
		MAIN CODE
==============================================================================
*/ 

//We need current options in order to create both add and edit forms.
$current_options = cs_get_course_payment_options ($_SESSION['_course']['sysCode']);

//Show and fill the add form.

if (($_GET['action'] == 'add') || ($_POST['action'] == 'add'))
{

	$form_new_options = new FormValidator('course_new_options','post',$_SERVER[SELF].'?action=current&num='.$_GET['num'].'&more='.$_GET['more'].'&view='.$_GET['view']);

//Only a test payment option per course is allowed.
	$no_more_test_options = 'false';
	foreach ($current_options as $option_id => $value)	
	{
		if ($value['credits'] == 0)
		{
			$no_more_test_options = 'true';
		}
	}	
//Valid amount rule, positive float number.
	$form_new_options -> registerRule('valid_amount','regex','/^\d*\.{0,1}\d+$/');
	
//Possible payment options of the course.
	$new_options = cs_get_course_possible_payment_options($_SESSION['_course']['sysCode']);	
	if (!$new_options)
	{
		Display :: display_warning_message(get_lang('NoMorePaymentOptionsAvailable'));
		Display :: display_footer();
		exit;
	}
	else $possible_options_number = count($new_options);

	$select_options[0]=get_lang('SelectPaymentOption');
	foreach ($new_options as $key => $value)
	{
		$select_options[$key]=$value['amount'].' '.get_language_unit($value['name']);//by xiaoping
	}
	
//User selected more than one option to add
	if (isset($_GET['more']))
	{
		$options_num= $_GET['num'];
	
		$group_add[] = $form_new_options->createElement('static','','',get_lang('Set'));
		$group_add[] = $form_new_options->createElement('text','credits',get_lang('credits'),array('size'=>'8','maxlength'=>'8'));
		$group_add[] = $form_new_options->createElement('static','','',get_lang('CreditsPer'));
		$group_add[] = $form_new_options->createElement('select','payment_option','payment_option',$select_options);

	//Do not show more options than the possible ones.
		if ($options_num >= $possible_options_number)
		{
			$options_num = $possible_options_number;
		}
		
		for ($i=2;$i<=$options_num;$i++)
		{
			$option_name = 'option_group'.$i;
			$form_new_options -> addGroup($group_add,$option_name);
			if ($no_more_test_options == 'true')
			{
				$form_new_options -> addGroupRule($option_name,array(array(),array(array(get_lang('CreditAmountRequired'),'required',null),array(get_lang('Creditsmustbepositive'),'valid_amount',null),array(get_lang('NoMoreTestOptions'),'nonzero')),array(),array(array(get_lang('SelectValidPaymentOption'),'nonzero'))));
			}
			else $form_new_options -> addGroupRule($option_name,array(array(),array(array(get_lang('CreditAmountRequired'),'required',null),array(get_lang('Creditsmustbepositive'),'valid_amount',null)),array(),array(array(get_lang('SelectValidPaymentOption'),'nonzero'))));
			// Client side validation disabled.
			//$form_new_options -> addGroupRule($option_name,array(array(),array(array(get_lang('CreditAmountRequired'),'required',null,'client'),array(get_lang('CreditAmountPositive'),'valid_amount',null,'client'),$nonzero_rule),array(),array(array(get_lang('SelectValidPaymentOption'),'nonzero',null,'client'))));
		}		
	}
	else $options_num=1;


	if ($options_num<=0)
	{
		$options_num = 1;
	}
	

// Option with the add-option link.
	$group[] = $form_new_options->createElement('static','','',get_lang('Set'));
	$group[] = $form_new_options->createElement('text','credits',get_lang('credits'),array('size'=>'8','maxlength'=>'8'));
	$group[] = $form_new_options->createElement('static','','',get_lang('CreditsPer'));
	$group[] = $form_new_options->createElement('select','payment_option',get_lang('payment_option'),$select_options);
	$group[] = $form_new_options->createElement('link','add_option','',$_SERVER[SELF].'?action=add&num='.++$options_num.'&more=true&view='.$_GET['view'],'<img src="img/add.gif" border="0" style="vertical-align: middle" title="'.get_lang('AddOption').'" alt="'.get_lang('AddOption').'"/></a>');
	$group[] = $form_new_options->createElement('link','delete_option','',$_SERVER[SELF].'?action=add&num='.($options_num-2).'&more=true&view='.$_GET['view'],'<img src="img/substract.gif" border="0" style="vertical-align: middle" title="'.get_lang('DeleteOption').'" alt="'.get_lang('DeleteOption').'"/></a>');


	$form_new_options -> addGroup ($group,'add_option_group');
	if ($no_more_test_options == 'true')
	{
		$form_new_options -> addGroupRule('add_option_group',array(array(),array(array(get_lang('CreditAmountRequired'),'required',null),array(get_lang('Creditsmustbepositive'),'valid_amount',null),array(get_lang('NoMoreTestOptions'),'nonzero')),array(),array(array(get_lang('SelectValidPaymentOption'),'nonzero'))));
	}
	else $form_new_options -> addGroupRule('add_option_group',array(array(),array(array(get_lang('CreditAmountRequired'),'required',null),array(get_lang('Creditsmustbepositive'),'valid_amount',null)),array(),array(array(get_lang('SelectValidPaymentOption'),'nonzero'))));

	// Client side validation disabled.
	//$form_new_options -> addGroupRule('add_option_group',array(array(),array(array(get_lang('CreditAmountRequired'),'required',null,'client'),array(get_lang('CreditAmountPositive'),'valid_amount',null,'client'),$nonzero_rule),array(),array(array(get_lang('SelectValidPaymentOption'),'nonzero',null,'client'))));


	$form_new_options -> addElement ('hidden','num_options',$options_num); 				
	$form_new_options -> addElement ('hidden','action','add');

	$form_new_options -> addElement ('submit','submit',get_lang('Ok'));
}

//Create the edit form.
if (($_GET['action'] == 'edit') || ($_POST['action'] == 'edit') || ($_GET['action'] == 'edited') || ($_POST['action'] == 'edited'))
{
	$form_edit_options = new FormValidator('course_edit_options','post',$_SERVER[SELF].'?action=edited&view='.$_GET['view']);
	$form_edit_options -> registerRule('valid_amount','regex','/^\d*\.{0,1}\d+$/');

//Several payment options seleceted to edit or already edited.
	if (($_POST['action'] == 'edit') || ($_POST['action'] == 'edited'))
	{

		$payment_options = $_POST['payment_options_selected'];
		foreach ($payment_options as $index => $option_id)	
		{
			$select_options[0] = $current_options[$option_id]['amount'].' '.$current_options[$option_id]['name'];
			$group_name='group_edit_'.$option_id;
			$group_name = array();		
			$group_name[] = $form_edit_options->createElement('static','','',get_lang('Set'));
			$group_name[] = $form_edit_options->createElement('text','credits',null,array('value'=>$current_options[$option_id]['credits'],'size'=>'8','maxlength'=>'8'));
			$group_name[] = $form_edit_options->createElement('static','','',get_lang('CreditsPer'));
			$group_name[] = $form_edit_options->createElement('select','payment_option','payment_option',$select_options);
			$form_edit_options -> addGroup ($group_name,'edited_group_'.$option_id);
			$form_edit_options -> addGroupRule('edited_group_'.$option_id,array(array(),array(array(get_lang('CreditAmountRequired'),'required'),array(get_lang('CreditAmountPositive'),'valid_amount'))));
			$form_edit_options -> addElement ('hidden','payment_options_selected['.$index.']',$option_id);
			$form_edit_options -> addElement ('hidden','action','edited');
		}
		$form_edit_options -> addElement ('hidden','multiple','true');
	
	}
	
//Only one payment option selected to edit.
	else
	{
		//The edit form is already submitted
		if (isset($_POST['option_id']))
		{
			$select_options[0] = $current_options[$_POST['option_id']]['amount'].' '.$current_options[$_POST['option_id']]['name'];
		}
		//The edit form is stil not submitted
		else $select_options[0] = $current_options[$_GET['option_id']]['amount'].' '.$current_options[$_GET['option_id']]['name'];
	
		$group_edit[] = $form_edit_options->createElement('static','','',get_lang('Set'));
		$group_edit[] = $form_edit_options->createElement('text','credits',null,array('value'=>$current_options[$_GET["option_id"]]["credits"],'size'=>'8','maxlength'=>'8'));
		$group_edit[] = $form_edit_options->createElement('static','','',get_lang('CreditsPer'));
		$group_edit[] = $form_edit_options->createElement('select','payment_option','payment_option',$select_options);
		$form_edit_options -> addGroup ($group_edit,'edited_group');
		$form_edit_options -> addGroupRule('edited_group',array(array(),array(array(get_lang('CreditAmountRequired'),'required',null),array(get_lang('Creditsmustbepositive'),'valid_amount',null))));
		//$form_edit_options -> addGroupRule('edited_group',array(array(),array(array(get_lang('CreditAmountRequired'),'required',null,'client'),array(get_lang('CreditAmountPositive'),'valid_amount',null,'client'),array(get_lang('CreditAmountRequired'),'nonzero',null,'client'))));	
		$form_edit_options -> addElement ('hidden','option_id',$_GET['option_id']);
		$form_edit_options -> addElement ('hidden','multiple','false');
			
	}
	$form_edit_options -> addElement ('submit','submit',get_lang('Ok'));
}


if (isset ($_GET['action']))
{
	switch ($_GET['action'])
	{
		// Delete selected courses
		case 'delete' :
		
			$error = false;
			
			if (isset ($_GET['option_id']))
			{
			
				$error = !(cs_delete_course_payment_option($_SESSION['_course']['sysCode'],$_GET['option_id']));
				if (!$error)
				{
					Display :: display_normal_message(get_lang('PaymentOptionDeleted'));
				}
				else Display :: display_normal_message(get_lang('PaymentOptionDeletedError'));
			}
			
			break;
			
		//Add a new payment option
		case 'add' :
		
			$form_new_options -> display();
				
			break;
		
		case 'edit' :
		
			$form_edit_options -> display();				
		
			break;
		
		case 'edited' :
		
			$error = false;
			
			if ($form_edit_options -> validate())
			{
				
				Display :: display_header($nameTools);
				//Add payment Options link
				echo '<img src="img/new_coins.gif" border="0" style="vertical-align: middle" title="'.get_lang("Edit").'" alt="'.get_lang("Edit").'"/> ';
				echo ' <a href="?action=add&view='.$_GET['view'].'">'.get_lang('AddPaymentOption').'</a>';
				
				$option = $form_edit_options->exportValues();
				
				//Check if the course has already a test option.
				$test_option_used = cs_course_have_test_option($_SESSION['_course']['sysCode']);
				
				if ($_POST['multiple'] == 'false')
				{
					//Users can not add more than one test option for the same course.
					if ($option['edited_group']['credits'] == 0 && $test_option_used)
					{
						Display::display_error_message(get_lang('TestPaymentOptionNotAllowed'));
					}
					else
					{
						$error = !cs_update_course_payment_option($_SESSION['_course']['sysCode'],$option['option_id'],$option['edited_group']['credits']);
						if (!$error)
						{	
							Display::display_normal_message(get_lang('PaymentOptionUpdated'));
						}
						else 
						{
							Display::display_error_message(get_lang('PaymentOptionUpdatedError'));	
						}				
					}
				}
				else
				{
	//First, we check if user modified correctly the payment options selected.				
					$payment_options_credits = '';
					$several_test_options = 'false';
										
					foreach ($_POST['payment_options_selected'] as $index => $option_id)
					{
						//Check if user selected two or more courses with 0 credits.
						if (in_array($option['edited_group_'.$option_id]['credits'],$payment_options_credits))
						{
							$several_test_options = 'true';
						}
						else
						{
							$payment_options_credits[]=	$option['edited_group_'.$option_id]['credits'];
						}

						if ($option['edited_group_'.$option_id]['credits'] == 0 && $test_option_used)
						{
							$test_option_not_allowed = 'true';
						}
						
					}
	// If User selected correct payment options, we stored them on thee database.
					if ($several_test_options == 'false' && $test_option_not_allowed == 'false')
					{
						foreach ($_POST['payment_options_selected'] as $index => $option_id)
						{
							if (!cs_update_course_payment_option($_SESSION['_course']['sysCode'],$option_id,$option['edited_group_'.$option_id]['credits']))
							{
								$error = true;	
							}
						}
						if (!$error)
						{
							Display::display_normal_message(get_lang('PaymentOptionsUpdated'));
						}
						else 
						{
							Display::display_error_message(get_lang('PaymentOptionUpdatedError'));	
						}
					}
					else if ($several_test_options == 'true')
						{
							Display::display_error_message(get_lang('SeveralTestPaymentOptions'));	
						}
						else Display::display_error_message(get_lang('TestPaymentOptionNotAllowed'));		
				}
			}
			else 
			{
				$interbreadcrumb[] = array ("url" => 'edit_pay_course.php?action=current&view='.$_GET['view'], "name" => get_lang('PaymentOptions'));
				$nameTools = get_lang('Edit');
				Display :: display_header($nameTools);
				$title = get_lang('editMenu');
				api_display_tool_title($title);
				$form_edit_options -> display();
				$form_error = 'true';
			}
			break;
	}
}

//Several options at the same time.
if (isset ($_POST['action']))
{
	switch ($_POST['action'])
	{
		// Delete selected courses
		case 'delete' :

			$payment_options_id = $_POST['payment_options_selected'];
			foreach ($payment_options_id as $index => $payment_option_id)
			{
				$error=cs_delete_course_payment_option(api_get_course_id(),$payment_option_id);
			}			
			Display::display_normal_message(get_lang('NumberOfOptionsDeleted').': '.count($payment_options_id));
			break;
			
		case 'add' :
			
			$error = false;
			
			if ($form_new_options -> validate())
			{
			
				Display :: display_header($nameTools);
				//Add Payment Options link.		
				echo '<img src="img/new_coins.gif" border="0" style="vertical-align: middle" title="'.get_lang("Edit").'" alt="'.get_lang("Edit").'"/> ';
				echo ' <a href="?action=add&view='.$_GET['view'].'">'.get_lang('AddPaymentOption').'</a>';
				
				$options = $form_new_options->exportValues();
				$payment_options_ids = '';
				$payment_options_credits = '';
	
	//First, we check if User set valid payment options.
	 			
	 			$test_option_used = cs_course_have_test_option($_SESSION['_course']['sysCode']);
	 			
				$same_option = 'false';
				$several_test_options = 'false';
			
				for ($j=2;$j<$options['num_options'];$j++)
				{
					//Check if the user set the same payment option several times
					if (in_array($options['option_group'.$j]['payment_option'],$payment_options_ids))
					{
						$same_option = 'true';
					}
					else
					{
						$payment_options_ids[]=	$options['option_group'.$j]['payment_option'];
					}
					//Check if User set a test option and the course already have one
					if ($options['option_group'.$j]['credits'] == 0 && $test_option_used)
					{
						$test_option_not_allowed = 'true';
					}					
					//Check if User set several test payment options
					if (in_array($options['option_group'.$j]['credits'],$payment_options_credits))
					{
						$several_test_options = 'true';
					}
					else
					{
						$payment_options_credits[]=	$options['option_group'.$j]['credits'];
					}					
				}
				
				if (in_array($options['add_option_group']['payment_option'],$payment_options_ids))
				{
					$same_option = 'true';
				}
				if (in_array($options['add_option_group']['credits'],$payment_options_credits))
				{
					$several_test_options = 'true';
				}
				
		//If User selected correct payment options, we stored them on thee database.				
				
				if ($same_option == 'false' && $several_test_options = 'false' && $test_option_not_allowed = 'fale')
				{
					for ($j=2;$j<$options['num_options'];$j++)
					{
						if (!cs_set_course_payment_option($_SESSION['_course']['sysCode'],$options['option_group'.$j]['credits'],$options['option_group'.$j]['payment_option']))
						{
							$error = true;
						}
					}
					if (!cs_set_course_payment_option($_SESSION['_course']['sysCode'],$options['add_option_group']['credits'],$options['add_option_group']['payment_option']))
					{
						$error = true;
					}
				
					$error?$display = get_lang('ErrorInsertingPaymentOptions'):$display = get_lang('ChangesStoredSuccessfully');
					Display::display_normal_message($display);
				}				
				else if ($same_option == 'true')
					{
						Display::display_normal_message(get_lang('PaymentOptionsRepeated'));
					}
					else if ($test_option_not_allowed = 'true')
						{
							Display::display_normal_message(get_lang('TestPaymentOptionNotAllowed'));
						}
						else Display::display_normal_message(get_lang('SeveralTestPaymentOptions'));
			}
			else
			{
				$interbreadcrumb[] = array ("url" => 'edit_pay_course.php?action=current&view='.$_GET['view'], "name" => get_lang('PaymentOptions'));
				$nameTools = get_lang('Add');
				Display :: display_header($nameTools);
				$title = get_lang('addMenu');
				api_display_tool_title($title);
				$form_new_options -> display();
				$form_error='true';
			}
			break;
			
		case 'edit' :

			$form_edit_options -> display();
			
			break;
	}
}


if(($_GET['action'] != 'add')&&($_GET['action'] != 'edit')&&($_POST['action'] != 'edit') && !$form_error)
{

		$parameters['action']='current';
		$parameters['view']=$_GET['view'];	

	$table = new SortableTable('current_payment_options','get_number_of_options', 'get_payment_data');
	
	$table->set_additional_parameters($parameters);
	$table->set_header(0, '', false);
	$table->set_header(1, get_lang('PeriodicityAmount'));
	$table->set_header(2, get_lang('Periodicity'));
	$table->set_header(3, get_lang('NumberOfCredits'));
	$table->set_header(4, '', false);
	$table->set_column_filter(4,'modify_filter');
	$table->set_form_actions(array ('delete' => get_lang('DeletePaymentOptions'), 'edit' => get_lang('EditPaymentOptions')),'payment_options_selected');
	$table->display();

}	
	

/*
==============================================================================
		FOOTER
==============================================================================
*/
Display :: display_footer();

?>