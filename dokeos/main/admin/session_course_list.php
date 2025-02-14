<?php
// name of the language file that needs to be included
$language_file='admin';

$cidReset=true;

include('../inc/global.inc.php');

// setting the section (for the tabs)
$this_section=SECTION_PLATFORM_ADMIN;

api_protect_admin_script();

// Database Table Definitions
$tbl_course							= Database::get_main_table(TABLE_MAIN_COURSE);
$tbl_session						= Database::get_main_table(TABLE_MAIN_SESSION);
$tbl_session_rel_course				= Database::get_main_table(TABLE_MAIN_SESSION_COURSE);
$tbl_session_rel_course_rel_user	= Database::get_main_table(TABLE_MAIN_SESSION_COURSE_USER);

$id_session=intval($_GET['id_session']);
$page=intval($_GET['page']);
$action=$_REQUEST['action'];
$sort=in_array($_GET['sort'],array('title','nbr_users'))?$_GET['sort']:'title';

$result=api_sql_query("SELECT name FROM $tbl_session WHERE id='$id_session'",__FILE__,__LINE__);

if(!list($session_name)=mysql_fetch_row($result))
{
	header('Location: session_list.php');
	exit();
}

if($action == 'delete')
{
	$idChecked = $_POST['idChecked'];
	if(is_array($idChecked))
	{
		$idChecked="'".implode("','",$idChecked)."'";

		api_sql_query("DELETE FROM $tbl_session_rel_course WHERE id_session='$id_session' AND course_code IN($idChecked)",__FILE__,__LINE__);

		$nbr_affected_rows=mysql_affected_rows();

		api_sql_query("DELETE FROM $tbl_session_rel_course_rel_user WHERE id_session='$id_session' AND course_code IN($idChecked)",__FILE__,__LINE__);

		api_sql_query("UPDATE $tbl_session SET nbr_courses=nbr_courses-$nbr_affected_rows WHERE id='$id_session'",__FILE__,__LINE__);
	}

	header('Location: '.api_get_self().'?id_session='.$id_session.'&sort='.$sort);
	exit();
}

$limit=20;
$from=$page * $limit;

$result=api_sql_query("SELECT code,title,nbr_users FROM $tbl_session_rel_course,$tbl_course WHERE course_code=code AND id_session='$id_session' ORDER BY $sort LIMIT $from,".($limit+1),__FILE__,__LINE__);

$Courses=api_store_result($result);

$nbr_results=sizeof($Sessions);

$tool_name = htmlspecialchars($session_name,ENT_QUOTES,$charset).' : '.get_lang('CourseListInSession');

$interbreadcrumb[]=array('url' => 'index.php',"name" => get_lang('PlatformAdmin'));
$interbreadcrumb[]=array('url' => "session_list.php","name" => get_lang('SessionList'));

Display::display_header($tool_name);

api_display_tool_title($tool_name);
?>

<div id="main">

<form method="post" action="<?php echo api_get_self(); ?>?id_session=<?php echo $id_session; ?>&sort=<?php echo $sort; ?>" onsubmit="javascript:if(!confirm('<?php echo get_lang('ConfirmYourChoice'); ?>')) return false;">

<br>


<?php
$tableHeader = array();
$tableHeader[] = array(' ');
$tableHeader[] = array(get_lang('CourseTitle'));
$tableHeader[] = array(get_lang('NbUsers'));
$tableHeader[] = array(get_lang('Actions'));


$tableCourses = array();
foreach($Courses as $key=>$enreg)
{
	$course = array();
	$course[] = '<input type="checkbox" name="idChecked[]" value="'.$enreg['code'].'">';
	$course[] = htmlspecialchars($enreg['title'],ENT_QUOTES,$charset);
	$course[] = '<a href="session_course_user_list.php?id_session='.$id_session.'&course_code='.$enreg['code'].'">'.$enreg['nbr_users'].' '.get_lang('Users').'</a>';
	$course[] = '<a href="session_course_edit.php?id_session='.$id_session.'&page=session_course_list.php&course_code='.$enreg['code'].'"><img src="../img/edit.gif" border="0" align="absmiddle" title="'.get_lang('Edit').'"></a>
				<a href="'.api_get_self().'?id_session='.$id_session.'&sort='.$sort.'&action=delete&idChecked[]='.$enreg['code'].'" onclick="javascript:if(!confirm(\''.addslashes(htmlspecialchars(get_lang("ConfirmYourChoice"),ENT_QUOTES,$charset)).'\')) return false;"><img src="../img/delete.gif" border="0" align="absmiddle" title="'.get_lang('Delete').'"></a>';
	$tableCourses[] = $course;
}
echo '<form method="post" action="'.api_get_self().'">';
Display :: display_sortable_table($tableHeader, $tableCourses, array (), array ());
echo '
	<select name="action">
	<option value="delete">'.get_lang('UnsubscribeCoursesFromSession').'</option>
	</select>
	<input type="submit" value="'.get_lang('Ok').'">
	</form>';
?>


<?php
Display::display_footer();
?>