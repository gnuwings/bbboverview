<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Form to filter the bbboverview report
 *
 * @package   report_bbboverview
 * @copyright 2017 Davo Smith, Synergy Learning
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/report/bbboverview/locallib.php');

/**
 * Class filter_form form to filter the results by date
 * @package report_bbboverview
 */
class filter_form extends \moodleform {
    /**
     * Form definition
     * @throws \HTML_QuickForm_Error
     * @throws \coding_exception
     */
    protected function definition() {
		global $DB;
        $mform = $this->_form;
		$start=$this->_customdata['start'];
				$end=$this->_customdata['end'];

        $mform->addElement('header', 'filterheader', get_string('filter'));
		$courses = allcourses();
		$users= array('0'=>"Select");
		
		if(isset($this->_customdata['course'])){
			$mform->setDefault('course', $this->_customdata['course']);
			$requesteddata = $this->_customdata['course'];
			if($requesteddata > 0){				 
				$enrolledusers = get_enrolled_users(\context_course::instance($requesteddata));

				if($enrolledusers){
					 foreach ($enrolledusers as $enroluser) {
						$users[$enroluser->id] = $enroluser->lastname.' '.$enroluser->firstname;
					 }
					
				}
				natcasesort($users);
				$selusers = array('0' => 'All users');
				$users = $selusers + $users;

			}
			if(isset($this->_customdata['user']))
				$mform->setDefault('user', $this->_customdata['user']);
		}
        
		$mform->addElement('select', 'course', get_string('course','report_bbboverview') , $courses,array('style'=>'width:30%'));

		$mform->addElement('select', 'user', get_string('users','report_bbboverview') , $users,array('style'=>'width:30%'));
		
		$mform->addElement('html', '<div id="fitem_id_reportrange" class="form-group row  fitem   ">');
		$mform->addElement('html', '<div class="col-md-3">
									<label class="col-form-label d-inline " for="id_reportrange">
										'.get_string('date', 'report_bbboverview').'
									</label>
								</div>
								<div class="col-md-9 form-inline felement" data-fieldtype="text">');
		

		$mform->addElement('html','<div  style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc;">
									<i class="fa fa-calendar"></i>&nbsp;
									<input type="text" id="reportrange" name="reportrange" style="border:none;"/>
									
									<span></span> <i class="fa fa-caret-down"></i>
								
								 <div class="form-control-feedback invalid-feedback" id="id_error_reportrange"></div>
            
									</div></div></div>');
		$mform->addElement('html', '<input type="hidden" id="startdate" value="'.$start.'" name="startdate"/>');
		$mform->addElement('html', '<input type="hidden" id="enddate" value="'.$end.'" name="enddate" />');
        $this->add_action_buttons(false, get_string('report','report_bbboverview'));
    }
    
     function validation($data, $files) {
		global $CFG, $DB;
        $errors = parent::validation($data, $files);
		
		        return $errors;

	 }

  
}
