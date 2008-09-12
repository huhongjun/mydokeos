<?php // $Id: subscription.php,,v 1.0 2006/03/15 14:34:45 poty $
/*
==============================================================================
	Dokeos - elearning and course management software
	
	Copyright (c) 2004-2006 Dokeos S.A.
	Copyright (c) E.U.I. Universidad Politï¿?cnica de Madrid (Spain)
	Copyright (c) Borja Nuï¿?ez Salinas - Programmer (bns@alumnos.upm.es)
	
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
*	This script display the payment options availables for the credit course
*	selected, subscribe the user with the payment option selected and update
*	his credits.
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
include_once('../../main/inc/global.inc.php');
$this_section=SECTION_COURSES;

api_block_anonymous_users();

/*
-----------------------------------------------------------
	Libraries
-----------------------------------------------------------
*/
include_once(api_get_path(LIBRARY_PATH) . 'course.lib.php');
require_once (api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php');
require_once(dirname(__FILE__).'/inc/cs_functions.inc.php');

/*
-----------------------------------------------------------
	Header
-----------------------------------------------------------
*/ 

$toolname = get_lang('PayCreditsCourse');
	
/*
==============================================================================
		MAIN CODE
==============================================================================
*/ 

if(isset($_GET['paycourse']) && isset($_GET['subscribe']))
{

//The user want to pay a course.
//check if want to pay a course which is already subscribed or want to subscribe to a course.
$subscribe = $_GET['subscribe'];
		
//Get Course Info.
$course_info =  CourseManager::get_course_information($_GET['paycourse']);

//Get the course payment options and credits.
$option = cs_get_course_payment_options($_GET['paycourse']);

$user_dont_pay = ! cs_can_user_access(api_get_user_id(),$_GET['paycourse']);

//Get current page.
$current_page = $_SERVER['PHP_SELF'].'?';
	
//set current page with $_GET variables to continue to next page.
foreach ($_GET as $key => $value)
{
	$get_variables .= $key.'='.$value.'&';
}

$current_page = $current_page.$get_variables;

$form = new FormValidator('cs_pay','post',$current_page);
			
//Adding show just possible payment options
$select =& $form->addElement('select','option',get_lang('Selectapaymentoption').':');
$user_credits = cs_get_user_credits();
$options = count($option);
if(!$form->validate())
{
	foreach ($option as $option_id => $values)
	{
		//Build payment option text.
		if ($values['credits'] == 0)
		{
			$option_text = get_lang('Testcourse')." ";
			$option_text .= $values['amount'].' '.$values['name'];
			//$option_text .= ($values['amount'] > 1)?'s ':' '; by xiaoping
			$option_text .= " ".get_lang('forfree').".";
		}
		else
		{
			$option_text = $values['amount'].' ';
			$option_text .= $values['name'];//$option_text .= ($values['amount'] > 1)?get_lang($values['name'].'s'):get_lang($values['name']);by xiaoping
			$option_text .= ' = ';
			$option_text .= ($values['credits'] - floor($values['credits']) == 0)?floor($values['credits']):$values['credits'];
			$option_text .= ' ';
			$option_text .= get_lang('Credits');//$option_text .= ($values['credits'] > 1)?get_lang('Credits').'.':get_lang('Credit').'.';by xiaoping
		}
		
		
		//add option Enabled or not depending on $user_credits.
		if ($user_credits >= $values['credits'])
		{
			if ($values['credits'] > 0 || ( $values['credits'] == 0 &&(cs_get_current_settings('cs_allow_test') == 'yes' && ! cs_user_last_sub_paid(api_get_user_id(),$_GET['paycourse']))))
			{//It is not a test option or it is a test option and test options are enabled and user was never subscribed to this course.
				$select->addOption($option_text, $option_id);
			}			
		}
		else
		{
			$options--;
			$select->addOption($option_text, $option_id, 'disabled');
			$unavailable_options[] = $option_text;
		}
	}
}
if ($options != count($option))
{
	//User have not enought credits for, at least, 1 option.
	$to_buy_form = new FormValidator('buy','post',api_get_path(WEB_PLUGIN_PATH).'/credits_system/buy_credits.php?category='.$_GET['category']);
	$to_buy_form->addElement('hidden','go_back',$current_page);
	if ($options)
	{//User have not enought credits to select all possible payment options. Link to buy credits shown.
		$to_buy_form->addElement('static','',get_lang('NotAllOptions').' <a href="javascript:document.buy.submit();">'.get_lang('BuyCredits').'</a>','');
	}
	else
	{//User have not enought credits to select any payment option. Link to buy credits and course info shown.
		//Fill interbreadcrumb.
		if ($subscribe)
		{
			$back_page = api_get_path(REL_CLARO_PATH).'auth/courses.php?action=subscribe&category='.$_GET['category'].'&up=';
			$interbreadcrumb[] = array ("url" => $back_page, "name" => get_lang('CourseManagement'));
			$interbreadcrumb[] = array ("url" => '#', "name" => get_lang('Subscribe'));
		}
		else
		{
			$back_page = api_get_path(WEB_PATH).'user_portal.php';
			$interbreadcrumb[] = array ("url" => $back_page, "name" => get_lang('MyCourses'));
			$interbreadcrumb[] = array ("url" => '#', "name" => get_lang('RenewSubscription'));						
		} 
		Display::display_header($tool_name);
		
		Display::display_warning_message(get_lang('NoCredits').'. '.get_lang('Please').' <a href="'.$back_page.'">'.get_lang('SelectAnotherCourse').'</a> '.get_lang('or').' <a href="javascript:document.buy.submit();">'.get_lang('BuyCredits').'</a>',false);
				
		//show course info and payment options.
		echo'<br/><br/><h3>'.get_lang('CourseInfo').':</h3>';
		echo '<b>'.get_lang('CourseTitle').'</b>: '.$course_info['title'].'<br/><b>'.get_lang('Code').'</b>: '.$course_info['code'].'<br/><b>'.get_lang('Tutor').'</b>:'.$course_info['tutor_name'];
		echo'<h4>'.get_lang('PaymentOptions').':</h4>';
		for ($i=0; $i < count($unavailable_options); $i++)
		{
			echo $unavailable_options[$i].'<br />';
		}
		
		$to_buy_form->display();
		Display::display_footer();
		exit;
	}
}
// END Adding show just possible payment options

$form->addElement('submit','Confirm',get_lang('Confirm'));

if ($subscribe)
{
	$form->addElement('link','back_subscribe','',api_get_path(REL_CLARO_PATH).'auth/courses.php?action=subscribe&category='.$_GET['category'].'&up=',get_lang('Backtosubscribe'));
}								
	
if ($form->validate())
{
	$selected_payment_option = $form->exportValues();	
	//Check, again, if user have enought credits to subscribe this option.
	if ((cs_get_user_credits() - $option[$selected_payment_option['option']]['credits']) >= 0)
	{
		//Subscribe user to this course in credit system.
		$res = cs_subscribe_user($_GET['paycourse'],$selected_payment_option['option'],$option[$selected_payment_option['option']]['amount'],$option[$selected_payment_option['option']]['name']);
		
		//Update User Credits.
		cs_set_user_credits(cs_get_user_credits() - $option[$selected_payment_option['option']]['credits']);
				
		if($res && $subscribe)//IF NO ERROR ON subscribe user to this course in credit system and user want to subscribe.
		{
			echo get_lang('loadingPlzWait').'...';
			//load the subscription page.
			$to_subscribe_form = new FormValidator('to_subscribe_form','post',api_get_path(WEB_PATH).'/main/auth/courses.php?action=subscribe&category='.$_GET['category'].'&up=&paycourse=paid');
			$to_subscribe_form->addElement('hidden','subscribe',$_GET['paycourse']);
			$to_subscribe_form->display();
			echo'<script type="text/javascript">document.to_subscribe_form.submit();</script>';
			exit;
		}
		elseif($res)
		{
			//load the course main page.
			echo'<script type="text/javascript">document.location.href="'.api_get_path(WEB_COURSE_PATH).$course_info['directory'].'/?id_session=0";</script>';
			exit;
		}else
		{
			Display::display_header($tool_name);
			Display::display_error_message(get_lang('Error').'<br/><a href="javascript:history.go(-1)">'.get_lang('GoBack').'</a>',false);
			Display::display_footer();
			exit;
		}
			
		}else
		{
			//Fill interbreadcrumb.
			if ($subscribe)
			{
				$back_page = api_get_path(REL_CLARO_PATH).'auth/courses.php?action=subscribe&category='.$_GET['category'].'&up=';
				$interbreadcrumb[] = array ("url" => $back_page, "name" => get_lang('CourseManagement'));
				$interbreadcrumb[] = array ("url" => '#', "name" => get_lang('Subscribe'));
			}
			else
			{
				$back_page = api_get_path(WEB_PATH).'user_portal.php';
				$interbreadcrumb[] = array ("url" => $back_page, "name" => get_lang('MyCourses'));
				$interbreadcrumb[] = array ("url" => '#', "name" => get_lang('RenewSubscription'));						
			} 
			Display::display_header($tool_name);
			
			$to_buy_form = new FormValidator('buy','post',api_get_path(WEB_PLUGIN_PATH).'/credits_system/buy_credits.php?category='.$_GET['category']);
			$to_buy_form->addElement('hidden','go_back',$current_page);
			$to_buy_form->display();
			Display::display_warning_message(get_lang('NoCredits').'. '.get_lang('Please').' <a href="'.$back_page.'">'.get_lang('SelectAnotherCourse').'</a> '.get_lang('or').' <a href="javascript:document.buy.submit();">'.get_lang('BuyCredits').'</a>',false);
			Display::display_footer();
			exit;
		}
}else
{//Fill interbreadcrumb.
	if ($subscribe)
	{
		$back_page = api_get_path(REL_CLARO_PATH).'auth/courses.php?action=subscribe&category='.$_GET['category'].'&up=';
		$interbreadcrumb[] = array ("url" => $back_page, "name" => get_lang('CourseManagement'));
		$interbreadcrumb[] = array ("url" => '#', "name" => get_lang('Subscribe'));
	}
	else
	{
		$back_page = api_get_path(WEB_PATH).'user_portal.php';
		$interbreadcrumb[] = array ("url" => $back_page, "name" => get_lang('MyCourses'));
		$interbreadcrumb[] = array ("url" => '#', "name" => get_lang('RenewSubscription'));						
	} 
	Display::display_header($tool_name);

	//show course info and payment options.
	echo'<br/><br/><h3>'.get_lang('CourseInfo').':</h3>';
	echo '<b>'.get_lang('CourseTitle').'</b>: '.$course_info['title'].'<br/><b>'.get_lang('Code').'</b>: '.$course_info['code'].'<br/><b>'.get_lang('Tutor').'</b>:'.$course_info['tutor_name'];
	echo'<h4>'.get_lang('PaymentOptions').':</h4>';
	$form->display();
	if(isset($to_buy_form))	
	{
		$to_buy_form->display();
	}
}
}
else
{//No course selected.
	Display::display_header($tool_name);
	Display::display_error_message(get_lang('Error').'<br/><a href="javascript:history.go(-1)">'.get_lang('GoBack').'</a>',false);
}

/*
==============================================================================
		FOOTER 
==============================================================================
*/ 
Display::display_footer();
?>
