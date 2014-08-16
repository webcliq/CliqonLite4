<?php
require_once("widget.php");
$params = array();
?>
<script type="text/javascript">
$(document).ready(function(){
	$("button").click(function (event) {
	  var field = $('#field').val();
	  if (event.target.value == "C"){
			$('#field').val('');
		}
		else if (event.target.value == "="){
			$('#field').val(eval(field));
		}	
		else{	
			$('#field').val(function(index, val) {
        		return val + event.target.value;
    		});
    	}
	});
});
</script>

<style type="text/css">
.ui-grid-a .ui-block-a { width: 75% }
.ui-grid-a .ui-block-b { width: 25%; }
</style>
<div class="" style="width: 300px; height:300px; overflow: auto;">
	<div data-role="page" data-theme="c" id="page-home" data-fullscreen="true">
	<div data-role="content" id="event-content">
	
		<fieldset class="ui-grid-a">
			<div class="ui-block-a"><input type="text" name="field" id="field"></div>
			<div class="ui-block-b"><button value='C' id="calc" data-theme="a" data-mini="true" >C</button></div>
		</fieldset>

		<fieldset class="ui-grid-c">
			<div class="ui-block-a"><button value=9 id="calc" data-theme="c">9</button></div>
			<div class="ui-block-b"><button value=8 id="calc" data-theme="c">8</button></div>
			<div class="ui-block-c"><button value=7 id="calc" data-theme="c">7</button></div>
			<div class="ui-block-d"><button value='+' id="calc" data-theme="b">+</button></div>	   
			<div class="ui-block-a"><button value=4 id="calc" data-theme="c">4</button></div>
			<div class="ui-block-b"><button value=5 id="calc" data-theme="c">5</button></div>
			<div class="ui-block-c"><button value=6 id="calc" data-theme="c">6</button></div>
			<div class="ui-block-d"><button value='-' id="calc" data-theme="b">-</button></div>	   
			<div class="ui-block-a"><button value=1 id="calc" data-theme="c">1</button></div>
			<div class="ui-block-b"><button value=2 id="calc" data-theme="c">2</button></div>
			<div class="ui-block-c"><button value=3 id="calc" data-theme="c">3</button></div>
			<div class="ui-block-d"><button value='*' id="calc" data-theme="b">*</button></div>	   
			<div class="ui-block-a"><button value=0 id="calc" data-theme="c">0</button></div>
			<div class="ui-block-b"><button value='.' id="calc" data-theme="c">.</button></div>
			<div class="ui-block-c"><button value='=' id="calc" data-theme="b">=</button></div>
			<div class="ui-block-d"><button value='/' id="calc" data-theme="b">/</button></div>	   
		</fieldset>
	</div><!-- /content --> 
	</div>
</div>