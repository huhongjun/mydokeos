<?php
/*
 * Created on 14/03/2007
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 $cidReset = true;

$language_file = "plugin_credits_system";

include_once('../../main/inc/global.inc.php');
require_once(api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php');
require_once(dirname(__FILE__).'/inc/cs_functions.inc.php');
require_once(dirname(__FILE__).'/inc/credits_database.lib.php');

/*
-----------------------------------------------------------
	Header
	include the HTTP, HTML headers plus the top banner
-----------------------------------------------------------
*/
$nameTools = get_lang('MyCourses');
api_block_anonymous_users();
Display :: display_header($nameTools);


$form = new FormValidator('course_option','post',api_get_path(WEB_PLUGIN_PATH).'/credits_system/inc/test.php');

// More than one option.
if ($_GET['action'] == 'add'){
	
	$options_num= $_GET['num'];
	
	$group_add[] = $form->createElement('static','','',get_lang('Set'));
	$group_add[] = $form->createElement('text','credits','number_of_credits[]','size="4"');
	$group_add[] = $form->createElement('static','','',get_lang('CreditsPer'));
	$group_add[] = $form->createElement('text','PeriodAmount','period_amount[]','size="4"');
	$group_add[] = $form->createElement('select','','number_of_credits[]',array('days','weeks','months','years'));
	

	for ($i=1;$i<=$options_num;$i++){
		$optionName = 'optionGroup'.$i;
		$form -> addGroup($group_add,$optionName);
	}		

	/*$_SESSION[_user][options_num]++;
	for ($i=1;$i<=$_SESSION[_user][options_num];$i++){
		$form -> addGroup($group_add,'optionGroup');
	}*/
	
}
else $options_num=0;//$_SESSION[_user][options_num]=0;

$options_num++;
$group[] = $form->createElement('static','','',get_lang('Set'));
$group[] = $form->createElement('text','credits','number_of_credits[]','size="4"');
$group[] = $form->createElement('static','','',get_lang('CreditsPer'));
$group[] = $form->createElement('text','periodAmount','period_amount[]','size="4"');
$group[] = $form->createElement('select','','number_of_credits[]',array('days','weeks','months','years'));
$group[] = $form->createElement('link','addOption','',api_get_path(WEB_PLUGIN_PATH).'credits_system/add_course_options.php?action=add&num='.$options_num,'Add Option');

//$group_add[] = $form->createElement('link','addOption','',api_get_path(WEB_PLUGIN_PATH).'credits_system/test.php?action=add&num='.$options_num,'Add Option');


// Option with the add-option-link possibility.
$form -> addGroup ($group,'addOptionGroup');

$form -> addElement ('submit','submit',get_lang('ok'));	
$form -> addElement ('hidden','numOptions',$options_num);

//$form -> addElement ('hidden','numOptions',$options_num); 
//$form -> addElement ('submit','submit',get_lang('ok'));


if ($form->validate()){
	
	$options = $form->exportValues();
 
	/*echo'Options selected for this course <br />';
	
	echo 'Amount: '.$options['addOptionGroup']['credits'].'Period: '.$options['addOptionGroup']['periodAmount'].'<br />';*/
	foreach ($options as $key=> $value){
		echo $key.' -> '.$value.'<br />';
	}
	/*foreach ($options['addOptionGroup'] as $key=> $value){
		echo $key.' -> '.$value.'<br />';
	}
	for ($j=1;$j<=$options['numOptions'];$j++){
		
		echo 'Amount: '.$options['optionGroup'.$j]['credits'].'Period: '.$options['optionGroup'.$j]['periodAmount'].'<br />';
	} */

}
$form -> display();




?>
