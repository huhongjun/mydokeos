<?php // $Id: usermanager.lib.php 15169 2008-04-29 06:27:22Z yannoo $
/*
==============================================================================
	Dokeos - elearning and course management software

	Copyright (c) 2004-2008 Dokeos SPRL
	Copyright (c) 2003 Ghent University (UGent)
	Copyright (c) 2001 Universite catholique de Louvain (UCL)
	Copyright (c) various contributors
	Copyright (c) Bart Mollet, Hogeschool Gent

	For a full list of contributors, see "credits.txt".
	The full license can be read in "license.txt".

	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	See the GNU General Public License for more details.

	Contact: Dokeos, rue du Corbeau, 108, B-1030 Brussels, Belgium, info@dokeos.com
==============================================================================
*/
/**
==============================================================================
*	This library provides functions for user management.
*	Include/require it in your code to use its functionality.
*
*	@package dokeos.library
==============================================================================
*/
// define constants for user extra field types
define('USER_FIELD_TYPE_TEXT',1);
define('USER_FIELD_TYPE_TEXTAREA',2);
define('USER_FIELD_TYPE_RADIO',3);
define('USER_FIELD_TYPE_SELECT',4);
define('USER_FIELD_TYPE_SELECT_MULTIPLE',5);
define('USER_FIELD_TYPE_DATE',6);
define('USER_FIELD_TYPE_DATETIME',7);

class UserManager
{
	/**
	  * Creates a new user for the platform
	  * @author Hugues Peeters <peeters@ipm.ucl.ac.be>,
	  * 		Roan Embrechts <roan_embrechts@yahoo.com>
	  *
	  * @param	string	Firstname
	  * @param	string	Lastname
	  * @param	int   	Status (1 for course tutor, 5 for student, 6 for anonymous)
	  * @param	string	e-mail address
	  * @param	string	Login
	  * @param	string	Password
	  * @param	string	Any official code (optional)
	  * @param	int	  	User language	(optional)
	  * @param	string	Phone number	(optional)
	  * @param	string	Picture URI		(optional)
	  * @param	string	Authentication source	(optional, defaults to 'platform', dependind on constant)
	  * @param	string	Account expiration date (optional, defaults to '0000-00-00 00:00:00')
	  * @param	int		Whether the account is enabled or disabled by default
 	  * @param	int		The user ID of the person who registered this user (optional, defaults to null)
 	  * @param	int		The department of HR in which the user is registered (optional, defaults to 0)
	  * @return int     new user id - if the new user creation succeeds
	  *         boolean false otherwise
	  *
	  * @desc The function tries to retrieve $_user['user_id'] from the global space.
	  * if it exists, $_user['user_id'] is the creator id       If       a problem arises,
	  * it stores the error message in global $api_failureList
	  */
	function create_user($firstName, $lastName, $status, $email, $loginName, $password, $official_code = '', $language='', $phone = '', $picture_uri = '', $auth_source = PLATFORM_AUTH_SOURCE, $expiration_date = '0000-00-00 00:00:00', $active = 1, $hr_dept_id=0, $extra=null)
	{
		global $_user, $userPasswordCrypted;
		
		// database table definition
		$table_user = Database::get_main_table(TABLE_MAIN_USER);
		
		// default langauge
		if ($language=='')
		{
			$language = api_get_setting('platformLanguage');
		}
		
		if ($_user['user_id'])
		{
			$creator_id = $_user['user_id'];
		}
		else
		{
			$creator_id = '';
		}
		// First check wether the login already exists
		if (! UserManager::is_username_available($loginName))
			return api_set_failure('login-pass already taken');
		//$password = "PLACEHOLDER";
		$password = ($userPasswordCrypted ? md5($password) : $password);		
		
		$sql = "INSERT INTO $table_user
					                SET lastname = '".Database::escape_string($lastName)."',
					                firstname = '".Database::escape_string($firstName)."',
					                username = '".Database::escape_string($loginName)."',
					                status = '".Database::escape_string($status)."',
					                password = '".Database::escape_string($password)."',
					                email = '".Database::escape_string($email)."',
					                official_code	= '".Database::escape_string($official_code)."',
					                picture_uri 	= '".Database::escape_string($picture_uri)."',
					                creator_id  	= '".Database::escape_string($creator_id)."',
					                auth_source = '".Database::escape_string($auth_source)."',
				                    phone = '".Database::escape_string($phone)."',
				                    language = '".Database::escape_string($language)."', 
				                    registration_date = now(),
				                    expiration_date = '".Database::escape_string($expiration_date)."',
									hr_dept_id = '".Database::escape_string($hr_dept_id)."',
									active = '".Database::escape_string($active)."'";
		$result = api_sql_query($sql);
		if ($result)
		{
			//echo "id returned";
			$return=Database::get_last_insert_id();
		}
		else
		{
			//echo "false - failed" ;
			$return=false;
		}
		if(is_array($extra) AND count($extra)>0)
		{
			$res = true;
			foreach($extra as $fname => $fvalue)
			{
				$res = $res && UserManager::update_extra_field($return,$fname,$fvalue);
			}
		}
		return $return;
	}

	/**
	 * Can user be deleted?
	 * This functions checks if there's a course in which the given user is the
	 * only course administrator. If that is the case, the user can't be
	 * deleted because the course would remain without a course admin.
	 * @param int $user_id The user id
	 * @return boolean true if user can be deleted
	 */
	function can_delete_user($user_id)
	{
		$table_course_user = Database :: get_main_table(TABLE_MAIN_COURSE_USER);
		$sql = "SELECT * FROM $table_course_user WHERE status = '1' AND user_id = '".$user_id."'";
		$res = api_sql_query($sql,__FILE__,__LINE__);
		while ($course = Database::fetch_object($res))
		{
			$sql = "SELECT user_id FROM $table_course_user WHERE status='1' AND course_code ='".$course->course_code."'";
			$res2 = api_sql_query($sql,__FILE__,__LINE__);
			if (Database::num_rows($res2) == 1)
			{
				return false;
			}
		}
		return true;
	}

	/**
	 * Delete a user from the platform
	 * @param int $user_id The user id
	 * @return boolean true if user is succesfully deleted, false otherwise
	 */
	function delete_user($user_id)
	{
		if (!UserManager :: can_delete_user($user_id))
		{
			return false;
		}
		$table_user = Database :: get_main_table(TABLE_MAIN_USER);
		$table_course_user = Database :: get_main_table(TABLE_MAIN_COURSE_USER);
		$table_class_user = Database :: get_main_table(TABLE_MAIN_CLASS_USER);
		$table_course = Database :: get_main_table(TABLE_MAIN_COURSE);
		$table_admin = Database :: get_main_table(TABLE_MAIN_ADMIN);
		$table_session_user = Database :: get_main_table(TABLE_MAIN_SESSION_USER);
		$table_session_course_user = Database :: get_main_table(TABLE_MAIN_SESSION_COURSE_USER);

		// Unsubscribe the user from all groups in all his courses
		$sql = "SELECT * FROM $table_course c, $table_course_user cu WHERE cu.user_id = '".$user_id."' AND c.code = cu.course_code";
		$res = api_sql_query($sql,__FILE__,__LINE__);
		while ($course = Database::fetch_object($res))
		{
			$table_group = Database :: get_course_table(TABLE_GROUP_USER, $course->db_name);
			$sql = "DELETE FROM $table_group WHERE user_id = '".$user_id."'";
			api_sql_query($sql,__FILE__,__LINE__);
		}

		// Unsubscribe user from all classes
		$sql = "DELETE FROM $table_class_user WHERE user_id = '".$user_id."'";
		api_sql_query($sql,__FILE__,__LINE__);

		// Unsubscribe user from all courses
		$sql = "DELETE FROM $table_course_user WHERE user_id = '".$user_id."'";
		api_sql_query($sql,__FILE__,__LINE__);
		
		// Unsubscribe user from all courses in sessions
		$sql = "DELETE FROM $table_session_course_user WHERE id_user = '".$user_id."'";
		api_sql_query($sql,__FILE__,__LINE__);
		
		// Unsubscribe user from all sessions
		$sql = "DELETE FROM $table_session_user WHERE id_user = '".$user_id."'";
		api_sql_query($sql,__FILE__,__LINE__);

		// Delete user picture
		$user_info = api_get_user_info($user_id);
		if(strlen($user_info['picture_uri']) > 0)
		{
			$img_path = api_get_path(SYS_CODE_PATH).'upload/users/'.$user_info['picture_uri'];
			unlink($img_path);
		}

		// Delete the personal course categories
		$course_cat_table = Database::get_user_personal_table(TABLE_USER_COURSE_CATEGORY);
		$sql = "DELETE FROM $course_cat_table WHERE user_id = '".$user_id."'";
		api_sql_query($sql,__FILE__,__LINE__);

		// Delete user from database
		$sql = "DELETE FROM $table_user WHERE user_id = '".$user_id."'";
		api_sql_query($sql,__FILE__,__LINE__);

		// Delete user from the admin table
		$sql = "DELETE FROM $table_admin WHERE user_id = '".$user_id."'";
		api_sql_query($sql,__FILE__,__LINE__);

		// Delete the personal agenda-items from this user
		$agenda_table = Database :: get_user_personal_table(TABLE_PERSONAL_AGENDA);
		$sql = "DELETE FROM $agenda_table WHERE user = '".$user_id."'";
		api_sql_query($sql,__FILE__,__LINE__);

		$gradebook_results_table = Database :: get_main_table(TABLE_MAIN_GRADEBOOK_RESULT);
		$sql = 'DELETE FROM '.$gradebook_results_table.' WHERE user_id = '.$user_id;
		api_sql_query($sql, __FILE__, __LINE__);

		$user = Database::fetch_array($res);
		$t_ufv = Database::get_main_table(TABLE_MAIN_USER_FIELD_VALUES);
		$sqlv = "DELETE FROM $t_ufv WHERE user_id = $user_id";
		$resv = api_sql_query($sqlv,__FILE__,__LINE__);
		
		return true;
	}

	/**
	 * Update user information with new openid
	 * @param int $user_id
	 * @param string $openid
	 * @return boolean true if the user information was updated
	 */
	function update_openid($user_id, $openid)
	{
		$table_user = Database :: get_main_table(TABLE_MAIN_USER);
		$sql = "UPDATE $table_user SET
				openid='".Database::escape_string($openid)."'";
		$sql .=	" WHERE user_id='$user_id'";
		return api_sql_query($sql,__FILE__,__LINE__);
	}
	/**
	 * Update user information
	 * @param int $user_id
	 * @param string $firstname
	 * @param string $lastname
	 * @param string $username
	 * @param string $password
	 * @param string $auth_source
	 * @param string $email
	 * @param int $status
	 * @param string $official_code
	 * @param string $phone
	 * @param string $picture_uri
	 * @param int The user ID of the person who registered this user (optional, defaults to null)
	 * @param int The department of HR in which the user is registered (optional, defaults to 0)
	 * @param	array	A series of additional fields to add to this user as extra fields (optional, defaults to null)
	 * @return boolean true if the user information was updated
	 */
	function update_user($user_id, $firstname, $lastname, $username, $password = null, $auth_source = null, $email, $status, $official_code, $phone, $picture_uri, $expiration_date, $active, $creator_id= null, $hr_dept_id=0, $extra=null)
	{
		global $userPasswordCrypted;
		$table_user = Database :: get_main_table(TABLE_MAIN_USER);
		$sql = "UPDATE $table_user SET
				lastname='".Database::escape_string($lastname)."',
				firstname='".Database::escape_string($firstname)."',
				username='".Database::escape_string($username)."',";
		if(!is_null($password))
		{
			$password = $userPasswordCrypted ? md5($password) : $password;
			$sql .= " password='".Database::escape_string($password)."',";
		}
		if(!is_null($auth_source))
		{
			$sql .=	" auth_source='".Database::escape_string($auth_source)."',";
		}
		$sql .=	"
				email='".Database::escape_string($email)."',
				status='".Database::escape_string($status)."',
				official_code='".Database::escape_string($official_code)."',
				phone='".Database::escape_string($phone)."',
				picture_uri='".Database::escape_string($picture_uri)."',
				expiration_date='".Database::escape_string($expiration_date)."',
				active='".Database::escape_string($active)."',
				hr_dept_id=".intval($hr_dept_id);
		if(!is_null($creator_id))
		{
			$sql .= ", creator_id='".Database::escape_string($creator_id)."'";
		}
		$sql .=	" WHERE user_id='$user_id'";
		$return = api_sql_query($sql,__FILE__,__LINE__);
		if(is_array($extra) and count($extra)>0)
		{
			$res = true;
			foreach($extra as $fname => $fvalue)
			{
				$res = $res && UserManager::update_extra_field($user_id,$fname,$fvalue);
			}
		}

		return $return;
	}

	/**
	 * Check if a username is available
	 * @param string the wanted username
	 * @return boolean true if the wanted username is available
	 */
	function is_username_available($username)
	{
		$table_user = Database :: get_main_table(TABLE_MAIN_USER);
		$sql = "SELECT username FROM $table_user WHERE username = '".addslashes($username)."'";
		$res = api_sql_query($sql,__FILE__,__LINE__);
		return Database::num_rows($res) == 0;
	}

	/**
	* @param array $conditions a list of condition (exemple : status=>STUDENT)
	* @param array $order_by a list of fields on which sort
	* @return array An array with all users of the platform.
	* @todo optional course code parameter, optional sorting parameters...
	*/
	function get_user_list($conditions = array(), $order_by = array())
	{
		$user_table = Database :: get_main_table(TABLE_MAIN_USER);
		$return_array = array();
		$sql_query = "SELECT * FROM $user_table";
		if(count($conditions)>0)
		{
			$sql_query .= ' WHERE ';
			foreach($conditions as $field=>$value)
			{
				$sql_query .= $field.' = '.$value;
			}
		}
		if(count($order_by)>0)
		{
			$sql_query .= ' ORDER BY '.implode(',',$order_by);
		}
		$sql_result = api_sql_query($sql_query,__FILE__,__LINE__);
		while ($result = Database::fetch_array($sql_result))
		{
			$return_array[] = $result;
		}
		return $return_array;
	}
	
	
	
	/**
	 * Get user information
	 * @param 	string 	The username
	 * @return array All user information as an associative array
	 */
	function get_user_info($username)
	{
		$user_table = Database :: get_main_table(TABLE_MAIN_USER);
		$sql = "SELECT * FROM $user_table WHERE username='".$username."'";
		$res = api_sql_query($sql,__FILE__,__LINE__);
		if(Database::num_rows($res)>0)
		{
			$user = Database::fetch_array($res);
		}
		else
		{
			$user = false;
		}
		return $user;
	}
	
	/**
	 * Get user information
	 * @param	string	The id
	 * @param	boolean	Whether to return the user's extra fields (defaults to false)
	 * @return	array 	All user information as an associative array
	 */
	function get_user_info_by_id($user_id,$user_fields=false)
	{
		$user_id = intval($user_id);
		$user_table = Database :: get_main_table(TABLE_MAIN_USER);
		$sql = "SELECT * FROM $user_table WHERE user_id=".$user_id;
		$res = api_sql_query($sql,__FILE__,__LINE__);
		if(Database::num_rows($res)>0)
		{
			$user = Database::fetch_array($res);
			$t_uf = Database::get_main_table(TABLE_MAIN_USER_FIELD);
			$t_ufv = Database::get_main_table(TABLE_MAIN_USER_FIELD_VALUES);
			$sqlf = "SELECT * FROM $t_uf ORDER BY field_order";
			$resf = api_sql_query($sqlf,__FILE__,__LINE__);
			if(Database::num_rows($resf)>0)
			{
				while($rowf = Database::fetch_array($resf))
				{
					$sqlv = "SELECT * FROM $t_ufv WHERE field_id = ".$rowf['id']." AND user_id = ".$user['user_id']." ORDER BY id DESC";
					$resv = api_sql_query($sqlv,__FILE__,__LINE__);
					if(Database::num_rows($resv)>0)
					{
						//There should be only one value for a field and a user
						$rowv = Database::fetch_array($resv);
						$user['extra'][$rowf['field_variable']] = $rowv['field_value'];
					}
					else
					{
						$user['extra'][$rowf['field_variable']] = '';
					}
				}
			}
			
		}
		else
		{
			$user = false;
		}
		return $user;
	}

	//for survey
	function get_teacher_list($course_id, $sel_teacher='')
	{
		$user_course_table = Database :: get_main_table(TABLE_MAIN_COURSE_USER);
		$user_table = Database :: get_main_table(TABLE_MAIN_USER);
		$sql_query = "SELECT * FROM $user_table a, $user_course_table b where a.user_id=b.user_id AND b.status=1 AND b.course_code='$course_id'";
		$sql_result = api_sql_query($sql_query,__FILE__,__LINE__);
		echo "<select name=\"author\">";
		while ($result = Database::fetch_array($sql_result))
		{
			if($sel_teacher==$result['user_id']) $selected ="selected";
			echo "\n<option value=\"".$result['user_id']."\" $selected>".$result['firstname']."</option>";
		}
		echo "</select>";
	}
	
	/**
	 * Get user picture URL or path from user ID (returns an array).
	 * The return format is a complete path, enabling recovery of the directory
	 * with dirname() or the file with basename(). This also works for the
	 * functions dealing with the user's productions, as they are located in
	 * the same directory.
	 * @param	integer	User ID
	 * @param	string	Type of path to return (can be 'none','system','rel','web')
	 * @param	bool	Whether we want to have the directory name returned 'as if' there was a file or not (in the case we want to know which directory to create - otherwise no file means no split subdir)
	 * @param	bool	If we want that the function returns the /main/img/unknown.jpg image set it at true 
	 * @return	array 	Array of 2 elements: 'dir' and 'file' which contain the dir and file as the name implies if image does not exist it will return the unknow image if anonymous parameter is true if not it returns an empty array
	 */
	function get_user_picture_path_by_id($id,$type='none',$preview=false,$anonymous=false)
	{
		if(empty($id) or empty($type))
		{
			if ($anonymous) 
			{
				$dir='';
				switch($type)
				{
					case 'system': //return the complete path to the file, from root
						$dir = api_get_path(SYS_CODE_PATH).'img/';
						break;
					case 'rel': //return the relative path to the file, from the Dokeos base dir
						$dir = api_get_path(REL_CODE_PATH).'img/';
						break;
					case 'web': //return the complete web URL to the file 
						$dir = api_get_path(WEB_CODE_PATH).'img/';
						break;
					case 'none': //return only the picture_uri (as is, without subdir)
					default:
						break;
				}
				$file_anonymous='unknown.jpg';
				return array('dir'=>$dir,'file'=>$file_anonymous);
			}
			else 
			{
				return array('dir'=>'','file'=>'');
			}			
		}
		
		$user_id = intval($id);
		$user_table = Database :: get_main_table(TABLE_MAIN_USER);
		$sql = "SELECT picture_uri FROM $user_table WHERE user_id=".$user_id;
		$res = api_sql_query($sql,__FILE__,__LINE__);
		
		$user=array();
		
		if(Database::num_rows($res)>0)
		{
			$user = Database::fetch_array($res);			
		}
		else
		{									
			if ($anonymous) 
			{
				$dir='';
				switch($type)
				{
					case 'system': //return the complete path to the file, from root
						$dir = api_get_path(SYS_CODE_PATH).'img/';
						break;
					case 'rel': //return the relative path to the file, from the Dokeos base dir
						$dir = api_get_path(REL_CODE_PATH).'img/';
						break;
					case 'web': //return the complete web URL to the file 
						$dir = api_get_path(WEB_CODE_PATH).'img/';
						break;
					case 'none': //return only the picture_uri (as is, without subdir)
					default:
						break;
				}
				$file_anonymous='unknown.jpg';
				return array('dir'=>$dir,'file'=>$file_anonymous);
			}
			else 
			{
				return array('dir'=>'','file'=>'');	
			}		
		}
				
		$path = trim($user['picture_uri']);
		
		if (empty($path)) 
		{			
			if ($anonymous) 
			{			
				switch($type)
				{
					case 'system': //return the complete path to the file, from root
						$dir = api_get_path(SYS_CODE_PATH).'img/';
						break;
					case 'rel': //return the relative path to the file, from the Dokeos base dir
						$dir = api_get_path(REL_CODE_PATH).'img/';
						break;
					case 'web': //return the complete web URL to the file 
						$dir = api_get_path(WEB_CODE_PATH).'img/';
						break;
					case 'none': //return only the picture_uri (as is, without subdir)
					default:
						break;
				}
				$file_anonymous='unknown.jpg';
				return array('dir'=>$dir,'file'=>$file_anonymous);			
			}
		}		
		
		$dir = '';
		$first = '';
		
		if(api_get_setting('split_users_upload_directory') === 'true')
		{
			if(!empty($path))
			{
				$first = substr($path,0,1).'/';
			}
			elseif($preview==true)
			{
				$first = substr(''.$user_id,0,1).'/';
			}
		}
		else
		{
			$first = $user_id.'/';
		}
				
		switch($type)
		{
			case 'system': //return the complete path to the file, from root
				$dir = api_get_path(SYS_CODE_PATH).'upload/users/'.$first;
				break;
			case 'rel': //return the relative path to the file, from the Dokeos base dir
				$dir = api_get_path(REL_CODE_PATH).'upload/users/'.$first;
				break;
			case 'web': //return the complete web URL to the file 
				$dir = api_get_path(WEB_CODE_PATH).'upload/users/'.$first;
				break;
			case 'none': //return only the picture_uri (as is, without subdir)
			default:
				break;
		}
		return array('dir'=>$dir,'file'=>$path);
	}

/*
-----------------------------------------------------------
	PRODUCTIONS FUNCTIONS
-----------------------------------------------------------
*/

	/**
	 * Returns an XHTML formatted list of productions for a user, or FALSE if he
	 * doesn't have any.
	 *
	 * If there has been a request to remove a production, the function will return
	 * without building the list unless forced to do so by the optional second
	 * parameter. This increases performance by avoiding to read through the
	 * productions on the filesystem before the removal request has been carried
	 * out because they'll have to be re-read afterwards anyway.
	 *
	 * @param	$user_id	User id
	 * @param	$force	Optional parameter to force building after a removal request
	 * @return	A string containing the XHTML code to dipslay the production list, or FALSE
	 */
	function build_production_list($user_id, $force = false, $showdelete=false)
	{
		if (!$force && !empty($_POST['remove_production']))
			return true; // postpone reading from the filesystem
	
		$productions = UserManager::get_user_productions($user_id);
	
		if (empty($productions))
			return false;
	
		$production_path = UserManager::get_user_picture_path_by_id($user_id,'web',true);
		$production_dir = $production_path['dir'].$user_id.'/';
		$del_image = api_get_path(WEB_CODE_PATH).'img/delete.gif';
		$del_text = get_lang('Delete');
	
		$production_list = '<ul id="productions">';
	
		foreach ($productions as $file)
		{
			$production_list .= '<li><a href="'.$production_dir.urlencode($file).'" target="_blank">'.htmlspecialchars($file).'</a>';
			if ($showdelete)
			{
				$production_list .= '<input type="image" name="remove_production['.urlencode($file).']" src="'.$del_image.'" alt="'.$del_text.'" title="'.$del_text.' '.htmlspecialchars($file).'" onclick="return confirmation(\''.htmlspecialchars($file).'\');" /></li>';
			}
		}
	
		$production_list .= '</ul>';
	
		return $production_list;
	}
	
	/**
	 * Returns an array with the user's productions.
	 *
	 * @param	$user_id	User id
	 * @return	An array containing the user's productions
	 */
	function get_user_productions($user_id)
	{
		$production_path = UserManager::get_user_picture_path_by_id($user_id,'system',true);
		$production_repository = $production_path['dir'].$user_id.'/';
		$productions = array();
	
		if (is_dir($production_repository))
		{
			$handle = opendir($production_repository);
	
			while ($file = readdir($handle))
			{
				if ($file == '.' || $file == '..' || $file == '.htaccess')
					continue; // skip current/parent directory and .htaccess
	
				$productions[] = $file;
			}
		}
	
		return $productions; // can be an empty array
	}
	
	/**
	 * Remove a user production.
	 *
	 * @param	$user_id		User id
	 * @param	$production	The production to remove
	 */
	function remove_user_production($user_id, $production)
	{
		$production_path = UserManager::get_user_picture_path_by_id($user_id,'system',true);
		unlink($production_path['dir'].$user_id.'/'.$production);
	}
	/**
	 * Update an extra field
	 * @param	integer	Field ID
	 * @param	array	Database columns and their new value
	 * @return	boolean	true if field updated, false otherwise
	 */
	function update_extra_field($fid,$columns)
	{
		//TODO check that values added are values proposed for enumerated field types
		$t_uf = Database::get_main_table(TABLE_MAIN_USER_FIELD);
		$fid = Database::escape_string($fid);
		$sqluf = "UPDATE $t_uf SET ";
		$known_fields = array('id','field_variable','field_type','field_display_text','field_default_value','field_order','field_visible','field_changeable');
		$safecolumns = array(); 
		foreach($columns as $index => $newval)
		{
			if(in_array($index,$known_fields))
			{			
				$safecolumns[$index] = Database::escape_string($newval);
				$sqluf .= $index." = '".$safecolumns[$index]."', ";
			}
		}
		$time = time();
		$sqluf .= " tms = FROM_UNIXTIME($time) WHERE id='$fid'";
		$resuf = api_sql_query($sqluf,__FILE__,__LINE__);
		return $resuf;
	}
	/**
	 * Update an extra field value for a given user
	 * @param	integer	User ID
	 * @param	string	Field variable name
	 * @param	string	Field value
	 * @return	boolean	true if field updated, false otherwise
	 */
	function update_extra_field_value($user_id,$fname,$fvalue='')
	{
		//TODO check that values added are values proposed for enumerated field types
		$t_uf = Database::get_main_table(TABLE_MAIN_USER_FIELD);
		$t_ufo = Database::get_main_table(TABLE_MAIN_USER_FIELD_OPTIONS);
		$t_ufv = Database::get_main_table(TABLE_MAIN_USER_FIELD_VALUES);
		$fname = Database::escape_string($fname);
		$fvalues = '';
		if(is_array($fvalue))
		{
			foreach($fvalue as $val)
			{
				$fvalues .= Database::escape_string($val).';';
			}
			if(!empty($fvalues))
			{
				$fvalues = substr($fvalues,0,-1);
			}
		}
		else
		{
			$fvalues = Database::escape_string($fvalue);
		}
		$sqluf = "SELECT * FROM $t_uf WHERE field_variable='$fname'";
		$resuf = api_sql_query($sqluf,__FILE__,__LINE__);
		if(Database::num_rows($resuf)==1)
		{ //ok, the field exists
			//	Check if enumerated field, if the option is available 
			$rowuf = Database::fetch_array($resuf);
			switch($rowuf['field_type'])
			{
				case 3:
				case 4:
				case 5:
					$sqluo = "SELECT * FROM $t_ufo WHERE field_id = ".$rowuf['id'];
					$resuo = api_sql_query($sqluo,__FILE__,__LINE__);
					$values = split(';',$fvalues);
					if(Database::num_rows($resuo)>0)
					{
						$check = false;
						while($rowuo = Database::fetch_array($resuo))
						{
							if(in_array($rowuo['option_value'],$values))
							{
								$check = true;
								break;
							}
						}
						if($check == false)
						{
							return false; //option value not found
						}
					}
					else
					{
						return false; //enumerated type but no option found
					}
					break;
				case 1:
				case 2:
				default:
					break;
			}
			$tms = time();
			$sqlufv = "SELECT * FROM $t_ufv WHERE user_id = $user_id AND field_id = ".$rowuf['id']." ORDER BY id";
			$resufv = api_sql_query($sqlufv,__FILE__,__LINE__);
			$n = Database::num_rows($resufv);
			if($n>1)
			{
				//problem, we already have to values for this field and user combination - keep last one
				while($rowufv = Database::fetch_array($resufv))
				{
					if($n > 1)
					{
						$sqld = "DELETE FROM $t_ufv WHERE id = ".$rowufv['id'];
						$resd = api_sql_query($sqld,__FILE__,__LINE__);
						$n--;
					}
					$rowufv = Database::fetch_array($resufv);
					if($rowufv['field_value'] != $fvalues)
					{ 
						$sqlu = "UPDATE $t_ufv SET field_value = '$fvalues', tms = FROM_UNIXTIME($tms) WHERE id = ".$rowufv['id'];
						$resu = api_sql_query($sqlu,__FILE__,__LINE__);
						return($resu?true:false);					
					}
					return true;
				}
			}
			elseif($n==1)
			{
				//we need to update the current record
				$rowufv = Database::fetch_array($resufv);
				if($rowufv['field_value'] != $fvalues)
				{
					$sqlu = "UPDATE $t_ufv SET field_value = '$fvalues', tms = FROM_UNIXTIME($tms) WHERE id = ".$rowufv['id'];
					//error_log('UM::update_extra_field_value: '.$sqlu);
					$resu = api_sql_query($sqlu,__FILE__,__LINE__);
					return($resu?true:false);
				}
				return true;
			}
			else
			{
				$sqli = "INSERT INTO $t_ufv (user_id,field_id,field_value,tms) " .
					"VALUES ($user_id,".$rowuf['id'].",'$fvalues',FROM_UNIXTIME($tms))";
				//error_log('UM::update_extra_field_value: '.$sqli);
				$resi = api_sql_query($sqli,__FILE__,__LINE__);
				return($resi?true:false);
			}
		}
		else
		{
			return false; //field not found
		}
	}
	/**
	 * Get an array of extra fieds with field details (type, default value and options)
	 * @param	integer	Offset (from which row)
	 * @param	integer	Number of items
	 * @param	integer	Column on which sorting is made
	 * @param	string	Sorting direction
	 * @param	boolean	Optional. Whether we get all the fields or just the visible ones
	 * @return	array	Extra fields details (e.g. $list[2]['type'], $list[4]['options'][2]['title']
	 */
	function get_extra_fields($from=0, $number_of_items=0, $column=5, $direction='ASC', $all_visibility=true)
	{
		$fields = array();
		$t_uf = Database :: get_main_table(TABLE_MAIN_USER_FIELD);
		$t_ufo = Database :: get_main_table(TABLE_MAIN_USER_FIELD_OPTIONS);
		$columns = array('id','field_variable','field_type','field_display_text','field_default_value','field_order','tms');
		$sort_direction = '';
		if(in_array(strtoupper($direction),array('ASC','DESC')))
		{
			$sort_direction = strtoupper($direction);
		}
		$sqlf = "SELECT * FROM $t_uf ";
		if($all_visibility==false)
		{
			$sqlf .= " WHERE field_visible = 1 ";
		}
		$sqlf .= " ORDER BY ".$columns[$column]." $sort_direction " ;
		if($number_of_items != 0)
		{
			$sqlf .= " LIMIT ".Database::escape_string($from).','.Database::escape_string($number_of_items);
		}
		$resf = api_sql_query($sqlf,__FILE__,__LINE__);
		if(Database::num_rows($resf)>0)
		{
			while($rowf = Database::fetch_array($resf))
			{
				$fields[$rowf['id']] = array(
					0=>$rowf['id'],
					1=>$rowf['field_variable'],
					2=>$rowf['field_type'],
					//3=>(empty($rowf['field_display_text'])?'':get_lang($rowf['field_display_text'],'')),
					//temporarily removed auto-translation. Need update to get_lang() to know if translation exists (todo)
					3=>(empty($rowf['field_display_text'])?'':$rowf['field_display_text']),
					4=>$rowf['field_default_value'],
					5=>$rowf['field_order'],
					6=>$rowf['field_visible'],
					7=>$rowf['field_changeable'],
					8=>array()
				);
				$sqlo = "SELECT * FROM $t_ufo WHERE field_id = ".$rowf['id'];
				$reso = api_sql_query($sqlo,__FILE__,__LINE__);
				if(Database::num_rows($reso)>0)
				{
					while($rowo = Database::fetch_array($reso))
					{
						$fields[$rowf['id']][8][$rowo['id']] = array(
							0=>$rowo['id'],
							1=>$rowo['option_value'],
							//2=>(empty($rowo['option_display_text'])?'':get_lang($rowo['option_display_text'],'')),
							2=>(empty($rowo['option_display_text'])?'':$rowo['option_display_text']),
							3=>$rowo['option_order']
						);
					}	
				}
			}
		}
		return $fields;
	}
	/**
	 * Get the number of extra fields currently recorded
	 * @param	boolean	Optional switch. true (default) returns all fields, false returns only visible fields
	 * @return	integer	Number of fields
	 */
	function get_number_of_extra_fields($all_visibility=true)
	{
		$t_uf = Database :: get_main_table(TABLE_MAIN_USER_FIELD);
		$sqlf = "SELECT * FROM $t_uf ";
		if($all_visibility == false)
		{
			$sqlf .= " WHERE field_visible = 1 ";
		}
		$sqlf .= " ORDER BY field_order";
		$resf = api_sql_query($sqlf,__FILE__,__LINE__);
		return Database::num_rows($resf);
	}
	/**
	  * Creates a new extra field
	  * @param	string	Field's internal variable name
	  * @param	int		Field's type
	  * @param	string	Field's language var name
	  * @param	string	Field's default value
	  * @param	string	Optional comma-separated list of options to provide for select and radio
	  * @return int     new user id - if the new user creation succeeds, false otherwise
	  */
	function create_extra_field($fieldvarname, $fieldtype, $fieldtitle, $fielddefault, $fieldoptions='')
	{		
		// database table definition
		$table_field 		= Database::get_main_table(TABLE_MAIN_USER_FIELD);
		$table_field_options= Database::get_main_table(TABLE_MAIN_USER_FIELD_OPTIONS);
		
		// First check wether the login already exists
		if (! UserManager::is_extra_field_available($fieldvarname))
			return api_set_failure('login-pass already taken');
		$sql = "SELECT MAX(field_order) FROM $table_field";
		$res = api_sql_query($sql,__FILE__,__LINE__);
		$order = 0;
		if(Database::num_rows($res)>0)
		{
			$row = Database::fetch_array($res);
			$order = $row[0]+1;
		}
		$time = time();
		$sql = "INSERT INTO $table_field
					                SET field_type = '".Database::escape_string($fieldtype)."',
					                field_variable = '".Database::escape_string($fieldvarname)."',
					                field_display_text = '".Database::escape_string($fieldtitle)."',
					                field_default_value = '".Database::escape_string($fielddefault)."',
					                field_order = '$order',
					                tms = FROM_UNIXTIME($time)";
		$result = api_sql_query($sql);
		if ($result)
		{
			//echo "id returned";
			$return=Database::get_last_insert_id();
		}
		else
		{
			//echo "false - failed" ;
			return false;
		}
		if(!empty($fieldoptions) && in_array($fieldtype,array(USER_FIELD_TYPE_RADIO,USER_FIELD_TYPE_SELECT,USER_FIELD_TYPE_SELECT_MULTIPLE)))
		{
			$list = split(';',$fieldoptions);
			foreach($list as $option)
			{
				$option = Database::escape_string($option);
				$sql = "SELECT * FROM $table_field_options WHERE field_id = $return AND option_value = '".$option."'";
				$res = api_sql_query($sql,__FILE__,__LINE__);
				if(Database::num_rows($res)>0)
				{
					//the option already exists, do nothing
				}
				else
				{
					$sql = "SELECT MAX(option_order) FROM $table_field_options WHERE field_id = $return";
					$res = api_sql_query($sql,__FILE__,__LINE__);
					$max = 1;
					if(Database::num_rows($res)>0)
					{
						$row = Database::fetch_array($res);
						$max = $row[0]+1;
					}
					$time = time();
					$sql = "INSERT INTO $table_field_options (field_id,option_value,option_display_text,option_order,tms) VALUES ($return,'$option','$option',$max,FROM_UNIXTIME($time))";
					$res = api_sql_query($sql,__FILE__,__LINE__);
					if($res === false)
					{
						$return = false;
					}
				}
			}
		}
		return $return;
	}
	/**
	 * Check if a field is available
	 * @param	string	the wanted username
	 * @return	boolean	true if the wanted username is available
	 */
	function is_extra_field_available($fieldname)
	{
		$t_uf = Database :: get_main_table(TABLE_MAIN_USER_FIELD);
		$sql = "SELECT * FROM $t_uf WHERE field_variable = '".Database::escape_string($fieldname)."'";
		$res = api_sql_query($sql,__FILE__,__LINE__);
		return Database::num_rows($res) <= 0;
	}
	/**
	 * Gets user extra fields data
	 * @param	integer	User ID
	 * @param	boolean	Whether to prefix the fields indexes with "extra_" (might be used by formvalidator)
	 * @param	boolean	Whether to return invisible fields as well
	 * @param	boolean	Whether to split multiple-selection fields or not
	 * @return	array	Array of fields => value for the given user
	 */
	function get_extra_user_data($user_id, $prefix=false, $all_visibility = true, $splitmultiple=false)
	{
		$extra_data = array();
		$t_uf = Database::get_main_table(TABLE_MAIN_USER_FIELD);
		$t_ufv = Database::get_main_table(TABLE_MAIN_USER_FIELD_VALUES);
		$user_id = Database::escape_string($user_id);
		$sql = "SELECT f.id as id, f.field_variable as fvar, f.field_type as type FROM $t_uf f ";
		if($all_visibility == false)
		{
			$sql .= " WHERE f.field_visible = 1 ";
		}
		$sql .= " ORDER BY f.field_order";
		$res = api_sql_query($sql,__FILE__,__LINE__);
		if(Database::num_rows($res)>0)
		{
			while($row = Database::fetch_array($res))
			{
				$sqlu = "SELECT field_value as fval " .
						" FROM $t_ufv " .
						" WHERE field_id=".$row['id']."" .
						" AND user_id=".$user_id;
				$resu = api_sql_query($sqlu,__FILE__,__LINE__);
				$fval = '';
				if(Database::num_rows($resu)>0)
				{
					$rowu = Database::fetch_array($resu);
					$fval = $rowu['fval'];
					if($row['type'] ==  USER_FIELD_TYPE_SELECT_MULTIPLE)
					{
						$fval = split(';',$rowu['fval']);
					}
				}
				if($prefix)
				{
					$extra_data['extra_'.$row['fvar']] = $fval; 
				}
				else
				{
					$extra_data[$row['fvar']] = $fval; 
				}
			}
		}
		
		return $extra_data;
	}
	
	/**
	 * Gives a list of [session_id-course_code] => [status] for the current user.
	 * @param integer $user_id
	 * @return array  list of statuses (session_id-course_code => status)
	 */
	function get_personal_session_course_list($user_id)
	{
		// Database Table Definitions
		$tbl_course_user = Database :: get_main_table(TABLE_MAIN_COURSE_USER);
		$tbl_course 				= Database :: get_main_table(TABLE_MAIN_COURSE);
		$tbl_user 					= Database :: get_main_table(TABLE_MAIN_USER);
		$tbl_session 				= Database :: get_main_table(TABLE_MAIN_SESSION);
		$tbl_session_user			= Database :: get_main_table(TABLE_MAIN_SESSION_USER);
		$tbl_course_user 			= Database :: get_main_table(TABLE_MAIN_COURSE_USER);
		$tbl_session_course 		= Database :: get_main_table(TABLE_MAIN_SESSION_COURSE);
		$tbl_session_course_user 	= Database :: get_main_table(TABLE_MAIN_SESSION_COURSE_USER);
	
		// variable initialisation
		$personal_course_list_sql = '';
		$personal_course_list = array();
	
		//Courses in which we suscribed out of any session
		/*$personal_course_list_sql = "SELECT course.code k, course.directory d, course.visual_code c, course.db_name db, course.title i,
											course.tutor_name t, course.course_language l, course_rel_user.status s, course_rel_user.sort sort,
											course_rel_user.user_course_cat user_course_cat
											FROM    ".$tbl_course."       course,".$main_course_user_table."   course_rel_user
											WHERE course.code = course_rel_user.course_code"."
											AND   course_rel_user.user_id = '".$user_id."'
											ORDER BY course_rel_user.user_course_cat, course_rel_user.sort ASC,i";*/
	
		$tbl_user_course_category    = Database :: get_user_personal_table(TABLE_USER_COURSE_CATEGORY);
		
		$personal_course_list_sql = "SELECT course.code k, course.directory d, course.visual_code c, course.db_name db, course.title i, course.tutor_name t, course.course_language l, course_rel_user.status s, course_rel_user.sort sort, course_rel_user.user_course_cat user_course_cat
										FROM    ".$tbl_course_user." course_rel_user
										LEFT JOIN ".$tbl_course." course
										ON course.code = course_rel_user.course_code
										LEFT JOIN ".$tbl_user_course_category." user_course_category
										ON course_rel_user.user_course_cat = user_course_category.id
										WHERE  course_rel_user.user_id = '".$user_id."'
										ORDER BY user_course_category.sort, course_rel_user.sort ASC, i";
		
		$course_list_sql_result = api_sql_query($personal_course_list_sql, __FILE__, __LINE__);
		
		while ($result_row = Database::fetch_array($course_list_sql_result))
		{
			$personal_course_list[] = $result_row;
		}
	
		// get the list of sessions where the user is subscribed as student
		$sessions_sql = "SELECT DISTINCT id, name, date_start, date_end
								FROM $tbl_session_user, $tbl_session
								WHERE id_session=id AND id_user=$user_id
								AND (date_start <= NOW() AND date_end >= NOW() OR date_start='0000-00-00')
								ORDER BY date_start, date_end, name";
		$result = api_sql_query($sessions_sql,__FILE__,__LINE__);
	
		$sessions=api_store_result($result);
	
		$sessions = array_merge($sessions , api_store_result($result));
	
		// get the list of sessions where the user is subscribed as coach in a course
		$sessions_sql = "SELECT DISTINCT id, name, date_start, date_end
								FROM $tbl_session as session
								INNER JOIN $tbl_session_course as session_rel_course
									ON session_rel_course.id_coach = $user_id
								AND (date_start <= NOW() AND date_end >= NOW() OR date_start='0000-00-00')
								ORDER BY date_start, date_end, name";
		$result = api_sql_query($sessions_sql,__FILE__,__LINE__);
	
		$session_is_coach = api_store_result($result);
	
		$sessions = array_merge($sessions , $session_is_coach);
	
		// get the list of sessions where the user is subscribed as coach
		$sessions_sql = "SELECT DISTINCT id, name, date_start, date_end
								FROM $tbl_session as session
								WHERE session.id_coach = $user_id
								AND (date_start <= NOW() AND date_end >= NOW() OR date_start='0000-00-00')
								ORDER BY date_start, date_end, name";
		$result = api_sql_query($sessions_sql,__FILE__,__LINE__);
	
		$sessions = array_merge($sessions , api_store_result($result));
	
	
		if(api_is_allowed_to_create_course())
		{
			foreach($sessions as $enreg)
			{
				$id_session = $enreg['id'];
				$personal_course_list_sql = "SELECT DISTINCT course.code k, course.directory d, course.visual_code c, course.db_name db, course.title i, CONCAT(user.lastname,' ',user.firstname) t, email, course.course_language l, 1 sort, category_code user_course_cat, date_start, date_end, session.id as id_session, session.name as session_name
											 FROM $tbl_session_course as session_course
											 INNER JOIN $tbl_course AS course
											 	ON course.code = session_course.course_code
											 INNER JOIN $tbl_session as session
												ON session.id = session_course.id_session
											 LEFT JOIN $tbl_user as user
												ON user.user_id = session_course.id_coach
											 WHERE session_course.id_session = $id_session
											 AND (session_course.id_coach=$user_id OR session.id_coach=$user_id)
											ORDER BY i";
	
				$course_list_sql_result = api_sql_query($personal_course_list_sql, __FILE__, __LINE__);
	
				while ($result_row = Database::fetch_array($course_list_sql_result))
				{
					$result_row['s'] = 2;
					$key = $result_row['id_session'].' - '.$result_row['k'];
					$personal_course_list[$key] = $result_row;
				}
			}
	
		}
	
		foreach($sessions as $enreg)
		{
			$id_session = $enreg['id'];
			$personal_course_list_sql = "SELECT DISTINCT course.code k, course.directory d, course.visual_code c, course.db_name db, course.title i, CONCAT(user.lastname,' ',user.firstname) t, email, course.course_language l, 1 sort, category_code user_course_cat, date_start, date_end, session.id as id_session, session.name as session_name, IF(session_course.id_coach = ".$user_id.",'2', '5')
										 FROM $tbl_session_course as session_course
										 INNER JOIN $tbl_course AS course
										 	ON course.code = session_course.course_code
										 LEFT JOIN $tbl_user as user
											ON user.user_id = session_course.id_coach
										 INNER JOIN $tbl_session_course_user
											ON $tbl_session_course_user.id_session = $id_session
											AND $tbl_session_course_user.id_user = $user_id
										INNER JOIN $tbl_session  as session
											ON session_course.id_session = session.id
										 WHERE session_course.id_session = $id_session
										 ORDER BY i";
	
			$course_list_sql_result = api_sql_query($personal_course_list_sql, __FILE__, __LINE__);
	
			while ($result_row = Database::fetch_array($course_list_sql_result))
			{
				$key = $result_row['id_session'].' - '.$result_row['k'];
				$result_row['s'] = $result_row['14'];
	
				if(!isset($personal_course_list[$key]))
				{
					$personal_course_list[$key] = $result_row;
				}
			}
		}
		//print_r($personal_course_list);
	
		return $personal_course_list;
	}
}
?>
