<?php // $Id: general_options.php,v 1.0 2006/03/15 14:34:45 poty $
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
*	This script displays and save the credits system general settings.
*
*	It dont display what is wrong when do not validate because the renderer.
*	In order to change this drop $renderer or add the setErrorTemplate to 
*	default renderer or use another renderer.
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
include_once(api_get_path('INCLUDE_PATH')."global.inc.php");

api_protect_admin_script(); 

/*
-----------------------------------------------------------
	Libraries
-----------------------------------------------------------
*/ 
require_once(api_get_path(LIBRARY_PATH).'/formvalidator/FormValidator.class.php');


/*
==============================================================================
		FUNCTIONS
==============================================================================
*/ 

/**
 * The function that retrieves all the possible settings for a certain config setting
 * @author Patrick Cool <patrick.cool@UGent.be>, Ghent University
*/
function get_settings_options($var)
{
	$table_settings_options = Database :: get_main_table(TABLE_MAIN_SETTINGS_OPTIONS);
	$sql = "SELECT * FROM $table_settings_options WHERE variable='$var'";
	$result = api_sql_query($sql, __FILE__, __LINE__);
	while ($row = mysql_fetch_array($result))
	{
		$temp_array = array ('value' => $row['value'], 'display_text' => $row['display_text']);
		$settings_options_array[] = $temp_array;
	}
	return $settings_options_array;
}

/**
 * This function return an array with all the possible payment options of the select.
 * 
 * @author Borja Nuñez Salinas
*/
function get_select_settings_options ($variable)
{
	$select[0] = get_lang('NoDefaultPO');
	if ($payment_options = cs_get_payment_options($variable))
	{
		foreach ($payment_options as $option_id => $values)
		{
		$select[$option_id] = $values['amount'].' '.$values['name']; 
		}
	}
	else
	{
		$select = array ('0'=>get_lang('NoPaymentOptions'));
	}
	return $select;
}

/*
==============================================================================
		MAIN CODE
==============================================================================
*/ 

$table_settings_current = Database :: get_main_table(TABLE_MAIN_SETTINGS_CURRENT);

$form = new FormValidator('general_options','post',api_get_path(PLUGIN_PATH).'credits_system_settings.php?select=csgeneraloptions');

$renderer = & $form->defaultRenderer();
$renderer->setHeaderTemplate('<div class="settingtitle">{header}</div>'."\n");
$renderer->setElementTemplate('<div class="settingcomment">{label}</div>'."\n".'<div class="settingvalue">{element}</div>'."\n");

$sqlsettings = "SELECT DISTINCT * FROM $table_settings_current WHERE scope='cs' GROUP BY variable ORDER BY id ASC";
$resultsettings = api_sql_query($sqlsettings, __FILE__, __LINE__);

while ($row = mysql_fetch_array($resultsettings))
	{
		$form->addElement('header', null, get_lang($row['title']));
		switch ($row['type'])
		{
			case 'textfield' :
				$form->addElement('text', $row['variable'], get_lang($row['comment']));
				$default_values[$row['variable']] = $row['selected_value'];
				break;
			case 'textarea' :
				$form->addElement('textarea', $row['variable'], get_lang($row['comment']));
				$default_values[$row['variable']] = $row['selected_value'];
				break;
			case 'radio' :
				$values = get_settings_options($row['variable']);
				$group = array ();
				foreach ($values as $key => $value)
				{
					$group[] = $form->createElement('radio', $row['variable'], '', get_lang($value['display_text']), $value['value']);
				}
				$form->addGroup($group, $row['variable'], get_lang($row['comment']), '<br />', false);
				$default_values[$row['variable']] = $row['selected_value'];
				break;
			case 'checkbox';
				$sql = "SELECT * FROM settings_current WHERE variable='".$row['variable']."'";
				$result = api_sql_query($sql, __FILE__, __LINE__);
				$group = array ();
				while ($rowkeys = mysql_fetch_array($result))
				{
					$element = & $form->createElement('checkbox', $rowkeys['subkey'], '', get_lang($rowkeys['subkeytext']));
					if ($rowkeys['selected_value'] == 'true' && ! $form->isSubmitted())
					{
						$element->setChecked(true);
					}
					$group[] = $element;
				}
				$form->addGroup($group, $row['variable'], get_lang($row['comment']), '<br />'."\n");
				break;
			case "select" :
				$values = get_select_settings_options($row['variable']);
				$form->addElement('select',$row['variable'],get_lang($row['comment']),$values);
				$default_values[$row['variable']] = $row['selected_value'];
				break;
			case "link" :
				$form->addElement('static', null, get_lang($row['comment']), get_lang('CurrentValue').' : '.$row['selected_value']);
		}
	}
//Add Rules
$form->registerRule('valid_amount','regex','/^\d*\.{0,1}\d+$/');

$form->addRule('cs_cost_per_credit',get_lang('UseNumericValuesPlz'),'valid_amount');
$form->addRule('cs_cost_per_credit',get_lang('EnterAnAmount'),'required');
$form->addRule('cs_cost_per_credit',get_lang('EnterAnAmount'),'nonzero');
$form->addRule('cs_cost_per_credit',get_lang('EnterAnAmount'),'maxlength','9');

$form->addRule('cs_default_payment_option_credits',get_lang('UseNumericValuesPlz'),'valid_amount');
$form->addRule('cs_default_payment_option_credits',get_lang('EnterAnAmount'),'required');
$form->addRule('cs_default_payment_option_credits',get_lang('EnterAnAmount'),'nonzero');
$form->addRule('cs_default_payment_option_credits',get_lang('EnterAnAmount'),'maxlength','9');

//$form->addRule('cs_payment_methods',get_lang('EnterAnAmount'),'required');


$form->addElement('submit', null, get_lang('Ok'));
$form->setDefaults($default_values);
	
if ($form->validate())
{
	$values = $form->exportValues();
	//Save last payment methods selected to load the its script if was not selected before.
	$sql = "SELECT subkey FROM $table_settings_current WHERE scope='cs' AND type='checkbox' AND variable='cs_payment_methods' AND selected_value = 'false'";
	$result = api_sql_query($sql, __FILE__, __LINE__);
	while ($row = mysql_fetch_array($result))
	{
		$load_script[$row['subkey']] = 'true';
	}

	// the first step is to set all the variables that have type=checkbox of the category
	// to false as the checkbox that is unchecked is not in the $_POST data and can
	// therefore not be set to false
	$sql = "UPDATE $table_settings_current SET selected_value='false' WHERE scope='cs' AND type='checkbox'";
	$result = api_sql_query($sql, __FILE__, __LINE__);
	// Save the settings
	foreach ($values as $key => $value)
	{
		if (!is_array($value))
		{
			$sql = "UPDATE $table_settings_current SET selected_value='".mysql_real_escape_string($value)."' WHERE variable='$key'";
			$result = api_sql_query($sql, __FILE__, __LINE__);
			if ($key == 'cs_allow_test' && mysql_real_escape_string($value) == 'no');
			{
				$table_course_credits = Database :: get_main_table(CS_TABLE_COURSE_CREDITS);
				$sql = "DELETE FROM $table_course_credits WHERE credits = 0";
				$result = api_sql_query($sql, __FILE__, __LINE__);
			}
		}
		else
		{
			foreach ($value as $subkey => $subvalue)
			{
				$sql = "UPDATE $table_settings_current SET selected_value='true' WHERE variable='$key' AND subkey = '$subkey'";
				$result = api_sql_query($sql, __FILE__, __LINE__);

				//Load selected payment methods settings script if was not loaded.
				if ($load_script[$subkey])
				{
					$sql_script = file_get_contents(dirname(__FILE__).'/DB_scripts/DB_'.$subkey.'_settings');
					$query = split(';', $sql_script);
					for ($i = 0; $i+1 < count($query); $i++)
					{
						$res = api_sql_query($query[$i], __FILE__, __LINE__) or die(mysql_error());
					}
				}
			}
		}
	}
	Display::display_confirmation_message(get_lang('SettingsStored'));
}

$form->display();
?>