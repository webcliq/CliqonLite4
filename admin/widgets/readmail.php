<?php
require_once("widget.php");

$params = array(
    
    // Uses Xpertmailer
    'popserver' => '',
    'username' => '',
    'password' => '',

);

?>
<div class="" style="width: 300px; height:330px; overflow: auto; padding:0px; margin: 0px; float:left;">
<?php echo $clqw->displayEmail($params) ?>
</div>