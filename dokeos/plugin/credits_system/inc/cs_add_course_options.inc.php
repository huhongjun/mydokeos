<?php
/*
 * Created on 14/03/2007
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
// include_once('../../../main/inc/global.inc.php');
//echo $_SESSION['_course']['sysCode'];
//$course_settings_page = api_get_path(REL_CLARO_PATH).'course_info/infocours.php';
//$add_course_options_page = api_get_path(WEB_PLUGIN__PATH).'credits_system/inc/add_course_options.inc.php';
//if($_SERVER['SCRIPT_NAME'] == $course_settings_page) {//|| ($_SERVER['SCRIPT_NAME'] == $add_course_options_page)){

//$language_file = "plugin_credits_system";

//include_once('../../../main/inc/global.inc.php');
//require_once(api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php');
//require_once(dirname(__FILE__).'/cs_functions.inc.php');
//require_once(dirname(__FILE__).'/cs_database.lib.php');

/*
-----------------------------------------------------------
	Header
	include the HTTP, HTML headers plus the top banner
-----------------------------------------------------------
*/
//$nameTools = get_lang('MyCourses');
//require_once(dirname(__FILE__).'/header2_and_3.php');
api_block_anonymous_users();
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

//Display :: display_header($nameTools);


//,api_get_path(WEB_PLUGIN_PATH).'/credits_system/inc/test.php'

$form_options = new FormValidator('course_new_options','post',$_SERVER[SELF].'?action='.$_GET['action'].'&payment_options=manage&num='.$_GET['num'].'&code='.$_SESSION['_course']['sysCode']);


// Current course payment options.
$form_options -> addElement ('static','','','Current Options');
$course_payment_options = cs_get_course_payment_options($_SESSION['_course']['sysCode']);

foreach ($course_payment_options as $key => $value){
	
		//echo $key.'->'.$value.'';
		$group_current[] = $form_options -> createElement('checkbox',$key,'',$value['credits'].'&nbsp;&nbsp; '.'Credits Per&nbsp;&nbsp;'.$value['amount'].' '.$value['name']);
		$group_current[] = $form_options -> createElement ('static','','','<br />');
}

$form_options -> addGroup($group_current,'current_options_group');
$form_options -> addElement ('static','','','');

// New course payment options.
$form_options -> addElement ('static','','','New Options');
$new_options = cs_get_course_possible_payment_options($_SESSION['_course']['sysCode']);

/*foreach ($new_options as $key => $value){
	
		echo $new_options[$key].'->'.eval($new_options[$value]).'<br />';
		
}*/

$select_options[0]=get_lang('SelectPaymentOption');
foreach ($new_options as $key => $value){
	
		//echo $key.'->'.$value.'';
		$select_options[$key]=$value['amount'].' '.get_language_unit($value['name']);//by xiaoping
//		$group_current[] = $formOptions -> createElement('checkbox',$course_payment_options[$key]['option_id'],'',$course_payment_options[$key]['credits'].'&nbsp;&nbsp; '.get_lang('CreditsPer').'&nbsp;&nbsp;'.$course_payment_options[$key]['amount'].' '.$course_payment_options[$key]['name']);
//		$group_current[] = $formOptions -> createElement ('static','','','<br />');
}

//More than one option.
if ($_GET['action'] == 'add'){
	
	$options_num= $_GET['num'];
	
	$group_add[] = $form_options->createElement('static','','','Set');
	$group_add[] = $form_options->createElement('text','credits','number_of_credits[]','size="4"');
	$group_add[] = $form_options->createElement('static','','','Credits Per');
	//$group_add[] = $form_options->createElement('text','periodAmount','period_amount[]','size="4"');
	$group_add[] = $form_options->createElement('select','payment_option','payment_option',$select_options);
	

	for ($i=1;$i<=$options_num;$i++){
		$option_name = 'option_group'.$i;
		$form_options -> addGroup($group_add,$option_name);
	}		

}
else $options_num=0;

$options_num++;


// Option with the add-option-link possibility.
$group[] = $form_options->createElement('static','','',get_lang('Set'));
$group[] = $form_options->createElement('text','credits','number_of_credits[]','size="4"');
$group[] = $form_options->createElement('static','','','Credits Per');
//$group[] = $formOptions->createElement('text','periodAmount','period_amount[]','size="4"');
$group[] = $form_options->createElement('select','payment_option','payment_option',$select_options);
$group[] = $form_options->createElement('link','add_option','',$_SERVER[SELF].'?action=add&payment_options=manage&num='.$options_num.'&code='.$_SESSION['_course']['sysCode'],'Add Option');

$form_options -> addGroup ($group,'add_option_group');

$form_options -> addElement ('hidden','num_options',$options_num); 
$form_options -> addElement ('hidden','course_id',$_SESSION['_course']['sysCode']);
$form_options -> addElement ('submit','submit',get_lang('ok'));


if ($form_options->validate()){
	
	$options = $form_options->exportValues();
 
	//echo'Options selected for this course <br />';
	
	$text='Options deleted: <br />';
	/*foreach ($options as $key=> $value){
		echo $key.' -> '.$value.'<br />';
	}*/
	foreach ($options['current_options_group'] as $key=> $value){
		$text.= 'DELETED option: '.$key.'<br />';
		$error=cs_delete_course_payment_option($options['course_id'],$key);
	}

	$text.='New payment options for this course: <br />';
	
	if ($options['add_option_group'.$j]['payment_option']){
		$text.='Amount: '.$options['add_option_group']['credits'].'   Payment Option ID: '.$options['add_option_group']['payment_option'].'<br />';
		$error=cs_set_course_payment_option($options['course_id'],$options['add_option_group']['credits'],$options['add_option_group']['payment_option']);
/*	foreach ($options['add_option_group'] as $key=> $value){
		echo $key.' -> '.$value.'<br />';
	}*/
	}
	for ($j=1;$j<$options['num_options'];$j++){
		
		if ($options['option_group'.$j]['payment_option']){
			$text.='Amount: '.$options['option_group'.$j]['credits'].'   Payment Option ID: '.$options['option_group'.$j]['payment_option'].'<br />';
			$error=cs_set_course_payment_option($options['course_id'],$options['option_group'.$j]['credits'],$options['option_group'.$j]['payment_option']);			
		}
	}
	Display::display_normal_message('Your changes has benn succesfully stored on the Database');
	echo '<div id="myprofilefooter">';
	$link_form = new FormValidator('redirection','post',$_SERVER[SELF].'?payment_options=finished');
	//api_get_path(REL_CLARO_PATH).'auth/courses.php
	$link_group[] = $link_form -> createElement('link','link1','',$_SERVER[SELF].'?payment_options=manage', '- Course Payment Options -');
	$link_group[] = $link_form -> createElement('link','submit','','javascript:document.redirection.submit()','- Course Settings -');

	$link_form -> addGroup ($link_group,'linkGroup');

	$link_form -> addElement ('hidden','cs_pay',$_SESSION['_course']['sysCode']);
	//$form -> addElement ('hidden',)
		
	$link_form -> display(); 
}

if (!isset($_POST['num_options'])) 
	$form_options -> display();

/*
==============================================================================
		FOOTER
==============================================================================
*/
//Display :: display_footer();
//exit;


?>
