<?php
class clqcodedit extends clq {
public $cfg = array();
public $rootpath = "";
public $jspath = "";
public $includepath = "";
public function __construct() {
global $cfg; $this->cfg = $cfg;	
global $rootpath; $this->rootpath = $rootpath;		
$this->jspath = $this->rootpath."js/";
$this->includepath = $this->rootpath."includes/";
$this->docroot = str_replace('\\', '/', $_SERVER["DOCUMENT_ROOT"])."/";
global $lcd; $this->lcd = $lcd;
}
public function test() {
$str = "Test: " . $this->thisclass;
return $str;
}
function setVar($var, $val) {
$this->$var = $val;
}
function getVar($var) {
return $this->$var;
}
function pageHeader() {
$hdr = "";
$hdr .= clqutil::meta('popup');
$hdr .= "<script type='text/javascript'>var jlcd = 'en';</script>";	
$hdr .= "<script type='text/javascript' src='".$this->jspath."jquery.js' ></script>";
$hdr .= "<script type='text/javascript' src='".$this->jspath."jquery-ui.js' ></script>";
$hdr .= "<script type='text/javascript' src='".$this->jspath."clqstartup.js' ></script>";
$hdr .= "<script type='text/javascript' src='".$this->jspath."clqutilities.js' ></script>";
$hdr .= "<script type='text/javascript' src='".$this->jspath."message.js' ></script>";
$hdr .= '<link rel="stylesheet" href="'.$this->rootpath.'admin/style/admin.css" type="text/css" media="screen, projection">';
$hdr .= '<link rel="stylesheet" href="'.$this->rootpath.'js/filetree/jqueryFileTree.css" type="text/css" media="screen, projection">';
$hdr .= "<style>
body, html {background-image: url(../images/blank.gif);}
</style>";
$hdr .= "</head>";
$hdr .= "<body spellcheck='false'>";
return $hdr;
}
function publishEditor() {
$editor = "";
$editor .= self::pageHeader();
$editor .= '
<div class="content" style="min-height:580px;">
<div class="column" style="width:140px; float:left;">
<h2 style="margin:0px; padding: 0px 0px 10px 5px;">'.clq::cStr('str(165)', 'Editor').'</h2>
<div id="filetree" class=""></div>
</div>
<div class="column" id="editor" style="width:780px; float:left;">				
<form method="post" action="#">
<input type="hidden" name="filename" id="filename" value="" />
<input type="button" name="" class="btn savebutton" value="'.clq::cStr('str(67)', 'Save').'" style="float:right; margin: 5px;">
<input type="button" name="" class="btn clearbutton" value="'.clq::cStr('str(71)', 'Clear').'" style="margin:5px; float:right;"><br />			
<textarea id="editcode" name="editcode" style="width:100%; height: 520px; border: 0px; background-color: #F2F2F2;"></textarea>
</form>
</div>		
</div>
';
$editor .= self::pageFooter();
return $editor;
} 
function pageFooter() {
$root = $this->rootpath;
$ftr = "";
$ftr .= '</body></html>';
$ftr .= "
<script type='text/javascript'>
<!--//
$(function() {
// include extra jQuery Scripts here
$.scriptPath='".$this->rootpath."js/filetree/';
jQuery.require([
'jqueryFileTree.js',
'edit_area_full.js',
]);	
$('button').button();  
$('#filetree').fileTree({
root: '".$this->rootpath."',
script:'".$this->rootpath."js/filetree/connectors/jqueryFileTree.php',
multiFolder: true
}, function(file) { openFile(file);});
function openFile(file) {
var urlstr = '".$this->rootpath."js/filetree/fileactions.php';
var postdata = 'action=getfile&filename=' + file;
$.ajax({ type: 'POST', url: urlstr, data: postdata,								
success: function(msg){	
$('#filename').val(file);
$('#editcode').val(msg);
var extn = file.substr( (file.lastIndexOf('.') +1) );
editAreaLoader.init({
id : 'editcode',			// textarea id
syntax: extn,				// syntax to be uses for highglighting
start_highlight: true,		// to display with highlight mode on start-up
language: '".$_SESSION['CLQ_Langcd']."',
allow_resize: 'both',
word_wrap: true			
});							
},
error: function() { apprise('".clq::cStr('str(156)', 'Error opening file')."');}
});				
}
$('.btn').button();
$('.savebutton').click(function() {
var newtext = editAreaLoader.getValue('editcode');
var fn = $('#filename').val();
var urlstr = '".$this->rootpath."js/filetree/fileactions.php';
var postdata = 'action=updatefile&filename=' + fn + '&newtext=' + newtext;
$.ajax({ type: 'POST', url: urlstr, data: postdata,								
success: function(msg){	apprise('".clq::cStr('str(102)', 'Saved successfully').": ' + msg); },
error: function() { apprise('".clq::cStr('str(156)')."', 'Error with saving');}
});				
});
$('.clearbutton').click(function() { reLoad(); });
});
//-->
</script>";
return $ftr;    
} 
}