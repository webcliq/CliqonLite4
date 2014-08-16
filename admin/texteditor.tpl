<!DOCTYPE html>
<html>
<head>
<title>RTE Editor</title>
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<script src="{$jspath}jquery.js"></script>
<script type="text/javascript" src="{$jspath}message.js"></script>
<script type="text/javascript" src="{$jspath}translate.js"></script>

{if="$texteditor==tinymce"}
	<script src="{$jspath}tinymce/jquery.tinymce.min.js"></script>
{/if}

{if="$texteditor==ckeditor"}
	<script src="{$jspath}ckeditor/ckeditor.js"></script>
	<script src="{$jspath}ckeditor/adapters/jquery.js"></script>
{/if}
<script>
{if="$texteditor==ckeditor"} {$ckedcfg} {/if}	
		
$(function() { 
	{if="$texteditor==tinymce"} $('#editarea').tinymce( {$tmcecfg} ); {/if}
	{if="$texteditor==ckeditor"} $('#editarea').ckeditor(); {/if}
});

function onsubmitform() {
	var formdata = $('#texteditor').serialize();
	var urlstr = '/includes/post.php';
	$.post(urlstr, formdata, function(msg){
		if(msg) {
			// Test Ok or Not
				var match = /Success/.test(msg);
			if (match == true) { 
				var n = noty({text: '{$success}', layout: 'topCenter', type: 'success'});
			} else {
				var n = noty({text: '{$error}', layout: 'topCenter', type: 'error'});
			}
		} else {
			var n = noty({text: '{$error}', layout: 'topCenter', type: 'error'});
		}	
	});
	return false;
};
</script>
</head>
<body>
	
	<form method="POST" action="#" name="texteditor" onsubmit="return onsubmitform()" id="texteditor">
	<input type="hidden" name="table" value="{$table}" />
	<input type="hidden" name="langcd" value="{$lcd}" />
	<input type="hidden" name="thislcd" value="{$thislcd}" />
	<input type="hidden" name="thisfld" value="clq_text" />
	<input type="hidden" name="action" value="updatevalue" />
	<input type="hidden" name="recid" value="{$recid}" />

	<textarea id="editarea" name="clq_text" style="width: 98%; height: 430px">{$content}</textarea>
</form>
</body>
</html>
