<!DOCTYPE html>
<html>
<head>
<title>Codeditor</title>
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<script src="{$jspath}jquery.js" charset="utf-8"></script>
<script type="text/javascript" src="{$jspath}ace/ace.js" charset="utf-8"></script>
<script type="text/javascript" src="{$jspath}message.js" charset="utf-8"></script>
</head>
<body>
<!-- Title and Record ID -->
<p>
<button id="getProg" class="btn" style="float:right; margin-right: 8px; margin-bottom: 10px;">Save</button>
</p>
<br />
<input type="hidden" name="table" value="{$table}" />
<input type="hidden" name="langcd" value="{$lcd}" />
<input type="hidden" name="recid" value="{$recid}" />
<textarea id="editor"></textarea>
</form>
</body>
</html>
<script>
var editor = ace.edit("editor");
editor.setTheme("ace/theme/monokai");
editor.getSession().setMode("ace/mode/javascript");

// get json
document.getElementById('getProg').onclick = function () {
    
    var`prog = document.getElementById('editor').value; // Got from Div
    var table = $('input[name="table"]').val();
	var langcd = $('input[name="langcd"]').val();
    var recid = $('input[name="recid"]').val();
    var val = $.base64Encode(JSON.stringify(prog, null, 2));
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
	}, 'json')    

};
</script>
