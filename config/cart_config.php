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
			'paypal.api.username'	=> 'mark.w_1281378192_biz_api1.conkas.com', 
			'paypal.api.password'	=> '1281378198',
			'paypal.api.signature'	=> 'AI-v.z5IzHzsNYlVyy5mnx-l.2qYAejREgyyHnFEGtMuGlQBbX8rk8jg',
			'paypal.sandbox'		=> 'sandbox',
			'paypal.merchantID'		=> 'PD4B5S7BYF3SS',

		/*	
			// Live
			'paypal.api.username'	=> 'mark.richards_api1.conkas.com', 
			'paypal.api.password'	=> 'W6A282F8UV2URXRT',
			'paypal.api.signature'	=> 'AFcWxV21C7fd0v3bYYYRCpSSRl31A5BW1TV6HJEKGm7chD9uhNf3MHbv',
			'paypal.sandbox'		=> '',
			'paypal.merchantID'		=> 'T4GLDFLCZAFK6', 	
		*/
		  
		  'paypal.currencyID'		=> 'EUR',
		  'paypal.paymentType'		=> 'Sale',	// or 'Authorisation' or 'Order'
		  'paypal.returnURL'		=> 'paypalreturn', // paypalresult if Authorisation
		  'paypal.cancelURL'		=> 'paypalcancel',
		  'paypal.hdrimg'			=> 'http://cdn.webcliq.net/files/clqlite/images/cliqonlogo_sm.png',
		  'paypal.hdrbackcolor'		=> '000000',
		  'paypal.hdrbordercolor'	=> '000000',
		  'paypal.payflowcolor'		=> '000000',
		  'paypal.merchantname'		=> 'Conkas',
		  'paypal.phonenumber'		=> '0034 971 619 042',			
		  'paypal.pagestyle'		=> 'CLQ',
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
	  'cart.coaddress'	=> '<b>Conkas cb</b> NIF: ES E57153751<br />
	  						Son Comas, 10, 07190, Esporles, Illes Balears, España<br />
	  						Tel: +34 971 611 397 - Mobile: +34 650 704 745',

	  'cart.table'		=> array(

	  		'header.backcolor'	=> '#000',
	  		'header.color'		=> '#fff',
	  		'header.image'		=> '',
	  		'header.coname'		=> 'Conkas',

	  		
	  )
								
);
$developer_account_email = 'mark.richards@webcliq.com';
$sandbox = true;


