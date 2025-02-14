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
		Rue des Palais 44 Paleizenstraat
		B-1030 Brussels - Belgium
		Tel. +32 (2) 211 34 56
*/


/**
*	Exercise library
* 	shows a question and its answers
*	@package dokeos.exercise
* 	@author Olivier Brouckaert <oli.brouckaert@skynet.be>
* 	@version $Id: exercise.lib.php 15412 2008-05-26 13:56:24Z elixir_inter $
*/


require("../inc/lib/fckeditor/fckeditor.php") ;
function showQuestion($questionId, $onlyAnswers=false, $origin=false)
{
	// reads question informations
	if(!$objQuestionTmp = Question::read($questionId))
	{
		// question not found
		return false;
	}

	$answerType=$objQuestionTmp->selectType();
	$pictureName=$objQuestionTmp->selectPicture();

	if ($answerType != HOT_SPOT) // Question is not of type hotspot
	{

		if(!$onlyAnswers)
		{
			$questionName=$objQuestionTmp->selectTitle();
			$questionDescription=$objQuestionTmp->selectDescription();

			$questionName=api_parse_tex($questionName);
			$s.=$questionName;
			$s.="</td>
			</tr>
			<tr>
			  <td valign='top' colspan='2'>
				<i>";
			$questionDescription=api_parse_tex($questionDescription);
			$s.=$questionDescription;
			$s.="</i>
			  </td>
			</tr>";
	
			if(!empty($pictureName))
			{
				$s.="
				<tr>
				  <td align='center' colspan='2'><img src='../document/download.php?doc_url=%2Fimages%2F'".$pictureName."' border='0'></td>
				</tr>";
			}

		}  // end if(!$onlyAnswers)

		// construction of the Answer object
		$objAnswerTmp=new Answer($questionId);

		$nbrAnswers=$objAnswerTmp->selectNbrAnswers();

		// only used for the answer type "Matching"
		if($answerType == MATCHING)
		{
			$cpt1='A';
			$cpt2=1;
			$Select=array();
		}
		elseif($answerType == FREE_ANSWER)
		{
	        #$comment = $objAnswerTmp->selectComment(1);
	        //

			$upload_path = api_get_path(REL_COURSE_PATH).$_SESSION['_course']['path'].'/document/';
			$oFCKeditor = new FCKeditor("choice[".$questionId."]") ;
			$oFCKeditor->BasePath	= api_get_path(WEB_PATH) . 'main/inc/lib/fckeditor/' ;
			$oFCKeditor->Config['CustomConfigurationsPath'] = api_get_path(REL_PATH)."main/inc/lib/fckeditor/myconfig.js";
			$oFCKeditor->Config['IMUploadPath'] = 'upload/test/';
			$oFCKeditor->ToolbarSet = "Test";
			$oFCKeditor->Width  = '80%';
			$oFCKeditor->Height = '300';
			$oFCKeditor->Value	= '' ;

			$TBL_LANGUAGES = Database::get_main_table(TABLE_MAIN_LANGUAGE);
			$sql="SELECT isocode FROM ".$TBL_LANGUAGES." WHERE english_name='".$_SESSION["_course"]["language"]."'";
			$result_sql=api_sql_query($sql);
			$isocode_language=mysql_result($result_sql,0,0);
			$oFCKeditor->Config['DefaultLanguage'] = $isocode_language;
		
			$s .= "<tr><td colspan='2'>".$oFCKeditor->CreateHtml()."</td></tr>";
			//$s.="<tr><td colspan='2'><textarea cols='80' rows='10' name='choice[".$questionId."]'>$answer</textarea></td></tr>";

		}

		for($answerId=1;$answerId <= $nbrAnswers;$answerId++)
		{
			$answer=$objAnswerTmp->selectAnswer($answerId);
			$answerCorrect=$objAnswerTmp->isCorrect($answerId);

			if($answerType == FILL_IN_BLANKS)
			{
				// splits text and weightings that are joined with the character '::'
				list($answer)=explode('::',$answer);

				// because [] is parsed here we follow this procedure:
				// 1. find everything between the [tex] and [/tex] tags
				$startlocations=strpos($answer,'[tex]');
				$endlocations=strpos($answer,'[/tex]');

				if($startlocations !== false && $endlocations !== false)
				{
					$texstring=substr($answer,$startlocations,$endlocations-$startlocations+6);
					// 2. replace this by {texcode}
					$answer=str_replace($texstring,'{texcode}',$answer);
				}

				// 3. do the normal matching parsing

				// replaces [blank] by an input field
				$answer=ereg_replace('\[[^]]+\]','<input type="text" name="choice['.$questionId.'][]" size="10">',nl2br($answer));
				// 4. replace the {texcode by the api_pare_tex parsed code}
				$texstring = api_parse_tex($texstring);
				$answer=str_replace("{texcode}",$texstring,$answer);
			}

			// unique answer
			if($answerType == UNIQUE_ANSWER)
			{
			$s.="
			<tr>
			  <td align='center'>
				<input class='checkbox' type='radio' name='choice[".$questionId."]' value='".$answerId."'>
			  </td>
			  <td>";
			$answer=api_parse_tex($answer);
			$s.=$answer;
			$s.="</td></tr>";

			}
			// multiple answers
			elseif($answerType == MULTIPLE_ANSWER)
			{
			$s.="<tr>
			  <td align='center'>
			<input class='checkbox' type='checkbox' name='choice[".$questionId."][".$answerId."]' value='1'>
			  </td>
			  <td>";
			$answer = api_parse_tex($answer);
			$s.=$answer;
			$s.="</td></tr>";

			}
			// fill in blanks
			elseif($answerType == FILL_IN_BLANKS)
			{
			$s.="<tr><td colspan='2'>$answer</td></tr>";

			}
			// free answer

			// matching
			else
			{
				if(!$answerCorrect)
				{
					// options (A, B, C, ...) that will be put into the list-box
					$Select[$answerId]['Lettre']=$cpt1++;
					// answers that will be shown at the right side
					$answer = api_parse_tex($answer);
					$Select[$answerId]['Reponse']=$answer;
				}
				else
				{
					$s.="
					<tr>
					  <td colspan='2'>
						<table border='0' cellpadding='0' cellspacing='0' width='100%'>
						<tr>";
					$answer=api_parse_tex($answer);
					$s.="<td width='40%' valign='top'><b>".$cpt2."</b>.&nbsp;".$answer."</td>
						  <td width='20%' align='center'>&nbsp;&nbsp;<select name='choice[".$questionId."][".$answerId."]'>
							<option value='0'>--</option>";

		            // fills the list-box
		            foreach($Select as $key=>$val)
		            {

						$s.="<option value='".$key."'>".$val['Lettre']."</option>";

					}  // end foreach()

					$s.="</select>&nbsp;&nbsp;</td>
						  <td width='40%' valign='top'>";
					if(isset($Select[$cpt2])) $s.='<b>'.$Select[$cpt2]['Lettre'].'.</b> '.$Select[$cpt2]['Reponse'];
						else $s.='&nbsp;';
					$s.="
					</td>
						</tr>
						</table>
					  </td>
					</tr>";

					$cpt2++;

					// if the left side of the "matching" has been completely shown
					if($answerId == $nbrAnswers)
					{
						// if it remains answers to shown at the right side
						while(isset($Select[$cpt2]))
						{


							$s.="<tr>
							  <td colspan='2'>
								<table border='0' cellpadding='0' cellspacing='0' width='100%'>
								<tr>
								  <td width='60%' colspan='2'>&nbsp;</td>
								  <td width='40%' valign='top'>";
							$s.='<b>'.$Select[$cpt2]['Lettre'].'.</b> '.$Select[$cpt2]['Reponse'];
							$s.="</td>
								</tr>
								</table>
							  </td>
							</tr>";


							$cpt2++;
						}	// end while()
					}  // end if()
				}
			}
		}	// end for()

		// destruction of the Answer object
		unset($objAnswerTmp);

		// destruction of the Question object
		unset($objQuestionTmp);

		if ($origin != 'export')
		{
			echo $s;
		}
		else
		{
			return($s);
		}
	}
	elseif ($answerType == HOT_SPOT) // Question is of type HOT_SPOT
	{
		$questionName=$objQuestionTmp->selectTitle();
		$questionDescription=$objQuestionTmp->selectDescription();

		// Get the answers, make a list
		$objAnswerTmp=new Answer($questionId);
		$nbrAnswers=$objAnswerTmp->selectNbrAnswers();

		$answer_list = '<div style="padding: 10px; margin-left: 0px; border: 1px solid #4271b5; height: 481px; width: 200px;"><b>'.get_lang('HotspotZones').'</b><ol>';
		for($answerId=1;$answerId <= $nbrAnswers;$answerId++)
		{
			$answer_list .= '<li>'.$objAnswerTmp->selectAnswer($answerId).'</li>';
		}
		$answer_list .= '</ol></div>';

		if(!$onlyAnswers)
		{
			$s=$questionName;
			$s.="</td>
			</tr>
			<tr>
			  <td valign='top' colspan='2'>
				<i>";
			$questionDescription=api_parse_tex($questionDescription);
			$s.=$questionDescription;
			$s.="</i>
			  </td>
			</tr>";
		}

		$canClick = isset($_GET['editQuestion']) ? '0' : (isset($_GET['modifyAnswers']) ? '0' : '1');
		//$tes = isset($_GET['modifyAnswers']) ? '0' : '1';
		//echo $tes;
		$s .= "<script type=\"text/javascript\" src=\"../plugin/hotspot/JavaScriptFlashGateway.js\"></script>
						<script src=\"../plugin/hotspot/hotspot.js\" type=\"text/javascript\"></script>
						<script language=\"JavaScript\" type=\"text/javascript\">
						<!--
						// -----------------------------------------------------------------------------
						// Globals
						// Major version of Flash required
						var requiredMajorVersion = 7;
						// Minor version of Flash required
						var requiredMinorVersion = 0;
						// Minor version of Flash required
						var requiredRevision = 0;
						// the version of javascript supported
						var jsVersion = 1.0;
						// -----------------------------------------------------------------------------
						// -->
						</script>
						<script language=\"VBScript\" type=\"text/vbscript\">
						<!-- // Visual basic helper required to detect Flash Player ActiveX control version information
						Function VBGetSwfVer(i)
						  on error resume next
						  Dim swControl, swVersion
						  swVersion = 0

						  set swControl = CreateObject(\"ShockwaveFlash.ShockwaveFlash.\" + CStr(i))
						  if (IsObject(swControl)) then
						    swVersion = swControl.GetVariable(\"\$version\")
						  end if
						  VBGetSwfVer = swVersion
						End Function
						// -->
						</script>

						<script language=\"JavaScript1.1\" type=\"text/javascript\">
						<!-- // Detect Client Browser type
						var isIE  = (navigator.appVersion.indexOf(\"MSIE\") != -1) ? true : false;
						var isWin = (navigator.appVersion.toLowerCase().indexOf(\"win\") != -1) ? true : false;
						var isOpera = (navigator.userAgent.indexOf(\"Opera\") != -1) ? true : false;
						jsVersion = 1.1;
						// JavaScript helper required to detect Flash Player PlugIn version information
						function JSGetSwfVer(i){
							// NS/Opera version >= 3 check for Flash plugin in plugin array
							if (navigator.plugins != null && navigator.plugins.length > 0) {
								if (navigator.plugins[\"Shockwave Flash 2.0\"] || navigator.plugins[\"Shockwave Flash\"]) {
									var swVer2 = navigator.plugins[\"Shockwave Flash 2.0\"] ? \" 2.0\" : \"\";
						      		var flashDescription = navigator.plugins[\"Shockwave Flash\" + swVer2].description;
									descArray = flashDescription.split(\" \");
									tempArrayMajor = descArray[2].split(\".\");
									versionMajor = tempArrayMajor[0];
									versionMinor = tempArrayMajor[1];
									if ( descArray[3] != \"\" ) {
										tempArrayMinor = descArray[3].split(\"r\");
									} else {
										tempArrayMinor = descArray[4].split(\"r\");
									}
						      		versionRevision = tempArrayMinor[1] > 0 ? tempArrayMinor[1] : 0;
						            flashVer = versionMajor + \".\" + versionMinor + \".\" + versionRevision;
						      	} else {
									flashVer = -1;
								}
							}
							// MSN/WebTV 2.6 supports Flash 4
							else if (navigator.userAgent.toLowerCase().indexOf(\"webtv/2.6\") != -1) flashVer = 4;
							// WebTV 2.5 supports Flash 3
							else if (navigator.userAgent.toLowerCase().indexOf(\"webtv/2.5\") != -1) flashVer = 3;
							// older WebTV supports Flash 2
							else if (navigator.userAgent.toLowerCase().indexOf(\"webtv\") != -1) flashVer = 2;
							// Can't detect in all other cases
							else 
							{
								flashVer = -1;
							}
							return flashVer;
						}
						// When called with reqMajorVer, reqMinorVer, reqRevision returns true if that version or greater is available

						function DetectFlashVer(reqMajorVer, reqMinorVer, reqRevision)
						{
						 	reqVer = parseFloat(reqMajorVer + \".\" + reqRevision);
						   	// loop backwards through the versions until we find the newest version
							for (i=25;i>0;i--) {
								if (isIE && isWin && !isOpera) {
									versionStr = VBGetSwfVer(i);
								} else {
									versionStr = JSGetSwfVer(i);
								}
								if (versionStr == -1 ) {
									return false;
								} else if (versionStr != 0) {
									if(isIE && isWin && !isOpera) {
										tempArray         = versionStr.split(\" \");
										tempString        = tempArray[1];
										versionArray      = tempString .split(\",\");
									} else {
										versionArray      = versionStr.split(\".\");
									}
									versionMajor      = versionArray[0];
									versionMinor      = versionArray[1];
									versionRevision   = versionArray[2];

									versionString     = versionMajor + \".\" + versionRevision;   // 7.0r24 == 7.24
									versionNum        = parseFloat(versionString);
						        	// is the major.revision >= requested major.revision AND the minor version >= requested minor
									if ( (versionMajor > reqMajorVer) && (versionNum >= reqVer) ) {
										return true;
									} else {
										return ((versionNum >= reqVer && versionMinor >= reqMinorVer) ? true : false );
									}
								}
							}
						}
						// -->
						</script>";
		$s .= '<tr><td valign="top" colspan="2" width="100%"><table><tr><td width="570">'."
					<script language=\"JavaScript\" type=\"text/javascript\">
						<!--
						// Version check based upon the values entered above in \"Globals\"
						var hasReqestedVersion = DetectFlashVer(requiredMajorVersion, requiredMinorVersion, requiredRevision);


						// Check to see if the version meets the requirements for playback
						if (hasReqestedVersion) {  // if we've detected an acceptable version
						    var oeTags = '<object type=\"application/x-shockwave-flash\" data=\"../plugin/hotspot/hotspot_user.swf?modifyAnswers=".$questionId."&amp;canClick:".$canClick."\" width=\"556\" height=\"501\">'
										+ '<param name=\"movie\" value=\"../plugin/hotspot/hotspot_user.swf?modifyAnswers=".$questionId."&amp;canClick:".$canClick."\" \/>'
										+ '<\/object>';
						    document.write(oeTags);   // embed the Flash Content SWF when all tests are passed
						} else {  // flash is too old or we can't detect the plugin
							var alternateContent = 'Error<br \/>'
								+ 'Hotspots requires Macromedia Flash 7.<br \/>'
								+ '<a href=http://www.macromedia.com/go/getflash/>Get Flash<\/a>';
							document.write(alternateContent);  // insert non-flash content
						}
						// -->
					</script></td>
					<td valign='top' align='left'>$answer_list</td></tr></table>
		</td></tr>";
		echo $s;

	}
	echo "<tr><td colspan='2'>&nbsp;</td></tr>";

	return $nbrAnswers;
}
?>