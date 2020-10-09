<?php
require('../../config.php');
    require_once($CFG->libdir . '/csvlib.class.php');
 $name= optional_param('name', '',PARAM_TEXT);
  $exceldata= optional_param('exceldata',null,PARAM_RAW);
  
    $csvexport = new csv_export_writer();
    $csvexport->set_filename($name);
        foreach(json_decode($exceldata)  as $col) {
           $csvexport->add_data($col);        
        }   
    $csvexport->download_file();
    return;
?>



