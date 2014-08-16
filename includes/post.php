<?php
/**
* post.php
* used by Ajax functions to post and put things into the database
*
*/
if(isset($_REQUEST['action'])) {$rootpath = "../"; $rq = $_REQUEST; require_once('gateway.php');} else { die("No action specified"); }; 
ob_start(); ob_end_clean(); 
require_once($rootpath."config/clqschema.cfg");
switch ($rq['action']) {
	
	case "login":
		if($rq['clq_username'] != "") {			
			if(array_key_exists($rq['clq_username'], $cfg['site.users'])) {
				if($cfg['site.users'][$rq['clq_username']]['password'] == $rq['clq_password']) {
					$_SESSION['CLQ_Username'] = $rq['clq_username'];
					$_SESSION['CLQ_Fullname'] = $cfg['site.users'][$rq['clq_username']]['fullname'];
					$_SESSION['CLQ_Langcd'] = $cfg['site.users'][$rq['clq_username']]['masteridm'];
					$_SESSION['CLQ_Level'] = $cfg['site.users'][$rq['clq_username']]['level'];
					$lcd = $_SESSION['CLQ_Langcd'];
					$result = "success";
				} else {
					$result = "failure";
				}
			} else {
				$result = "failure";
			}
		} else {
			$result = "failure";
		}		
	break;

	case "logout":
		$_SESSION['CLQ_Fullname'] = "";
		$_SESSION['CLQ_Username'] = "";
		$_SESSION['CLQ_Langcd'] = "";
		$_SESSION['CLQ_Level'] = 0;
		$result = "failure";
	break;

	case "insertrow": case "copyrecord":

		if($rq['idiomreplicate'] == 'true') {

			foreach($cfg['site.idiom'] as $lcdcode => $lcdname) {
				$ins = R::dispense($rq['table']);
				foreach($rq as $fld => $value) {
					if(stristr($fld, "clq_")) {
						$ins->$fld = $value;
					}
				}
				$ins->clq_langcd = $lcdcode;
				$res = R::store($ins);
			}

		} else {
			$ins = R::dispense($rq['table']);
			foreach($rq as $fld => $value) {
				if(stristr($fld, "clq_")) {
					$ins->$fld = $value;
				}
			}
			$ins->clq_langcd = $rq['langcd'];
			$res = R::store($ins);
		}
		if($res != 0) {
			$row = R::getCell("SELECT * FROM ".$rq['table']." WHERE clq_reference = ? AND clq_type = ? AND clq_langcd = ?", array($rq['clq_reference'], $rq['tabletype'], $rq['langcd']) );
		}
		$result = json_encode($row);
	break;

	case "updaterow":

		$upd = R::load($rq['table'], $rq['recid']);
		foreach($rq as $fld => $value) {
			if(stristr($fld, "clq_")) {
				$upd->$fld = $value;
			}
		}
		$res = R::store($upd);

		if($res != 0) {
			$row = R::getCell("SELECT * FROM ".$rq['table']." WHERE id = ?", array($rq['recid']) );
		}
		$result = json_encode($row);
	break;

	case "updateset":

		foreach($cfg['site.idiom'] as $lcdcode => $lcdname) {

			$upd = R::load($rq['table'], $rq['id_'.$lcdcode]);
			foreach($rq as $fld => $value) {
				if(stristr($fld, "_".$lcdcode)) {
					if(stristr($fld, "clq_")) {
						$fld = str_replace("_".$lcdcode, "", $fld);
						$upd->$fld = $value;
					}	
				} else {
					if(stristr($fld, "clq_")) {
						$upd->$fld = $value;
					}		
				}
			}
			$res = R::store($upd);
		}

		if($res != 0) {
			$row = R::getCell("SELECT * FROM ".$rq['table']." WHERE clq_reference = ? AND clq_type = ? AND clq_langcd = ?", array($rq['clq_reference'], $rq['tabletype'], $rq['langcd']) );
		}
		$result = json_encode($row);
	break;

	case "deleterecord":
		if(!array_key_exists("ref", $rq)) {$ref = R::getCell("SELECT clq_reference FROM ".$rq['table']." WHERE id = ?", array($rq['recid']));} else {$ref = $rq['ref'];};
		$res = R::exec("DELETE FROM ".$rq['table']." WHERE clq_reference = ? AND clq_type = ?", array($ref, $rq['tabletype']));
		if(is_numeric($res)) {
			$result = "Deleted";
		}
	break;

	case "deleteset": 
		
		if($rq['tabletype'] == "image") {
			$val1 = str_replace("//", "/", $rq['ref']);
			$val2 = explode("/", $val1);
			$rq['ref'] = $val2[2];
		}

		$res = R::exec("DELETE FROM ".$rq['table']." WHERE clq_reference = ? AND clq_type = ?", array($rq['ref'], $rq['tabletype']));
		if(is_numeric($res)) {
			
			if($rq['tabletype'] == "image") {
				unlink($val1);
				// Delete image and thumb by ref
			}

			$result = "Deleted";
		}
	break;

	case "updatevalue":	// By ID
		$fld = $rq['thisfld'];
		$upd = R::load($rq['table'], $rq['recid']);
		if($fld == "clq_extra") {
			$val = base64_decode($rq["clq_extra"]);
			$upd->$fld = str_replace('\"', '"', $val);
		} else {
			$upd->$fld = $rq[$fld];
		}
		$res = R::store($upd);
		if(is_numeric($res)) {if($res > 0) {
			$result = "Success";
		}}; 
	break;

	case "updatevalbyid":	// Supports posting after inline edit from Datatable - needs to be by Ref
		$row = R::getRow("SELECT clq_reference, clq_type FROM ".$rq['table']." WHERE id = ?", array($rq['recid']));
		$result = "Error";
		$sql = "UPDATE ".$rq['table']." SET ".$rq['fld']." = '".$rq['value']."' WHERE clq_reference = ? AND clq_type = ?";
		$res = R::exec($sql, array($row['clq_reference'], $row['clq_type']));	
		if(is_numeric($res)) {if($res > 0) {
			$result = "Success";
		}}; 
	break;

	case "updatevalbyref":	
		$result = "Error";
		$sql = "UPDATE ".$rq['table']." SET ".$rq['fld']." = '".$rq['value']."' WHERE clq_reference = ? AND clq_type = ?";
		$res = R::exec($sql, array($rq['clq_reference'], $rq['tabletype']));	
		if(is_numeric($res)) {if($res > 0) {
			$result = "Success";
		}}; 
	break;

	case "uploadfile":
		require_once($rootpath."config/clqschema.cfg");			
	break;

	case "changetype":
		$sql = "UPDATE ".$rq['table']." SET clq_type = '".$rq['clq_type']."' WHERE clq_reference = ? AND clq_type = ?";
		$res = R::exec($sql, array($rq['clq_reference'], $rq['tabletype']));
		if($res > 0) {
			$result = "Ok";
		}
	break;

	case "doexport":
		$clqutl = new clqutil();
		$clqutl->doExport($rq);
	break;

	case "doimport":
		$fn = $rq['filename'];
		array_key_exists('testimport', $rq) ? $ti = 'y' : $ti = 'n';
		array_key_exists('deleterecords', $rq) ? $dr = 'y' : $dr = 'n';
		array_key_exists('updaterecords', $rq) ? $ur = 'y' : $ur = 'n';
		$clqutl = new clqutil();
		$result = $clqutl->doImport($rq, $fn, $ti, $dr, $ur);
	break;

	case "doquery":
		$result = R::exec($rq['query']);
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

	case "copycontent":

		// Get Content for Master
		$sql = "SELECT clq_text FROM ".$rq['table']." WHERE clq_langcd = ? AND clq_type = ? AND clq_reference = ?";
		$cell = R::getCell($sql, array($rq['langcd'], $rq['tabletype'], $rq['clq_reference']));

		// Copy to others
		$sql = "UPDATE ".$rq['table']." SET clq_text = '".$cell."' WHERE clq_langcd != ? AND clq_type = ? AND clq_reference = ?";
		$res = R::exec($sql, array($rq['langcd'], $rq['tabletype'], $rq['clq_reference']));
		if($res > 0) {
			$result = "Ok";
		}
	break;
	
	default: var_dump($rq);	break;
};
// Switch ends
echo $result;
