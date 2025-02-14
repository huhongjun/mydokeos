<?php //$id: $
/**
 * Controller script. Prepares the common background variables to give to the scripts corresponding to
 * the requested action
 * @package dokeos.learnpath
 * @author Yannick Warnier <ywarnier@beeznest.org>
 */
 // zml edit ??��?��?��?????  �??????��?????????????????�?????????��?????请�??
/**
 * Initialisations
 */
 error_reporting(0);
$debug = 0;
if($debug>0) error_log('New LP -+- Entered lp_controller.php -+-',0);
// name of the language file that needs to be included 
// zml add ???�??????��??�?�????件�?????�?
if (isset($_GET['action']))
{ 
	if($_GET['action'] == 'export')
	{ //only needed on export ??????�?export
		$language_file[] = 'hotspot';
		$language_file[] = 'exercice';
	}
}
$language_file[] = "course_home";
$language_file[] = "scormdocument";
$language_file[] = "scorm";
$language_file[] = "learnpath";
$language_file[] = 'resourcelinker';

//flag to allow for anonymous user - needs to be set before global.inc.php
// zml edit ??? global.inc.php �???? ???�?设置???许�?��????��?��?????�?
$use_anonymous = true;

//include class definitions before session_start() to ensure availability when touching
//session vars containing learning paths
// zml edit ???session�?�???????类�??�?�?�????�?�?�?�?session ??? vars �????�?�?�?�?
require_once('learnpath.class.php');
if($debug>0) error_log('New LP - Included learnpath',0);
require_once('learnpathItem.class.php');
if($debug>0) error_log('New LP - Included learnpathItem',0);
require_once('scorm.class.php');
if($debug>0) error_log('New LP - Included scorm',0);
require_once('scormItem.class.php');
if($debug>0) error_log('New LP - Included scormItem',0);
require_once('aicc.class.php');
if($debug>0) error_log('New LP - Included aicc',0);
require_once('aiccItem.class.php');
if($debug>0) error_log('New LP - Included aiccItem',0);

require_once('back_compat.inc.php');
if($debug>0) error_log('New LP - Included back_compat',0);

if ($is_allowed_in_course == false){
	api_not_allowed(true);
}

require_once(api_get_path(LIBRARY_PATH) . "/fckeditor/fckeditor.php");
$lpfound = false;

$myrefresh = 0;
$myrefresh_id = 0;
if(!empty($_SESSION['refresh']) && $_SESSION['refresh']==1){
	//check if we should do a refresh of the oLP object (for example after editing the LP)
	//if refresh is set, we regenerate the oLP object from the database (kind of flush)
	// zml edit �???��????????�?�?oLP对象???�???��?��??�???��??�?LP�????�?�??????��?��??设置�????�?�???��??�??????????oLP对象�????�???��?��??
	api_session_unregister('refresh');
	$myrefresh = 1;
	if($debug>0) error_log('New LP - Refresh asked',0);
}
if($debug>0) error_log('New LP - Passed refresh check',0);

if(!empty($_REQUEST['dialog_box'])){
	$dialog_box = stripslashes(urldecode($_REQUEST['dialog_box']));
	//stripslashes()??��????????线�??�??????��?��??�?: �????�????  �???????: �?�?�?  �?�?: string stripslashes(string str);
	//???容�?��??�??????��?��????��??�?�?串中?????????线�??�??????��??�?�?�?�???????线�???????��??�?�?�????�?�?�??????��?????�?�???????线�??就�?��?��?��?????
	
	//urldecode()�???? URL �????�?�?�? ??��?��??�?: �????�???? �???????: �?�?�? �?�?: string urldecode(string str);
	//???容�?��??�??????��?��?? URL �???????�?�?串�???????????�?????????��?????�????使�?? %## ?????��?????
}

$lp_controller_touched = 1;

if(isset($_SESSION['lpobject']))
{
	if($debug>0) error_log('New LP - SESSION[lpobject] is defined',0);
	$oLP = unserialize($_SESSION['lpobject']);
	//unserialize()对�??�????已�?????????????????�?�????�?�?�???�转??��?? PHP ?????��??
	//�??????????�????�?????????��?????�? integer???float???string???array ??? object???
	//�????�???????�?�?串�?????解�????????�????�???? FALSE??? 
	if(is_object($oLP)){
		//is_object ??��????????类�????????为类类�????? �?�?: int is_object(mixed var); �???????: ??��?? ??��?��??�?: PHP 系�???????? ???容�?��??: ??��?????为类类�?????�????true�???????�????false??? 
		if($debug>0) error_log('New LP - oLP is object',0);
		if($myrefresh == 1 OR $oLP->cc != api_get_course_id()){
			if($debug>0) error_log('New LP - Course has changed, discard lp object',0);
			if($myrefresh == 1){$myrefresh_id = $oLP->get_id();}
			$oLP = null;
			api_session_unregister('oLP');
			api_session_unregister('lpobject');
		}else{
			$_SESSION['oLP'] = $oLP;
			$lp_found = true;			
		}
	}
}
if($debug>0) error_log('New LP - Passed data remains check',0);

if($lp_found == false 
	|| ($_SESSION['oLP']->get_id() != $_REQUEST['lp_id'])
	)
{
	if($debug>0) error_log('New LP - oLP is not object, has changed or refresh been asked, getting new',0);		
	//regenerate a new lp object? Not always as some pages don't need the object (like upload?)
	// zml edit �?????????????页�?��?��??�???????�?�?lp对象�?�?�?
	if(!empty($_REQUEST['lp_id']) || !empty($myrefresh_id)){
		if($debug>0) error_log('New LP - lp_id is defined',0);
		//select the lp in the database and check which type it is (scorm/dokeos/aicc) to generate the
		//right object
		// zml edit ??��?��??�?�???????lp并�????��?????�?类�??�?scorm/dokeos/aicc�?�????产�??正确???对象
		$lp_table = Database::get_course_table('lp');
		if(!empty($_REQUEST['lp_id'])){
			$lp_id = learnpath::escape_string($_REQUEST['lp_id']);
		}else{
			$lp_id = $myrefresh_id;
		}
		$sel = "SELECT * FROM $lp_table WHERE id = $lp_id";
		if($debug>0) error_log('New LP - querying '.$sel,0);
		$res = api_sql_query($sel);
		if(Database::num_rows($res))
		{
			$row = Database::fetch_array($res);
			$type = $row['lp_type'];
			if($debug>0) error_log('New LP - found row - type '.$type. ' - Calling constructor with '.api_get_course_id().' - '.$lp_id.' - '.api_get_user_id(),0);			
			switch($type){
				case 1:
			if($debug>0) error_log('New LP - found row - type dokeos - Calling constructor with '.api_get_course_id().' - '.$lp_id.' - '.api_get_user_id(),0);			
					$oLP = new learnpath(api_get_course_id(),$lp_id,api_get_user_id());
					if($oLP !== false){ $lp_found = true; }else{eror_log($oLP->error,0);}
					break;
				case 2:
			if($debug>0) error_log('New LP - found row - type scorm - Calling constructor with '.api_get_course_id().' - '.$lp_id.' - '.api_get_user_id(),0);			
					$oLP = new scorm(api_get_course_id(),$lp_id,api_get_user_id());
					if($oLP !== false){ $lp_found = true; }else{eror_log($oLP->error,0);}
					break;
				case 3:
			if($debug>0) error_log('New LP - found row - type aicc - Calling constructor with '.api_get_course_id().' - '.$lp_id.' - '.api_get_user_id(),0);			
					$oLP = new aicc(api_get_course_id(),$lp_id,api_get_user_id());
					if($oLP !== false){ $lp_found = true; }else{eror_log($oLP->error,0);}
					break;					
				default:
			if($debug>0) error_log('New LP - found row - type other - Calling constructor with '.api_get_course_id().' - '.$lp_id.' - '.api_get_user_id(),0);			
					$oLP = new learnpath(api_get_course_id(),$lp_id,api_get_user_id());
					if($oLP !== false){ $lp_found = true; }else{eror_log($oLP->error,0);}
					break;
			}
		}
	}else{
		if($debug>0) error_log('New LP - Request[lp_id] and refresh_id were empty',0);
	}
	if($lp_found)
	{
		$_SESSION['oLP'] = $oLP;
	}
}
if($debug>0) error_log('New LP - Passed oLP creation check',0);


/**
 * Actions switching  zml edit ???�?�????
 */
 // zml edit reinitialises ??��??�?JavaScript??��?��?��?�项???�???????�?
$_SESSION['oLP']->update_queue = array(); //reinitialises array used by javascript to update items in the TOC
 // zml edit �?该使??? -> clear_message()??��??�?�?工�??
$_SESSION['oLP']->message = ''; //should use ->clear_message() method but doesn't work

$fck_attribute['Width'] = '100%';
$fck_attribute['Height'] = '950';
$fck_attribute['ToolbarSet'] = 'Full';
$fck_attribute['Config']['FullPage'] = true;

if($_GET['isStudentView'] == 'true')
{
	if($_REQUEST['action'] != 'list' AND $_REQUEST['action'] != 'view')
	{
		if(!empty($_REQUEST['lp_id']))
		{
			$_REQUEST['action'] = 'view';
		}
		else
		{
			$_REQUEST['action'] = 'list';
		}
	}
}

switch($_REQUEST['action'])
{
	case 'add_item':
		
		if(!api_is_allowed_to_edit()){
			api_not_allowed(true);
		}
		
		if($debug > 0) error_log('New LP - add item action triggered', 0);
		
		if(!$lp_found)
		{	//check if the learnpath ID was defined, otherwise send back to list
		    // zml edit �???��?????�?�?�?�? ID �?�?�?�????�??????��??�?
			error_log('New LP - No learnpath given for add item', 0); 
			require('lp_list.php'); 
		}
		else
		{
			$_SESSION['refresh'] = 1;
			
			if(isset($_POST['submit_button']) && !empty($_POST['title']))
			{	//if a title was sumbitted 				
				if(isset($_SESSION['post_time']) && $_SESSION['post_time'] == $_POST['post_time'])
				{	//check post_time to ensure ??? (counter-hacking measure?)
					require('lp_add_item.php');
				}
				else
				{
					$_SESSION['post_time'] = $_POST['post_time'];
					
					if($_POST['type'] == TOOL_DOCUMENT)
					{
						if(isset($_POST['path']) && $_GET['edit'] != 'true')
						{
							$document_id = $_POST['path'];
						}
						else
						{
							$document_id = $_SESSION['oLP']->create_document($_course);
						}
								
						$new_item_id = $_SESSION['oLP']->add_item($_POST['parent'], $_POST['previous'], $_POST['type'], $document_id, $_POST['title'], $_POST['description'], $_POST['prerequisites']);
					}
					else
					{	//for all other item types than documents, load the item using the item type and path rather than its ID
						// zml edit 对�??????????��??项�??类�????????件�?��??�????载�?�项???使�?��??项�??类�?????�?�?�??????????ID
						$new_item_id = $_SESSION['oLP']->add_item($_POST['parent'], $_POST['previous'], $_POST['type'], $_POST['path'], $_POST['title'], $_POST['description'], $_POST['prerequisites']);
					}
					//display 	??�示				
					require('lp_add_item.php');
				}
			}
			else
			{
				require('lp_add_item.php');
			}
		}
		
		break;
	
	case 'add_lp':
	
		if(!api_is_allowed_to_edit()){
			api_not_allowed(true);
		}
		
		if($debug > 0) error_log('New LP - add_lp action triggered', 0);
		
		$_REQUEST['learnpath_name'] = trim($_REQUEST['learnpath_name']);
						
		if(!empty($_REQUEST['learnpath_name']))
		{
			$_SESSION['refresh'] = 1;
			
			if(isset($_SESSION['post_time']) && $_SESSION['post_time'] == $_REQUEST['post_time'])
			{
				require('lp_add.php');
			}
			else
			{
				$_SESSION['post_time'] = $_REQUEST['post_time'];
							
				//Kevin Van Den Haute: changed $_REQUEST['learnpath_description'] by '' because it's not used
				//old->$new_lp_id = learnpath::add_lp(api_get_course_id(), $_REQUEST['learnpath_name'], $_REQUEST['learnpath_description'], 'dokeos', 'manual', '');
				$new_lp_id = learnpath::add_lp(api_get_course_id(), $_REQUEST['learnpath_name'], '', 'dokeos', 'manual', '');
				//learnpath::toggle_visibility($new_lp_id,'v');
				//Kevin Van Den Haute: only go further if learnpath::add_lp has returned an id
				if(is_numeric($new_lp_id))
				{
					//TODO maybe create a first module directly to avoid bugging the user with useless queries
					$_SESSION['oLP'] = new learnpath(api_get_course_id(),$new_lp_id,api_get_user_id());
							
					//$_SESSION['oLP']->add_item(0,-1,'dokeos_chapter',$_REQUEST['path'],'Default');
				
					require('lp_build.php');
				}
			}
		}
		else
			require('lp_add.php');
		
		break;
		
	case 'admin_view':
	
		if(!api_is_allowed_to_edit()){
			api_not_allowed(true);
		}
		
		if($debug>0) error_log('New LP - admin_view action triggered',0);
		
		if(!$lp_found){ error_log('New LP - No learnpath given for admin_view', 0); require('lp_list.php'); }
		else
		{
			$_SESSION['refresh'] = 1;
			
			require('lp_admin_view.php');
		}
		
		break;
	
	case 'build':
	
		if(!api_is_allowed_to_edit()){
			api_not_allowed(true);
		}
	
		if($debug > 0) error_log('New LP - build action triggered', 0);
		
		if(!$lp_found){ error_log('New LP - No learnpath given for build', 0); require('lp_list.php'); }
		else
		{
			$_SESSION['refresh'] = 1;
			
			require('lp_build.php');
		}
		
		break;
		
	case 'delete_item':
	
		if(!api_is_allowed_to_edit()){
			api_not_allowed(true);
		}
		
		if($debug > 0) error_log('New LP - delete item action triggered', 0);
		
		if(!$lp_found){ error_log('New LP - No learnpath given for delete item', 0); require('lp_list.php'); }
		else
		{
			$_SESSION['refresh'] = 1;
			
			if(is_numeric($_GET['id']))
			{
				$_SESSION['oLP']->delete_item($_GET['id']);
				
				$is_success = true;
			}
			
			if(isset($_GET['view']) && $_GET['view'] == 'build')
			{
				require('lp_build.php');
			}
			else
			{
				require('lp_admin_view.php');
			}
		}
		
		break;
		
	case 'edit_item':
	
		if(!api_is_allowed_to_edit()){
			api_not_allowed(true);
		}
		
		if($debug > 0) error_log('New LP - edit item action triggered', 0);
		
		if(!$lp_found){ error_log('New LP - No learnpath given for edit item', 0); require('lp_list.php'); }
		else
		{
			$_SESSION['refresh'] = 1;
			
			if(isset($_POST['submit_button']) && !empty($_POST['title']))
			{
				$_SESSION['oLP']->edit_item($_GET['id'], $_POST['parent'], $_POST['previous'], $_POST['title'], $_POST['description'], $_POST['prerequisites']);
				
				if(isset($_POST['content_lp']))
				{
					$_SESSION['oLP']->edit_document($_course);
				}
				
				$is_success = true;
			}
			
			if(isset($_GET['view']) && $_GET['view'] == 'build')
			{
				require('lp_edit_item.php');
			}
			else
			{ 
				require('lp_admin_view.php');
			}
		}
		
		break;
	
	case 'edit_item_prereq':
	
		if(!api_is_allowed_to_edit()){
			api_not_allowed(true);
		}
		
		if($debug > 0) error_log('New LP - edit item prereq action triggered', 0);
		
		if(!$lp_found){ error_log('New LP - No learnpath given for edit item prereq', 0); require('lp_list.php'); }
		else
		{
			if(isset($_POST['submit_button']))
			{
				$_SESSION['refresh'] = 1;
				
				$_SESSION['oLP']->edit_item_prereq($_GET['id'], $_POST['prerequisites'], $_POST['min_' . $_POST['prerequisites']], $_POST['max_' . $_POST['prerequisites']]);
			}
			
			require('lp_edit_item_prereq.php');
		}
		
		break;
		
	case 'move_item':
		
		if(!api_is_allowed_to_edit()){
			api_not_allowed(true);
		}
		
		if($debug > 0) error_log('New LP - move item action triggered', 0);
		
		if(!$lp_found){ error_log('New LP - No learnpath given for move item', 0); require('lp_list.php'); }
		else
		{
			$_SESSION['refresh'] = 1;
			
			if(isset($_POST['submit_button']))
			{
				$_SESSION['oLP']->edit_item($_GET['id'], $_POST['parent'], $_POST['previous'], $_POST['title'], $_POST['description']);
				
				$is_success = true;
			}
			
			if(isset($_GET['view']) && $_GET['view'] == 'build')
			{
				require('lp_move_item.php');
			}
			else
			{
				$_SESSION['oLP']->move_item($_GET['id'], $_GET['direction']);
				
				require('lp_admin_view.php');
			}
		}
		
		break;
		
	case 'view_item':
	
		if(!api_is_allowed_to_edit()){
			api_not_allowed(true);
		}		
		
		if($debug>0) error_log('New LP - view_item action triggered', 0);
		
		if(!$lp_found){ error_log('New LP - No learnpath given for view item', 0); require('lp_list.php'); }
		else
		{
			$_SESSION['refresh'] = 1;
			
			require('lp_view_item.php');
		}
		
		break;	
		
	case 'upload':
		if(!api_is_allowed_to_edit()){
			api_not_allowed(true);
		}
		if($debug>0) error_log('New LP - upload action triggered',0);
		$cwdir = getcwd();
		require('lp_upload.php');
		//reinit current working directory as many functions in upload change it
		chdir($cwdir);
		require('lp_list.php');
		break;
	case 'export':
		if(!api_is_allowed_to_edit()){
			api_not_allowed(true);
		}
		if($debug>0) error_log('New LP - export action triggered',0);
		if(!$lp_found){ error_log('New LP - No learnpath given for export',0); require('lp_list.php'); }
		else{
			$_SESSION['oLP']->scorm_export();
			exit();
			//require('lp_list.php'); 
		}
		break;
	case 'delete':
		if(!api_is_allowed_to_edit()){
			api_not_allowed(true);
		}
		if($debug>0) error_log('New LP - delete action triggered',0);
		if(!$lp_found){ error_log('New LP - No learnpath given for delete',0); require('lp_list.php'); }
		else{
			$_SESSION['refresh'] = 1;
			//remove lp from homepage if it is there
			//$_SESSION['oLP']->toggle_visibility((int)$_GET['lp_id'],'i');
			$_SESSION['oLP']->delete(null,(int)$_GET['lp_id'],'remove');
			api_session_unregister('oLP');
			require('lp_list.php');
		}
		break;
	case 'toggle_visible': //change lp visibility (inside lp tool)
		if(!api_is_allowed_to_edit()){
			api_not_allowed(true);
		}
		if($debug>0) error_log('New LP - visibility action triggered',0);
		if(!$lp_found){ error_log('New LP - No learnpath given for visibility',0); require('lp_list.php'); }
		else{
			learnpath::toggle_visibility($_REQUEST['lp_id'],$_REQUEST['new_status']);
			require('lp_list.php');
		}
		break;
	case 'toggle_publish': //change lp published status (visibility on homepage)
		if(!api_is_allowed_to_edit()){
			api_not_allowed(true);
		}
		if($debug>0) error_log('New LP - publish action triggered',0);
		if(!$lp_found){ error_log('New LP - No learnpath given for publish',0); require('lp_list.php'); }
		else{
			learnpath::toggle_publish($_REQUEST['lp_id'],$_REQUEST['new_status']);
			require('lp_list.php');
		}
		break;
	case 'move_lp_up': //change lp published status (visibility on homepage)
		if(!api_is_allowed_to_edit()){
			api_not_allowed(true);
		}
		if($debug>0) error_log('New LP - publish action triggered',0);
		if(!$lp_found)
		{ 
			error_log('New LP - No learnpath given for publish',0); 
			require('lp_list.php'); 
		}
		else
		{
			learnpath::move_up($_REQUEST['lp_id']);
			require('lp_list.php');
		}
		break;
	case 'move_lp_down': //change lp published status (visibility on homepage)
		if(!api_is_allowed_to_edit()){
			api_not_allowed(true);
		}
		if($debug>0) error_log('New LP - publish action triggered',0);
		if(!$lp_found){ 
			error_log('New LP - No learnpath given for publish',0); 
			require('lp_list.php'); 
		}
		else
		{
			learnpath::move_down($_REQUEST['lp_id']);
			require('lp_list.php');
		}
		break;
	case 'edit':
		if(!api_is_allowed_to_edit())
		{
			api_not_allowed(true);
		}
		if($debug>0) error_log('New LP - edit action triggered',0);
		if(!$lp_found){ error_log('New LP - No learnpath given for edit',0); require('lp_list.php'); }
		else{
			$_SESSION['refresh'] = 1;
			require('lp_edit.php');
		}
		break;
	case 'update_lp':
		if(!api_is_allowed_to_edit()){
			api_not_allowed(true);
		}
		if($debug>0) error_log('New LP - update_lp action triggered',0);
		if(!$lp_found){ error_log('New LP - No learnpath given for edit',0); require('lp_list.php'); }
		else{
			$_SESSION['refresh'] = 1;
			$_SESSION['oLP']->set_name($_REQUEST['lp_name']);
			$_SESSION['oLP']->set_encoding($_REQUEST['lp_encoding']);
			$_SESSION['oLP']->set_maker($_REQUEST['lp_maker']);
			$_SESSION['oLP']->set_proximity($_REQUEST['lp_proximity']);
			$_SESSION['oLP']->set_theme($_REQUEST['lp_theme']);				
			require('lp_list.php');
		}	
		break;
	case 'add_sub_item': //add an item inside a chapter
		if(!api_is_allowed_to_edit()){
			api_not_allowed(true);
		}
		if($debug>0) error_log('New LP - add sub item action triggered',0);
		if(!$lp_found){ error_log('New LP - No learnpath given for add sub item',0); require('lp_list.php'); }
		else{
			$_SESSION['refresh'] = 1;
			if(!empty($_REQUEST['parent_item_id'])){
				$_SESSION['from_learnpath']='yes';
				$_SESSION['origintoolurl'] = 'lp_controller.php?action=admin_view&lp_id='.$_REQUEST['lp_id'];
				require('resourcelinker.php');
				//$_SESSION['oLP']->add_sub_item($_REQUEST['parent_item_id'],$_REQUEST['previous'],$_REQUEST['type'],$_REQUEST['path'],$_REQUEST['title']);
			}else{
				require('lp_admin_view.php');
			}
		}
		break;
	case 'deleteitem':
	case 'delete_item':
		if(!api_is_allowed_to_edit()){
			api_not_allowed(true);
		}
		if($debug>0) error_log('New LP - delete item action triggered',0);
		if(!$lp_found){ error_log('New LP - No learnpath given for delete item',0); require('lp_list.php'); }
		else{
			$_SESSION['refresh'] = 1;
			if(!empty($_REQUEST['id'])){
				$_SESSION['oLP']->delete_item($_REQUEST['id']);
			}
			require('lp_admin_view.php');
		}
		break;
	case 'edititemprereq':
	case 'edit_item_prereq':
		if(!api_is_allowed_to_edit()){
			api_not_allowed(true);
		}
		if($debug>0) error_log('New LP - edit item prereq action triggered',0);
		if(!$lp_found){ error_log('New LP - No learnpath given for edit item prereq',0); require('lp_list.php'); }
		else{
			if(!empty($_REQUEST['id']) && !empty($_REQUEST['submit_item'])){
				$_SESSION['refresh'] = 1;
				$_SESSION['oLP']->edit_item_prereq($_REQUEST['id'],$_REQUEST['prereq']);
			}
			require('lp_admin_view.php');
		}
		break;
	case 'restart':
		if($debug>0) error_log('New LP - restart action triggered',0);
		if(!$lp_found){ error_log('New LP - No learnpath given for restart',0); require('lp_list.php'); }
		else{
			$_SESSION['oLP']->restart();
			require('lp_view.php');
		}
		break;
	case 'last':
		if($debug>0) error_log('New LP - last action triggered',0);
		if(!$lp_found){ error_log('New LP - No learnpath given for last',0); require('lp_list.php'); }
		else{
			$_SESSION['oLP']->last();
			require('lp_view.php');
		}
		break;
	case 'first':
		if($debug>0) error_log('New LP - first action triggered',0);
		if(!$lp_found){ error_log('New LP - No learnpath given for first',0); require('lp_list.php'); }
		else{
			$_SESSION['oLP']->first();
			require('lp_view.php');
		}
		break;
	case 'next':
		if($debug>0) error_log('New LP - next action triggered',0);
		if(!$lp_found){ error_log('New LP - No learnpath given for next',0); require('lp_list.php'); }
		else{
			$_SESSION['oLP']->next();
			require('lp_view.php');
		}
		break;
	case 'previous':
		if($debug>0) error_log('New LP - previous action triggered',0);
		if(!$lp_found){ error_log('New LP - No learnpath given for previous',0); require('lp_list.php'); }
		else{
			$_SESSION['oLP']->previous();
			require('lp_view.php');
		}
		break;
	case 'content':
		if($debug>0) error_log('New LP - content action triggered',0);
		if($debug>0) error_log('New LP - Item id is '.$_GET['item_id'],0);
		if(!$lp_found){ error_log('New LP - No learnpath given for content',0); require('lp_list.php'); }
		else{
			$_SESSION['oLP']->save_last();
			$_SESSION['oLP']->set_current_item($_GET['item_id']); 
			$_SESSION['oLP']->start_current_item();
			require('lp_content.php'); 
		}
		break;	
	case 'view':
		if($debug > 0)
			error_log('New LP - view action triggered', 0);
		if(!$lp_found)
		{
			error_log('New LP - No learnpath given for view', 0);
			require('lp_list.php');
		}
		else
		{
			if($debug > 0){error_log('New LP - Trying to set current item to ' . $_REQUEST['item_id'], 0);}
			$_SESSION['oLP']->set_current_item($_REQUEST['item_id']);
			require('lp_view.php');
		}
		break;		
	case 'save':
		if($debug>0) error_log('New LP - save action triggered',0);
		if(!$lp_found){ error_log('New LP - No learnpath given for save',0); require('lp_list.php'); }
		else{
			$_SESSION['oLP']->save_item();
			require('lp_save.php');
		}
		break;		
	case 'stats':
		if($debug>0) error_log('New LP - stats action triggered',0);
		if(!$lp_found){ error_log('New LP - No learnpath given for stats',0); require('lp_list.php'); }
		else{
			$_SESSION['oLP']->save_current();
			$_SESSION['oLP']->save_last();
			//declare variables to be used in lp_stats.php
			$lp_id = $_SESSION['oLP']->get_id();
			$list = $_SESSION['oLP']->get_flat_ordered_items_list($lp_id);
			$user_id = api_get_user_id();
			$stats_charset =  api_get_setting('platform_charset');//???平�?��?�置???�????(gb2312)??��?�置�?�?�?�???��??页�?��?????:by xiaoping
			require('lp_stats.php');
		}
		break;
	case 'list':
		if($debug>0) error_log('New LP - list action triggered',0);
		if($lp_found){
			$_SESSION['refresh'] = 1;
			$_SESSION['oLP']->save_last();
		}
		require('lp_list.php');
		break;
	case 'mode':
		//switch between fullscreen and embedded mode
		if($debug>0) error_log('New LP - mode change triggered',0);
		$mode = $_REQUEST['mode'];
		if($mode == 'fullscreen'){
			$_SESSION['oLP']->mode = 'fullscreen';
		}else{
			$_SESSION['oLP']->mode = 'embedded';		
		}
		require('lp_view.php');
		break;
	case 'switch_view_mode':
		if($debug>0) error_log('New LP - switch_view_mode action triggered',0);
		if(!$lp_found){ error_log('New LP - No learnpath given for switch',0); require('lp_list.php'); }
		$_SESSION['refresh'] = 1;
		$_SESSION['oLP']->update_default_view_mode();		
		require('lp_list.php');
		break;
	case 'switch_force_commit':
		if($debug>0) error_log('New LP - switch_force_commit action triggered',0);
		if(!$lp_found){ error_log('New LP - No learnpath given for switch',0); require('lp_list.php'); }
		$_SESSION['refresh'] = 1;
		$_SESSION['oLP']->update_default_scorm_commit();
		require('lp_list.php');
		break;
	case 'switch_reinit':
		if($debug>0) error_log('New LP - switch_reinit action triggered',0);
		if(!$lp_found){ error_log('New LP - No learnpath given for switch',0); require('lp_list.php'); }
		$_SESSION['refresh'] = 1;
		$_SESSION['oLP']->update_reinit();
		require('lp_list.php');
		break;		
	case 'switch_scorm_debug':
		if($debug>0) error_log('New LP - switch_scorm_debug action triggered',0);
		if(!$lp_found){ error_log('New LP - No learnpath given for switch',0); require('lp_list.php'); }
		$_SESSION['refresh'] = 1;
		$_SESSION['oLP']->update_scorm_debug();
		require('lp_list.php');
		break;		
	case 'intro_cmdAdd':
		if($debug>0) error_log('New LP - intro_cmdAdd action triggered',0);
		//add introduction section page
		break;
	case 'js_api_refresh':
		if($debug>0) error_log('New LP - js_api_refresh action triggered',0);
		if(!$lp_found){ error_log('New LP - No learnpath given for js_api_refresh',0); require('lp_message.php'); }
		if(isset($_REQUEST['item_id'])){
			$htmlHeadXtra[] = $_SESSION['oLP']->get_js_info($_REQUEST['item_id']);
		}
		require('lp_message.php');
		break;	
	case 'return_to_course_homepage':
        if(!$lp_found){ error_log('New LP - No learnpath given for stats',0); require('lp_list.php'); }
        else{
            $_SESSION['oLP']->save_current();
            $_SESSION['oLP']->save_last();
            //declare variables to be used in lp_stats.php
            $lp_id = $_SESSION['oLP']->get_id();
            $list = $_SESSION['oLP']->get_flat_ordered_items_list($lp_id);
            $user_id = api_get_user_id();
            $stats_charset = api_get_setting('platform_charset');
            header('location: ../course_home/course_home.php?'.api_get_cidreq());
        }
        break;        
	default:
		if($debug>0) error_log('New LP - default action triggered',0);
		//$_SESSION['refresh'] = 1;
		require('lp_list.php');
		break;
}
if(!empty($_SESSION['oLP'])){
	$_SESSION['lpobject'] = serialize($_SESSION['oLP']);
	if($debug>0) error_log('New LP - lpobject is serialized in session',0);
}
?>
