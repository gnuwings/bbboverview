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
 * Display user bbboverview reports for a course (totals)
 *
 * @package    report
 * @subpackage bbboverview
 * @copyright  1999 onwards Martin Dougiamas (http://dougiamas.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once('filter_form.php');
require_once('locallib.php');
require_once($CFG->dirroot . '/mod/bigbluebuttonbn/locallib.php');
require_once($CFG->libdir . '/filelib.php');

require_login();

// PERMISSION.
$requestedcourse = optional_param('course', 0, PARAM_INT);
$requesteduser= optional_param('user', 0, PARAM_INT);
$exportrequest= optional_param('export', 0, PARAM_INT);
$reportrange= optional_param('reportrange', '-', PARAM_RAW);
$startd= optional_param('startdate', '-', PARAM_RAW);
$endd= optional_param('enddate', '-', PARAM_RAW);
$page         = optional_param('page', 0, PARAM_INT);
$perpage      = optional_param('perpage', 20, PARAM_INT);        // How many row per page.
$sort    = optional_param('sort', 'issuedate', PARAM_RAW);
$dir     = optional_param('dir', 'DESC', PARAM_ALPHA);
$params = array();

if ($requestedcourse) {
    $params['course'] = $requestedcourse;
}
if ($requesteduser) {
    $params['user'] = $requesteduser;
}
if ($page) {
    $params['page'] = $page;
}
if ($perpage) {
    $params['perpage'] = $perpage;
}
$params['start']=$startd;
$params['end']=$endd;

$context = context_system::instance();
require_capability('report/bbboverview:view', $context);

$myarray = array('start'=>$startd,'end'=> $endd);
$title = get_string('pluginname', 'report_bbboverview');
$heading = $SITE->fullname;
$posturl = new moodle_url('/report/bbboverview/index.php');
$PAGE->set_url($posturl);
$PAGE->set_pagelayout('report');
$PAGE->set_context($context);
$PAGE->set_title($title);
$PAGE->set_heading($heading);
$basetext = get_string('administrationsite');
$baseurl = new moodle_url('/admin/search.php');
$PAGE->navbar->add($basetext, $baseurl);
$basetext = get_string('reports');
$baseurl = new moodle_url('/admin/category.php', array(
	'category' => 'reports'
));
$PAGE->navbar->add($basetext, $baseurl);
$PAGE->navbar->add($title, new moodle_url($PAGE->url));
$PAGE->requires->jquery();
$PAGE->requires->js('/report/bbboverview/js/moment.min.js', true);
$PAGE->requires->js('/report/bbboverview/js/daterangepicker.js', true);
$PAGE->requires->js('/report/bbboverview/js/custom.js', true);
$PAGE->requires->js_init_call('getDate', array($myarray));
$PAGE->requires->css('/report/bbboverview/css/daterangepicker.css');
$jsmodule = array(
	'name' => 'report_bbboverview',
	'fullpath' => '/report/bbboverview/js/module.js',
	'requires' => array('json'),
);
$PAGE->requires->js_init_call('M.report_bbboverview.init', array() , false, $jsmodule);
     

if($requestedcourse >0)
	$filterform = new filter_form(null,$params);
else
	$filterform = new filter_form(null);
if($startd != '-'){
	$datefrom = strtotime($startd);
}
else {
	$datefrom = strtotime(date('Y-m-d', strtotime('today - 30 days'))); 
}
if($endd != '-'){
	$dateto = strtotime($endd);
}
else {
		$dateto = time();
}
$sql = "SELECT blog.*,bbb.id as bbbid,bbb.name,c.fullname
			FROM {bigbluebuttonbn} bbb
			INNER JOIN {course} c on c.id=bbb.course
			JOIN (
				SELECT blogs.id as blogid,blogs.bigbluebuttonbnid,blogs.timecreated,blogs.meetingid,
				blogs.userid,u.firstname,u.lastname
				FROM {bigbluebuttonbn_logs} blogs 
				JOIN {user} u ON blogs.userid = u.id
				WHERE u.deleted = 0 AND u.suspended = 0
				AND blogs.log = 'Create' 
			) blog ON blog.bigbluebuttonbnid = bbb.id

			WHERE 1 ";
$sql .=" AND blog.timecreated > $datefrom AND blog.timecreated < $dateto";
 $sqlparams = array();
if ($requestedcourse>0) {
	$sql .= " AND c.id=:course";
	$sqlparams['course'] = $requestedcourse;
}
if ($requesteduser >0 ) {
	$sql .=" AND blog.userid=:user";
	$sqlparams['user'] = $requesteduser;

}
$sort =" bbb.name";
$dir = " ASC";
$sql .= " ORDER BY $sort $dir";

$activities = $DB->get_records_sql($sql,$sqlparams,$page * $perpage, $perpage);
$serverurl = \mod_bigbluebuttonbn\locallib\config::get('server_url');
$sharedsecret = \mod_bigbluebuttonbn\locallib\config::get('shared_secret');
$apicallfunctionname = 'getMeetingInfo';
$apicallcreate = 'create';
// $apicallparameter = "meetingID=abc123";
// http://yourserver.com/bigbluebutton/api/create?name=Test&meetingID=test01&checksum=1234


if(!empty($activities)){
	$array_excel = array();
	$array_excel[] = array(get_string('sessionname','report_bbboverview'),
							get_string('course','report_bbboverview'),
							get_string('owner','report_bbboverview'),
							get_string('date','report_bbboverview'),
							get_string('length','report_bbboverview'),
							get_string('attendees','report_bbboverview'),
							get_string('activeattendees','report_bbboverview'));
	foreach ($activities as $activity) {
		$createrid = $activity->userid;
		$length = get_bbb_length($activity->blogid,$activity->bigbluebuttonbnid, $createrid);
		$noofattendees = get_bbb_noofattendees($activity->blogid,$activity->bigbluebuttonbnid, $createrid);
			
        $array_excel[] = array($activity->name,
								$activity->fullname,
                               $activity->firstname.' '.$activity->lastname,
								date('d/m/Y',$activity->timecreated),
								$length,
								$noofattendees,
								'');
	}
		//$exceldata=json_encode($array_excel);
	if ($exportrequest!=0) {
		if ($exportrequest==1) {
			$exporturl = new moodle_url('/report/bbboverview/download_excel.php',array('name'=>'BBBOverview','exceldata' => json_encode($array_excel)));

		}
		if ($exportrequest==2) {
			$exporturl = new moodle_url('/report/bbboverview/download_csv.php',array('name'=>'BBBOverview','exceldata' => json_encode($array_excel)));
		}
		if ($exportrequest==3) {
			$exporturl = new moodle_url('/report/bbboverview/download_pdf.php',array('name'=>'BBBOverview','exceldata' => json_encode($array_excel)));
		}

		redirect($exporturl);
	}
}

if($exportrequest==0){
	echo $OUTPUT->header();
	echo $OUTPUT->heading($title);
	$filterform->display();

	if(!empty($activities)){

		$export = array('0'=>'Export','1'=>'xlsx','2'=>'csv','3'=>'pdf');
		echo '<div style="float:right;">';
		echo $OUTPUT->single_select($posturl, 'export', $export, $exportrequest, '', ''); 
		echo '</div>';
		
		$table = new html_table();
		$table->tablealign="left";
		$table->head  = array(get_string('sessionname','report_bbboverview'),
								get_string('course','report_bbboverview'),
								get_string('owner','report_bbboverview'),
								get_string('date','report_bbboverview'),
								get_string('length','report_bbboverview'),
								get_string('attendees','report_bbboverview'),
								get_string('activeattendees','report_bbboverview'),
								get_string('details','report_bbboverview'));
		$table->colclasses = array('leftalign');
		$table->align = array('centre');
		$table->width = '50%';
		$table->attributes['class'] = 'generaltable';
		$table->data = array();
		$isdata=0;
		foreach ($activities as $activity) {
			/*$apicallparameter = "meetingID=".$activity->meetingid;
			
			$checksum = sha1($apicallfunctionname.$apicallparameter.$sharedsecret);
			echo $apicall = $serverurl.'api/'.$apicallfunctionname.'?'.$apicallparameter.'&checksum='.$checksum;
			$curl = new curl;
			$curl->setHeader('Content-Type: application/json; charset=utf-8');

			$responses = $curl->post($apicall,'', array('CURLOPT_FAILONERROR' => true));
			$xml=(array)simplexml_load_string($responses);
			print_r($xml);*/

			$createrid = $activity->userid;
			$length = get_bbb_length($activity->blogid,$activity->bigbluebuttonbnid, $createrid);
			$noofattendees = get_bbb_noofattendees($activity->blogid,$activity->bigbluebuttonbnid, $createrid);
			
			$row = array();
			$row[] = $activity->name;
			$row[] = $activity->fullname;
			$row[] = $activity->firstname.' '.$activity->lastname;
			$row[]= date('d/m/Y',$activity->timecreated);
			$row[] = $length;
			$row[] = $noofattendees;
			$row[] = "";
			$alt = 'actiontext';
			$link = new \moodle_url('/report/bbboverview/datamodels/sequencinghandler.php', ['id' => $activity->blogid]);
			$row[] = html_writer::tag('a', get_string('view','report_bbboverview'), array(
				'href' => $link,
				'alt' => $alt,
				'class' => 'view',
				'data-toggle' => 'modal',
				 'data-target' => '#modalForm'
			));
			$table->data[] = $row;
			//echo '<hr/>';
		}
		echo html_writer::start_tag('div', array('id'=>'bbboverview'));
		echo html_writer::table($table);
		echo html_writer::end_tag('div');
		echo ' <div class="modal fade modal-view" id="modalForm" role="dialog">
			<div class="modal-dialog">
				<div class="modal-content">      <!-- Modal Header -->
					<div class="modal-header">
									<h4 class="modal-title" id="myModalLabel">BBB attendance overview report</h4>

						<button type="button" class="close" data-dismiss="modal">
							<span aria-hidden="true">&times;</span>
							<span class="sr-only">Close</span>
						</button>
					</div>
					
					<!-- Modal Body -->
					<div class="modal-body" id="session-view">
				
				</div>
					
				</div>
			</div>
		</div>';
		  
	} else {
		echo 'No records';
	}

	echo $OUTPUT->footer();

}
