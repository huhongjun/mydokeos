<?php //$id: $
/* 
==============================================================================
	Dokeos - elearning and course management software

	Copyright (c) 2004-2008 Dokeos SPRL
	Copyright (c) various contributors

	For a full list of contributors, see "credits.txt".
	The full license can be read in "license.txt".

	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	See the GNU General Public License for more details.

	Contact address: Dokeos, rue du Corbeau, 108, B-1030 Brussels, Belgium 
	info@dokeos.com
==============================================================================
*/
/**
==============================================================================
* This is the course creation library for Dokeos.
* It contains functions to create a course.
* Include/require it in your code to use its functionality.
*
* @package dokeos.library
* @todo clean up horrible structure, script is unwieldy, for example easier way to deal with
* different tool visibility settings: ALL_TOOLS_INVISIBLE, ALL_TOOLS_VISIBLE, CORE_TOOLS_VISIBLE...
==============================================================================
*/

include_once (api_get_path(LIBRARY_PATH).'database.lib.php');

/*
==============================================================================
		FUNCTIONS
==============================================================================
*/

/**
* Not tested yet.
* We need this new function so not every script that creates courses needs
* to be changed when the behaviour necessary to create a course changes.
* This will reduce bugs.
* @param	string	Course code requested (might be altered to match possible values)
* @param	string	Course title
* @param	string	Tutor name
* @param	integer	Course category code
* @param	string	Course language
* @param	integer Course admin ID
* @param	string	DB prefix
* @param	integer	Expiration delay in unix timestamp
* @return true if the course creation was succesful, false otherwise.
*/
function create_course($wanted_code, $title, $tutor_name, $category_code, $course_language, $course_admin_id, $db_prefix, $firstExpirationDelay)
{
	$keys = define_course_keys($wanted_code, "", $db_prefix);

	if(sizeof($keys))
	{
		$visual_code = $keys["currentCourseCode"];
		$code = $keys["currentCourseId"];
		$db_name = $keys["currentCourseDbName"];
		$directory = $keys["currentCourseRepository"];
		$expiration_date = time() + $firstExpirationDelay;

		prepare_course_repository($directory, $code);
		update_Db_course($db_name);
		fill_course_repository($directory);
		fill_Db_course($db_name, $directory, $course_language);
		add_course_role_right_location_values($code);
		register_course($code, $visual_code, $directory, $db_name, $tutor_name, $category_code, $title, $course_language, $course_admin_id, $expiration_date);

		return true;
	}
	else
		return false;
}


function generate_course_code($course_title)
{
	//$wantedCode = strtr($course_title, "�����������������������������������������������������������", "AAAAAACEEEEIIIIDNOOOOOOUUUUYsaaaaaaaceeeeiiiionoooooouuuuyy");
	//$wantedCode = substr(str_replace(
	//	array('Á','À','Â','Ä','Ã','Å','Æ' ,'Ç','É','È','Ê','Ë','Í','Ì','Î','Ï','Ĩ','Ó','Ò','Ô','Ö','Õ','Ø' ,'Œ' ,'Ú','Ù','Û','Ü','Ũ','Ÿ','Ý','Ð','Ñ','ß' ,'à','á','â','ä','ã','å','æ' ,'ç','Š','é','è','ê','ë','ì','í','î','ï','ĩ','ò','ó','ô','ö','õ','ø' ,'œ' ,'ú','ù','û','ü','ũ','ÿ','ý','ñ','š','€'),
	//	array('A','A','A','A','A','A','Ae','C','E','E','E','E','I','I','I','I','I','O','O','O','O','O','Oe','Oe','U','U','U','U','U','Y','Y','D','N','SS','a','a','a','a','a','a','ae','c','S','e','e','e','e','i','i','i','i','i','o','o','o','o','o','oe','oe','u','u','u','u','u','y','y','n','š','Euro'),
	//	$course_title)
	//	,0,20);
	$string    = htmlspecialchars(strtolower($course_title));
   	$string    = strtoupper(preg_replace("/&(.)(acute|grave|cedil|circ|ring|tilde|uml|slash|elig|Elig|mp);/", "$1", $string));
   	$string    = preg_replace("/[^A-Z0-9]/", "", html_entity_decode($string));
	return $string;
}


/**
 *	Defines the four needed keys to create a course based on several parameters.
 *	@return array with the needed keys ["currentCourseCode"], ["currentCourseId"], ["currentCourseDbName"], ["currentCourseRepository"]
 *
 * @param	$wantedCode the code you want for this course
 * @param	string prefix // prefix added for ALL keys
 * @todo	eliminate globals
 */
function define_course_keys($wantedCode, $prefix4all = "", $prefix4baseName = "", $prefix4path = "", $addUniquePrefix = false, $useCodeInDepedentKeys = true)
{
	global $prefixAntiNumber, $_configuration;
	$course_table = Database :: get_main_table(TABLE_MAIN_COURSE);	
	$wantedCode = generate_course_code($wantedCode);
	$keysCourseCode = $wantedCode;
	if(!$useCodeInDepedentKeys)
	{
		$wantedCode = '';
	}

	if($addUniquePrefix)
	{
		$uniquePrefix = substr(md5(uniqid(rand())), 0, 10);
	}
	else
	{
		$uniquePrefix = '';
	}

	$keys = array ();

	$finalSuffix = array ('CourseId' => '', 'CourseDb' => '', 'CourseDir' => '');

	$limitNumbTry = 100;

	$keysAreUnique = false;

	$tryNewFSCId = $tryNewFSCDb = $tryNewFSCDir = 0;

	while (!$keysAreUnique)
	{
		$keysCourseId = $prefix4all.$uniquePrefix.$wantedCode.$finalSuffix['CourseId'];

		$keysCourseDbName = $prefix4baseName.$uniquePrefix.strtoupper($keysCourseId).$finalSuffix['CourseDb'];

		$keysCourseRepository = $prefix4path.$uniquePrefix.$wantedCode.$finalSuffix['CourseDir'];

		$keysAreUnique = true;

		// check if they are unique
		$query = "SELECT 1 FROM ".$course_table . " WHERE code='".$keysCourseId . "' LIMIT 0,1";
		$result = api_sql_query($query, __FILE__, __LINE__);

		if($keysCourseId == DEFAULT_COURSE || Database::num_rows($result))
		{
			$keysAreUnique = false;

			$tryNewFSCId ++;

			$finalSuffix['CourseId'] = substr(md5(uniqid(rand())), 0, 4);
		}

		if($_configuration['single_database'])
		{
			$query = "SHOW TABLES FROM `".$_configuration['main_database']."` LIKE '".$_configuration['table_prefix']."$keysCourseDbName".$_configuration['db_glue']."%'";
			$result = api_sql_query($query, __FILE__, __LINE__);
		}
		else
		{
			$query = "SHOW DATABASES LIKE '$keysCourseDbName'";
			$result = api_sql_query($query, __FILE__, __LINE__);
		}

		if(Database::num_rows($result))
		{
			$keysAreUnique = false;

			$tryNewFSCDb ++;

			$finalSuffix['CourseDb'] = substr('_'.md5(uniqid(rand())), 0, 4);
		}

		// @todo: use and api_get_path here instead of constructing it by yourself
		if(file_exists($_configuration['root_sys'].$_configuration['course_folder'].$keysCourseRepository))
		{
			$keysAreUnique = false;

			$tryNewFSCDir ++;

			$finalSuffix['CourseDir'] = substr(md5(uniqid(rand())), 0, 4);
		}

		if(($tryNewFSCId + $tryNewFSCDb + $tryNewFSCDir) > $limitNumbTry)
		{
			return $keys;
		}
	}

	// db name can't begin with a number
	if(!stristr("abcdefghijklmnopqrstuvwxyz", $keysCourseDbName[0]))
	{
		$keysCourseDbName = $prefixAntiNumber.$keysCourseDbName;
	}

	$keys["currentCourseCode"] = $keysCourseCode;
	$keys["currentCourseId"] = $keysCourseId;
	$keys["currentCourseDbName"] = $keysCourseDbName;
	$keys["currentCourseRepository"] = $keysCourseRepository;

	return $keys;
}

/**
 *
 *
 */
function prepare_course_repository($courseRepository, $courseId)
{
	umask(0);
	$perm = api_get_setting('permissions_for_new_directories');
	$perm = octdec(!empty($perm)?$perm:'0770');
    $perm_file = api_get_setting('permissions_for_new_files');
    $perm_file = octdec(!empty($perm_file)?$perm_file:'0660');
	mkdir(api_get_path(SYS_COURSE_PATH).$courseRepository, $perm);
	mkdir(api_get_path(SYS_COURSE_PATH).$courseRepository . "/document", $perm);
	mkdir(api_get_path(SYS_COURSE_PATH).$courseRepository . "/document/images", $perm);
	mkdir(api_get_path(SYS_COURSE_PATH).$courseRepository . "/document/images/gallery/", $perm);
	mkdir(api_get_path(SYS_COURSE_PATH).$courseRepository . "/document/audio", $perm);
	mkdir(api_get_path(SYS_COURSE_PATH).$courseRepository . "/document/flash", $perm);
	mkdir(api_get_path(SYS_COURSE_PATH).$courseRepository . "/document/video", $perm);
	mkdir(api_get_path(SYS_COURSE_PATH).$courseRepository . "/dropbox", $perm);
	mkdir(api_get_path(SYS_COURSE_PATH).$courseRepository . "/group", $perm);
	mkdir(api_get_path(SYS_COURSE_PATH).$courseRepository . "/page", $perm);
	mkdir(api_get_path(SYS_COURSE_PATH).$courseRepository . "/scorm", $perm);
	mkdir(api_get_path(SYS_COURSE_PATH).$courseRepository . "/temp", $perm);
	mkdir(api_get_path(SYS_COURSE_PATH).$courseRepository . "/upload", $perm);
	mkdir(api_get_path(SYS_COURSE_PATH).$courseRepository . "/upload/forum", $perm);
	mkdir(api_get_path(SYS_COURSE_PATH).$courseRepository . "/upload/test", $perm);
	mkdir(api_get_path(SYS_COURSE_PATH).$courseRepository . "/upload/blog", $perm);
	mkdir(api_get_path(SYS_COURSE_PATH).$courseRepository . "/work", $perm);

	//create .htaccess in dropbox
	$fp = fopen(api_get_path(SYS_COURSE_PATH).$courseRepository . "/dropbox/.htaccess", "w");
	fwrite($fp, "AuthName AllowLocalAccess
	               AuthType Basic

	               order deny,allow
	               deny from all

	               php_flag zlib.output_compression off");
	fclose($fp);

	// build index.php of course
	$fd = fopen(api_get_path(SYS_COURSE_PATH).$courseRepository . "/index.php", "w");

	// str_replace() removes \r that cause squares to appear at the end of each line
	$string = str_replace("\r", "", "<?" . "php
	\$cidReq = \"$courseId\";
	\$dbname = \"$courseId\";

	include(\"../../main/course_home/course_home.php\");
	?>");
	fwrite($fd,$string);
    $perm_file = api_get_setting('permissions_for_new_files');
    $perm_file = octdec(!empty($perm_file)?$perm_file:'0660');
    @chmod(api_get_path(SYS_COURSE_PATH).$courseRepository . '/index.php',$perm_file);
	$fd = fopen(api_get_path(SYS_COURSE_PATH).$courseRepository . '/group/index.html', 'w');
	$string = "<html></html>";
	fwrite($fd, "$string");
	return 0;
};

function update_Db_course($courseDbName)
{
	global $_configuration;

	if(!$_configuration['single_database'])
	{
		api_sql_query("CREATE DATABASE IF NOT EXISTS `" . $courseDbName . "`", __FILE__, __LINE__);
	}

	$courseDbName = $_configuration['table_prefix'].$courseDbName.$_configuration['db_glue'];

	$tbl_course_homepage 		= $courseDbName . "tool";
	$TABLEINTROS 				= $courseDbName . "tool_intro";

	// Group tool
	$TABLEGROUPS 				= $courseDbName . "group_info";
	$TABLEGROUPCATEGORIES 		= $courseDbName . "group_category";
	$TABLEGROUPUSER 			= $courseDbName . "group_rel_user";
	$TABLEGROUPTUTOR 			= $courseDbName . "group_rel_tutor";

	$TABLEITEMPROPERTY 			= $courseDbName . "item_property";

	$TABLETOOLUSERINFOCONTENT 	= $courseDbName . "userinfo_content";
	$TABLETOOLUSERINFODEF 		= $courseDbName . "userinfo_def";

	$TABLETOOLCOURSEDESC		= $courseDbName . "course_description";
	$TABLETOOLAGENDA 			= $courseDbName . "calendar_event";
	$TABLETOOLAGENDAREPEAT		= $courseDbName . "calendar_event_repeat";
	$TABLETOOLAGENDAREPEATNOT	= $courseDbName . "calendar_event_repeat_not";

	// Announcements
	$TABLETOOLANNOUNCEMENTS 	= $courseDbName . "announcement";

	// Resourcelinker
	$TABLEADDEDRESOURCES 		= $courseDbName . "resource";

	// Student Publication
	$TABLETOOLWORKS 			= $courseDbName . "student_publication";

	// Document
	$TABLETOOLDOCUMENT 			= $courseDbName . "document";

	// Forum
	$TABLETOOLFORUMCATEGORY 	= $courseDbName . "forum_category";
	$TABLETOOLFORUM 			= $courseDbName . "forum_forum";
	$TABLETOOLFORUMTHREAD 		= $courseDbName . "forum_thread";
	$TABLETOOLFORUMPOST 		= $courseDbName . "forum_post";
	$TABLETOOLFORUMMAILCUE 		= $courseDbName . "forum_mailcue";
	$TABLETOOLFORUMATTACHMENT	= $courseDbName . "forum_attachment";
	$TABLETOOLFORUMNOTIFICATION = $courseDbName . 'forum_notification';

	// Link
	$TABLETOOLLINK 				= $courseDbName . "link";
	$TABLETOOLLINKCATEGORIES 	= $courseDbName . "link_category";

	$TABLETOOLONLINECONNECTED 	= $courseDbName . "online_connected";
	$TABLETOOLONLINELINK 		= $courseDbName . "online_link";

	// Chat
	$TABLETOOLCHATCONNECTED 	= $courseDbName . "chat_connected";

	// Quiz (a.k.a. exercises)
	$TABLEQUIZ 					= $courseDbName . "quiz";
	$TABLEQUIZQUESTION 			= $courseDbName . "quiz_rel_question";
	$TABLEQUIZQUESTIONLIST 		= $courseDbName . "quiz_question";
	$TABLEQUIZANSWERSLIST 		= $courseDbName . "quiz_answer";

	// Dropbox
	$TABLETOOLDROPBOXPOST 		= $courseDbName . 'dropbox_post';
	$TABLETOOLDROPBOXFILE 		= $courseDbName . 'dropbox_file';
	$TABLETOOLDROPBOXPERSON 	= $courseDbName . 'dropbox_person';
	$TABLETOOLDROPBOXCATEGORY 	= $courseDbName . 'dropbox_category';
	$TABLETOOLDROPBOXFEEDBACK 	= $courseDbName . 'dropbox_feedback';

	// Learning Path
	$TABLELEARNPATHITEMS 		= $courseDbName . "learnpath_item";
	$TABLELEARNPATHCHAPTERS 	= $courseDbName . "learnpath_chapter";
	$TABLELEARNPATHMAIN 		= $courseDbName . "learnpath_main";
	$TABLELEARNPATHUSERS 		= $courseDbName . "learnpath_user";

	// New Learning path
	$TABLELP					= $courseDbName . "lp";
	$TABLELPITEM				= $courseDbName . "lp_item";
	$TABLELPVIEW				= $courseDbName . "lp_view";
	$TABLELPITEMVIEW			= $courseDbName . "lp_item_view";
	$TABLELPIVINTERACTION		= $courseDbName . "lp_iv_interaction";
	$TABLELPIVOBJECTIVE			= $courseDbName . "lp_iv_objective";

	// Smartblogs (Kevin Van Den Haute :: kevin@develop-it.be)
	$tbl_blogs					= $courseDbName . 'blog';
	$tbl_blogs_comments			= $courseDbName . 'blog_comment';
	$tbl_blogs_posts			= $courseDbName . 'blog_post';
	$tbl_blogs_rating			= $courseDbName . 'blog_rating';
	$tbl_blogs_rel_user			= $courseDbName . 'blog_rel_user';
	$tbl_blogs_tasks			= $courseDbName . 'blog_task';
	$tbl_blogs_tasks_rel_user	= $courseDbName . 'blog_task_rel_user';
	$tbl_blogs_attachment		= $courseDbName . 'blog_attachment';

	//Smartblogs permissions (Kevin Van Den Haute :: kevin@develop-it.be)
	$tbl_permission_group		= $courseDbName . 'permission_group';
	$tbl_permission_user		= $courseDbName . 'permission_user';
	$tbl_permission_task		= $courseDbName . 'permission_task';

	//Smartblogs roles (Kevin Van Den Haute :: kevin@develop-it.be)
	$tbl_role					= $courseDbName . 'role';
	$tbl_role_group				= $courseDbName . 'role_group';
	$tbl_role_permissions		= $courseDbName . 'role_permissions';
	$tbl_role_user				= $courseDbName . 'role_user';

	//Survey variables for course homepage;
	$TABLESURVEY 				= $courseDbName . 'survey';
	$TABLESURVEYQUESTION		= $courseDbName . 'survey_question';
	$TABLESURVEYQUESTIONOPTION	= $courseDbName . 'survey_question_option';
	$TABLESURVEYINVITATION		= $courseDbName . 'survey_invitation';
	$TABLESURVEYANSWER			= $courseDbName . 'survey_answer';

	// audiorecorder
	$TABLEAUDIORECORDER = $courseDbName.'audiorecorder';

	// Course settings
	$TABLESETTING = $courseDbName . "course_setting";

	/*
	-----------------------------------------------------------
		Announcement tool
	-----------------------------------------------------------
	*/
	$sql = "
		CREATE TABLE `".$TABLETOOLANNOUNCEMENTS . "` (
		id mediumint unsigned NOT NULL auto_increment,
		title text,
		content mediumtext,
		end_date date default NULL,
		display_order mediumint NOT NULL default 0,
		email_sent tinyint default 0,
		PRIMARY KEY (id)
		) TYPE=MyISAM";
	api_sql_query($sql, __FILE__, __LINE__);

	/*
	-----------------------------------------------------------
		Resources
	-----------------------------------------------------------
	*/
	$sql = "
		CREATE TABLE `".$TABLEADDEDRESOURCES . "` (
		id int unsigned NOT NULL auto_increment,
		source_type varchar(50) default NULL,
		source_id int unsigned default NULL,
		resource_type varchar(50) default NULL,
		resource_id int unsigned default NULL,
		UNIQUE KEY id (id)
		) TYPE=MyISAM";
	api_sql_query($sql, __FILE__, __LINE__);

	$sql = "
		CREATE TABLE `".$TABLETOOLUSERINFOCONTENT . "` (
		id int unsigned NOT NULL auto_increment,
		user_id int unsigned NOT NULL,
		definition_id int unsigned NOT NULL,
		editor_ip varchar(39) default NULL,
		edition_time datetime default NULL,
		content text NOT NULL,
		PRIMARY KEY (id),
		KEY user_id (user_id)
		) TYPE=MyISAM";

	api_sql_query($sql, __FILE__, __LINE__);

	// Unused table. Temporarily ignored for tests.
	// Reused because of user/userInfo and user/userInfoLib scripts
	$sql = "
		CREATE TABLE `".$TABLETOOLUSERINFODEF . "` (
		id int unsigned NOT NULL auto_increment,
		title varchar(80) NOT NULL default '',
		comment text,
		line_count tinyint unsigned NOT NULL default 5,
		rank tinyint unsigned NOT NULL default 0,
		PRIMARY KEY (id)
		) TYPE=MyISAM";

	api_sql_query($sql, __FILE__, __LINE__);

	/*
	-----------------------------------------------------------
		Forum tool
	-----------------------------------------------------------
	*/
	// Forum Category
	$sql = "
		CREATE TABLE `".$TABLETOOLFORUMCATEGORY . "` (
		 cat_id int NOT NULL auto_increment,
		 cat_title varchar(255) NOT NULL default '',
		 cat_comment text,
		 cat_order int NOT NULL default 0,
		 locked int NOT NULL default 0,
		 PRIMARY KEY (cat_id)
		) TYPE=MyISAM";

	api_sql_query($sql, __FILE__, __LINE__);

	// Forum
	$sql = "
		CREATE TABLE `".$TABLETOOLFORUM . "` (
		 forum_id int NOT NULL auto_increment,
		 forum_title varchar(255) NOT NULL default '',
		 forum_comment text,
		 forum_threads int default 0,
		 forum_posts int default 0,
		 forum_last_post int default 0,
		 forum_category int default NULL,
		 allow_anonymous int default NULL,
		 allow_edit int default NULL,
		 approval_direct_post varchar(20) default NULL,
		 allow_attachments int default NULL,
		 allow_new_threads int default NULL,
		 default_view varchar(20) default NULL,
		 forum_of_group varchar(20) default NULL,
		 forum_group_public_private varchar(20) default 'public',
		 forum_order int default NULL,
		 locked int NOT NULL default 0,
		 session_id int NOT NULL default 0,
		 PRIMARY KEY (forum_id)
		) TYPE=MyISAM";

	api_sql_query($sql, __FILE__, __LINE__);

	// Forum Threads
	$sql = "
		CREATE TABLE `".$TABLETOOLFORUMTHREAD . "` (
		 thread_id int NOT NULL auto_increment,
		 thread_title varchar(255) default NULL,
		 forum_id int default NULL,
		 thread_replies int default 0,
		 thread_poster_id int default NULL,
		 thread_poster_name varchar(100) default '',
		 thread_views int default 0,
		 thread_last_post int default NULL,
		 thread_date datetime default '0000-00-00 00:00:00',
		 thread_sticky tinyint unsigned default 0,
		 locked int NOT NULL default 0,
		 PRIMARY KEY (thread_id)
		) TYPE=MyISAM";

	api_sql_query($sql, __FILE__, __LINE__);
	$sql = "ALTER TABLE `".$TABLETOOLFORUMTHREAD . "` ADD INDEX idx_forum_thread_forum_id (forum_id)";
	api_sql_query($sql, __FILE__, __LINE__);
	
	// Forum Posts
	$sql = "
		CREATE TABLE `".$TABLETOOLFORUMPOST . "` (
		 post_id int NOT NULL auto_increment,
		 post_title varchar(250) default NULL,
		 post_text text,
		 thread_id int default 0,
		 forum_id int default 0,
		 poster_id int default 0,
		 poster_name varchar(100) default '',
		 post_date datetime default '0000-00-00 00:00:00',
		 post_notification tinyint default 0,
		 post_parent_id int default 0,
		 visible tinyint default 1,
		 PRIMARY KEY (post_id),
		 KEY poster_id (poster_id),
		 KEY forum_id (forum_id)
		) TYPE=MyISAM";

	api_sql_query($sql, __FILE__, __LINE__);
	$sql = "ALTER TABLE `".$TABLETOOLFORUMPOST . "` ADD INDEX idx_forum_post_thread_id (thread_id)";
	api_sql_query($sql, __FILE__, __LINE__);
	$sql = "ALTER TABLE `".$TABLETOOLFORUMPOST . "` ADD INDEX idx_forum_post_visible (visible)";
	api_sql_query($sql, __FILE__, __LINE__);

	// Forum Mailcue
	$sql = "
		CREATE TABLE `".$TABLETOOLFORUMMAILCUE . "` (
		 thread_id int default NULL,
		 user_id int default NULL,
		 post_id int default NULL
		) TYPE=MyISAM";

	api_sql_query($sql, __FILE__, __LINE__);
	
	
	// Forum Attachment
	$sql = "CREATE TABLE  `".$TABLETOOLFORUMATTACHMENT."` (
			  id int NOT NULL auto_increment,
			  path varchar(255) NOT NULL,
			  comment text,
			  size int NOT NULL default 0,
			  post_id int NOT NULL,
			  filename varchar(255) NOT NULL,
			  PRIMARY KEY (id)
			)";
	api_sql_query($sql, __FILE__, __LINE__);
	
	// Forum notification
	$sql = "CREATE TABLE  `".$TABLETOOLFORUMNOTIFICATION."` (
			  user_id int(11),
			  forum_id varchar(11),
			  thread_id varchar(11),
			  post_id varchar(11),
			    KEY user_id (user_id),
  				KEY forum_id (forum_id)
			)";
	api_sql_query($sql, __FILE__, __LINE__);	

	/*
	-----------------------------------------------------------
		Exercise tool
	-----------------------------------------------------------
	*/
	// Exercise tool - Tests/exercises
	$sql = "
		CREATE TABLE `".$TABLEQUIZ . "` (
		id mediumint unsigned NOT NULL auto_increment,
		title varchar(200) NOT NULL,
		description text default NULL,
		sound varchar(50) default NULL,
		type tinyint unsigned NOT NULL default 1,
		random smallint(6) NOT NULL default 0,
		active tinyint NOT NULL default 0,
		results_disabled TINYINT UNSIGNED NOT NULL DEFAULT 0,
		access_condition TEXT DEFAULT NULL,
		PRIMARY KEY (id)
		)";
	api_sql_query($sql, __FILE__, __LINE__);

	// Exercise tool - questions
	$sql = "
		CREATE TABLE `".$TABLEQUIZQUESTIONLIST . "` (
		id mediumint unsigned NOT NULL auto_increment,
		question varchar(200) NOT NULL,
		description text default NULL,
		ponderation smallint unsigned default NULL,
		position mediumint unsigned NOT NULL default 1,
		type tinyint unsigned NOT NULL default 2,
		picture varchar(50) default NULL,
		PRIMARY KEY (id)
		)";
	api_sql_query($sql, __FILE__, __LINE__);
	$sql = "ALTER TABLE `".$TABLEQUIZQUESTIONLIST . "` ADD INDEX (position)";
	api_sql_query($sql, __FILE__, __LINE__);
	 
	// Exercise tool - answers
	$sql = "
		CREATE TABLE `".$TABLEQUIZANSWERSLIST . "` (
		id mediumint unsigned NOT NULL,
		question_id mediumint unsigned NOT NULL,
		answer text NOT NULL,
		correct mediumint unsigned default NULL,
		comment text default NULL,
		ponderation smallint default NULL,
		position mediumint unsigned NOT NULL default 1,
	    hotspot_coordinates tinytext,
	    hotspot_type enum('square','circle','poly') default NULL,
		PRIMARY KEY (id, question_id)
		)";
	api_sql_query($sql, __FILE__, __LINE__);

	// Exercise tool - Test/question relations
	$sql = "
		CREATE TABLE `".$TABLEQUIZQUESTION . "` (
		question_id mediumint unsigned NOT NULL,
		exercice_id mediumint unsigned NOT NULL,
		PRIMARY KEY (question_id,exercice_id)
		)";
	api_sql_query($sql, __FILE__, __LINE__);

	/*
	-----------------------------------------------------------
		Course description
	-----------------------------------------------------------
	*/
	$sql = "
		CREATE TABLE `".$TABLETOOLCOURSEDESC . "` (
		id TINYINT UNSIGNED NOT NULL auto_increment,
		title VARCHAR(255),
		content TEXT,
		UNIQUE (id)
		)";
	api_sql_query($sql, __FILE__, __LINE__);

	/*
	-----------------------------------------------------------
		Course homepage tool list
	-----------------------------------------------------------
	*/
	$sql = "
		CREATE TABLE `" . $tbl_course_homepage . "` (
		id int unsigned NOT NULL auto_increment,
		name varchar(100) NOT NULL,
		link varchar(255) NOT NULL,
		image varchar(100) default NULL,
		visibility tinyint unsigned default 0,
		admin varchar(200) default NULL,
		address varchar(120) default NULL,
		added_tool tinyint unsigned default 1,
		target enum('_self','_blank') NOT NULL default '_self',
		category enum('authoring','interaction','admin') NOT NULL default 'authoring',
		PRIMARY KEY (id)
		) TYPE=MyISAM";
	api_sql_query($sql, __FILE__, __LINE__);

	/*
	-----------------------------------------------------------
		Agenda tool
	-----------------------------------------------------------
	*/
	$sql = "
		CREATE TABLE `".$TABLETOOLAGENDA . "` (
		id int unsigned NOT NULL auto_increment,
		title varchar(200) NOT NULL,
		content text,
		start_date datetime NOT NULL default '0000-00-00 00:00:00',
		end_date datetime NOT NULL default '0000-00-00 00:00:00',
        parent_event_id INT NULL,
		PRIMARY KEY (id)
		)";
	api_sql_query($sql, __FILE__, __LINE__);

	$sql = "
		CREATE TABLE `".$TABLETOOLAGENDAREPEAT. "` (
		cal_id INT DEFAULT 0 NOT NULL,
		cal_type VARCHAR(20),  
		cal_end INT,  
		cal_frequency INT DEFAULT 1,  
		cal_days CHAR(7),  
		PRIMARY KEY (cal_id)
		)";
	api_sql_query($sql,__FILE__,__LINE__);
	$sql = "
		CREATE TABLE `".$TABLETOOLAGENDAREPEATNOT."` (
		cal_id INT NOT NULL,  
		cal_date INT NOT NULL,  
		PRIMARY KEY ( cal_id, cal_date )
		)";
	api_sql_query($sql,__FILE__,__LINE__);
		
	/*
	-----------------------------------------------------------
		Document tool
	-----------------------------------------------------------
	*/
	$sql = "
		CREATE TABLE `".$TABLETOOLDOCUMENT . "` (
			id int unsigned NOT NULL auto_increment,
			path varchar(255) NOT NULL default '',
			comment text,
			title varchar(255) default NULL,
			filetype set('file','folder') NOT NULL default 'file',
			size int NOT NULL default 0,
			readonly TINYINT UNSIGNED NOT NULL,
			PRIMARY KEY (`id`)
		)";
	api_sql_query($sql, __FILE__, __LINE__);

	/*
	-----------------------------------------------------------
		Student publications
	-----------------------------------------------------------
	*/
	$sql = "
		CREATE TABLE `".$TABLETOOLWORKS . "` (
		id int unsigned NOT NULL auto_increment,
		url varchar(200) default NULL,
		title varchar(200) default NULL,
		description varchar(250) default NULL,
		author varchar(200) default NULL,
		active tinyint default NULL,
		accepted tinyint default 0,
		post_group_id int DEFAULT 0 NOT NULL,
		sent_date datetime NOT NULL default '0000-00-00 00:00:00',
		filetype set('file','folder') NOT NULL default 'file',
		PRIMARY KEY (id)
		)";
	api_sql_query($sql, __FILE__, __LINE__);

	/*
	-----------------------------------------------------------
		Links tool
	-----------------------------------------------------------
	*/
	$sql = "
		CREATE TABLE `".$TABLETOOLLINK . "` (
		id int unsigned NOT NULL auto_increment,
		url TEXT NOT NULL,
		title varchar(150) default NULL,
		description text,
		category_id smallint unsigned default NULL,
		display_order smallint unsigned NOT NULL default 0,
		on_homepage enum('0','1') NOT NULL default '0',
		PRIMARY KEY (id)
		)";
	api_sql_query($sql, __FILE__, __LINE__);

	$sql = "
		CREATE TABLE `".$TABLETOOLLINKCATEGORIES . "` (
		id smallint unsigned NOT NULL auto_increment,
		category_title varchar(255) NOT NULL,
		description text,
		display_order mediumint unsigned NOT NULL default 0,
		PRIMARY KEY (id)
		)";
	api_sql_query($sql, __FILE__, __LINE__);

	/*
	-----------------------------------------------------------
		Online
	-----------------------------------------------------------
	*/
	$sql = "
		CREATE TABLE `".$TABLETOOLONLINECONNECTED . "` (
		user_id int unsigned NOT NULL,
		last_connection datetime NOT NULL default '0000-00-00 00:00:00',
		PRIMARY KEY (user_id)
		)";
	api_sql_query($sql, __FILE__, __LINE__);

	$sql = "
		CREATE TABLE `".$TABLETOOLONLINELINK . "` (
		id smallint unsigned NOT NULL auto_increment,
		name char(50) NOT NULL default '',
		url char(100) NOT NULL,
		PRIMARY KEY (id)
		)";
	api_sql_query($sql, __FILE__, __LINE__);

	$sql = "
		CREATE TABLE `".$TABLETOOLCHATCONNECTED . "` (
		user_id int unsigned NOT NULL default '0',
		last_connection datetime NOT NULL default '0000-00-00 00:00:00',
		PRIMARY KEY (user_id)
		)";
	api_sql_query($sql, __FILE__, __LINE__);

	/*
	-----------------------------------------------------------
		Groups tool
	-----------------------------------------------------------
	*/
	api_sql_query("CREATE TABLE `".$TABLEGROUPS . "` (
		id int unsigned NOT NULL auto_increment,
		name varchar(100) default NULL,
		category_id int unsigned NOT NULL default 0,
		description text,
		max_student smallint unsigned NOT NULL default 8,
		doc_state tinyint unsigned NOT NULL default 1,
		calendar_state tinyint unsigned NOT NULL default 0,
		work_state tinyint unsigned NOT NULL default 0,
		announcements_state tinyint unsigned NOT NULL default 0,
		secret_directory varchar(255) default NULL,
		self_registration_allowed tinyint unsigned NOT NULL default '0',
		self_unregistration_allowed tinyint unsigned NOT NULL default '0',
		PRIMARY KEY (id)
		)", __FILE__, __LINE__);

	api_sql_query("CREATE TABLE `".$TABLEGROUPCATEGORIES . "` (
		id int unsigned NOT NULL auto_increment,
		title varchar(255) NOT NULL default '',
		description text NOT NULL,
		doc_state tinyint unsigned NOT NULL default 1,
		calendar_state tinyint unsigned NOT NULL default 1,
		work_state tinyint unsigned NOT NULL default 1,
		announcements_state tinyint unsigned NOT NULL default 1,
		forum_state tinyint unsigned NOT NULL default 0,
		max_student smallint unsigned NOT NULL default 8,
		self_reg_allowed tinyint unsigned NOT NULL default 0,
		self_unreg_allowed tinyint unsigned NOT NULL default 0,
		groups_per_user smallint unsigned NOT NULL default 0,
		display_order smallint unsigned NOT NULL default 0,
		PRIMARY KEY (id)
		)", __FILE__, __LINE__);

	api_sql_query("CREATE TABLE `".$TABLEGROUPUSER . "` (
		id int unsigned NOT NULL auto_increment,
		user_id int unsigned NOT NULL,
		group_id int unsigned NOT NULL default 0,
		status int NOT NULL default 0,
		role char(50) NOT NULL,
		PRIMARY KEY (id)
		)", __FILE__, __LINE__);

	api_sql_query("CREATE TABLE `".$TABLEGROUPTUTOR . "` (
		id int NOT NULL auto_increment,
		user_id int NOT NULL,
		group_id int NOT NULL default 0,
		PRIMARY KEY (id)
		)", __FILE__, __LINE__);

	api_sql_query("CREATE TABLE `".$TABLEITEMPROPERTY . "` (
		tool varchar(100) NOT NULL default '',
		insert_user_id int unsigned NOT NULL default '0',
		insert_date datetime NOT NULL default '0000-00-00 00:00:00',
		lastedit_date datetime NOT NULL default '0000-00-00 00:00:00',
		ref int NOT NULL default '0',
		lastedit_type varchar(100) NOT NULL default '',
		lastedit_user_id int unsigned NOT NULL default '0',
		to_group_id int unsigned default NULL,
		to_user_id int unsigned default NULL,
		visibility tinyint NOT NULL default '1',
		start_visible datetime NOT NULL default '0000-00-00 00:00:00',
		end_visible datetime NOT NULL default '0000-00-00 00:00:00'
		) TYPE=MyISAM;", __FILE__, __LINE__);
	api_sql_query("ALTER TABLE `$TABLEITEMPROPERTY` ADD INDEX idx_item_property_toolref (tool,ref)", __FILE__, __LINE__);

	/*
	-----------------------------------------------------------
		Tool introductions
	-----------------------------------------------------------
	*/
	api_sql_query("
		CREATE TABLE `".$TABLEINTROS . "` (
		id varchar(50) NOT NULL,
		intro_text text NOT NULL,
		PRIMARY KEY (id))", __FILE__, __LINE__);

	/*
	-----------------------------------------------------------
		Dropbox tool
	-----------------------------------------------------------
	*/
	api_sql_query("
		CREATE TABLE `".$TABLETOOLDROPBOXFILE . "` (
		id int unsigned NOT NULL auto_increment,
		uploader_id int unsigned NOT NULL default 0,
		filename varchar(250) NOT NULL default '',
		filesize int unsigned NOT NULL,
		title varchar(250) default '',
		description varchar(250) default '',
		author varchar(250) default '',
		upload_date datetime NOT NULL default '0000-00-00 00:00:00',
		last_upload_date datetime NOT NULL default '0000-00-00 00:00:00',
		cat_id int NOT NULL default 0,
		session_id SMALLINT UNSIGNED NOT NULL,
		PRIMARY KEY (id),
		UNIQUE KEY UN_filename (filename)
		)", __FILE__, __LINE__);
		
	api_sql_query("ALTER TABLE `$TABLETOOLDROPBOXFILE` ADD INDEX ( `session_id` )", __FILE__, __LINE__);
	
	api_sql_query("
		CREATE TABLE `".$TABLETOOLDROPBOXPOST . "` (
		file_id int unsigned NOT NULL,
		dest_user_id int unsigned NOT NULL default 0,
		feedback_date datetime NOT NULL default '0000-00-00 00:00:00',
		feedback text default '',
		cat_id int(11) NOT NULL default 0,
		session_id SMALLINT UNSIGNED NOT NULL,
		PRIMARY KEY (file_id,dest_user_id)
		)", __FILE__, __LINE__);
		
	api_sql_query("ALTER TABLE `$TABLETOOLDROPBOXPOST` ADD INDEX ( `session_id` )", __FILE__, __LINE__);

	api_sql_query("
		CREATE TABLE `".$TABLETOOLDROPBOXPERSON . "` (
		file_id int unsigned NOT NULL,
		user_id int unsigned NOT NULL default 0,
		PRIMARY KEY (file_id,user_id)
		)", __FILE__, __LINE__);

	$sql = "CREATE TABLE `".$TABLETOOLDROPBOXCATEGORY."` (
  			cat_id int NOT NULL auto_increment,
			cat_name text NOT NULL,
  			received tinyint unsigned NOT NULL default 0,
  			sent tinyint unsigned NOT NULL default 0,
  			user_id int NOT NULL default 0,
  			PRIMARY KEY  (cat_id)
  			)";
	api_sql_query($sql, __FILE__, __LINE__);

	$sql = "CREATE TABLE `".$TABLETOOLDROPBOXFEEDBACK."` (
			  feedback_id int NOT NULL auto_increment,
			  file_id int NOT NULL default 0,
			  author_user_id int NOT NULL default 0,
			  feedback text NOT NULL,
			  feedback_date datetime NOT NULL default '0000-00-00 00:00:00',
			  PRIMARY KEY  (feedback_id),
			  KEY file_id (file_id),
			  KEY author_user_id (author_user_id)
  			)";
	api_sql_query($sql, __FILE__, __LINE__);

	/*
	-----------------------------------------------------------
		New learning path
	-----------------------------------------------------------
	*/
	$sql = "CREATE TABLE IF NOT EXISTS `$TABLELP` (" .
		"id				int	unsigned	primary key auto_increment," . //unique ID, generated by MySQL
		"lp_type		smallint	unsigned not null," .	//lp_types can be found in the main database's lp_type table
		"name			tinytext	not null," . //name is the text name of the learning path (e.g. Word 2000)
		"ref			tinytext	null," . //ref for SCORM elements is the SCORM ID in imsmanifest. For other learnpath types, just ignore
		"description	text	null,". //textual description
		"path 			text	not null," . //path, starting at the platforms root (so all paths should start with 'courses/...' for now)
		"force_commit  tinyint		unsigned not null default 0, " . //stores the default behaviour regarding SCORM information
		"default_view_mod char(32) not null default 'embedded'," .//stores the default view mode (embedded or fullscreen)
		"default_encoding char(32)	not null default 'ISO-8859-1', " . //stores the encoding detected at learning path reading
		"display_order int		unsigned	not null default 0," . //order of learnpaths display in the learnpaths list - not really important
		"content_maker tinytext  not null default ''," . //the content make for this course (ENI, Articulate, ...)
		"content_local 	varchar(32)  not null default 'local'," . //content localisation ('local' or 'distant')
		"content_license	text not null default ''," . //content license
		"prevent_reinit tinyint		unsigned not null default 1," . //stores the default behaviour regarding items re-initialisation when viewed a second time after success
		"js_lib         tinytext    not null default ''," . //the JavaScript library to load for this lp
		"debug 			tinyint		unsigned not null default 0," . //stores the default behaviour regarding items re-initialisation when viewed a second time after success
		"theme 		varchar(255)    not null default '' " . //stores the theme of the LP 			
		")";
	if(!api_sql_query($sql, __FILE__, __LINE__))
	{
		error_log($sql,0);
	}

	$sql = "CREATE TABLE IF NOT EXISTS `$TABLELPVIEW` (" .
		"id				int		unsigned	primary key auto_increment," . //unique ID from MySQL
		"lp_id			int		unsigned	not null," . //learnpath ID from 'lp'
		"user_id		int 	unsigned	not null," . //user ID from main.user
		"view_count		smallint unsigned	not null default 0," . //integer counting the amount of times this learning path has been attempted
		"last_item		int		unsigned	not null default 0," . //last item seen in this view
		"progress		int		unsigned	default 0 )"; //lp's progress for this user
	if(!api_sql_query($sql, __FILE__, __LINE__))
	{
		error_log($sql,0);
	}
	$sql = "ALTER TABLE `$TABLELPVIEW` ADD INDEX (lp_id) ";
	if(!api_sql_query($sql, __FILE__, __LINE__))
	{
		error_log($sql,0);
	}
	$sql = "ALTER TABLE `$TABLELPVIEW` ADD INDEX (user_id) ";
	if(!api_sql_query($sql, __FILE__, __LINE__))
	{
		error_log($sql,0);
	}
	
	$sql = "CREATE TABLE IF NOT EXISTS `$TABLELPITEM` (" .
		"id				int	unsigned	primary	key auto_increment," .	//unique ID from MySQL
		"lp_id			int unsigned	not null," .	//lp_id from 'lp'
		"item_type		char(32)	not null default 'dokeos_document'," . //can be dokeos_document, dokeos_chapter or scorm_asset, scorm_sco, scorm_chapter
		"ref			tinytext	not null default ''," . //the ID given to this item in the imsmanifest file
		"title			tinytext	not null," . //the title/name of this item (to display in the T.O.C.)
		"description	tinytext	not null default ''," . //the description of this item - deprecated
		"path			text		not null," . //the path to that item, starting at 'courses/...' level
		"min_score		float unsigned	not null default 0," . //min score allowed
		"max_score		float unsigned	not null default 100," . //max score allowed
		"mastery_score float unsigned null," . //minimum score to pass the test
		"parent_item_id		int unsigned	not null default 0," . //the item one level higher
		"previous_item_id	int unsigned	not null default 0," . //the item before this one in the sequential learning order (MySQL id)
		"next_item_id		int unsigned	not null default 0," . //the item after this one in the sequential learning order (MySQL id)
		"display_order		int unsigned	not null default 0," . //this is needed for ordering items under the same parent (previous_item_id doesn't give correct order after reordering)
		"prerequisite  text  null default null," . //prerequisites in AICC scripting language as defined in the SCORM norm (allow logical operators)
		"parameters  text  null," . //prerequisites in AICC scripting language as defined in the SCORM norm (allow logical operators)
		"launch_data 	text	not null default ''," . //data from imsmanifest <item>
		"max_time_allowed char(13) NULL default '')"; //data from imsmanifest <adlcp:maxtimeallowed>
	if(!api_sql_query($sql, __FILE__, __LINE__))
	{
		error_log($sql,0);
	}
	$sql = "ALTER TABLE `$TABLELPITEM` ADD INDEX (lp_id)";
	if(!api_sql_query($sql, __FILE__, __LINE__))
	{
		error_log($sql,0);
	}

	$sql = "CREATE TABLE IF NOT EXISTS `$TABLELPITEMVIEW` (" .
		"id				bigint	unsigned	primary key auto_increment," . //unique ID
		"lp_item_id		int unsigned	not null," . //item ID (MySQL id)
		"lp_view_id		int unsigned 	not null," . // learning path view id (attempt)
		"view_count		int unsigned	not null default 0," . //how many times this item has been viewed in the current attempt (generally 0 or 1)
		"start_time		int unsigned	not null," . //when did the user open it?
		"total_time		int unsigned not null default 0," . //after how many seconds did he close it?
		"score			float unsigned not null default 0," . //score returned by SCORM or other techs
		"status			char(32) not null default 'not attempted'," . //status for this item (SCORM)
		"suspend_data	text null default ''," .
		"lesson_location text null default ''," .
		"core_exit		varchar(32) not null default 'none'," .
		"max_score		varchar(8) default ''" .
		")";
	if(!api_sql_query($sql, __FILE__, __LINE__))
	{
		error_log($sql,0);
	}
	$sql = "ALTER TABLE `$TABLELPITEMVIEW` ADD INDEX (lp_item_id) ";
	if(!api_sql_query($sql, __FILE__, __LINE__))
	{
		error_log($sql,0);
	}
	$sql = "ALTER TABLE `$TABLELPITEMVIEW` ADD INDEX (lp_view_id) ";
	if(!api_sql_query($sql, __FILE__, __LINE__))
	{
		error_log($sql,0);
	}

	$sql = "CREATE TABLE IF NOT EXISTS `$TABLELPIVINTERACTION`(" .
		"id				bigint	unsigned 		primary key auto_increment," .
		"order_id		smallint	unsigned	not null default 0,". //internal order (0->...) given by Dokeos
		"lp_iv_id		bigint	unsigned not null," . //identifier of the related sco_view
		"interaction_id	varchar(255) not null default ''," . //sco-specific, given by the sco
		"interaction_type	varchar(255) not null default ''," . //literal values, SCORM-specific (see p.63 of SCORM 1.2 RTE)
		"weighting			double not null default 0," .
		"completion_time	varchar(16) not null default ''," . //completion time for the interaction (timestamp in a day's time) - expected output format is scorm time
		"correct_responses	text not null default ''," . //actually a serialised array. See p.65 os SCORM 1.2 RTE)
		"student_response	text not null default ''," . //student response (format depends on type)
		"result			varchar(255) not null default ''," . //textual result
		"latency		varchar(16)	not null default ''" . //time necessary for completion of the interaction
		")";
	if(!api_sql_query($sql, __FILE__, __LINE__))
	{
		error_log($sql,0);
	}
	$sql = "ALTER TABLE `$TABLELPIVINTERACTION` ADD INDEX (lp_iv_id) ";
	if(!api_sql_query($sql, __FILE__, __LINE__))
	{
		error_log($sql,0);
	}
	
	$sql = "CREATE TABLE IF NOT EXISTS `$TABLELPIVOBJECTIVE`(" .
		"id				bigint	unsigned 		primary key auto_increment," .
		"lp_iv_id		bigint	unsigned not null," . //identifier of the related sco_view
		"order_id		smallint	unsigned	not null default 0,". //internal order (0->...) given by Dokeos
		"objective_id	varchar(255) not null default ''," . //sco-specific, given by the sco
		"score_raw		float unsigned not null default 0," . //score
		"score_max		float unsigned not null default 0," . //max score
		"score_min		float unsigned not null default 0," . //min score
		"status			char(32) not null default 'not attempted'" . //status, just as sco status
		")";
	if(!api_sql_query($sql, __FILE__, __LINE__))
	{
		error_log($sql,0);
	}
	$sql = "ALTER TABLE `$TABLELPIVOBJECTIVE` ADD INDEX (lp_iv_id) ";
	if(!api_sql_query($sql, __FILE__, __LINE__))
	{
		error_log($sql,0);
	}

	/*
	-----------------------------------------------------------
		Smart Blogs
	-----------------------------------------------------------
	*/
	$sql = "
		CREATE TABLE `" . $tbl_blogs . "` (
			blog_id smallint NOT NULL AUTO_INCREMENT ,
			blog_name varchar( 250 ) NOT NULL default '',
			blog_subtitle varchar( 250 ) default NULL ,
			date_creation datetime NOT NULL default '0000-00-00 00:00:00',
			visibility tinyint unsigned NOT NULL default 0,
			PRIMARY KEY ( blog_id )
		) ENGINE = MYISAM DEFAULT CHARSET = latin1 COMMENT = 'Table with blogs in this course';";

	if(!api_sql_query($sql, __FILE__, __LINE__))
	{
		error_log($sql, 0);
	}

	$sql = "
		CREATE TABLE `" . $tbl_blogs_comments . "` (
			comment_id int NOT NULL AUTO_INCREMENT ,
			title varchar( 250 ) NOT NULL default '',
			comment longtext NOT NULL ,
			author_id int NOT NULL default 0,
			date_creation datetime NOT NULL default '0000-00-00 00:00:00',
			blog_id mediumint NOT NULL default 0,
			post_id int NOT NULL default 0,
			task_id int default NULL ,
			parent_comment_id int NOT NULL default 0,
			PRIMARY KEY ( comment_id )
		) ENGINE = MYISAM DEFAULT CHARSET = latin1 COMMENT = 'Table with comments on posts in a blog';";

	if(!api_sql_query($sql, __FILE__, __LINE__))
	{
		error_log($sql, 0);
	}

	$sql = "
		CREATE TABLE `" . $tbl_blogs_posts . "` (
			post_id int NOT NULL AUTO_INCREMENT ,
			title varchar( 250 ) NOT NULL default '',
			full_text longtext NOT NULL ,
			date_creation datetime NOT NULL default '0000-00-00 00:00:00',
			blog_id mediumint NOT NULL default 0,
			author_id int NOT NULL default 0,
			PRIMARY KEY ( post_id )
		) ENGINE = MYISAM DEFAULT CHARSET = latin1 COMMENT = 'Table with posts / blog.';";

	if(!api_sql_query($sql, __FILE__, __LINE__))
	{
		error_log($sql, 0);
	}

	$sql = "
		CREATE TABLE `" . $tbl_blogs_rating . "` (
			rating_id int NOT NULL AUTO_INCREMENT ,
			blog_id int NOT NULL default 0,
			rating_type enum( 'post', 'comment' ) NOT NULL default 'post',
			item_id int NOT NULL default 0,
			user_id int NOT NULL default 0,
			rating mediumint NOT NULL default 0,
			PRIMARY KEY ( rating_id )
		) ENGINE = MYISAM DEFAULT CHARSET = latin1 COMMENT = 'Table with ratings for post/comments in a certain blog';";

	if(!api_sql_query($sql, __FILE__, __LINE__))
	{
		error_log($sql, 0);
	}

	$sql = "
		CREATE TABLE `" . $tbl_blogs_rel_user . "` (
			blog_id int NOT NULL default 0,
			user_id int NOT NULL default 0,
			PRIMARY KEY ( blog_id , user_id )
		) ENGINE = MYISAM DEFAULT CHARSET = latin1 COMMENT = 'Table representing users subscribed to a blog';";

	if(!api_sql_query($sql, __FILE__, __LINE__))
	{
		error_log($sql, 0);
	}

	$sql = "
		CREATE TABLE `" . $tbl_blogs_tasks . "` (
			task_id mediumint NOT NULL AUTO_INCREMENT ,
			blog_id mediumint NOT NULL default 0,
			title varchar( 250 ) NOT NULL default '',
			description text NOT NULL ,
			color varchar( 10 ) NOT NULL default '',
			system_task tinyint unsigned NOT NULL default 0,
			PRIMARY KEY ( task_id )
		) ENGINE = MYISAM DEFAULT CHARSET = latin1 COMMENT = 'Table with tasks for a blog';";

	if(!api_sql_query($sql, __FILE__, __LINE__))
	{
		error_log($sql, 0);
	}

	$sql = "
		CREATE TABLE `" . $tbl_blogs_tasks_rel_user . "` (
			blog_id mediumint NOT NULL default 0,
			user_id int NOT NULL default 0,
			task_id mediumint NOT NULL default 0,
			target_date date NOT NULL default '0000-00-00',
			PRIMARY KEY ( blog_id , user_id , task_id )
		) ENGINE = MYISAM DEFAULT CHARSET = latin1 COMMENT = 'Table with tasks assigned to a user in a blog';";

	if(!api_sql_query($sql, __FILE__, __LINE__))
	{
		error_log($sql, 0);
	}
	
	$sql ="CREATE TABLE  `" .$tbl_blogs_attachment."` (
		  id int unsigned NOT NULL auto_increment,
		  path varchar(255) NOT NULL COMMENT 'the real filename',
		  comment text,
		  size int NOT NULL default '0',
		  post_id int NOT NULL,
		  filename varchar(255) NOT NULL COMMENT 'the user s file name',
		  blog_id int NOT NULL,
		  comment_id int NOT NULL default '0',
  		PRIMARY KEY  (id)
		)";
	
	if(!api_sql_query($sql, __FILE__, __LINE__))
	{
		error_log($sql, 0);
	}
	
	
	

	$sql = "
		CREATE TABLE `" . $tbl_permission_group . "` (
			id int NOT NULL AUTO_INCREMENT ,
			group_id int NOT NULL default 0,
			tool varchar( 250 ) NOT NULL default '',
			action varchar( 250 ) NOT NULL default '',
			PRIMARY KEY (id)
		) ENGINE = MYISAM DEFAULT CHARSET = latin1;";

	if(!api_sql_query($sql, __FILE__, __LINE__))
	{
		error_log($sql, 0);
	}

	$sql = "
		CREATE TABLE `" . $tbl_permission_user . "` (
			id int NOT NULL AUTO_INCREMENT ,
			user_id int NOT NULL default 0,
			tool varchar( 250 ) NOT NULL default '',
			action varchar( 250 ) NOT NULL default '',
			PRIMARY KEY ( id )
		) ENGINE = MYISAM DEFAULT CHARSET = latin1;";

	if(!api_sql_query($sql, __FILE__, __LINE__))
	{
		error_log($sql, 0);
	}

	$sql = "
		CREATE TABLE `" . $tbl_permission_task . "` (
			id int NOT NULL AUTO_INCREMENT ,
			task_id int NOT NULL default 0,
			tool varchar( 250 ) NOT NULL default '',
			action varchar( 250 ) NOT NULL default '',
			PRIMARY KEY ( id )
		) ENGINE = MYISAM DEFAULT CHARSET = latin1;";

	if(!api_sql_query($sql, __FILE__, __LINE__))
	{
		error_log($sql, 0);
	}

	$sql = "
		CREATE TABLE `" . $tbl_role . "` (
			role_id int NOT NULL AUTO_INCREMENT ,
			role_name varchar( 250 ) NOT NULL default '',
			role_comment text,
			default_role tinyint default 0,
			PRIMARY KEY ( role_id )
		) ENGINE = MYISAM DEFAULT CHARSET = latin1;";

	if(!api_sql_query($sql, __FILE__, __LINE__))
	{
		error_log($sql, 0);
	}

	$sql = "
		CREATE TABLE `" . $tbl_role_group . "` (
			role_id int NOT NULL default 0,
			scope varchar( 20 ) NOT NULL default 'course',
			group_id int NOT NULL default 0
		) ENGINE = MYISAM DEFAULT CHARSET = latin1;";

	if(!api_sql_query($sql, __FILE__, __LINE__))
	{
		error_log($sql, 0);
	}

	$sql = "
		CREATE TABLE `" . $tbl_role_permissions . "` (
			role_id int NOT NULL default 0,
			tool varchar( 250 ) NOT NULL default '',
			action varchar( 50 ) NOT NULL default '',
			default_perm tinyint NOT NULL default 0
		) ENGINE = MYISAM DEFAULT CHARSET = latin1;";

	if(!api_sql_query($sql, __FILE__, __LINE__))
	{
		error_log($sql, 0);
	}

	$sql = "
		CREATE TABLE `" . $tbl_role_user . "` (
			role_id int NOT NULL default 0,
			scope varchar( 20 ) NOT NULL default 'course',
			user_id int NOT NULL default 0
		) ENGINE = MYISAM DEFAULT CHARSET = latin1;";

	if(!api_sql_query($sql, __FILE__, __LINE__))
	{
		error_log($sql, 0);
	}
	//end of Smartblogs

	/*
	-----------------------------------------------------------
		Course Config Settings
	-----------------------------------------------------------
	*/
	api_sql_query("
		CREATE TABLE `".$TABLESETTING . "` (
		id 			int unsigned NOT NULL auto_increment,
		variable 	varchar(255) NOT NULL default '',
		subkey		varchar(255) default NULL,
		type 		varchar(255) default NULL,
		category	varchar(255) default NULL,
		value		varchar(255) NOT NULL default '',
		title 		varchar(255) NOT NULL default '',
		comment 	varchar(255) default NULL,
		subkeytext 	varchar(255) default NULL,
		PRIMARY KEY (id)
 		)", __FILE__, __LINE__);

	/*
	-----------------------------------------------------------
		Survey
	-----------------------------------------------------------
	*/
	$sql = "CREATE TABLE `".$TABLESURVEY."` (
			  survey_id int unsigned NOT NULL auto_increment,
			  code varchar(20) default NULL,
			  title text default NULL,
			  subtitle text default NULL,
			  author varchar(20) default NULL,
			  lang varchar(20) default NULL,
			  avail_from date default NULL,
			  avail_till date default NULL,
			  is_shared char(1) default '1',
			  template varchar(20) default NULL,
			  intro text,
			  surveythanks text,
			  creation_date datetime NOT NULL default '0000-00-00 00:00:00',
			  invited int NOT NULL,
			  answered int NOT NULL,
			  invite_mail text NOT NULL,
			  reminder_mail text NOT NULL,
			  anonymous enum('0','1') NOT NULL default '0',
			  access_condition TEXT DEFAULT NULL,
			  PRIMARY KEY  (survey_id)
			)";

	$result = api_sql_query($sql,__FILE__,__LINE__) or die(mysql_error($sql));
	/*
	if(!api_sql_query($sql))
	{
		error_log($sql,0);
	}
	*/

	$sql = "CREATE TABLE `".$TABLESURVEYINVITATION."` (
			  survey_invitation_id int unsigned NOT NULL auto_increment,
			  survey_code varchar(20) NOT NULL,
			  user varchar(250) NOT NULL,
			  invitation_code varchar(250) NOT NULL,
			  invitation_date datetime NOT NULL,
			  reminder_date datetime NOT NULL,
			  answered int(2) NOT NULL default '0',
			  PRIMARY KEY  (survey_invitation_id)
			)";
	$result = api_sql_query($sql, __FILE__, __LINE__) or die(mysql_error($sql));
	/*
	if(!api_sql_query($sql))
	{
		error_log($sql,0);
	}
	*/

	$sql = "CREATE TABLE `".$TABLESURVEYQUESTION."` (
			  question_id int unsigned NOT NULL auto_increment,
			  survey_id int unsigned NOT NULL,
			  survey_question text NOT NULL,
			  survey_question_comment text NOT NULL,
			  type varchar(250) NOT NULL,
			  display varchar(10) NOT NULL,
			  sort int NOT NULL,
			  shared_question_id int(11),
			  max_value int(11),
			  PRIMARY KEY  (question_id)
			)";
	$result = api_sql_query($sql, __FILE__, __LINE__) or die(mysql_error($sql));
	/*
	if(!api_sql_query($sql))
	{
		error_log($sql,0);
	}
	*/

	$sql ="CREATE TABLE `".$TABLESURVEYQUESTIONOPTION."` (
	  question_option_id int unsigned NOT NULL auto_increment,
	  question_id int unsigned NOT NULL,
	  survey_id int unsigned NOT NULL,
	  option_text text NOT NULL,
	  sort int NOT NULL,
	  PRIMARY KEY  (question_option_id)
	)";
	$result = api_sql_query($sql, __FILE__, __LINE__) or die(mysql_error($sql));
	/*
	if(!api_sql_query($sql))
	{
		error_log($sql,0);
	}
	*/

	$sql = "CREATE TABLE `".$TABLESURVEYANSWER."` (
			  answer_id int unsigned NOT NULL auto_increment,
			  survey_id int unsigned NOT NULL,
			  question_id int unsigned NOT NULL,
			  option_id TEXT NOT NULL,
			  value int unsigned NOT NULL,
			  user varchar(250) NOT NULL,
			  PRIMARY KEY  (answer_id)
			)";
	$result = api_sql_query($sql, __FILE__, __LINE__) or die(mysql_error($sql));
	/*
	if(!api_sql_query($sql))
	{
		error_log($sql,0);
	}
	*/

	return 0;
}

function browse_folders($path, $files, $media)
{
	if($media=='images')
	{
		$code_path = api_get_path(SYS_CODE_PATH)."default_course_document/images/";
	}
	if($media=='audio')
	{
		$code_path = api_get_path(SYS_CODE_PATH)."default_course_document/audio/";
	}
	if($media=='flash')
	{
		$code_path = api_get_path(SYS_CODE_PATH)."default_course_document/flash/";
	}
	if($media=='video')
	{
		$code_path = api_get_path(SYS_CODE_PATH)."default_course_document/video/";
	}
	if(is_dir($path))
	{
		$handle = opendir($path);
		while (false !== ($file = readdir($handle))) 
		{
			if(is_dir($path.$file) && strpos($file,'.')!==0)
			{
				$files[]["dir"] = str_replace($code_path,"",$path.$file."/");
				$files = browse_folders($path.$file."/",$files,$media);
			}
			elseif(is_file($path.$file) && strpos($file,'.')!==0)
			{
		        $files[]["file"] = str_replace($code_path,"",$path.$file);
			}
		}
	}
	return $files;
}

function sort_pictures($files,$type)
{
	$pictures=array();
	foreach($files as $key => $value){
		if($value[$type]!=""){
			$pictures[][$type]=$value[$type];
		}
	}
	return $pictures;
}

/**
*	Fills the course repository with some
*	example content.
*	@version	 1.2
*/
function fill_course_repository($courseRepository)
{
	$old_umask = umask(0);
	$sys_course_path = api_get_path(SYS_COURSE_PATH);
	$web_code_path = api_get_path(WEB_CODE_PATH);

	/*doc_html = file(api_get_path(SYS_CODE_PATH).'document/example_document.html');

	$fp = fopen($sys_course_path.$courseRepository.'/document/example_document.html', 'w');

	foreach ($doc_html as $key => $enreg)
	{
		$enreg = str_replace('"stones.jpg"', '"'.$web_code_path.'img/stones.jpg"', $enreg);

		fputs($fp, $enreg);
	}
	fclose($fp);
    */
	$default_document_array=array();

	if(api_get_setting('example_material_course_creation')<>'false')
	{
		$img_code_path = api_get_path(SYS_CODE_PATH)."default_course_document/images/";
		$audio_code_path = api_get_path(SYS_CODE_PATH)."default_course_document/audio/";
		$flash_code_path = api_get_path(SYS_CODE_PATH)."default_course_document/flash/";
		$video_code_path = api_get_path(SYS_CODE_PATH)."default_course_document/video/";
		$course_documents_folder_images=$sys_course_path.$courseRepository.'/document/images/gallery/';
		$course_documents_folder_audio=$sys_course_path.$courseRepository.'/document/audio/';
		$course_documents_folder_flash=$sys_course_path.$courseRepository.'/document/flash/';
		$course_documents_folder_video=$sys_course_path.$courseRepository.'/document/video/';

		/*
		 * Images
		 */
	   	$files=array();

		$files=browse_folders($img_code_path,$files,'images');

		$pictures_array = sort_pictures($files,"dir");
		$pictures_array = array_merge($pictures_array,sort_pictures($files,"file"));

		$perm = api_get_setting('permissions_for_new_directories');
		$perm = octdec(!empty($perm)?$perm:'0770');
		$perm_file = api_get_setting('permissions_for_new_files');
		$perm_file = octdec(!empty($perm_file)?$perm_file:'0660');
		if(!is_dir($course_documents_folder_images))
		{
			mkdir($course_documents_folder_images,$perm);
		}

		$handle = opendir($img_code_path);
	
		foreach($pictures_array as $key => $value)
		{
			if($value["dir"]!="")
			{
				mkdir($course_documents_folder_images.$value["dir"],$perm);
			}
			if($value["file"]!="")
			{
				copy($img_code_path.$value["file"],$course_documents_folder_images.$value["file"]);
				chmod($course_documents_folder_images.$value["file"],$perm_file);
			}
		}
		
		//trainer thumbnails fix
		
		$path_thumb=mkdir($course_documents_folder_images.'trainer/.thumbs',$perm);				
		$handle = opendir($img_code_path.'trainer/.thumbs/');		
		
		while (false !== ($file = readdir($handle))) 
		{
			if (is_file($img_code_path.'trainer/.thumbs/'.$file))
			{
		        copy($img_code_path.'trainer/.thumbs/'.$file,$course_documents_folder_images.'trainer/.thumbs/'.$file);
				chmod($course_documents_folder_images.'trainer/.thumbs/'.$file,$perm_file);
			}		
		}		

		$default_document_array['images']=$pictures_array;

		/*
		 * Audio
		 */
		$files=array();

		$files=browse_folders($audio_code_path,$files,'audio');

		$audio_array = sort_pictures($files,"dir");
		$audio_array = array_merge($audio_array,sort_pictures($files,"file"));

		if(!is_dir($course_documents_folder_audio))
		{
			mkdir($course_documents_folder_audio,$perm);
		}

		$handle = opendir($audio_code_path);

		foreach($audio_array as $key => $value){

			if($value["dir"]!=""){
				mkdir($course_documents_folder_audio.$value["dir"],$perm);
			}
			if($value["file"]!=""){
				copy($audio_code_path.$value["file"],$course_documents_folder_audio.$value["file"]);
				chmod($course_documents_folder_audio.$value["file"],$perm_file);
			}

		}
		$default_document_array['audio']=$audio_array;

		/*
		 * Flash
		 */
		$files=array();

		$files=browse_folders($flash_code_path,$files,'flash');

		$flash_array = sort_pictures($files,"dir");
		$flash_array = array_merge($flash_array,sort_pictures($files,"file"));

		if(!is_dir($course_documents_folder_flash))
		{
			mkdir($course_documents_folder_flash,$perm);
		}

		$handle = opendir($flash_code_path);

		foreach($flash_array as $key => $value){

			if($value["dir"]!=""){
				mkdir($course_documents_folder_flash.$value["dir"],$perm);
			}
			if($value["file"]!=""){
				copy($flash_code_path.$value["file"],$course_documents_folder_flash.$value["file"]);
				chmod($course_documents_folder_flash.$value["file"],$perm_file);
			}

		}
		$default_document_array['flash']=$flash_array;

		/*
		 * Video
		 */
		$files=array();

		$files=browse_folders($video_code_path,$files,'video');

		$video_array = sort_pictures($files,"dir");
		$video_array = array_merge($video_array,sort_pictures($files,"file"));

		if(!is_dir($course_documents_folder_video))
		{
			mkdir($course_documents_folder_video,$perm);
		}

		$handle = opendir($video_code_path);

		foreach($video_array as $key => $value){

			if($value["dir"]!=""){
				mkdir($course_documents_folder_video.$value["dir"],$perm);
			}
			if($value["file"]!=""){
				copy($video_code_path.$value["file"],$course_documents_folder_video.$value["file"]);
				chmod($course_documents_folder_video.$value["file"],$perm_file);
			}

		}
		$default_document_array['video']=$video_array;

	}
	umask($old_umask);
	return $default_document_array;
}

/**
 * Function to convert a string from the Dokeos language files to a string ready
 * to insert into the database.
 * @author Bart Mollet (bart.mollet@hogent.be)
 * @param string $string The string to convert
 * @return string The string converted to insert into the database
 */
function lang2db($string)
{
	$string = str_replace("\\'", "'", $string);
	$string = Database::escape_string($string);
	return $string;
}
/**
*	Fills the course database with some required content and example content.
*	@version 1.2
*/
function fill_Db_course($courseDbName, $courseRepository, $language,$default_document_array)
{
	global $_configuration, $clarolineRepositoryWeb, $_user;

	$courseDbName = $_configuration['table_prefix'].$courseDbName.$_configuration['db_glue'];

	$tbl_course_homepage = $courseDbName . "tool";
	$TABLEINTROS = $courseDbName . "tool_intro";

	$TABLEGROUPS = $courseDbName . "group_info";
	$TABLEGROUPCATEGORIES = $courseDbName . "group_category";
	$TABLEGROUPUSER = $courseDbName . "group_rel_user";

	$TABLEITEMPROPERTY = $courseDbName . "item_property";

	$TABLETOOLCOURSEDESC = $courseDbName . "course_description";
	$TABLETOOLAGENDA = $courseDbName . "calendar_event";
	$TABLETOOLANNOUNCEMENTS = $courseDbName . "announcement";
	$TABLEADDEDRESOURCES = $courseDbName . "resource";
	$TABLETOOLWORKS = $courseDbName . "student_publication";
	$TABLETOOLWORKSUSER = $courseDbName . "stud_pub_rel_user";
	$TABLETOOLDOCUMENT = $courseDbName . "document";

	$TABLETOOLLINK = $courseDbName . "link";

	$TABLEQUIZ = $courseDbName . "quiz";
	$TABLEQUIZQUESTION = $courseDbName . "quiz_rel_question";
	$TABLEQUIZQUESTIONLIST = $courseDbName . "quiz_question";
	$TABLEQUIZANSWERSLIST = $courseDbName . "quiz_answer";
	$TABLESETTING = $courseDbName . "course_setting";

	$TABLEFORUMCATEGORIES = $courseDbName . "forum_category";
	$TABLEFORUMS = $courseDbName . "forum_forum";
	$TABLEFORUMTHREADS = $courseDbName . "forum_thread";
	$TABLEFORUMPOSTS = $courseDbName . "forum_post";


	$nom = $_user['lastName'];
	$prenom = $_user['firstName'];

	include (api_get_path(SYS_CODE_PATH) . "lang/english/create_course.inc.php");
	include (api_get_path(SYS_CODE_PATH) . "lang/".$language . "/create_course.inc.php");

	mysql_select_db("$courseDbName");

	/*
	==============================================================================
			All course tables are created.
			Next sections of the script:
			- insert links to all course tools so they can be accessed on the course homepage
			- fill the tool tables with examples
	==============================================================================
	*/

	$visible4all = 1;
	$visible4AdminOfCourse = 0;
	$visible4AdminOfClaroline = 2;

	/*
	-----------------------------------------------------------
		Course homepage tools
	-----------------------------------------------------------
	*/
	api_sql_query("INSERT INTO `" . $tbl_course_homepage . "` VALUES ('', '" . TOOL_COURSE_DESCRIPTION . "','course_description/','info.gif','".string2binary(api_get_setting('course_create_active_tools', 'course_description')) . "','0','squaregrey.gif','NO','_self','authoring')", __FILE__, __LINE__);
	api_sql_query("INSERT INTO `" . $tbl_course_homepage . "` VALUES ('', '" . TOOL_CALENDAR_EVENT . "','calendar/agenda.php','agenda.gif','".string2binary(api_get_setting('course_create_active_tools', 'agenda')) . "','0','squaregrey.gif','NO','_self','interaction')", __FILE__, __LINE__);
	api_sql_query("INSERT INTO `" . $tbl_course_homepage . "` VALUES ('', '" . TOOL_DOCUMENT . "','document/document.php','folder_document.gif','".string2binary(api_get_setting('course_create_active_tools', 'documents')) . "','0','squaregrey.gif','NO','_self','authoring')", __FILE__, __LINE__);
	api_sql_query("INSERT INTO `" . $tbl_course_homepage . "` VALUES ('', '" . TOOL_LEARNPATH . "','newscorm/lp_controller.php','scorm.gif','".string2binary(api_get_setting('course_create_active_tools', 'learning_path')) . "','0','squaregrey.gif','NO','_self','authoring')", __FILE__, __LINE__);
	api_sql_query("INSERT INTO `" . $tbl_course_homepage . "` VALUES ('', '" . TOOL_LINK . "','link/link.php','links.gif','".string2binary(api_get_setting('course_create_active_tools', 'links')) . "','0','squaregrey.gif','NO','_self','authoring')", __FILE__, __LINE__);
	api_sql_query("INSERT INTO `" . $tbl_course_homepage . "` VALUES ('', '" . TOOL_QUIZ . "','exercice/exercice.php','quiz.gif','".string2binary(api_get_setting('course_create_active_tools', 'quiz')) . "','0','squaregrey.gif','NO','_self','authoring')", __FILE__, __LINE__);
	api_sql_query("INSERT INTO `" . $tbl_course_homepage . "` VALUES ('', '" . TOOL_ANNOUNCEMENT . "','announcements/announcements.php','valves.gif','".string2binary(api_get_setting('course_create_active_tools', 'announcements')) . "','0','squaregrey.gif','NO','_self','authoring')", __FILE__, __LINE__);
	api_sql_query("INSERT INTO `" . $tbl_course_homepage . "` VALUES ('', '" . TOOL_FORUM . "','forum/index.php','forum.gif','".string2binary(api_get_setting('course_create_active_tools', 'forums')) . "','0','squaregrey.gif','NO','_self','interaction')", __FILE__, __LINE__);
	api_sql_query("INSERT INTO `" . $tbl_course_homepage . "` VALUES ('', '" . TOOL_DROPBOX . "','dropbox/index.php','dropbox.gif','".string2binary(api_get_setting('course_create_active_tools', 'dropbox')) . "','0','squaregrey.gif','NO','_self','interaction')", __FILE__, __LINE__);
	api_sql_query("INSERT INTO `" . $tbl_course_homepage . "` VALUES ('', '" . TOOL_USER . "','user/user.php','members.gif','".string2binary(api_get_setting('course_create_active_tools', 'users')) . "','0','squaregrey.gif','NO','_self','interaction')", __FILE__, __LINE__);
	api_sql_query("INSERT INTO `" . $tbl_course_homepage . "` VALUES ('', '" . TOOL_GROUP . "','group/group.php','group.gif','".string2binary(api_get_setting('course_create_active_tools', 'groups')) . "','0','squaregrey.gif','NO','_self','interaction')", __FILE__, __LINE__);
	api_sql_query("INSERT INTO `" . $tbl_course_homepage . "` VALUES ('', '" . TOOL_CHAT . "','chat/chat.php','chat.gif','".string2binary(api_get_setting('course_create_active_tools', 'chat')) . "','0','squaregrey.gif','NO','_self','interaction')", __FILE__, __LINE__);
	api_sql_query("INSERT INTO `" . $tbl_course_homepage . "` VALUES ('', '" . TOOL_STUDENTPUBLICATION . "','work/work.php','works.gif','".string2binary(api_get_setting('course_create_active_tools', 'student_publications')) . "','0','squaregrey.gif','NO','_self','interaction')", __FILE__, __LINE__);
	api_sql_query("INSERT INTO `" . $tbl_course_homepage . "` VALUES ('', '" . TOOL_SURVEY."','survey/survey_list.php','survey.gif','1','0','','NO','_self','interaction')");	


	if(api_get_setting('service_visio','active')=='true')
	{
		$mycheck = api_get_setting('service_visio','visio_host');
		if(!empty($mycheck))
		{
			api_sql_query("INSERT INTO `" . $tbl_course_homepage . "` VALUES ('', '" . TOOL_VISIO_CONFERENCE . "','conference/index.php?type=conference','visio_meeting.gif','1','0','squaregrey.gif','NO','_self','interaction')", __FILE__, __LINE__);
			api_sql_query("INSERT INTO `" . $tbl_course_homepage . "` VALUES ('', '" . TOOL_VISIO_CLASSROOM . "','conference/index.php?type=classroom','visio.gif','1','0','squaregrey.gif','NO','_self','authoring')", __FILE__, __LINE__);
		}
	}

	// Smartblogs (Kevin Van Den Haute :: kevin@develop-it.be)
	$sql = "INSERT INTO `" . $tbl_course_homepage . "` VALUES ('','" . TOOL_BLOGS . "','blog/blog_admin.php','blog_admin.gif','" . string2binary(api_get_setting('course_create_active_tools', 'blogs')) . "','1','squaregrey.gif','NO','_self','admin')";
	api_sql_query($sql, __FILE__, __LINE__);
	// end of Smartblogs

	/*
	-----------------------------------------------------------
		Course homepage tools for course admin only
	-----------------------------------------------------------
	*/
	api_sql_query("INSERT INTO `" . $tbl_course_homepage . "` VALUES ('', '" . TOOL_TRACKING . "','tracking/courseLog.php','statistics.gif','$visible4AdminOfCourse','1','', 'NO','_self','admin')", __FILE__, __LINE__);
	api_sql_query("INSERT INTO `" . $tbl_course_homepage . "` VALUES ('', '" . TOOL_COURSE_SETTING . "','course_info/infocours.php','reference.gif','$visible4AdminOfCourse','1','', 'NO','_self','admin')", __FILE__, __LINE__);
	api_sql_query("INSERT INTO `".$tbl_course_homepage."` VALUES ('','".TOOL_COURSE_MAINTENANCE."','course_info/maintenance.php','backup.gif','$visible4AdminOfCourse','1','','NO','_self', 'admin')", __FILE__, __LINE__);

	/*
	-----------------------------------------------------------
		course_setting table (courseinfo tool)
	-----------------------------------------------------------
	*/
	api_sql_query("INSERT INTO `".$TABLESETTING . "`(variable,value,category) VALUES ('email_alert_manager_on_new_doc',0,'work')", __FILE__, __LINE__);
	api_sql_query("INSERT INTO `".$TABLESETTING . "`(variable,value,category) VALUES ('email_alert_on_new_doc_dropbox',0,'dropbox')", __FILE__, __LINE__);
	api_sql_query("INSERT INTO `".$TABLESETTING . "`(variable,value,category) VALUES ('allow_user_edit_agenda',0,'agenda')", __FILE__, __LINE__);
	api_sql_query("INSERT INTO `".$TABLESETTING . "`(variable,value,category) VALUES ('allow_user_edit_announcement',0,'announcement')", __FILE__, __LINE__);
	api_sql_query("INSERT INTO `".$TABLESETTING . "`(variable,value,category) VALUES ('email_alert_manager_on_new_quiz',0,'quiz')", __FILE__, __LINE__);
	api_sql_query("INSERT INTO `".$TABLESETTING . "`(variable,value,category) VALUES ('allow_user_image_forum',1,'forum')", __FILE__, __LINE__);
	api_sql_query("INSERT INTO `".$TABLESETTING . "`(variable,value,category) VALUES ('course_theme','','theme')", __FILE__, __LINE__);
	api_sql_query("INSERT INTO `".$TABLESETTING . "`(variable,value,category) VALUES ('allow_learning_path_theme','1','theme')", __FILE__, __LINE__);
	/*
	-----------------------------------------------------------
		Course homepage tools for platform admin only
	-----------------------------------------------------------
	*/
	
	
	/*
	-----------------------------------------------------------
		Group tool
	-----------------------------------------------------------
	*/
	api_sql_query("INSERT INTO `".$TABLEGROUPCATEGORIES . "` ( id , title , description , max_student , self_reg_allowed , self_unreg_allowed , groups_per_user , display_order ) VALUES ('2', '".lang2db(get_lang('DefaultGroupCategory')) . "', '', '8', '0', '0', '0', '0');", __FILE__, __LINE__);
	

	/*
	-----------------------------------------------------------
		Example Material
	-----------------------------------------------------------
	*/
	global $language_interface;
	// Example material in the same language  
	$language_interface_tmp=$language_interface;
	$language_interface=$language;	
		
	if(api_get_setting('example_material_course_creation')<>'false')
	{

		/*
		-----------------------------------------------------------
			Documents
		-----------------------------------------------------------
		*/
		//api_sql_query("INSERT INTO `".$TABLETOOLDOCUMENT . "`(path,title,filetype,size) VALUES ('/example_document.html','example_document.html','file','3367')", __FILE__, __LINE__);
		//we need to add the document properties too!
		//$example_doc_id = Database :: get_last_insert_id();
		//api_sql_query("INSERT INTO `".$TABLEITEMPROPERTY . "` (tool,insert_user_id,insert_date,lastedit_date,ref,lastedit_type,lastedit_user_id,to_group_id,to_user_id,visibility) VALUES ('document',1,NOW(),NOW(),$example_doc_id,'DocumentAdded',1,0,NULL,1)", __FILE__, __LINE__);

		api_sql_query("INSERT INTO `".$TABLETOOLDOCUMENT . "`(path,title,filetype,size) VALUES ('/images','".get_lang('Images')."','folder','0')", __FILE__, __LINE__);
		$example_doc_id = Database :: get_last_insert_id();
		api_sql_query("INSERT INTO `".$TABLEITEMPROPERTY . "` (tool,insert_user_id,insert_date,lastedit_date,ref,lastedit_type,lastedit_user_id,to_group_id,to_user_id,visibility) VALUES ('document',1,NOW(),NOW(),$example_doc_id,'DocumentAdded',1,0,NULL,0)", __FILE__, __LINE__);

		api_sql_query("INSERT INTO `".$TABLETOOLDOCUMENT . "`(path,title,filetype,size) VALUES ('/images/gallery','".get_lang('DefaultCourseImages')."','folder','0')", __FILE__, __LINE__);
		$example_doc_id = Database :: get_last_insert_id();
		api_sql_query("INSERT INTO `".$TABLEITEMPROPERTY . "` (tool,insert_user_id,insert_date,lastedit_date,ref,lastedit_type,lastedit_user_id,to_group_id,to_user_id,visibility) VALUES ('document',1,NOW(),NOW(),$example_doc_id,'DocumentAdded',1,0,NULL,0)", __FILE__, __LINE__);

		api_sql_query("INSERT INTO `".$TABLETOOLDOCUMENT . "`(path,title,filetype,size) VALUES ('/audio','".get_lang('Audio')."','folder','0')", __FILE__, __LINE__);
		$example_doc_id = Database :: get_last_insert_id();
		api_sql_query("INSERT INTO `".$TABLEITEMPROPERTY . "` (tool,insert_user_id,insert_date,lastedit_date,ref,lastedit_type,lastedit_user_id,to_group_id,to_user_id,visibility) VALUES ('document',1,NOW(),NOW(),$example_doc_id,'DocumentAdded',1,0,NULL,0)", __FILE__, __LINE__);

		api_sql_query("INSERT INTO `".$TABLETOOLDOCUMENT . "`(path,title,filetype,size) VALUES ('/flash','".get_lang('Flash')."','folder','0')", __FILE__, __LINE__);
		$example_doc_id = Database :: get_last_insert_id();
		api_sql_query("INSERT INTO `".$TABLEITEMPROPERTY . "` (tool,insert_user_id,insert_date,lastedit_date,ref,lastedit_type,lastedit_user_id,to_group_id,to_user_id,visibility) VALUES ('document',1,NOW(),NOW(),$example_doc_id,'DocumentAdded',1,0,NULL,0)", __FILE__, __LINE__);

		api_sql_query("INSERT INTO `".$TABLETOOLDOCUMENT . "`(path,title,filetype,size) VALUES ('/video','".get_lang('Video')."','folder','0')", __FILE__, __LINE__);
		$example_doc_id = Database :: get_last_insert_id();
		api_sql_query("INSERT INTO `".$TABLEITEMPROPERTY . "` (tool,insert_user_id,insert_date,lastedit_date,ref,lastedit_type,lastedit_user_id,to_group_id,to_user_id,visibility) VALUES ('document',1,NOW(),NOW(),$example_doc_id,'DocumentAdded',1,0,NULL,0)", __FILE__, __LINE__);

		//FILL THE COURSE DOCUMENT WITH DEFAULT COURSE PICTURES
		$sys_course_path = api_get_path(SYS_COURSE_PATH);

		foreach($default_document_array as $media_type=>$array_media)
		{
			if($media_type=='images')
			{
				$path_documents='/images/gallery/';
				$course_documents_folder=$sys_course_path.$courseRepository.'/document/images/gallery/';
			}
			if($media_type=='audio')
			{
				$path_documents='/audio/';
				$course_documents_folder=$sys_course_path.$courseRepository.'/document/audio/';
			}
			if($media_type=='flash')
			{
				$path_documents='/flash/';
				$course_documents_folder=$sys_course_path.$courseRepository.'/document/flash/';
			}
			if($media_type=='video')
			{
				$path_documents='/video/';
				$course_documents_folder=$sys_course_path.$courseRepository.'/document/video/';
			}
			foreach($array_media as $key => $value)
			{
				if($value["dir"]!="")
				{
					$folder_path=substr($value["dir"],0,strlen($value["dir"])-1);
					$temp=explode("/",$folder_path);
					api_sql_query("INSERT INTO `".$TABLETOOLDOCUMENT . "`(path,title,filetype,size) VALUES ('$path_documents".$folder_path."','".$temp[count($temp)-1]."','folder','0')", __FILE__, __LINE__);
					$image_id = Database :: get_last_insert_id();
					api_sql_query("INSERT INTO `".$TABLEITEMPROPERTY . "` (tool,insert_user_id,insert_date,lastedit_date,ref,lastedit_type,lastedit_user_id,to_group_id,to_user_id,visibility) VALUES ('document',1,NOW(),NOW(),$image_id,'DocumentAdded',1,0,NULL,0)", __FILE__, __LINE__);
				}
				
				if($value["file"]!="")
				{
					$temp=explode("/",$value["file"]);
					$file_size=filesize($course_documents_folder.$value["file"]);
			        api_sql_query("INSERT INTO `".$TABLETOOLDOCUMENT . "`(path,title,filetype,size) VALUES ('$path_documents".$value["file"]."','".$temp[count($temp)-1]."','file','$file_size')", __FILE__, __LINE__);
					$image_id = Database :: get_last_insert_id();
					api_sql_query("INSERT INTO `".$TABLEITEMPROPERTY . "` (tool,insert_user_id,insert_date,lastedit_date,ref,lastedit_type,lastedit_user_id,to_group_id,to_user_id,visibility) VALUES ('document',1,NOW(),NOW(),$image_id,'DocumentAdded',1,0,NULL,0)", __FILE__, __LINE__);
				}
			}
		}

		/*
		-----------------------------------------------------------
			Agenda tool
		-----------------------------------------------------------
		*/
		api_sql_query("INSERT INTO `".$TABLETOOLAGENDA . "` VALUES ( '', '".lang2db(get_lang('AgendaCreationTitle')) . "', '".lang2db(get_lang('AgendaCreationContenu')) . "', now(), now(), NULL)", __FILE__, __LINE__);
		//we need to add the item properties too!
		$insert_id = Database :: get_last_insert_id();
		$sql = "INSERT INTO `".$TABLEITEMPROPERTY . "` (tool,insert_user_id,insert_date,lastedit_date,ref,lastedit_type,lastedit_user_id,to_group_id,to_user_id,visibility) VALUES ('" . TOOL_CALENDAR_EVENT . "',1,NOW(),NOW(),$insert_id,'AgendaAdded',1,0,NULL,1)";
		api_sql_query($sql, __FILE__, __LINE__);

		/*
		-----------------------------------------------------------
			Links tool
		-----------------------------------------------------------
		*/
		$add_google_link_sql = "	INSERT INTO `".$TABLETOOLLINK . "`
							VALUES ('1','http://www.google.com','Google','".lang2db(get_lang('Google')) . "','0','0','0')";
		api_sql_query($add_google_link_sql, __FILE__, __LINE__);
		//we need to add the item properties too!
		$insert_id = Database :: get_last_insert_id();
		$sql = "INSERT INTO `".$TABLEITEMPROPERTY . "` (tool,insert_user_id,insert_date,lastedit_date,ref,lastedit_type,lastedit_user_id,to_group_id,to_user_id,visibility) VALUES ('" . TOOL_LINK . "',1,NOW(),NOW(),$insert_id,'LinkAdded',1,0,NULL,1)";
		api_sql_query($sql, __FILE__, __LINE__);

		$add_wikipedia_link_sql = "	INSERT INTO `".$TABLETOOLLINK . "`
							VALUES ('', 'http://www.wikipedia.org','Wikipedia','".lang2db(get_lang('Wikipedia')) . "','0','1','0')";
		api_sql_query($add_wikipedia_link_sql, __FILE__, __LINE__);
		//we need to add the item properties too!
		$insert_id = Database :: get_last_insert_id();
		$sql = "INSERT INTO `".$TABLEITEMPROPERTY . "` (tool,insert_user_id,insert_date,lastedit_date,ref,lastedit_type,lastedit_user_id,to_group_id,to_user_id,visibility) VALUES ('" . TOOL_LINK . "',1,NOW(),NOW(),$insert_id,'LinkAdded',1,0,NULL,1)";
		api_sql_query($sql, __FILE__, __LINE__);

		/*
		-----------------------------------------------------------
			Annoucement tool
		-----------------------------------------------------------
		*/
		$sql = "INSERT INTO `".$TABLETOOLANNOUNCEMENTS . "` (title,content,end_date,display_order,email_sent) VALUES ('".lang2db(get_lang('AnnouncementExampleTitle')) . "', '".lang2db(get_lang('AnnouncementEx')) . "', NOW(), '1','0')";
		api_sql_query($sql, __FILE__, __LINE__);
		//we need to add the item properties too!
		$insert_id = Database :: get_last_insert_id();
		$sql = "INSERT INTO `".$TABLEITEMPROPERTY . "` (tool,insert_user_id,insert_date,lastedit_date,ref,lastedit_type,lastedit_user_id,to_group_id,to_user_id,visibility) VALUES ('" . TOOL_ANNOUNCEMENT . "',1,NOW(),NOW(),$insert_id,'AnnouncementAdded',1,0,NULL,1)";
		api_sql_query($sql, __FILE__, __LINE__);

		/*
		-----------------------------------------------------------
			Introduction text
		-----------------------------------------------------------
		*/
	
		$intro_text='<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td width="110" valign="middle" align="left"><img src="'.api_get_path(WEB_IMG_PATH).'mr_dokeos.png" alt="mr. Dokeos" title="mr. Dokeos" /></td><td valign="middle" align="left">'.lang2db(get_lang('IntroductionText')).'</td></tr></table>';
		api_sql_query("INSERT INTO `".$TABLEINTROS . "` VALUES ('" . TOOL_COURSE_HOMEPAGE . "','".$intro_text. "')", __FILE__, __LINE__);
		api_sql_query("INSERT INTO `".$TABLEINTROS . "` VALUES ('" . TOOL_STUDENTPUBLICATION . "','".lang2db(get_lang('IntroductionTwo')) . "')", __FILE__, __LINE__);
	
		
		/*
		-----------------------------------------------------------
			Exercise tool
		-----------------------------------------------------------
		*/
		api_sql_query("INSERT INTO `".$TABLEQUIZANSWERSLIST . "` VALUES ( '1', '1', '".lang2db(get_lang('Ridiculise')) . "', '0', '".lang2db(get_lang('NoPsychology')) . "', '-5', '1','','')",__FILE__,__LINE__);
		api_sql_query("INSERT INTO `".$TABLEQUIZANSWERSLIST . "` VALUES ( '2', '1', '".lang2db(get_lang('AdmitError')) . "', '0', '".lang2db(get_lang('NoSeduction')) . "', '-5', '2','','')", __FILE__, __LINE__);
		api_sql_query("INSERT INTO `".$TABLEQUIZANSWERSLIST . "` VALUES ( '3', '1', '".lang2db(get_lang('Force')) . "', '1', '".lang2db(get_lang('Indeed')) . "', '5', '3','','')", __FILE__, __LINE__);
		api_sql_query("INSERT INTO `".$TABLEQUIZANSWERSLIST . "` VALUES ( '4', '1', '".lang2db(get_lang('Contradiction')) . "', '1', '".lang2db(get_lang('NotFalse')) . "', '5', '4','','')", __FILE__, __LINE__);
		$html=addslashes('<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td width="110" valign="top" align="left"><img src="'.api_get_path(WEB_CODE_PATH).'default_course_document/images/mr_dokeos/thinking.jpg"></td><td valign="top" align="left">'.lang2db(get_lang('Antique')).'</td></tr></table>');
		api_sql_query('INSERT INTO `'.$TABLEQUIZ . '` (title, description, type, random, active, results_disabled ) VALUES ("'.lang2db(get_lang('ExerciceEx')) . '", "'.$html.'", "1", "0", "1", "0")', __FILE__, __LINE__);
		api_sql_query("INSERT INTO `".$TABLEQUIZQUESTIONLIST . "` VALUES ( '1', '".lang2db(get_lang('SocraticIrony')) . "', '".lang2db(get_lang('ManyAnswers')) . "', '10', '1', '2','')", __FILE__, __LINE__);
		api_sql_query("INSERT INTO `".$TABLEQUIZQUESTION . "` VALUES ( '1', '1')", __FILE__, __LINE__);


		/*
		-----------------------------------------------------------
			Forum tool
		-----------------------------------------------------------
		*/
		api_sql_query("INSERT INTO `$TABLEFORUMCATEGORIES` VALUES (1,'".lang2db(get_lang('ExampleForumCategory'))."', '', 1, 0)", __FILE__, __LINE__);
		$insert_id = Database :: get_last_insert_id();
		api_sql_query("INSERT INTO `".$TABLEITEMPROPERTY . "` (tool,insert_user_id,insert_date,lastedit_date,ref,lastedit_type,lastedit_user_id,to_group_id,to_user_id,visibility) VALUES ('forum_category',1,NOW(),NOW(),$insert_id,'ForumCategoryAdded',1,0,NULL,1)", __FILE__, __LINE__);

		api_sql_query("INSERT INTO `$TABLEFORUMS` (forum_title, forum_comment, forum_threads,forum_posts,forum_last_post,forum_category, allow_anonymous, allow_edit,allow_attachments, allow_new_threads,default_view,forum_of_group,forum_group_public_private, forum_order,locked,session_id ) VALUES ('".lang2db(get_lang('ExampleForum'))."', '', 0, 0, 0, 1, 0, 1, '0', 1, 'flat','0', 'public', 1, 0,0)", __FILE__, __LINE__);
		$insert_id = Database :: get_last_insert_id();
		api_sql_query("INSERT INTO `".$TABLEITEMPROPERTY . "` (tool,insert_user_id,insert_date,lastedit_date,ref,lastedit_type,lastedit_user_id,to_group_id,to_user_id,visibility) VALUES ('" . TOOL_FORUM . "',1,NOW(),NOW(),$insert_id,'ForumAdded',1,0,NULL,1)", __FILE__, __LINE__);

		api_sql_query("INSERT INTO `$TABLEFORUMTHREADS` VALUES (1, '".lang2db(get_lang('ExampleThread'))."', 1, 0, 1, '', 0, 1, NOW(), 0, 0)", __FILE__, __LINE__);
		$insert_id = Database :: get_last_insert_id();
		api_sql_query("INSERT INTO `".$TABLEITEMPROPERTY . "` (tool,insert_user_id,insert_date,lastedit_date,ref,lastedit_type,lastedit_user_id,to_group_id,to_user_id,visibility) VALUES ('forum_thread',1,NOW(),NOW(),$insert_id,'ForumThreadAdded',1,0,NULL,1)", __FILE__, __LINE__);

		api_sql_query("INSERT INTO `$TABLEFORUMPOSTS` VALUES (1, '".lang2db(get_lang('ExampleThread'))."', '".lang2db(get_lang('ExampleThreadContent'))."', 1, 1, 1, '', NOW(), 0, 0, 1)", __FILE__, __LINE__);

	}
		
	$language_interface=$language_interface_tmp;	

	return 0;
};

/**
 * function string2binary converts the string "true" or "false" to the boolean true false (0 or 1)
 * This is used for the Dokeos Config Settings as these store true or false as string
 * and the api_get_setting('course_create_active_tools') should be 0 or 1 (used for
 * the visibility of the tool)
 * @param string	$variable
 * @author Patrick Cool, patrick.cool@ugent.be
 */
function string2binary($variable)
{
	if($variable == "true")
	{
		return true;
	}
	if($variable == "false")
	{
		return false;
	}
}

/**
 * function register_course to create a record in the course table of the main database
 * @param string	$courseId
 * @param string	$courseCode
 * @param string	$courseRepository
 * @param string	$courseDbName
 * @param string	$tutor_name
 * @param string	$category
 * @param string	$title			complete name of course
 * @param string	$course_language		lang for this course
 * @param string	$uid				uid of owner
 * @param integer	Expiration date in unix time representation
 * @param array		Optional array of teachers' user ID
 */
function register_course($courseSysCode, $courseScreenCode, $courseRepository, $courseDbName, $titular, $category, $title, $course_language, $uidCreator, $expiration_date = "", $teachers=array())
{
	global $defaultVisibilityForANewCourse, $error_msg;
	$TABLECOURSE = Database :: get_main_table(TABLE_MAIN_COURSE);
	$TABLECOURSUSER = Database :: get_main_table(TABLE_MAIN_COURSE_USER);

	$TABLEANNOUNCEMENTS = Database :: get_course_table(TABLE_ANNOUNCEMENT,$courseDbName);

	$okForRegisterCourse = true;

	// Check if I have all
	if(empty ($courseSysCode))
	{
		$error_msg[] = "courseSysCode is missing";
		$okForRegisterCourse = false;
	}
	if(empty ($courseScreenCode))
	{
		$error_msg[] = "courseScreenCode is missing";
		$okForRegisterCourse = false;
	}
	if(empty ($courseDbName))
	{
		$error_msg[] = "courseDbName is missing";
		$okForRegisterCourse = false;
	}
	if(empty ($courseRepository))
	{
		$error_msg[] = "courseRepository is missing";
		$okForRegisterCourse = false;
	}
	if(empty ($titular))
	{
		$error_msg[] = "titular is missing";
		$okForRegisterCourse = false;
	}
	if(empty ($title))
	{
		$error_msg[] = "title is missing";
		$okForRegisterCourse = false;
	}
	if(empty ($course_language))
	{
		$error_msg[] = "language is missing";
		$okForRegisterCourse = false;
	}

	if(empty ($expiration_date))
	{
		$expiration_date = "NULL";
	}
	else
	{
		$expiration_date = "FROM_UNIXTIME(".$expiration_date . ")";
	}
	if($okForRegisterCourse)
	{
		$titular=addslashes($titular);
		// here we must add 2 fields
		$sql = "INSERT INTO ".$TABLECOURSE . " SET
					code = '".Database :: escape_string($courseSysCode) . "',
					db_name = '".Database :: escape_string($courseDbName) . "',
					directory = '".Database :: escape_string($courseRepository) . "',
					course_language = '".Database :: escape_string($course_language) . "',
					title = '".Database :: escape_string($title) . "',
					description = '".lang2db(get_lang('CourseDescription')) . "',
					category_code = '".Database :: escape_string($category) . "',
					visibility = '".$defaultVisibilityForANewCourse . "',
					show_score = '',
					disk_quota = '".api_get_setting('default_document_quotum') . "',
					creation_date = now(),
					expiration_date = ".$expiration_date . ",
					last_edit = now(),
					last_visit = NULL,
					tutor_name = '".Database :: escape_string($titular) . "',
					visual_code = '".Database :: escape_string($courseScreenCode) . "'";
		
		api_sql_query($sql, __FILE__, __LINE__);

		$sort = api_max_sort_value('0', api_get_user_id());
		
		require_once (api_get_path(LIBRARY_PATH).'course.lib.php');
		$i_course_sort = CourseManager :: userCourseSort($uidCreator,$courseSysCode);
		
		$sql = "INSERT INTO ".$TABLECOURSUSER . " SET
					course_code = '".addslashes($courseSysCode) . "',
					user_id = '".Database::escape_string($uidCreator) . "',
					status = '1',
					role = '".lang2db(get_lang('Professor')) . "',
					tutor_id='1',
					sort='". ($i_course_sort) . "',
					user_course_cat='0'";
		api_sql_query($sql, __FILE__, __LINE__);

		if(count($teachers)>0)
		{
			foreach($teachers as $key)
			{
				$sql = "INSERT INTO ".$TABLECOURSUSER . " SET
					course_code = '".Database::escape_string($courseSysCode) . "',
					user_id = '".Database::escape_string($key) . "',
					status = '1',
					role = '',
					tutor_id='0',
					sort='". ($sort +1) . "',
					user_course_cat='0'";
				api_sql_query($sql, __FILE__, __LINE__);
			}
		}

	}

	return 0;
}

/**
*	WARNING: this function always returns true.
*/
function checkArchive($pathToArchive)
{
	return TRUE;
}

function readPropertiesInArchive($archive, $isCompressed = TRUE)
{
	include (api_get_path(LIBRARY_PATH) . "pclzip/pclzip.lib.php");
	printVar(dirname($archive), "Zip : ");
	$uid = api_get_user_id();
	/*
	string tempnam ( string dir, string prefix)
	tempnam() cree un fichier temporaire unique dans le dossier dir. Si le dossier n'existe pas, tempnam() va generer un nom de fichier dans le dossier temporaire du systeme.
	Avant PHP 4.0.6, le comportement de tempnam() dependait de l'OS sous-jacent. Sous Windows, la variable d'environnement TMP remplace le parametre dir; sous Linux, la variable d'environnement TMPDIR a la priorite tandis que pour les OS en systeme V R4, le parametre dir sera toujours utilise si le dossier qu'il represente existe. Consultez votre documentation pour plus de details.
	tempnam() retourne le nom du fichier temporaire, ou la chaine NULL en cas d'echec.
	*/
	$zipFile = new pclZip($archive);
	$tmpDirName = dirname($archive) . "/tmp".$uid.uniqid($uid);
	if(mkpath($tmpDirName))
		$unzippingSate = $zipFile->extract($tmpDirName);
	else
		die("mkpath failed");
	$pathToArchiveIni = dirname($tmpDirName) . "/archive.ini";
	//	echo $pathToArchiveIni;
	$courseProperties = parse_ini_file($pathToArchiveIni);
	rmdir($tmpDirName);
	return $courseProperties;
}
?>