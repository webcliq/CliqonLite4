<?php
$clqdbm = new clqmysql(true, $db['db'], $db['server'], $db['user'], $db['password']);

$body = '<style media="print"> 
.noprint {display:none;} 
</style>
<style>
.items {padding-right: 10px;}
</style>';
$body .= "<div style='width:960px; border: 1px #C5C5C5 solid; margin-top:5px; padding:0px; minheight:4000px' id='admincontent' class='round5 white'>";

// Header
$body .= "	<div style='height: 26px; padding: 5px;' id='header'>";

// Right
$body .= "		<div style='float:right; width:400px; text-align:right; padding: 5px 10px 0px 0px;' id='hdrright'>";
$body .= 			"<img src='".$adminpath."view/images/icons/printer.png' id='print_button' title='".$clq->cStr('str(999)', 'Print')."' class='icons'  />";
$body .= "		</div>";

// Left
$body .= "		<div style='width:500px;' id='hdrleft'>";
$body .= "			<h2>".$clq->cStr('str(999)', 'Database Schema')."</h2>";
$body .= "		</div>";

$body .= "	</div>";

// Form
$body .= "	<div id='mcolumns' style=' margin-left: 20px; padding:0px;'>";
	$tables = $clqdbm->GetTables();
	foreach($tables as $table) {
		$body .= "<div style='width:160px; float:left;".$rowcol."' class='items'>";
		$body .= "	<div style='height:30px; ' class=''><h3 style='margin:0px; padding: 0px;'>".$table."</h3></div>";
		
				$columns = $clqdbm->GetColumnNames($table);
				$body .= "<div style='margin-bottom: 10px;'>";
				foreach($columns as $columnname) {
					$body .= $columnname."<br />";
				}
				$body .= "</div>";
		$body .= "</div>";		
	}	
$body .= "	</div>";
$body .= "</div>";

$body .= "
<script language='javascript' type='text/javascript'>
<!--//
$(function() {

	// Load jQuery File Dynamically that are required for Admin
	$.scriptPath = '".$rootpath."js/';

	// jQuery Scripts
 	jQuery.require([
		'masonry.js'
	]);	
	
	$('#print_button').click(function(){
		$('#admincontent').jqprint();
	});	
		
	$('#mcolumns').masonry({
  		itemSelector: '.items',
	});
	
});	
//-->
</script>
";
?>