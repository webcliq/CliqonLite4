<?php
// Common.php
// Business Logic common to all pages
$Q = new clq();

$tpl->set('lcd', $lcd);
$tpl->set('siteicon', $cfg['site.favicon']); 
$tpl->set('replyto', $cfg['site.email']);
$tpl->set('lcdarray', $Q->cLcd());

$jsfiles = array (
	'message', 'cliqon', 'browser'
);
$tpl->set('jsfiles', $jsfiles);

$commonscripts = "";
$tpl->set('commonscripts', $commonscripts);
