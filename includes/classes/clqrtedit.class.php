<?php 
class clqrtedit extends clq {
public $thisclass = "clqrtedit extends clq";
public $schema = array(), $type, $recid, $text, $row, $idiom; 
public $repl 			= array("*"," ","¬"); 
public $with 			= array("'","+","&");
public $lcd				= "en";
public $cfg 			= array();
function __construct($ptype) {
global $rootpath; $this->rootpath = $rootpath;
global $lcd; $this->lcd = $lcd;
global $cfg; $this->cfg = $cfg;
$this->classpath = $this->rootpath."classes/";
$this->imgpath = $this->rootpath."view/images/";
$this->iconpath = $this->rootpath."view/images/";       
$this->type = $ptype;
global $clqschema; $this->schema = $clqschema[$this->type];		
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
$this->row = R::$f->begin()->select('*')->from($this->schema['tbl'])->where(' id = ? ')->put($this->recid)->get('row');
$this->text = $this->row[$this->schema['tblcnt']];
$this->idiom = $this->row[$this->schema['tblidm']];
$hdr .= "<script type='text/javascript'>var jlcd = '".$_SESSION['CLQ_Langcd']."';</script><script type='text/javascript' src='".$this->rootpath."tmp/admin.js' ></script><script type='text/javascript' src='".$this->rootpath."js/message.js' ></script>";	
$hdr .= '<link rel="stylesheet" href="'.$this->rootpath.'view/style/style.css" type="text/css" media="screen, projection">';
$hdr .= "<style>
.menubuttons {height:8px; padding:2px; line-height:8px;}
.mb {height:8px; padding:2px; line-height:8px;}
.small {font-size:10px;}
/*
.clqpopupclass *, .mceContentBody *, body.mceContentBody * {
border-left:50px solid cream;
margin-left: 30px;
padding-left:30px;
width: 600px;
color: red;
}
*/
</style>";
$hdr .= "</head>";
$hdr .= "<body spellcheck='false' style='background-color: #E5EFFD; padding: 0px; margin: 0px;'>";
return $hdr;
}
function publishEditor() {
$editor = "";
$editor .= self::pageHeader();
$editor .= '
<div class="" style="margin: 0 auto; padding: 0px; scrolling: none; overflow: hidden;">
<form name="contenteditor" id="contenteditor" action="#" method="post" >
<input type="hidden" name="recid" value="'.$this->recid.'" />
<input type="hidden" name="tbl" value="'.$this->schema['tbl'].'" />
<input type="hidden" name="tblref" value="'.$this->schema['tblref'].'" />
<input type="hidden" name="tbltype" value="'.$this->schema['tbltype'].'" />
<input type="hidden" name="langcd" value="'.$this->lcd.'" />
<input type="hidden" name="userid" value="'.$_SESSION['CLQ_UserName'].'" />
<textarea name="'.$this->schema['tblcnt'].'" id="'.$this->schema['tblcnt'].'" style="width:100%; height:100%; margin: 0 auto;">'.$this->text.'</textarea>
</form>
</div>
<div style="height:16px; color: #003366; padding:2px; padding-left: 10px; text-align: left;">'.$this->idiom.':'.$this->row['clq_common'].'</div>
';
$editor .= self::pageFooter();
return $editor;
}    
function pageFooter() {
$ftr = "";
$ftr .= '</body></html>';
$ftr .= "
<script type='text/javascript'>
<!--//
$(function() {
// include extra jQuery Scripts here
$.scriptPath='".$this->rootpath."js/tiny_mce/';
jQuery.require([
'jquery.tinymce.js', 
'plugins/tinybrowser/tb_tinymce.js.php'	
]);	
$('button').button();  
// Tiny MCE Initialisation
var inst = $('#".$this->schema['tblcnt']."').tinymce({
script_url : '".$this->rootpath."js/tiny_mce/tiny_mce_gzip.php',
force_br_newlines : true,
force_p_newlines : false,
forced_root_block : '', // Needed for 3.x
theme : 'advanced',
mode: 'textareas',
skin : 'o2k7',
language : '".$this->lcd."',
auto_cleanup_word : true,
plugins :'advhr,advimage,advlink,contextmenu,directionality,emotions,fullscreen,iespell,inlinepopups,insertdatetime,layer,media,nonbreaking,noneditable,pagebreak,paste,preview,print,safari,searchreplace,style,table,template,visualchars,xhtmlxtras,tinyautosave',
width: '680', 
height: '627',
plugin_insertdate_dateFormat : '%d-%M-%Y',
file_browser_callback : 'tinyBrowser',
preformatted : true,
content_css : '".$this->rootpath.$this->cfg['site.stylesheetpath'].$this->cfg['site.stylesheet']."',
// Theme options
theme_advanced_buttons1 : 'bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,bullist,numlist,outdent,indent,		styleselect,formatselect,fontselect,fontsizeselect',
// 
theme_advanced_buttons2 : 'translate, clqsave,undo,redo,|,link,unlink,anchor,image,cleanup,|,insertdate,inserttime,|,forecolor,backcolor,tablecontrols,|,removeformat,visualaid,emotions',
theme_advanced_buttons3 : 'cut,copy,paste,pastetext,pasteword,search,replace,|,blockquote,sub,sup,charmap,iespell,media,advhr,print,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,hr,pagebreak,preview,tinyautosave,code',
theme_advanced_buttons4 : '',
theme_advanced_statusbar : false,
// theme_advanced_statusbar_location : 'bottom',
theme_advanced_resizing : false, // Setting to true seems to ignore height instruction
body_class : 'clqpopupclass',
theme_advanced_toolbar_location : 'top',
theme_advanced_toolbar_align : 'center',
setup : function(ed) { ";
if($this->idiom != "" && $this->idiom != $lcd) {
$ftr .= "
ed.addButton('translate', {
	title : '".str('92:Translate')."', 'class': 'mb',image : '".$this->rootpath."images/icons/wand.png',
	onclick : function() {
		$('#".$this->schema['tblcnt']."').tinymce().save();
		var ti = '".$this->idiom."';
		var lcd = '".$this->lcd."';
		var notyid = noty({
			text: lcd + ' >> '+ ti, type: 'confirm', layout: 'topCenter',
			buttons: [
				{addClass: 'btn btn-primary', text: 'Ok', onClick: function(notyid) {
					textfrom = $('textarea[name=\"".$this->schema['tblcnt']."\"]').getValue();
					var url = '".$this->rootpath."admin/includes/get.php';
					var data = 'action=translate&idmfrom=' + lcd + '&idmto=' + ti + '&textfrom=' + escape(textfrom);
					$.ajax({
						type: 'POST', url: url, data: data,
						success: function(msg) {
							// $('textarea[name=\"".$this->schema['tblcnt']."\"]').setValue(msg);
							$('#".$this->schema['tblcnt']."').val(msg);
							notyid.close();
						}
					}); textfrom = null;	
				}},
				{addClass: 'btn btn-danger', text: '".str('135:Cancel')."', onClick: function(notyid) {
					notyid.close();
				}}
			]
		});
	}
}); ";
};
$ftr .=  "					
// Save button
ed.addButton('clqsave', {
title : 'Save',
'class': 'mb',
image : '".$this->rootpath."images/icons/disk.png',
onclick : function() {
// tinyMCE.activeEditor.getContent({format : 'raw'}) );
$('#".$this->schema['tblcnt']."').tinymce().save();
var urlstr = '".$this->rootpath."admin/includes/get.php';
var postdata = $('#contenteditor').serialize();
postdata += '&action=updatecontent&ptype=".$this->type."';
$.ajax({
type: 'POST', url: urlstr, data: postdata,
success: function(msg) {
if (msg != '') { 							
".clq::nMsg('update', str('102:Saved Successfully'), 'info').";											
} else {
".clq::nMsg('update', str('569:Update failed - database error'), 'error').";
}
}, 
failure: function() {
".clq::nMsg('update', str('570:Update failed - ajax error'), 'error').";	
}
})
}
});			
} // End setup
});
});
//-->
</script>";
return $ftr;    
}
function updateContent($p) { 
$array = array();
foreach($p as $fldname => $value) {
if(stristr($fldname, "clq_")) {$array[$fldname] = $value;};
}
$result = clqdb::updateRecord($p['tbl'], $array, $p['recid']);
return $result;
}
}