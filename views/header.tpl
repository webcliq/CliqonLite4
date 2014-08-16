<!doctype html>
<!--[if IE 8]>    <html class="no-js lt-ie9" lang="en"> <![endif]-->
<!-- Consider adding a manifest.appcache: h5bp.com/d/Offline -->
<!--[if gt IE 8]><!--> <html class="no-js" lang="{$lcd}"> <!--<![endif]-->
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta name="robots" content="all" />
	<meta name="revisit-after" content="5 days" />
	<meta name="distribution" content="global" />
	<meta name="rating" content="general" />
	<meta name="classification" content="{function="Q::cCfg('site.metaclassification')"}" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0"> 
	<meta name="Author" content="Webcliq" />
	<meta name="Content-Language" content="{$lcd}" />    
	<link rel="icon" href="{$siteicon}" type="image/x-icon">
	<meta name="reply-to" content="{function="Q::cCfg('site.email')"}" />	
    <title>{function="Q::cCfg('pagetitle')"}</title>
	<meta name="description" content="{function="Q::cCfg('site.metadescription')"}" />
	<meta name="keywords" content="{function="Q::cCfg('site.metakeywords')"}" />
    <link rel="stylesheet" href="css/{$stylesheet}">
    <script type="text/javascript" src="js/loader.js"></script>
	{loop="compressjsfiles"}
		<script type="text/javascript" src="../min/index.php?f={$value}"></script>
	{/loop}
    {$ajax}
	<!--[if lt IE 9]>
		<script type="text/javascript" src="{$viewpath}js/html5.js"></script>
	<![endif]-->
</head>
<body>




