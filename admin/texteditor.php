<?php
// Texteditor.Php

$tmcecfg = array(

	'script_url' => '/includes/js/tinymce/tinymce.min.js',
	'content_css' => '/includes/css/pure.css', 
	'language' => $rq['langcd'],
	'nowrap' => true/false,
	'menubar' => 'tools table format view insert edit', // or false
	'statusbar' => false, 'fullscreen' => true,
	'resize' => 'both',
	'plugins' => array(
		'advlist', 'anchor', 'autolink', 'autoresize', 'autosave', 'charmap', 'code', 'example',
		'image', 'link', 'lists', 'media', 'paste', 'preview', 'print', 'save', 'textcolor', 'fullscreen'
		// 'contextmenu', 'importcss', 'searchreplace', 'spellchecker', 'table', 'wordcount', 'improvedcode'
	),
	'toolbar' => array(
		'undo redo | bold italic | alignleft aligncenter alignright | bullist numlist outdent indent | link image | forecolor backcolor | preview save'
	),
	// 'contextmenu' => array(link image inserttable | cell row column deletetable)
	'save_enablewhendirty' => false,

	'height' => '430px'

);

/*
	menu : { // this is the complete default configuration
		file   : {title : 'File'  , items : 'newdocument'},
		edit   : {title : 'Edit'  , items : 'undo redo | cut copy paste pastetext | selectall'},
		insert : {title : 'Insert', items : 'link media | template hr'},
		view   : {title : 'View'  , items : 'visualaid'},
		format : {title : 'Format', items : 'bold italic underline strikethrough superscript subscript | formats | removeformat'},
		table  : {title : 'Table' , items : 'inserttable tableprops deletetable | cell row column'},
		tools  : {title : 'Tools' , items : 'spellchecker code'}
	}

*/
$tpl->set('tmcecfg', str_replace('\\', '',json_encode($tmcecfg)));	

$ckedcfg = "
CKEDITOR.disableAutoInline = true;
CKEDITOR.config.height = '430px';
CKEDITOR.config.allowedContent = true;
CKEDITOR.config.extraAllowedContent = 'p(*)[*]{*};div[id]';
// CKEDITOR.config.removePlugins = '';
CKEDITOR.config.toolbarCanCollapse = true;
CKEDITOR.config.extraPlugins = 'translatebutton';
";
$tpl->set('ckedcfg', $ckedcfg);	

$row = R::getRow("SELECT clq_text, clq_langcd FROM ".$rq['table']." WHERE id = ?", array($rq['recid']));

$tpl->set('content', $row['clq_text']);
$tpl->set('thislcd', $row['clq_langcd']);

$tpl->set('table', $rq['table']);
$tpl->set('recid', $rq['recid']);
$tpl->set('texteditor', $cfg['site.texteditor']);
$tpl->set('lcd', $rq['langcd']);
