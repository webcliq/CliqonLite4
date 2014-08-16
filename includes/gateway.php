<?php
/**
* Cliqon Gateway
* This script loads all the utility functions that do not involve display of content or page on screen
*
* @author Mark Richards, Webcliq
* @version 1.01
*/
// Load Config File
session_start();
$cfgpath = $rootpath."config/"; 
require($cfgpath."config.php");
$sitepath = $cfg['site.url']."/";
// var_dump($cfg);

// Error Handling
require_once("error.php");

// Session Handling
array_key_exists('langcd', $_REQUEST) ? $lcd = $_REQUEST['langcd'] : $lcd = $cfg['site.defaultidiom'];
$_SESSION['CLQ_Langcd'] = $lcd;
$_SESSION['CLQ_Sitepath'] = $sitepath;
$_SESSION['CLQ_Config'] = $cfg;

// More Paths
$tmpath = $rootpath."tmp/";
$includepath = $rootpath."includes/";
$jspath = $rootpath."includes/js/";
$classpath = "includes/classes/";

// Strip white space
if($cfg['site.minify'] == true) {
	require_once("phpminifier.php");
	$dir_iterator = new RecursiveDirectoryIterator($classpath); 
	$iterator = new RecursiveIteratorIterator($dir_iterator, RecursiveIteratorIterator::SELF_FIRST);

	$files = array(); 
	foreach ($iterator as $file) { 
		if(substr($file->getPathname(), -3) == 'php') { 
			$files[] = $file->getPathname(); 
		} 
	}; usort($files);
	
	$origfile = array();
	foreach($files as $original) {
		$origfile[0] = $original;
		PhpMinifier::minify($origfile, $original);
		echo $original." is compressed<br />";
	}
}

// Minify a PHP single file
	
/*
	require_once("phpminifier.php");
	$original = $classpath."clqadmin.class.php";
	$origfile = array();
	$origfile[0] = $original;
	PhpMinifier::minify($origfile, $original);
	echo $original." is compressed<br />";
*/
	
// Start with the Autoloader and Classes are available
require_once("autoload.php");

if($cfg['site.rbphar'] == true) {
	loadFile("includes/rb.phar"); // Version 4 - Version 3 if exists would autoload
	// echo "RB.phar loaded";
} else {
	loadFile("includes/classes/rb.class.php");
}

switch($dbcfg['dbtype']){
	case"mysql":R::setup('mysql:host='.$dbcfg['server'].';dbname='.$dbcfg['db'],$dbcfg['user'],$dbcfg['password']);break;
	case"pgsql":R::setup('pgsql:host='.$dbcfg['server'].';dbname='.$dbcfg['db'],$dbcfg['user'],$dbcfg['password']);break;
	case"sqlite":R::setup('sqlite:'.$rootpath.'data/'.$dbcfg['db'], $db['user'], $dbcfg['password']); break;
}
R::useWriterCache(true);

// Load All Utility Functions
$clq = new clq(); 

// Load jQuery for PHP classes
if($cfg['site.usejq4php'] == true) {
	include_once 'classes/jq4php/YepSua/Labs/RIA/jQuery4PHP/YsJQueryAutoloader.php';
	YsJQueryAutoloader::register();
}

// Hand back to clqstartup
