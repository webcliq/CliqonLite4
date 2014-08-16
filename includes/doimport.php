<?php
// Form getter
if(!session_id()) {session_start();}; $rootpath = "../";

// Error Handling
require_once($rootpath."lib/includes/error.php");

$lcd = $_REQUEST['langcd'];

require_once $rootpath."config/config.php";
require_once $rootpath."lib/classes/clq.class.php";
$clq = new clq();
require_once $rootpath."lib/classes/clqcsvimport.class.php";
$dbimp = new clqcsvimport();

/*
langcd=en
encoding=UTF-8
file_source=import_gallery.csv

use_csv_header=on
duplicate_idiom=on
do_insert=on
do_test=on

field_separate_char=;
field_enclose_char="
field_escape_char=\
whichtable=images
*/

// print_r($_REQUEST)

/**
* Rules
* File Source has to be valid and file must exist as csv.
*/
if($_REQUEST['file_source'] == "") {$errmsg = "Import File name must not be empty"; goto exitlabel;};
$extn = explode(".", $_REQUEST['file_source']); if($extn[1] != "csv") {$errmsg = "Import File must be of type CSV"; goto exitlabel;};
if(!file_exists($rootpath."data/".$_REQUEST['file_source'])) {$errmsg = "Import File does not exist"; goto exitlabel;};

/** Test - Read in Import file and convert to array
$fn = $rootpath."data/".$_REQUEST['file_source'];	// This is at root of the file using this script.
$fd = fopen ($fn, "r"); 							// opening the file in read mode
$csv = fread ($fd, filesize($fn)); 					// reading the content of the file
fclose ($fd);               						// Closing the file pointer	
$result = $csv;
*/

$result = $dbimp->import();
echo $result;
exit;

exitlabel:
echo $errmsg;;
