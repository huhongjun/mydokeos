<?php
DEFINE ('CS_TABLE_PAYMENT_OPTION','cs_payment_option');
DEFINE ('CS_TABLE_USER_CREDITS','cs_user_credits');
DEFINE ('CS_TABLE_COURSE_CREDITS','cs_course_credits');
DEFINE ('CS_TABLE_PAYMENT','cs_payment');
DEFINE ('CS_TABLE_COURSE_CREDITS_REL_USER_CREDITS','cs_subscriptions');
DEFINE ('CS_TABLE_OPTIONS_HISTORY','cs_options_history');

//Add enabled payment methods databases.
require_once(dirname(__FILE__).'/cs_functions.inc.php');
$payment_methods = cs_get_current_settings('cs_payment_methods');
for ($i=0; $i < count($payment_methods['cs_payment_methods']); $i++)
{
	DEFINE ('CS_TABLE_'.strtoupper($payment_methods['cs_payment_methods'][$i]['subkey']).'_PAYMENT_INFO','cs_'.$payment_methods['cs_payment_methods'][$i]['subkey'].'_payment_info');
}

include_once(api_get_path(LIBRARY_PATH).'database.lib.php');
?>
