<?php
$morejs = "

var cologorenderer = function (row, datafield, value) {
    
	var src = explode('|', value);
    return '<img style=\"\" src=\"".$this->cologopath."' + src[0] + '\" alt=\"' + src[0] + '\" title=\"' + src[2] + '\" class=\" gridlogo imgLiquid' + src[1] + '\"  />';
}

";
