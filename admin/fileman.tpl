{include="admheader"}
<link rel="stylesheet" href="/includes/css/clq-elfinder.css">
<body style="background-color:#DDDDDD;">
	<div id="adminspace" style="width:800px; height: 620px; overflow-y: hidden; overflow-x: auto;"></div>
   	<script type="text/javascript" charset="utf-8">	
	loadScript("{$jspath}clqstartup.js", function() {
	loadScript("/includes/js/cliqon.js", function() {
		
		webix.ready(function(){ 

			// Load jQuery File Dynamically that are required for cms_lite_insert.php
			$.scriptPath = '/includes/js/';

			// jQuery Scripts
			jQuery.require([
				'browser.js', 'elfinder.js', 'i18n/elfinder.{$lcd}.js'
			]);
					
			webix.ui({
				type:"space", container:"adminspace", rows:[			
					{type:"space", padding: 0, cols:[
						{type:"line", padding: 0, rows: {$admmenu} },				
						{type:"line", padding: 0, rows: {$admcontent} }
					]}	
				]
			}).show();	
	
			$(this).ajaxStart(function() {
				$('#loading').removeClass('loadinghide');
				$('#loading').show();
			}).ajaxStop(function() {
				$('#loading').fadeOut(500);
				$('#loading').addClass('loadinghide');
			});
			
			var eloptions = {
				'url' : '/includes/connector.php',
				'lang':'{$lcd}',
				// 'width':'600', 
				'height':'580',
				'defaultView':'list',
				'commands' : [
				    'open', 'reload', 'home', 'up', 'back', 'forward', 'getfile', 'quicklook', 
				    'download', 'rm', 'rename', 'mkfile', 'upload', 'extract', 'info', 'view', 'help',
				    'resize', 'sort'
				],
				uiOptions : {
					/*
					// toolbar configuration
					toolbar : [
						['back', 'forward'],
						// ['reload'],
						// ['home', 'up'],
						['mkdir', 'mkfile', 'upload'],
						['open', 'download', 'getfile'],
						['info'],
						['quicklook'],
						['copy', 'cut', 'paste'],
						['rm'],
						['duplicate', 'rename', 'edit', 'resize'],
						['extract', 'archive'],
						['search'],
						['view'],
						['help']
					],
					*/

					// directories tree options
					tree : {
						// expand current root on init
						openRootOnLoad : true,
						// auto load current dir parents
						syncTree : true
					},

					// navbar options
					navbar : {
						minWidth : 100,
						maxWidth : 150
					},

					// current working directory options
					cwd : {
						// display parent directory in listing as ".."
						oldSchool : false
					}
				}
			};
			
			var elf = $('#filemanager').elfinder(eloptions).elfinder('instance');  
							
			// Datepicker
			$('#datepicker').datepicker({
				inline: true
			});

			if( $('#popupframe').exists === true) {
				if( $('#admmenu').notexists === true) {
					var urlstr = "?page=admin&admin=dashboard&langcd=" + store.get('clq_langcd') + "&userid=" + store.get('clq_username');
					fLoad(urlstr);
				}
			};	  
			{$adminscripts}		
		});
		console.log('clqstartup.js, cliqon.js loaded');
	})});
	</script>
</body>
</html>
{include="admfooter"}
