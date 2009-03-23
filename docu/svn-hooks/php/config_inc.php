<?php
	$g_hostname = 'localhost';
	$g_db_type = 'mysql';
	$g_database_name = 'bugtracker';
	$g_db_username = 'root';
	$g_db_password = '';
	/*以下四行为与mantis集成而添加的SVN用户 By Wuhongbo*/
	$g_source_control_notes_view_status = VS_PUBLIC;
 	$g_source_control_account = 'SVN';
    $g_source_control_set_status_to = OFF;
    $g_source_control_regexp = "/\bissue [#]{0,1}(\d+)\b/i";
?>