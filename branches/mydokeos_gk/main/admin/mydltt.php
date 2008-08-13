<?php
/**
* @author: Patrick Cool <patrick.cool@UGent.be>, Ghent University
* @date: march 2005 (during the commuting train rides)
* @comment: 	The idea of this is to allow easy (specific) language translation 
*				for the current platform. With this tool it is no longer needed to 
*				get onto the filesystem to modify a language variable.
*				This is a sort of a DLTT (Dokeos Language Translation Tool:
*				http://www.dokeos.com/DLTT) but for the current platform only. 
*				
*				Translations are made in 3 steps:
*				1: you select the file which contains the language variable that
*				   you want to translate (=> reading all the language variables
*				   in that file)
*				2: you edit the language variable of your choice
*				3: you submit your change (=> writing the language file)
*
* @todo: 	add features to search for a particular word in a certain language. This can be 
* 			done by getting the get_defined_vars first, including all the language files of
*			of the language we are looking for and getting the get_defined_vars again.
*			If we then do a array_diff then we have all the language variables of all the 
*			language files of that particular language. We can now loop the array and check
*			if the array value contains the string we are looking for. 
* @todo: add a feature that allows you to add a new language variable (for custom developments)				
*/

//language variables
$SelectFile="You have to select a file first. ";
$MakePersonalTranslation="myDLTT";
$TranslationMessage="This tool is designed te allow easy (custom) language translations for the current platform. 
					With this tool it is no longer needed to go to the 	filesystem and change the language files. 
					When you find translation mistakes, please correct them in the <a href='http://www.dokeos.com/DLTT'>DLTT (Dokeos Language Translation Tool)</a> also.
					By doing so you are actively contributing to the quality of Dokeos. <br /> <a href='".$_SERVER['PHP_SELF']."?action=hidemessage'><<< hide this message</a>";

// we are in the platform admin section so the course_id has to be unset
$cidReset=true;

$htmlHeadXtra[]="<style type=\"text/css\">
<!--
.translate_menu {
	color: #EFEFEF;
	border: 1px solid #666666;
}
-->
</style>
";

// including the global file
include('../inc/global.inc.php');

// ***********************************************************************
// STEP 3: a translation was submitted
// ***********************************************************************
if ($_POST)
	{
	// step 3 part 1: we get all the language variables in the english language variable because we assume that
	// english is the most complete language and it contains all the needed language variables.
	// to get all the english language variables we put all the defined variables (language variables and others)
	// in an array, we include the language variable, we get the defined variables again and put them in a second array
	// and by calculating the difference between the first and the second array we will come up with an array that contains only
	// the language variables of the included language file
	$langvars_before=get_defined_vars();
	include "../lang/english/".$_GET['file'];
	$langvars_after=get_defined_vars();
	$langvars_diff=array_diff($langvars_after, $langvars_before);
	// step 3 part 2: we create an associative array with all the language variables as the key and the translation into
	// the target language as the values. For this we need to include the target language.
	include "../lang/".$_SESSION['targetlanguage']."/".$_GET['file'];
	foreach ($langvars_diff as $key=>$value)
		{
		//echo $key."/".$$key."***";
		$write2file[$key]=$$key;
		}
	// step 3 part 3: now we change the array with the language variables for the language variable that we submitted
	// (we want to change this language variable). Theref
	// Off course we have to use the translation that the platform administrator in the form
	$write2file[$_GET['variable']]=$_POST['newtranslation'];
	
	// step 3 part 4: we create one variable that contains all the content that has to be written into the language file
	// this contains the opening and closing php tags, the comment and all the language variable definitions
	$write2file_flat="<?php \n"; // the opening php tag
	$write2file_flat.="// this file contains some re-translated language variables. You can get the original Dokeos language files on http://www.dokeos.com/DLTT \n"; // the comment
	foreach ($write2file as $key=>$value) // all the language variable definitions
		{$write2file_flat.="\$$key=\"$value\";\n";}
	$write2file_flat.=" ?>"; // the closing php tag
	
	// step 3 part 5: we write the variable that contains all the content of the language file into the language file
	// writing permissions to this file are needed
	// 2do: file permissions checking
	$handle=$rootSys.$clarolineRepositoryAppend."lang/".$_SESSION['targetlanguage']."/".$_GET['file'];
	$file=fopen($handle,w);
	fwrite($file,$write2file_flat);
	
	// step 3 part 6: some cleaning up and a redirection to the page that contains an overview of all the language variables 
	// inside the selected file. You can see the changes immediately.
	unset($file);
	unset($write2file);
	unset($write2file_flat);
	header("Location: mydltt.php?file=".$_GET['file']);
	exit;
	}


// setting the error reporting (this was for testing purposes but can now probably be deleted)
error_reporting(E_ALL & ~E_NOTICE);

// the name of the tool
$nameTools=$MakePersonalTranslation;

// the breadcrumbs
$interbredcrump[]=array("url" => $rootAdminWeb,"name" => 'Platform Administration');

// Sessions: we use English as the default source language and the platform language as the default platform language when no source or targetlanguage is set
if (!$_SESSION['sourcelanguage'])
	{$_SESSION['sourcelanguage']="english";}
if (!$_SESSION['targetlanguage'])
	{$_SESSION['targetlanguage']=$platformLanguage;}
	
// When the admin selects a language from the dropdown menu
if ($_GET['sourcelanguage'])
	{$_SESSION['sourcelanguage']=$_GET['sourcelanguage'];}
if ($_GET['targetlanguage'])
	{$_SESSION['targetlanguage']=$_GET['targetlanguage'];}

// Onlye the Platform Admin is allowed here	
$is_allowedToAdmin=$is_platformAdmin;

//including the header
include($includePath.'/header.inc.php');

// displaying the tool title (Make personal translation)
api_display_tool_title($nameTools);

// display the message if $_SESSION['intromessage'] does not exist or differs from 'hide'
if ($_GET['action']=='hidemessage')
{
	$_SESSION['intromessage']='hide';
}
if (!$_SESSION['intromessage'] OR $_SESSION['intromessage']<>'hide')
{
	Display::display_normal_message($TranslationMessage);
}

// if the user is not the platform admin and she/he guesses the URL (It's open source you know) she/he will get an error message.
if(!$is_allowedToAdmin)
{
	die($langNotAllowed);
}

// STEP 2: the platform administrator has chosen a certain file and we have now to display all the language variables (in the source and target language) that 
// occur in  this language file. Assumption: The english language file contains all the language variables that are needed (not more, not less)
if ($_GET['file'])
	{
	// the language variables: we assume that english is the most complete language and contains all the language variables
	$langvars_before=get_defined_vars();
	include "../lang/english/".$_GET['file'];
	$langvars_after=get_defined_vars();
	$langvars_diff=array_diff($langvars_after, $langvars_before);
	//print_r($langvars_after);
	// resetting all the language variables that got included: english
	foreach ($langvars_diff as $key=>$value)
		{ unset($$key);}
	
	//getting all the source translations into the array
	include "../lang/".$_SESSION['sourcelanguage']."/".$_GET['file'];
	foreach ($langvars_diff as $key=>$value)
		{
		$translation[$key]['sourcelanguage']=$$key;
		}
	// resetting all the language variables that got included: sourcelanguage
	foreach ($langvars_diff as $key=>$value)
		{ unset($$key);}
	//echo "abc<pre>";
	//print_r($translation);
	//echo "</pre>";
	//echo $_SESSION['sourcelanguage'];
	
	//getting all the target translations into the array
	include "../lang/".$_SESSION['targetlanguage']."/".$_GET['file'];
	foreach ($langvars_diff as $key=>$value)
		{
		$translation[$key]['targetlanguage']=$$key;
		}
	// resetting all the language variables that got included: targetlanguage
	foreach ($langvars_diff as $key=>$value)
		{ unset($$key);}
		
	//echo "<pre>";
	//print_r($translation);
	//echo "</pre>";
	
	/*//the source language
	$source_array_before=get_defined_vars();
	include "../lang/".$_SESSION['sourcelanguage']."/".$_GET['file'];
	$source_array_after=get_defined_vars();
	$source_difference=array_diff($source_array_after, $source_array_before);

	//the target language
	$target_array_before=get_defined_vars();
	include "../lang/".$_SESSION['targetlanguage']."/".$_GET['file'];
	$target_array_after=get_defined_vars();
	$target_difference=array_diff($target_array_after, $target_array_before);
	/*
	echo "<pre>";
	print_r($target_difference);
	echo "<pre>";
	*/
	
	}
?>
<script language="JavaScript" type="text/JavaScript">
<!--
function MM_jumpMenu(targ,selObj,restore){ //v3.0
  eval(targ+".location='"+selObj.options[selObj.selectedIndex].value+"'");
  if (restore) selObj.selectedIndex=0;
}
//-->
</script>



<form name="form1" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>?file=<?php echo $_GET['file'];?>&variable=<?php echo $_GET['variable']; ?>">
  <table width="100%" border="0">
    <tr>
      <td valign="top" width="230" style="bgcolor: #ff0000; border: 1px solid #666666;"><strong>step 1: select file 
	  <?php 
	  if (isset($_GET['file']))
	  	{ echo "(currently viewing ".$_GET['file'].")";}
	  ?></strong><br>
	  <?php 
	  // we use english as the most complete language. 
	  // This creates a list of all the language variables
	  $english_folder="../lang/english/";
	  $all_files=ls_flat($english_folder);
	  echo "<ul>";
	  foreach ($all_files as $key => $value)
	  	{
		echo "<li><a href=\"".$_SERVER['PHP_SELF']."?file=$value\">$value</a></li>";
		}
	  echo "</ul>";
	  ?>      </td>
      <td valign="top">
	  <?php 
	  if ($_GET['file'])
	  	 {
	  ?>

      <table width="100%"  style="bgcolor: #ff0000; border: 1px solid #666666;">
	  <tr><td colspan="4">      <strong>step	2:	select the variable you want to translate locally</strong></td></tr>
<form name="form2">
				<tr><td colspan="4" align="right"><?php echo count($translation); ?> language variables defined in <?php echo $_GET['file']; ?></td></tr>

				<tr style="background-color: #EFEFEF; border: 1px solid #666666;">
			  		<td  style="background-color: #EFEFEF; border: 1px solid #666666;">variable</td>
			  		<td  style="background-color: #EFEFEF; border: 1px solid #666666; text-align: center;">
						<select name="select" onChange="MM_jumpMenu('parent',this,0)">
						<?php
							// we should change the api_get_languages function so that we can retrieve all languages or only the active ones (default)
							$all_languages=api_get_languages();
							$folder_languages=$all_languages['folder'];
							foreach ($folder_languages as $key=>$language)
							{
								echo "<option value=\"".$_SERVER['PHP_SELF']."?file=".$_GET['file']."&sourcelanguage=".$language."\"";
								showsourceselected($language);
								echo ">".$language."</option>\n";
							}
						?>
			  			</select>
					</td>
				  	<td  style="background-color: #EFEFEF; border: 1px solid #666666; text-align: center;">
						<select name="select" onChange="MM_jumpMenu('parent',this,0)">
						<?php
							// we should change the api_get_languages function so that we can retrieve all languages or only the active ones (default)
							$all_languages=api_get_languages();
							$folder_languages=$all_languages['folder'];
							foreach ($folder_languages as $key=>$language)
							{
								echo "<option value=\"".$_SERVER['PHP_SELF']."?file=".$_GET['file']."&targetlanguage=".$language."\"";
								showtargetselected($language);
								echo ">".$language."</option>\n";
							}
						?>
						  	
							<option value="<?php echo $_SERVER['PHP_SELF']."?file=".$_GET['file']."&targetlanguage=arabic"; ?>" <?php showtargetselected("arabic");?>>arabic</option>
						  	<option value="<?php echo $_SERVER['PHP_SELF']."?file=".$_GET['file']."&targetlanguage=dutch"; ?>"  <?php showtargetselected("dutch");?>>dutch</option>
						  	<option value="<?php echo $_SERVER['PHP_SELF']."?file=".$_GET['file']."&targetlanguage=english"; ?>"  <?php showtargetselected("english");?>>english</option>
			  			</select>
					</td>
				  <td  style="background-color: #EFEFEF; border: 1px solid #666666; text-align: center;">edit</td>
			  </tr>
			
			<?php 
			$i=1;
			foreach ($translation as $key => $value)
				{
				if (fmod($i,2)==0) 
				{	$style='style="background-color: #F5F5F5; border: 1px solid #666666;"';
				} 
				else
				{	$style='';}
				
				echo "<tr $style>\n";
				echo "\t<td>$i / $key</td>\n";

				echo "\t<td>".$translation[$key]['sourcelanguage']."</td>\n";
				if ($key!==$_GET['variable'])
					{ echo "\t<td>".$translation[$key]['targetlanguage']."</td>\n";}
				else
					{ echo "\t<td><input type=\"text\" name=\"newtranslation\" value=\"".$translation[$key]['targetlanguage']."\"><input type=\"Submit\" value=\"Submit\"></td>\n";}
				echo "\t<td><a href=\"".$_SERVER['PHP_SELF']."?file=".$_GET['file']."&variable=".$key."\"><img src=\"../img/edit.gif\" border=\"0\"></a></td>\n";
				echo "</tr>";
				$i++;
				}
		?>
</form>
	    </table>
	  <?php 
			}
		else
			{echo $SelectFile; }
		?>	  </td>
    </tr>
  </table>
</form>

<?php
include($includePath.'/footer.inc.php');
	// resetting all the language variables that got included: targetlanguage
	foreach ($langvars_diff as $key=>$value)
		{ unset($$key);}

?>

<?php 
//**********************************************************
//		function ls_flat($dir)
//**********************************************************
// recursive directory lister
// output is string where the filenames ar concatenated with a / 
function ls_flat($dir){
   $output=array();
   $handle = opendir($dir);
   for(;(false !== ($readdir = readdir($handle)));){
       if($readdir != '.' && $readdir != '..'){
           $path = $dir.'/'.$readdir;
           if(is_dir($path))    $output.= ls_flat($path);
           if(is_file($path) and substr($path,-3)=="php")    
		   		{
				$path=str_replace("../lang/english//","",$path);
				$output[]= $path;
				}
       }
   }
   return isset($output)?$output:false;
   closedir($handle);
}
// this function does something
function showtargetselected($language)
{
if ($language==$_SESSION['targetlanguage'])
	{echo " selected"; }
}
// this function does something
function showsourceselected($language)
{
if ($language==$_SESSION['sourcelanguage'])
	{echo " selected"; }
}

?>

