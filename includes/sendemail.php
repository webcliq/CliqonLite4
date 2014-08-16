<?php 
ob_start();
$rootpath = "../"; 
require($rootpath.'includes/gateway.php');

// Create Mail Form and Send
// Success Text: $stxt

$stxt = "";
$stxt .= $linetext." \n";

if(isset($_REQUEST['name'])) {$stxt .= $clq->cStr('12:Full Name').": ".$_REQUEST['name']."<br /> \n";};
if(isset($_REQUEST['phone'])) {$stxt .= $clq->cStr('14:Telephone').": ".$_REQUEST['phone']."<br /> \n";};
if(isset($_REQUEST['email'])) {$stxt .= $clq->cStr('13:Email').": ".$_REQUEST['email']."<br /> \n";};

$stxt .= $clq->cStr('17:Subject').": ".$_REQUEST['subject']."<br /> \n";
$stxt .= $clq->cStr('15:Message').": ".$_REQUEST['message']."<br /> \n";

$sbj = $_REQUEST['subject'];


ob_end_clean();

$pos = strpos($res, "OK");
if($pos !== false) {
	echo "Success";
} else {
	echo "Failed";
}
