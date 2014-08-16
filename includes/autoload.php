<?php

require_once('autoloader.class.php');
Autoloader::setCacheFilePath($rootpath.'tmp/class_path_cache.txt');
Autoloader::excludeFolderNamesMatchingRegex('/^CVS|\..*$/');
Autoloader::setClassFileSuffix('.class.php');
Autoloader::setClassPaths(array(
    $rootpath.'includes/classes/',
));
spl_autoload_register(array('Autoloader', 'loadClass'));

function loadFile($filepath, $tst = false) {
    
    global $sitepath; global $clqlog;

    if($tst === true) {
        
        $clqlog->writeLog('About to Load File',$_SERVER["DOCUMENT_ROOT"]."/".$filepath);

        if(url_exists($sitepath.$filepath) !== true) {      
            $clqlog->writeLog('It says file not found', $filepath);
            return false;
        } else {
            $clqlog->writeLog('It says file is found', $_SERVER["DOCUMENT_ROOT"]."\\".$filepath);    
            if(is_readable($_SERVER["DOCUMENT_ROOT"]."/".$filepath)) {

                $clqlog->writeLog('It says file can be read', $_SERVER["DOCUMENT_ROOT"]."\\".$filepath);

                $ok = checkFile($_SERVER["DOCUMENT_ROOT"]."/".$filepath);
                if($ok == "OK") {
                    $clqlog->writeLog('It says file is OK', $_SERVER["DOCUMENT_ROOT"]."\\".$filepath);
                    require_once($_SERVER["DOCUMENT_ROOT"]."/".$filepath);
                    $clqlog->writeLog('It says file is loaded', $_SERVER["DOCUMENT_ROOT"]."\\".$filepath); 
                    return true;
                } else {
                    $clqlog->writeLog('File did not come back OK', $_SERVER["DOCUMENT_ROOT"]."\\".$filepath);
                }
            } else {
                $clqlog->writeLog('It says file cannot be read', $_SERVER["DOCUMENT_ROOT"]."\\".$filepath);
            }
            return false;
        }

    } else {
        require_once($_SERVER["DOCUMENT_ROOT"]."/".$filepath); return true;
    }
}

function url_exists($url) {
    
    $url = str_replace("http://", "", $url);
    if (strstr($url, "/")) {
        $url = explode("/", $url, 2);
        $url[1] = "/".$url[1];
    } else {
        $url = array($url, "/");
    }

    $fh = fsockopen($url[0], 80);
    if ($fh) {
        fputs($fh,"GET ".$url[1]." HTTP/1.1\nHost:".$url[0]."\n\n");
        if (fread($fh, 22) == "HTTP/1.1 404 Not Found") { return FALSE; }
        else { return TRUE;    }

    } else { return FALSE;}

}

function checkFile($filepath) {

    global $clqlog;
    $handle = fopen($filepath, 'r');
    $code = fread($handle, filesize($filepath));
    $url = 'http://phpcodechecker.com/api/';
    $myvars = 'code='.$code;

    $ch = curl_init($url);
    curl_setopt( $ch, CURLOPT_POST, 1);
    curl_setopt( $ch, CURLOPT_POSTFIELDS, $myvars);
    curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt( $ch, CURLOPT_HEADER, 0);
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt( $ch, CURLOPT_HTTPHEADER, 'Content-type: application/json');
    
    $response = curl_exec($ch);
    if(stristr($response, '=[No Error]') !== false) {
        return "OK";
    } else {
        $clqlog->writeLog('Check Syntax', $response);
    }
    
}
