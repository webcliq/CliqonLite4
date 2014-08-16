<?php
// RepGen.php
// Business Logic for RepGen.tpl - Report Generator using reportico

// Make calls to database when possible
$tpl->set('lcd', $lcd);
$clqadm = new clqadmin();
// Column one, menu
$tpl->set("admmenu", $clqadm->publishMenu('repgen'));

// Column two and three - List plus space for form etc
$tpl->set("admcontent", $clqadm->publishRepGen());
$tpl->set('adminscripts', $clqadm->getAdminScripts("repgen", "report")); 

// In footer
$tpl->set('toolbar', $clqadm->publishToolbar('report'));
$tpl->set("utilmenu", $clqadm->publishUtilMenu('report'));
$ready = true;

