<?php function strip_php_comments(&$str){$str=str_replace("<?php",'<?php ',$str);$str=str_replace("\r",'',$str);$str=ereg_replace("/\*([^*]|[\r\n]|(\*+([^*/]|[\r\n])))*\*+/",'',$str);$str=ereg_replace("//[\x20-\x7E]*\n",'',$str);$str=ereg_replace("#[\x20-\x7E]*\n",'',$str);$str=ereg_replace("\t|\n",'',$str);}function cc_files($path,$destination){global $rootpath;global $sitepath;@mkdir($destination);$dir=opendir($path);while($file=readdir($dir)){if($file==''||$file=='.'||$file=='..')continue;if(is_dir($path.'/'.$file)){cc_files($path.'/'.$file,$destination.'/'.$file.'/');}else{$contents=file_get_contents($path.'/'.$file);if(strtolower(substr($file,strrpos($file,'.')+1))=='php'){strip_php_comments($contents);}file_put_contents($destination.'/'.$file,$contents);}}}?>