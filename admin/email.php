<?php
// Email.Php
// Business Logic for Email.tpl

// Make calls to database when possible
if($lcd == "") {$lcd = "en";};
$tpl->set('lcd', $lcd);
$clqadm = new clqadmin();
// Column one, menu
$tpl->set("admmenu", $clqadm->publishMenu("email"));
$ready = true;
