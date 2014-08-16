{* Adminheader is called by each Admin Page to add an appropriate HTML5 Document Header to each page that appewars in the iFrame. *}
<!doctype html>
<!--[if lt IE 7]> <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js lt-ie9 lt-ie8" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js lt-ie9" lang="en"> <![endif]-->
<!-- Consider adding a manifest.appcache: h5bp.com/d/Offline -->
<!--[if gt IE 8]><!--> <html class="no-js" lang="{$lcd}"> <!--<![endif]-->
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0"> 
	<meta name="Content-Language" content="{$lcd}" />    
    <link rel="stylesheet" href="{$viewpath}theme/{$stylesheet}">
	<!-- Scripts.tpl -->
	<script type="text/javascript" src="/includes/js/loader.js"></script>
	<script type="text/javascript" src="/includes/js/i18n/cliqon.{$lcd}.js"></script>
</head>






