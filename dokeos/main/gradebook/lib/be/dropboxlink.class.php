<?php

/**
 * Gradebook link to dropbox item
 * @author Bert Stepp�
 * @package dokeos.gradebook
 */
class DropboxLink extends EvalLink
{

// INTERNAL VARIABLES

    private $dropbox_table = null;

// CONSTRUCTORS

    function DropboxLink()
    {
    	$this->set_type(LINK_DROPBOX);
    }


// FUNCTIONS IMPLEMENTING ABSTRACTLINK

	public function get_view_url ($stud_id)
	{
		// find a file uploaded by the given student,
		// with the same title as the evaluation name

    	$eval = $this->get_evaluation();

		$sql = 'SELECT filename'
				.' FROM '.$this->get_dropbox_table()
				.' WHERE uploader_id = '.$stud_id
				." AND title = '".mysql_real_escape_string($eval->get_name())."'";

		$result = api_sql_query($sql, __FILE__, __LINE__);
		if ($fileurl = mysql_fetch_row($result))
		{
	    	$course_info = Database :: get_course_info($this->get_course_code());

			$url = api_get_path(WEB_PATH)
					.'main/gradebook/open_document.php?file='
					.$course_info['directory']
					.'/'
					.$fileurl[0];

			return $url;
		}
		else
			return null;
		
	}

    public function get_type_name()
    {
    	return get_lang('DokeosDropbox');
    }
    
	public function is_allowed_to_change_name()
	{
		return false;
	}

    
// INTERNAL FUNCTIONS
    
    /**
     * Lazy load function to get the dropbox database table
     */
    private function get_dropbox_table ()
    {
    	if (!isset($this->dropbox_table))
    	{
	    	$course_info = Database :: get_course_info($this->get_course_code());
			$database_name = $course_info['db_name'];
			$this->dropbox_table = Database :: get_course_table(TABLE_DROPBOX_FILE, $database_name);
    	}
   		return $this->dropbox_table;
    }


	
}
?>