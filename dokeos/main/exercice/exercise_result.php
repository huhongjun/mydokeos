<?php
/*
    DOKEOS - elearning and course management software

    For a full list of contributors, see documentation/credits.html

    This program is free software; you can redistribute it and/or
    modify it under the terms of the GNU General Public License
    as published by the Free Software Foundation; either version 2
    of the License, or (at your option) any later version.
    See "documentation/licence.html" more details.

    Contact:
		Dokeos
		Rue du Corbeau, 108
		B-1030 Brussels - Belgium
		info@dokeos.com
*/
/**
*	Exercise result
*	This script gets informations from the script "exercise_submit.php",
*	through the session, and calculates the score of the student for
*	that exercise.
*	Then it shows the results on the screen.
*	@package dokeos.exercise
*	@author Olivier Brouckaert, main author
*	@author Roan Embrechts, some refactoring
* 	@version $Id: exercise_result.php 15438 2008-05-27 06:46:30Z elixir_inter $
*
*	@todo	split more code up in functions, move functions to library?
*/
/*
==============================================================================
		INIT SECTION
==============================================================================
*/
include('exercise.class.php');
include('question.class.php');
include('answer.class.php');
if($_GET['origin']=='learnpath')
{
	require_once ('../newscorm/learnpath.class.php');
	require_once ('../newscorm/learnpathItem.class.php');
	require_once ('../newscorm/scorm.class.php');
	require_once ('../newscorm/scormItem.class.php');
	require_once ('../newscorm/aicc.class.php');
	require_once ('../newscorm/aiccItem.class.php');
}

// answer types
define('UNIQUE_ANSWER',	1);
define('MULTIPLE_ANSWER',	2);
define('FILL_IN_BLANKS',	3);
define('MATCHING',		4);
define('FREE_ANSWER', 5);
define('HOT_SPOT', 6);
define('HOT_SPOT_ORDER', 	7);

global $_cid;
// name of the language file that needs to be included
$language_file='exercice';

include('../inc/global.inc.php');
$this_section=SECTION_COURSES;

/* ------------	ACCESS RIGHTS ------------ */
// notice for unauthorized people.
api_protect_course_script(true);

include_once(api_get_path(LIBRARY_PATH).'events.lib.inc.php');
include_once(api_get_path(LIBRARY_PATH).'mail.lib.inc.php');
include_once(api_get_path(LIBRARY_PATH).'course.lib.php');

// Database table definitions
$TBL_EXERCICE_QUESTION 	= Database::get_course_table(TABLE_QUIZ_TEST_QUESTION);
$TBL_EXERCICES         	= Database::get_course_table(TABLE_QUIZ_TEST);
$TBL_QUESTIONS         	= Database::get_course_table(TABLE_QUIZ_QUESTION);
$TBL_REPONSES          	= Database::get_course_table(TABLE_QUIZ_ANSWER);
$TBL_TRACK_EXERCICES	= Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_EXERCICES);
$TBL_TRACK_ATTEMPT		= Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_ATTEMPT);
$main_user_table 		= Database :: get_main_table(TABLE_MAIN_USER);
$main_course_user_table = Database :: get_main_table(TABLE_MAIN_COURSE_USER);
$table_ans 				= Database :: get_course_table(TABLE_QUIZ_ANSWER);

//temp values to move to AWACS
$dsp_percent = false; //false to display total score as absolute values
//debug param. 0: no display - 1: debug display
$debug=0;
if($debug>0){echo str_repeat('&nbsp;',0).'Entered exercise_result.php'."<br />\n";var_dump($_POST);}
// general parameters passed via POST/GET
if ( empty ( $origin ) ) {
     $origin = $_REQUEST['origin'];
}
if ( empty ( $learnpath_id ) ) {
     $learnpath_id       = mysql_real_escape_string($_REQUEST['learnpath_id']);
}
if ( empty ( $learnpath_item_id ) ) {
     $learnpath_item_id  = mysql_real_escape_string($_REQUEST['learnpath_item_id']);
}
if ( empty ( $formSent ) ) {
    $formSent       = $_REQUEST['formSent'];
}
if ( empty ( $exerciseResult ) ) {
     $exerciseResult = $_SESSION['exerciseResult'];
}
if ( empty ( $questionId ) ) {
    $questionId = $_REQUEST['questionId'];
}
if ( empty ( $choice ) ) {
    $choice = $_REQUEST['choice'];
}
if ( empty ( $questionNum ) ) {
   $questionNum    = mysql_real_escape_string($_REQUEST['questionNum']);
}
if ( empty ( $nbrQuestions ) ) {
    $nbrQuestions   = mysql_real_escape_string($_REQUEST['nbrQuestions']);
}
if ( empty ( $questionList ) ) {
    $questionList = $_SESSION['questionList'];
}
if ( empty ( $objExercise ) ) {
    $objExercise = $_SESSION['objExercise'];
}
$main_user_table = Database :: get_main_table(TABLE_MAIN_USER);
$main_admin_table = Database :: get_main_table(TABLE_MAIN_ADMIN);
$courseName = $_SESSION['_course']['name'];
$query = "select user_id from $main_admin_table";
$admin_id = mysql_result(api_sql_query($query),0,"user_id");
$uinfo = api_get_user_info($admin_id);
$from = $uinfo['mail'];
$from_name = $uinfo['lastname'].' '.$uinfo['firstname'];
$str = $_SERVER['REQUEST_URI'];
$arr = explode('/',$str);
$url = api_get_path(WEB_CODE_PATH).'exercice/exercice.php?'.api_get_cidreq().'&show=result';

//$url =  $_SERVER['SERVER_NAME'].'/'.$arr[1].'/';
 // if the above variables are empty or incorrect, stops the script
if(!is_array($exerciseResult) || !is_array($questionList) || !is_object($objExercise))
{

	header('Location: exercice.php');
	exit();
}
$exerciseTitle=$objExercise->selectTitle();
$exerciseDescription=$objExercise->selectDescription();
$exerciseDescription=stripslashes($exerciseDescription);


$nameTools=get_lang('Exercice');

$interbreadcrumb[]=array("url" => "exercice.php","name" => get_lang('Exercices'));


if ($origin != 'learnpath')
{
	//so we are not in learnpath tool
	Display::display_header($nameTools,"Exercise");
}
else
{

	if(empty($charset))
	{
		$charset = 'ISO-8859-15';
	}
	header('Content-Type: text/html; charset='. $charset);

	@$document_language = Database::get_language_isocode($language_interface);
	if(empty($document_language))
	{
	  //if there was no valid iso-code, use the english one
	  $document_language = 'en';
	}

	/*
	 * HTML HEADER
	 */

?>
<!DOCTYPE html
     PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $document_language; ?>" lang="<?php echo $document_language; ?>">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
</head>

<body>
<link rel="stylesheet" type="text/css" href="<?php echo api_get_path(WEB_CODE_PATH).'css/'.api_get_setting('stylesheets').'/frames.css'; ?>" />

<?php
}


if($objExercise->results_disabled)
{
	ob_start();
}

/*
==============================================================================
		FUNCTIONS
==============================================================================
*/

function display_unique_or_multiple_answer($answerType, $studentChoice, $answer, $answerComment, $answerCorrect)
{
	?>
	<tr>
	<td width="5%" align="center">
		<img src="../img/<?php echo ($answerType == UNIQUE_ANSWER)?'radio':'checkbox'; echo $studentChoice?'_on':'_off'; ?>.gif"
		border="0" alt="" />
	</td>
	<td width="5%" align="center">
		<img src="../img/<?php echo ($answerType == UNIQUE_ANSWER)?'radio':'checkbox'; echo $answerCorrect?'_on':'_off'; ?>.gif"
		border="0" alt=" " />
	</td>
	<td width="45%" style="border-bottom: 1px solid #4171B5;">
		<?php
		$answer=api_parse_tex($answer);
		echo $answer; ?>
	</td>
	<td width="45%" style="border-bottom: 1px solid #4171B5;">
		<?php
		$answerComment=api_parse_tex($answerComment);
		if($studentChoice) echo nl2br(make_clickable(stripslashes($answerComment))); else echo '&nbsp;'; ?>
	</td>
	</tr>
	<?php
}

function display_fill_in_blanks_answer($answer)
{
	?>
		<tr>
		<td>
			<?php echo nl2br($answer); ?>
		</td>
		</tr>
	<?php
}

function display_free_answer($answer)
{
	?>
		<tr>
		<td width="55%">
			<?php echo nl2br(stripslashes($answer)); ?>
		</td>
   <td width="45%">
    <?php echo get_lang('notCorrectedYet');?>

   </td>
		</tr>
	<?php
}

function display_hotspot_answer($answerId, $answer, $studentChoice, $answerComment)
{
	//global $hotspot_colors;
	$hotspot_colors = array("", // $i starts from 1 on next loop (ugly fix)
            						"#4271B5",
									"#FE8E16",
									"#3B3B3B",
									"#BCD631",
									"#D63173",
									"#D7D7D7",
									"#90AFDD",
									"#AF8640",
									"#4F9242",
									"#F4EB24",
									"#ED2024",
									"#45C7F0",
									"#F7BDE2");
	?>
		<tr>
				<td valign="top">
					<div style="float:left; padding-left:5px;">
						<div style="display:inline; float:left; width:80px;"><?php echo $answer ?></div>
						<div style="height:11px; width:11px; background-color:<?php echo $hotspot_colors[$answerId]; ?>; display:inline; float:left; margin-top:3px;"></div>
					</div>
				</td>
				<td valign="top"><?php echo $answerId; ?></td>
				<td valign="top">
					<?php $studentChoice = ($studentChoice)?get_lang('Correct'):get_lang('Fault'); echo $studentChoice; ?>
				</td>
				<td valign="top">
					<?php echo stripslashes($answerComment); ?>
				</td>
		</tr>
	<?php
}

/*
==============================================================================
		MAIN CODE
==============================================================================
*/
$exerciseTitle=api_parse_tex($exerciseTitle);

?>
	<h3><?php echo $exerciseTitle ?>: <?php echo get_lang("Result"); ?></h3>
	<?php echo $exerciseDescription; ?>
	<form method="get" action="exercice.php">
	<input type="hidden" name="origin" value="<?php echo $origin; ?>" />
    <input type="hidden" name="learnpath_id" value="<?php echo $learnpath_id; ?>" />
    <input type="hidden" name="learnpath_item_id" value="<?php echo $learnpath_item_id; ?>" />

<?php
	$i=$totalScore=$totalWeighting=0;
	if($debug>0){echo "ExerciseResult: "; var_dump($exerciseResult); echo "QuestionList: ";var_dump($questionList);}


	// added by Priya Saini
	$sql = "select max(exe_Id) as id from ".$TBL_TRACK_EXERCICES;
	$res = api_sql_query($sql, __FILE__, __LINE__);
	$exeId =mysql_result($res,0,"id");
	$exeId=$exeId+1;
	
	$counter=0;
	foreach($questionList as $questionId)
	{
		$counter++;
		// gets the student choice for this question
		$choice=$exerciseResult[$questionId];
		// creates a temporary Question object

		$objQuestionTmp = Question :: read($questionId);

		$questionName=$objQuestionTmp->selectTitle();
		$questionDescription=$objQuestionTmp->selectDescription();
		$questionWeighting=$objQuestionTmp->selectWeighting();
		$answerType=$objQuestionTmp->selectType();
		$quesId =$objQuestionTmp->selectId(); //added by priya saini

		// destruction of the Question object
		unset($objQuestionTmp);

		if($answerType == UNIQUE_ANSWER || $answerType == MULTIPLE_ANSWER)
		{
			$colspan=4;
		}
		elseif($answerType == MATCHING || $answerType == FREE_ANSWER)
		{
			$colspan=2;
		}
		elseif($answerType == HOT_SPOT || $answerType == HOT_SPOT_ORDER)
		{
			$colspan=4;
			$rowspan=$nbrAnswers+1;
		}
		else
		{
			$colspan=1;
		}
		?>
			<table width="100%" border="0" cellpadding="3" cellspacing="2">
			<tr bgcolor="#E6E6E6">
			<td colspan="<?php echo $colspan; ?>">
				<?php echo get_lang("Question").' '.($counter).' : '.$questionName; ?>
			</td>
			</tr>
			<tr>
			<td colspan="<?php echo $colspan; ?>">
				<i>
				<?php echo $questionDescription; ?>
				</i>
			</td>
			</tr>
		<?php
		if($answerType == UNIQUE_ANSWER || $answerType == MULTIPLE_ANSWER)
		{
			?>
				<tr>
				<td width="5%" valign="top" align="center" nowrap="nowrap">
					<i><?php echo get_lang("Choice"); ?></i>
				</td>
				<td width="5%" valign="top" nowrap="nowrap">
					<i><?php echo get_lang("ExpectedChoice"); ?></i>
				</td>
				<td width="45%" valign="top">
					<i><?php echo get_lang("Answer"); ?></i>
				</td>
				<td width="45%" valign="top">
					<i><?php echo get_lang("Comment"); ?></i>
				</td>
				</tr>
			<?php
		}
		elseif($answerType == FILL_IN_BLANKS)
		{
			?>
				<tr>
				<td>
					<i><?php echo get_lang("Answer"); ?></i>
				</td>
				</tr>
			<?php
		}
		elseif($answerType == FREE_ANSWER)
		{
			?>
				<tr>
				<td width="55%">
					<i><?php echo get_lang("Answer"); ?></i>
				</td>
				<td width="45%" valign="top">
					<i><?php echo get_lang("Comment"); ?></i>
				</td>
				</tr>
			<?php
		}
		elseif($answerType == HOT_SPOT)
		{
			?>
				<tr>
					<td valign="top" colspan="2">
						<table width="556" border="0">
							<tr>
								<td width="40%" valign="top">
									<i><?php echo get_lang("CorrectAnswer"); ?></i><br /><br />
								</td>
								<td width="15%" valign="top">
									<i><?php echo get_lang("ClickNumber"); ?></i><br /><br />
								</td>
								<td width="20%" valign="top">
									<i><?php echo get_lang('HotspotHit'); ?></i><br /><br />
								</td>
								<td width="25%" valign="top">
									<i><?php echo get_lang("Comment"); ?></i><br /><br />
								</td>
							</tr>
			<?php
		}
		else
		{
			?>
				<tr>
				<td width="50%">
					<i><?php echo get_lang("ElementList"); ?></i>
				</td>
				<td width="50%">
					<i><?php echo get_lang("CorrespondsTo"); ?></i>
				</td>
				</tr>
			<?php
		}

		// construction of the Answer object
		$objAnswerTmp=new Answer($questionId);
		$nbrAnswers=$objAnswerTmp->selectNbrAnswers();
		$questionScore=0;
		if($answerType == FREE_ANSWER)
			$nbrAnswers = 1;
		for($answerId=1;$answerId <= $nbrAnswers;$answerId++)
		{
			$answer=$objAnswerTmp->selectAnswer($answerId);
			$answerComment=$objAnswerTmp->selectComment($answerId);
			$answerCorrect=$objAnswerTmp->isCorrect($answerId);
			$answerWeighting=$objAnswerTmp->selectWeighting($answerId);

			switch($answerType)
			{
				// for unique answer
				case UNIQUE_ANSWER :
										$studentChoice=($choice == $answerId)?1:0;

										if($studentChoice)
										{
										  	$questionScore+=$answerWeighting;
											$totalScore+=$answerWeighting;
										}


										break;
				// for multiple answers
				case MULTIPLE_ANSWER :

										$studentChoice=$choice[$answerId];

										if($studentChoice)
										{
											$questionScore+=$answerWeighting;
											$totalScore+=$answerWeighting;
										}

										break;
				// for fill in the blanks
				case FILL_IN_BLANKS :	// splits text and weightings that are joined with the character '::'

										list($answer,$answerWeighting)=explode('::',$answer);

										// splits weightings that are joined with a comma
										$answerWeighting=explode(',',$answerWeighting);

										// we save the answer because it will be modified

										$temp=$answer;

										// TeX parsing
										// 1. find everything between the [tex] and [/tex] tags
										 $startlocations=strpos($temp,'[tex]');
										$endlocations=strpos($temp,'[/tex]');

										if($startlocations !== false && $endlocations !== false)
										{

											$texstring=substr($temp,$startlocations,$endlocations-$startlocations+6);
											// 2. replace this by {texcode}
											$temp=str_replace($texstring,'{texcode}',$temp);
										}

										$answer='';


										$j=0;

										// the loop will stop at the end of the text
										while(1)
										{

											// quits the loop if there are no more blanks
											if(($pos = strpos($temp,'[')) === false)
											{
												// adds the end of the text
												 $answer.=$temp;
												// TeX parsing
												$texstring = api_parse_tex($texstring);
												$answer=str_replace("{texcode}",$texstring,$answer);
												break;
											}

											// adds the piece of text that is before the blank and ended by [
											$answer.=substr($temp,0,$pos+1);
											$temp=substr($temp,$pos+1);

											// quits the loop if there are no more blanks
											if(($pos = strpos($temp,']')) === false)
											{

												// adds the end of the text
												$answer.=$temp;
												break;
											}

											$choice[$j]=trim($choice[$j]);

											// if the word entered by the student IS the same as the one defined by the professor
											if(strtolower(substr($temp,0,$pos)) == stripslashes(strtolower($choice[$j])))
											{
												// gives the related weighting to the student
												$questionScore+=$answerWeighting[$j];

												// increments total score
												$totalScore+=$answerWeighting[$j];

												// adds the word in green at the end of the string
												$answer.=stripslashes($choice[$j]);
											}
											// else if the word entered by the student IS NOT the same as the one defined by the professor
											elseif(!empty($choice[$j]))
											{
												// adds the word in red at the end of the string, and strikes it
												$answer.='<font color="red"><s>'.stripslashes($choice[$j]).'</s></font>';
											}
											else
											{
												// adds a tabulation if no word has been typed by the student
												$answer.='&nbsp;&nbsp;&nbsp;';
											}

											// adds the correct word, followed by ] to close the blank
											$answer.=' / <font color="green"><b>'.substr($temp,0,$pos).'</b></font>]';

											$j++;

											$temp=substr($temp,$pos+1);
										}

										break;
				// for free answer
				case FREE_ANSWER :
										$studentChoice=$choice;

										if($studentChoice)
										{
											//Score is at -1 because the question has'nt been corected
										  	$questionScore=-1;
											$totalScore+=0;
										}


										break;
				// for matching
				case MATCHING :
										if($answerCorrect)
										{
											if($answerCorrect == $choice[$answerId])
											{
												$questionScore+=$answerWeighting;
												$totalScore+=$answerWeighting;
												$choice[$answerId]=$matching[$choice[$answerId]];
											}
											elseif(!$choice[$answerId])
											{
												$choice[$answerId]='&nbsp;&nbsp;&nbsp;';
											}
											else
											{
												$choice[$answerId]='<font color="red"><s>'.$matching[$choice[$answerId]].'</s></font>';
											}
										}
										else
										{
											$matching[$answerId]=$answer;
										}
										break;
				// for hotspot with no order
				case HOT_SPOT :			$studentChoice=$choice[$answerId];

										if($studentChoice)
										{
											$questionScore+=$answerWeighting;
											$totalScore+=$answerWeighting;
										}

										break;
				// for hotspot with fixed order
				case HOT_SPOT_ORDER :	$studentChoice=$choice['order'][$answerId];

										if($studentChoice == $answerId)
										{
											$questionScore+=$answerWeighting;
											$totalScore+=$answerWeighting;
											$studentChoice = true;
										}
										else
										{
											$studentChoice = false;
										}

										break;
			} // end switch Answertype
			
			if($answerType != MATCHING || $answerCorrect)
			{
				if($answerType == UNIQUE_ANSWER || $answerType == MULTIPLE_ANSWER)
				{
					display_unique_or_multiple_answer($answerType, $studentChoice, $answer, $answerComment, $answerCorrect);
				}
				elseif($answerType == FILL_IN_BLANKS)
				{
					display_fill_in_blanks_answer($answer);
				}
				elseif($answerType == FREE_ANSWER)
				{
					// to store the details of open questions in an array to be used in mail

					$arrques[] = $questionName;
					$arrans[]  = $choice;
					$firstName =   $_SESSION['_user']['firstName'];
					$lastName =   $_SESSION['_user']['lastName'];
					$mail =  $_SESSION['_user']['mail'];
					$coursecode =  $_SESSION['_course']['id'];
					$to = '';
					$teachers = array();
					if(api_get_setting('use_session_mode')=='true' && !empty($_SESSION['id_session']))
					{
						$teachers = CourseManager::get_coach_list_from_course_code($coursecode,$_SESSION['id_session']);
					}
					else
					{
						$teachers = CourseManager::get_teacher_list_from_course_code($coursecode);
					}
					$num = count($teachers);
					if($num>1)
					{
						$to = array();
						foreach($teachers as $teacher)
						{
							$to[] = $teacher['email'];
						}
					}elseif($num>0){
						foreach($teachers as $teacher)
						{
							$to = $teacher['email'];
						}
					}else{
						//this is a problem (it means that there is no admin for this course)
					}
					display_free_answer($choice);
				}
				elseif($answerType == HOT_SPOT)
				{
					
					$tbl_track_e_hotspot = Database::get_statistic_table(TABLE_STATISTIC_TRACK_E_HOTSPOT);
					// Save into db
					$sql = "INSERT INTO $tbl_track_e_hotspot (`hotspot_user_id` , `hotspot_course_code` , `hotspot_exe_id` , `hotspot_question_id` , `hotspot_answer_id` , `hotspot_correct` , `hotspot_coordinate` ) 
							VALUES ('".$_user['user_id']."', '".$_course['id']."', '$exeId', '$questionId', '$answerId', '$studentChoice', '".$_SESSION['exerciseResultCoordinates'][$questionId][$answerId]."')";
				
					$result = api_sql_query($sql,__FILE__,__LINE__);
					
					display_hotspot_answer($answerId, $answer, $studentChoice, $answerComment);
				}
				elseif($answerType == HOT_SPOT_ORDER)
				{
					display_hotspot_order_answer($answerId, $answer, $studentChoice, $answerComment);
				}
				else
				{
					?>
						<tr>
						<td width="50%">
							<?php
							$answer=api_parse_tex($answer);
							echo $answer; ?>
						</td>
						<td width="50%">
							<?php echo $choice[$answerId]; ?> / <font color="green"><b>
							<?php
							$matching[$answerCorrect]=api_parse_tex($matching[$answerCorrect]);
							echo $matching[$answerCorrect]; ?></b></font>
						</td>
						</tr>
					<?php
				}
			}
		} // end for that loops over all answers of the current question

		if ($answerType == HOT_SPOT || $answerType == HOT_SPOT_ORDER)
			{
				// We made an extra table for the answers
				echo "</table></td></tr>";
				?>
				
				<tr>
					<td colspan="2">
						<i><?php echo get_lang('Hotspot'); ?></i><br /><br />
						<object type="application/x-shockwave-flash" data="../plugin/hotspot/hotspot_solution.swf?modifyAnswers=<?php echo $questionId ?>&exe_id=&from_db=0" width="556" height="421">
							<param name="movie" value="../plugin/hotspot/hotspot_solution.swf?modifyAnswers=<?php echo $questionId ?>&exe_id=&from_db=0" />
						</object>
					</td>
				</tr>
				<?php 
			}
		?>
			<tr>
			<td colspan="<?php echo $colspan; ?>" align="left">
				<b>
				<?php
				if($questionScore==-1){ 
					echo get_lang('Score')." : 0/$questionWeighting";
				}
				else{
					echo get_lang('Score')." : $questionScore/$questionWeighting";
				}
				?></b><br /><br />
			</td>
			</tr>
			</table>
		<?php
		// destruction of Answer
		unset($objAnswerTmp);

		$i++;

		$totalWeighting+=$questionWeighting;
		//added by priya saini
		if($_configuration['tracking_enabled'])
		{
			if(empty($choice)){
				$choice = 0;
			}
			if ($answerType==MULTIPLE_ANSWER )
			{
				$reply = array_keys($choice);
				for ($i=0;$i<=sizeof($reply);$i++)//解决了多选题无操作时不写入数据库的问题 即如果学员没做多选题则该题在查看时不显示 by xiaoping
				{
					$ans = $reply[$i];
					exercise_attempt($questionScore,$ans,$quesId,$exeId,$i);
				}
			}
			elseif ($answerType==MATCHING)
			{
				$j=sizeof($matching)+1;

				for ($i=0;$i<sizeof($choice);$i++,$j++)
				{
					$val = $choice[$j];
					if (preg_match_all ('#<font color="red"><s>([0-9a-z ]*)</s></font>#', $val, $arr1))
						$val = $arr1[1][0];
					$val=addslashes($val);
					$val=strip_tags($val);
					$sql = "select position from $table_ans where question_id=$questionId and answer='$val' AND correct=0";
					$res = api_sql_query($sql, __FILE__, __LINE__);
					$answer = mysql_result($res,0,"position");				
					
					exercise_attempt($questionScore,$answer,$quesId,$exeId,$j);

				}
			}
			elseif ($answerType==FREE_ANSWER)
			{
				$answer = $choice;
				exercise_attempt($questionScore,$answer,$quesId,$exeId,0);
			}
			elseif ($answerType==UNIQUE_ANSWER)
			{
				$sql = "select id from $table_ans where question_id=$questionId and position=$choice";
				$res = api_sql_query($sql, __FILE__, __LINE__);
				$answer = mysql_result($res,0,"id");
				exercise_attempt($questionScore,$answer,$quesId,$exeId,0);
			}
			else
			{
				exercise_attempt($questionScore,$answer,$quesId,$exeId,0);
			}
		}
	} // end huge foreach() block that loops over all questions
	?>
		<table width="100%" border="0" cellpadding="3" cellspacing="2">
		<tr>
		<td>
			<b><?php echo get_lang('YourTotalScore')." ";
			if($dsp_percent == true){
			  echo number_format(($totalScore/$totalWeighting)*100,1,'.','')."%";
			}else{
			  echo $totalScore."/".$totalWeighting;
			}
                        ?></b>
		</td>
		</tr>
		<tr>
		<td>
		<br />
			<?php
			if ($origin != 'learnpath')
			{
			?>
			<input type="submit" value="<?php echo get_lang('Finish'); ?>" />
			<?php
			}
			?>
		</td>
		</tr>
		</table>

		</form>

		<br />
	<?php
/*
==============================================================================
		Tracking of results
==============================================================================
*/

if($_configuration['tracking_enabled'])
{
	//include(api_get_path(LIBRARY_PATH).'events.lib.inc.php');
	event_exercice($objExercise->selectId(),$totalScore,$totalWeighting,$answer,$question_id);

}

if($objExercise->results_disabled)
{
	ob_end_clean();
	Display :: display_normal_message(get_lang('ExerciseFinished').'<br /><a href="exercice.php" />'.get_lang('Back').'</a>',false);
}

if ($origin != 'learnpath')
{
	//we are not in learnpath tool
	Display::display_footer();
}else{
	//record the results in the learning path, using the SCORM interface (API)
	echo '<script language="javascript" type="text/javascript">window.parent.API.void_save_asset('.$totalScore.','.$totalWeighting.');</script>'."\n";
	echo '</body></html>';
}
$send_email = api_get_course_setting('email_alert_manager_on_new_quiz');
if ($send_email && count($arrques)>0)
{
$mycharset = api_get_setting('platform_charset'); 
$msg = '<html><head>
	<link rel="stylesheet" href="'.api_get_path(WEB_CODE_PATH).'css/'.api_get_setting('stylesheets').'/default.css" type="text/css">
	<meta content="text/html; charset='.$mycharset.'" http-equiv="content-type">';
$msg .= '</head>
<body><br />
<p>'.get_lang('OpenQuestionsAttempted').' : 
</p>
<p>'.get_lang('AttemptDetails').' : ><br />
</p>
<table width="730" height="136" border="0" cellpadding="3" cellspacing="3">
					<tr>
    <td width="229" valign="top"><h2>&nbsp;&nbsp;'.get_lang('CourseName').'</h2></td>
    <td width="469" valign="top"><h2>#course#</h2></td>
  </tr>
  <tr>
    <td width="229" valign="top" class="outerframe">&nbsp;&nbsp;'.get_lang('TestAttempted').'</span></td>
    <td width="469" valign="top" class="outerframe">#exercise#</td>
  </tr>
  <tr>
    <td valign="top">&nbsp;&nbsp;<span class="style10">'.get_lang('StudentName').'</span></td>
    <td valign="top" >#firstName# #lastName#</td>
  </tr>
  <tr>
    <td valign="top" >&nbsp;&nbsp;'.get_lang('StudentEmail').' </td>
    <td valign="top"> #mail#</td>
</tr></table>
<p><br />'.get_lang('OpenQuestionsAttemptedAre').' :</p>
 <table width="730" height="136" border="0" cellpadding="3" cellspacing="3">';
  for($i=0;$i<sizeof($arrques);$i++)
  {
	  $msg.='
		<tr>
	    <td width="220" valign="top" bgcolor="#E5EDF8">&nbsp;&nbsp;<span class="style10">'.get_lang('Question').'</span></td>
	    <td width="473" valign="top" bgcolor="#F3F3F3"><span class="style16"> #questionName#</span></td>
	  	</tr>
	  	<tr>
	    <td width="220" valign="top" bgcolor="#E5EDF8">&nbsp;&nbsp;<span class="style10">'.get_lang('Answer').' </span></td>
	    <td valign="top" bgcolor="#F3F3F3"><span class="style16"> #answer#</span></td>
	  	</tr>';
	
		$msg1= str_replace("#exercise#",$exerciseTitle,$msg);
		$msg= str_replace("#firstName#",$firstName,$msg1);
		$msg1= str_replace("#lastName#",$lastName,$msg);
		$msg= str_replace("#mail#",$mail,$msg1);
		$msg1= str_replace("#questionName#",$arrques[$i],$msg);
		$msg= str_replace("#answer#",$arrans[$i],$msg1);
		$msg1= str_replace("#i#",$i,$msg);
		$msg= str_replace("#course#",$courseName,$msg1);
	}
	$msg.='</table><br>
 	<span class="style16">'.get_lang('ClickToCommentAndGiveFeedback').',<br />
<a href="#url#">#url#</a></span></body></html>';

	$msg1= str_replace("#url#",$url,$msg);
	$mail_content = stripslashes($msg1);
	$student_name = $_SESSION['_user']['lastName'].' '.$_SESSION['_user']['firstName'];
	$subject = get_lang('OpenQuestionsAttempted');
	api_mail_html($student_name, $to, $subject, $mail_content, $from_name, $from, array('encoding'=>$mycharset,'charset'=>$mycharset));
}
?>
