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
*	Exercise class: This class allows to instantiate an object of type Exercise
*	@package dokeos.exercise
* 	@author Olivier Brouckaert
* 	@version $Id: exercise.class.php 14786 2008-04-08 14:11:46Z elixir_inter $
*/


if(!class_exists('Exercise')):

class Exercise
{
	var $id;
	var $exercise;
	var $description;
	var $sound;
	var $type;
	var $random;
	var $active;
	var $timeLimit;

	var $questionList;  // array with the list of this exercise's questions

	/**
	 * constructor of the class
	 *
	 * @author - Olivier Brouckaert
	 */
	function Exercise()
	{
		$this->id=0;
		$this->exercise='';
		$this->description='';
		$this->sound='';
		$this->type=1;
		$this->random=0;
		$this->active=1;
		$this->questionList=array();
		$this->timeLimit = 0;
	}

	/**
	 * reads exercise informations from the data base
	 *
	 * @author - Olivier Brouckaert
	 * @param - integer $id - exercise ID
	 * @return - boolean - true if exercise exists, otherwise false
	 */
	function read($id)
	{
		global $_course;

	    $TBL_EXERCICE_QUESTION  = Database::get_course_table(TABLE_QUIZ_TEST_QUESTION);
    	$TBL_EXERCICES          = Database::get_course_table(TABLE_QUIZ_TEST);
	    $TBL_QUESTIONS          = Database::get_course_table(TABLE_QUIZ_QUESTION);
    	#$TBL_REPONSES           = Database::get_course_table(TABLE_QUIZ_ANSWER);

		$sql="SELECT title,description,sound,type,random,active, results_disabled FROM $TBL_EXERCICES WHERE id='$id'";
		$result=api_sql_query($sql,__FILE__,__LINE__);

		// if the exercise has been found
		if($object=Database::fetch_object($result))
		{
			$this->id=$id;
			$this->exercise=$object->title;
			$this->description=$object->description;
			$this->sound=$object->sound;
			$this->type=$object->type;
			$this->random=$object->random;
			$this->active=$object->active;
			$this->results_disabled =$object->results_disabled;

			$sql="SELECT question_id,position FROM $TBL_EXERCICE_QUESTION,$TBL_QUESTIONS WHERE question_id=id AND exercice_id='$id' ORDER BY position";
			$result=api_sql_query($sql,__FILE__,__LINE__);

			// fills the array with the question ID for this exercise
			// the key of the array is the question position
			while($object=Database::fetch_object($result))
			{
				// makes sure that the question position is unique
				while(isset($this->questionList[$object->position]))
				{
					$object->position++;
				}

				$this->questionList[$object->position]=$object->question_id;
			}
			return true;
		}

		// exercise not found
		return false;
	}

	/**
	 * returns the exercise ID
	 *
	 * @author - Olivier Brouckaert
	 * @return - integer - exercise ID
	 */
	function selectId()
	{
		return $this->id;
	}

	/**
	 * returns the exercise title
	 *
	 * @author - Olivier Brouckaert
	 * @return - string - exercise title
	 */
	function selectTitle()
	{
		return $this->exercise;
	}
	/**
	 * returns the time limit
	 */
	function selectTimeLimit()
	{
		return $this->timeLimit;
	}

	/**
	 * returns the exercise description
	 *
	 * @author - Olivier Brouckaert
	 * @return - string - exercise description
	 */
	function selectDescription()
	{
		return $this->description;
	}

	/**
	 * returns the exercise sound file
	 *
	 * @author - Olivier Brouckaert
	 * @return - string - exercise description
	 */
	function selectSound()
	{
		return $this->sound;
	}

	/**
	 * returns the exercise type
	 *
	 * @author - Olivier Brouckaert
	 * @return - integer - exercise type
	 */
	function selectType()
	{
		return $this->type;
	}

	/**
	 * tells if questions are selected randomly, and if so returns the draws
	 *
	 * @author - Olivier Brouckaert
	 * @return - integer - 0 if not random, otherwise the draws
	 */
	function isRandom()
	{
		return $this->random;
	}
	/**
	 * Same as isRandom() but has a name applied to values different than 0 or 1
	 */
	function getShuffle()
	{
		return $this->random;
	}

	/**
	 * returns the exercise status (1 = enabled ; 0 = disabled)
	 *
	 * @author - Olivier Brouckaert
	 * @return - boolean - true if enabled, otherwise false
	 */
	function selectStatus()
	{
		return $this->active;
	}

	/**
	 * returns the array with the question ID list
	 *
	 * @author - Olivier Brouckaert
	 * @return - array - question ID list
	 */
	function selectQuestionList()
	{
		return $this->questionList;
	}

	/**
	 * returns the number of questions in this exercise
	 *
	 * @author - Olivier Brouckaert
	 * @return - integer - number of questions
	 */
	function selectNbrQuestions()
	{
		return sizeof($this->questionList);
	}

	/**
     * selects questions randomly in the question list
	 *
	 * @author - Olivier Brouckaert
	 * @return - array - if the exercise is not set to take questions randomly, returns the question list
	 *					 without randomizing, otherwise, returns the list with questions selected randomly
     */
	function selectRandomList()
	{
		// if the exercise is not a random exercise, or if there are not at least 2 questions
		if(!$this->random || $this->selectNbrQuestions() < 2)
		{
			return $this->questionList;
		}

		// takes all questions
		if($this->random == -1 || $this->random >= $this->selectNbrQuestions())
		{
			$draws=$this->selectNbrQuestions();
		}
		else
		{
			$draws=$this->random;
		}

		srand((double)microtime()*1000000);

		$randQuestionList=array();
		$alreadyChosed=array();

		// loop for the number of draws
		for($i=0;$i < $draws;$i++)
		{
			// selects a question randomly
			do
			{
				$rand=rand(0,$this->selectNbrQuestions()-1);
			}
			// if the question has already been selected, continues in the loop
			while(in_array($rand,$alreadyChosed));

			$alreadyChosed[]=$rand;

			$j=0;

			foreach($this->questionList as $key=>$val)
			{
				// if we have found the question chosed above
				if($j == $rand)
				{
					$randQuestionList[$key]=$val;
					break;
				}

				$j++;
			}
		}

		return $randQuestionList;
	}

	/**
	 * returns 'true' if the question ID is in the question list
	 *
	 * @author - Olivier Brouckaert
	 * @param - integer $questionId - question ID
	 * @return - boolean - true if in the list, otherwise false
	 */
	function isInList($questionId)
	{
		return in_array($questionId,$this->questionList);
	}

	/**
	 * changes the exercise title
	 *
	 * @author - Olivier Brouckaert
	 * @param - string $title - exercise title
	 */
	function updateTitle($title)
	{
		$this->exercise=$title;
	}

	/**
	 * changes the exercise description
	 *
	 * @author - Olivier Brouckaert
	 * @param - string $description - exercise description
	 */
	function updateDescription($description)
	{
		$this->description=$description;
	}

	/**
	 * changes the exercise sound file
	 *
	 * @author - Olivier Brouckaert
	 * @param - string $sound - exercise sound file
	 * @param - string $delete - ask to delete the file
	 */
	function updateSound($sound,$delete)
	{
		global $audioPath, $documentPath,$_course, $_user;
        $TBL_DOCUMENT = Database::get_course_table(TABLE_DOCUMENT);
        $TBL_ITEM_PROPERTY = Database::get_course_table(TABLE_ITEM_PROPERTY);

		if($sound['size'] && (strstr($sound['type'],'audio') || strstr($sound['type'],'video')))
		{
			$this->sound=$sound['name'];

			if(@move_uploaded_file($sound['tmp_name'],$audioPath.'/'.$this->sound))
			{
				$query="SELECT 1 FROM $TBL_DOCUMENT "
            ." WHERE path='".str_replace($documentPath,'',$audioPath).'/'.$this->sound."'";
				$result=api_sql_query($query,__FILE__,__LINE__);

				if(!mysql_num_rows($result))
				{
        /*$query="INSERT INTO $TBL_DOCUMENT(path,filetype) VALUES "
            ." ('".str_replace($documentPath,'',$audioPath).'/'.$this->sound."','file')";
        api_sql_query($query,__FILE__,__LINE__);*/
        $id = add_document($_course,str_replace($documentPath,'',$audioPath).'/'.$this->sound,'file',$sound['size'],$sound['name']);

        //$id = Database::get_last_insert_id();
        //$time = time();
        //$time = date("Y-m-d H:i:s", $time);
        // insert into the item_property table, using default visibility of "visible"
        /*$query = "INSERT INTO $TBL_ITEM_PROPERTY "
                ."(tool, ref, insert_user_id,to_group_id, insert_date, lastedit_date, lastedit_type) "
                ." VALUES "
                ."('".TOOL_DOCUMENT."', $id, $_user['user_id'], 0, '$time', '$time', 'DocumentAdded' )";
        api_sql_query($query,__FILE__,__LINE__);*/
        api_item_property_update($_course, TOOL_DOCUMENT, $id, 'DocumentAdded',$_user['user_id']);
        item_property_update_on_folder($_course,str_replace($documentPath,'',$audioPath),$_user['user_id']);
				}
			}
		}
		elseif($delete && is_file($audioPath.'/'.$this->sound))
		{
			$this->sound='';
		}
	}

	/**
	 * changes the exercise type
	 *
	 * @author - Olivier Brouckaert
	 * @param - integer $type - exercise type
	 */
	function updateType($type)
	{
		$this->type=$type;
	}

	/**
	 * sets to 0 if questions are not selected randomly
	 * if questions are selected randomly, sets the draws
	 *
	 * @author - Olivier Brouckaert
	 * @param - integer $random - 0 if not random, otherwise the draws
	 */
	function setRandom($random)
	{
		$this->random=$random;
	}

	/**
	 * enables the exercise
	 *
	 * @author - Olivier Brouckaert
	 */
	function enable()
	{
		$this->active=1;
	}

	/**
	 * disables the exercise
	 *
	 * @author - Olivier Brouckaert
	 */
	function disable()
	{
		$this->active=0;
	}
	
	function disable_results()
	{
		$this->results_disabled = true;
	}
	
	function enable_results()
	{
		$this->results_disabled = false;
	}

	/**
	 * updates the exercise in the data base
	 *
	 * @author - Olivier Brouckaert
	 */
	function save()
	{
		$TBL_EXERCICES = Database::get_course_table(TABLE_QUIZ_TEST);
        $TBL_QUESTIONS = Database::get_course_table(TABLE_QUIZ_QUESTION);

		$id=$this->id;
		$exercise=addslashes($this->exercise);
		$description=addslashes($this->description);
		$sound=addslashes($this->sound);
		$type=$this->type;
		$random=$this->random;
		$active=$this->active;
		$results_disabled = intval($this->results_disabled);

		// exercise already exists
		if($id)
		{
			$sql="UPDATE $TBL_EXERCICES SET title='$exercise',description='$description',sound='$sound',type='$type',random='$random',active='$active',results_disabled='$results_disabled' WHERE id='$id'";
			api_sql_query($sql,__FILE__,__LINE__);
		}
		// creates a new exercise
		else
		{
			$sql="INSERT INTO $TBL_EXERCICES(title,description,sound,type,random,active, results_disabled) VALUES('$exercise','$description','$sound','$type','$random','$active',$results_disabled)";
			api_sql_query($sql,__FILE__,__LINE__);

			$this->id=mysql_insert_id();
		}

		// updates the question position
		foreach($this->questionList as $position=>$questionId)
		{
			$sql="UPDATE $TBL_QUESTIONS SET position='$position' WHERE id='$questionId'";
			api_sql_query($sql,__FILE__,__LINE__);
		}
	}

	/**
	 * moves a question up in the list
	 *
	 * @author - Olivier Brouckaert
	 * @param - integer $id - question ID to move up
	 */
	function moveUp($id)
	{
		foreach($this->questionList as $position=>$questionId)
		{
			// if question ID found
			if($questionId == $id)
			{
				// position of question in the array
				$pos1=$position;

				prev($this->questionList);

				// position of previous question in the array
				$pos2=key($this->questionList);

				// error, can't move question
				if(!$pos2)
				{
					return;
				}

				$id2=$this->questionList[$pos2];

				// exits foreach()
				break;
			}

			// goes to next question
			next($this->questionList);
		}

		// permutes questions in the array
		$temp=$this->questionList[$pos2];
		$this->questionList[$pos2]=$this->questionList[$pos1];
		$this->questionList[$pos1]=$temp;
	}

	/**
	 * moves a question down in the list
	 *
	 * @author - Olivier Brouckaert
	 * @param - integer $id - question ID to move down
	 */
	function moveDown($id)
	{
		foreach($this->questionList as $position=>$questionId)
		{
			// if question ID found
			if($questionId == $id)
			{
				// position of question in the array
				$pos1=$position;

				next($this->questionList);

				// position of next question in the array
				$pos2=key($this->questionList);

				// error, can't move question
				if(!$pos2)
				{
					return;
				}

				$id2=$this->questionList[$pos2];

				// exits foreach()
				break;
			}

			// goes to next question
			next($this->questionList);
		}

		// permutes questions in the array
		$temp=$this->questionList[$pos2];
		$this->questionList[$pos2]=$this->questionList[$pos1];
		$this->questionList[$pos1]=$temp;
	}

	/**
	 * adds a question into the question list
	 *
	 * @author - Olivier Brouckaert
	 * @param - integer $questionId - question ID
	 * @return - boolean - true if the question has been added, otherwise false
	 */
	function addToList($questionId)
	{
		// checks if the question ID is not in the list
		if(!$this->isInList($questionId))
		{
			// selects the max position
			if(!$this->selectNbrQuestions())
			{
				$pos=1;
			}
			else
			{
				$pos=max(array_keys($this->questionList))+1;
			}

			$this->questionList[$pos]=$questionId;

			return true;
		}

		return false;
	}

	/**
	 * removes a question from the question list
	 *
	 * @author - Olivier Brouckaert
	 * @param - integer $questionId - question ID
	 * @return - boolean - true if the question has been removed, otherwise false
	 */
	function removeFromList($questionId)
	{
		// searches the position of the question ID in the list
		$pos=array_search($questionId,$this->questionList);

		// question not found
		if($pos === false)
		{
			return false;
		}
		else
		{
			// deletes the position from the array containing the wanted question ID
			unset($this->questionList[$pos]);

			return true;
		}
	}

	/**
	 * deletes the exercise from the database
	 * Notice : leaves the question in the data base
	 *
	 * @author - Olivier Brouckaert
	 */
	function delete(){
		$TBL_EXERCICES = Database::get_course_table(TABLE_QUIZ_TEST);
		$sql="UPDATE $TBL_EXERCICES SET active='-1' WHERE id='".$this->id."'";
		api_sql_query($sql);
	}

	/**
	 * Creates the form to create / edit an exercise
	 * @param FormValidator $form the formvalidator instance (by reference)
	 */
	function createForm ($form)
	{

		// title
		$form -> addElement('text', 'exerciseTitle', get_lang('ExerciseName').' : ','class="input_titles"');

		// fck editor
		global $fck_attribute;
		$fck_attribute = array();
		$fck_attribute['Height'] = '250';
		$fck_attribute['Width'] = '100%';
		$fck_attribute['ToolbarSet'] = 'NewTest';
		$form -> addElement ('html_editor', 'exerciseDescription', get_lang('ExerciseDescription').' : ');

		// type
		$radios = array();
		$radios[] = FormValidator :: createElement ('radio', 'exerciseType', null, get_lang('SimpleExercise'),'1');
		$radios[] = FormValidator :: createElement ('radio', 'exerciseType', null, get_lang('SequentialExercise'),'2');
		$form -> addGroup($radios, null, get_lang('ExerciseType').' : ', '<br />');

		// random
		/*$random = array();
		$random[] = FormValidator :: createElement ('text', 'randomQuestions', null,null,'0');
		$form -> addGroup($random,null,get_lang('RandomQuestions').' : ','<br />');*/

		// submit
		$form -> addElement('submit', 'submitExercise', get_lang('Ok'));

		// rules
		$form -> addRule ('exerciseTitle', get_lang('GiveExerciseName'), 'required');

		// defaults
		$defaults = array();
		if($this -> id > 0)
		{
			$defaults['exerciseType'] = $this -> selectType();
			$defaults['exerciseTitle'] = $this -> selectTitle();
			$defaults['exerciseDescription'] = $this -> selectDescription();
			//$defaults['randomQuestions'] = $this -> isRandom();
		}
		else{
			$defaults['exerciseType'] = 1;
			//$defaults['randomQuestions'] = 0;
			$defaults['exerciseDescription'] = '';
		}

		$form -> setDefaults($defaults);


	}


	/**
	 * function which process the creation of exercises
	 * @param FormValidator $form the formvalidator instance
	 */
	function processCreation($form)
	{

		$this -> updateTitle($form -> getSubmitValue('exerciseTitle'));
		$this -> updateDescription($form -> getSubmitValue('exerciseDescription'));
		$this -> updateType($form -> getSubmitValue('exerciseType'));
		$this -> setRandom($form -> getSubmitValue('randomQuestions'));
		$this -> save();

	}
}

endif;
?>