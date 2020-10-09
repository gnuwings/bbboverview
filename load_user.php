<?php

require_once(dirname(__FILE__).'/../../config.php');
require_once('locallib.php');
global $DB;
$courseid = optional_param('value', 0, PARAM_INT);
if($courseid > 0 ){
	$enrolledusers = get_enrolled_users(\context_course::instance($courseid));

	if($enrolledusers){
		 foreach ($enrolledusers as $enroluser) {
			$users[$enroluser->id] = $enroluser->lastname.' '.$enroluser->firstname;
		 }
		
	}
	natcasesort($users);
	$alluser = array(0 => get_string('allusers','search'));
	$users = $alluser+$users;
}			 	
	echo json_encode($users);
	
	
?>
