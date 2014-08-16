<?php
// Gallery.php
// Business Logic for Gallery.tpl

// Make calls to database when possible
$tpl->set('lcd', $lcd);
$clqadm = new clqadmin();
// Column one, menu
$tpl->set("admmenu", $clqadm->publishMenu('proxy'));

// Column two - Proxy - define here 
$url = $rq['url'];

$wjs = "
	[{type: 'header', template: '".$url."'},
	{type: 'iframe', height:580,  minWidth:600, template: '<iframe width='100%' frameborder=0 src='".$url."' class='auto-height' allowtransparency='true' scrolling='auto' style='margin:0px; padding:0px;'></iframe> '}]
";

$tpl->set("admcontent", $wjs);
$tpl->set('adminscripts', ""); 
$tpl->set("toolbar", "");
$tpl->set("utilmenu", "");
$ready = true;