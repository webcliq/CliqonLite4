<?php 
/**
* Working towards an MD5 Sum
*/
// Loads Config and Clq Class plus basic stuff
$rootpath = "../../"; $result = "";
require_once($rootpath."includes/gateway.php");

if(!isset($_SESSION['CLQ_Username']) && $_REQUEST['action'] != "login") {die('Access denied');};
// print_r($_REQUEST);
// array of directories to check
$dirarray = array(
    'admin', 'classes', 'includes', 'js', 'min'
);

$clqf = new clqfile();
$clqf->setVar('rootpath', $rootpath);

// Title for the Page 
$result .= "<h2>".str('582:Update Files')."</h2>";
$result .= "<p>".str('583:Update Files instructions')."</p>";

// Step one list the local directories and files

foreach($dirarray as $directory) {      
    $files .= $clqf->listTree('list', $directory);
}
$files = trim($files, '|');
$dirfiles = explode('|', $files);

$filehasharray = array();
foreach($dirfiles as $filename) {            
     $hash = md5_file($rootpath.$filename);   
     $hash = trim($hash); $file = trim($filename);
     $filecsv .= "\"".$file."\",\"".$hash."\"\r\n";
}

// Prepare and write file array
// Create a local system hash file
$hfile = $rootpath.'filehash.txt';
$handle = fopen($hfile, 'w') or die('Cannot open file:  '.$hfile);
fwrite($handle, $filecsv);
fclose($handle);


$locfile = $rootpath.'filehash.txt';
# Open the File.
if (($handle = fopen($locfile, "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {    
        $locarray[$data[0]] = $data[1];
    }
    fclose($handle);
}

// Get the equivalent hash file from the repository - in this case our OwnCloud Server
// Write file to disk
$checkfile = "http://webcliq:grouse@own.ojonet.net/remote.php/webdav/cliqonlite/svrfilehash.txt";
$curl = new clqcurl();
$hashfile = $curl->get($checkfile);
$hfile = $rootpath.'remote_filehash.txt';
$handle = fopen($hfile, 'w') or die('Cannot open file:  '.$hfile);
fwrite($handle, $hashfile);
fclose($handle);

$remfile = $rootpath.'remote_filehash.txt';
# Open the File.
if (($handle = fopen($remfile, "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {    
        $remarray[$data[0]] = $data[1];
    }
    fclose($handle);
}

$dfile = $rootpath.'filehash.txt';
unlink($dfile);
$hfile = $rootpath.'remote_filehash.txt';
unlink($hfile);

// use $locarray - $file => $hash
// use $remarray - $file => $hash

$diffarray = array_diff($remarray, $locarray);

// Now we can start to create a sensible print out

$result .= "<div class='tbl'>";
foreach($diffarray as $file => $hash) {
   $file = str_replace('//', '/', $file);

   // Introduce a get changes routine for this file from Server database

   $result .= "
    <div class='tblr' rel='".$file."' >
        <span class='tblc' style='width:300px;'>".$file."<br />
        <span style='font-size: 9px; color: grey;'>Version upgrade</span></span>
        <button class='tblc btn comparebutton' data-action='compare' style=''>Compare</button>
        <button class='tblc btn overwritebutton' data-action='overwrite' style='margin-left: 10px;'>Overwrite</button>
    </div>
    ";
};
$result .= "</div>";


echo $result;
