<?php 
/**
* Generic Get Content activity - Admin variety
* could be a straight information return such as Help
* also Dataform
*/
// Loads Config and Clq Class plus basic stuff
$rootpath = "../../"; $result = "";
require_once($rootpath."includes/gateway.php");

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache'); 

if(!isset($_SESSION['CLQ_Username']) && $_REQUEST['action'] != "login") {die('Access denied');};

$msg = $clq->cStr('str(999)', 'Get Error');
if(isset($_REQUEST['action'])) {$action = $_REQUEST['action'];} else { die($msg); };

switch($action) {

		case "updatefiles":
			$result = updatefiles();    
		break;

		default: echo $action; break;

}

function updateFiles() {

	global $rootpath;
    // array of directories to check
    $dirarray = array(
        'admin', 'classes', 'config', 'includes', 'js', 'min'
    );

    foreach($dirarray as $directory) {      
        $files .= ListFolder($rootpath.$directory);
    }
    $files = trim($files, '|');
    $dirfiles = explode('|', $files);

    // Webpath
    $dserver = "http://webcliq:grouse@own.ojonet.net/remote.php/webdav/cliqonlite/";
    $curl = new clqcurlq();

    $config = array(
        "window" => 5,
        "timeout" => 10,
        "callback" => "clqcallback"
    );

    $curl->config($config);

    $steps = count($dirfiles);
    $clqmsg = new clqmsg($steps);
    $clqmsg->setVar('progress', 0);
    
    foreach($dirfiles as $file) {            
        $curl->get($dserver.$file);  
    }

    if ($result = $curl->execute()) {
    	echo "All requests have been processed.";
    } else {
    	echo "An error occured that prevented processing of all requests.";
    }

    $clqmsg->send_message('TERMINATE', 100);
    return $result;
}

$list = ""; $files = "";

function ListFolder($path) {
    
	global $list; global $files;
    //using the opendir function
    $dir_handle = @opendir($path) or die("Unable to open $path");
   
    //Leave only the lastest folder name
    $dirname = end(explode("/", $path));
   
    //display the target folder.
    $list .= ("<li>$dirname\n");
    $list .= "<ul>\n";
    
    while (false !== ($file = readdir($dir_handle))) {
        if($file!="." && $file!="..") {
            if (is_dir($path."/".$file)) {
                //Display a list of sub folders.
                ListFolder($path."/".$file);
            } else {
                //Display a list of files.
                $rawf = $path."/".$file;
                $files .= str_replace('../../', '', $rawf).'|';
                $list .= "<li>$file</li>";
            }
        }
    }

    $list .= "</ul>\n";
    $list .= "</li>\n";
   
    //closing the directory
    closedir($dir_handle);
    return $files;
} 

//   pass two file names
//   returns TRUE if files are the same, FALSE otherwise
function files_identical($fn1, $fn2) {
    if(filetype($fn1) !== filetype($fn2))
        return FALSE;

    if(filesize($fn1) !== filesize($fn2))
        return FALSE;

    if(!$fp1 = fopen($fn1, 'rb'))
        return FALSE;

    if(!$fp2 = fopen($fn2, 'rb')) {
        fclose($fp1);
        return FALSE;
    }

    $same = TRUE;
    while (!feof($fp1) and !feof($fp2))
        if(fread($fp1, READ_LEN) !== fread($fp2, READ_LEN)) {
            $same = FALSE;
            break;
        }

    if(feof($fp1) !== feof($fp2))
        $same = FALSE;

    fclose($fp1);
    fclose($fp2);

    return $same;
} 

// define the callback functions
function clqcallback($output, $info) {
    
    global $clqmsg;
    $url = $info["url"];

    /*
    if( !files_identical($file, $output) ) {
        $result = $file.'<br />';                 
    } else {
        $result = "Problem with: ".$file."<br />";
    }
    */

    $clqmsg->send_message($url);

}  