<?php
class clqmenu extends clqdb {
public $thisclass = "clqmenu";
public $repl = array("*"," ","¬"); public $with = array("'","+","&");
public $topmenu = array(), $sidemenu = array(), $popup1 = array(), $popup2 = array(), $rootpath, $includepath, $iconpath, $lcd, 
$cfg = array(), $keyfield = "id", $group = "operator", $thismenu = "adminmenu", $menudivid, $sidemenuicon;
function __construct() {	
global $rootpath; $this->rootpath = $rootpath;
$this->includepath = $this->rootpath."includes/";
$this->iconpath = $this->rootpath."images/icons/";
$this->sidemenuicon = $this->rootpath."view/style/images/icons48/";
$this->imgdir = $this->rootpath."images/";
global $cfg; $this->cfg = $cfg;
global $lcd; $this->lcd = $lcd;
}
function setVar($var, $val) { $this->$var = $val; }
function getVar($var) { return $this->$var; }
function plainMenu() {		
foreach($this->topmenu as $txt => $class) {
$txt = str_replace("u", "", $txt);
if(is_array($class)) {				
$menu .= '<li class="liclass drop-down"><a href="#"><span class="">'.$this->cStr('str('.$txt.')').'</span></a> <ul>'.self::subMenu($class).'</ul></li>';
} else {				
$menu .= '<li class="liclass single-link"><a target="" href="#" class="'.$class.'button">'.$this->cStr('str('.$txt.')', $class).'</a></li>';
}
}
return "<ul>".$menu."</ul>";					
}	
function subMenu($menuarray) {	
foreach($menuarray as $txt => $class) {			
$txt = str_replace("u", "", $txt);				
$menu .= '<li class=""><a target="" href="#" class="'.$class.'button">'.$this->cStr('str('.$txt.')').'</a></li>';
}
return $menu;					
}	
function sideMenu($sdmenu) {
$menu = '<ul id="navigation">';
foreach($sdmenu['base'] as $key => $row) {
$l = explode("|", $row); $menu .= '<li><a href="'.$l[0].'" id="'.$key.'" title="'.str($l[2]).'" style="background-image: url('.$this->sidemenuicon.$l[1].')"></a></li>';
}; $menu .= '</ul>'; return $menu;
}
public function publishMenu($thismenu = "adminmenu") {
if($thismenu != "") {$this->menu = $thismenu;};
$menu = self::ItemSet(0);
return $menu;				
}
function itemSet($parentid = 0) {
if(isset($_GET['clq_langcd'])) {
$thisidm = $_GET['clq_langcd'];
} else {
$thisidm = $this->lcd;
}  
$fldlist = array(
'clq_text',
'clq_summary',
'clq_url',
'clq_value',
'clq_options',
'clq_parent',
'clq_order',
'clq_usage',
'clq_image',
);  
$wherearray = array(
'clq_parent|=|'.$parentid, 
'clq_langcd|=|´'.$thisidm.'´', 
'clq_options|<>|´n´',
'clq_type|=|´'.$this->menu.'´',
);
$sql = "SELECT ".clqdb::fieldList(array_flip($fldlist))." FROM clqdata ".clqdb::whereClause($wherearray)." ".clqdb::orderBy('clq_order ASC');
$rs = R::getAll($sql);
if($rs) {
if($parentid == 0) {
$ulid = "clqmenu";
$liclass = "liclass drop-down";
} else {
$ulid = "";
$liclass = "liclass single-link";
}
$m = ""; 
$m .= '<div class="" style="" id="'.$this->menudivid.'" >';                            
$m .= '<ul class="col10" style="" id="'.$ulid.'" >';
for($r = 0; $r < count($rs); $r++) {
$m.= '<li class="'.$liclass.'" style="" id="" >';
$m.= self::lineItem($rs[$r]);
$m.= self::itemSet($rs[$r]['id']);
$m.= '</li>'.PHP_EOL;                  
}
$m .= '</ul>';
$m .= '</div>';
return $m;
} else {
return;
}           
}
public function lineItem($row) {
switch ($row['clq_usage']) {		
case "label":
$itm = '<a href="#" '.$opts.' title="'.$row['clq_summary'].'"  class="'.$row['clq_value'].'">'.$row['clq_text'].'</a>';
break;
case "webpage":
$url = str_replace("{lcd}", $this->lcd, $row['clq_url']);
$itm = '<a href="'.$url.'" '.$opts.' title="'.$row['clq_summary'].'" class="'.$row['clq_value'].'" >'.$row['clq_text'].'</a>';
break;
case "image":
$url = str_replace("{lcd}", $this->lcd, $row['clq_url']);
$itm = '<a href="'.$url.'" '.$opts.' title="'.$row['clq_summary'].'" class="'.$row['clq_value'].'" ><img src="'.$this->rootpath.$this->imgpath.$row['clq_image'].'" '.$row['clq_text'].' /></a>'; 			break;
case "script":
$itm = '<a href="javascript:'.$row['clq_url'].'" '.$opts.' title="'.$row['clq_summary'].'"  class="'.$row['clq_value'].'">'.$row['clq_text'].'</a>';
break;
case "plain": default:
$itm = '<a '.$row['clq_url'].'>'.$row['clq_text'].'</a>';
break;
}
return $itm;
}
function convertOptions($qvalue) {
$a = explode("|", $qvalue); $options = "";
foreach($a as $n => $b) {
$opt = explode(",", $b);
$options .= $opt[0].'="'.$opt[1].'" ';
}
return $options;
}
function publishFooter() {
$ftr = "";
$ftr .= $this->footer;
$sql = "SELECT id FROM clqdata WHERE clq_type = 'footer' AND clq_langcd = '".$this->lcd."' AND clq_display <> 'n' ORDER BY clq_order ASC";
$rs = R::getAll($sql);
for($l = 0; $l < count($rs); $l++) {
$ftr .= " | ".$this->lineItem($rs[$l]['id']);
}	
return $ftr;		
}
function imageIcons($hdrbuttons) {
$str = '';  
foreach($hdrbuttons as $k => $a) {
if($k == 'idioms') {$str .= self::imageIdioms($hdrbuttons['idioms']);}
elseif($k == 'print') {$str .= self::imagePrint($hdrbuttons['print']);}
else{
$str .= self::displayIcon($a, $k);
}
}       
return $str;    
}
function imageIdioms($a) {
$img = '';
foreach($a as $code => $ln) { if($code != $this->lcd) {
$img .= '<a href="'.$_SERVER['PHP_SELF'].'?langcd='.$code.'&page=admin"><img src="'.$this->cfg['site.defaultdir'].$this->imgdir.$ln[0].'" title="'.$ln[1].'" alt="'.$ln[1].'" class="'.$ln[2].'" style="height: 16px; vertical-align: middle; padding-right: 6px;" /></a>';
}}      
return $img;        
}
function displayIcon($a, $k) {
if($_SESSION['CLQ_Level'] >= $a[3]) {
	return '<a><img src="'.$this->cfg['site.defaultdir'].$this->imgdir.$a[0].'" title="'.$a[1].'" alt="'.$a[1].'" rel="'.$k.'" class="'.$a[2].' icon" style="" /></a>';
} else { return; }
}
function imagePrint($a) {   
return '<a><img src="'.$this->cfg['site.defaultdir'].$this->imgdir.$a[0].'" title="'.$a[1].'" alt="'.$a[1].'" class="icon print_button" style="" /></a>';   
}
}