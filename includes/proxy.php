<?php
// Website url to open
$url = $_REQUEST['url'];
$webpage = file_get_contents($url);
echo $webpage;
