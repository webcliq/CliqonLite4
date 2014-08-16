<?php
// Dashboard.Php
// Business Logic for Dashboard.tpl

// Make calls to database when possible
if($lcd == "") {$lcd = "en";};
$tpl->set('lcd', $lcd);
$clqadm = new clqadmin();
// Column one, menu
$tpl->set("admmenu", $clqadm->publishMenu("dashboard"));

// Column two - Dashboard 
$tpl->set('dashboard', $clqadm->publishDashboard()); 
$tpl->set('adminscripts', $clqadm->getAdminScripts("dashboard", "")); 

$tpl->set("toolbar", "");
$tpl->set("utilmenu", $clqadm->publishUtilMenu(""));
$ready = true;
