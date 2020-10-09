<?php
require('../../config.php');
require_once($CFG->libdir.'/pdflib.php');
$name= optional_param('name', '',PARAM_TEXT);
  $exceldata= optional_param('exceldata',null,PARAM_RAW);
		
		$doc = new pdf();
		$doc->setPrintHeader(false);
		$doc->setPrintFooter(false);
		$doc->AddPage();
		$tbl='';	
		$tbl = '<table border="1">';
		
$rowno = 0;
    foreach (json_decode($exceldata) as $row) {
		$tbl .= '<tr>';
        $colno = 0;
        foreach($row as $col) {

			
			$tbl .= '<td>'.$col.'</td>';
		 $colno++;
        }
        $rowno++;
			
			$tbl .= '</tr>';
		}
		$tbl .= '</table>';	
		$name=$name.'_'.time();	
		$doc->WriteHTML($tbl);
        $downloadfilename = clean_filename("$name.pdf");
		$doc->Output($downloadfilename,'D');
       redirect('/report/bbboverview/index.php');

