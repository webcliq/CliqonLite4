<?php
// Gallery.php
// Business Logic for Gallery.tpl

// Make calls to database when possible
$tpl->set('lcd', $lcd);
$clqadm = new clqadmin();
// Column one, menu
$tpl->set("admmenu", $clqadm->publishMenu('image'));

// Column two - List
$tpl->set("admcontent", $clqadm->publishGallery());
$tpl->set('adminscripts', $clqadm->getAdminScripts('gallery')); 
$tpl->set('toolbar', $clqadm->publishToolbar("image"));
$tpl->set("utilmenu", $clqadm->publishUtilMenu("image"));
$ready = true;
