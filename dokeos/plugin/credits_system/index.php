<?php // $Id: index.php 10675 2007/20/04 13:03:10 $
/*
==============================================================================
	Dokeos - elearning and course management software

	Copyright (c) E.U.I. Universidad Politécnica de Madrid (Spain)

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
$language_file = 'plugin_credits_system';

	if ($user_id = api_get_user_id())
	{
		require_once(dirname(__FILE__).'/inc/cs_functions.inc.php');
		cs_check_database();
		echo '<a href="'.api_get_path(WEB_PLUGIN_PATH).'credits_system/my_credits.php">My Credits ('.cs_get_user_credits($user_id).')</a>';
		require_once(dirname(__FILE__).'/inc/check_subscription.inc.php');
		require_once(dirname(__FILE__).'/inc/check_access.inc.php');
	}
?>