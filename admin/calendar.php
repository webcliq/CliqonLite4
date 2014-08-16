<?php
// Calendar.php
// Business Logic for Calendar.tpl

// Make calls to database when possible
$tpl->set('lcd', $lcd);
$clqadm = new clqadmin();
// Column one, menu
$tpl->set("admmenu", $clqadm->publishMenu('event')); // maybe calendar

// Column two - Calendar
$tpl->set("admcontent", $clqadm->publishCalendar($rq['table'], $rq['tabletype']));
$tpl->set('adminscripts', $clqadm->getAdminScripts("calendar", $rq['tabletype'])); 

// In footer
$tpl->set('toolbar', $clqadm->publishToolbar($rq['tabletype']));
$tpl->set("utilmenu", $clqadm->publishUtilMenu($rq['tabletype']));
$ready = true;