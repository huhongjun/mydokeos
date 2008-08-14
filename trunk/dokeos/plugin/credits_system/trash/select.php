<?php

echo '<br /><span style="font-size:18px">Need help with Quickform or Flexy? - email me and I\'ll see if I can help
<br /><a href="http://www.idig.za.net/contact/">please help</a>.</span>';

/* This script shows how to use the select element in quickform*/
	
//loads the class definition for Quickform
include_once('../../main/inc/global.inc.php');
require_once(api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php');

//instantiates the an HTML_Quickform object
$form = new formValidator('Php_form','post');	

//load array with menu items and values	
$colors=array('red'=>'Red','black'=>'Black','white'=>'White');

//add select item to menu
$form->addElement('select','color','label',$colors,'multiple');

//retrieve the reference to the element 'color'
$element = & $form->getElement('color');

//set variable to the selected values
$colors = $element->getSelected();

//toggle to allow multiple selections
$element->setMultiple(true);
//$element->setMultiple(false);

//checks to see if set to allow multiple selections
$multiple_set = $element->getMultiple();

//set the default values for the select element
$selected_items = $element->setSelected('white,black');

//submit button
$form->addElement('submit',NULL,'Submit');

//display form
$form->display();

//displays whether or not the multiple option is set or not
echo "multiple set = ".$multiple_set;
if ($multiple_set != 1) {
 	echo "<br /> You can only choose one item from the menu";
	
} else {
	echo "<br /> You can choose more than one item from the menu";	
}


//displays the selected items after submitting the form   
if (isset ($_POST['color'])) {
	foreach ($_POST as $k=>$v) {
	 foreach ($v as $v2)  
		echo '<br />'.$k.' = '.$v2;
		$color_array[] = $v2;
  	} 
}

//displays the array $colors values
if (is_array($colors))
	{
	echo "<br />its an array";
	foreach ($colors as $k) {
		echo "<br /> color in array = ".$k;
	}
	} 
else 
	{
	 echo "<br />its not an array";
	}
?>

<br /><h3 style="color:#008000">Provided by <a href="http://www.idig.za.net">Independent Digital</a> for your convenience</h3>