<?php
// $Id: infocours.php 10902 2007-01-25 14:44:35Z elixir_julian $
/*
==============================================================================
	Dokeos - elearning and course management software

	Copyright (c) 2004 Dokeos S.A.
	Copyright (c) 2003 Ghent University (UGent)
	Copyright (c) 2001 Universite catholique de Louvain (UCL)
	Copyright (c) Hugues Peeters
	Copyright (c) Roan Embrechts (Vrije Universiteit Brussel)
	Copyright (c) Olivier Brouckaert
	Copyright (c) Bart Mollet, Hogeschool Gent

	For a full list of contributors, see "credits.txt".
	The full license can be read in "license.txt".

	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	See the GNU General Public License for more details.

	Contact: Dokeos, 181 rue Royale, B-1000 Brussels, Belgium, info@dokeos.com
==============================================================================


==============================================================================
	   INIT SECTION
==============================================================================
*/
// name of the language file that needs to be included
$language_file = array ('create_course', 'course_info');
include ('../../main/inc/global.inc.php');
$this_section = SECTION_COURSES;

$nameTools = get_lang("ModifInfo");

$course_code = $_course["sysCode"];

// WARNING : try to use GARBAGE_PATH ? but no WEB_GARBAGE ? (api_get_path)
$app_share_tmp_dir_base = api_get_path(SYS_CODE_PATH).'garbage/app_share/';
mkdir ($app_share_tmp_dir_base, 0700); 
$app_share_tmp_dir = $app_share_tmp_dir_base.$course_code;
$app_base_file = api_get_path(SYS_CODE_PATH).'app_share/DokeosAppShare.exe';
$app_share_app_file = $app_share_tmp_dir.'/DokeosAppShare.exe';

$specialCode='';
if (file_exists($app_share_app_file) == FALSE) {
	
	mkdir ($app_share_tmp_dir, 0700);
	
	if (file_exists($app_base_file) == FALSE) {
		echo('FATAL ERROR: file <b>'.$app_base_file.'</b> not found.<br />');
	} else {
		$source = fopen($app_base_file, "r");
		$target = fopen($app_share_app_file, "a" );
		
		$specialCode = rand(100000,999999).time().rand(100000,999999);
		$contents = fread ($source, filesize ($app_base_file));
		fwrite ($target, $contents, filesize ($app_base_file));
		fwrite ($target, $specialCode, filesize ($app_base_file));
		fclose($source);
		fclose($target);
	}
	
} else {
	$source = fopen($app_share_app_file, "r" );
	fread ($source, filesize ($app_base_file)); // skip binary content
	$serverID = fread ($source, filesize($app_share_app_file)-filesize ($app_base_file));
	fclose($source);
}

/*
==============================================================================
		HEADER
==============================================================================
*/
Display :: display_header("appShare");

if ($_GET["client"] == 'true') {
	?>
	<HTML>
  <HEAD><TITLE> [test viewApplet appShare] </TITLE></HEAD>
  <BODY>
  <SPAN style='position: absolute; top:0px;left:0px'>
<OBJECT style="width: 100%; height: 100%"
    ID='DokeosSharing'
    classid = 'clsid:8AD9C840-044E-11D1-B3E9-00805F499D93'
    codebase = 'http://java.sun.com/update/1.4.2/jinstall-1_4-windows-i586.cab#Version=1,4,0,0'
    WIDTH = 1000 HEIGHT = 700 >
    <PARAM NAME = CODE VALUE = VncViewer.class >
    <PARAM NAME = ARCHIVE VALUE = VncViewer.jar >
    <PARAM NAME = 'type' VALUE = 'application/x-java-applet;version=1.4'>
    <PARAM NAME = 'scriptable' VALUE = 'false'>
    <PARAM NAME = PORT VALUE=443>
	<PARAM NAME = 'HOST' VALUE='dokeos.noctis.be'>
	<PARAM NAME = 'SERVERID' VALUE='<?php echo($serverID);?>'>
    <PARAM NAME = ENCODING VALUE=Tight>
    <PARAM NAME = 'Open New Window' VALUE='Yes'>
    <COMMENT>
	<EMBED 
            type = 'application/x-java-applet;version=1.4' \
            CODE = VncViewer.class \
            ARCHIVE = VncViewer.jar \
            WIDTH = 1000 \
            HEIGHT = 700 \
            PORT = 443 \
			SERVERID = '<?php echo($serverID);?>' \
            ENCODING =Tight \
	    scriptable = false \
	    pluginspage ='http://java.sun.com/products/plugin/index.html#download'>
	    <NOEMBED>
            </NOEMBED>
	</EMBED>
    </COMMENT>
</OBJECT>
  </SPAN>
  </BODY>
</HTML>
	<?php
} else {

if (api_is_allowed_to_edit()) {
	// WARNING : try to use GARBAGE_PATH ? but no WEB_GARBAGE ? (api_get_path) 
	$linkToFile = api_get_path(WEB_PATH).'main/garbage/app_share/'.$course_code.'/DokeosAppShare.exe';
?>
	 <h3>pr�requis pour DokeosAppShare :</h3>
	 <ul>
	  <li>Microsoft .NET : <a target="top" href="http://www.microsoft.com/downloads/details.aspx?familyid=0856eacb-4362-4b0d-8edd-aab15c5e04f5&displaylang=en">install</a></li>
	  <li style="margin-top: 5px;">Visual J# Redistributable Packages : <a target="top" href="http://msdn2.microsoft.com/en-us/vjsharp/bb188598.aspx">install</a></li>
	 </ul>
	 <form style="float: left;" id="view_screen" action="<?php echo($linkToFile);?>">
	 <input style="width: 220px; font-size: 14px; font-weight: bold;" type="submit" value="Partager mon �cran" />
	 </form>
<?php
}?>
	 <form style="float: left;" id="view_screen" action="">
	  <input type="hidden" name="client" value="true" />
	  <input style="margin-left: 20px; width: 220px; font-size: 14px; font-weight: bold;" type="submit" value="Visualiser l'�cran partag�" />
	 </form><?php
}

/*
==============================================================================
		FOOTER
==============================================================================
*/
Display::display_footer();?>

