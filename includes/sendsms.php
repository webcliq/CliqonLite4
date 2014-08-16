<?php 
$rootpath = "../"; 
require($rootpath.'includes/gateway.php');

// Create SMS

$enqmsg = $_REQUEST['enqmsg'];
$phone = $_REQUEST['phone'];

$sendto = "";

// Start with Confirmation
$rmsg = $clqutil->sendElasticEmail($cfg['mail.xto'], $sbj, "", $stxt, $cfg['mail.from'], $cfg['mail.from_name'])."\n";

echo $rmsg;

    define('DISPLAY_XPM4_ERRORS', true); // display XPM4 errors
    include($classpath.'cliqon.mail.php'); // path to 'MAIL.php' file from XPM4 package
        
    $clqmail = new MAIL; 
    $clqmail->From($config['mail.webmaster']); 
    if($config['mail.status'] != "test") { // equals production
      $clqmail->AddTo($config['sms.emailaddress'], $config['sms.emailname']); 
    };
    $clqmail->AddTo($config['mail.addcc'], $config['mail.addcc_name']);   
    
    $stxt = "
      api_id:".$config['sms.api_id']."\n
      user:".$config['sms.user']."\n
      password:".$config['sms.password']."\n
      to:".$_REQUEST['mobno']."\n
      text:".$_REQUEST['smsmsg']."
    ";  
    
    $clqmail->Text($stxt);  
    
    // connect to MTA server 'smtp.hostname.net' port '25' with authentication: 'username'/'password'
    $cmail = $clqmail->Connect($config['mail.host'], $config['mail.port'], $config['mail.username'], $config['mail.password']) or die(print_r($clqmail->Result));
    // send mail relay using the '$c' resource connection
    $alertmessage = $clqmail->Send($cmail) ? "OK" : "Error" ;
    
    // optional, for debugging}
    if($config['mail.status'] == "test") { 
      echo $config['mail.host'].":".$config['mail.port'].":".$config['mail.username'].":".$config['mail.password']."<br /> \n"; 
      print_r($clqmail->History);
    }
    
    // disconnect from server 
    $clqmail->Disconnect(); 

