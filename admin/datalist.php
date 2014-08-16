<?php
// DataList.php
// Business Logic for DataList.tpl

// Make calls to database when possible
$tpl->set('lcd', $lcd);
$clqadm = new clqadmin();
// Column one, menu
$tpl->set("admmenu", $clqadm->publishMenu($rq['tabletype']));

// Column two - Dashboard 
$tpl->set('datalist', $clqadm->publishDataList()); 
$tpl->set('adminscripts', ""); 

$tpl->set("toolbar", "");
$tpl->set("utilmenu", "");
$ready = true;
