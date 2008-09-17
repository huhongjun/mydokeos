<?php
// $Id: index.php 8216 2006-11-3 18:03:15 NushiFirefox $
/*
==============================================================================
	Dokeos - elearning and course management software

	Copyright (c) 2006 Bart Mollet <bart.mollet@hogent.be>

	For a full list of contributors, see "credits.txt".
	The full license can be read in "license.txt".

	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	See the GNU General Public License for more details.

	Contact: Dokeos, 181 rue Royale, B-1000 Brussels, Belgium, info@dokeos.com
==============================================================================
*/
$language_file = 'plugin_credits_system';
/*
==============================================================================
		INIT SECTION
==============================================================================
*/
require_once (api_get_path(LIBRARY_PATH).'/formvalidator/FormValidator.class.php');

/*
-----------------------------------------------------------
	Libraries
-----------------------------------------------------------
*/
include_once(dirname(__FILE__).'/inc/cs_database.lib.php');

/**
==============================================================================
* This class provides some functions for statistics
* @package dokeos.statistics
==============================================================================
*/
class CreditSystem
{
	function make_size_string($size) {
		if ($size < pow(2,10)) return $size." bytes";
		if ($size >= pow(2,10) && $size < pow(2,20)) return round($size / pow(2,10), 0)." KB";
		if ($size >= pow(2,20) && $size < pow(2,30)) return round($size / pow(2,20), 1)." MB";
		if ($size > pow(2,30)) return round($size / pow(2,30), 2)." GB";
	}
	
	function rescale($data, $max = 500)
	{
		$data_max = 1;
		foreach ($data as $index => $value)
		{
			$data_max = ($data_max < $value ? $value : $data_max);
		}
		reset($data);
		$result = array ();
		$delta = $max / $data_max;
		foreach ($data as $index => $value)
		{
			$result[$index] = (int) round($value * $delta);
		}
		return $result;
	}
	
	function print_stats2($title, $stats, $show_total = true, $is_file_size = false)
	{
		$total = 0;
		$data = CreditSystem::rescale($stats);
		echo '<table class="data_table" cellspacing="0" cellpadding="3">
			  		  <tr><th colspan="'.($show_total ? '4' : '3').'">'.$title.'</th></tr>';
		$i = 0;
		foreach($stats as $subtitle => $number)
		{
			$total += $number;
		}
		foreach ($stats as $subtitle => $number)
		{
			$i = $i % 13;
			if (strlen($subtitle) > 30)
			{
				$subtitle = '<acronym title="'.$subtitle.'">'.substr($subtitle, 0, 27).'...</acronym>';
			}
			if(!$is_file_size)
			{
				$number_label = number_format($number, 0, ',', '.');
			}
			else
			{
				$number_label = CreditSystem::make_size_string($number);
			}
			echo '<tr class="row_'.($i%2 == 0 ? 'odd' : 'even').'">
								<td width="150">'.$subtitle.'</td>
								<td width="550">
						 			<img src="../../main/img/bar_1u.gif" width="'.$data[$subtitle].'" height="10"/>
								</td>';
								if($show_total)
								{
									echo '<td align="right"> '.number_format(100*$number/$total, 1, ',', '.').'%</td>';
								}
								echo'<td align="right">'.$number_label.'</td>';
			
			echo '</tr>';
			$i ++;
		}
		if ($show_total)
		{
			if(!$is_file_size)
			{
				$total_label = number_format($total, 0, ',', '.');
			}
			else
			{
				$total_label = CreditSystem::make_size_string($total);
			}
			echo '<tr><th  colspan="4" align="right">'.get_lang('Total').': '.$total_label.'</td></tr>';
		}
		echo '</table>';
	}
	
	function print_credits_users_stats($type)
	{
		$payment_table = Database :: get_main_table(CS_TABLE_PAYMENT);
		$main_user_table = Database :: get_main_table(TABLE_MAIN_USER);
		//$period = get_lang('AmountCreditsMonth');
		$sql = "SELECT DATE_FORMAT(date, '%M') AS stat_date, firstname AS name, lastname AS last_name, SUM(credits) AS amount_of_credits FROM $payment_table T1,$main_user_table T2 WHERE (T1.user_id = T2.user_id) AND (date LIKE '$type%') GROUP BY stat_date,name, last_name ORDER BY  amount_of_credits";
		$res = api_sql_query($sql,__FILE__,__LINE__);
		$result = array();
		//echo sql;
		while($obj = mysql_fetch_object($res))
		{
			$result[$obj->last_name.' '.$obj->name] = $obj->amount_of_credits;//zml edit 
			$period = $obj->stat_date;
			//zml edit
		    if($period == "January"){
				$period = get_lang('January');
			}elseif($period == "February"){
				$period = get_lang('February');
			}elseif($period == "March"){
				$period = get_lang('March');
			}elseif($period == "April"){
				$period = get_lang('April');
			}elseif($period == "May"){
				$period = get_lang('May');
			}elseif($period == "June"){
				$period = get_lang('June');
			}elseif($period == "July"){
				$period = get_lang('July');
			}elseif($period == "Aguest"){
				$period = get_lang('Aguest');
			}elseif($period == "September"){
				$period = get_lang('September');
			}elseif($period == "October"){
				$period = get_lang('October');
			}elseif($period == "November"){
				$period = get_lang('November');
			}elseif($period == "December"){
				$period = get_lang('December');
			}
		}
			
		CreditSystem::print_stats2(get_lang('CreditsUsers').' ('.$period.')',$result,true);
	}
	
	function print_stats($title, $stats, $show_total = true, $is_file_size = false)
	{
		$total = 0;
		$data = CreditSystem::rescale($stats);
		echo '<table class="data_table" cellspacing="0" cellpadding="3">
			  		  <tr><th colspan="'.($show_total ? '4' : '3').'">'.$title.'</th></tr>';
		$i = 0;
		foreach($stats as $subtitle => $number)
		{
			$total += $number;
		}
		foreach ($stats as $subtitle => $number)
		{
			$i = $i % 13;
			if (strlen($subtitle) > 30)
			{
				$subtitle = '<acronym title="'.$subtitle.'">'.substr($subtitle, 0, 27).'...</acronym>';
			}
			if(!$is_file_size)
			{
				$number_label = number_format($number, 0, ',', '.');
			}
			else
			{
				$number_label = CreditSystem::make_size_string($number);
			}
			echo '<tr class="row_'.($i%2 == 0 ? 'odd' : 'even').'">
								<td width="150">'.$subtitle.'</td>
								<td width="550">
						 			<img src="../../main/img/bar_1u.gif" width="'.$data[$subtitle].'" height="10"/>
								</td>';
								if($show_total)
								{
									echo '<td align="right"> '.number_format(100*$number/$total, 1, ',', '.').'%</td>';
								}
								echo'<td align="right">'.$number_label.' <a href="menu_statistics.php?action=users&type='.$subtitle.'"><img src="../../main/img/synthese_view.gif" border="0" style="vertical-align: middle" title="'.get_lang('Info').'" alt="'.get_lang('Info').'"/></a></td>';
			echo '</tr>';
			$i ++;
		}
		
		if ($show_total)
		{
			if(!$is_file_size)
			{
				$total_label = number_format($total, 0, ',', '.');
			}
			else
			{
				$total_label = CreditSystem::make_size_string($total);
			}
			echo '<tr><th  colspan="4" align="right">'.get_lang('Total').': '.$total_label.'</td></tr>';
		}
		echo '</table>';
	}
	
	function print_credits_stats($type)
	{
		$payment_table = Database :: get_main_table(CS_TABLE_PAYMENT);
		switch($type)
		{	
			case 'month':
				$period = get_lang('AmountCreditsMonth');
				$sql = "SELECT DATE_FORMAT(date, '%Y-%m') AS stat_date, SUM(credits) AS amount_of_credits FROM ".$payment_table." GROUP BY stat_date ORDER BY  amount_of_credits";
				$res = api_sql_query($sql,__FILE__,__LINE__);
				$result = array();
				while($obj = mysql_fetch_object($res))
				{
					$result[$obj->stat_date] = $obj->amount_of_credits;
				}
				CreditSystem::print_stats(get_lang('Credits').' ('.$period.')',$result,true);
				break;
			case 'year':
				$period = get_lang('AmountCreditsYear');
				$sql = "SELECT DATE_FORMAT(date, '%Y') AS stat_date, SUM(credits) AS amount_of_credits FROM ".$payment_table." GROUP BY stat_date ORDER BY  amount_of_credits";
				$res = api_sql_query($sql,__FILE__,__LINE__);
				$result = array();
				while($obj = mysql_fetch_object($res))
				{
					$result[$obj->stat_date] = $obj->amount_of_credits;
				}
				CreditSystem::print_stats2(get_lang('Credits').' ('.$period.')',$result,true);
				break;
		}		
		
	}
	
	
}
?>