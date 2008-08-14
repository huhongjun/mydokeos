<?php
require_once (api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php');
$subscribe_page = api_get_path(REL_CLARO_PATH).'auth/courses.php';
if($_SERVER['SCRIPT_NAME'] == $subscribe_page && isset($_GET['action']) && $_GET['action'] == 'subscribe' && isset($_POST['subscribe']))
{
	//The user wants to subscribe to a course...
	//So below you can add some code to check the payment first.
	//Or if everyting is okay, just let the user pass through and don't exit the application
	echo '</div></div></div>'; //Just closing some div's which were opened in the Dokeos header
	echo '<div><div>';	// ... and opening some new ones which we blocked by interupting the header
	Display::display_normal_message('You want to subscribe to course '.$_POST['subscribe'].'? This is a pay course.');
	
$form = new FormValidator('update_payment_methods','post',api_get_path(WEB_PATH).'main/auth/courses.php?action=subscribe&amp;category=LANG&amp;up=');
$select = array('0'=> 'Test 1 session = 0 credits',
				'1'=> '1 session = 10 credits',
				'2'=> '1 day = 15 credits', 
				'3'=> '1 week = 70 credits', 
				'4'=> '6 month = 1225 credits', 
				'5'=> '1 year = 2100 credits');
$form->addElement('select','Select Payment option: ','Select payment option: ',$select);
//fill options by db query (cs_payment_methods table).
$group = array();
$group[] = $form->createElement('checkbox', 'enable_paypal', '', '', 'paypal');
$group[] = $form->createElement('image','paypal',"http://localhost/plugin/credits_system/img/logo_paypal.gif");
$form->addGroup($group,'paypal','Select payment method: ');
$form->addElement('submit','Subscribe','Subscribe');
$form->display();
	
	Display::display_footer();
	exit;
}
?>
