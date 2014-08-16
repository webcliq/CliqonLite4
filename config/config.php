<?php
// Web site specific configuration file
$dbcfg = array(
    'dbtype' => 'sqlite', // sqlite
    // 'server' => '192.168.3.6',
    'db' => 'cliqondb.sqlite', // cliqondb.sqlite
    'user' => 'root',
    'password' => 'Sonc4mas',
    // 'port'	=> '3306',
);
        
$cfg = array(
    
    'site.idiom' => array('en' => 'English', 'es' => 'Español'),
    'site.idiomflags' => array('en' => 'en.png', 'es' => 'es.png'),
    'site.defaultidiom' => 'en',
    // 'site.url' => 'http://'.$_SERVER['SERVER_NAME'].'/',
	'site.url' => 'http://example.cliqonlite.com/',
	'site.dir' => '/home/webroot/example.cliqonlite.com/', // Elfinder
    'site.timezone' => 'Europe/Madrid',
    'site.dateformat' => 'd-M-Y',
    'site.numberformat' => '2|,|.|| €', // // Used by numbers - dec places, dec sep, thousands sep '2|,|.'
	'site.title' => 'Cliqonlite',
	'site.author' => 'Webcliq',
	'site.favicon' => 'http://cliqonlite.com/views/icons/cliqon.ico',
	'site.email' => 'info@cliqonlite.com',
	'site.logo' => 'cliqonlite.png',
	'site.subhead' =>	'Web development micro toolset',
	'site.metaclassification' => 'internet',
	'site.metadescription' => 'Web development toolset',
	'site.metakeywords' => 'web pages, internet, web design, development, programming, php, javascript',

	'site.defaultpage' => 'index',
	'site.clqtpl' => array(
		"base_url"      => null,
		"tpl_ext"		=> "tpl",
		"tpl_dir"       => "views/",
		"cache_dir"     => "cache/",
		"debug"         => false, // set to false to improve the speed
	),
	'site.admclqtpl' => array(
		"base_url"      => null,
		"tpl_ext"		=> "tpl",
		"tpl_dir"       => "admin/",
		"cache_dir"     => "cache/",
		"debug"         => false, // set to false to improve the speed
	),
	
	'site.server' => 'Apache',
	'site.stylesheetpath' => 'views/css/',
	'site.stylesheet' => 'style.css',
	'site.defaultcss' => 'style.css',
	'site.iconpath' => 'views/icons/',
	'site.imgpath' => 'views/images/',
	'site.jspath' => 'includes/js/', // After rootpath
	'site.texteditor' => 'ckeditor', // tinymce or ckeditor

	'site.permissions' => false,	// Use User permissions system to control access to pages
	'site.autoload' => true,		// Use Autoload (or list of individual )
	'site.minify' => false,			// Use PHP minify function
	'site.rbphar' => false,			// Use Rb.Phar as opposed to Rb.Class.Php
	'site.usejq4php' => true,		// Use YepSua jQuery 4 PHP program to generate jQuery in PHP Classes
	'site.cdn' => true,			// use CDN for js and css files - used in clqutil Class - 
	'site.cdnlocn' => 'http://cdn.webcliq.net/files/clqlite/js/',			// If site.cdn == true, URL of CDN location
	'site.test' => true,			// Use Diagnostics popup function

	'site.users' => array(		
		'admin' => array('username' => 'admin', 'password' => 'password', 'fullname' => 'Administrator', 'masteridm' => 'en', 'email' => 'admin@cliqonlite.com', 'level' => 90),		
	),
	
	'site.clq_title'	=> 'Cliqon',
	'site.bingkey'		=> 'f81IVLJxga23f5dnJNhqYadSnuHmqKOrkUevZ7VE18g=',
	
	// Mail Configuration

	// Webcliq.Net
	'mail.host'					=> 'webcliq.net',
	'mail.port'					=> 587,
	'mail.username'				=> 'formmail@webcliq.net',
	'mail.password'				=> 'B25frm#', //

	// Where emails come from if not defined in Form
	'mail.from' 				=> 'webmaster@cliqonlite.com',
	'mail.from_name'			=> 'Webcliq',

	// Production Status
	'mail.status'				=> 'test',	// test or production

	// Primary Client email
	'mail.to'					=> '', 
	'mail.to_name'				=> '', 

	// Secondary Client email
	'mail.cc'					=> '',
	'mail.cc_name'				=> '',

	// Testing Address
	'mail.xto'					=> 'mark.richards@conkas.com',
	'mail.xto_name'				=> 'Mark Richards',

);

/**
 * @var string Microsoft/Bing Primary Account Key
 */
if (!defined('ACCOUNT_KEY')) {
    define('ACCOUNT_KEY', 'Primary Account Key');
}
if (!defined('CACHE_DIRECTORY')) {
    define('CACHE_DIRECTORY', $rootpath.'cache/translate/');
}
if (!defined('LANG_CACHE_FILE')) {
    define('LANG_CACHE_FILE', 'lang.cache');
}
if (!defined('ENABLE_CACHE')) {
    define('ENABLE_CACHE', false);
}
if (!defined('UNEXPECTED_ERROR')) {
    define('UNEXPECTED_ERROR', 'There is some un expected error . please check the code');
}
if (!defined('MISSING_ERROR')) {
    define('MISSING_ERROR', 'Missing Required Parameters ( Language or Text) in Request');
}
