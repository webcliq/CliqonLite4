<!doctype html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0"> 
	<meta name="Content-Language" content="{$lcd}" />    
	<link rel="stylesheet" href="css/{$stylesheet}">
    <script type="text/javascript" src="../includes/js/loader.js"></script>
	{loop="compressjsfiles"}
		<script type="text/javascript" src="../min/index.php?f={$value}"></script>
	{/loop}
    {$ajax}
	<!--[if lt IE 9]>
		<script type="text/javascript" src="../includes/js/html5.js"></script>
	<![endif]-->
	
</head>
<body>
<h1>Content</h1>
<script type="text/javascript">
	
	loadScript("{$jspath}clqstartup.js", function() {
		
		var w = 440; var ww = document.body.clientWidth; var wl = ww - w; wl = (wl/2);
		var jlcd = '{$lcd}';

		$(function() {

			// Common path
			$.scriptPath = '../includes/js/';

			// jQuery Scripts
			jQuery.require([
				{loop="$jsfiles"}
				'{$value}.js',
				{/loop}
			]);

			$(this).ajaxStart(function() {
				$('#loading').removeClass('loadinghide');
				$('#loading').show();
			}).ajaxStop(function() {
				$('#loading').fadeOut(500);
				$('#loading').addClass('loadinghide');
			});

			$('#openadmin').on('click', function(e) { doLogin(e) });

			{$commonscripts}

			{$pagescripts}

		});

	});
	
</script>
</body>
</html>
<div id="popup"></div>
<div id="loading" class="loadinghide"></div>
