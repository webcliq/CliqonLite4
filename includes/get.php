<?php
/**
* get.php
* used by Ajax functions to get things from the database
*
*/
if(isset($_REQUEST['action'])) {$rootpath = "../"; $rq = $_REQUEST; require_once('gateway.php');} else { die("Access denied"); }; 
ob_start(); ob_end_clean();
require_once($rootpath."config/clqschema.cfg");
switch ($rq['action']) {

	/**
	* Use clqadmin to get a form definition from clqschema.cfg
	*/
	case "getdataset":
		$clqadm = new clqadmin();
		$result = $clqadm->dataTableData($rq);
	break;

	case "getformdef":
		$clqadm = new clqadmin();
		$result = json_encode($clqadm->getFormDefinition($rq));
	break;

	case "getformsetdef":
		$clqadm = new clqadmin();
		$result = json_encode($clqadm->getFormSetDefinition($rq));
	break;
	
	case "getviewdef":
		$clqadm = new clqadmin();
		$result = json_encode($clqadm->getViewDefinition($rq));	
	break;

	case "getval": // By ID
		$result = R::getCell("SELECT ".$rq['fld']." FROM ".$rq['table']." WHERE id = ?", array($rq['recid']) );
	break;

	case "getrow":
		
		if($rq['tabletype'] == "image") {
			$val1 = str_replace("//", "/", $rq['val']);
			$val2 = explode("/", $val1);
			$rq['val'] = $val2[2];
		}

		$row = R::getRow("SELECT * FROM ".$rq['table']." WHERE clq_reference = ? AND clq_type = ? AND clq_langcd = ?", array($rq['val'], $rq['tabletype'], $rq['langcd']) );
		$result = json_encode($row);
	break;

	case "getrowbyid":
		$row = R::getRow("SELECT * FROM ".$rq['table']." WHERE id = ?", array($rq['recid']) );
		$result = json_encode($row);
	break;

	case "getsetbyid":

	break;

	case "getsetbyref":

		// table, type and ref
		// Get row for standard language
		$row = R::getRow("SELECT * FROM ".$rq['table']." WHERE clq_reference = ? AND clq_type = ? AND clq_langcd = ?", array($rq['ref'], $rq['tabletype'], $rq['langcd']) );
		$res = array();
		foreach($row as $fld => $val) {
			if($fld != "clq_text") {
				$res[$fld] = $val;
			}
			$fldlist[] = $fld;
		}

		// Clq Text by each language and adjust the Fldname
		$rs = R::getAll("SELECT * FROM ".$rq['table']." WHERE clq_reference = ? AND clq_type = ?", array($rq['ref'], $rq['tabletype']) );
		for($r = 0; $r < count($rs); $r++) {
			foreach($fldlist as $f => $fldname) {
				$res[$fldname."_".$rs[$r]['clq_langcd']] = $rs[$r][$fldname];
			}
		}
		$result = json_encode($res);
		
	break;

	case "isunique": 
		$cell = R::getCell("SELECT DISTINCT ".$rq['fld']." FROM ".$rq['table']." WHERE clq_reference = ? AND clq_type = ?", array($rq['val'], $rq['tabletype']) );
		if($cell != "") {
			$result = "Exists";
		} else {
			$result = "";
		}
	break;

	case "changepassword": 

	break;

	case "translate":
		require_once($rootpath."includes/classes/clqtranslate.class.php");	
		$translator = new MicrosoftTranslator($cfg['site.bingkey']);
		$text_to_translate = $_REQUEST['textfrom']; $from = $_REQUEST['idmfrom']; $to = $_REQUEST['idmto'];
		$translator->translate($from, $to, $text_to_translate); $str = json_decode($translator->response->jsonResponse, true);
		$qrepl = array('<string xmlns="http://schemas.microsoft.com/2003/10/Serialization/">', '</string>');
		$result = str_replace($qrepl, '', $str['translation']);
	break;

	case "texteditor": case "jsoneditor": case "codeeditor": case "filemanager":

		clqtpl::configure('path_replace', false); 
		clqtpl::configure('path_replace_list', array('<a>','<link>','<img>')); // but not <scripts>

		$tpldir = "../admin/";
		clqtpl::configure('tpl_dir', $tpldir);
		$tpl = new clqtpl();
		$tpl->set('viewpath', $tpldir);
		$tpl->set('jspath', "../includes/js/");

		$tpl->set('success', 'Record updated successfully');
		$tpl->set('error', 'Error saving record');
		require_once($tpldir.$rq['action'].".php");
        echo $tpl->publish($rq['action']);

	break;

	case "getnextref":
		
		// fld	clq_reference
		// table	clqstring
		// tabletype	string

        $a = explode("(", $rq['defval']); $b = $a[0]; // defval = $b.'('.$n.')'

        $sql = "SELECT DISTINCT ".$rq['fld']." FROM ".$rq['table']." WHERE clq_type = ? AND clq_langcd = ? ORDER BY id DESC LIMIT 1";
        $nextref = R::getCell($sql, array($rq['tabletype'], $rq['langcd']));
             
        if($nextref != "") {             
            $d = explode("(", $nextref); $e = $d[0]; $f = explode(")", $d[1]); $p = $f[0]; $p = +$p + 1;
            $nextref = $b.'('.$p.')';
        } else {
            $nextref = $rq['defval'].$nextref;         
        };
        
        $result = trim($nextref);
	break;

	case "getsecn":
		$Q = new clq();
		$result = $Q->cSecn($rq['ref']);
	break;

	case "getlist":
		$rs = R::getAll("SELECT * FROM clqstring WHERE clq_langcd = ? AND clq_type = ?", array($rq['langcd'], 'report') );
		$result = json_encode($rs);
	break;

	case "getreportdef":
		$json = R::getCell("SELECT clq_extra FROM clqstring WHERE id = ?", array($rq['recid']));
		if(stripos($json, "{") !== false) {$result = $json;} else {$result = base64_decode($json);};
	break;

	case "runreport":
		$json = R::getCell("SELECT clq_extra FROM clqstring WHERE id = ?", array($rq['recid']));
		if(stripos($json, "{") !== false) {$cell = $json;} else {$cell = base64_decode($json);};
		$def = json_decode($cell, true);
		$query = $def['query'];
		$groupby = $def['groupby'];
		$rs = R::getAll($query);
		ob_start(); ob_end_clean();
		header('Content-Type: application/json');
		$result = json_encode($rs);
	break;

	case "getcalendardata":
		// $clqadm = new clqadmin();
		// $result = json_encode($clqadm->getCalendarData("event", $rq));
		$sql = "SELECT * FROM clqdata WHERE clq_type = 'event' AND clq_langcd = '".$rq['langcd']."' AND ((clq_datefrom BETWEEN '".$rq["start"]."' AND '".$rq["end"]."') AND (clq_dateto BETWEEN '".$rq["start"]."' AND '".$rq["end"]."'))";
		$result = json_encode(R::getAll($sql));
	break;

	case "getcalendarevent":

	break;

	case "getadminhelp":
		$sql = "SELECT clq_text FROM clqstring WHERE clq_type = ? AND clq_reference = ? AND clq_value = ? AND clq_langcd = ?";
		$hlp = R::getCell($sql, array('help', $rq['tabletype'], $rq['table'], $rq['langcd']));
		if($hlp != "") {
			$result = $hlp;
		} else {
			$result = "Help is not yet available for: ".$rq['table'].'.'.$rq['tabletype'];
		}
	break;

	case "clearcache":

		try {
			$files = glob('../tmp/*.tmp', GLOB_MARK);
	    	foreach($files as $file) {
	            unlink($file);
	        };
	        $result = "Success";
		} catch (Exception $e) {
		 	throw new Exception('Error = ', 0, $e);
		}

	break;

	case "sendemail":
		
		$email = $rq["email"];
		$name = $rq["name"];
		
		$sbj =  $rq["subject"];
		$stxt = $rq["name"].": ".$rq["email"]."<br />";
		$stxt .= $rq["message"];

		require('generate_email.php');
		// Runs routines in gen_email and populates $result variable with Success or Error, also a debug if Test.
		// As debug will be more than "Success", result will be printed.
		
	break;

	case "getopts":
		$Q = new clq();
		$result = $Q->cList($rq['list']);
	break;
	
	default: var_dump($rq); break;
};
// Switch ends
echo $result;