{include="admheader"}
<link rel="stylesheet" href="{$sitepath}includes/css/clq-fullcalendar.css">
<body style="background-color:#DDDDDD;">
	<div id="adminspace" style="width:800px; height: 620px; overflow-y: hidden; overflow-x: auto;"></div>
   	<script type="text/javascript" charset="utf-8">	
	loadScript("{$jspath}clqstartup.js", function() {
	loadScript("/includes/js/cliqon.js", function() {
		
		webix.ready(function(){ 

			// Load jQuery File Dynamically that are required for calendar
			$.scriptPath = '/includes/js/';

			// jQuery Scripts
			jQuery.require([
				'fullcalendar.js', 'i18n/all.js'
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