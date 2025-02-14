<?php
// name of the language file that needs to be included
$language_file = array('registration','tracking','exercice');

$cidReset = true;

require ('../inc/global.inc.php');
require_once (api_get_path(LIBRARY_PATH).'tracking.lib.php');
require_once (api_get_path(LIBRARY_PATH).'course.lib.php');
require_once (api_get_path(LIBRARY_PATH).'usermanager.lib.php');
require_once ('../newscorm/learnpath.class.php');

$nameTools=get_lang('MyProgress');

$this_section = 'session_my_progress';

api_block_anonymous_users();

Display :: display_header($nameTools);

// Database table definitions
$tbl_course 				= Database :: get_main_table(TABLE_MAIN_COURSE);
$tbl_user 					= Database :: get_main_table(TABLE_MAIN_USER);
$tbl_session 				= Database :: get_main_table(TABLE_MAIN_SESSION);
$tbl_course_user 			= Database :: get_main_table(TABLE_MAIN_COURSE_USER);
$tbl_session_course 		= Database :: get_main_table(TABLE_MAIN_SESSION_COURSE);
$tbl_session_course_user 	= Database :: get_main_table(TABLE_MAIN_SESSION_COURSE_USER);
$tbl_stats_lastaccess 		= Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_LASTACCESS);
$tbl_stats_exercices 		= Database :: get_statistic_table(TABLE_STATISTIC_TRACK_E_EXERCICES);
$tbl_course_lp_view 		= Database :: get_course_table('lp_view');
$tbl_course_lp_view_item 	= Database :: get_course_table('lp_item_view');
$tbl_course_lp 				= Database :: get_course_table('lp');
$tbl_course_lp_item 		= Database :: get_course_table('lp_item');
$tbl_course_quiz 			= Database :: get_course_table('quiz');


// get course list
$sql = 'SELECT course_code FROM '.$tbl_course_user.' WHERE user_id='.$_user['user_id'];
$rs = api_sql_query($sql, __FILE__, __LINE__);
$Courses = array();
while($row = Database :: fetch_array($rs))
{
	$Courses[$row['course_code']] = CourseManager::get_course_information($row['course_code']);
}

// get the list of sessions where the user is subscribed as student
$sql = 'SELECT DISTINCT course_code FROM '.Database :: get_main_table(TABLE_MAIN_SESSION_COURSE_USER).' WHERE id_user='.intval($_user['user_id']);
$rs = api_sql_query($sql, __FILE__, __LINE__);
while($row = Database :: fetch_array($rs))
{
	$Courses[$row['course_code']] = CourseManager::get_course_information($row['course_code']);
}

api_display_tool_title($nameTools);

$now=date('Y-m-d');

?>

<table class="data_table" width="100%">
<tr class="tableName">
	<td colspan="6">
		<strong><?php echo get_lang('MyCourses'); ?></strong>
	</td>
</tr>
<tr>
  <th><?php echo get_lang('Course'); ?></th>
  <th><?php echo get_lang('Time'); ?></th>
  <th><?php echo get_lang('Progress'); ?></th>
  <th><?php echo get_lang('Score'); ?></th>
  <th><?php echo get_lang('LastConnexion'); ?></th>
  <th><?php echo get_lang('Details'); ?></th>
</tr>

<?php
$i = 0;
$totalWeighting = 0;
$totalScore = 0;
$totalItem = 0;
$totalProgress = 0;

foreach($Courses as $enreg)
{
	$weighting = 0;

	$lastConnexion = Tracking :: get_last_connection_date_on_the_course($_user['user_id'],$enreg['code']);
	$progress = Tracking :: get_avg_student_progress($_user['user_id'], $enreg['code']);
	$time = api_time_to_hms(Tracking :: get_time_spent_on_the_course($_user['user_id'], $enreg['code']));
	$pourcentageScore = Tracking :: get_avg_student_score($_user['user_id'], $enreg['code']);
?>

<tr class='<?php echo $i?'row_odd':'row_even'; ?>'>
  	<td>
		<?php echo html_entity_decode($enreg['title'],ENT_QUOTES,$charset); ?>
  	</td>

  	<td align='center'>
		<?php echo $time; ?>
  	</td>

  	<td align='center'>
  		<?php echo $progress.'%'; ?>
  	</td>

  	<td align='center'>
		<?php echo $pourcentageScore.'%'; ?>
  	</td>

  	<td align='center'>
		<?php echo $lastConnexion; ?>
  	</td>

  	<td align='center'>
		<a href="<?php echo api_get_self(); ?>?course=<?php echo $enreg['code']; ?>"> <?php echo '<img src="'.api_get_path(WEB_IMG_PATH).'2rightarrow.gif" border="0" />';?> </a>
  	</td>
</tr>

<?php



	$i=$i ? 0 : 1;
}
?>
</table>

<br/><br/>

<?php
/*
 * **********************************************************************************************
 *
 * 	Details for one course
 *
 * **********************************************************************************************
 */
	if(isset($_GET['course']))
	{
		$course = Database::escape_string($_GET['course']);
		$a_infosCours = CourseManager::get_course_information($course);
		
		//get coach and session_name if there is one and if session_mode is activated
		if(api_get_setting('use_session_mode')=='true')
		{
			$tbl_user = Database :: get_main_table(TABLE_MAIN_USER);
			$tbl_session = Database :: get_main_table(TABLE_MAIN_SESSION);
			$tbl_session_course = Database :: get_main_table(TABLE_MAIN_SESSION_COURSE);
			$tbl_session_course_user = Database :: get_main_table(TABLE_MAIN_SESSION_COURSE_USER);
			
			$sql = 'SELECT id_session 
					FROM '.$tbl_session_course_user.' session_course_user
					WHERE session_course_user.id_user = '.intval($_user['user_id']).'
					AND session_course_user.course_code = "'.Database::escape_string($course).'"
					ORDER BY id_session DESC';
			$rs = api_sql_query($sql,__FILE__,__LINE__);
			
			$row=Database::fetch_array($rs);			
			if (!empty ($row[0]))
			{
				$session_id =intval($row[0]);	
			}
			//$session_id =intval(Database::result($rs,0,0));			
			
			if($session_id>0)
			{
				// get session name and coach of the session
				$sql = 'SELECT name, id_coach FROM '.$tbl_session.' 
						WHERE id='.$session_id;
				$rs = api_sql_query($sql,__FILE__,__LINE__);						
				$session_name = Database::result($rs,0,'name');
				$session_coach_id = intval(Database::result($rs,0,'id_coach'));
				
				// get coach of the course in the session
				$sql = 'SELECT id_coach FROM '.$tbl_session_course.' 
						WHERE id_session='.$session_id.'
						AND course_code = "'.Database::escape_string($_GET['course']).'"';
				$rs = api_sql_query($sql,__FILE__,__LINE__);						
				$session_course_coach_id = intval(Database::result($rs,0,0));

				if($session_course_coach_id!=0)
				{
					$coach_infos = UserManager :: get_user_info_by_id($session_course_coach_id);
					$a_infosCours['tutor_name'] = $coach_infos['lastname'].' '.$coach_infos['firstname'];
				}
				else if($session_coach_id!=0)
				{
					$coach_infos = UserManager :: get_user_info_by_id($session_coach_id);
					$a_infosCours['tutor_name'] = $coach_infos['lastname'].' '.$coach_infos['firstname'];
				}
			}
		} // end if(api_get_setting('use_session_mode')=='true')
		
		$tableTitle = $a_infosCours['title'].' | '.get_lang('Teacher').' : '.$a_infosCours['tutor_name'].((!empty($session_name)) ? ' | '.get_lang('Session').' : '.$session_name : '');
		

		?>
		<table class="data_table" width="100%">
			<tr class="tableName">
				<td colspan="4">
					<strong><?php echo $tableTitle; ?></strong>
				</td>
			</tr>
			<tr>
			  <th class="head"><?php echo get_lang('Learnpath'); ?></th>
			  <th class="head"><?php echo get_lang('Time'); ?></th>
			  <th class="head"><?php echo get_lang('Progress'); ?></th>
			  <th class="head"><?php echo get_lang('LastConnexion'); ?></th>
			</tr>
			<?php //解决sql错误 by xiaoping
				$sqlLearnpath = "SELECT lp.name,lp.id FROM crs_".$a_infosCours['db_name']."_lp AS lp";

				$resultLearnpath = api_sql_query($sqlLearnpath);

				if(Database::num_rows($resultLearnpath)>0)
				{
					while($a_learnpath = Database::fetch_array($resultLearnpath))
					{


						$progress = learnpath :: get_db_progress($a_learnpath['id'],$_user['user_id'], '%',$a_infosCours['db_name']);


						// calculates last connection time
						//解决sql错误 by xiaoping
						$sql = 'SELECT MAX(start_time)
									FROM crs_'.$a_infosCours['db_name'].'_lp_item_view AS item_view
									INNER JOIN crs_'.$a_infosCours['db_name'].'_lp_view AS view
										ON item_view.lp_view_id = view.id
										AND view.lp_id = '.$a_learnpath['id'].'
										AND view.user_id = '.$_user['user_id'];
						$rs = api_sql_query($sql, __FILE__, __LINE__);
						$start_time = Database::result($rs, 0, 0);

						// calculates time
						//解决sql错误 by xiaoping
						$sql = 'SELECT SUM(total_time)
									FROM crs_'.$a_infosCours['db_name'].'_lp_item_view AS item_view
									INNER JOIN crs_'.$a_infosCours['db_name'].'_lp_view AS view
										ON item_view.lp_view_id = view.id
										AND view.lp_id = '.$a_learnpath['id'].'
										AND view.user_id = '.$_user['user_id'];
						$rs = api_sql_query($sql, __FILE__, __LINE__);
						$total_time = Database::result($rs, 0, 0);


						echo "<tr>
								<td>
							 ";
						echo 		stripslashes($a_learnpath['name']);
						echo "	</td>
								<td>
							 ";
						echo api_time_to_hms($total_time);
						echo "	</td>
								<td align='center'>
							 ";
						echo		$progress;
						echo "	</td>
								<td align='center'>
							 ";
						if($start_time!=''){
							echo format_locale_date(get_lang('dateFormatLong'),$start_time);
						}
						else{
							echo '-';
						}
						echo "	</td>
							  </tr>
							 ";
					}

				}
				else
				{
					echo "	<tr>
								<td colspan='4'>
									".get_lang('NoLearnpath')."
								</td>
							</tr>
						 ";
				}



			?>
			<tr>
			  <th class="head"><?php echo get_lang('Exercices'); ?></th>
			  <th class="head"><?php echo get_lang('Score'); ?></th>
			  <th class="head"><?php echo get_lang('Attempts'); ?></th>
			  <th class="head"><?php echo get_lang('Details'); ?></th>
			</tr>

			<?php
				//解决sql错误 by xiaoping
				$sql='SELECT visibility FROM crs_'.$a_infosCours['db_name'].'_'.TABLE_TOOL_LIST.' WHERE name="quiz"';
				$resultVisibilityTests = api_sql_query($sql);
				
				if(Database::result($resultVisibilityTests,0,'visibility')==1){
				    //解决sql错误 by xiaoping
					$sqlExercices = "SELECT quiz.title,id
									FROM crs_".$a_infosCours['db_name']."_quiz AS quiz 
									WHERE active='1'";	
					$resuktExercices = api_sql_query($sqlExercices);
					if(Database::num_rows($resuktExercices)>0){
						while($a_exercices = Database::fetch_array($resuktExercices))
						{
							$sqlEssais = "	SELECT COUNT(ex.exe_id) as essais
											FROM $tbl_stats_exercices AS ex
											WHERE ex.exe_user_id='".$_user['user_id']."' AND ex.exe_cours_id = '".$a_infosCours['code']."'
											AND ex.exe_exo_id = ".$a_exercices['id']
										 ;
							$resultEssais = api_sql_query($sqlEssais);
							$a_essais = Database::fetch_array($resultEssais);
		
							$sqlScore = "SELECT exe_id , exe_result,exe_weighting
										 FROM $tbl_stats_exercices
										 WHERE exe_user_id = ".$_user['user_id']."
										 AND exe_cours_id = '".$a_infosCours['code']."'
										 AND exe_exo_id = ".$a_exercices['id']."
										ORDER BY exe_date DESC LIMIT 1";
		
							$resultScore = api_sql_query($sqlScore);
							$score = 0;
							while($a_score = Database::fetch_array($resultScore))
							{
								$score = $score + $a_score['exe_result'];
								$weighting = $weighting + $a_score['exe_weighting'];
								$exe_id = $a_score['exe_id'];
							}					
							
							if  ($weighting>0)
							{							
								$pourcentageScore = round(($score*100)/$weighting);
							}
							else
							{
								$pourcentageScore=0;			
							}
		
							$weighting = 0;
		
							echo "<tr>
									<td>
								 ";
							echo $a_exercices['title'];
							echo "</td> ";
							echo "<td>";
							echo $pourcentageScore.'%';
							echo "</td>		
								  <td>
								 ";
							echo $a_essais['essais'];
							echo '</td>
								  <td align="center" width="200">
								 ';
							if($a_essais['essais']>0)
								echo '<a href="../exercice/exercise_show.php?origin=student_progress&id='.$exe_id.'&cidReq='.$a_infosCours['code'].'&id_session='.$_GET['id_session'].'"> <img src="'.api_get_path(WEB_IMG_PATH).'quiz.gif" border="0"> </a>';
							echo "</td>
								 </tr>
								 ";
						}
					}
					else{
						echo '<tr><td colspan="4">'.get_lang('NoEx').'</td></tr>';
					}
				}
				else{
					echo '<tr><td colspan="4">'.get_lang('NoEx').'</td></tr>';
				}

			?>
		</table>
		<?php
	}

Display :: display_footer();
?>
