<?php
/* 
Always included
Generate User and Admin Emails
* Cliqon Lite version using clqmail, not xpmail

	// Mail Configuration

	// Webcliq.Net
	'mail.host'					=> 'webcliq.net',
	'mail.port'					=> 587,
	'mail.username'				=> 'formmail@webcliq.net',
	'mail.password'				=> 'B25frm#', //

	// Where emails come from if not defined in Form
	'mail.from' 				=> 'webmaster@cliqonlite.com',
	'mail.from_name'			=> 'Webcliq',

	// Production Status
	'mail.status'				=> 'test',	// test or production

	// Primary Client email
	'mail.to'					=> '', 
	'mail.to_name'				=> '', 

	// Secondary Client email
	'mail.cc'					=> '',
	'mail.cc_name'				=> '',

	// Testing Address
	'mail.xto'					=> 'mark.richards@conkas.com',
	'mail.xto_name'				=> 'Mark Richards',
*/

$clqm = new clqmail(); 

$clqm->host($cfg['mail.host']);
$clqm->port($cfg['mail.port']); // default 25
$clqm->user($cfg['mail.username']);
$clqm->password( $cfg['mail.password']);

$clqm->from($email); // email address
$clqm->sender_name($name); // sender name
$clqm->reply($email); // email address again -- if sending a list, it would be different

if($cfg['mail.status'] == "test") { // equals production
	$clqm->to($cfg['mail.xto']);
} else {
	$clqm->to($cfg['mail.to']);
	if($cfg['mail.cc'] != "") {
		$clqm->cc($cfg['mail.cc']);
	}
	$clqm->bcc($cfg['mail.xto']);
}
$clqm->subject($sbj);
$clqm->message($stxt);

$send = $clqm->send();
if($send == true) {
	if($cfg['mail.status'] == "test") {
		$result = "Success: ".$clqm->debug();
	} else {
		$result = "Success";
	}
} else {
	$result = $clqm->report();
}

// Ends generate email

/*
 *	clqmail public functions
 *	new clqmail()		-> 	initialize the class
 *	host(str)		->	define smtp host
 *	port(int)		->	define port to connect (default: 25);
 *	secure(str)		->	define secure connection type (default: none);
 *	limit($int)		->	smtp maximum recipients in one session, defaul 50
 *
 *	user(str)		->	define username to connect
 *	password(str)		->	define password to connect
 *
 *	mail_list()		->	add header send mail To: Undisclosed recipients <senders@mail> and add list_id and list unsubscribe headers
 *	list_id(str)		->	add list id header
 *	unsubscribe()		->	sends unsubscribe header, usage: unsubscribe($mailaddress, optionally $url);
 *
 *	from(str)		->	define senders mail
 *	sender_name(str)	->	define senders name to show
 *	reply($mail)		->	define reply to (default: no-reply), if $mail is not defined, reply to sender
 *	to()			->	define recipents (one or more in array or comma separated list)
 *	cc()			->	define recipents of carbon copies (one or more in array or comma separated list)
 *	bcc()			->	define recipents of blind carbon copies (one or more in array or comma separated list)
 *	subject($str)		->	define mails subject
 *	message($str)		->	define message
 *	message_from_file($file)->	define the file contains the message (if message is defined from file the message(str) will be ignored)
 *	txt()			->	send mail as text/plain (default: html)
 *	attach()		->	define attachment (one or more in array or comma separated list) whith
 *					limitation that file and directory names cannot contains comma
 *	embedd_pics()		->	define inline pictures for html files (one or more in array or comma separated list) 
 *					the filename in html img src="" part must appear in embedd_pics list or will be ignored (send without)
 *					LIMITATIONS:
 *					1.	file and directory names cannot contains comma
 *					2.	cannot use same filename with different path more times (will send only first picture for all)
 *
 *	send()			->	mail send (returns TRUE if mail is sended for one recipient)
 *	report()		->	report warnings and errors
 *	debug()			->	show sended commands and responses for debug

$mail=new clqmail();

// posts
$mail->host($_POST['host']);
$mail->port($_POST['port']);
if ($_POST['secure']) $mail->secure($_POST['secure']);
if ($_POST['username']) $mail->user($_POST['username']);
if ($_POST['password']) $mail->password($_POST['password']);
$mail->limit(20);
if ($_POST['sender']) $mail->sender_name($_POST['sender']);
if ($_POST['from']) $mail->from($_POST['from']);
if ($_POST['reply'])	$mail->reply();
if ($_POST['maillist'])	$mail->mail_list();
if ($_POST['listID']) $mail->list_id($_POST['listID']);
if ($_POST['unsubscribe']) $mail->unsubscribe($_POST['unsubscribe']);
if ($_POST['to']) $mail->to($_POST['to']);
if ($_POST['bcc']) $mail->bcc($_POST['bcc']);
if ($_POST['cc']) $mail->cc($_POST['cc']);
if ($_POST['subject']) $mail->subject($_POST['subject']);
if ($_POST['message']) $mail->message($_POST['message']);
 *
 */
