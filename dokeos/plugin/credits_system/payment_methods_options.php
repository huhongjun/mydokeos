<?php // $Id: payment_methods_options.php,v 1.0 2006/03/15 14:34:45 poty $
/*
==============================================================================
	Dokeos - elearning and course management software
	
	Copyright (c) 2004-2006 Dokeos S.A.
	Copyright (c) E.U.I. Universidad Politécnica de Madrid (Spain)
	Copyright (c) Borja Nuñez Salinas - Programmer (bns@alumnos.upm.es)
	
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
*	This script display the enabled payment methods options.
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

/*
-----------------------------------------------------------
	Libraries
-----------------------------------------------------------
*/ 
require_once(api_get_path(LIBRARY_PATH).'/formvalidator/FormValidator.class.php');
	
/*
==============================================================================
		MAIN CODE
==============================================================================
*/ 

$table_settings_current = Database :: get_main_table(TABLE_MAIN_SETTINGS_CURRENT);

$form = new FormValidator('payment_methods_options','post',api_get_path(PLUGIN_PATH).'credits_system_settings.php?select=cspaymentmethodsoptions');

$renderer = & $form->defaultRenderer();
$renderer->setHeaderTemplate('<div class="settingtitle">{header}</div>'."\n");
$renderer->setElementTemplate('<div class="settingcomment">{label}</div>'."\n".'<div class="settingvalue">{element}</div>'."\n");

$sqlsettings = "SELECT * FROM $table_settings_current WHERE scope='cs_pm_settings' ORDER BY variable ASC";
$resultsettings = api_sql_query($sqlsettings, __FILE__, __LINE__);

$current_settings = cs_get_current_settings();
for($i=0; $i < count($current_settings['cs_payment_methods']); $i++)
{
	$payment_Methods['cs_'.$current_settings['cs_payment_methods'][$i]['subkey']] = $current_settings['cs_payment_methods'][$i]['selected_value'];
}

$no_options = true;
while ($row = mysql_fetch_array($resultsettings))
	{
		if ($payment_Methods[$row['variable']] == 'true')
		{
			$no_options = false;
			if($row['variable'] != $prev_variable)
			{
				$form->addElement('header', null, get_lang($row['title']));
			}
			$form->addElement('text', $row['subkey'], get_lang($row['comment']));
			$default_values[$row['subkey']] = $row['selected_value'];
			$prev_variable = $row['variable'];
		}
	}

$form->addElement('submit', null, get_lang('Ok'));
$form->setDefaults($default_values);

if ($form->validate())
{
	$values = $form->exportValues();
	// Save the settings
	foreach ($values as $key => $value)
	{
			$sql = "UPDATE $table_settings_current SET selected_value='".mysql_real_escape_string($value)."' WHERE subkey='$key'";
			$result = api_sql_query($sql, __FILE__, __LINE__);
	}
	Display::display_confirmation_message(get_lang('SettingsStored'));
}

if($no_options)
{
	Display::display_normal_message(get_lang('NoOptionsAvailable'));
}
else
{
	$form->display();
}
?>
