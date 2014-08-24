<?php
// Cliqon version of Xpertmailer
$_RESULT = array();

class FUNC extends FUNC5 { };
class FUNC5 {

	static public function is_debug($debug) {
		return (is_array($debug) && isset($debug[0]['class'], $debug[0]['type'], $debug[0]['function'], $debug[0]['file'], $debug[0]['line']));
	}

	static public function microtime_float() {
		return microtime(true);
	}

	static public function is_win() {
		return (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
	}

	static public function log_errors($msg = null, $strip = false) {
		if (defined('LOG_XPM4_ERRORS')) {
			if (is_string(LOG_XPM4_ERRORS) && is_string($msg) && is_bool($strip)) {
				if (is_array($arr = unserialize(LOG_XPM4_ERRORS)) && isset($arr['type']) && is_int($arr['type']) && ($arr['type'] == 0 || $arr['type'] == 1 || $arr['type'] == 3)) {
					$msg = "\r\n".'['.date('m-d-Y H:i:s').'] XPM4 '.($strip ? str_replace(array('<br />', '<b>', '</b>', "\r\n"), '', $msg) : $msg);
					if ($arr['type'] == 0) error_log($msg);
					else if ($arr['type'] == 1 && isset($arr['destination'], $arr['headers']) && 
						is_string($arr['destination']) && strlen(trim($arr['destination'])) > 5 && count(explode('@', $arr['destination'])) == 2 && 
						is_string($arr['headers']) && strlen(trim($arr['headers'])) > 3) {
						error_log($msg, 1, trim($arr['destination']), trim($arr['headers']));
					} else if ($arr['type'] == 3 && isset($arr['destination']) && is_string($arr['destination']) && strlen(trim($arr['destination'])) > 1) {
						error_log($msg, 3, trim($arr['destination']));
					} else if (defined('DISPLAY_XPM4_ERRORS') && DISPLAY_XPM4_ERRORS == true) trigger_error('invalid LOG_XPM4_ERRORS constant value', E_USER_WARNING);
				} else if (defined('DISPLAY_XPM4_ERRORS') && DISPLAY_XPM4_ERRORS == true) trigger_error('invalid LOG_XPM4_ERRORS constant type', E_USER_WARNING);
			} else if (defined('DISPLAY_XPM4_ERRORS') && DISPLAY_XPM4_ERRORS == true) trigger_error('invalid parameter(s) type', E_USER_WARNING);
		}
	}

	static public function trace($debug, $message = null, $level = 0, $ret = false) {
		if (self::is_debug($debug) && is_string($message) && ($level == 0 || $level == 1 || $level == 2)) {
			if ($level == 0) $mess = 'Error';
			else if ($level == 1) $mess = 'Warning';
			else if ($level == 2) $mess = 'Notice';
			$emsg = '<br /><b>'.$mess.'</b>: '.$message.
				' on '.strtoupper($debug[0]['class']).$debug[0]['type'].$debug[0]['function'].'()'.
				' in <b>'.$debug[0]['file'].'</b> on line <b>'.$debug[0]['line'].'</b><br />'."\r\n";
			self::log_errors($emsg, true);
			if ($level == 0) {
				if (defined('DISPLAY_XPM4_ERRORS') && DISPLAY_XPM4_ERRORS == true) die($emsg);
				else exit;
			} else if (defined('DISPLAY_XPM4_ERRORS') && DISPLAY_XPM4_ERRORS == true) echo $emsg;
		} else {
			$emsg = 'invalid debug parameters';
			self::log_errors(': '.$emsg, true);
			if ($level == 0) {
				if (defined('DISPLAY_XPM4_ERRORS') && DISPLAY_XPM4_ERRORS == true) trigger_error($emsg, E_USER_ERROR);
				else exit;
			} else if (defined('DISPLAY_XPM4_ERRORS') && DISPLAY_XPM4_ERRORS == true) trigger_error($emsg, E_USER_WARNING);
		}
		return $ret;
	}

	static public function str_clear($str = null, $addrep = null, $debug = null) {
		if (!self::is_debug($debug)) $debug = debug_backtrace();
		$err = array();
		$rep = array("\r", "\n", "\t");
		if (!is_string($str)) $err[] = 'invalid argument type';
		if ($addrep == null) $addrep = array();
		if (is_array($addrep)) {
			if (count($addrep) > 0) {
				foreach ($addrep as $strrep) {
					if (is_string($strrep) && $strrep != '') $rep[] = $strrep;
					else {
						$err[] = 'invalid array value';
						break;
					}
				}
			}
		} else $err[] = 'invalid array type';
		if (count($err) == 0) return ($str == '') ? '' : str_replace($rep, '', $str);
		else self::trace($debug, implode(', ', $err));
	}

	static public function is_alpha($str = null, $num = true, $add = '', $debug = null) {
		if (!self::is_debug($debug)) $debug = debug_backtrace();
		$err = array();
		if (!is_string($str)) $err[] = 'invalid argument type';
		if (!is_bool($num)) $err[] = 'invalid numeric type';
		if (!is_string($add)) $err[] = 'invalid additional type';
		if (count($err) > 0) self::trace($debug, implode(', ', $err));
		else {
			if ($str != '') {
				$lst = 'abcdefghijklmnoqprstuvwxyzABCDEFGHIJKLMNOQPRSTUVWXYZ'.$add;
				if ($num) $lst .= '1234567890';
				$len1 = strlen($str);
				$len2 = strlen($lst);
				$match = true;
				for ($i = 0; $i < $len1; $i++) {
					$found = false;
					for ($j = 0; $j < $len2; $j++) {
						if ($lst{$j} == $str{$i}) {
							$found = true;
							break;
						}
					}
					if (!$found) {
						$match = false;
						break;
					}
				}
				return $match;
			} else return false;
		}
	}

	static public function is_hostname($str = null, $addr = false, $debug = null) {
		if (!self::is_debug($debug)) $debug = debug_backtrace();
		$err = array();
		if (!is_string($str)) $err[] = 'invalid hostname type';
		if (!is_bool($addr)) $err[] = 'invalid address type';
		if (count($err) > 0) self::trace($debug, implode(', ', $err));
		else {
			$ret = false;
			if (trim($str) != '' && self::is_alpha($str, true, '-.')) {
				if (count($exphost1 = explode('.', $str)) > 1 && !(strstr($str, '.-') || strstr($str, '-.'))) {
					$set = true;
					foreach ($exphost1 as $expstr1) {
						if ($expstr1 == '') {
							$set = false;
							break;
						}
					}
					if ($set) {
						foreach (($exphost2 = explode('-', $str)) as $expstr2) {
							if ($expstr2 == '') {
								$set = false;
								break;
							}
						}
					}
					$ext = $exphost1[count($exphost1)-1];
					$len = strlen($ext);
					if ($set && $len >= 2 && $len <= 6 && self::is_alpha($ext, false)) $ret = true;
				}
			}
			return ($ret && $addr && gethostbyname($str) == $str) ? false : $ret;
		}
	}

	static public function is_ipv4($str = null, $debug = null) {
		if (!self::is_debug($debug)) $debug = debug_backtrace();
		if (is_string($str)) return (trim($str) != '' && ip2long($str) && count(explode('.', $str)) === 4);
		else self::trace($debug, 'invalid argument type');
	}

	static public function getmxrr_win($hostname = null, &$mxhosts, $debug = null) {
		if (!self::is_debug($debug)) $debug = debug_backtrace();
		$mxhosts = array();
		if (!is_string($hostname)) self::trace($debug, 'invalid hostname type');
		else {
			$hostname = strtolower($hostname);
			if (self::is_hostname($hostname, true, $debug)) {
				$retstr = exec('nslookup -type=mx '.$hostname, $retarr);
				if ($retstr && count($retarr) > 0) {
					foreach ($retarr as $line) {
						if (preg_match('/.*mail exchanger = (.*)/', $line, $matches)) $mxhosts[] = $matches[1];
					}
				}
			} else self::trace($debug, 'invalid hostname value', 1);
			return (count($mxhosts) > 0);
		}
	}

	static public function is_mail($addr = null, $vermx = false, $debug = null) {
		if (!self::is_debug($debug)) $debug = debug_backtrace();
		$err = array();
		if (!is_string($addr)) $err[] = 'invalid address type';
		if (!is_bool($vermx)) $err[] = 'invalid MX type';
		if (count($err) > 0) self::trace($debug, implode(', ', $err));
		else {
			$ret = (count($exp = explode('@', $addr)) === 2 && $exp[0] != '' && $exp[1] != '' && self::is_alpha($exp[0], true, '_-.+') && (self::is_hostname($exp[1]) || self::is_ipv4($exp[1])));
			if ($ret && $vermx) {
				if (self::is_ipv4($exp[1])) $ret = false;
				else $ret = self::is_win() ? self::getmxrr_win($exp[1], $mxh, $debug) : getmxrr($exp[1], $mxh);
			}
			return $ret;
		}
	}

	static public function mime_type($name = null, $debug = null) {
		if (!self::is_debug($debug)) $debug = debug_backtrace();
		if (!is_string($name)) self::trace($debug, 'invalid filename type');
		else {
			$name = self::str_clear($name);
			$name = trim($name);
			if ($name == '') return self::trace($debug, 'invalid filename value', 1);
			else {
				$ret = 'application/octet-stream';
				$arr = array(
					'z'    => 'application/x-compress', 
					'xls'  => 'application/x-excel', 
					'gtar' => 'application/x-gtar', 
					'gz'   => 'application/x-gzip', 
					'cgi'  => 'application/x-httpd-cgi', 
					'php'  => 'application/x-httpd-php', 
					'js'   => 'application/x-javascript', 
					'swf'  => 'application/x-shockwave-flash', 
					'tar'  => 'application/x-tar', 
					'tgz'  => 'application/x-tar', 
					'tcl'  => 'application/x-tcl', 
					'src'  => 'application/x-wais-source', 
					'zip'  => 'application/zip', 
					'kar'  => 'audio/midi', 
					'mid'  => 'audio/midi', 
					'midi' => 'audio/midi', 
					'mp2'  => 'audio/mpeg', 
					'mp3'  => 'audio/mpeg', 
					'mpga' => 'audio/mpeg', 
					'ram'  => 'audio/x-pn-realaudio', 
					'rm'   => 'audio/x-pn-realaudio', 
					'rpm'  => 'audio/x-pn-realaudio-plugin', 
					'wav'  => 'audio/x-wav', 
					'bmp'  => 'image/bmp', 
					'fif'  => 'image/fif', 
					'gif'  => 'image/gif', 
					'ief'  => 'image/ief', 
					'jpe'  => 'image/jpeg', 
					'jpeg' => 'image/jpeg', 
					'jpg'  => 'image/jpeg', 
					'png'  => 'image/png', 
					'tif'  => 'image/tiff', 
					'tiff' => 'image/tiff', 
					'css'  => 'text/css', 
					'htm'  => 'text/html', 
					'html' => 'text/html', 
					'txt'  => 'text/plain', 
					'rtx'  => 'text/richtext', 
					'vcf'  => 'text/x-vcard', 
					'xml'  => 'text/xml', 
					'xsl'  => 'text/xsl', 
					'mpe'  => 'video/mpeg', 
					'mpeg' => 'video/mpeg', 
					'mpg'  => 'video/mpeg', 
					'mov'  => 'video/quicktime', 
					'qt'   => 'video/quicktime', 
					'asf'  => 'video/x-ms-asf', 
					'asx'  => 'video/x-ms-asf', 
					'avi'  => 'video/x-msvideo', 
					'vrml' => 'x-world/x-vrml', 
					'wrl'  => 'x-world/x-vrml');
				if (count($exp = explode('.', $name)) >= 2) {
					$ext = strtolower($exp[count($exp)-1]);
					if (trim($exp[count($exp)-2]) != '' && isset($arr[$ext])) $ret = $arr[$ext];
				}
				return $ret;
			}
		}
	}

}

class MIME extends MIME5 { }
class MIME5 {

	const LE = "\r\n";
	const HLEN = 52;
	const MLEN = 73;

	const HCHARSET = 'utf-8';
	const MCHARSET = 'us-ascii';

	const HENCDEF = 'quoted-printable';
	const MENCDEF = 'quoted-printable';

	static public $hencarr = array('quoted-printable' => '', 'base64' => '');
	static public $mencarr = array('7bit' => '', '8bit' => '', 'quoted-printable' => '', 'base64' => '', 'binary' => '');

	static public $qpkeys = array(
			"\x00","\x01","\x02","\x03","\x04","\x05","\x06","\x07",
			"\x08","\x09","\x0A","\x0B","\x0C","\x0D","\x0E","\x0F",
			"\x10","\x11","\x12","\x13","\x14","\x15","\x16","\x17",
			"\x18","\x19","\x1A","\x1B","\x1C","\x1D","\x1E","\x1F",
			"\x7F","\x80","\x81","\x82","\x83","\x84","\x85","\x86",
			"\x87","\x88","\x89","\x8A","\x8B","\x8C","\x8D","\x8E",
			"\x8F","\x90","\x91","\x92","\x93","\x94","\x95","\x96",
			"\x97","\x98","\x99","\x9A","\x9B","\x9C","\x9D","\x9E",
			"\x9F","\xA0","\xA1","\xA2","\xA3","\xA4","\xA5","\xA6",
			"\xA7","\xA8","\xA9","\xAA","\xAB","\xAC","\xAD","\xAE",
			"\xAF","\xB0","\xB1","\xB2","\xB3","\xB4","\xB5","\xB6",
			"\xB7","\xB8","\xB9","\xBA","\xBB","\xBC","\xBD","\xBE",
			"\xBF","\xC0","\xC1","\xC2","\xC3","\xC4","\xC5","\xC6",
			"\xC7","\xC8","\xC9","\xCA","\xCB","\xCC","\xCD","\xCE",
			"\xCF","\xD0","\xD1","\xD2","\xD3","\xD4","\xD5","\xD6",
			"\xD7","\xD8","\xD9","\xDA","\xDB","\xDC","\xDD","\xDE",
			"\xDF","\xE0","\xE1","\xE2","\xE3","\xE4","\xE5","\xE6",
			"\xE7","\xE8","\xE9","\xEA","\xEB","\xEC","\xED","\xEE",
			"\xEF","\xF0","\xF1","\xF2","\xF3","\xF4","\xF5","\xF6",
			"\xF7","\xF8","\xF9","\xFA","\xFB","\xFC","\xFD","\xFE",
			"\xFF");

	static public $qpvrep = array(
			"=00","=01","=02","=03","=04","=05","=06","=07",
			"=08","=09","=0A","=0B","=0C","=0D","=0E","=0F",
			"=10","=11","=12","=13","=14","=15","=16","=17",
			"=18","=19","=1A","=1B","=1C","=1D","=1E","=1F",
			"=7F","=80","=81","=82","=83","=84","=85","=86",
			"=87","=88","=89","=8A","=8B","=8C","=8D","=8E",
			"=8F","=90","=91","=92","=93","=94","=95","=96",
			"=97","=98","=99","=9A","=9B","=9C","=9D","=9E",
			"=9F","=A0","=A1","=A2","=A3","=A4","=A5","=A6",
			"=A7","=A8","=A9","=AA","=AB","=AC","=AD","=AE",
			"=AF","=B0","=B1","=B2","=B3","=B4","=B5","=B6",
			"=B7","=B8","=B9","=BA","=BB","=BC","=BD","=BE",
			"=BF","=C0","=C1","=C2","=C3","=C4","=C5","=C6",
			"=C7","=C8","=C9","=CA","=CB","=CC","=CD","=CE",
			"=CF","=D0","=D1","=D2","=D3","=D4","=D5","=D6",
			"=D7","=D8","=D9","=DA","=DB","=DC","=DD","=DE",
			"=DF","=E0","=E1","=E2","=E3","=E4","=E5","=E6",
			"=E7","=E8","=E9","=EA","=EB","=EC","=ED","=EE",
			"=EF","=F0","=F1","=F2","=F3","=F4","=F5","=F6",
			"=F7","=F8","=F9","=FA","=FB","=FC","=FD","=FE",
			"=FF");

	static public function unique($add = null) {
		return md5(microtime(true).$add);
	}

	static public function is_printable($str = null, $debug = null) {
		if (!FUNC5::is_debug($debug)) $debug = debug_backtrace();
		if (!is_string($str)) FUNC5::trace($debug, 'invalid argument type');
		else {
			$contain = implode('', self::$qpkeys);
			return (strcspn($str, $contain) == strlen($str));
		}
	}

	static public function qp_encode($str = null, $len = null, $end = null, $debug = null) {
		if (!FUNC5::is_debug($debug)) $debug = debug_backtrace();
		$err = array();
		if (!is_string($str)) $err[] = 'invalid argument type';
		if ($len == null) $len = self::MLEN;
		else if (!(is_int($len) && $len > 1)) $err[] = 'invalid line length value';
		if ($end == null) $end = self::LE;
		else if (!is_string($end)) $err[] = 'invalid line end value';
		if (count($err) > 0) FUNC5::trace($debug, implode(', ', $err));
		else {
			if ($str == '') return $str;
			else {
				$out = array();
				foreach (explode($end, $str) as $line) {
					if ($line == '') $out[] = '';
					else {
						$line = str_replace('=', '=3D', $line);
						$line = str_replace(self::$qpkeys, self::$qpvrep, $line);
						preg_match_all('/.{1,'.$len.'}([^=]{0,2})?/', $line, $match);
						$mcnt = count($match[0]);
						for ($i = 0; $i < $mcnt; $i++) {
							$line = (substr($match[0][$i], -1) == ' ') ? substr($match[0][$i], 0, -1).'=20' : $match[0][$i];
							if (($i+1) < $mcnt) $line .= '=';
							$out[] = $line;
						}
					}
				}
				return implode($end, $out);
			}
		}
	}

	static public function encode_header($str = null, $charset = null, $encoding = null, $len = null, $end = null, $debug = null) {
		if (!FUNC5::is_debug($debug)) $debug = debug_backtrace();
		$err = array();
		if (!is_string($str)) $err[] = 'invalid argument type';
		if ($charset == null) $charset = self::HCHARSET;
		else if (!is_string($charset)) $err[] = 'invalid charset type';
		else if (!(strlen($charset) >= 2 && FUNC5::is_alpha($charset, true, '-'))) $err[] = 'invalid charset value';
		if ($encoding == null) $encoding = self::HENCDEF;
		else if (!is_string($encoding)) $err[] = 'invalid encoding type';
		else {
			$encoding = strtolower(FUNC5::str_clear($encoding));
			if (!isset(self::$hencarr[$encoding])) $err[] = 'invalid encoding value';
		}
		if ($len == null) $len = self::HLEN;
		else if (!(is_int($len) && $len > 1)) $err[] = 'invalid line length value';
		if ($end == null) $end = self::LE;
		else if (!is_string($end)) $err[] = 'invalid line end value';
		if (count($err) > 0) FUNC5::trace($debug, implode(', ', $err));
		else {
			if ($str == '') return $str;
			else {
				$enc = false;
				$dif = $len - strlen('=?'.$charset.'?X??=');
				if ($encoding == 'quoted-printable') {
					if (!self::is_printable($str)) {
						$new = (($dif-4) > 2) ? ($dif-4) : $len;
						$enc = self::qp_encode($str, $new, $end);
						$enc = str_replace(array('?', ' ', '='.$end), array('=3F', '_', $end), $enc);
					}
				} else if ($encoding == 'base64') {
					$new = ($dif > 3) ? $dif : $len;
					if ($new > 3) {
						for ($i = $new; $i > 2; $i--) {
							$crt = '';
							for ($j = 0; $j <= $i; $j++) $crt .= 'x';
							if (strlen(base64_encode($crt)) <= $new) {
								$new = $i;
								break;
							}
						}
					}
					$cnk = rtrim(chunk_split($str, $new, $end));
					$imp = array();
					foreach (explode($end, $cnk) as $line) if ($line != '') $imp[] = base64_encode($line);
					$enc = implode($end, $imp);
				}
				$res = array();
				if ($enc) {
					$chr = ($encoding == 'base64') ? 'B' : 'Q';
					foreach (explode($end, $enc) as $val) if ($val != '') $res[] = '=?'.$charset.'?'.$chr.'?'.$val.'?=';
				} else {
					$cnk = rtrim(chunk_split($str, $len, $end));
					foreach (explode($end, $cnk) as $val) if ($val != '') $res[] = $val;
				}
				return implode($end."\t", $res);
			}
		}
	}

	static public function decode_header($str = null, $debug = null) {
		if (!FUNC5::is_debug($debug)) $debug = debug_backtrace();
		if (!is_string($str)) FUNC5::trace($debug, 'invalid argument type');
		else {
			$str = trim(FUNC5::str_clear($str));
			$arr = array();
			if ($str == '') $arr[] = array('charset' => self::HCHARSET, 'value' => '');
			else {
				foreach (preg_split('/(?<!\\?(?i)q)\\?\\=/', $str, -1, PREG_SPLIT_NO_EMPTY) as $str1) {
					foreach (explode('=?', $str1, 2) as $str2) {
						$def = false;
						if (count($exp = explode('?B?', $str2)) == 2) {
							if (strlen($exp[0]) >= 2 && FUNC5::is_alpha($exp[0], true, '-') && trim($exp[1]) != '') $def = array('charset' => $exp[0], 'value' => base64_decode(trim($exp[1])));
						} else if (count($exp = explode('?b?', $str2)) == 2) {
							if (strlen($exp[0]) >= 2 && FUNC5::is_alpha($exp[0], true, '-') && trim($exp[1]) != '') $def = array('charset' => $exp[0], 'value' => base64_decode(trim($exp[1])));
						} else if (count($exp = explode('?Q?', $str2)) == 2) {
							if (strlen($exp[0]) >= 2 && FUNC5::is_alpha($exp[0], true, '-') && $exp[1] != '') $def = array('charset' => $exp[0], 'value' => quoted_printable_decode(str_replace('_', ' ', $exp[1])));
						} else if (count($exp = explode('?q?', $str2)) == 2) {
							if (strlen($exp[0]) >= 2 && FUNC5::is_alpha($exp[0], true, '-') && $exp[1] != '') $def = array('charset' => $exp[0], 'value' => quoted_printable_decode(str_replace('_', ' ', $exp[1])));
						}
						if ($def) {
							if ($def['value'] != '') $arr[] = array('charset' => $def['charset'], 'value' => $def['value']);
						} else {
							if ($str2 != '') $arr[] = array('charset' => self::HCHARSET, 'value' => $str2);
						}
					}
				}
			}
			return $arr;
		}
	}

	static public function decode_content($str = null, $encoding = null, $debug = null) {
		if (!FUNC5::is_debug($debug)) $debug = debug_backtrace();
		$err = array();
		if (!is_string($str)) $err[] = 'invalid content type';
		if ($encoding == null) $encoding = '7bit';
		else if (!is_string($encoding)) $err[] = 'invalid encoding type';
		else {
			$encoding = strtolower($encoding);
			if (!isset(self::$mencarr[$encoding])) $err[] = 'invalid encoding value';
		}
		if (count($err) > 0) FUNC5::trace($debug, implode(', ', $err));
		else {
			if ($encoding == 'base64') {
				$str = trim(FUNC5::str_clear($str));
				return base64_decode($str);
			} else if ($encoding == 'quoted-printable') {
				return quoted_printable_decode($str);
			} else return $str;
		}
	}

	static public function message($content = null, $type = null, $name = null, $charset = null, $encoding = null, $disposition = null, $id = null, $len = null, $end = null, $debug = null) {
		if (!FUNC5::is_debug($debug)) $debug = debug_backtrace();
		$err = array();
		if (!(is_string($content) && $content != '')) $err[] = 'invalid content type';
		if ($type == null) $type = 'application/octet-stream';
		else if (is_string($type)) {
			$type = trim(FUNC5::str_clear($type));
			if (strlen($type) < 4) $err[] = 'invalid type value';
		} else $err[] = 'invalid type';
		if (is_string($name)) {
			$name = trim(FUNC5::str_clear($name));
			if ($name == '') $err[] = 'invalid name value';
		} else if ($name != null) $err[] = 'invalid name type';
		if ($charset == null) $charset = self::MCHARSET;
		else if (!is_string($charset)) $err[] = 'invalid charset type';
		else if (!(strlen($charset) >= 2 && FUNC5::is_alpha($charset, true, '-'))) $err[] = 'invalid charset value';
		if ($encoding == null) $encoding = self::MENCDEF;
		else if (!is_string($encoding)) $err[] = 'invalid encoding type';
		else {
			$encoding = strtolower(FUNC5::str_clear($encoding));
			if (!isset(self::$mencarr[$encoding])) $err[] = 'invalid encoding value';
		}
		if ($disposition == null) $disposition = 'inline';
		else if (is_string($disposition)) {
			$disposition = strtolower(FUNC5::str_clear($disposition));
			if (!($disposition == 'inline' || $disposition == 'attachment')) $err[] = 'invalid disposition value';
		} else $err[] = 'invalid disposition type';
		if (is_string($id)) {
			$id = FUNC5::str_clear($id, array(' '));
			if ($id == '') $err[] = 'invalid id value';
		} else if ($id != null) $err[] = 'invalid id type';
		if ($len == null) $len = self::MLEN;
		else if (!(is_int($len) && $len > 1)) $err[] = 'invalid line length value';
		if ($end == null) $end = self::LE;
		else if (!is_string($end)) $err[] = 'invalid line end value';
		if (count($err) > 0) FUNC5::trace($debug, implode(', ', $err));
		else {
			$header = ''.
				'Content-Type: '.$type.';'.$end."\t".'charset="'.$charset.'"'.
				(($name == null) ? '' : ';'.$end."\t".'name="'.$name.'"').$end.
				'Content-Transfer-Encoding: '.$encoding.$end.
				'Content-Disposition: '.$disposition.
				(($name == null) ? '' : ';'.$end."\t".'filename="'.$name.'"').
				(($id == null) ? '' : $end.'Content-ID: <'.$id.'>');
			if ($encoding == '7bit' || $encoding == '8bit') $content = wordwrap(self::fix_eol($content), $len, $end, true);
			else if ($encoding == 'base64') $content = rtrim(chunk_split(base64_encode($content), $len, $end));
			else if ($encoding == 'quoted-printable') $content = self::qp_encode(self::fix_eol($content), $len, $end);
			return array('header' => $header, 'content' => $content);
		}
	}

	static public function compose($text = null, $html = null, $attach = null, $uniq = null, $end = null, $debug = null) {
		if (!FUNC5::is_debug($debug)) $debug = debug_backtrace();
		$err = array();
		if ($text == null && $html == null) $err[] = 'message is not set';
		else {
			if ($text != null) {
				if (!(is_array($text) && isset($text['header'], $text['content']) && is_string($text['header']) && is_string($text['content']) && self::isset_header($text['header'], 'content-type', 'text/plain', $debug))) $err[] = 'invalid text message type';
			}
			if ($html != null) {
				if (!(is_array($html) && isset($html['header'], $html['content']) && is_string($html['header']) && is_string($html['content']) && self::isset_header($html['header'], 'content-type', 'text/html', $debug))) $err[] = 'invalid html message type';
			}
		}
		if ($attach != null) {
			if (is_array($attach) && count($attach) > 0) {
				foreach ($attach as $arr) {
					if (!(is_array($arr) && isset($arr['header'], $arr['content']) && is_string($arr['header']) && is_string($arr['content']) && (self::isset_header($arr['header'], 'content-disposition', 'inline', $debug) || self::isset_header($arr['header'], 'content-disposition', 'attachment', $debug)))) {
						$err[] = 'invalid attachment type';
						break;
					}
				}
			} else $err[] = 'invalid attachment format';
		}
		if ($end == null) $end = self::LE;
		else if (!is_string($end)) $err[] = 'invalid line end value';
		if (count($err) > 0) FUNC5::trace($debug, implode(', ', $err));
		else {
			$multipart = false;
			if ($text && $html) $multipart = true;
			if ($attach) $multipart = true;
			$header = $body = array();
			$header[] = 'Date: '.date('r');
			$header[] = base64_decode('WC1NYWlsZXI6IFhQTTQgdi4wLjUgPCB3d3cueHBlcnRtYWlsZXIuY29tID4=');
			if ($multipart) {
				$uniq = ($uniq == null) ? 0 : intval($uniq);
				$boundary1 = '=_1.'.self::unique($uniq++);
				$boundary2 = '=_2.'.self::unique($uniq++);
				$boundary3 = '=_3.'.self::unique($uniq++);
				$disp['inline'] = $disp['attachment'] = false;
				if ($attach != null) {
					foreach ($attach as $darr) {
						if (self::isset_header($darr['header'], 'content-disposition', 'inline', $debug)) $disp['inline'] = true;
						else if (self::isset_header($darr['header'], 'content-disposition', 'attachment', $debug)) $disp['attachment'] = true;
					}
				}
				$hstr = 'Content-Type: multipart/%s;'.$end."\t".'boundary="%s"';
				$bstr = '--%s'.$end.'%s'.$end.$end.'%s';
				$body[] = 'This is a message in MIME Format. If you see this, your mail reader does not support this format.'.$end;
				if ($text && $html) {
						if ($disp['inline'] && $disp['attachment']) {
							$header[] = sprintf($hstr, 'mixed', $boundary1);
							$body[] = '--'.$boundary1;
							$body[] = sprintf($hstr, 'related', $boundary2).$end;
							$body[] = '--'.$boundary2;
							$body[] = sprintf($hstr, 'alternative', $boundary3).$end;
							$body[] = sprintf($bstr, $boundary3, $text['header'], $text['content']);
							$body[] = sprintf($bstr, $boundary3, $html['header'], $html['content']);
							$body[] = '--'.$boundary3.'--';
							foreach ($attach as $desc) if (self::isset_header($desc['header'], 'content-disposition', 'inline', $debug)) $body[] = sprintf($bstr, $boundary2, $desc['header'], $desc['content']);
							$body[] = '--'.$boundary2.'--';
							foreach ($attach as $desc) if (self::isset_header($desc['header'], 'content-disposition', 'attachment', $debug)) $body[] = sprintf($bstr, $boundary1, $desc['header'], $desc['content']);
							$body[] = '--'.$boundary1.'--';
						} else if ($disp['inline']) {
							$header[] = sprintf($hstr, 'related', $boundary1);
							$body[] = '--'.$boundary1;
							$body[] = sprintf($hstr, 'alternative', $boundary2).$end;
							$body[] = sprintf($bstr, $boundary2, $text['header'], $text['content']);
							$body[] = sprintf($bstr, $boundary2, $html['header'], $html['content']);
							$body[] = '--'.$boundary2.'--';
							foreach ($attach as $desc) $body[] = sprintf($bstr, $boundary1, $desc['header'], $desc['content']);
							$body[] = '--'.$boundary1.'--';
						} else if ($disp['attachment']) {
							$header[] = sprintf($hstr, 'mixed', $boundary1);
							$body[] = '--'.$boundary1;
							$body[] = sprintf($hstr, 'alternative', $boundary2).$end;
							$body[] = sprintf($bstr, $boundary2, $text['header'], $text['content']);
							$body[] = sprintf($bstr, $boundary2, $html['header'], $html['content']);
							$body[] = '--'.$boundary2.'--';
							foreach ($attach as $desc) $body[] = sprintf($bstr, $boundary1, $desc['header'], $desc['content']);
							$body[] = '--'.$boundary1.'--';
						} else {
							$header[] = sprintf($hstr, 'alternative', $boundary1);
							$body[] = sprintf($bstr, $boundary1, $text['header'], $text['content']);
							$body[] = sprintf($bstr, $boundary1, $html['header'], $html['content']);
							$body[] = '--'.$boundary1.'--';
						}
				} else if ($text) {
					$header[] = sprintf($hstr, 'mixed', $boundary1);
					$body[] = sprintf($bstr, $boundary1, $text['header'], $text['content']);
					foreach ($attach as $desc) $body[] = sprintf($bstr, $boundary1, $desc['header'], $desc['content']);
					$body[] = '--'.$boundary1.'--';
				} else if ($html) {
					if ($disp['inline'] && $disp['attachment']) {
						$header[] = sprintf($hstr, 'mixed', $boundary1);
						$body[] = '--'.$boundary1;
						$body[] = sprintf($hstr, 'related', $boundary2).$end;
						$body[] = sprintf($bstr, $boundary2, $html['header'], $html['content']);
						foreach ($attach as $desc) if (self::isset_header($desc['header'], 'content-disposition', 'inline', $debug)) $body[] = sprintf($bstr, $boundary2, $desc['header'], $desc['content']);
						$body[] = '--'.$boundary2.'--';
						foreach ($attach as $desc) if (self::isset_header($desc['header'], 'content-disposition', 'attachment', $debug)) $body[] = sprintf($bstr, $boundary1, $desc['header'], $desc['content']);
						$body[] = '--'.$boundary1.'--';
					} else if ($disp['inline']) {
						$header[] = sprintf($hstr, 'related', $boundary1);
						$body[] = sprintf($bstr, $boundary1, $html['header'], $html['content']);
						foreach ($attach as $desc) $body[] = sprintf($bstr, $boundary1, $desc['header'], $desc['content']);
						$body[] = '--'.$boundary1.'--';
					} else if ($disp['attachment']) {
						$header[] = sprintf($hstr, 'mixed', $boundary1);
						$body[] = sprintf($bstr, $boundary1, $html['header'], $html['content']);
						foreach ($attach as $desc) $body[] = sprintf($bstr, $boundary1, $desc['header'], $desc['content']);
						$body[] = '--'.$boundary1.'--';
					}
				}
			} else {
				if ($text) {
					$header[] = $text['header'];
					$body[] = $text['content'];
				} else if ($html) {
					$header[] = $html['header'];
					$body[] = $html['content'];
				}
			}
			$header[] = 'MIME-Version: 1.0';
			return array('header' => implode($end, $header), 'content' => implode($end, $body));
		}
	}

	static public function isset_header($str = null, $name = null, $value = null, $debug = null) {
		if (!FUNC5::is_debug($debug)) $debug = debug_backtrace();
		$err = array();
		if (!(is_string($str) && $str != '')) $err[] = 'invalid header type';
		if (!(is_string($name) && strlen($name) > 1 && FUNC5::is_alpha($name, true, '-'))) $err[] = 'invalid name type';
		if ($value != null && !is_string($value)) $err[] = 'invalid value type';
		if (count($err) > 0) FUNC5::trace($debug, implode(', ', $err));
		else {
			$ret = false;
			if ($exp = self::split_header($str, $debug)) {
				foreach ($exp as $harr) {
					if (strtolower($harr['name']) == strtolower($name)) {
						if ($value != null) $ret = (strtolower($harr['value']) == strtolower($value)) ? $harr['value'] : false;
						else $ret = $harr['value'];
						if ($ret) break;
					}
				}
			}
			return $ret;
		}
	}

	static public function split_header($str = null, $debug = null) {
		if (!FUNC5::is_debug($debug)) $debug = debug_backtrace();
		if (!(is_string($str) && $str != '')) FUNC5::trace($debug, 'invalid header value');
		else {
			$str = str_replace(array(";\r\n\t", "; \r\n\t", ";\r\n ", "; \r\n "), '; ', $str);
			$str = str_replace(array(";\n\t", "; \n\t", ";\n ", "; \n "), '; ', $str);
			$str = str_replace(array("\r\n\t", "\r\n "), '', $str);
			$str = str_replace(array("\n\t", "\n "), '', $str);
			$arr = array();
			foreach (explode("\n", $str) as $line) {
				$line = trim(FUNC5::str_clear($line));
				if ($line != '') {
					if (count($exp1 = explode(':', $line, 2)) == 2) {
						$name = rtrim($exp1[0]);
						$val1 = ltrim($exp1[1]);
						if (strlen($name) > 1 && FUNC5::is_alpha($name, true, '-') && $val1 != '') {
							$name = ucfirst($name);
							$hadd = array();
							if (substr(strtolower($name), 0, 8) == 'content-') {
								$exp2 = explode('; ', $val1);
								$cnt2 = count($exp2);
								if ($cnt2 > 1) {
									for ($i = 1; $i < $cnt2; $i++) {
										if (count($exp3 = explode('=', $exp2[$i], 2)) == 2) {
											$hset = trim($exp3[0]);
											$hval = trim($exp3[1], ' "');
											if ($hset != '' && $hval != '') $hadd[strtolower($hset)] = $hval;
										}
									}
								}
							}
							$val2 = (count($hadd) > 0) ? trim($exp2[0]) : $val1;
							$arr[] = array('name' => $name, 'value' => $val2, 'content' => $hadd);
						}
					}
				}
			}
			if (count($arr) > 0) return $arr;
			else FUNC5::trace($debug, 'invalid header value', 1);
		}
	}

	static public function split_message($str = null, $debug = null) {
		if (!FUNC5::is_debug($debug)) $debug = debug_backtrace();
		if (!(is_string($str) && $str != '')) FUNC5::trace($debug, 'invalid message value');
		else {
			$ret = false;
			if (strpos($str, "\r\n\r\n")) $ret = explode("\r\n\r\n", $str, 2);
			else if (strpos($str, "\n\n")) $ret = explode("\n\n", $str, 2);
			if ($ret) return array('header' => trim($ret[0]), 'content' => $ret[1]);
			else return false;
		}
	}

	static public function split_mail($str = null, &$headers, &$body, $debug = null) {
		if (!FUNC5::is_debug($debug)) $debug = debug_backtrace();
		$headers = $body = false;
		if (!$part = self::split_message($str, $debug)) return false;
		if (!$harr = self::split_header($part['header'], $debug)) return false;
		$type = $boundary = false;
		foreach ($harr as $hnum) {
			if (strtolower($hnum['name']) == 'content-type') {
				$type = strtolower($hnum['value']);
				foreach ($hnum['content'] as $hnam => $hval) {
					if (strtolower($hnam) == 'boundary') {
						$boundary = $hval;
						break;
					}
				}
				if ($boundary) break;
			}
		}
		$headers = $harr;
		$body = array();
		if (substr($type, 0, strlen('multipart/')) == 'multipart/' && $boundary && strstr($part['content'], '--'.$boundary.'--')) $body = self::_parts($part['content'], $boundary, strtolower(substr($type, strlen('multipart/'))), $debug);
		if (count($body) == 0) $body[] = self::_content($str, $debug);
	}

	static private function _parts($str = null, $boundary = null, $multipart = null, $debug = null) {
		if (!FUNC5::is_debug($debug)) $debug = debug_backtrace();
		$err = array();
		if (!(is_string($str) && $str != '')) $err[] = 'invalid content value';
		if (!(is_string($boundary) && $boundary != '')) $err[] = 'invalid boundary value';
		if (!(is_string($multipart) && $multipart != '')) $err[] = 'invalid multipart value';
		if (count($err) > 0) FUNC5::trace($debug, implode(', ', $err));
		else {
			$ret = array();
			if (count($exp = explode('--'.$boundary.'--', $str)) == 2) {
				if (count($exp = explode('--'.$boundary, $exp[0])) > 2) {
					$cnt = 0;
					foreach ($exp as $split) {
						$cnt++;
						if ($cnt > 1 && $part = self::split_message($split, $debug)) {
							if ($harr = self::split_header($part['header'], $debug)) {
								$type = $newb = false;
								foreach ($harr as $hnum) {
									if (strtolower($hnum['name']) == 'content-type') {
										$type = strtolower($hnum['value']);
										foreach ($hnum['content'] as $hnam => $hval) {
											if (strtolower($hnam) == 'boundary') {
												$newb = $hval;
												break;
											}
										}
										if ($newb) break;
									}
								}
								if (substr($type, 0, strlen('multipart/')) == 'multipart/' && $newb && strstr($part['content'], '--'.$newb.'--')) $ret = self::_parts($part['content'], $newb, $multipart.'|'.strtolower(substr($type, strlen('multipart/'))), $debug);
								else {
									$res = self::_content($split, $debug);
									$res['multipart'] = $multipart;
									$ret[] = $res;
								}
							}
						}
					}
				}
			}
			return $ret;
		}
	}

	static private function _content($str = null, $debug = null) {
		if (!FUNC5::is_debug($debug)) $debug = debug_backtrace();
		if (!(is_string($str) && $str != '')) FUNC5::trace($debug, 'invalid content value');
		else {
			if (!$part = self::split_message($str, $debug)) return null;
			if (!$harr = self::split_header($part['header'], $debug)) return null;
			$body = array();
			$clen = strlen('content-');
			$encoding = false;
			foreach ($harr as $hnum) {
				if (substr(strtolower($hnum['name']), 0, $clen) == 'content-') {
					$name = strtolower(substr($hnum['name'], $clen));
					if ($name == 'transfer-encoding') $encoding = strtolower($hnum['value']);
					else if ($name == 'id') $body[$name] = array('value' => trim($hnum['value'], '<>'), 'extra' => $hnum['content']);
					else $body[$name] = array('value' => $hnum['value'], 'extra' => $hnum['content']);
				}
			}
			if ($encoding == 'base64' || $encoding == 'quoted-printable') $body['content'] = self::decode_content($part['content'], $encoding, $debug);
			else {
				if ($encoding) $body['transfer-encoding'] = $encoding;
				$body['content'] = $part['content'];
			}
			if (substr($body['content'], -2) == "\r\n") $body['content'] = substr($body['content'], 0, -2);
			else if (substr($body['content'], -1) == "\n") $body['content'] = substr($body['content'], 0, -1);
			return $body;
		}
	}

	static public function fix_eol($str = null, $debug = null) {
		if (!FUNC5::is_debug($debug)) $debug = debug_backtrace();
		if (!(is_string($str) && $str != '')) FUNC5::trace($debug, 'invalid content value');
		else {
			$str = str_replace("\r\n", "\n", $str);
			$str = str_replace("\r", "\n", $str);
			if (self::LE != "\n") $str = str_replace("\n", self::LE, $str);
			return $str;
		}
	}

}

class SMTP extends SMTP5 { }
class SMTP5 {

	const CRLF = "\r\n";
	const PORT = 25;
	const TOUT = 30;
	const COUT = 5;
	const BLEN = 1024;

	static private function _cres($conn = null, &$resp, $code1 = null, $code2 = null, $debug = null) {
		if (!FUNC5::is_debug($debug)) $debug = debug_backtrace();
		$err = array();
		if (!is_resource($conn)) $err[] = 'invalid resource connection';
		if (!(is_int($code1) && $code1 > 99 && $code1 < 1000)) $err[] = 'invalid 1 code value';
		if ($code2 != null) {
			if (!(is_int($code2) && $code2 > 99 && $code2 < 1000)) $err[] = 'invalid 2 code value';
		}
		if (count($err) > 0) return FUNC5::trace($debug, implode(', ', $err), 1);
		else {
			$ret = true;
			do {
				if ($result = fgets($conn, self::BLEN)) {
					$resp[] = $result;
					$rescode = substr($result, 0, 3);
					if (!($rescode == $code1 || $rescode == $code2)) {
						$ret = false;
						break;
					}
				} else {
					$resp[] = 'can not read';
					$ret = false;
					break;
				}
			} while ($result[3] == '-');
			return $ret;
		}
	}

	static public function mxconnect($host = null, $port = null, $tout = null, $name = null, $context = null, $debug = null) {
		global $_RESULT;
		$_RESULT = array();
		if (!FUNC5::is_debug($debug)) $debug = debug_backtrace();
		if (!is_string($host)) FUNC5::trace($debug, 'invalid host type');
		else {
			$host = strtolower(trim($host));
			if (!($host != '' && FUNC5::is_hostname($host, true, $debug))) FUNC5::trace($debug, 'invalid host value');
		}
		$res = FUNC5::is_win() ? FUNC5::getmxrr_win($host, $arr, $debug) : getmxrr($host, $arr);
		$con = false;
		if ($res) {
			foreach ($arr as $mx) {
				if ($con = self::connect($mx, $port, null, null, null, $tout, $name, $context, null, $debug)) break;
			}
		}
		if (!$con) $con = self::connect($host, $port, null, null, null, $tout, $name, $context, null, $debug);
		return $con;
	}

	static public function connect($host = null, $port = null, $user = null, $pass = null, $vssl = null, $tout = null, $name = null, $context = null, $login = null, $debug = null) {
		if (!FUNC5::is_debug($debug)) $debug = debug_backtrace();
		global $_RESULT;
		$_RESULT = $err = array();
		if ($port == null) $port = self::PORT;
		if ($tout == null) $tout = self::TOUT;
		if (!is_string($host)) $err[] = 'invalid host type';
		else {
			$host = strtolower(trim($host));
			if (!($host != '' && ($host == 'localhost' || FUNC5::is_ipv4($host) || FUNC5::is_hostname($host, true, $debug)))) $err[] = 'invalid host value';
		}
		if (!(is_int($port) && $port > 0)) $err[] = 'invalid port value';
		if ($user != null) {
			if (!is_string($user)) $err[] = 'invalid username type';
			else if (($user = FUNC5::str_clear($user)) == '') $err[] = 'invalid username value';
		}
		if ($pass != null) {
			if (!is_string($pass)) $err[] = 'invalid password type';
			else if (($pass = FUNC5::str_clear($pass)) == '') $err[] = 'invalid password value';
		}
		if (($user != null && $pass == null) || ($user == null && $pass != null)) $err[] = 'invalid username/password combination';
		if ($vssl != null) {
			if (!is_string($vssl)) $err[] = 'invalid ssl version type';
			else {
				$vssl = strtolower($vssl);
				if (!($vssl == 'tls' || $vssl == 'ssl' || $vssl == 'sslv2' || $vssl == 'sslv3')) $err[] = 'invalid ssl version value';
			}
		}
		if (!(is_int($tout) && $tout > 0)) $err[] = 'invalid timeout value';
		if ($name != null) {
			if (!is_string($name)) $err[] = 'invalid name type';
			else {
				$name = strtolower(trim($name));
				if (!($name != '' && ($name == 'localhost' || FUNC5::is_ipv4($name) || FUNC5::is_hostname($name, true, $debug)))) $err[] = 'invalid name value';
			}
		} else $name = '127.0.0.1';
		if ($context != null && !is_resource($context)) $err[] = 'invalid context type';
		if ($login != null) {
			$login = strtolower(trim($login));
			if (!($login == 'login' || $login == 'plain' || $login == 'cram-md5')) $err[] = 'invalid authentication type value';
		}
		if (count($err) > 0) FUNC5::trace($debug, implode(', ', $err));
		else {
			$ret = false;
			$prt = ($vssl == null) ? 'tcp' : $vssl;
			$conn = ($context == null) ? stream_socket_client($prt.'://'.$host.':'.$port, $errno, $errstr, $tout) : stream_socket_client($prt.'://'.$host.':'.$port, $errno, $errstr, $tout, STREAM_CLIENT_CONNECT, $context);
			if (!$conn) $_RESULT[101] = $errstr;
			else if (!stream_set_timeout($conn, self::COUT)) $_RESULT[102] = 'could not set stream timeout';
			else if (!self::_cres($conn, $resp, 220, null, $debug)) $_RESULT[103] = $resp;
			else {
				$continue = true;
				if (!self::ehlo($conn, $name, $debug)) $continue = self::helo($conn, $name, $debug);
				if ($continue) {
					if ($user == null) $ret = true;
					else if ($login != null) $ret = self::auth($conn, $user, $pass, $login, $debug);
					else {
						list($code, $arr) = each($_RESULT);
						$auth['default'] = $auth['login'] = $auth['plain'] = $auth['cram-md5'] = false;
						foreach ($arr as $line) {
							if (substr($line, 0, strlen('250-AUTH ')) == '250-AUTH ') {
								foreach (explode(' ', substr($line, strlen('250-AUTH '))) as $type) {
									$type = strtolower(trim($type));
									if ($type == 'login' || $type == 'plain' || $type == 'cram-md5') $auth[$type] = true;
								}
							} else if (substr($line, 0, strlen('250 AUTH=')) == '250 AUTH=') {
								$expl = explode(' ', strtolower(trim(substr($line, strlen('250 AUTH=')))), 2);
								if ($expl[0] == 'login' || $expl[0] == 'plain' || $expl[0] == 'cram-md5') $auth['default'] = $expl[0];
							}
						}
						if ($auth['default']) $ret = self::auth($conn, $user, $pass, $auth['default'], $debug);
						if (!$ret && $auth['login'] && $auth['default'] != 'login') $ret = self::auth($conn, $user, $pass, 'login', $debug);
						if (!$ret && $auth['plain'] && $auth['default'] != 'plain') $ret = self::auth($conn, $user, $pass, 'plain', $debug);
						if (!$ret && $auth['cram-md5'] && $auth['default'] != 'cram-md5') $ret = self::auth($conn, $user, $pass, 'cram-md5', $debug);
						if (!$ret && !$auth['login'] && $auth['default'] != 'login') $ret = self::auth($conn, $user, $pass, 'login', $debug);
						if (!$ret && !$auth['plain'] && $auth['default'] != 'plain') $ret = self::auth($conn, $user, $pass, 'plain', $debug);
						if (!$ret && !$auth['cram-md5'] && $auth['default'] != 'cram-md5') $ret = self::auth($conn, $user, $pass, 'cram-md5', $debug);
					}
				}
			}
			if (!$ret) {
				if (is_resource($conn)) self::disconnect($conn, $debug);
				$conn = false;
			}
			return $conn;
		}
	}

	static public function send($conn = null, $addrs = null, $mess = null, $from = null, $debug = null) {
		if (!FUNC5::is_debug($debug)) $debug = debug_backtrace();
		global $_RESULT;
		$_RESULT = $err = array();
		if (!is_resource($conn)) $err[] = 'invalid resource connection';
		if (!is_array($addrs)) $err[] = 'invalid to address type';
		else {
			$aver = true;
			if (count($addrs) > 0) {
				foreach ($addrs as $addr) {
					if (!FUNC5::is_mail($addr)) {
						$aver = false;
						break;
					}
				}
			} else $aver = false;
			if (!$aver) $err[] = 'invalid to address value';
		}
		if (!is_string($mess)) $err[] = 'invalid message value';
		if ($from == null) {
			$from = @ini_get('sendmail_from');
			if ($from == '' || !FUNC5::is_mail($from)) $from = (isset($_SERVER['SERVER_ADMIN']) && FUNC5::is_mail($_SERVER['SERVER_ADMIN'])) ? $_SERVER['SERVER_ADMIN'] : 'postmaster@localhost';
		} else {
			if (!is_string($from)) $err[] = 'invalid from address type';
			else if (!($from != '' && FUNC5::is_mail($from))) $err[] = 'invalid from address value';
		}
		if (count($err) > 0) FUNC5::trace($debug, implode(', ', $err));
		else {
			$ret = false;
			if (self::from($conn, $from, $debug)) {
				$continue = true;
				foreach ($addrs as $dest) {
					if (!self::to($conn, $dest, $debug)) {
						$continue = false;
						break;
					}
				}
				if ($continue) {
					if (self::data($conn, $mess, $debug)) $ret = self::rset($conn, $debug);
				}
			}
			return $ret;
		}
	}

	static public function disconnect($conn = null, $debug = null) {
		if (!FUNC5::is_debug($debug)) $debug = debug_backtrace();
		global $_RESULT;
		$_RESULT = array();
		if (!is_resource($conn)) return FUNC5::trace($debug, 'invalid resource connection', 1);
		else {
			if (!fwrite($conn, 'QUIT'.self::CRLF)) $_RESULT[300] = 'can not write';
			else $_RESULT[301] = 'Send QUIT';
			return @fclose($conn);
		}
	}

	static public function quit($conn = null, $debug = null) {
		if (!FUNC5::is_debug($debug)) $debug = debug_backtrace();
		global $_RESULT;
		$_RESULT = array();
		$ret = false;
		if (!is_resource($conn)) FUNC5::trace($debug, 'invalid resource connection');
		else if (!fwrite($conn, 'QUIT'.self::CRLF)) $_RESULT[302] = 'can not write';
		else {
			$_RESULT[303] = ($vget = @fgets($conn, self::BLEN)) ? $vget : 'can not read';
			$ret = true;
		}
		return $ret;
	}

	static public function helo($conn = null, $host = null, $debug = null) {
		if (!FUNC5::is_debug($debug)) $debug = debug_backtrace();
		global $_RESULT;
		$_RESULT = $err = array();
		if (!is_resource($conn)) $err[] = 'invalid resource connection';
		if (!is_string($host)) $err[] = 'invalid host type';
		else {
			$host = strtolower(trim($host));
			if (!($host != '' && ($host == 'localhost' || FUNC5::is_ipv4($host) || FUNC5::is_hostname($host, true, $debug)))) $err[] = 'invalid host value';
		}
		if (count($err) > 0) FUNC5::trace($debug, implode(', ', $err));
		else {
			$ret = false;
			if (!fwrite($conn, 'HELO '.$host.self::CRLF)) $_RESULT[304] = 'can not write';
			else if (!self::_cres($conn, $resp, 250, null, $debug)) $_RESULT[305] = $resp;
			else {
				$_RESULT[306] = $resp;
				$ret = true;
			}
			return $ret;
		}
	}

	static public function ehlo($conn = null, $host = null, $debug = null) {
		if (!FUNC5::is_debug($debug)) $debug = debug_backtrace();
		global $_RESULT;
		$_RESULT = $err = array();
		if (!is_resource($conn)) $err[] = 'invalid resource connection';
		if (!is_string($host)) $err[] = 'invalid host type';
		else {
			$host = strtolower(trim($host));
			if (!($host != '' && ($host == 'localhost' || FUNC5::is_ipv4($host) || FUNC5::is_hostname($host, true, $debug)))) $err[] = 'invalid host value';
		}
		if (count($err) > 0) FUNC5::trace($debug, implode(', ', $err));
		else {
			$ret = false;
			if (!fwrite($conn, 'EHLO '.$host.self::CRLF)) $_RESULT[307] = 'can not write';
			else if (!self::_cres($conn, $resp, 250, null, $debug)) $_RESULT[308] = $resp;
			else {
				$_RESULT[309] = $resp;
				$ret = true;
			}
			return $ret;
		}
	}

	static public function auth($conn = null, $user = null, $pass = null, $type = null, $debug = null) {
		if (!FUNC5::is_debug($debug)) $debug = debug_backtrace();
		global $_RESULT;
		$_RESULT = $err = array();
		if (!is_resource($conn)) $err[] = 'invalid resource connection';
		if (!is_string($user)) $err[] = 'invalid username type';
		else if (($user = FUNC5::str_clear($user)) == '') $err[] = 'invalid username value';
		if (!is_string($pass)) $err[] = 'invalid password type';
		else if (($pass = FUNC5::str_clear($pass)) == '') $err[] = 'invalid password value';
		if ($type == null) $type = 'login';
		if (!is_string($type)) $err[] = 'invalid authentication type';
		else {
			$type = strtolower(trim($type));
			if (!($type == 'login' || $type == 'plain' || $type == 'cram-md5')) $err[] = 'invalid authentication type value';
		}
		if (count($err) > 0) FUNC5::trace($debug, implode(', ', $err));
		else {
			$ret = false;
			if ($type == 'login') {
				if (!fwrite($conn, 'AUTH LOGIN'.self::CRLF)) $_RESULT[310] = 'can not write';
				else if (!self::_cres($conn, $resp, 334, null, $debug)) $_RESULT[311] = $resp;
				else if (!fwrite($conn, base64_encode($user).self::CRLF)) $_RESULT[312] = 'can not write';
				else if (!self::_cres($conn, $resp, 334, null, $debug)) $_RESULT[313] = $resp;
				else if (!fwrite($conn, base64_encode($pass).self::CRLF)) $_RESULT[314] = 'can not write';
				else if (!self::_cres($conn, $resp, 235, null, $debug)) $_RESULT[315] = $resp;
				else {
					$_RESULT[316] = $resp;
					$ret = true;
				}
			} else if ($type == 'plain') {
				if (!fwrite($conn, 'AUTH PLAIN '.base64_encode($user.chr(0).$user.chr(0).$pass).self::CRLF)) $_RESULT[317] = 'can not write';
				else if (!self::_cres($conn, $resp, 235, null, $debug)) $_RESULT[318] = $resp;
				else {
					$_RESULT[319] = $resp;
					$ret = true;
				}
			} else if ($type == 'cram-md5') {
				if (!fwrite($conn, 'AUTH CRAM-MD5'.self::CRLF)) $_RESULT[200] = 'can not write';
				else if (!self::_cres($conn, $resp, 334, null, $debug)) $_RESULT[201] = $resp;
				else {
					if (strlen($pass) > 64) $pass = pack('H32', md5($pass));
					if (strlen($pass) < 64) $pass = str_pad($pass, 64, chr(0));
					$pad1 = substr($pass, 0, 64) ^ str_repeat(chr(0x36), 64);
					$pad2 = substr($pass, 0, 64) ^ str_repeat(chr(0x5C), 64);
					$chal = substr($resp[count($resp)-1], 4);
					$innr = pack('H32', md5($pad1.base64_decode($chal)));
					if (!fwrite($conn, base64_encode($user.' '.md5($pad2.$innr)).self::CRLF)) $_RESULT[202] = 'can not write';
					else if (!self::_cres($conn, $resp, 235, null, $debug)) $_RESULT[203] = $resp;
					else {
						$_RESULT[204] = $resp;
						$ret = true;
					}
				}
			}
			return $ret;
		}
	}

	static public function from($conn = null, $addr = null, $debug = null) {
		if (!FUNC5::is_debug($debug)) $debug = debug_backtrace();
		global $_RESULT;
		$_RESULT = $err = array();
		if (!is_resource($conn)) $err[] = 'invalid resource connection';
		if (!is_string($addr)) $err[] = 'invalid from address type';
		else if (!($addr != '' && FUNC5::is_mail($addr))) $err[] = 'invalid from address value';
		if (count($err) > 0) FUNC5::trace($debug, implode(', ', $err));
		else {
			$ret = false;
			if (!fwrite($conn, 'MAIL FROM:<'.$addr.'>'.self::CRLF)) $_RESULT[320] = 'can not write';
			else if (!self::_cres($conn, $resp, 250, null, $debug)) $_RESULT[321] = $resp;
			else {
				$_RESULT[322] = $resp;
				$ret = true;
			}
			return $ret;
		}
	}

	static public function to($conn = null, $addr = null, $debug = null) {
		if (!FUNC5::is_debug($debug)) $debug = debug_backtrace();
		global $_RESULT;
		$_RESULT = $err = array();
		if (!is_resource($conn)) $err[] = 'invalid resource connection';
		if (!is_string($addr)) $err[] = 'invalid to address type';
		else if (!($addr != '' && FUNC5::is_mail($addr))) $err[] = 'invalid to address value';
		if (count($err) > 0) FUNC5::trace($debug, implode(', ', $err));
		else {
			$ret = false;
			if (!fwrite($conn, 'RCPT TO:<'.$addr.'>'.self::CRLF)) $_RESULT[323] = 'can not write';
			else if (!self::_cres($conn, $resp, 250, 251, $debug)) $_RESULT[324] = $resp;
			else {
				$_RESULT[325] = $resp;
				$ret = true;
			}
			return $ret;
		}
	}

	static public function data($conn = null, $mess = null, $debug = null) {
		if (!FUNC5::is_debug($debug)) $debug = debug_backtrace();
		global $_RESULT;
		$_RESULT = $err = array();
		if (!is_resource($conn)) $err[] = 'invalid resource connection';
		if (!(is_string($mess) && $mess != '')) $err[] = 'invalid message value';
		if (count($err) > 0) FUNC5::trace($debug, implode(', ', $err));
		else {
			$ret = false;
			if (!fwrite($conn, 'DATA'.self::CRLF)) $_RESULT[326] = 'can not write';
			else if (!self::_cres($conn, $resp, 354, null, $debug)) $_RESULT[327] = $resp;
			else {
				$continue = true;
				foreach (explode(self::CRLF, $mess) as $line) {
					if ($line != '' && $line[0] == '.') $line = '.'.$line;
					if (!fwrite($conn, $line.self::CRLF)) {
						$_RESULT[328] = 'can not write';
						$continue = false;
						break;
					}
				}
				if ($continue) {
					if (!fwrite($conn, '.'.self::CRLF)) $_RESULT[329] = 'can not write';
					else if (!self::_cres($conn, $resp, 250, null, $debug)) $_RESULT[330] = $resp;
					else {
						$_RESULT[331] = $resp;
						$ret = true;
					}
				}
			}
			return $ret;
		}
	}

	static public function rset($conn = null, $debug = null) {
		if (!FUNC5::is_debug($debug)) $debug = debug_backtrace();
		global $_RESULT;
		$_RESULT = array();
		$ret = false;
		if (!is_resource($conn)) FUNC5::trace($debug, 'invalid resource connection');
		else if (!fwrite($conn, 'RSET'.self::CRLF)) $_RESULT[332] = 'can not write';
		else if (!self::_cres($conn, $resp, 250, null, $debug)) $_RESULT[333] = $resp;
		else {
			$_RESULT[334] = $resp;
			$ret = true;
		}
		return $ret;
	}

	static public function recv($conn = null, $code1 = null, $code2 = null, $debug = null) {
		if (!FUNC5::is_debug($debug)) $debug = debug_backtrace();
		global $_RESULT;
		$_RESULT = array();
		$ret = false;
		if (!self::_cres($conn, $resp, $code1, $code2, $debug)) $_RESULT[335] = $resp;
		else {
			$_RESULT[336] = $resp;
			$ret = true;
		}
		return $ret;
	}

}

class MAIL extends MAIL5 { }
class MAIL5 {

	public $From = null;
	public $To = array();
	public $Cc = array();
	public $Bcc = array();

	public $Subject = null;
	public $Text = null;
	public $Html = null;
	public $Header = array();
	public $Attach = array();

	public $Host = null;
	public $Port = null;
	public $User = null;
	public $Pass = null;
	public $Vssl = null;
	public $Tout = null;
	public $Auth = null;

	public $Name = null;
	public $Path = null;
	public $Priority = null;

	public $Context = null;

	public $SendMail = '/usr/sbin/sendmail';
	public $QMail = '/var/qmail/bin/sendmail';

	private $_conns = array();
	public $History = array();
	public $Result = null;

	public function __construct() {
		$this->_result(array(0 => 'initialize class'));
	}

	private function _result($data = array(), $ret = null) {
		$this->History[][strval(microtime(true))] = $data;
		$this->Result = $data;
		return $ret;
	}

	public function context($arr = null, $debug = null) {
		if (!FUNC5::is_debug($debug)) $debug = debug_backtrace();
		if (!is_array($arr)) FUNC5::trace($debug, 'invalid context type');
		else if (!is_resource($res = stream_context_create($arr))) FUNC5::trace($debug, 'invalid context value');
		else {
			$this->Context = $res;
			return $this->_result(array(0 => 'set context connection'), true);
		}
	}

	public function name($host = null, $debug = null) {
		if (!FUNC5::is_debug($debug)) $debug = debug_backtrace();
		if (!is_string($host)) FUNC5::trace($debug, 'invalid hostname type');
		else {
			$host = strtolower(trim($host));
			if (!($host != '' && ($host == 'localhost' || FUNC5::is_ipv4($host) || FUNC5::is_hostname($host, true, $debug)))) FUNC5::trace($debug, 'invalid hostname value');
			$this->Name = $host;
			return $this->_result(array(0 => 'set HELO/EHLO hostname'), true);
		}
	}

	public function path($addr = null, $debug = null) {
		if (!FUNC5::is_debug($debug)) $debug = debug_backtrace();
		if (!is_string($addr)) FUNC5::trace($debug, 'invalid address type');
		else {
			if (!($addr != '' && FUNC5::is_mail($addr))) FUNC5::trace($debug, 'invalid address value');
			$this->Path = $addr;
			return $this->_result(array(0 => 'set Return-Path address'), true);
		}
	}

	public function priority($level = null, $debug = null) {
		if (!FUNC5::is_debug($debug)) $debug = debug_backtrace();
		if ($level == null) {
			$this->Priority = null;
			return $this->_result(array(0 => 'unset priority'), true);
		} else if (is_int($level) || is_string($level)) {
			if (is_string($level)) $level = strtolower(trim(FUNC5::str_clear($level)));
			if ($level == 1 || $level == 3 || $level == 5 || $level == 'high' || $level == 'normal' || $level == 'low') {
				$this->Priority = $level;
				return $this->_result(array(0 => 'set priority'), true);
			} else FUNC5::trace($debug, 'invalid level value');
		} else FUNC5::trace($debug, 'invalid level type');
	}

	public function from($addr = null, $name = null, $charset = null, $encoding = null, $debug = null) {
		if (!FUNC5::is_debug($debug)) $debug = debug_backtrace();
		$err = array();
		if (!is_string($addr)) $err[] = 'invalid address type';
		else if (!FUNC5::is_mail($addr)) $err[] = 'invalid address value';
		if ($name != null) {
			if (!is_string($name)) $err[] = 'invalid name type';
			else {
				$name = trim(FUNC5::str_clear($name));
				if ($name == '') $err[] = 'invalid name value';
			}
		}
		if ($charset != null) {
			if (!is_string($charset)) $err[] = 'invalid charset type';
			else if (!(strlen($charset) >= 2 && FUNC5::is_alpha($charset, true, '-'))) $err[] = 'invalid charset value';
		}
		if ($encoding != null) {
			if (!is_string($encoding)) $err[] = 'invalid encoding type';
			else {
				$encoding = strtolower($encoding);
				if (!isset(MIME5::$hencarr[$encoding])) $err[] = 'invalid encoding value';
			}
		}
		if (count($err) > 0) FUNC5::trace($debug, implode(', ', $err));
		else {
			$this->From = array('address' => $addr, 'name' => $name, 'charset' => $charset, 'encoding' => $encoding);
			return $this->_result(array(0 => 'set From address'), true);
		}
	}

	public function addto($addr = null, $name = null, $charset = null, $encoding = null, $debug = null) {
		if (!FUNC5::is_debug($debug)) $debug = debug_backtrace();
		$err = array();
		if (!is_string($addr)) $err[] = 'invalid address type';
		else if (!FUNC5::is_mail($addr)) $err[] = 'invalid address value';
		if ($name != null) {
			if (!is_string($name)) $err[] = 'invalid name type';
			else {
				$name = trim(FUNC5::str_clear($name));
				if ($name == '') $err[] = 'invalid name value';
			}
		}
		if ($charset != null) {
			if (!is_string($charset)) $err[] = 'invalid charset type';
			else if (!(strlen($charset) >= 2 && FUNC5::is_alpha($charset, true, '-'))) $err[] = 'invalid charset value';
		}
		if ($encoding != null) {
			if (!is_string($encoding)) $err[] = 'invalid encoding type';
			else {
				$encoding = strtolower($encoding);
				if (!isset(MIME5::$hencarr[$encoding])) $err[] = 'invalid encoding value';
			}
		}
		if (count($err) > 0) FUNC5::trace($debug, implode(', ', $err));
		else {
			$find = false;
			if (count($this->To) > 0) {
				$ladr = strtolower($addr);
				foreach ($this->To as $to) {
					if ($ladr == strtolower($to['address'])) {
						FUNC5::trace($debug, 'duplicate To address "'.$addr.'"', 1);
						$find = true;
					}
				}
			}
			if ($find) return false;
			else {
				$this->To[] = array('address' => $addr, 'name' => $name, 'charset' => $charset, 'encoding' => $encoding);
				return $this->_result(array(0 => 'add To address'), true);
			}
		}
	}

	public function delto($addr = null, $debug = null) {
		if (!FUNC5::is_debug($debug)) $debug = debug_backtrace();
		if ($addr == null) {
			$this->To = array();
			return $this->_result(array(0 => 'delete all To addresses'), true);
		} else if (!(is_string($addr) && FUNC5::is_mail($addr))) {
			FUNC5::trace($debug, 'invalid address value');
		} else {
			$ret = false;
			$new = array();
			if (count($this->To) > 0) {
				$addr = strtolower($addr);
				foreach ($this->To as $to) {
					if ($addr == strtolower($to['address'])) $ret = true;
					else $new[] = $to;
				}
			}
			if ($ret) {
				$this->To = $new;
				return $this->_result(array(0 => 'delete To address'), true);
			} else return FUNC5::trace($debug, 'To address "'.$addr.'" not found', 1);
		}
	}

	public function addcc($addr = null, $name = null, $charset = null, $encoding = null, $debug = null) {
		if (!FUNC5::is_debug($debug)) $debug = debug_backtrace();
		$err = array();
		if (!is_string($addr)) $err[] = 'invalid address type';
		else if (!FUNC5::is_mail($addr)) $err[] = 'invalid address value';
		if ($name != null) {
			if (!is_string($name)) $err[] = 'invalid name type';
			else {
				$name = trim(FUNC5::str_clear($name));
				if ($name == '') $err[] = 'invalid name value';
			}
		}
		if ($charset != null) {
			if (!is_string($charset)) $err[] = 'invalid charset type';
			else if (!(strlen($charset) >= 2 && FUNC5::is_alpha($charset, true, '-'))) $err[] = 'invalid charset value';
		}
		if ($encoding != null) {
			if (!is_string($encoding)) $err[] = 'invalid encoding type';
			else {
				$encoding = strtolower($encoding);
				if (!isset(MIME5::$hencarr[$encoding])) $err[] = 'invalid encoding value';
			}
		}
		if (count($err) > 0) FUNC5::trace($debug, implode(', ', $err));
		else {
			$find = false;
			if (count($this->Cc) > 0) {
				$ladr = strtolower($addr);
				foreach ($this->Cc as $cc) {
					if ($ladr == strtolower($cc['address'])) {
						FUNC5::trace($debug, 'duplicate Cc address "'.$addr.'"', 1);
						$find = true;
					}
				}
			}
			if ($find) return false;
			else {
				$this->Cc[] = array('address' => $addr, 'name' => $name, 'charset' => $charset, 'encoding' => $encoding);
				return $this->_result(array(0 => 'add Cc address'), true);
			}
		}
	}

	public function delcc($addr = null, $debug = null) {
		if (!FUNC5::is_debug($debug)) $debug = debug_backtrace();
		if ($addr == null) {
			$this->Cc = array();
			return $this->_result(array(0 => 'delete all Cc addresses'), true);
		} else if (!(is_string($addr) && FUNC5::is_mail($addr))) {
			FUNC5::trace($debug, 'invalid address value');
		} else {
			$ret = false;
			$new = array();
			if (count($this->Cc) > 0) {
				$addr = strtolower($addr);
				foreach ($this->Cc as $cc) {
					if ($addr == strtolower($cc['address'])) $ret = true;
					else $new[] = $cc;
				}
			}
			if ($ret) {
				$this->Cc = $new;
				return $this->_result(array(0 => 'delete Cc address'), true);
			} else return FUNC5::trace($debug, 'Cc address "'.$addr.'" not found', 1);
		}
	}

	public function addbcc($addr = null, $debug = null) {
		if (!FUNC5::is_debug($debug)) $debug = debug_backtrace();
		if (!is_string($addr)) FUNC5::trace($debug, 'invalid address type');
		else if (!FUNC5::is_mail($addr)) FUNC5::trace($debug, 'invalid address value');
		$find = false;
		if (count($this->Bcc) > 0) {
			$ladr = strtolower($addr);
			foreach ($this->Bcc as $bcc) {
				if ($ladr == strtolower($bcc)) {
					FUNC5::trace($debug, 'duplicate Bcc address "'.$addr.'"', 1);
					$find = true;
				}
			}
		}
		if ($find) return false;
		else {
			$this->Bcc[] = $addr;
			return $this->_result(array(0 => 'add Bcc address'), true);
		}
	}

	public function delbcc($addr = null, $debug = null) {
		if (!FUNC5::is_debug($debug)) $debug = debug_backtrace();
		if ($addr == null) {
			$this->Bcc = array();
			return $this->_result(array(0 => 'delete all Bcc addresses'), true);
		} else if (!(is_string($addr) && FUNC5::is_mail($addr))) {
			FUNC5::trace($debug, 'invalid address value');
		} else {
			$ret = false;
			$new = array();
			if (count($this->Bcc) > 0) {
				$addr = strtolower($addr);
				foreach ($this->Bcc as $bcc) {
					if ($addr == strtolower($bcc)) $ret = true;
					else $new[] = $bcc;
				}
			}
			if ($ret) {
				$this->Bcc = $new;
				return $this->_result(array(0 => 'delete Bcc address'), true);
			} else return FUNC5::trace($debug, 'Bcc address "'.$addr.'" not found', 1);
		}
	}

	public function addheader($name = null, $value = null, $charset = null, $encoding = null, $debug = null) {
		if (!FUNC5::is_debug($debug)) $debug = debug_backtrace();
		$err = array();
		if (!is_string($name)) $err[] = 'invalid name type';
		else {
			$name = ucfirst(trim(FUNC5::str_clear($name)));
			if (!(strlen($name) >= 2 && FUNC5::is_alpha($name, true, '-'))) $err[] = 'invalid name value';
		}
		if (!is_string($value)) $err[] = 'invalid content type';
		else {
			$value = trim(FUNC5::str_clear($value));
			if ($value == '') $err[] = 'invalid content value';
		}
		if ($charset != null) {
			if (!is_string($charset)) $err[] = 'invalid charset type';
			else if (!(strlen($charset) >= 2 && FUNC5::is_alpha($charset, true, '-'))) $err[] = 'invalid charset value';
		}
		if ($encoding != null) {
			if (!is_string($encoding)) $err[] = 'invalid encoding type';
			else {
				$encoding = strtolower($encoding);
				if (!isset(MIME5::$hencarr[$encoding])) $err[] = 'invalid encoding value';
			}
		}
		if (count($err) > 0) FUNC5::trace($debug, implode(', ', $err));
		else {
			$ver = strtolower($name);
			$err = false;
			if ($ver == 'to') $err = 'can not set "To", for this, use function "AddTo()"';
			else if ($ver == 'cc') $err = 'can not set "Cc", for this, use function "AddCc()"';
			else if ($ver == 'bcc') $err = 'can not set "Bcc", for this, use function "AddBcc()"';
			else if ($ver == 'from') $err = 'can not set "From", for this, use function "From()"';
			else if ($ver == 'subject') $err = 'can not set "Subject", for this, use function "Subject()"';
			else if ($ver == 'x-priority') $err = 'can not set "X-Priority", for this, use function "Priority()"';
			else if ($ver == 'x-msmail-priority') $err = 'can not set "X-MSMail-Priority", for this, use function "Priority()"';
			else if ($ver == 'x-mimeole') $err = 'can not set "X-MimeOLE", for this, use function "Priority()"';
			else if ($ver == 'date') $err = 'can not set "Date", this value is automaticaly set';
			else if ($ver == 'content-type') $err = 'can not set "Content-Type", this value is automaticaly set';
			else if ($ver == 'content-transfer-encoding') $err = 'can not set "Content-Transfer-Encoding", this value is automaticaly set';
			else if ($ver == 'content-disposition') $err = 'can not set "Content-Disposition", this value is automaticaly set';
			else if ($ver == 'mime-version') $err = 'can not set "Mime-Version", this value is automaticaly set';
			else if ($ver == 'x-mailer') $err = 'can not set "X-Mailer", this value is automaticaly set';
			else if ($ver == 'message-id') $err = 'can not set "Message-ID", this value is automaticaly set';
			if ($err) FUNC5::trace($debug, $err);
			else {
				$this->Header[] = array('name' => $name, 'value' => $value, 'charset' => $charset, 'encoding' => $encoding);
				return $this->_result(array(0 => 'add header'), true);
			}
		}
	}

	public function delheader($name = null, $debug = null) {
		if (!FUNC5::is_debug($debug)) $debug = debug_backtrace();
		if ($name == null) {
			$this->Header = array();
			return $this->_result(array(0 => 'delete all headers'), true);
		} else if (!(is_string($name) && strlen($name) >= 2 && FUNC5::is_alpha($name, true, '-'))) {
			FUNC5::trace($debug, 'invalid name value');
		} else {
			$ret = false;
			$new = array();
			if (count($this->Header) > 0) {
				$name = strtolower($name);
				foreach ($this->Header as $header) {
					if ($name == strtolower($header['name'])) $ret = true;
					else $new[] = $header;
				}
			}
			if ($ret) {
				$this->Header = $new;
				return $this->_result(array(0 => 'delete header'), true);
			} else return FUNC5::trace($debug, 'header not found', 1);
		}
	}

	public function subject($content = null, $charset = null, $encoding = null, $debug = null) {
		if (!FUNC5::is_debug($debug)) $debug = debug_backtrace();
		$err = array();
		if (!is_string($content)) $err[] = 'invalid content type';
		else {
			$content = trim(FUNC5::str_clear($content));
			if ($content == '') $err[] = 'invalid content value';
		}
		if ($charset != null) {
			if (!is_string($charset)) $err[] = 'invalid charset type';
			else if (!(strlen($charset) >= 2 && FUNC5::is_alpha($charset, true, '-'))) $err[] = 'invalid charset value';
		}
		if ($encoding != null) {
			if (!is_string($encoding)) $err[] = 'invalid encoding type';
			else {
				$encoding = strtolower($encoding);
				if (!isset(MIME5::$hencarr[$encoding])) $err[] = 'invalid encoding value';
			}
		}
		if (count($err) > 0) FUNC5::trace($debug, implode(', ', $err));
		else {
			$this->Subject = array('content' => $content, 'charset' => $charset, 'encoding' => $encoding);
			return $this->_result(array(0 => 'set subject'), true);
		}
	}

	public function text($content = null, $charset = null, $encoding = null, $debug = null) {
		if (!FUNC5::is_debug($debug)) $debug = debug_backtrace();
		$err = array();
		if (!(is_string($content) && $content != '')) $err[] = 'invalid content type';
		if ($charset != null) {
			if (!is_string($charset)) $err[] = 'invalid charset type';
			else if (!(strlen($charset) >= 2 && FUNC5::is_alpha($charset, true, '-'))) $err[] = 'invalid charset value';
		}
		if ($encoding != null) {
			if (!is_string($encoding)) $err[] = 'invalid encoding type';
			else {
				$encoding = strtolower($encoding);
				if (!isset(MIME5::$mencarr[$encoding])) $err[] = 'invalid encoding value';
			}
		}
		if (count($err) > 0) FUNC5::trace($debug, implode(', ', $err));
		else {
			$this->Text = array('content' => $content, 'charset' => $charset, 'encoding' => $encoding);
			return $this->_result(array(0 => 'set text version'), true);
		}
	}

	public function html($content = null, $charset = null, $encoding = null, $debug = null) {
		if (!FUNC5::is_debug($debug)) $debug = debug_backtrace();
		$err = array();
		if (!(is_string($content) && $content != '')) $err[] = 'invalid content type';
		if ($charset != null) {
			if (!is_string($charset)) $err[] = 'invalid charset type';
			else if (!(strlen($charset) >= 2 && FUNC5::is_alpha($charset, true, '-'))) $err[] = 'invalid charset value';
		}
		if ($encoding != null) {
			if (!is_string($encoding)) $err[] = 'invalid encoding type';
			else {
				$encoding = strtolower($encoding);
				if (!isset(MIME5::$mencarr[$encoding])) $err[] = 'invalid encoding value';
			}
		}
		if (count($err) > 0) FUNC5::trace($debug, implode(', ', $err));
		else {
			$this->Html = array('content' => $content, 'charset' => $charset, 'encoding' => $encoding);
			return $this->_result(array(0 => 'set html version'), true);
		}
	}

	public function attach($content = null, $type = null, $name = null, $charset = null, $encoding = null, $disposition = null, $id = null, $debug = null) {
		if (!FUNC5::is_debug($debug)) $debug = debug_backtrace();
		$err = array();
		if (!(is_string($content) && $content != '')) $err[] = 'invalid content type';
		if ($type != null) {
			if (!is_string($type)) $err[] = 'invalid type value';
			else {
				$type = trim(FUNC5::str_clear($type));
				if (strlen($type) < 4) $err[] = 'invalid type value';
			}
		}
		if ($name != null) {
			if (!is_string($name)) $err[] = 'invalid name type';
			else {
				$name = trim(FUNC5::str_clear($name));
				if ($name == '') $err[] = 'invalid name value';
			}
		}
		if ($charset != null) {
			if (!is_string($charset)) $err[] = 'invalid charset type';
			else if (!(strlen($charset) >= 2 && FUNC5::is_alpha($charset, true, '-'))) $err[] = 'invalid charset value';
		}
		if ($encoding == null) $encoding = 'base64';
		else if (is_string($encoding)) {
			$encoding = strtolower($encoding);
			if (!isset(MIME5::$mencarr[$encoding])) $err[] = 'invalid encoding value';
		} else $err[] = 'invalid encoding type';
		if ($disposition == null) $disposition = 'attachment';
		else if (is_string($disposition)) {
			$disposition = strtolower(FUNC5::str_clear($disposition));
			if (!($disposition == 'inline' || $disposition == 'attachment')) $err[] = 'invalid disposition value';
		} else $err[] = 'invalid disposition type';
		if ($id != null) {
			if (!is_string($id)) $err[] = 'invalid id type';
			else {
				$id = FUNC5::str_clear($id, array(' '));
				if ($id == '') $err[] = 'invalid id value';
			}
		}
		if (count($err) > 0) FUNC5::trace($debug, implode(', ', $err));
		else {
			$this->Attach[] = array('content' => $content, 'type' => $type, 'name' => $name, 'charset' => $charset, 'encoding' => $encoding, 'disposition' => $disposition, 'id' => $id);
			return $this->_result(array(0 => 'add attachment'), true);
		}
	}

	public function delattach($name = null, $debug = null) {
		if (!FUNC5::is_debug($debug)) $debug = debug_backtrace();
		if ($name == null) {
			$this->Attach = array();
			return $this->_result(array(0 => 'delete all attachments'), true);
		} else if (!(is_string($name) && strlen($name) > 1)) {
			FUNC5::trace($debug, 'invalid name value');
		} else {
			$ret = false;
			$new = array();
			if (count($this->Attach) > 0) {
				$name = strtolower($name);
				foreach ($this->Attach as $att) {
					if ($name == strtolower($att['name'])) $ret = true;
					else $new[] = $att;
				}
			}
			if ($ret) {
				$this->Attach = $new;
				return $this->_result(array(0 => 'delete attachment'), true);
			} else return FUNC5::trace($debug, 'attachment not found', 1);
		}
	}

	public function connect($host = null, $port = null, $user = null, $pass = null, $vssl = null, $tout = null, $name = null, $context = null, $auth = null, $debug = null) {
		global $_RESULT;
		$_RESULT = array();
		if (!FUNC5::is_debug($debug)) $debug = debug_backtrace();
		if ($host == null) $host = $this->Host;
		if ($port == null) $port = $this->Port;
		if ($user == null) $user = $this->User;
		if ($pass == null) $pass = $this->Pass;
		if ($vssl == null) $vssl = $this->Vssl;
		if ($tout == null) $tout = $this->Tout;
		if ($name == null) $name = $this->Name;
		if ($context == null) $context = $this->Context;
		if ($auth == null) $auth = $this->Auth;
		if ($ret = SMTP5::connect($host, $port, $user, $pass, $vssl, $tout, $name, $context, $auth, $debug)) $this->_conns[] = $ret;
		return $this->_result($_RESULT, $ret);
	}

	public function disconnect($resc = null, $debug = null) {
		global $_RESULT;
		$_RESULT = array();
		if (!FUNC5::is_debug($debug)) $debug = debug_backtrace();
		if ($resc != null) {
			if (count($this->_conns) > 0) {
				$new = array();
				foreach ($this->_conns as $cres) {
					if ($cres != $resc) $new[] = $cres;
				}
				$this->_conns = $new;
			}
			$disc = SMTP5::disconnect($resc, $debug);
			return $this->_result($_RESULT, $disc);
		} else {
			$rarr = array();
			$disc = true;
			if (count($this->_conns) > 0) {
				foreach ($this->_conns as $cres) {
					if (!SMTP5::disconnect($cres, $debug)) $disc = false;
					$rarr[] = $_RESULT;
				}
			}
			return $this->_result($rarr, $disc);
		}
	}

	public function send($resc = null, $debug = null) {
		global $_RESULT;
		$_RESULT = $err = array();
		if (!FUNC5::is_debug($debug)) $debug = debug_backtrace();
		if (is_resource($resc)) $delivery = 'relay';
		else {
			if ($resc == null) $resc = 'local';
			if (!is_string($resc)) $err[] = 'invalid connection type';
			else {
				$resc = strtolower(trim($resc));
				if ($resc == 'local' || $resc == 'client' || $resc == 'sendmail' || $resc == 'qmail') $delivery = $resc;
				else $err[] = 'invalid connection value';
			}
		}
		if (count($this->To) == 0) $err[] = 'to mail address is not set';
		if (!isset($this->Subject['content'])) $err[] = 'mail subject is not set';
		if (!(isset($this->Text['content']) || isset($this->Html['content']))) $err[] = 'mail message is not set';
		if (count($err) > 0) FUNC5::trace($debug, implode(', ', $err));
		else {
			$header['local'] = $header['client'] = array();
			$body = '';
			$from = null;
			if (isset($this->From['address']) && is_string($this->From['address'])) {
				$from = $this->From['address'];
				$hv = 'From: ';
				if (isset($this->From['name']) && trim($this->From['name']) != '') {
					$hn = MIME5::encode_header($this->From['name'], 
						isset($this->From['charset']) ? $this->From['charset'] : null, 
						isset($this->From['encoding']) ? $this->From['encoding'] : null, 
						null, null, $debug);
					if ($hn == $this->From['name']) $hn = '"'.str_replace('"', '\\"', $this->From['name']).'"';
					$hv .= $hn.' <'.$this->From['address'].'>';
				} else $hv .= $this->From['address'];
				$header['local'][] = $hv;
				$header['client'][] = $hv;
			}
			$addrs = $arr = array();
			foreach ($this->To as $to) {
				if (isset($to['address']) && FUNC5::is_mail($to['address'], false, $debug)) {
					$addrs[] = $to['address'];
					if (isset($to['name']) && trim($to['name']) != '') {
						$hn = MIME5::encode_header($to['name'], 
							isset($to['charset']) ? $to['charset'] : null, 
							isset($to['encoding']) ? $to['encoding'] : null, 
							null, null, $debug);
						if ($hn == $to['name']) $hn = '"'.str_replace('"', '\\"', $to['name']).'"';
						$arr[] = $hn.' <'.$to['address'].'>';
					} else $arr[] = $to['address'];
				}
			}
			if (count($arr) > 0) {
				$to = implode(', ', $arr);
				$header['client'][] = 'To: '.implode(', '.MIME5::LE."\t", $arr);
			} else FUNC5::trace($debug, 'to mail address is not set');
			if (count($this->Cc) > 0) {
				$arr = array();
				foreach ($this->Cc as $cc) {
					if (isset($cc['address']) && FUNC5::is_mail($cc['address'], false, $debug)) {
						$addrs[] = $cc['address'];
						if (isset($cc['name']) && trim($cc['name']) != '') {
							$hn = MIME5::encode_header($cc['name'], 
								isset($cc['charset']) ? $cc['charset'] : null, 
								isset($cc['encoding']) ? $cc['encoding'] : null, 
								null, null, $debug);
							if ($hn == $cc['name']) $hn = '"'.str_replace('"', '\\"', $cc['name']).'"';
							$arr[] = $hn.' <'.$cc['address'].'>';
						} else $arr[] = $cc['address'];
					}
				}
				if (count($arr) > 0) {
					$header['local'][] = 'Cc: '.implode(', ', $arr);
					$header['client'][] = 'Cc: '.implode(', '.MIME5::LE."\t", $arr);
				}
			}
			$hbcc = '';
			if (count($this->Bcc) > 0) {
				$arr = array();
				foreach ($this->Bcc as $bcc) {
					if (FUNC5::is_mail($bcc, false, $debug)) {
						$arr[] = $bcc;
						$addrs[] = $bcc;
					}
				}
				if (count($arr) > 0) {
					$header['local'][] = 'Bcc: '.implode(', ', $arr);
					$hbcc = MIME5::LE.'Bcc: '.implode(', ', $arr);
				}
			}
			$hn = MIME5::encode_header($this->Subject['content'], 
				isset($this->Subject['charset']) ? $this->Subject['charset'] : null, 
				isset($this->Subject['encoding']) ? $this->Subject['encoding'] : null, 
				null, null, $debug);
			$subject = $hn;
			$header['client'][] = 'Subject: '.$hn;
			if (is_int($this->Priority) || is_string($this->Priority)) {
				$arr = false;
				if ($this->Priority == 1 || $this->Priority == 'high') $arr = array(1, 'high');
				else if ($this->Priority == 3 || $this->Priority == 'normal') $arr = array(3, 'normal');
				else if ($this->Priority == 5 || $this->Priority == 'low') $arr = array(5, 'low');
				if ($arr) {
					$header['local'][] = 'X-Priority: '.$arr[0];
					$header['local'][] = 'X-MSMail-Priority: '.$arr[1];
					$header['local'][] = 'X-MimeOLE: Produced By XPertMailer v.4 MIME Class'; // << required by SpamAssassin in conjunction with "X-MSMail-Priority"
					$header['client'][] = 'X-Priority: '.$arr[0];
					$header['client'][] = 'X-MSMail-Priority: '.$arr[1];
					$header['client'][] = 'X-MimeOLE: Produced By XPertMailer v.4 MIME Class';
				}
			}
			$header['client'][] = 'Message-ID: <'.MIME5::unique().'@xpertmailer.com>';
			if (count($this->Header) > 0) {
				foreach ($this->Header as $harr) {
					if (isset($harr['name'], $harr['value']) && strlen($harr['name']) >= 2 && FUNC5::is_alpha($harr['name'], true, '-')) {
						$hn = MIME5::encode_header($harr['value'], 
							isset($harr['charset']) ? $harr['charset'] : null, 
							isset($harr['encoding']) ? $harr['encoding'] : null, 
							null, null, $debug);
						$header['local'][] = ucfirst($harr['name']).': '.$hn;
						$header['client'][] = ucfirst($harr['name']).': '.$hn;
					}
				}
			}
			$text = $html = $att = null;
			if (isset($this->Text['content'])) {
				$text = MIME5::message($this->Text['content'], 'text/plain', null, 
					isset($this->Text['charset']) ? $this->Text['charset'] : null, 
					isset($this->Text['encoding']) ? $this->Text['encoding'] : null, 
					null, null, null, null, $debug);
			}
			if (isset($this->Html['content'])) {
				$html = MIME5::message($this->Html['content'], 'text/html', null, 
					isset($this->Html['charset']) ? $this->Html['charset'] : null, 
					isset($this->Html['encoding']) ? $this->Html['encoding'] : null, 
					null, null, null, null, $debug);
			}
			if (count($this->Attach) > 0) {
				$att = array();
				foreach ($this->Attach as $attach) {
					if (isset($attach['content'])) {
						$att[] = MIME5::message($attach['content'], 
							isset($attach['type']) ? $attach['type'] : null, 
							isset($attach['name']) ? $attach['name'] : null, 
							isset($attach['charset']) ? $attach['charset'] : null, 
							isset($attach['encoding']) ? $attach['encoding'] : null, 
							isset($attach['disposition']) ? $attach['disposition'] : null, 
							isset($attach['id']) ? $attach['id'] : null, 
							null, null, $debug);
					}
				}
				if (count($att) == 0) $att = null;
			}
			$arr = MIME5::compose($text, $html, $att);
			if ($delivery == 'relay') {
				$res = SMTP5::send($resc, $addrs, implode(MIME5::LE, $header['client']).MIME5::LE.$arr['header'].MIME5::LE.MIME5::LE.$arr['content'], (($this->Path != null) ? $this->Path : $from), $debug);
				return $this->_result($_RESULT, $res);
			} else if ($delivery == 'local') {
				$rpath = (!FUNC5::is_win() && $this->Path != null) ? '-f '.$this->Path : null;
				$spath = ($this->Path != null) ? @ini_set('sendmail_from', $this->Path) : false;
				if (!FUNC5::is_win()) $arr['content'] = str_replace("\r\n", "\n", $arr['content']);
				$res = mail($to, $subject, $arr['content'], implode(MIME5::LE, $header['local']).MIME5::LE.$arr['header'], $rpath);
				if ($spath) @ini_restore('sendmail_from');
				return $this->_result(array(0 => 'send mail local'), $res);
			} else if ($delivery == 'client') {
				$group = array();
				foreach ($addrs as $addr) {
					$exp = explode('@', $addr);
					$group[strtolower($exp[1])][] = $addr;
				}
				$ret = true;
				$reg = (count($group) == 1);
				foreach ($group as $domain => $arrs) {
					$con = SMTP5::mxconnect($domain, $this->Port, $this->Tout, $this->Name, $this->Context, $debug);
					if ($reg) $this->_result(array($domain => $_RESULT));
					if ($con) {
						if (!SMTP5::send($con, $arrs, implode(MIME5::LE, $header['client']).MIME5::LE.$arr['header'].MIME5::LE.MIME5::LE.$arr['content'], (($this->Path != null) ? $this->Path : $from), $debug)) $ret = false;
						if ($reg) $this->_result(array($domain => $_RESULT));
						SMTP5::disconnect($con, $debug);
					} else $ret = false;
				}
				if (!$reg) $this->_result(array(0 => 'send mail client'));
				return $ret;
			} else if ($delivery == 'sendmail' || $delivery == 'qmail') {
				$ret = false;
				$comm = (($delivery == 'sendmail') ? $this->SendMail : $this->QMail).' -oi'.(($this->Path != null) ? ' -f '.$this->Path : '').' -t';
				if ($con = popen($comm, 'w')) {
					if (fputs($con, implode(MIME5::LE, $header['client']).$hbcc.MIME5::LE.$arr['header'].MIME5::LE.MIME5::LE.$arr['content'])) {
						$res = pclose($con) >> 8 & 0xFF;
						if ($res == 0) {
							$ret = true;
							$this->_result(array(0 => 'send mail using "'.ucfirst($delivery).'" program'));
						} else $this->_result(array(0 => $res));
					} else $this->_result(array(0 => 'can not write'));
				} else $this->_result(array(0 => 'can not write line command'));
				return $ret;
			}
		}
	}

}

class POP3 extends POP35 { }
class POP35 {

	const CRLF = "\r\n";
	const PORT = 110;
	const TOUT = 30;
	const COUT = 5;
	const BLEN = 1024;

	static private function _ok($conn, &$resp, $debug = null) {
		if (!is_resource($conn)) return FUNC5::trace($debug, 'invalid resource connection', 1);
		else {
			$ret = true;
			do {
				if ($result = fgets($conn, self::BLEN)) {
					$resp[] = $result;
					if (substr($result, 0, 3) != '+OK') {
						$ret = false;
						break;
					}
				} else {
					$resp[] = 'can not read';
					$ret = false;
					break;
				}
			} while ($result[3] == '-');
			return $ret;
		}
	}

	static public function connect($host = null, $user = null, $pass = null, $port = null, $vssl = null, $tout = null, $context = null, $debug = null) {
		if (!FUNC5::is_debug($debug)) $debug = debug_backtrace();
		global $_RESULT;
		$_RESULT = array();
		if ($port == null) $port = self::PORT;
		if ($tout == null) $tout = self::TOUT;
		$err = array();
		if (!is_string($host)) $err[] = 'invalid host type';
		else {
			if (!(trim($host) != '' && (FUNC5::is_ipv4($host) || FUNC5::is_hostname($host, true, $debug)))) $err[] = 'invalid host value';
		}
		if (!is_string($user)) $err[] = 'invalid username type';
		else if (($user = FUNC5::str_clear($user)) == '') $err[] = 'invalid username value';
		if (!is_string($pass)) $err[] = 'invalid password type';
		else if (($pass = FUNC5::str_clear($pass)) == '') $err[] = 'invalid password value';
		if (!(is_int($port) && $port > 0)) $err[] = 'invalid port value';
		if ($vssl != null) {
			if (!is_string($vssl)) $err[] = 'invalid ssl version type';
			else {
				$vssl = strtolower($vssl);
				if (!($vssl == 'tls' || $vssl == 'ssl' || $vssl == 'sslv2' || $vssl == 'sslv3')) $err[] = 'invalid ssl version value';
			}
		}
		if (!(is_int($tout) && $tout > 0)) $err[] = 'invalid timeout value';
		if ($context != null && !is_resource($context)) $err[] = 'invalid context type';
		if (count($err) > 0) FUNC5::trace($debug, implode(', ', $err));
		else {
			$ret = false;
			$prt = ($vssl == null) ? 'tcp' : $vssl;
			$conn = ($context == null) ? stream_socket_client($prt.'://'.$host.':'.$port, $errno, $errstr, $tout) : stream_socket_client($prt.'://'.$host.':'.$port, $errno, $errstr, $tout, STREAM_CLIENT_CONNECT, $context);
			if (!$conn) $_RESULT[401] = $errstr;
			else if (!stream_set_timeout($conn, self::COUT)) $_RESULT[402] = 'could not set stream timeout';
			else if (!self::_ok($conn, $resp, $debug)) $_RESULT[403] = $resp;
			else $ret = self::auth($conn, $user, $pass, $debug);
			if (!$ret) {
				if (is_resource($conn)) @fclose($conn);
				$conn = false;
			}
			return $conn;
		}
	}

	static public function auth($conn = null, $user = null, $pass = null, $debug = null) {
		if (!FUNC5::is_debug($debug)) $debug = debug_backtrace();
		global $_RESULT;
		$_RESULT = array();
		$err = array();
		if (!is_resource($conn)) $err[] = 'invalid resource connection';
		if (!is_string($user)) $err[] = 'invalid username type';
		else if (($user = FUNC5::str_clear($user)) == '') $err[] = 'invalid username value';
		if (!is_string($pass)) $err[] = 'invalid password type';
		else if (($pass = FUNC5::str_clear($pass)) == '') $err[] = 'invalid password value';
		if (count($err) > 0) FUNC5::trace($debug, implode(', ', $err));
		else {
			$ret = false;
			if (!fwrite($conn, 'USER '.$user.self::CRLF)) $_RESULT[404] = 'can not write';
			else if (!self::_ok($conn, $resp, $debug)) $_RESULT[405] = $resp;
			else if (!fwrite($conn, 'PASS '.$pass.self::CRLF)) $_RESULT[405] = 'can not write';
			else if (!self::_ok($conn, $resp, $debug)) $_RESULT[406] = $resp;
			else {
				$_RESULT[407] = $resp;
				$ret = true;
			}
			return $ret;
		}
	}

	static public function disconnect($conn = null, $debug = null) {
		if (!FUNC5::is_debug($debug)) $debug = debug_backtrace();
		global $_RESULT;
		$_RESULT = array();
		if (!is_resource($conn)) FUNC5::trace($debug, 'invalid resource connection', 1);
		else {
			if (!fwrite($conn, 'QUIT'.self::CRLF)) $_RESULT[437] = 'can not write';
			else if (!self::_ok($conn,  $resp, $debug)) $_RESULT[438] = $resp;
			else $_RESULT[439] = $resp;
			return @fclose($conn);
		}
	}

	static public function pnoop($conn = null, $debug = null) {
		if (!FUNC5::is_debug($debug)) $debug = debug_backtrace();
		global $_RESULT;
		$_RESULT = array();
		if (!is_resource($conn)) FUNC5::trace($debug, 'invalid resource connection');
		else {
			$ret = false;
			if (!fwrite($conn, 'NOOP'.self::CRLF)) $_RESULT[408] = 'can not write';
			else if (!self::_ok($conn,  $resp, $debug)) $_RESULT[409] = $resp;
			else {
				$_RESULT[410] = $resp;
				$ret = true;
			}
			return $ret;
		}
	}

	static public function prset($conn = null, $debug = null) {
		if (!FUNC5::is_debug($debug)) $debug = debug_backtrace();
		global $_RESULT;
		$_RESULT = array();
		if (!is_resource($conn)) FUNC5::trace($debug, 'invalid resource connection');
		else {
			$ret = false;
			if (!fwrite($conn, 'RSET'.self::CRLF)) $_RESULT[411] = 'can not write';
			else if (!self::_ok($conn,  $resp, $debug)) $_RESULT[412] = $resp;
			else {
				$_RESULT[413] = $resp;
				$ret = true;
			}
			return $ret;
		}
	}

	static public function pquit($conn = null, $debug = null) {
		if (!FUNC5::is_debug($debug)) $debug = debug_backtrace();
		global $_RESULT;
		$_RESULT = array();
		if (!is_resource($conn)) FUNC5::trace($debug, 'invalid resource connection');
		else {
			$ret = false;
			if (!fwrite($conn, 'QUIT'.self::CRLF)) $_RESULT[414] = 'can not write';
			else if (!self::_ok($conn,  $resp, $debug)) $_RESULT[415] = $resp;
			else {
				$_RESULT[416] = $resp;
				$ret = true;
			}
			return $ret;
		}
	}

	static public function pstat($conn = null, $debug = null) {
		if (!FUNC5::is_debug($debug)) $debug = debug_backtrace();
		global $_RESULT;
		$_RESULT = array();
		if (!is_resource($conn)) FUNC5::trace($debug, 'invalid resource connection');
		else {
			$ret = false;
			if (!fwrite($conn, 'STAT'.self::CRLF)) $_RESULT[417] = 'can not write';
			else if (!self::_ok($conn,  $resp, $debug)) $_RESULT[418] = $resp;
			else {
				if (count($exp = explode(' ', substr($resp[0], 4, -strlen(self::CRLF)))) == 2) {
					$val1 = intval($exp[0]);
					$val2 = intval($exp[1]);
					if (strval($val1) === $exp[0] && strval($val2) === $exp[1]) {
						$ret = array($val1 => $val2);
						$_RESULT[421] = $resp;
					} else $_RESULT[420] = $resp;
				} else $_RESULT[419] = $resp;
			}
			return $ret;
		}
	}

	static public function pdele($conn = null, $msg = null, $debug = null) {
		if (!FUNC5::is_debug($debug)) $debug = debug_backtrace();
		global $_RESULT;
		$_RESULT = array();
		$err = array();
		if (!is_resource($conn)) $err[] = 'invalid resource connection';
		if (!(is_int($msg) && $msg > 0)) $err[] = 'invalid message number';
		if (count($err) > 0) FUNC5::trace($debug, implode(', ', $err));
		else {
			$ret = false;
			if (!fwrite($conn, 'DELE '.$msg.self::CRLF)) $_RESULT[422] = 'can not write';
			else if (!self::_ok($conn,  $resp, $debug)) $_RESULT[423] = $resp;
			else {
				$_RESULT[424] = $resp;
				$ret = true;
			}
			return $ret;
		}
	}

	static public function pretr($conn = null, $msg = null, $debug = null) {
		if (!FUNC5::is_debug($debug)) $debug = debug_backtrace();
		global $_RESULT;
		$_RESULT = array();
		$err = array();
		if (!is_resource($conn)) $err[] = 'invalid resource connection';
		if (!(is_int($msg) && $msg > 0)) $err[] = 'invalid message number';
		if (count($err) > 0) FUNC5::trace($debug, implode(', ', $err));
		else {
			$ret = false;
			if (!fwrite($conn, 'RETR '.$msg.self::CRLF)) $_RESULT[425] = 'can not write';
			else if (!self::_ok($conn,  $resp, $debug)) $_RESULT[426] = $resp;
			else {
				$ret = '';
				do {
					if ($res = fgets($conn, self::BLEN)) $ret .= $res;
					else {
						$_RESULT[427] = 'can not read';
						$ret = false;
						break;
					}
				} while ($res != '.'.self::CRLF);
				if ($ret) {
					$ret = substr($ret, 0, -strlen(self::CRLF.'.'.self::CRLF));
					$_RESULT[428] = $resp;
				}
			}
			return $ret;
		}
	}

	static public function plist($conn = null, $msg = null, $debug = null) {
		if (!FUNC5::is_debug($debug)) $debug = debug_backtrace();
		global $_RESULT;
		$_RESULT = array();
		$err = array();
		if (!is_resource($conn)) $err[] = 'invalid resource connection';
		if ($msg == null) $msg = 0;
		if (!(is_int($msg) && $msg >= 0)) $err[] = 'invalid message number';
		if (count($err) > 0) FUNC5::trace($debug, implode(', ', $err));
		else {
			$ret = false;
			$num = ($msg > 0) ? true : false;
			if (!fwrite($conn, 'LIST'.($num ? ' '.$msg : '').self::CRLF)) $_RESULT[429] = 'can not write';
			else if (!self::_ok($conn, $resp, $debug)) $_RESULT[430] = $resp;
			else {
				if ($num) {
					if (count($exp = explode(' ', substr($resp[0], 4, -strlen(self::CRLF)))) == 2) {
						$val1 = intval($exp[0]);
						$val2 = intval($exp[1]);
						if (strval($val1) === $exp[0] && strval($val2) === $exp[1]) {
							$ret = array($val1 => $val2);
							$_RESULT[433] = $resp;
						} else $_RESULT[432] = $resp;
					} else $_RESULT[431] = $resp;
				} else {
					do {
						if ($res = fgets($conn, self::BLEN)) {
							if (count($exp = explode(' ', substr($res, 0, -strlen(self::CRLF)))) == 2) {
								$val1 = intval($exp[0]);
								$val2 = intval($exp[1]);
								if (strval($val1) === $exp[0] && strval($val2) === $exp[1]) {
									$ret[$val1] = $val2;
									$_RESULT[436] = $resp;
								}
							} else if ($res[0] != '.') {
								$_RESULT[435] = $res;
								$ret = false;
								break;
							}
						} else {
							$_RESULT[434] = 'can not read';
							$ret = false;
							break;
						}
					} while ($res[0] != '.');
				}
			}
			return $ret;
		}
	}

	static public function puidl($conn = null, $msg = null, $debug = null) {
		if (!FUNC5::is_debug($debug)) $debug = debug_backtrace();
		global $_RESULT;
		$_RESULT = array();
		$err = array();
		if (!is_resource($conn)) $err[] = 'invalid resource connection';
		if ($msg == null) $msg = 0;
		if (!(is_int($msg) && $msg >= 0)) $err[] = 'invalid message number';
		if (count($err) > 0) FUNC5::trace($debug, implode(', ', $err));
		else {
			$ret = false;
			$num = ($msg > 0) ? true : false;
			if (!fwrite($conn, 'UIDL'.($num ? ' '.$msg : '').self::CRLF)) $_RESULT[440] = 'can not write';
			else if (!self::_ok($conn, $resp, $debug)) $_RESULT[441] = $resp;
			else {
				if ($num) {
					if (count($exp = explode(' ', substr($resp[0], 4, -strlen(self::CRLF)))) == 2) {
						$val1 = intval($exp[0]);
						$val2 = trim($exp[1]);
						if (strval($val1) === $exp[0] && $val2 != '') {
							$ret = array($val1 => $val2);
							$_RESULT[444] = $resp;
						} else $_RESULT[443] = $resp;
					} else $_RESULT[442] = $resp;
				} else {
					do {
						if ($res = fgets($conn, self::BLEN)) {
							if (count($exp = explode(' ', substr($res, 0, -strlen(self::CRLF)))) == 2) {
								$val1 = intval($exp[0]);
								$val2 = trim($exp[1]);
								if (strval($val1) === $exp[0] && $val2 != '') {
									$ret[$val1] = $val2;
									$_RESULT[446] = $resp;
								}
							} else if ($res[0] != '.') {
								$_RESULT[445] = $res;
								$ret = false;
								break;
							}
						} else {
							$_RESULT[434] = 'can not read';
							$ret = false;
							break;
						}
					} while ($res[0] != '.');
				}
			}
			return $ret;
		}
	}

}
?>