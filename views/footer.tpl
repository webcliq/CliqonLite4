<script type="text/javascript">
	loadScript("{$jspath}clqstartup.js", function() {
	var w = 440; var ww = document.body.clientWidth; var wl = ww - w; wl = (wl/2);
	var t = 640; var tw = document.body.clientWidth; var tl = tw - t; tl = (tl/2);
	var jlcd = '{$lcd}';

	$(function() {

		// Load jQuery File Dynamically that are required for cms_lite_insert.php
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
		
		{$commonscripts}
		{$pagescripts}

	});

});
</script>
</body>
</html>
<div id="popup"></div>
<div id="loading" class="loadinghide"></div>
