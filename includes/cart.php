<?php 
/**
* Supports CliqonLite Cart Functions
*/

// Loads Config and Clq Class plus basic stuff
$rootpath = "../";
require_once($rootpath."includes/gateway.php");
require_once($rootpath."includes/classes/clqcart.class.php");
require_once($rootpath."config/cart_config.php");
require_once($rootpath."config/countries.php");

$clqcart = new clqcart();
$rq = $_REQUEST;
ob_start(); ob_end_clean();
$msg = "No action specified";
if(isset($_REQUEST['action'])) {$action = $_REQUEST['action'];} else { die($msg); };

switch($action) {

	case "stockdetails":

		// Get details for a Stock Code and return 
		header('Content-type: application/json');
		$sql = "SELECT * FROM clqdata WHERE clq_langcd = ? AND clq_reference = ? AND clq_type = ?";
		$row = R::getRow($sql, array($lcd, $rq['ref'], 'catalog'));
		$rs = array();
		foreach($row as $key => $val) {
			if($key != 'clq_extra') {
				
				$rs[$key] = $clqcart->chgNum($val);
			} else {
				$x = json_decode($val, true);
				foreach($x as $xkey => $xval) {
					$rs[$xkey] = $xval;
				}
			}
		}
		$result = json_encode($rs);
	break;
		
	case "processorder":

		$token = $clqcart->setCheckout($rq);
		if($token == "") {
			$result = " - error with Token";
		} else {
			// We can move forward
			$result = $clqcart->goPayPal($token);    	
		}
	break;

	case "paypalresult":

		$token = $rq['token'];		// EC-3BX25222FW447363
		$payerid = $rq['PayerID'];	// VBRU3UE8FNAK8
		$printout = "Table";

		if($token == "") {
			
			$result = "Error with Payer ID and Token"; 
			
		} else { // We can move forward
						
			$result = $clqcart->doCheckout($token, $payerid);
			if($result) {
				$printout = $clqcart->onSuccess($result);

			} else {
				// Error with PayPal DoCheckout Step
				ob_clean(); 
				header("Location: ".$rootpath."includes/cart.php?langcd=".$lcd."&action="); 
			};
			
			// $_SESSION['CLQ_ThisOrder'] = $_GET;
			// echo $clqpaypal->onSuccess();
			$result = $printout;
		}
	break;

	case "paypalcancel": 

		// http://fv.cliqon.net/includes/cart.php?langcd=en&action=paypalcancel&token=EC-67F580892M607800W
		// Close the window and set the content of the Alert Message

		$result = "
		<script type='text/javascript'>
		<!--//
		alertMsg = 'Cancel';
		window.onload = myOnloadFunc;
		function myOnloadFunc() {
			window.close();
		}
		//-->
		</script>
		"; 
	break;	

	case "paypalreturn":  
		
		// http://cliqonlite.com//includes/cart.php?langcd=en&action=paypalreturn&token=EC-97U97235NR802773J&PayerID=VBRU3UE8FNAK8

		if(array_key_exists("token", $rq)) {

			$table = $clqcart->onResult($rq);

			$result =  html::set_header("charset=utf8","language=en","title=Print Order");
			$result =  html::set_header("css=/views/css/style.css");
			$result .= html::display_header();
			$result .= html::body();
			$result .= html::remark("Print Order Table");
			$result .= html::div("class=pad", "txt=".$table);
			$result .= html::finish();
		} else {
			$result = "Problem with PayPal: ".$rq;
		}

	break;

	case "printorder":
		$table = $clqcart->printOrder($_SESSION['CLQ_ThisOrder']);
		$result =  html::set_header("charset=utf8","language=en","title=Print Order");
		$result =  html::set_header("js=/includes/js/cliqon.js", "css=/views/css/style.css");
		$result .= html::display_header();
		$result .= html::body();
		$result .= html::remark("Print Order Table");
		$result .= html::div("class=pad", "txt=".$table);
		$result .= html::finish();
	break;

	default: 
		$result = $action.":".$msg;
	break;
}

echo $result;