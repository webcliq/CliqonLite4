<?php
// FileMan.php
// Business Logic for fileman.tpl - File Manager using elfinder or fileman

// Make calls to database when possible
$tpl->set('lcd', $lcd);
$clqadm = new clqadmin();
// Column one, menu
$tpl->set("admmenu", $clqadm->publishMenu('fileman'));

// Column two - List
$tpl->set("admcontent", $clqadm->publishFileMan());
$tpl->set('adminscripts', ""); 

$tpl->set("toolbar", "");
$tpl->set("utilmenu", "");
$ready = true;