<?php
require($rootpath."classes/clqidiom.class.php");
$clqidm = new clqidiom();
$body = "
<style>
	@import url('".$rootpath."admin/style/uniform_base.css');
	@import url('".$rootpath."admin/style/uniform.default.css');
	div .label_field {text-align:right; width: 120px; float: left;}
	div .form_row {margin-left: 10px; clear: left; margin-bottom:5px;}
	div .form_field {width: 300px; vertical-align: top; margin-left: 125px;}
	.missingicon{vertical-align: middle; height: 16px; padding: 0px 1px 0px 5px;} .missingbutton {cursor:pointer;}	
</style>
";

$body = "<div style='width:960px; border: 1px #C5C5C5 solid; margin-top:5px; padding:0px;' class='round5 white'>";

// Header
$body .= "	<div style='height: 26px; padding: 5px; margin-top:5px;' id='chkheader'>";

// Right
$body .= "		<div style='float:right; width:400px; text-align:right; padding: 5px 10px 0px 0px;' id='chkhdrright'>";
$body .= 			"button";
$body .= "		</div>";

// Left
$body .= "		<div style='width:500px;' id='chkhdrleft'>";
$body .= "			<h2>".$clq->cStr('str(138)','Check Missing')."</h2>";
$body .= "		</div>";

$body .= "	</div>";

$body .= "	<div id='chkbodyleft' style='min-height:430px; width:600px;'>";
$body .= 		$clqidm->displayMissing();
$body .= "	</div>";
$body .= "</div>";

$body .= "<div id='cmenu'></div>";
$body .= $clqidm->displayMissingJS();
	

