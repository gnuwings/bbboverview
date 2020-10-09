<?php
require_once($CFG->dirroot . '/group/lib.php');

function allcourses(){
	global $DB,$USER;
	$courses = array('0'=>"Select");
	$sql = "SELECT distinct(c.id),fullname FROM {course} c";            		
	$sql .=" where c.id > 1";
	$courseobj = $DB->get_records_sql($sql);
	
	if($courseobj){
		foreach($courseobj as $course){
              $courses[$course->id] = $course->fullname;
            
		}
	}
	
	return $courses;
		
}
  
    
  function get_activities_list($course) {
	  global $DB;
        $activities = array();
		$activity =array('bigbluebuttonbn');
        $modinfo = get_fast_modinfo($course);
        
        if (!empty($modinfo->cms)) {
            foreach ($modinfo->cms as $cm) {
				if(in_array($cm->modname,$activity)){
					//print_r($cm);
					if (!$cm->uservisible || !$cm->has_view()) {
						continue;
					}
					
						$modname = strip_tags($cm->get_formatted_name());
						if (core_text::strlen($modname) > 55) {
							$modname = core_text::substr($modname, 0, 50)."...";
						}
						if (!$cm->visible) {
							$modname = "(".$modname.")";
						}
						$moddata = new stdClass();
						$moddata->id = $cm->instance;
						$moddata->name =  $modname;
						$activities[$cm->id] = $moddata;

				}
            }
           
        }
        return $activities;
    }

function get_bbb_noofattendees($blogid,$id,$createrid) {
		global $DB;
		
	$creatorjoined = $DB->get_record('bigbluebuttonbn_logs',array('id' => $blogid));
	$sql = "SELECT COUNT(id)
			FROM {bigbluebuttonbn_logs} 
			WHERE bigbluebuttonbnid = ? AND log = ? AND id > $blogid";
			
	// Find out next session start of the same meeting
	$sql1 = "SELECT * FROM {bigbluebuttonbn_logs} 
			WHERE meetingid = '".$creatorjoined->meetingid."' AND id > $blogid 
			AND log='Create' ORDER BY timecreated ASC LIMIT 0,1 ";
	$nextsession = $DB->get_record_sql($sql1);
	if($nextsession){
		$sql .= " AND id < ".$nextsession->id;
	}

    $noofattendies = $DB->count_records_sql($sql, array($id, 'Join'));
	return $noofattendies-1;
	
	
}

function get_bbb_length($blogid,$id,$createrid) {
		global $DB;
		
	$time =0;
	$creatorjoined = $DB->get_record('bigbluebuttonbn_logs',array('id' => $blogid));
	// Find out next session start of the same meeting
	$sql = "SELECT * FROM {bigbluebuttonbn_logs} 
			WHERE meetingid = '".$creatorjoined->meetingid."' AND id > $blogid 
			AND log='Create' ORDER BY timecreated ASC LIMIT 0,1 ";
	$nextsession = $DB->get_record_sql($sql);
	if($nextsession){
		$maxofcurrentsession = $nextsession->timecreated;
	}
		
	$sql = "SELECT * FROM {logstore_standard_log} c";            		
	$sql .=" WHERE component ='mod_bigbluebuttonbn' AND action ='left' 
				AND objectid=$id AND userid=$createrid AND timecreated > ".$creatorjoined->timecreated.
				" ORDER BY timecreated ASC LIMIT 0,1";
	$creatorleft = $DB->get_record_sql($sql);
	
	if(!$creatorleft){
		$sql = "SELECT * FROM {logstore_standard_log} c";            		
		$sql .=" WHERE component ='mod_bigbluebuttonbn' AND action ='left' 
					AND objectid=$id AND timecreated > ".$creatorjoined->timecreated.
					" ORDER BY timecreated ASC LIMIT 0,1";
		$creatorleft = $DB->get_record_sql($sql);
		
	}
	if($creatorleft)
	$time = $creatorleft->timecreated - $creatorjoined->timecreated;
	
	return format_length($time);
	
	
}
    /**
     * Formats time based in Moodle function format_time($totalsecs).
     * @param int $totalsecs
     * @return string
     */
function format_length($totalsecs) {
        $totalsecs = abs($totalsecs);

        $str = new stdClass();
        $str->hour = get_string('hour');
        $str->hours = get_string('hours');
        $str->min = get_string('min');
        $str->mins = get_string('mins');
        $str->sec = get_string('sec');
        $str->secs = get_string('secs');

        $hours = floor($totalsecs / HOURSECS);
        $remainder = $totalsecs - ($hours * HOURSECS);
        $mins = floor($remainder / MINSECS);
        $secs = round($remainder - ($mins * MINSECS) , 2);
        
		if ($secs >30) {
			$mins = $mins + 1;
		}
		
        $sm = ($mins == 1) ? $str->min : $str->mins;
        $sh = ($hours == 1) ? $str->hour : $str->hours;

        $ohours = '';
        $omins = '';

        if ($hours) {
            $ohours = $hours . ' ' . $sh;
        }
        if ($mins) {
            $omins = $mins . ' ' . $sm;
        }

        if ($hours) {
            return trim($ohours . ' ' . $omins);
        }
        if ($mins) {
            return trim($omins);
        }

        return "";
    }
