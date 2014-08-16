<?php
// Jsoneditor.php
$json = R::getCell("SELECT clq_extra FROM ".$rq['table']." WHERE id = ?", array($rq['recid']));
if(stripos($json, "{") !== false) {$tpl->set('json', $json);} else {$tpl->set('json', base64_decode($json));};
$tpl->set('table', $rq['table']);
$tpl->set('recid', $rq['recid']);
$tpl->set('lcd', $rq['langcd']);