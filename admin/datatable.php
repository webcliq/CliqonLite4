<?php
// DataTable.php
// Business Logic for DataTable.tpl

// Make calls to database when possible
$tpl->set('lcd', $lcd);
$clqadm = new clqadmin();

// Column one, menu
$tpl->set("admmenu", $clqadm->publishMenu($rq['tabletype']));

// Column two - Dashboard or Table
$tpl->set("datatable", $clqadm->publishDatatable($rq['table'], $rq['tabletype']));
$tpl->set('adminscripts', $clqadm->getAdminScripts("datatable", $rq['tabletype'])); 

// In footer
$tpl->set('toolbar', $clqadm->publishToolbar($rq['tabletype']));
$tpl->set("utilmenu", $clqadm->publishUtilMenu($rq['tabletype']));
$ready = true;