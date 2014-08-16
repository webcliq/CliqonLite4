{include="admheader"}
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

			]);
					
			webix.ui({
				type:"space", container:"adminspace", rows:[			
					{type:"space", padding: 0, cols:[
						{type:"line", padding: 0, rows: {$admmenu} },				
						{type:"line", padding: 0, rows: {$datalist} }
					]}	
				]
			}).show();	

			// If Doc Ready used, have to be inside Doc Ready!
			function getList(lstr) {
				var l = 0; var strs = [];
				$.each(lstr, function(index, value) {
					strs[l] = {'key':l, 'value':value}; l++;
				})
				return strs;
			};

		    $$("list1_input").attachEvent("onTimedKeyPress",function(){
		        var value = this.getValue().toLowerCase();
		        $$("list1").filter(function(obj){
		            return obj.value.toLowerCase().indexOf(value)==0;
		        })
		    });
		    $$("list2_input").attachEvent("onTimedKeyPress",function(){
		        var value = this.getValue().toLowerCase();
		        $$("list2").filter(function(obj){
		            return obj.value.toLowerCase().indexOf(value)==0;
		        })
		    });
	
			$(this).ajaxStart(function() {
				$('#loading').removeClass('loadinghide');
				$('#loading').show();
			}).ajaxStop(function() {
				$('#loading').fadeOut(500);
				$('#loading').addClass('loadinghide');
			});
							
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
