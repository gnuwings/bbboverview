<?php

require('../../config.php');
require_once($CFG->dirroot.'/lib/excellib.class.php');
 $name= optional_param('name', '',PARAM_TEXT);
  $exceldata= optional_param('exceldata',null,PARAM_RAW);
  
$workbook = new MoodleExcelWorkbook("-");
$workbook->send($name.'_'.time().'.xlsx');

  $worksheet = array();
    $worksheet[0] = $workbook->add_worksheet('');
    $rowno = 0;
    foreach (json_decode($exceldata) as $row) {
        $colno = 0;
        foreach($row as $col) {
            $worksheet[0]->write($rowno, $colno, $col);
            $colno++;
        }
        $rowno++;
    }
    $workbook->close();


?>
