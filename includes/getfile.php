<?php 
/**
* This file gets a remote file and 
*/
// Loads Config and Clq Class plus basic stuff
$rootpath = "../../"; $result = "";
require_once($rootpath."includes/gateway.php");

if(!isset($_SESSION['CLQ_Username']) && $_REQUEST['action'] != "login") {die('Access denied');};

$pathfile = $_GET['pathfile']; // Path/File from Root or Remote

// Get the equivalent hash file from the repository - in this case our OwnCloud Server
// Write file to disk
$getfile = "http://webcliq:grouse@own.ojonet.net/remote.php/webdav/cliqonlite/".$pathfile;
$curl = new clqcurl();
$hashfile = $curl->get($getfile);
$remfile = $rootpath.$pathfile;

if (is_writable($remfile)) {

    // In our example we're opening $filename in append mode.
    // The file pointer is at the bottom of the file hence
    // that's where $somecontent will go when we fwrite() it.
    if (!$handle = fopen($remfile, 'w')) {
         echo str('586:The file is not accessible');
         exit;
    }

    // Write $somecontent to our opened file.
    if (fwrite($handle, $hashfile) === FALSE) {
        echo str('585:There was an error copying the file');
        exit;
    }

    echo str('584:File copied successfully');

    fclose($handle);

} else {
    echo str('586:The file is not accessible');
}
// Ends routine