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
require_once('../../../config.php');

require_once($CFG->libdir . '/completionlib.php');
require_once($CFG->dirroot . '/report/bbboverview/locallib.php');

$id = optional_param('id', 0, PARAM_INT);
$loadmore = optional_param('loadmore', 5, PARAM_INT);
$page = optional_param('page', 1, PARAM_INT);
$sort = optional_param('sort', 'fullname', PARAM_RAW);
$dir = optional_param('dir', 'ASC', PARAM_ALPHA);

$PAGE->set_url('/report/bbboverview/datamodels/sequencinghandler.php');
$PAGE->set_context(context_system::instance());

require_login();

$seq = array();
$url = new moodle_url($PAGE->url,array('id'=>$id));
 $seq['table'] = html_writer::div(html_writer::tag('p', get_string('nocourses') , array(
        'class' => 'text-muted mt-3'
    )) , '', array(
        'class' => 'text-xs-center text-center mt-3',
        'data-region' => 'empty-message'
    ))."<a id='textlinkpage'  href=\"$url\">Page </a>".$id;
echo json_encode($seq);

