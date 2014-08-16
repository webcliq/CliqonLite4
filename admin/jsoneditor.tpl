<!DOCTYPE html>
<html>
<head>
<title>JSON Editor</title>
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<link rel="stylesheet" type="text/css" href="{$jspath}jsonedit/jsoneditor-min.css">
<script src="{$jspath}jquery.js"></script>
<script type="text/javascript" src="{$jspath}jsoneditor.js"></script>
<script type="text/javascript" src="{$jspath}ace/ace.js"></script>
<script type="text/javascript" src="{$jspath}ace/theme-jsoneditor.js"></script>
<script type="text/javascript" src="{$jspath}message.js"></script>
</head>
<body>
<!-- Title and Record ID -->
<p>
<button id="getJSON" class="btn" style="float:right; margin-right: 8px; margin-bottom: 10px;">Save</button>
</p>
<br />
<div id="jsoneditor" style="width: 730px; height: 530px; clear:both;"></div>
	
<input type="hidden" name="table" value="{$table}" />
<input type="hidden" name="langcd" value="{$lcd}" />
<input type="hidden" name="recid" value="{$recid}" />
</form>
</body>
</html>
<script>
$(function() { 

});

var container = document.getElementById('jsoneditor');

var options = {
	mode: 'tree',
	modes: ['code', 'form', 'text', 'tree', 'view'], // allowed modes
	error: function (err) {
	  	var n = noty({'text': '{$error}: ' + err.toString(), layout: 'topCenter', type: 'error'});
	}
};

var editor = new jsoneditor.JSONEditor(container, options, {$json});

// get json
document.getElementById('getJSON').onclick = function () {
    
    var json = editor.get();
    var table = $('input[name="table"]').val();
	var langcd = $('input[name="langcd"]').val();
    var recid = $('input[name="recid"]').val();
    var val = $.base64Encode(JSON.stringify(json, null, 2));
	var formdata = 'action=updatevalue&langcd=' + langcd + '&table=' + table + '&recid=' + recid + '&thisfld=clq_extra&clq_extra=' + val;

	var urlstr = '/includes/post.php';
	$.post(urlstr, formdata, function(msg){
		// Test Ok or Not
		var match = /Success/.test(msg);
		if (match == true) { 
			var n = noty({'text': '{$success}', layout: 'topCenter', type: 'success'});
		} else {
			var n = noty({'text': '{$error}', layout: 'topCenter', type: 'error'});
		}	
	})    
};
</script>
