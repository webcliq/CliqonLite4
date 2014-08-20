<?php
// Cart Runtime Configuration styles etc.

// Cliqon with SmartCart
$shopcfg = array(
);

$cartcfg = array( 

	  'cart.status'				=> 'test',		// production or test
	  'cart.merchant'			=> 'paypal',	// If false, will submit order as email else it uses Payment Gateway
	  'cart.paymentgateway' 	=> array(

			
			// Sandbox
			'paypal.api.username'	=> '', 
			'paypal.api.password'	=> '',
			'paypal.api.signature'	=> '',
			'paypal.sandbox'		=> 'sandbox',
			'paypal.merchantID'		=> '',

		/*	
			// Live
			'paypal.api.username'	=> '', 
			'paypal.api.password'	=> '',
			'paypal.api.signature'	=> '',
			'paypal.sandbox'		=> '',
			'paypal.merchantID'		=> '', 	
		*/
		  
		  'paypal.currencyID'		=> 'EUR',
		  'paypal.paymentType'		=> 'Sale',	// or 'Authorisation' or 'Order'
		  'paypal.returnURL'		=> 'paypalreturn', // paypalresult if Authorisation
		  'paypal.cancelURL'		=> 'paypalcancel',
		  'paypal.hdrimg'			=> '',
		  'paypal.hdrbackcolor'		=> '000000',
		  'paypal.hdrbordercolor'	=> '000000',
		  'paypal.payflowcolor'		=> '000000',
		  'paypal.merchantname'		=> '',
		  'paypal.phonenumber'		=> '',			
		  'paypal.pagestyle'		=> '',
		  'paypal.taxmultiplier'	=> 0,
		  
		  /*			
		  'paypal.'		=> '',
		  'paypal.'		=> '',
		  'paypal.'		=> '',
		  'paypal.'		=> '',
		  'paypal.'		=> '',
		  */
	  ),
	  'cart.coname'		=> 'Cliqon',
	  'cart.coaddress'	=> '',

	  'cart.table'		=> array(

	  		'header.backcolor'	=> '#000',
	  		'header.color'		=> '#fff',
	  		'header.image'		=> '',
	  		'header.coname'		=> '',

	  		
	  )
								
);
$developer_account_email = 'mark.richards@webcliq.com';
$sandbox = true;


