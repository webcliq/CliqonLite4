<?php
// Jsoneditor.php
$json = R::getCell("SELECT clq_extra FROM ".$rq['table']." WHERE id = ?", array($rq['recid']));

$tpl->set('json', $json);
$tpl->set('table', $rq['table']);
$tpl->set('recid', $rq['recid']);
$tpl->set('lcd', $rq['langcd']);