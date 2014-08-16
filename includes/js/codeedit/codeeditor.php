<?php
$rootpath = "../../";
require_once($rootpath."lib/gateway.php");
require ($rootpath."lib/classes/clqhtml.class.php");
require ($rootpath."lib/classes/clqcodedit.class.php");
$clqce = new clqcodedit();
echo $clqce->publishEditor();
