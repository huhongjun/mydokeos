<?php
$subscribe_page = api_get_path(WEB_PLUGIN_PATH).'credits_system/my_credits.php';
if($_SERVER['SCRIPT_NAME'] == $subscribe_page)
{
	if(api_is_platform_admin())
	{
	}
	Display::display_footer();
	exit;
}
?>