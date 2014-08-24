<?php
/**
* Cliqon generic class
*
*/
class clq {
	  
	 public$thisclass="clq";
	 public $lcd, $browser, $os, $ip, $rootpath, $icnpath, $sitepath, $dhtmlxpath; 
	 public$qrepl=array();
	 public$qwith=array();
	 public$cfg=array();
	 public$properties=array();
	 public $idmextn = ".png";
	 private$list=array();
	 private$enclose=array('begin'=>'','end'=>'');
	 private$delimit='';
	 public $clqschema = array();


	function __construct(){

		global $rootpath; $this->rootpath = $rootpath; self::__set('rootpath',$this->rootpath); 
		global $sitepath; if(!$sitepath) {$this->sitepath = $_SESSION['CLQ_Sitepath'];} else {$this->sitepath = $sitepath;}; 
    self::__set('sitepath',$this->sitepath);
		global $lcd; if(!$lcd) {$this->lcd = $_SESSION['CLQ_Langcd'];} else {$this->lcd = $lcd;}; self::__set('lcd',$this->lcd);
		
		global $cfg; if(!$cfg) {
			$this->cfg = $_SESSION['CLQ_Config'];
		} else {
			$this->cfg = $cfg;
		}; 
		self::__set('cfg', $this->cfg);
		
		global $schema; $this->clqschema = $schema; self::__set('clqschema',$this->clqschema);
		$this->icnpath=$this->sitepath."admin/icons/";
		$this->qrepl=array("{lcd}");
		$this->qwith=array($this->lcd);

		// Make table if not exists
		foreach($this->clqschema as $tablename => $tablearray) {
		  if(R::inspect($tablename) == false ) {
			$create = R::dispense($tablename);
			foreach($this->clqschema[$tablename]['flds'] as $fldname => $fldarray) {
			  if($fldname != 'id') 
				$create->$fldname = 'z';
			}
			$created = R::store($create);
		  }
		}
		
		// Load cfg from database
		$sql = "SELECT clq_extra FROM clqstring WHERE clq_type = ? AND clq_reference = ? and clq_langcd = ?";
		$cell = R::getCell($sql, array('config', 'site.cfg', $this->lcd));
		if($cell != "") {
			$newcfg = json_decode($cell, true);
		}
		
		foreach($newcfg as $key => $value) {
			$this->cfg[$key] = $value; // This has the effect of globally overwriting any configured value with a value from the database
			$_SESSION['CLQ_Config'][$key] = $value;
		}

	}

	function __set($key,$val){$this->properties[$key]=$val;}
	function setVar($key,$val){$this->properties[$key]=$val;}
	function __get($key){if(array_key_exists($key,$this->properties))return$this->properties[$key];else return null;}
	function getVar($key){if(array_key_exists($key,$this->properties))return$this->properties[$key];else return null;}
	function getProps() {return $this->properties;}

	function cStr($str, $type = "string"){
    try{
      if($str==":" || $str==""){
        return "";
      } else { 
        $parts = explode(":", $str);
        if($parts[0]=='999') {
          return $parts[1];
        } else {
          if(is_numeric($parts[0])) {
            $ref = "str(".$parts[0].")";
          } else {
            $ref = $parts[0];
          };

          $txt = self::cacheRead($ref.'_'.$this->lcd.'.tmp'); //17 is id of the article, for example
          // it doesn't exist, fetch data from database
          if (empty($txt)) {
              $vals = array($_SESSION['CLQ_Langcd'], "%".$ref."%", $type);
              $row = R::findOne("clqstring",' clq_langcd = ? AND clq_reference LIKE ? AND clq_type = ?', $vals);
              if(!$row){
                if($parts[1] != "") {
                  $txt="e:".$parts[1];
                } else {
                  $txt = "e:".$parts[0];
              }}  else {
                $txt = $row->clq_text;
              }; 
              self::cacheWrite($ref.'_'.$this->lcd.'.tmp', $txt);  
              return $txt;  
          } else {
              return $txt;
          }
        }}
    }
    catch(Exception $e){
      return$e->getMessage();
    }
  }

  function cSecn($ref){
    
    try {
      
      if($ref == "") {
        return "e:";
      } else { 
        
        $ref = trim($ref);
        $txt = self::cacheRead($ref.'_'.$this->lcd.'.tmp'); //17 is id of the article, for example
        // it doesn't exist, fetch data from database
        if (empty($txt)) {
            $vals = array($_SESSION['CLQ_Langcd'], $ref, "section");
            $row = R::findOne("clqstring",' clq_langcd = ? AND clq_reference = ? AND clq_type = ?', $vals);
            if(!$row){
              $txt="e:".$ref;
            } else {
              $txt = $row->clq_text;
            }; 
            self::cacheWrite($ref.'_'.$this->lcd.'.tmp', $txt);  
            return $txt;  
        } else {
            return $txt;
        }
      }
    }
    catch(Exception $e){
      return$e->getMessage();
    }
  }

  /**
  * Look up value in database in specific language, 
  * if it does not exist, get value from Config file,
  * if value does not exist there, return error
  */
  function cCfg($ref) {

    try {
      
        $ref = trim($ref);
        $txt = self::cacheRead($ref.'_'.$this->lcd.'.tmp'); //17 is id of the article, for example
        // it doesn't exist, fetch data from database
        if (empty($txt)) {
            $vals = array($_SESSION['CLQ_Langcd'], $ref, "config");
            $row = R::findOne("clqstring",' clq_langcd = ? AND clq_reference = ? AND clq_type = ?', $vals);
            if(!$row) {
				$txt = $this->cfg[$ref];
            } else {
				$txt = $row->clq_value;				
            }; 
            self::cacheWrite($ref.'_'.$this->lcd.'.tmp', $txt);  
            return $txt;  
        } else {
            return $txt;
        }     
    }
    catch(Exception $e){
      return$e->getMessage();
    }
  }

  /**
  * lookup list options
  */
  function cList($list, $json = true) { 

    try {
      
        $list = trim($list); $opts = null;
        
        $opts = self::cacheRead($list.'_'.$this->lcd.'.tmp'); //17 is id of the article, for example
        // it doesn't exist, fetch data from database
        if (empty($opts)) {
            $vals = array($_SESSION['CLQ_Langcd'], $list, "list");
            $row = R::findOne("clqstring",' clq_langcd = ? AND clq_reference = ? AND clq_type = ?', $vals);
            if(!$row) {
              $opts = array('norecs' => 'No Records');
            } else {
              $opts = $row->clq_extra;
            }; 
            self::cacheWrite($list.'_'.$this->lcd.'.tmp', $opts);   
        };

        if($json == false) {
          return json_decode($opts, true);
        } else {
          return $opts;
        }

    }
    catch(Exception $e){
      return $e->getMessage();
    }
  }

  function cOption($list, $ref) {
      $opts = self::cList($list, false);
      $opt = $opts[$ref];
      if($opt != "") {
        return $opt;
      } else {
        return "e:".$ref;
      }
  }
  
	/**
	 * Send debug code to the Javascript console
	 */ 
	function toConsole($data) {
		if(is_array($data) || is_object($data)) {
			echo("<script>console.log('PHP: ".json_encode($data)."');</script>");
		} else {
			echo("<script>console.log('PHP: ".$data."');</script>");
		}
	}


  function fDate($cell=""){
    date_default_timezone_set($this->cfg['site.timezone']);
    $date = new DateTime($cell);
    $ddate = $date->format($this->cfg['site.dateformat']);
    return $ddate;
  }

  function fDatePlus($cell = "", $diff = "") {
    date_default_timezone_set($this->cfg['site.timezone']);
    $date = new DateTime($cell);
    $date->add(new DateInterval('P'.$diff));
    $ddate = $date->format($this->cfg['site.dateformat']);
    return $ddate;
  }

  function fDateMinus($cell = "", $diff = "") {
    date_default_timezone_set($this->cfg['site.timezone']);
    $date = new DateTime($cell);
    $date->sub(new DateInterval('P'.$diff));
    $ddate = $date->format($this->cfg['site.dateformat']);
    return $ddate;
  }

  function dbDate($cell=""){
    $date = new DateTime($cell);
    $dbate = $date->format('Y-m-d');
    return $dbate;
  }

  function formatNumber($val, $dp = null, $ds = null, $ts = null) {
      // 'site.numberformat' => '2|,|.|| €', // // Used by numbers - dec places, dec sep, thousands sep '2|,|.'
      $fn = explode('|', $this->cfg['site.numberformat']);
      if(!isset($dp)) {$dp = $fn[0];};
      if(!isset($ds)) {$ds = $fn[1];};
      if(!isset($ts)) {$ts = $fn[2];};
      $val = number_format(+$val, $dp, $ds, $ts);
      return $val;
  }
  function formatCurrency($val, $dp = null, $ds = null, $ts = null, $ps = null, $as = null) {
      $fn = explode('|', $this->cfg['site.numberformat']);
      if(!isset($dp)) {$dp = $fn[0];};
      if(!isset($ds)) {$ds = $fn[1];};
      if(!isset($ts)) {$ts = $fn[2];};
      if(!isset($ps)) {$ps = $fn[3];};
      if(!isset($as)) {$as = $fn[4];};
      $val = $ps.number_format(+$val, $dp, $ds, $ts).$as;
      return $val;
  }
  function urlNumber($val) {
    $val = str_replace(".", "", $val);
    $number = str_replace(",", ".", $val);
    return number_format((float)$number, 2, '.', '');
  }
    function rnd(){for($i=0;$i<6;$i++){$d=rand(1,30)%2;$rchr.=$d?chr(rand(65,90)):chr(rand(48,57));}return$rchr;}

    /**
    * @desc Function read retrieves value from cache
    * @param $fileName - name of the cache file
    * Usage: Cache::read('fileName.extension')
    */
    function cacheRead($fileName) {
        $fileName = $this->rootpath.'cache/'.$fileName;
        if (file_exists($fileName)) {
            $handle = fopen($fileName, 'rb');
            $variable = fread($handle, filesize($fileName));
            fclose($handle);
            return unserialize($variable);
        } else {
            return null;
        }
    }
     
    /**
    * @desc Function for writing key => value to cache
    * @param $fileName - name of the cache file (key)
    * @param $variable - value
    * Usage: Cache::write('fileName.extension', value)
    */
    function cacheWrite($fileName, $variable) {
        $fileName = $this->rootpath.'cache/'.$fileName;
        $handle = fopen($fileName, 'a');
        fwrite($handle, serialize($variable));
        fclose($handle);
    }
     
    /**
    * @desc Function for deleteing cache file
    * @param $fileName - name of the cache file (key)
    * Usage: Cache::delete('fileName.extension')
    */
    function cacheDelete($fileName) {
        $fileName = $_SESSION['CLQ_Sitepath'].'cache/'.$fileName;
        @unlink($fileName);
    }

    /**
    * Uses PHPNodeJs to run a function under NodeJS from Server
    * result is return to function
    * runs Asynchronous code
    */
    function runJS($jsfunc, $funcname, $args = array(), $usejquery = false) {
      $phpjs = new phpnodejs(true);
      return $phpjs->run($jsfunc, $funcname, $args, $usejquery);
    }   

	function writeLog($cat,$msg) {
		if($this->cfg['site.test'] == true) {
			$fn=$this->rootpath."log/cliqon.log";
			$file=fopen($fn,"a+");$size=filesize($fn);$space.="\n";
			fwrite($file,date()." [".$cat."] ".$msg);
			$text=fread($file,$size);
			fwrite($file,$space);
			fclose($file);
		}
	}

	function copyFile($url, $filename){
		
		$file = fopen($url,"rb");
		if(!$file) {
			return false;
		} else {
			$fc=fopen($filename,"wb");
			while(!feof($file)){
				$line=fread($file,1028);
				fwrite($fc,$line);
			}
			fclose($fc);
			return true;
		}
	}

	function openFile($fn, $dir, $op = "r") {
		$mf = $this->sitepath.$dir.$fn;
		$handle = fopen($mf, $op) or die('Cannot open file:  '.$mf); //implicitly creates file
		return $handle;
	}

	function createFile($fn, $dir) {
		$handle = self::openFile($fn, $dir, 'w');
		return $handle;
	}

	function readFile($fn, $dir) {
		$mf = $this->sitepath.$dir.$fn;
		$handle = self::openFile($fn, $dir, 'r');
		$data = fread($handle, filesize($mf));
		fclose($handle);
		return $data;
	}

	function writeFile($fn, $dir, $data) {
		$mf = $this->sitepath.$dir.$fn;
		$handle = self::openFile($fn, $dir, 'w');
		fwrite($handle, $data);
		$newdata = self::readFile($fn, $dir);
		if($newdata == $data){return "OK";} else {return;};
	}

	function appendFile($fn, $dir, $data) {
		$mf = $this->sitepath.$dir.$fn;
		$olddata = self::readFile($fn, $dir);
		$newdata = $olddata.$data;
		return self::writeFile($fn, $dir, $newdata);
	}

	function closeFile($handle) {
		fclose($handle);
	}

	function deleteFile($fn, $dir) {
		$mf = $this->sitepath.$dir.$fn;
		unlink($mf);
		if(!file_exists($mf)){return "OK";} else {return;};
	}

  function recurse_copy(
  	$src,$dst){$dir=opendir($src);@mkdir($dst);while(false!==($file=readdir($dir))){if(($file!='.')&&($file!='..')){if(is_dir($src.'/'.$file)){recurse_copy($src.'/'.$file,$dst.'/'.$file);}else{copy($src.'/'.$file,$dst.'/'.$file);}}}closedir($dir);}

  function globr($sDir, $sPattern, $nFlags=NULL) {
  	$sDir=escapeshellcmd($sDir);$aFiles=glob("$sDir/$sPattern",$nFlags);foreach(@glob("$sDir/*",GLOB_ONLYDIR)as$sSubDir){$aSubFiles=$this->globr($sSubDir,$sPattern,$nFlags);$aFiles=array_merge($aFiles,$aSubFiles);}return$aFiles;}
  function parentDir(){
  	$parentDir=join(array_slice(split("/",dirname($_SERVER['PHP_SELF'])),0,-1),"/").'/';return$parentDir;}
  function listTree($type,$path){
  	if($type=="list"){$f=self::ListFolder($this->rootpath.$path."/".$file,"list");return$f;}else{$l=self::ListFolder($this->rootpath.$path."/".$file,"tree");return$l;}}
  function ListFolder($path,$type="list"){
  	$dir_handle=@opendir($path)or die("Unable to open $path");$dirname=end(explode("/",$path));$l.=("<li>$dirname\n");$l.="<ul>\n";while(false!==($file=readdir($dir_handle))){if($file!="."&&$file!=".."){if(is_dir($path."/".$file)){if($type=="list"){$f.=self::ListFolder($path."/".$file,"list");}else{$l.=self::ListFolder($path."/".$file,"tree");}}else{$rawf=$path."/".$file;$qrepl=array('../../','//');$qwith=array('','/');$f.=str_replace($qrepl,$qwith,$rawf).'|';$l.="<li>$file</li>";}}}$l.="</ul>\n";$l.="</li>\n";closedir($dir_handle);if($type=="list"){return$f;}else{return$l;}}
  function toLcandNsp($directory){
  	$f=self::listTree('list',$directory);$f=str_replace('//','/',$f);$files=explode('|',$f);foreach($files as$key=>$name){$oldName=$name;$newName=strtolower($name);$newName=str_replace(' ','',$newName);echo $newName.',';rename($this->rootpath.$oldName,$this->rootpath.$newName);}return"Done";}

    /* Utility Functions */
    public static function toString($array){
    $result = array();
    $depth = 0;
    foreach($array as $k => $v) {
    $show_val = ( is_array($v) ? "" : $v );

    // show the indents
    $result []= str_repeat("  ", $depth);
    if($depth == 0) {
    // this is a root node. no parents
    $result []= "O ";
    } elseif(is_array($v)) {
    // this is a normal node. parents and children
    $result []= "+ ";
    } else {
    // this is a leaf node. no children
    $result []= "- ";
    }

    // show the actual node
    if ($show_val == "") {
    $result []= "<strong>{$k}</strong>:";
    } else {
    $result []= $k . " (".$show_val.")"."";
    }

    if(is_array($v)) {
    // this is what makes it recursive, rerun for childs
    $temp = self::toTree($v, ($depth+1));
    foreach($temp as $t) {
    $result []= $t;
    }
    }
    }
    return implode($result);
    }

    private static function showtype($show_val) {
    // convert bools to text and quote 'text bools'!
    if (is_string($show_val) &&
    ($show_val == "true" || $show_val == "false")) {
    return "\"{$show_val}\"";
    } elseif (is_bool($show_val) && $show_val === true) {
    return "true";
    } elseif (is_bool($show_val) && $show_val === false) {
    return "false";
    } elseif (is_null($show_val)) {
    return "null";
    } else {
    return $show_val;
    }
    }

    private static function toTree($pieces, $depth = 0) {
    foreach($pieces as $k => $v) {
    // skip the baseval thingy. Not a real node.
    //if($k == "__base_val") continue;
    // determine the real value of this node.
    $show_val = ( is_array($v) ? "" : $v );

    $show_val = self::showtype($show_val);

    // show the indents
    $result []= str_repeat("  ", $depth);
    if($depth == 0) {
    // this is a root node. no parents
    $result []= "O ";
    } elseif(is_array($v)) {
    // this is a normal node. parents and children
    $result []= "+ ";
    } else {
    // this is a leaf node. no children
    $result []= "- ";
    }

    // show the actual node
    if ($show_val == "") {
    $result []= "<strong>{$k}</strong>:";
    } else {
    $result []= $k . ": <i>{$show_val}</i>";
    }

    if(is_array($v)) {
    // this is what makes it recursive, rerun for childs
    $temp = self::toTree($v, ($depth+1));
    if (is_array($temp)) {
    foreach($temp as $t) {
    $result []= $t;
    }
    } else {
    $result []= $t;
    }
    }
    }
    return $result;
    }

    function csv_to_array($filename='', $delimiter=',') {
    if(!file_exists($filename) || !is_readable($filename))
    return FALSE;

    $header = NULL;
    $data = array();
    if (($handle = fopen($filename, 'r')) !== FALSE)
    {
    while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE)
    {
    if(!$header)
    $header = $row;
    else
    $data[] = array_combine($header, $row);
    }
    fclose($handle);
    }
    return $data;
    }

    function array_to_csv($array, $header_row = true, $col_sep = ",", $row_sep = "\n", $qut = '"') {
    if (!is_array($array) or !is_array($array[0])) return false;

    //Header row.
    if ($header_row) {
    foreach ($array[0] as $key => $val) {
    //Escaping quotes.
    $key = str_replace($qut, "$qut$qut", $key);
    $output .= "$col_sep$qut$key$qut";
    }
    $output = substr($output, 1)."\n";
    }
    //Data rows.
    foreach ($array as $key => $val) {
    $tmp = '';
    foreach ($val as $cell_key => $cell_val) {
    //Escaping quotes.
    if(is_numeric($cell_val)) {
    $tmp .= "$col_sep$cell_val";
    } else {
    $cell_val = str_replace($qut, "$qut$qut", $cell_val);
    $tmp .= "$col_sep$qut$cell_val$qut";
    }            
    $tmp .= "$col_sep$qut$cell_val$qut";
    }
    $output .= substr($tmp, 1).$row_sep;
    } 
    return $output;
    }


    function getVal($tbl, $fld, $wherearray = NULL, $opts = "") {
      
      try { 
        // R::getCell('SELECT clq_css FROM clqtemplate WHERE clq_value = :val AND clq_type = :type', [':val' => $page, ':type' => 'site']);
        
        $sql = "SELECT ".$fld." FROM ".$tbl;
        $wh = "";
        $params = array();
        if(is_array($wherearray)) {
          $wh = "WHERE ";
          foreach ($wherearray as $key => $val){
            $wh .= $key." = :".$key." AND ";
            $params[':'.$key] = $val;
          }
          $wh = trim($wh, " AND ");
          $sql .= $wh;          
        }

        $result = R::getCell($sql, $params.$opts);
        if(!$result) {
          return"e:";
        } else {
          return $result;
        }
      } catch (Exception $e){
        throw new Exception('System Error: ', 0, $e);
      }
    }

    function getRow($tbl, $fldsarray = NULL, $wherearray = NULL, $json = false) {
            
      try { 

        // Fields
        if(is_array($fldsarray)) {
          $flds = "";
          foreach($fldsarray as $q => $fld) {
            $flds .= $fld.", ";
          }
          $flds = trim($flds, ", ");          
        } else {
          $flds = "*";
        }

        // Start with Table
        $sql = "SELECT ".$flds." FROM ".$tbl;

        // Where
        $wh = "";
        $params = array();
        if(is_array($wherearray)) {
          $wh = "WHERE ";
          foreach ($wherearray as $key => $val){
            $wh .= $key." = :".$key." AND ";
            $params[':'.$key] = $val;
          }
          $wh = trim($wh, " AND ");
          $sql .= $wh;          
        }

        // Params - not needed

        // Result
        $result = R::getRow($sql, $params);
        if(!$result){
          
          return "e:";

        } else {
          
          if($json === true) {
            return json_encode($result);
          } else {
            return $result;
          }
          
        }
      } catch (Exception $e){
        throw new Exception('System Error: ', 0, $e);
      }
    }

    function getSet($tbl, $fldsarray = NULL, $wherearray = NULL, $json = false, $opts = NULL) {
            
      try { 

        // Fields
        if(is_array($fldsarray)) {
          $flds = "";
          foreach($fldsarray as $q => $fld) {
            $flds .= $fld.", ";
          }
          $flds = trim($flds, ", ");          
        } else {
          $flds = "*";
        }

        // Start with Table
        $sql = "SELECT ".$flds." FROM ".$tbl;

        // Where
        $wh = "";
        $params = array();
        if(is_array($wherearray)) {
          $wh = "WHERE ";
          foreach ($wherearray as $key => $val){
            $wh .= $key." = :".$key." AND ";
            $params[':'.$key] = $val;
          }
          $wh = trim($wh, " AND ");
          $sql .= $wh;          
        }

        // Params
        if(is_array($opts)) {
          
          if(array_key_exists('limit', $opts)) {
            $params .= " LIMIT ".$opts['limit']; // offset,number of results
          } 

          if(array_key_exists('order', $opts)) {
            $params .= " ORDER BY ".$opts['order']; // field asc/desc, field asc/desc
          } 

        }

        // Result
        $result = R::getAll($sql, $params);
        if(!$result){ 
          return "e:";
        } else {
          if($json === true) {
            return json_encode($result);
          } else {
            return $result;
          }
        }
      } catch (Exception $e){
        throw new Exception('System Error: ', 0, $e);
      }
    }

    function getSequel($sql, $wherearray = NULL, $json = NULL, $opts = NULL, $row = true) {

      try { 
        
        // Where
        $wh = "";
        $params = array();
        if(is_array($wherearray)) {
          $wh = "WHERE ";
          foreach ($wherearray as $key => $val){
            $wh .= $key." = :".$key." AND ";
            $params[':'.$key] = $val;
          }
          $wh = trim($wh, " AND ");
          $sql .= $wh;          
        }

        // Params
        if(is_array($opts)) {
          
          if(array_key_exists('limit', $opts)) {
            $params .= " LIMIT ".$opts['limit']; // offset,number of results
          } 

          if(array_key_exists('order', $opts)) {
            $params .= " ORDER BY ".$opts['order']; // field asc/desc, field asc/desc
          } 

        }

        if($row === true) {
          $result = R::getRow($sql, $params);
        } else {
          $result = R::getAll($sql, $params);
        } 
        
        if(!$result) {
          return "e:";
        } else {
          if($json === true) {
            return json_encode($result);
          } else {
            return $result;
          }
        }
      } catch (Exception $e){
        throw new Exception('System Error: ', 0, $e);
      }
    } 
        
    function getNextRef($default,$fldname="clq_reference",$tbl="clqstring",$type="string"){$sql="SELECT ".$fldname." FROM ".$tbl." WHERE clq_type = '".$type."' AND clq_langcd <> 'z' ORDER BY id DESC LIMIT 1";$nextref=R::getCell($sql);$initref=$nextref;if($nextref!=""){$aa=explode("(",$nextref);$a=$aa[0];$ab=explode(")",$aa[1]);$n=$ab[0];$n=$n+1;$b=$ab[1];$nextref=$a."(".$n.")".$b;}else{$nextref=$default;};return trim($nextref);}
    
    function getFldset($formtype){$fldset=array();for($f=0;$f<count($this->formcfg['formfields']);$f++){foreach($this->formcfg['formfields'][$f]as$fld=>$array){$fldset[]=$fld;}}return$fldset;}
    
    function getShared($formtype){$formcfg=self::getFormConfig($formtype);$shared=array();for($f=0;$f<count($this->formcfg['formfields']);$f++){foreach($this->formcfg['formfields'][$f]as$fld=>$array){$shared[$fld]=$array['set'];}}return$shared;}
    function setVal($tbl,$fld,$val,$wherarray){


    }

    function getExtra($tbl,$fld,$where,$type="val"){
      $sql="SELECT clq_extra FROM ".$tbl." WHERE";
      foreach($where as$key=>$val){
        $v=explode('|',$val);
        $sql.=" ".$v[0]." ".$v[1]." '".$v[2]."' AND";
      }
      $sql=trim($sql," AND");
      $json=R::getCell($sql);
      $array=json_decode($json,true);
      if($type=="val"){
        return$array[$fld];
      } else {
        return$array;
      }
    }
    
    function getValbyId($table,$fld,$id){
      return self::getVal($table, $fld, array('id' => $id));
    }
    
    function getXtraValbyId($table,$fld,$id){
      $xtra = self::getVal($table, 'clq_extra', array('id' => $id)); $xtrarray = json_decode($xtra,true);return$xtrarray[$fld];
    }
    
    function getXtrabyRef($table,$ref){
      return self::getVal($table, 'clq_extra', array('clq_reference' => $ref, 'clq_langcd' => $this->lcd));
    }
    
    function getXtraFldbyRef($table,$ref,$fld){
      $xtra = self::getXtrabyRef($table,$ref); $xtrarray=json_decode($xtra,true);return$xtrarray[$fld];
    }
    
    function getID($table,$fldval,$fldname){
      return self::getVal($table, 'id', array($fldname => $fldval)); 
    }
    
    function getIDfromArray($table,$array){
      $sql="SELECT id FROM ".$table." WHERE ";foreach($array as$key=>$value){$sql.=$key." = '".$value."' AND ";}$sql=trim($sql," AND ");return R::getCell($sql);}

    /**
    * Format a Display Value
    */
    function formatValue($cell,$id,$schema){
      array_key_exists('subtype',$schema)?$type=$schema['subtype']:$type=$schema['type'];
      array_key_exists('options',$schema)?$defn=explode('|',$schema['options']):
      $defn=array(false);
      switch($type){
        case"yesno":$value=self::displayYesNo($cell);break;
        case"image":if($cell!='blank.gif'){$value=$this->rootpath.$defn[0].$cell;}else{$value=$cell;}break;
        case"idiom":$value=self::idiomFlag($cell);break;
        case"date":$value="<span style=\"\" class=\"\" >".self::fDate($cell)."</span>";break;
        case"textlimit":$value=self::textLimit($cell,$defn[0]);break;
        case"url":$value="<a href=\"".$cell."\" target=\"_blank\" style=\"\" class=\"click-cursor \" >".$cell."</a>";break;
        case"email":if($cell=='nomail'){$value='';}else{$value=$cell;}break;
        case"number":$value="<span style=\"\" class=\"\" >".self::formatNumber($cell)."</span>";break;
        case"currency":$value="<span style=\"\" class=\"\" >".self::formatCurrency($cell)."</span>";break;
        case"list":$list=$defn[0];$default=$defn[1];$value=self::cListOption($list,$cell,$default);break;
        case"companyname":$clqcmp=new clqdirectory();$coid=$clqcmp->getCoidFromID($id);$value=$clqcmp->getCompanyNameSet($coid);break;
        case"companyaddress":$clqcmp=new clqdirectory();$coid=$clqcmp->getCoidFromID($id);$value=$clqcmp->getCompanyAddressSet($coid);break;
        case"companylogo":$clqcmp=new clqdirectory();$coid=$clqcmp->getCoidFromID($id);$value=$clqcmp->getCompanyLogo($coid);break;
        case"addressmap":$clqcmp=new clqdirectory();$value=$clqcmp->getAddressMap($addrid);break;
        case"titlesummary":$value=self::getTitleSummary($cell,$id,$schema);break;
        default:case"string":$value=$cell;break;
      }
      return$value;
    }
    
    function getTitleSummary($cell,$id,$schema){if(!array_key_exists('dataspec',$schema)){return$cell;}else{$dcfg=$schema['dataspec'];
    $ref = self::getVal($dcfg['fromtable'], $dcfg['fromref'], array('id' => $id));
    $sql="SELECT ";foreach($dcfg['flds']as$fld){$sql.=$fld.",";}$sql=trim($sql,",");$sql.=" FROM ".$dcfg['totable']." WHERE ".$dcfg['toref']." = '".$ref."' ";$wh="";if(isset($dcfg['where'])){foreach($dcfg['where']as$q=>$whs){$w=explode('|',$whs);if($w[1]=="LIKE"){$w[2]=str_replace("´","",$w[2]);$wh.=" AND ".$w[0]." LIKE '%".$w[2]."%' ";}else{$wh.=" AND ".$w[0]." ".$w[1]." ".$w[2];}}}$wrepl=array("{lcd}","´");$wwith=array($this->lcd,"'");$wh=str_replace($wrepl,$wwith,$wh);$sql.=$wh;$value="";$row=R::getRow($sql);foreach($dcfg['flds']as$fld){$value.=$row[$fld].", ";}$value=trim($value,", ");return$value;}}
    
    function updateDB($val,$type){switch($type){case"date":case"dateto":case"datefrom":return clq::dbDate($val);break;case"number":case"currency":return clq::tidyVal($val,"currency");break;case"ownerid":case"author":return$_SESSION['CLQ_Username'];break;case"password":if($val!=""){$hasher=new PasswordHash(8,false);return$hasher->HashPassword($val);}else{return"";};break;case"textu":return strtoupper(clq::tidyVal($val,"sapos"));break;case"textl":return strtolower(clq::tidyVal($val,"sapos"));break;case"extra":return json_encode($val);break;default:return clq::tidyVal($val,"sapos");break;}}
    
    function insertRecord($p){$result="";$this->formcfg=self::getFormConfig($p['frm_type']);$thistbl=self::trapTable($this->formcfg['table']['tbl']);$thistblidm=$this->formcfg['table']['tblidm'];$fldset=self::getFldset($p['frm_type']);if($this->formcfg['table']['replicate']==true){$idms=$this->cfg['site.idiom'];foreach($idms as$code=>$lcdname){$row=R::dispense($thistbl);foreach($fldset as$f=>$fldname){$fldtype=self::getFrmFldType($fldname);if($fldtype!=""){$row->$fldname=self::updateDB($p[$fldname],$fldtype);}else{$row->$fldname=$p[$fldname];}};$row->$thistblidm=$code;if($code==$this->lcd){$recid=R::store($row);}else{$res=R::store($row);}}}else{$row=R::dispense($thistbl);foreach($fldset as$f=>$fldname){$fldtype=self::getFrmFldType($fldname);if($fldtype!=""){$row->$fldname=self::updateDB($p[$fldname],$fldtype);}else{$row->$fldname=$p[$fldname];}}$recid=R::store($row);}$rowa=array('id'=>$recid);$rowb=array();$this->gridcfg=self::getGridConfig($p['frm_type']);foreach($this->gridcfg['grid']['datacols']as$fldname=>$a){
    $val = self::getVal($thistbl, $fldname, array('id' => $recid));
    $rowb[$fldname]=$val;}$result=array_merge($rowa,$rowb);return json_encode($result);}
    
    function updateRecord($p){$this->formcfg=self::getFormConfig($p['frm_type']);$thistbl=self::trapTable($this->formcfg['table']['tbl']);$thisref=$this->formcfg['table']['tblref'];$thistype=$this->formcfg['table']['tbltype'];$thistblidm=$this->formcfg['table']['tblidm'];$fldset=self::getFldset($p['frm_type']);$row=R::load($thistbl,$p['frm_recid']);foreach($fldset as$f=>$fldname){if(!($fldname==$thistblidm||$fldname==$thisref||$fldname==$thistype)){$fldtype=self::getFrmFldType($fldname);if($fldtype!=""){if(isset($p[$fldname])){$row->$fldname=clqdb::updateDB($p[$fldname],$fldtype);}}}}$res=R::store($row);$result=array();$rowa=array('id'=>$p['frm_recid']);$rowb=array();$this->gridcfg=self::getGridConfig($p['frm_type']);foreach($this->gridcfg['grid']['datacols']as$fldname=>$a){
    $val = self::getVal($thistbl, $fldname, array('id' => $p['frm_recid']));
    $rowb[$fldname]=$val;}$result=array_merge($rowa,$rowb);return json_encode($rowb);}
    
    function updateSet($p){$this->formcfg=self::getFormConfig($p['frm_type']);$thistbl=self::trapTable($this->formcfg['table']['tbl']);$thisref=$this->formcfg['table']['tblref'];$thistype=$this->formcfg['table']['tbltype'];$thistblidm=$this->formcfg['table']['tblidm'];$fldspec=self::getFldset($p['frm_type']);$shared=self::getShared($p['frm_type']);foreach($this->cfg['site.idiom']as$lcdcode=>$lcdname){$thislcddata=array();foreach($fldspec as$f=>$fldname){if(!($fldname==$thistblidm||$fldname==$thisref||$fldname==$thistype)){if($shared[$fldname]==false){if(isset($p[$fldname]))$thislcddata[$fldname]=$p[$fldname];}else{if(isset($p[$fldname."_".$lcdcode]))$thislcddata[$fldname]=$p[$fldname."_".$lcdcode];}}}$sql="SELECT id FROM ".$thistbl." WHERE ".$thisref." = '".$p[$thisref]."' AND ".$thistblidm." = '".$lcdcode."'";$recid=R::getCell($sql);$set=R::load($thistbl,$recid);foreach($thislcddata as$fldname=>$val){$fldtype=self::getFrmFldType($fldname);if($fldtype!=""||$fldtype!=$thistblidm){$set->$fldname=clqdb::updateDB($val,$fldtype);}}$set->clq_langcd=$lcdcode;$res=R::store($set);unset($thislcddata);}$result=array();$rowa=array('id'=>$p['frm_recid']);$rowb=array();$this->gridcfg=self::getGridConfig($p['frm_type']);foreach($this->gridcfg['grid']['datacols']as$fldname=>$a){
    $val = self::getVal($thistbl, $fldname, array('id' => $p['frm_recid']));
    $rowb[$fldname]=$val;}$result=array_merge($rowa,$rowb);return json_encode($result);}
    
    function deleteRecord($p){$this->formcfg=self::getFormConfig($p['frm_type']);$thistbl=self::trapTable($this->formcfg['table']['tbl']);$thisref=$this->formcfg['table']['tblref'];$thistblidm=$this->formcfg['table']['tblidm'];$fldspec=self::getFldset($p['frm_type']);if($p['action']=="deleteset"){
    $ref = self::getVal($thistbl, $thisref, array('id' => $p['recid']));
    $result=R::exec("DELETE FROM ".$thistbl." WHERE ".$thisref." = '".$ref."'");}else{$result=R::exec("DELETE FROM ".$thistbl." WHERE id = ".$p['recid']);}return$result;}
    
    function updXtra(){$upd=R::load($_POST['tbl'],$_POST['recid']);$upd->clq_extra=base64_decode($_POST['xtra']);$result=$_POST['tbl'].':'.$_POST['recid'].':';$result.=R::store($upd);return$result;}
    
    function updateRecordbyFld($table,$array,$fldval,$fldname){$id=self::getID($table,$fldval,$fldname);$updrec=R::load($table,$id);foreach($array as$key=>$value){$updrec->$key=clq::tidyVal($value,"sapos");}$newid=R::store($updrec);return$newid;}
    
    function trapTable($tbl){if(stristr($tbl,'_view',true)!==false){return stristr($tbl,'_view',true);}else{return$tbl;}}
    
    function tidyResult($result){$result=json_encode($result);$result=str_replace("|"," | ",$result);return$result;}

    function getSQL($fldstr,$tbl,$wherestr,$ordstr,$limit=null){
      return "SELECT ".self::fieldList($fldstr)." FROM ".$tbl." ".self::whereClause($wherestr)." ".self::orderBy($ordstr);
    }

    function whereClause($wherearray){
      $where=" WHERE ";
      foreach($wherearray as$n=>$ln) {
        $w=explode("|",$ln);
        $w[2]=str_replace("´","'",$w[2]);
        if(stristr($w[1],"LIKE")){
          $where.=$w[0]." ".$w[1]." %".$w[2]."% AND ";
        } else {
          $where.=$w[0]." ".$w[1]." ".$w[2]." AND ";
        }
      };
      $where=trim($where," AND ");
      $where=str_replace("{lcd}", $this->lcd, $where);
      return $where;
    }

    function fieldList($fldstr){$fldlist="id, ";foreach($fldstr as$fld=>$params){$fldlist.=$fld.", ";}$fldlist=trim($fldlist,", ");return$fldlist;}

    function orderBy($ordstr){$ordarray=explode(",",$ordstr);$orderby=" ORDER BY ";foreach($ordarray as$o=>$ord){$orderby.=$ord.", ";}$orderby=trim($orderby,", ");return$orderby;}

    function checkUserinDB($uid,$pwd){$hasher=new PasswordHash(8,false);$sql="SELECT * FROM clquser WHERE clq_username = '".$uid."'";
      $row=R::getRow($sql);$check=$hasher->CheckPassword($pwd,$row['clq_password']);if($check){$_SESSION['CLQ_Username']=$row['clq_fullname'];$_SESSION['CLQ_Userid']=$row['clq_username'];$_SESSION['CLQ_Language']=$row['clq_idiom'];$_SESSION['CLQ_UserLevel']=$row['clq_level'];$_SESSION['CLQ_Group']=$row['clq_group'];if(isset($row->username)){return$row->username;}else{return"";}}
    }
    function getFrmFldType($fldname){$type="text";for($f=0;$f<count($this->formcfg['formfields']);$f++){if(array_key_exists($fldname,$this->formcfg['formfields'][$f])){if(array_key_exists('subtype',$this->formcfg['formfields'][$f][$fldname])){if($this->formcfg['formfields'][$f][$fldname]['subtype']!=''){$type=$this->formcfg['formfields'][$f][$fldname]['subtype'];}else{if(array_key_exists('type',$this->formcfg['formfields'][$f][$fldname])){$type=$this->formcfg['formfields'][$f][$fldname]['type'];}}}}}return$type;}

    /**
    * Format an Insert Value
    */
    function fVal($fld, $val) {

      if(stristr($fld, 'date')) {
        return clq::dbDate($val);
      // } elseif(stristr($fld, 'number')) {

      } else {
        return $val;
      }
    }

    public function scaffoldTable($table, $file, $ll = true) {
    include($this->rootpath."scaffold/".$file.".php");       
    if($ll == true) {
    foreach($this->cfg['site.idiom'] as $idm => $idmname) {
    for($m = 0; $m < count($menuarray); $m++) {
    $nm = R::dispense($table);
    $mm = $menuarray[$m];
    foreach($mm as $fld => $val) {
    $val = str_replace($this->qrepl, $this->qwith, $val);
    $nm->$fld = $val;
    }
    $nm->clq_langcd = $idm;
    $result = R::store($nm);
    }
    }
    } else {
    for($m = 0; $m < count($menuarray); $m++) {
    $nm = R::dispense($table);
    foreach($menuarray[$m] as $fld => $val) {
    $val = str_replace($this->qrepl, $this->qwith, $val);
    $nm->$fld = $val;
    }
    $nm->clq_langcd = $idm;
    $result = R::store($nm);
    }
    }
    }

	public function addDiagnostics($label, $value) {
	if($this->cfg['site.test'] == true) {
	if(!isset($_SESSION['CLQ_Diagnostics'])) {
	$_SESSION['CLQ_Diagnostics'] = array();
	}
	$_SESSION['CLQ_Diagnostics'][$label] = $value;
	}
	}
	public function displayDiagnostics($label, $value) {
	$table = "<table style='' cellpadding=3 cellspacing=0 width='100%' id='displaydiagnostics' >".PHP_EOL;
	$table .= "<thead>".PHP_EOL;
	$table .= "<th>".$this->cnStr(0,'Label')."</th>".PHP_EOL;
	$table .= "<th>".$this->cnStr(0,'Value')."</th>".PHP_EOL;
	$table .= "<thead>".PHP_EOL;
	$table .= "<tbody>".PHP_EOL;
	foreach($_SESSION['CLQ_Diagnostics'] as $lbl => $val) {
	$table .= "<tr>".PHP_EOL;
	$table .= "<td valign='top' align='right' class='tblabel' >".$lbl.":</td>".PHP_EOL;
	$table .= "<td valign='top' class='tbvalue' >".$val."</td>".PHP_EOL;
	$table .= "</tr>".PHP_EOL;
	}
	$table .= "</tbody>".PHP_EOL;
	$table .= "</table>";
	return $table;
	}   
	
		/*
		 * Generates an array of all the keys and values related to languages
		'site.iconpath'
		'site.idiom' => array('en' => 'English', 'es' => 'Español'),
		'site.idiomflags' => array('en' => 'en.png', 'es' => 'es.png'),
		'site.defaultidiom' => 'en',
		 * */
		function cLcd() {
			
			$lcdarray = array();
			
			foreach($this->cfg['site.idiom'] as $lcdcode => $lcdname) {
				$lcdarray[$lcdcode]['code'] = $lcdcode;
				$lcdarray[$lcdcode]['name'] = $lcdname;
			}
			
			foreach($this->cfg['site.idiomflags'] as $lcdcode => $lcdicon) {
				$lcdarray[$lcdcode]['flag'] = $lcdicon;
				$lcdarray[$lcdcode]['flagpath'] = $this->cfg['site.iconpath'].$lcdicon;
			}
			
			foreach($lcdarray as $lcdcode => $ln) {
				$lcdarray[$lcdcode]['selected'] = false;
				if($lcdcode == $this->lcd) {
					$lcdarray[$lcdcode]['selected'] = true;
				}
			}
			return $lcdarray;
		}
		
		function jqTidy($str) {
				
			$str = str_replace('<script language="javascript" type="text/javascript">', '', $str);
			$str = str_replace('</script>', '', $str);
			$str = str_replace('/* <![CDATA[ */', '', $str);
			$str = str_replace('/* ]]> */', '', $str);
			$str = str_replace('jQuery', '$', $str);
			
			// Anything else??
			
			return $str;
		}
		
		/**
		 * Join $file to $dir path, and clean up any excess slashes.
		 *
		 * @author A. Grandt <php@grandt.com>
		 * @author Greg Kappatos
		 *
		 * @param string $dir
		 * @param string $file
		 *
		 * @return string Joined path, with the correct forward slash dir separator.
		 */
		function pathJoin($dir, $file) {
			return self::getrelpath($dir.(empty($dir) || empty($file) ? '' : DIRECTORY_SEPARATOR) . $file);
		}

		/**
		 * Clean up a path, removing any unnecessary elements such as /./, // or redundant ../ segments.
		 * If the path starts with a "/", it is deemed an absolute path and any /../ in the beginning is stripped off.
		 * The returned path will not end in a "/".
		 *
		 * @param String $path The path to clean up
		 * @return String the clean path
		*/
		function getrelpath($path) {
			$path = preg_replace("#/+\.?/+#", "/", str_replace("\\", "/", $path));
			$dirs = explode("/", rtrim(preg_replace('#^(\./)+#', '', $path), '/'));
					
			$offset = 0;
			$sub = 0;
			$subOffset = 0;
			$root = "";

			if (empty($dirs[0])) {
				$root = "/";
				$dirs = array_splice($dirs, 1);
			} else if (preg_match("#[A-Za-z]:#", $dirs[0])) {
				$root = strtoupper($dirs[0]) . "/";
				$dirs = array_splice($dirs, 1);
			} 

			$newDirs = array();
			foreach ($dirs as $dir) {
				if ($dir !== "..") {
					$subOffset--;    
					$newDirs[++$offset] = $dir;
				} else {
					$subOffset++;
					if (--$offset < 0) {
						$offset = 0;
						if ($subOffset > $sub) {
							$sub++;
						} 
					}
				}
			}

			if (empty($root)) {
				$root = str_repeat("../", $sub);
			} 
			return $root . implode("/", array_slice($newDirs, 0, $offset));
		}

        /**
        * Generate the compatible menu here and then run it
        */
        public function clqUnList($listtype, $json = false) {  
            $ulst = self::getULSet($listtype, "_", $json); // Top Level
            return $ulst;
        }

        function getULSet($listtype, $z, $json) {
            
            $sql = "SELECT * FROM clqdata WHERE clq_type = ? AND clq_langcd = ? AND clq_order LIKE ? ORDER BY clq_order ASC";
            $rs = R::getAll($sql, array($listtype, $this->lcd, $z));

            if(count($rs) > 0) {
              
              if($json == false) {

                $ulst = '<ul>';
                for($r = 0; $r < count($rs); $r++) {      
                  $ulst .= self::getLItem($rs[$r], $listtype, $json);
                }      
                $ulst .= "</ul>".PHP_EOL; 

              } else {

                $ulst = '[';
                for($r = 0; $r < count($rs); $r++) {      
                  $ulst .= self::getLItem($rs[$r], $listtype, $json);
                }      
                $ulst = trim($ulst, ",");
                $ulst .= "]"; 
              }

            } else {
              $ulst = "";
            }
            return $ulst;
        }

        function getLItem($row, $listtype, $json) {

          if($json == false) {

            $line = '<li>'.$row['clq_reference'].' : '.$row['clq_order'].' : '.$row['clq_title'];
            $line .= self::getULSet($listtype, $row['clq_order']."_", $json);
            $line .= '</li>'.PHP_EOL;

          } else {

            $line = '{id: "'.$row['clq_reference'].'", value: "'.$row['clq_order'].' : '.$row['clq_title'].'"';
            $nextset = self::getULSet($listtype, $row['clq_order']."_", $json);
            if($nextset != "") {
               $line .= ', data: '.$nextset;
            }
            $line .= '},';

          }

          return $line;
        }



}
// Class Ends

class Q extends clq{};
class_alias('clq','Q ');

function ssort($data, $x, $g) {
    $ptn = explode('|', $x);
    foreach($data as $k => $el) {
      $ref = $el[$g];
      $num = ltrim(rtrim($ref, $ptn[1]), $ptn[0]);
      $b[$k] = $num;
    }
    array_multisort($b, SORT_NUMERIC, $data);
    return $data; 
}

class DateTimeChain extends DateTime{
  public function modify($modify){parent::modify($modify);return$this;}
  public function setDate($year,$month,$day){parent::setDate($year,$month,$day);return$this;}
  public function setISODate($year,$week,$day=null){parent::setISODate($year,$week,$day);return$this;}
  public function setTime($hour,$minute,$second=null){parent::setTime($hour,$minute,$second);return$this;}
  public function setTimezone($timezone){parent::setTimezone($timezone);return$this;}
}

define ("DEVELOP_MODE",true);
define ("DOCTYPE","<!DOCTYPE html>");
class html {
   static $STACK = array();
   static $JSMSG = NULL;
   # Close all opened tags
   function finish()  {self::closetag(-1);if (self::$JSMSG) self::script("txt=alert('".self::$JSMSG."');");}
   # Onerow
   function srow($ONEPARAM = NULL) {
     if (!$ONEPARAM) return;
     $PARAMS = explode("|",$ONEPARAM);
     return(self::tag($PARAMS));
   }
   # Insert HTML tag. the first param is the tag name
   function tag()  {
      $RETVAL = false;
      $INNERHTML = NULL; $HTML = ""; $TAG = "";
      $PARAMS = func_get_args();
      if (is_array($PARAMS[0])) $PARAMS = $PARAMS[0];
      if (!$PARAMS) return; # No params, end
      # Prepare tag params
      foreach ($PARAMS as $ONE) {
        $ONEATTR = @explode("=",$ONE,2);
        if ($ONEATTR['0'] == "txt") $INNERHTML = $ONEATTR['1'];
        else if ($ONEATTR['0'] == "ret") $RETVAL = true;
        else if ($ONEATTR['0'] == $PARAMS['0']) $TAG = $ONEATTR['0'];
      }
      # Creating HTML row
      if ( (DEVELOP_MODE == true) && !$RETVAL) $HTML = str_repeat(" ",count(self::$STACK));
      $HTML .= sprintf("<%s",$TAG);
      foreach ($PARAMS as $ONE) {
	if (!$ONE) continue;
        $ONEATTR = @explode("=",$ONE,2);
        if ( ($ONEATTR['0'] == "txt") || ($ONEATTR['0'] == "ret") || ($ONEATTR['0'] == $TAG) ) continue;
        $HTML .= sprintf(' %s="%s"',$ONEATTR['0'],$ONEATTR['1']);
      }
      if (!self::check_if_not_closable_tag($TAG)) $HTML .= ">";
      if ( isset($INNERHTML) && !self::check_if_not_closable_tag($TAG) ) {
        $HTML .= $INNERHTML;
        $HTML .= sprintf("</%s>",$TAG);
      }
      else if ( !!self::check_if_not_closable_tag($TAG) ) $HTML .= " />";
      else self::$STACK[count(self::$STACK)] = $TAG;
      # End of HTML row
      if ( (DEVELOP == true) && !$RETVAL) $HTML .= "\n";
      if (!!$RETVAL) return ($HTML); else print $HTML;
   }
   # Checking for not closable tag
   private function check_if_not_closable_tag($TAG) {
     $ENUM = array("img","input","br","hr","param","meta","link","base","frame","embed");
     return (in_array($TAG,$ENUM));
   }
   #------------------ Tag closer --------------------
   function closetag($NUM = 1,$RETVAL = false) {
   $HTML = NULL;
   if ( $NUM < 1 ) $NUM = count(self::$STACK);
   $INDEX = count(self::$STACK) - 1;
     while ($NUM--) {
       $HTROW = sprintf("</%s>",self::$STACK[$INDEX]);
       if (DEVELOP == true && !$RETVAL) $HTROW = @str_repeat(" ",$INDEX).$HTROW."\n";
       $HTML .= $HTROW;
       unset (self::$STACK[$INDEX--]);
     }
     if (!$RETVAL) print $HTML; else return ($HTML);
   }
   #------------------- Start block ------------------------
   function start_block() {self::$STACK[count(self::$STACK)] = "stpt";}
   #------------------ End block --------------------------
   function end_block() {
      for ($I = count(self::$STACK) ; $I > 0 ; --$I) {if (self::$STACK[$I]=="stpt") break;}
      if (!$I) return;
      $CLOSING = count(self::$STACK) - $I - 1;
      if (!!$CLOSING) self::closetag($CLOSING);
      unset (self::$STACK[(count(self::$STACK) - 1)]);
   }
   #------------------ Display header --------------------
   function display_header() {self::set_header("display");}
   #------------------ Header preset ---------------------
   function set_header() {
     $ARGS = func_get_args();
     static $HEADER =  NULL;
     foreach ($ARGS as $ONE) {
       $EXP = explode("=",$ONE,2);
       if ( (DEVELOP == true) && ($EXP['0'] != "display") ) { $HEADER .= "  ";$EOL = "\n"; } else $EOL = NULL;
       if (!$EXP['0']) return;
       else if ($EXP['0'] == "charset") $HEADER .= self::meta("http-equiv=Content-Type","content=text/html;charset=".$EXP['1'],"ret").$EOL;
       else if ($EXP['0'] == "description") $HEADER .= self::meta("name=Description","content=".$EXP['1'],"ret").$EOL;
       else if ($EXP['0'] == "keywords") $HEADER .= self::meta("name=keywords","content=".$EXP['1'],"ret").$EOL;
       else if ($EXP['0'] == "refresh") $HEADER .= self::meta("http-equiv=refresh","content=".$EXP['1'],"ret").$EOL;
       else if ($EXP['0'] == "language") $HEADER .= self::meta("http-equiv=language","content=".$EXP['1'],"ret").$EOL;
       else if ($EXP['0'] == "nocache") $HEADER .= self::meta("http-equiv=cache-control","content=no-cache","ret").$EOL;
       else if ($EXP['0'] == "title") $HEADER .= self::title("txt=". $EXP['1'],"ret").$EOL;
       else if ($EXP['0'] == "css") $HEADER .= self::link("rel=stylesheet","type=text/css","href=".$EXP['1'],"ret").$EOL;
       else if ($EXP['0'] == "script") $HEADER .= self::script("type=text/javascript","txt=".$EXP['1'],"ret").$EOL;
       else if ($EXP['0'] == "icon") $HEADER .= self::link("rel=shortcut icon","href=".$EXP['1'],"ret").$EOL;
       else if ($EXP['0'] == "link") $HEADER .= self::srow("link|".$EXP['1']."|ret").$EOL; # (ie.: link=name=content|href=2|param2=3)
       else if ($EXP['0'] == "meta") $HEADER .= self::srow("meta|".$EXP['1']."|ret").$EOL; # (ie.: meta=name=content|param=2|param2=3)
       else if ($EXP['0'] == "js") $HEADER .= self::script("type=text/javascript","src=".$EXP['1'],"txt=","ret").$EOL;
       else if ($EXP['0'] == "display") {print DOCTYPE."\n"; self::html();self::head(); print $HEADER;self::closetag();}
       else {$HEADER .= self::tag(array_merge(func_get_args(),array("ret"))).$EOL;break;}
     }
   }
   #---------------- Addional elements -------------------
   function write($TXT) {print (str_repeat(" ",count(self::$STACK)).$TXT."\n");}
   function remark($TXT) { if (DEVELOP_MODE == true) self::write("<!--".$TXT."-->"); }
   function message($TXT) { self::$JSMSG .= $TXT; }
   #---------------- active elements ---------------------
   function button() {return(self::tag(array_merge(array("input","type=button"),func_get_args())));}
   function checkbox() {return(self::tag(array_merge(array("input","type=checkbox"),func_get_args())));}
   function file() {return(self::tag(array_merge(array("input","type=file"),func_get_args())));}
   function hidden() {return(self::tag(array_merge(array("input","type=hidden"),func_get_args())));}
   function image() {return(self::tag(array_merge(array("input","type=image"),func_get_args())));}
   function password() {return(self::tag(array_merge(array("input","type=password"),func_get_args())));}
   function radio() {return(self::tag(array_merge(array("input","type=radio"),func_get_args())));}
   function reset() {return(self::tag(array_merge(array("input","type=reset"),func_get_args())));}
   function submit() {return(self::tag(array_merge(array("input","type=submit"),func_get_args())));}
   function text() {return(self::tag(array_merge(array("input","type=text"),func_get_args())));}
   function date() {return(self::tag(array_merge(array("input","type=date"),func_get_args())));}
   function number() {return(self::tag(array_merge(array("input","type=number"),func_get_args())));}
   function range() {return(self::tag(array_merge(array("input","type=range"),func_get_args())));}
   function slider() {return(self::tag(array_merge(array("input","type=slider"),func_get_args())));}
   #--------------- Create any other tag -----------------
   static function __callStatic($name, $arguments) {return(self::tag(array_merge(array($name),$arguments)));}
}
