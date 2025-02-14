<?php
$language_file='admin';

$cidReset=true;

include('../inc/global.inc.php');

api_protect_admin_script(true);

$tbl_session=Database::get_main_table(TABLE_MAIN_SESSION);
$tbl_session_rel_course=Database::get_main_table(TABLE_MAIN_SESSION_COURSE);
$tbl_session_rel_course_rel_user=Database::get_main_table(TABLE_MAIN_SESSION_COURSE_USER);
$tbl_session_rel_user=Database::get_main_table(TABLE_MAIN_SESSION_USER);

$page=intval($_GET['page']);
$action=$_REQUEST['action'];
$sort=in_array($_GET['sort'],array('name','nbr_courses','date_start','date_end'))?$_GET['sort']:'name';
$idChecked = $_REQUEST['idChecked'];


if($action == 'delete')
{
	if(is_array($idChecked))
	{
		$idChecked=Database::escape_string(implode(',',$idChecked));
	}
	else
	{
		$idChecked=intval($idChecked);
	}
	
	if(!api_is_platform_admin())
	{
		$sql = 'SELECT session_admin_id FROM '.Database :: get_main_table(TABLE_MAIN_SESSION).' WHERE id='.$idChecked;
		$rs = api_sql_query($sql,__FILE__,__LINE__);
		if(mysql_result($rs,0,0)!=$_user['user_id'])
		{
			api_not_allowed(true);
		}
	}

	api_sql_query("DELETE FROM $tbl_session WHERE id IN($idChecked)",__FILE__,__LINE__);

	api_sql_query("DELETE FROM $tbl_session_rel_course WHERE id_session IN($idChecked)",__FILE__,__LINE__);

	api_sql_query("DELETE FROM $tbl_session_rel_course_rel_user WHERE id_session IN($idChecked)",__FILE__,__LINE__);
	
	api_sql_query("DELETE FROM $tbl_session_rel_user WHERE id_session IN($idChecked)",__FILE__,__LINE__);

	header('Location: '.api_get_self().'?sort='.$sort);
	exit();
}

$limit=20;
$from=$page * $limit;

//if user is crfp admin only list its sessions
if(!api_is_platform_admin())
{
	$where = 'WHERE session_admin_id='.intval($_user['user_id']);
	$where .= (empty($_POST['keyword']) ? " " : " AND name LIKE '%".addslashes(trim($_POST['keyword']))."%'");
}
else
	$where .= (empty($_POST['keyword']) ? " " : " WHERE name LIKE '%".addslashes(trim($_POST['keyword']))."%'");

$result=api_sql_query("SELECT id,name,nbr_courses,date_start,date_end 
						FROM $tbl_session 
						 $where
						ORDER BY $sort 
						LIMIT $from,".($limit+1),__FILE__,__LINE__);

$Sessions=api_store_result($result);

$nbr_results=sizeof($Sessions);

$tool_name = get_lang('SessionList');

$interbreadcrumb[]=array("url" => "index.php","name" => get_lang('AdministrationTools'));

Display::display_header($tool_name);

api_display_tool_title($tool_name);
?>

<div id="main">

<?php

if(isset($_GET['action'])){
	Display::display_normal_message(Security::remove_XSS($_GET['message']), false);
}

?>
<form method="POST" action="session_list.php">
	<input type="text" name="keyword" value="<?php echo Security::remove_XSS(trim($_GET['keyword'])); ?>"/>
	<input type="submit" value="<?php echo get_lang('Search'); ?>"/>
	</form>
<form method="post" action="<?php echo api_get_self(); ?>?action=delete&sort=<?php echo $sort; ?>" onsubmit="javascript:if(!confirm('<?php echo get_lang('ConfirmYourChoice'); ?>')) return false;">

<div align="left">

<?php

if(count($Sessions)==0 && isset($_POST['keyword']))
{
	echo get_lang('NoSearchResults');
}
else
{
	if($page)
	{
	?>

	<a href="<?php echo api_get_self(); ?>?page=<?php echo $page-1; ?>&sort=<?php echo $sort; ?>"><?php echo get_lang('Previous'); ?></a>

	<?php
	}
	else
	{
		echo get_lang('Previous');
	}
	?>

	|

	<?php
	if($nbr_results > $limit)
	{
	?>

	<a href="<?php echo api_get_self(); ?>?page=<?php echo $page+1; ?>&sort=<?php echo $sort; ?>"><?php echo get_lang('Next'); ?></a>

	<?php
	}
	else
	{
		echo get_lang('Next');
	}
	?>

	</div>

	<br>

	<table class="data_table" width="100%">
	<tr>
	  <th>&nbsp;</th>
	  <th><a href="<?php echo api_get_self(); ?>?sort=name"><?php echo get_lang('NameOfTheSession'); ?></a></th>
	  <th><a href="<?php echo api_get_self(); ?>?sort=nbr_courses"><?php echo get_lang('NumberOfCourses'); ?></a></th>
	  <th><a href="<?php echo api_get_self(); ?>?sort=date_start"><?php echo get_lang('StartDate'); ?></a></th>
	  <th><a href="<?php echo api_get_self(); ?>?sort=date_end"><?php echo get_lang('EndDate'); ?></a></th>
	  <th><?php echo get_lang('Actions'); ?></th>
	</tr>

	<?php
	$i=0;

	foreach($Sessions as $key=>$enreg)
	{
		if($key == $limit)
		{
			break;
		}
		$sql = 'SELECT COUNT(course_code) FROM '.$tbl_session_rel_course.' WHERE id_session='.intval($enreg['id']);

	  	$rs = api_sql_query($sql, __FILE__, __LINE__);
	  	list($nb_courses) = Database::fetch_array($rs);

	?>

	<tr class="<?php echo $i?'row_odd':'row_even'; ?>">
	  <td><input type="checkbox" name="idChecked[]" value="<?php echo $enreg['id']; ?>"></td>
	  <td><a href="resume_session.php?id_session=<?php echo $enreg['id']; ?>"><?php echo htmlspecialchars($enreg['name'],ENT_QUOTES,$charset); ?></a></td>
	  <td><a href="session_course_list.php?id_session=<?php echo $enreg['id']; ?>"><?php echo $nb_courses; echo get_lang('cours')?> </a></td>
	  <td><?php echo htmlspecialchars($enreg['date_start'],ENT_QUOTES,$charset); ?></td>
	  <td><?php echo htmlspecialchars($enreg['date_end'],ENT_QUOTES,$charset); ?></td>
	  <td>
		<a href="add_users_to_session.php?page=session_list.php&id_session=<?php echo $enreg['id']; ?>"><img src="../img/add_user_big.gif" border="0" align="absmiddle" title="<?php echo get_lang('SubscribeUsersToSession'); ?>"></a>
		<a href="add_courses_to_session.php?page=session_list.php&id_session=<?php echo $enreg['id']; ?>"><img src="../img/synthese_view.gif" border="0" align="absmiddle" title="<?php echo get_lang('SubscribeCoursesToSession'); ?>"></a>
		<a href="session_edit.php?page=session_list.php&id=<?php echo $enreg['id']; ?>"><img src="../img/edit.gif" border="0" align="absmiddle" title="<?php echo get_lang('Edit'); ?>"></a>
		<a href="<?php echo api_get_self(); ?>?sort=<?php echo $sort; ?>&action=delete&idChecked=<?php echo $enreg['id']; ?>" onclick="javascript:if(!confirm('<?php echo get_lang('ConfirmYourChoice'); ?>')) return false;"><img src="../img/delete.gif" border="0" align="absmiddle" title="<?php echo get_lang('Delete'); ?>" alt="<?php echo get_lang('Delete'); ?>"></a>
	  </td>
	</tr>

	<?php
		$i=$i ? 0 : 1;
	}

	unset($Sessions);

	?>

	</table>

	<br>

	<div align="left">

	<?php
	if($page)
	{
	?>

	<a href="<?php echo api_get_self(); ?>?page=<?php echo $page-1; ?>&sort=<?php echo $sort; ?>"><?php echo get_lang('Previous'); ?></a>

	<?php
	}
	else
	{
		echo get_lang('Previous');
	}
	?>

	|

	<?php
	if($nbr_results > $limit)
	{
	?>

	<a href="<?php echo api_get_self(); ?>?page=<?php echo $page+1; ?>&sort=<?php echo $sort; ?>"><?php echo get_lang('Next'); ?></a>

	<?php
	}
	else
	{
		echo get_lang('Next');
	}
	?>

	</div>

	<br>

	<select name="action">
	<option value="delete"><?php echo get_lang('DeleteSelectedSessions'); ?></option>
	</select>
	<input type="submit" value="<?php echo get_lang('Ok'); ?>">
	<?php } ?>
</table>

</div>

<?php

Display::display_footer();
?>