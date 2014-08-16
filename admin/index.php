<?php
$rootpath = $_SERVER['DOCUMENT_ROOT']."/";
// $rootpath = "../";
if( !file_exists($rootpath."config/config.php") && strpos(($rootpath."config/config.php"), 'Web') == false ) {
   	header("Location: ../install/");
} else {
	header("Location: ../");
}
// end
