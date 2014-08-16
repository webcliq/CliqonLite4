<?php
/**
* Cliqon Startup script
* this script assumes that content will be output to screen as page
* provides a simple routing function
* @author Mark Richards, Webcliq
* @version 1.08
*/
set_include_path($rootpath);
// Load Gateway.Php - this can be used whenever a file is loaded but Templating not required
require_once("gateway.php");
array_key_exists('page', $_REQUEST) ? $page = $_REQUEST['page'] : $page = "index";
require_once($rootpath."config/clqschema.cfg");

// clqtpl::configure($cfg['site.clqtpl']['base_url']);
// clqtpl::configure($cfg['site.clqtpl']['tpl_ext']);
// clqtpl::configure($cfg['site.clqtpl']['cache_dir']);
// clqtpl::configure($cfg['site.clqtpl']['debug'])
// clqtpl::configure( 'php_enabled', true );
if($page == "admin") { // Administrative popup
	array_key_exists('admin', $_REQUEST) ? $page = $_REQUEST['admin'] : $page = "dashboard";
	$tpldir = $cfg['site.admclqtpl']['tpl_dir'];
	clqtpl::configure('path_replace', false); 
	// clqtpl::configure('path_replace_list', array('<a>','<link>','<img>')); // but not <scripts>	
	clqtpl::configure('tpl_dir', $tpldir);
	$tpl = new clqtpl();
	$tpl->set('viewpath', $tpldir);
} else { // Front end website
	$tpldir = $cfg['site.clqtpl']['tpl_dir'];
	clqtpl::configure('tpl_dir', $tpldir);
	clqtpl::configure('path_replace', true); // /views appears root of website
	$tpl = new clqtpl();
	$tpl->set('viewpath', $tpldir);
	require_once($tpldir."common.php");
}

if($cfg['site.cdn'] == true) {
	$tpl->set('jspath', $cfg['site.cdnlocn']);
} else {
	$tpl->set('jspath', $cfg['site.jspath']);
}

// List here any JS or CSS files that require compressing
$jsfiles = array(
	"/includes/js/i18n/cliqon.".$lcd.".js"
);
$tpl->set('compressjsfiles', $jsfiles);
$tpl->set('rootpath', $rootpath);
$tpl->set('stylesheet', $cfg['site.defaultcss']);
$tpl->set('ajax', "");

$rq = $_REQUEST;
$ready = false;

require_once($tpldir.$page.".php");
while($ready !== true) {usleep(1000);};
echo $tpl->publish($page);
