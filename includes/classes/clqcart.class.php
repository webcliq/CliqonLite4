<?php 
// Cliqon Cart Class

class clqcart extends clq {

	public $thisclass="clqcart";
	public $db, $lcd, $rootpath, $icnpath, $sitepath, $scripts, $sendto;
	public $cfg = array();
	public $imgdir = "views/catalog/";
	public $clqschema = array();
	public $admschema = array();
	public $table = "clqdata", $type = "catalog"; 
	public $lstr = array();
	public $cartcfg			= array();
	public $shopcfg			= array();
	public $clqcurl			= "";
	public $countries		= array();

	function __construct(){

		global $rootpath; $this->rootpath = $rootpath;
		$this->icnpath = "admin/theme/icons/";
		global $sitepath; if(!$sitepath) {$this->sitepath = $_SESSION['CLQ_Sitepath'];} else {$this->sitepath = $sitepath;}; 
		global $cfg; if(!$cfg) {$this->cfg = $_SESSION['CLQ_Config'];} else {$this->cfg = $cfg;};
		global $lcd; if(!$lcd) {$this->lcd = $_SESSION['CLQ_Langcd'];} else {$this->lcd = $lcd;}; 
		global $schema; $this->clqschema = $schema; 
		global $cartcfg; $this->cartcfg = $cartcfg;
		global $shopcfg; $this->shopfg = $shopcfg;	
		global $clq_countries; $this->countries = $clq_countries;

		require_once($this->rootpath."config/clqadmschema.cfg");
		$this->admschema = $admschema;

		// Cliqon Lite language handler
		require_once($this->rootpath."includes/classes/i18n/cliqon.".$this->lcd.".lcd");
		$this->lstr = $lstr;

		// R::debug(true);
		if(R::inspect('cartlog') == false ) {
			$str = R::dispense("cartlog");
			$str->cart_id = "0";
			$str->cart_date = date("Y-m-d"); 
			$str->cart_client = ""; 
			$str->cart_products = "";
			$str->cart_summary = ""; 
			$str->cart_orderno = ""; 
			$str->cart_instructions = ""; 
			$str->cart_extra = "";  
			$str->cart_status = "initialised"; 
			$str->cart_notes = ""; 
			$id = R::store($str);	
		}
		// R::debug(false);
	}
	
	// Test Class is accessible
	function testPayPal() { $str = "Test PayPal"; return $str; }
	
	/******************************************** Any routines for SmartCart Shop ************************************************/

		function chgNum($txt) {
			
			$str = $txt;
			$str = str_replace(".", "", $str);
			$str = str_replace(",", "", $str);
			if(!is_numeric($str)) {
				return $txt;
			} else {
				$num = str_replace(".", "", $txt);
				$number = str_replace(",", ".", $num);
				return number_format((float)$number, 2, '.', '');
			}
		}	
		
		function printOrder($rq, $payid = null, $token = null, $transid = null) {
			
			date_default_timezone_set($this->config['site.timezone']);
			$date = new DateTime();
			$ddate = $date->format('d-M-Y'); 

			// Table layout array
			/*
			'header.backcolor'	=> '#000',
	  		'header.color'		=> '#fff',
	  		'header.image'		=> '',
	  		'header.coname'		=> 'Conkas',
			*/
			$tbl = 	$this->cartcfg['cart.table'];

			// Design and populate table
			$table = '
			<style>
			.hdricons {
				height:20px;
				margin-top: 20px;
				cursor:pointer;
				padding: 3px;
			}
			</style>
			<table width="100%" cellpadding="0" cellspacing="0" id="resulttable" class="clqtable clqtable-bordered" style="">
				<thead class="" style="background-color:'.$tbl['header.backcolor'].'; color: '.$tbl['header.color'].';">
					<tr>
						<td colspan=4 class="" style="margin-right:20px;">
							<img src="/includes/icons/white/delete_icon&24.png" align="right" class="hdricons" onClick="window.close()" id="closebutton" title="'.$this->lstr[29].'" alt="'.$this->lstr[29].'" />				
							<img src="/includes/icons/white/print_icon&24.png" align="right" class="hdricons" id="printbutton" onClick="window.print()" title="'.$this->lstr[64].'" alt="'.$this->lstr[64].'" />
							<h2>'.$tbl['header.coname'].'</h2></td>
					<tr>
					<tr>
						<td colspan=4>'.$this->cartcfg['cart.coaddress'].'</td>
					<tr>
				</thead>
			
				<tbody>
					
					<!-- Customer address and Order Header -->
					<tr>
						<td style="border:0;">
							<span>
								<strong style="margin-bottom:5px;">'.$this->lstr[415].'</strong><br />
								'.$rq['name'][0].' '.$rq['name'][1].' '.$rq['name'][2].'<br />
								'.$rq['address'].'	
							</span>		
						</td>
						<td>			
							<table class="clqtable clqtable-striped">
								<tr>
									<td nowrap="nowrap" align="right" class="label">'.$this->lstr[31].'</td>
									<td>'.$_SESSION['CLQ_ThisOrder_CartID'].'</td>
								</tr>
								<tr>
									<td nowrap="nowrap" align="right" class="label">'.$this->lstr[60].'</td>
									<td>'.$ddate.'</td>
								</tr>				
								<tr>
									<td nowrap="nowrap" align="right" class="label">'.$this->lstr[152].'</td>
									<td>'.$rq['telephone'].'</td>
								</tr>
								<tr>
									<td nowrap="nowrap" align="right" class="label">'.$this->lstr[163].'</td>
									<td>'.$rq['email'].'</td
								</tr>
								<tr>
									<!-- Add transaction to CartLog  -->
									<td nowrap="nowrap" align="right" class="label" valign="top">'.$this->lstr[341].'</td>
									<td>'.$token.':'.$payid.':'.$transid.'</td>
								</tr>
							</table>
						</td>
					<tr>

					<tr>
						<td colspan=4>
							<table class="clqtable">
								<thead class="" style="background-color:'.$tbl['header.backcolor'].'; color: '.$tbl['header.color'].';">
									<tr >
										<!-- Reference and Description  -->
										<td>'.$this->lstr[48].'</td>
										<td>'.$this->lstr[204].'</td>

										<!-- Qty, Price, Value, Delivery Charge, Tax, Line Total (Extended)-->
										<td  align="right">'.$this->lstr[401].'</td>
										<td align="right">'.$this->lstr[88].'</td>
										<td align="right">'.$this->lstr[59].'</td>
										<td align="right">'.$this->lstr[407].'</td>
										<td align="right">'.$this->lstr[417].'</td>
										<td align="right">'.$this->lstr[300].'</td>

										<!-- Lead Time -->
										<td>*</td>
									</tr>
								</thead>

								<tbody>
								'; 
			
								// Calculate Leadtime Message here
								$leadtime = $this->lstr[416];
								$leadtimeval = 5;
								
								// Create Product Entry
								$subtotal = 0; $shipping = 0; $tax = 0; $i = 1; $lnarray = array();
								$ol = $rq["orderline"]; 
								// array(2) { [0]=> string(43) "CDE100|Apple Macbook Notebook PC|1|1.505,00" [1]=> string(42) "CDE101|Sony VAIO 11 Notebook PC|1|1.704,00" }	
								
								$i = 1;
								foreach($ol as $n => $oi) {
									
									$t = explode('|', $oi);

									// Get item info from Stock items in db 
									$sql = "SELECT * FROM clqdata WHERE clq_reference = ? and clq_langcd = ?";
									$row = R::getRow($sql, array($t[0], $this->lcd));
									
									// Convert extra into array
									$xrow = json_decode($row['clq_extra'], true);

									// Update Leadtime Val
									//$leadtime = str_replace("{n}", $leadtimeval, $leadtime);
									//$leadtimeval = $this->getLeadTime($t[0], $leadtimeval);

									// Calculate itemshipping multiplying delivery cost by quantity
									$delivery = (clq::urlNumber($xrow['delivery']) *  $t[2]);
									
									// Tax rate as 1.n
									$taxrate = floatval('1.'.$xrow['taxrate']); 
									
									// Calculate item value by multiplying item price by quantity
									$itemprice = (clq::urlNumber($row['clq_value']) * $t[2]);
									
									if($xrow['taxincl'] == 'y') {
										
										// If tax included
										$itemvalue = (float)$itemprice  * (1 / (float)$taxrate);
										$itemshipping = (float)$delivery * (1 / (float)$taxrate);
										$totalnetitem = (float)((float)$itemvalue + (float)$itemshipping);
										$itemtax = (float)((float)$itemprice + (float)$delivery) - (float)$totalnetitem;
										

									} else { 
										
										// Add tax
										$itemvalue = $itemprice;
										$itemshipping = $delivery;
										$totalnetitem = (float)((float)$itemvalue + (float)$itemshipping);
										$itemtax = (float)((float)$itemprice + (float)$delivery) * (float)$taxrate;			
												
									}
									
									$totalnetitem = (float)$itemvalue;
									$totalgrossitem = $itemvalue + $itemshipping + $itemtax;
									
									// Order Lines
									$table .= '
									<tr>

										<!-- Reference -->
										<td>'.$t[0].'</td>

										<!-- Description -->
										<td>'.$t[1].'</td>

										<!-- Quantity -->
										<td align="right"><span style="text-align:right;">'.$t[2].'</span></td>

										<!-- Price -->
										<td align="right">'.clq::formatCurrency($row["clq_value"]).'</td>

										<!-- Value (Price x quantity) -->
										<td align="right">'.clq::formatCurrency($itemvalue).'</td>

										<!-- Delivery Charge -->
										<td align="right">'.clq::formatCurrency($itemshipping).'</td>

										<!-- Tax Element (Included or Exxclusive) -->
										<td align="right">'.clq::formatCurrency($itemtax).'</td>

										<!-- Line Total -->
										<td align="right">'.clq::formatCurrency($totalgrossitem).'</td>

										<!-- Delivery or Lead Time -->
										<td>'.$xrow["leadtime"].'</td>

									</tr>'; 
									$i++;				
								
									$subtotalnet = $subtotalnet + $itemvalue;
									$shipping = $shipping + $itemshipping;				
									$tax = $tax + $itemtax;
									$total = $total + $totalgrossitem;					
								}

								if((+$subtotalnet +$tax +$shipping) !== +$total) {
									if( (+$subtotalnet +$tax +$shipping) > +$total) {
										+$tax = +$tax - 0.01;
									}  
									if( (+$subtotalnet +$tax +$shipping) < +$total) {
										+$tax = +$tax + 0.01;
									} 				
								}
							
								$table .= '
								</tbody>
							</table>
						</td>
					<tr>
				</tbody>
						
				<tfoot>
					<tr>
						<td valign="top" style="vertical-align:top;">
							<span>
								<strong style="margin-bottom:5px;">'.$this->lstr[162].'</strong><br />			
								'.$leadtime.'<br />
								'.$rq['notes'].'	
							</span>
						</td>
						<td valign="top">			
							<table width="100%" cellpadding="3">
								<tr>
									<td nowrap="nowrap" align="right" class="label">'.$this->lstr[409].'</td>
									<td align="right">'.number_format((float)$subtotalnet, 2, '.', '').'€</td>
								</tr>
								<tr>
									<td nowrap="nowrap" align="right" class="label">'.$this->lstr[410].'</td>
									<td align="right">'.number_format((float)$shipping, 2, '.', '').'€</td>
								</tr>
								<tr>
									<td nowrap="nowrap" align="right" class="label">'.$this->lstr[413].'</td>
									<td align="right">'.number_format((float)$tax, 2, '.', '').'€</td>
								</tr>	
								<tr>
									<td nowrap="nowrap" align="right" class="label">'.$this->lstr[412].'</td>
									<td align="right">'.number_format((float)$total, 2, '.', '').'€</td>
								</tr>			
							</table>
						</td>
					<tr>
				</tfoot>		
			</table>
			'; 	
			return $table;
		}	
		
	/******************************************** Routines for PayPal Express Checkout *******************************************/
	
		/* Step 1 - Get a Paypal Token */
		public function setCheckout($rq) {
			
			$authcfg = $this->cartcfg['cart.paymentgateway'];
			// var_dump($this->cartcfg);
			$token = "";	
			$env = self::PayPalEnvironment();	
			$ord = self::orderDetails($rq);
			
			if($authcfg['paypal.sandbox'] == 'sandbox') {
				$PayPalUrl = "https://api-3t.sandbox.paypal.com/nvp?";
			} else {
				$PayPalUrl = "https://api-3t.paypal.com/nvp?";
			}
							
			$this->clqcurl = new clqcurl($PayPalUrl);	
			$this->clqcurl->post($env.$ord);
			$result = urldecode($this->clqcurl->execute()); // will get back the result	

			// Test
			$env = str_replace("&", PHP_EOL."<br />&", $env);
			$ord = str_replace("&", PHP_EOL."<br />&", $ord);
			clq::writeLog("String sent to Paypal to get a token", $PayPalUrl.$env.$ord);
			clq::writeLog('PayPal_Raw_Result', $result);
			// echo "Paypal URL: ".$PayPalUrl."<br />";
			// echo "PP Environment: ".$env."<br />";
			// echo "PP Order: ".$ord."<br />";
			// echo "PayPal_Raw_Result: ".$result;
						
			$token = self::parsePPresult($result, "TOKEN");
			if($token != "") {
				$_SESSION['CLQ_ThisOrder'] = $rq;
			}
			return $token;
		}

		/* Step 2 - Payment Confirmed - display thankyou message and write result to Log - not convinced */
		public function onResult($result) {
			
			// Parse though result to get Transaction ID
			$split = explode("&", $result); $ppres = array();
			foreach($split as $n => $line) {
				$ln = explode("=", $line);
				$ppres[$ln[0]] = $ln[1];
			}
			$transid = $this->parsePPresult($result, "TRANSACTIONID");
			$payid = $this->parsePPresult($result, "PayerID");
			$token = $this->parsePPresult($result, "token");		
			$ack = $this->parsePPresult($result, "ACK");

			// Write result to log
			clq::writeLog("Do Checkout Response", $result);	

			$ord = $_SESSION['CLQ_ThisOrder'];	
			self::addLog($ord);
			
			$table = self::printOrder($ord, $payid, $token, $transid);
		
			// Send Email
			$table .= '<p style="margin-top:5px;">'.$this->sendEmail($table).' : '.$ack.'</p>';
			
			$_SESSION['CLQ_ThisOrder'] = null; 
			$_SESSION['CLQ_ThisOrder_CartID'] = null;
			
			return $table;	
		}

	/******************************************** Routines for PayPal Traditional Route *****************************************/

		/* Step 2 - Log in to PayPal */
		public function goPayPal($token) {
			$_SESSION['CLQ_ThisOrder'] = $_REQUEST;
			
			if($this->cartcfg['cart.paymentgateway']['paypal.sandbox'] == 'sandbox') {
				$PayPalUrl = "https://www.sandbox.paypal.com/cgi-bin/webscr?";
			} else {
				$PayPalUrl = "https://www.paypal.com/cgi-bin/webscr?";
			}
			$str = "cmd=_express-checkout&token=".$token;
			header("Location: ".$PayPalUrl.$str);							
		}
		
		/* Step 3 - We have a Token and the Buyer has logged in etc.  */
		public function getCheckout($token) {
				
			// Start with API Stuff
			$authcfg = $this->cartcfg['cart.paymentgateway'];
			$Auth = "USER=".urlencode($authcfg['paypal.api.username'])."&PWD=".urlencode($authcfg['paypal.api.password'])."&SIGNATURE=".urlencode($authcfg['paypal.api.signature'])."&VERSION=".urlencode('7.4')."&METHOD=GetExpressCheckoutDetails&TOKEN=".urlencode($token);
			
			if($authcfg['paypal.sandbox'] == 'sandbox') {
				$PayPalUrl = "https://api-3t.sandbox.paypal.com/nvp";
			} else {
				$PayPalUrl = "https://api-3t.paypal.com/nvp";
			}		
		}
		
		/* Step 4- Do Checkout and get success or failure */
		public function doCheckout($token, $payerid) {

			// Start with API Stuff
			$authcfg = $this->cartcfg['cart.paymentgateway'];
			
			// Calculate a Total by obtaining the items array out of the request
			$rq = $_SESSION['CLQ_ThisOrder'];
			$ol = $rq["orderline"]; // array(2) { [0]=> string(43) "CDE100|Apple Macbook Notebook PC|1|1.505,00" [1]=> string(42) "CDE101|Sony VAIO 11 Notebook PC|1|1.704,00" }	
			$total = 0; $token = "";
			foreach($ol as $n => $oi) {
				$t = explode('|', $oi);
				// echo self::chgNum($t[3]);
				$total = $total + (int)self::chgNum($t[3]);
			}

			$str = "USER=".urlencode($authcfg['paypal.api.username'])."&PWD=".urlencode($authcfg['paypal.api.password']).
			"&SIGNATURE=".urlencode($authcfg['paypal.api.signature'])."&VERSION=".urlencode('82')."&PAYERID=".urlencode($payerid).
			"&PAYMENTACTION=Sale&METHOD=DoExpressCheckoutPayment&TOKEN=".urlencode($token)."&AMT=".$total.
			"&CURRENCYCODE=".urlencode($authcfg['paypal.currencyID']);
			
			if($authcfg['paypal.sandbox'] == 'sandbox') {
				$PayPalUrl = "https://api-3t.sandbox.paypal.com/nvp?";
			} else {
				$PayPalUrl = "https://api-3t.paypal.com/nvp?";
			}
			
			$this->clqcurl = new clqcurl($PayPalUrl);	
			$this->clqcurl->post($str);
			
			// Test
			$testresult = $PayPalUrl.$str;
			clq::writeLog('PayPal Result', $testresult);
			// echo $testresult;

			$result = urldecode($this->clqcurl->execute()); // will get back the result		
			return $result;
		}
		
		/* Step 5 - Payment Confirmed - display thankyou message and write result to Log */
		public function onSuccess($result) {
			
			// Parse though result to get Transaction ID
			$split = explode("&", $result); $ppres = array();
			foreach($split as $n => $line) {
				$ln = explode("=", $line);
				$ppres[$ln[0]] = $ln[1];
			}
			$transid = $this->parsePPresult($result, "TRANSACTIONID");		
			$ack = $this->parsePPresult($result, "ACK");
			clq::writeLog("Do Checkout Response", $result);	
			
			$table = "";
			// Test
			// print_r($_SESSION['CLQ_ThisOrder']);
			$rq = $_SESSION['CLQ_ThisOrder'];	
			
			$table .= self::printOrder($rq, $transid);
			self::addLog($rq);			
			
			// Send Email
			$table .= '<p style="margin-top:5px;">'.$this->sendEmail($table).' : '.$ack.'</p>';
			
			$_SESSION['CLQ_ThisOrder'] = null; 
			$_SESSION['CLQ_ThisOrder_CartID'] = null;
			
			return $table;	
		}
	
	/******************************************************** Helper Functions ***************************************************/

		/* Get a Result out of a Payal Result string  */
		public function parsePPresult($result, $label) {
		
			// Parse though result to get Transaction ID
			$split = explode("&", $result); $ppres = array();
			foreach($split as $n => $line) {
				$ln = explode("=", $line);
				$ppres[$ln[0]] = $ln[1];
			}
			$val = $ppres[$label];	
			return $val;	
		}

		/* PayPal Environment */
		public function PayPalEnvironment() {
			
			$str = ''; 

			// Set variable here
			$authcfg = $this->cartcfg['cart.paymentgateway'];
			$urlstr = $this->cfg['site.url']."/includes/cart.php?langcd=".$this->lcd."&action=";

			$str .= 'USER='.urlencode($authcfg['paypal.api.username']).
				'&PWD='.urlencode($authcfg['paypal.api.password']).
				'&SIGNATURE='.urlencode($authcfg['paypal.api.signature']).
				'&VERSION=82'.
				'&SKIPDETAILS=1'.
				'&PAYMENTREQUEST_0_PAYMENTACTION=Sale'.
				'&METHOD=SetExpressCheckout'.
				// '&TOKEN='.
				'&CURRENCYCODE='.$authcfg['paypal.currencyID'].
				'&RETURNURL='.urlencode($urlstr.$authcfg['paypal.returnURL']).
				'&CANCELURL='.urlencode($urlstr.$authcfg['paypal.cancelURL']).
				'&PAGESTYLE='.urlencode($authcfg['paypal.pagestyle']).
				'&HDRIMG='.urlencode($authcfg['paypal.hdrimg']).
				'&HDRBORDERCOLOR='.urlencode($authcfg['paypal.hdrbordercolor']).
				'&HDRBACKCOLOR='.urlencode($authcfg['paypal.hdrbackcolor']).
				'&PAYFLOWCOLOR='.urlencode($authcfg['paypal.payflowcolor']).
				'&SOLUTIONTYPE='.urlencode('Mark').
				'&LANDINGPAGE='.urlencode('Billing').
				'&CHANNELTYPE='.urlencode('Merchant').
				'&BRANDNAME='.urlencode($authcfg['paypal.merchantname']).
				'&CUSTOMERSERVICENUMBER='.urlencode($authcfg['paypal.phonenumber']).
				'&SURVEYNABLE=0'.
				'&NOSHIPPING=2'.
				'&ALLOWNOTE=1'.
				'&ADDROVERRIDE=1'.
				'&LOCALECODE='.strtoupper($this->lcd);

				// This is followed by Order Detail

			return $str;
		}
		
		/* Organise Order Header and Details */
		public function orderDetails($rq) {
			
			/*
	       	'clq_type', 'clq_langcd', 'clq_reference', 'clq_title'     
	        'clq_value'     // Price   
	        'clq_extra'     {"taxincl":"y","taxrate":"21","delivery":"5","instock":0}',   
			*/

			// Create Variable for later use
			$str = ''; 
			$authcfg = $this->cartcfg['cart.paymentgateway']; 
			$custom = "Comments: ".$rq['notes'];
			$fullname = $rq['name'][0] . ' ' . $rq['name'][1] . ' ' . $rq['name'][2];
			$subtotal = 0; $shipping = 0; $tax = 0; $total = 0; $lns = ""; $i = 0;

			foreach($rq['orderline'] as $n => $oi) {
				
				$t = explode('|', $oi);

				// Get item info from Stock items in db 
				$sql = "SELECT * FROM clqdata WHERE clq_reference = ? and clq_langcd = ?";
				$row = R::getRow($sql, array($t[0], $this->lcd));
				
				// Convert extra into array
				$xrow = json_decode($row['clq_extra'], true);
				
				// Calculate itemshipping multiplying delivery cost by quantity
				$delivery = (clq::urlNumber($xrow['delivery']) *  $t[2]);
				
				// Tax rate as 1.n
				$taxrate = floatval('1.'.$xrow['taxrate']); 
				
				// Calculate item value by multiplying item price by quantity
				$itemprice = (clq::urlNumber($row['clq_value']) * $t[2]);
				
				if($xrow['taxincl'] == 'y') { // If tax included
					
					$itemvalue = self::mround((float)$itemprice  * (1 / (float)$taxrate), 2);
					$itemshipping = self::mround((float)$delivery * (1 / (float)$taxrate), 2);
					$totalnetitem = self::mround((float)((float)$itemvalue + (float)$itemshipping), 2);
					$itemtax = self::mround((float)((float)$itemprice + (float)$delivery) - (float)$totalnetitem, 2);
					

				} else { 
					
					// Add tax
					$itemvalue = $itemprice;
					$itemshipping = $delivery;
					$totalnetitem = self::mround((float)((float)$itemvalue + (float)$itemshipping), 2);
					$itemtax = self::mround((float)((float)$itemprice + (float)$delivery) * (float)$taxrate, 2);									
				}
				
				$totalgrossitem = $itemvalue + $itemshipping + $itemtax;
				
				// Order Lines
				$lns .= '&L_PAYMENTREQUEST_0_AMT'.$i.'='.number_format($totalgrossitem, 2, '.', '').	
					'&L_PAYMENTREQUEST_0_NAME'.$i.'='.urlencode($row['clq_reference']).
					'&L_PAYMENTREQUEST_0_NUMBER'.$i.'='.(int)($i+1).
					'&L_PAYMENTREQUEST_0_QTY'.$i.'='.$t[2].
					// '&L_PAYMENTREQUEST_0_TAXAMT'.$i.'='.$itemtax
					'&L_PAYMENTREQUEST_0_DESC'.$i.'='.urlencode($row['clq_title']);						
				
				$i++;
				
				// Creates the running Order totals
				$subtotalnet = $subtotalnet + $itemvalue;
				$shipping = $shipping + $itemshipping;				
				$tax = $tax + $itemtax;
				$total = $total + $totalgrossitem;		

			} // Ends the for each orderline

			// Problem if $rq['ordertotal'] is not the same as totnet + shipping + tax
			$sub = number_format(self::mround((float)$subtotalnet,2), 2, '.', '');
			$sh = number_format(self::mround((float)$shipping,2), 2, '.', '');
			// $net = number_format( self::mround(((float)$sh + (float)$sub),2), 2, '.', '');
			$net = number_format(self::mround(((float)$sub),2), 2, '.', '');
			$tx = number_format(self::mround((float)$tax, 2), 2, '.', '');
			$tot = number_format(self::mround((float)$total, 2), 2, '.', '');

			$desc = $this->lstr[409].": ".$net.", ";
			$desc .= $this->lstr[410].": ".$sh.", ";
			$desc .= $this->lstr[413].": ".$tx.", ";
			$desc .= $this->lstr[412].": ".$tot;	

			// Tests - prints in Window if PayPal not returned	
			// echo $desc;
								
			$str .= '&PAYMENTREQUEST_0_AMT='.$tot.
				'&PAYMENTREQUEST_0_CURRENCYCODE='.$authcfg['paypal.currencyID'].		
				// '&PAYMENTREQUEST_0_ITEMAMT='.$net.
				// '&PAYMENTREQUEST_0_SHIPPINGAMT='.$sh.
				// '&PAYMENTREQUEST_0_TAXAMT='.$tx.	
				'&PAYMENTREQUEST_0_DESC='.urlencode($desc);
				'&PAYMENTREQUEST_0_CUSTOM='.urlencode($desc).
				'&PAYMENTREQUEST_0_CartID='.urlencode($this->getNextCartID()).
				'&PAYMENTREQUEST_0_SHIPTONAME='.urlencode($fullname).	

				/*
				Used on Cliqon but not on Cliqon Lite

				'&PAYMENTREQUEST_0_SHIPTOSTREET='.urlencode($rq['addr1']).
				'&PAYMENTREQUEST_0_SHIPTOSTREET2='.urlencode($rq['addr2']).
				'&PAYMENTREQUEST_0_SHIPTOCITY='.urlencode($rq['city']).
				'&PAYMENTREQUEST_0_SHIPTOSTATE='.urlencode($rq['region']).
				'&PAYMENTREQUEST_0_SHIPTOZIP='.urlencode($rq['postcode']).
				'&PAYMENTREQUEST_0_SHIPTOCOUNTRY='.urlencode(strtoupper($rq['country'])).
				*/

				'&PAYMENTREQUEST_0_SHIPTOPHONENUM='.urlencode($rq['telephone']).
				'&PAYMENTREQUEST_0_NOTETEXT='.urlencode($rq['address']).
				'&PAYMENTREQUEST_0_ALLOWEDPAYMENTMETHOD='.urlencode('InstantPaymentOnly').
				'&PAYMENTREQUEST_0_PAYMENTACTION='.urlencode('Sale').
				'&PAYMENTREQUEST_0_PAYMENTREQUESTID='.urlencode(rand()).
				'&SELLERPAYPALACCOUNTID='.urlencode($authcfg['paypal.merchantID']).
				'&EMAIL='.urlencode($rq['email']);
				
			// Add Lines to Summary
			$str .= $lns; 
			return $str;
		}
		
		/* Get Next Invoice Number from Cart Log */
		public function getNextCartID() {
			
			$nextcartid = rand();
			$sql = "select id, cart_id from cartlog order by cart_id desc limit 1";		
			$lastcartid = self::getSingleRBValue($sql, "cart_id");
			$nextcartid = (+$lastcartid + 1);
			$_SESSION['CLQ_ThisOrder_CartID'] = $nextcartid;
			return $_SESSION['CLQ_ThisOrder_CartID'];
		}
		
		// Get Country Name
		function getCountryName($cc) {
			$countries = array_flip($this->countries);
			return $countries[strtoupper($cc)];
		}
		
		/* Add new entry to Cart Log */
		public function addLog($rq) {
			
			$authcfg = $this->cartcfg['cart.paymentgateway'];
			if(isset($_SESSION['CLQ_ThisOrder_CartID'])) {$cartid = $_SESSION['CLQ_ThisOrder_CartID'];} else {$cartid = rand();}; // Internal Reference
			
			$str = R::dispense("cartlog");
			$str->cart_id = $cartid;
			$str->cart_date = date("Y-m-d"); 
			
			// Create Client Entry
			$client = array(
				
				'name' 			=> $rq['name'][0].' '.$rq['name'][1].' '.$rq['name'][2],
				'address' 		=> $rq['address'], 
				'telno'			=> $rq['telephone'],
				'email'			=> $rq['email'],
				
			);
			$str->cart_client = json_encode($client);
			$str->cart_instructions = $rq['notes']; 
			$this->sendto = $rq['email'];
			// Create Product Entry
			$subtotal = 0; $shipping = 0; $tax = 0; $i = 1; $lnarray = array();
			$ol = $rq["orderline"]; // array(2) { [0]=> string(43) "CDE100|Apple Macbook Notebook PC|1|1.505,00" [1]=> string(42) "CDE101|Sony VAIO 11 Notebook PC|1|1.704,00" }	
			
			foreach($ol as $n => $oi) {
				
				$t = explode('|', $oi);

				// Get item info from Stock items in db 
				$sql = "SELECT * FROM clqdata WHERE clq_reference = ? and clq_langcd = ?";
				$row = R::getRow($sql, array($t[0], $this->lcd));
				
				// Convert extra into array
				$xrow = json_decode($row['clq_extra'], true);
				
				// Calculate itemshipping multiplying delivery cost by quantity
				$delivery = (clq::urlNumber($xrow['delivery']) *  $t[2]);
				
				// Tax rate as 1.n
				$taxrate = floatval('1.'.$xrow['taxrate']); 
				
				// Calculate item value by multiplying item price by quantity
				$itemprice = (clq::urlNumber($row['clq_value']) * $t[2]);
				
				if($xrow['taxincl'] == 'y') {
					
					// If tax included
					$itemvalue = (float)$itemprice  * (1 / (float)$taxrate);
					$itemshipping = (float)$delivery * (1 / (float)$taxrate);
					$totalnetitem = (float)((float)$itemvalue + (float)$itemshipping);
					$itemtax = (float)((float)$itemprice + (float)$delivery) - (float)$totalnetitem;
					

				} else { 
					
					// Add tax
					$itemvalue = $itemprice;
					$itemshipping = $delivery;
					$totalnetitem = (float)((float)$itemvalue + (float)$itemshipping);
					$itemtax = (float)((float)$itemprice + (float)$delivery) * (float)$taxrate;			
							
				}
				
				$totalnetitem = (float)$itemvalue;
				$totalgrossitem = $itemvalue + $itemshipping + $itemtax;
				
				// Order Lines
				$lnarray["line_".$i] = array(
					'stockcode' 		=> $row['clq_reference'],
					'name' 				=> $row['clq_title'],
					'quantity'			=> $t[2],
					'shipping' 			=> $itemshipping,
					'additional_info' 	=> '',
					'options' 			=> '',
					'price'				=> $itemvalue,			
				); 

				$i++;
				
				$subtotalnet = $subtotalnet + $itemvalue;
				$shipping = $shipping + $itemshipping;				
				$tax = $tax + $itemtax;
				$total = $total + $totalgrossitem;						
										
			}			
					
			$str->cart_products = json_encode($lnarray);
		
			// Summary
			$sum = array(
				'subtotal' => $subtotalnet,
				'shipping' => $shipping,
				'tax' => $tax,
				'total' => $total
			);
			$str->cart_summary = json_encode($sum); 
						
			$str->cart_extra = "";  
			$str->cart_status = "submitted"; 
			$str->cart_notes = "No internal Notes"; 
			$id = R::store($str);				
					
			// $str = "Log Entry Created: ".$id.", ".$this->createTask($_POST, $cartid);
			// Mail Log entry
			return "Log Entry Created: ".$id;				
			return $id;	
		}
		
		/* Update Cart Log with new status */
		public function updateLog($cartid, $status) { self::getSingleRBRow($sql); }

		/* Create an entry in the Cart Log based on the POST variables */	
		public function addCartToLog() {
			
			$cartid = $this->getCartID();
			$str = R::dispense("cartlog");
			$str->cart_id = $cartid;
			$str->cart_date = date("Y-m-d"); 
			
			// Create Client Entry
			$client = array(
				
				'name' 			=> $_POST['fullname'],
				'companyname'	=> $_POST['companyname'],
				'regno'			=> $_POST['regno'],
				'address' 		=> $_POST['address'],
				'telno'			=> $_POST['telno'],
				'email'			=> $_POST['email'],
				
			);
			$str->cart_client = json_encode($client);
			$str->cart_orderno = $_POST['orderno']; 
			$str->cart_instructions = $_POST['comments']; 
				
			// Create Product Entry
			$lnarray = array();
			for ($q = 1; $q < 21; $q++) {
				if(isset($_POST['NAME_'.$q])) {
					
					$lnarray["line_".$q] = array(
					
						'stockcode' 		=> $_POST['ID_'.$q],
						'name' 				=> $_POST['NAME_'.$q],
						'quantity'			=> $_POST['QUANTITY_'.$q],
						'shipping' 			=> $_POST['SHIPPING_'.$q],
						'additional_info' 	=> $_POST['ADDTLINFO_'.$q],
						'options' 			=> $_POST['on0_'.$q],
						'price'				=> $_POST['PRICE_'.$q],
					
					);

				}
			};
			$str->cart_products = json_encode($lnarray);
		
			// Summary
			$sum = array(
				'subtotal' => $_POST['SUBTOTAL'],
				'shipping' => $_POST['SHIPPING'],
				'tax' => $_POST['TAX'],
				'total' => $_POST['TOTAL']
			);
			$str->cart_summary = json_encode($sum); 
						
			$str->cart_extra = "";  
			$str->cart_status = "submitted"; 
			$str->cart_notes = "No internal Notes"; 
			$id = R::store($str);				
					
			// $str = "Log Entry Created: ".$id.", ".$this->createTask($_POST, $cartid);
			return "Log Entry Created: ".$id;		
		}
		
		/* Send an Email with Content as Table  */
		public function sendEmail($table) {
			
			$sbj = $this->lstr[31];
			// Start with Confirmation
			$rmsg = self::sendMail($this->sendto, $sbj, "", $table)."\n";
			return $rmsg;	
		}
		
		function getLeadTime($stref, $currleadtimeval) {
			
			$newleadtimeval = $currleadtimeval;
			// CHANGE
			$sql = "SELECT clq_extra FROM clqdata WHERE clq_reference = ? and clq_langcd = ?";
			$result = R::getCell($sql, array($stref, $this->lcd));
			$xtra = json_decode($result, true);
			
			if($xtra['delivery'] > $currleadtimeval) {
				$newleadtimeval = $xtra['delivery'];			
			}; 				
			return $newleadtimeval;
		}
		
		function sendMail($sendto, $sbj, $q = "", $stxt) {

			// May need to load
			$clqm = new clqmail(); 

			$clqm->host($this->cfg['mail.host']);
			$clqm->port($this->cfg['mail.port']); // default 25
			$clqm->user($this->cfg['mail.username']);
			$clqm->password($this->cfg['mail.password']);

			$clqm->from($this->cfg['mail.from']); // email address
			$clqm->sender_name($this->cfg['mail.from_name']); // sender name
			$clqm->reply($this->cfg['mail.from']); // email address again -- if sending a list, it would be different

			if($this->cfg['mail.status'] == "test") { // equals production
				$clqm->to($this->cfg['mail.xto']);
			} else {
				$clqm->to($this->cfg['mail.to']);
				$clqm->cc($sendto);
				$clqm->bcc($this->cfg['mail.xto']);
			}
			$clqm->subject($sbj);
			$clqm->message($stxt);

			$send = $clqm->send();
			if($send == true) {
				if($this->cfg['mail.status'] == "test") {
					$result = "Success: ".$clqm->debug();
				} else {
					$result = "Success";
				}
			} else {
				$result = $clqm->report();
			}

			return $result;
		}

	/************************************************************** Cart Functions ***********************************************/

		public function mthYear($sub, $val = "") {
			
			if($val != "") {$v = explode("|", $val);};
			$str = ""; 
			// $str = $val." - ".$v[0]."|".$v[1];
			$str .= '
				<select name="client_card'.$sub.'mth" style="width:50px;" id="client_card'.$sub.'mth" class="" >';
	  			
				for($m = 1; $m < 13; $m++) {					
					if(strlen($m) == 1) {$m = "0".$m;};
					$str .= '<option value="'.$m.'"';
					if($val != "") {if($m == $v[0]) { $str .= ' selected="selected" ';}};
					$str .= '>'.$m.'</option>'; 		
				}		
	  
				$str .= '</select>
				
				<select name="client_card'.$sub.'yr" style="width:70px;" id="client_card'.$sub.'yr" class="" >';
	  			
				for($y = 2012; $y < 2025; $y++) {					
					$str .= '<option value="'.$y.'"';
					if($val != "") {if($y == $v[1]) { $str .= ' selected="selected" ';}};
					$str .= '>'.$y.'</option>'; 		
				}		
	  
			$str .= '</select>';		
			return $str;
		}
		
		/* Create a new Cart ID and check that it does not already exist */	
		public function getCartID() {	
			$sql = "select id, cart_id from cartlog order by id limit 1 ";		
			$cartid = $this->clqnop->getSingleRBValue($sql, "cart_id");
			return $cartid;		
		}
		
		/* Utility Function */
		public function getSingleRBValue($sql, $field) {

			$rs = R::getAll($sql);
			for($q = 0; $q < count($rs); $q++) { 
				$str = $rs[$q][$field];
			}
			return $str;			
		}
		
		/* Utility function to format Price */
		public function formatPrice($price) {
			// '&pound;||.|,|2' or | €|,|.|2  - // Used by numbers - dec places, dec sep, thousands sep '2|,|.'
			$fn = explode('|', $this->cfg['site.numberformat']);
			return $fn[0].number_format($price, $fn[4], $fn[2], $fn[3]).$fn[1];	
		}
		
		public function getSingleRBRow($sql) {
			
			$rs = R::getAll($sql);
			for($q = 0; $q < count($rs); $q++) { 
				$str = (object)$rs[$q];
			}

			return $str;	
		}

		function mround($number, $precision=0) {

			$precision = ($precision == 0 ? 1 : $precision);   
			$pow = pow(10, $precision);

			$ceil = ceil($number * $pow)/$pow;
			$floor = floor($number * $pow)/$pow;

			$pow = pow(10, $precision+1);

			$diffCeil     = $pow*($ceil-$number);
			$diffFloor     = $pow*($number-$floor)+($number < 0 ? -1 : 1);

			if($diffCeil >= $diffFloor) return $floor;
			else return $ceil;
		}


}
// End Class

