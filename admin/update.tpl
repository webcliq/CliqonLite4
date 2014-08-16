
<style>
	.btn {padding:2px; background-color: #003366; color: #fff; border-color: #003366; margin-right: 10px;}
	.btn:hover {background-color: #16A085; border-color: #16A085;}
	.inpt {padding: 6px; margin-right: 20px;}
    .tbl {display:table;}
    .tblr {display:table-row;}
    .tblc {display:table-cell;}
</style>     
<div id="results" class="pad" style=""></div>
<div class="clear clearfix">&nbsp;</div>


<script type="text/javascript">
<!--//
$(function() {

	var url = "{$rootpath}admin/includes/checkfiles.php";

	$('#results').load(url);

	$('.comparebutton').livequery('click', function(e) {
		var file = $(this).parent().attr('rel');
	    var urlstr = '{$rootpath}apps/diff/compare.php?pathfile=' + file; 
	    // alert(urlstr);
	    TINY.box.show({iframe: urlstr, top: 40, boxid:'framelesspad10', width:960, height:600, fixed:false, opacity:20});
	});

	$('.overwritebutton').livequery('click', function(e) {
		var file = $(this).parent().attr('rel');
		var url = '{$rootpath}admin/includes/getfile.php'; 
		var getdata = 'pathfile=' + file; 
		$.ajax({
            type: 'GET', url: url, data: getdata,
            success: function(msg) {
                var noty = noty(msg);
            }, failure: function() {
            	var noty = noty('Ajax Error');
            }
        });
	});


});
//-->
</script>
