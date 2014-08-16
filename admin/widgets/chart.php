<?php
require_once("widget.php");
require_once("../config/chart.cfg");
?>
<div class="" style="width: 300px; height:340px; overflow: auto; padding:0px; margin: 0px; float:left;">
<?php echo $clqw->displayChart($chart[$_REQUEST['chartname']]) ?>
</div>