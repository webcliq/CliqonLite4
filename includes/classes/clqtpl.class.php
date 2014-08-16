<?php class clqtpl extends clq{public static$tpl_dir="view/";static$cache_dir="tmp/";static$base_url=null;public static$tpl_ext="tpl";static$path_replace=true;static$path_replace_list=array('a','link','img','script','input');static$black_list=array('\$this','clqtpl::','self::','_SESSION','_SERVER','_ENV','eval','exec','unlink','rmdir');static$check_template_update=true;static$php_enabled=true;static$debug=false;public$var=array();protected$tpl=array(),$cache=false,$cache_id=null;protected static$config_name_sum=array();const CACHE_EXPIRE_TIME=3600;function __construct(){global $rootpath;$this->rootpath=$rootpath;global $lcd;$this->lcd=$lcd;global $cfg;$this->cfg=$cfg;if($this->cfg['site.defaultdir']!=""){self::$tpl_dir=$this->cfg['site.defaultdir'];};if($this->cfg['site.defaultextn']!=""){self::$tpl_ext=$this->cfg['site.defaultextn'];};}function set($variable,$value=null){if(is_array($variable))$this->var+=$variable;else $this->var[$variable]=$value;}function publish($tpl_name,$return_string=false){try{$this->check_template($tpl_name);}catch(clqtpl_Exception $e){$output=$this->printDebug($e);die($output);}if(!$this->cache&&!$return_string){extract($this->var);include$this->tpl['compiled_filename'];unset($this->tpl);}else{ob_start();extract($this->var);include$this->tpl['compiled_filename'];$clqtpl_contents=ob_get_clean();if($this->cache)file_put_contents($this->tpl['cache_filename'],"<?php if(!class_exists('clqtpl')){exit;}?>".$clqtpl_contents);unset($this->tpl);if($return_string)return$clqtpl_contents;else echo $clqtpl_contents;}}function cache($tpl_name,$expire_time=self::CACHE_EXPIRE_TIME,$cache_id=null){$this->cache_id=$cache_id;if(!$this->check_template($tpl_name)&&file_exists($this->tpl['cache_filename'])&&(time()-filemtime($this->tpl['cache_filename'])<$expire_time))return substr(file_get_contents($this->tpl['cache_filename']),43);else{if(file_exists($this->tpl['cache_filename']))unlink($this->tpl['cache_filename']);$this->cache=true;}}static function configure($setting,$value=null){if(is_array($setting))foreach($setting as$key=>$value)self::configure($key,$value);elseif(property_exists(__CLASS__,$setting)){self::$$setting=$value;self::$config_name_sum[$setting]=$value;}}protected function check_template($tpl_name){if(!isset($this->tpl['checked'])){$tpl_basename=basename($tpl_name);$tpl_basedir=strpos($tpl_name,"/")?dirname($tpl_name).'/':null;$tpl_dir=self::$tpl_dir.$tpl_basedir;$this->tpl['tpl_filename']=$tpl_dir.$tpl_basename.'.'.self::$tpl_ext;$temp_compiled_filename=self::$cache_dir.$tpl_basename.".".md5($tpl_dir.serialize(self::$config_name_sum));$this->tpl['compiled_filename']=$temp_compiled_filename.'.rtpl.php';$this->tpl['cache_filename']=$temp_compiled_filename.'.s_'.$this->cache_id.'.rtpl.php';if(self::$check_template_update&&!file_exists($this->tpl['tpl_filename'])){$e=new clqtpl_NotFoundException('Template '.$tpl_basename.' not found! - '.$this->tpl['tpl_filename']);throw $e->setTemplateFile($this->tpl['tpl_filename']);}if(!file_exists($this->tpl['compiled_filename'])||(self::$check_template_update&&filemtime($this->tpl['compiled_filename'])<filemtime($this->tpl['tpl_filename']))){$this->compileFile($tpl_basename,$tpl_basedir,$this->tpl['tpl_filename'],self::$cache_dir,$this->tpl['compiled_filename']);return true;}$this->tpl['checked']=true;}}protected function xml_reSubstitution($capture){return"<?php echo '<?xml ".stripslashes($capture[1])." ?>'; ?>";}protected function compileFile($tpl_basename,$tpl_basedir,$tpl_filename,$cache_dir,$compiled_filename){$this->tpl['source']=$template_code=file_get_contents($tpl_filename);$template_code=preg_replace("/<\?xml(.*?)\?>/s","##XML\\1XML##",$template_code);if(!self::$php_enabled)$template_code=str_replace(array("<?","?>"),array("&lt;?","?&gt;"),$template_code);$template_code=preg_replace_callback("/##XML(.*?)XML##/s",array($this,'xml_reSubstitution'),$template_code);$template_compiled="<?php if(!class_exists('clqtpl')){exit;}?>".$this->compileTemplate($template_code,$tpl_basedir);$template_compiled=str_replace("?>\n","?>\n\n",$template_compiled);if(!is_dir($cache_dir))mkdir($cache_dir,0755,true);if(!is_writable($cache_dir))throw new clqtpl_Exception('Cache directory '.$cache_dir.'doesn\'t have write permission. Set write permission or set clqtpl_CHECK_TEMPLATE_UPDATE to false. More details on http://www.cliqon.com/Documentation/Documentation-for-PHP-developers/Configuration/');file_put_contents($compiled_filename,$template_compiled);}protected function compileTemplate($template_code,$tpl_basedir){$tag_regexp=array('loop'=>'(\{loop(?: name){0,1}="\${0,1}[^"]*"\})','loop_close'=>'(\{\/loop\})','if'=>'(\{if(?: condition){0,1}="[^"]*"\})','elseif'=>'(\{elseif(?: condition){0,1}="[^"]*"\})','else'=>'(\{else\})','if_close'=>'(\{\/if\})','function'=>'(\{function="[^"]*"\})','noparse'=>'(\{noparse\})','noparse_close'=>'(\{\/noparse\})','ignore'=>'(\{ignore\}|\{\*)','ignore_close'=>'(\{\/ignore\}|\*\})','include'=>'(\{include="[^"]*"(?: cache="[^"]*")?\})','template_info'=>'(\{\$template_info\})',);$tag_regexp="/".join("|",$tag_regexp)."/";$template_code=preg_split($tag_regexp,$template_code,-1,PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY);$template_code=$this->path_replace($template_code,$tpl_basedir);$compiled_code=$this->compileCode($template_code);return$compiled_code;}protected function compileCode($parsed_code){$compiled_code=$open_if=$comment_is_open=$ignore_is_open=null;$loop_level=0;while($html=array_shift($parsed_code)){if(!$comment_is_open&&(strpos($html,'{/ignore}')!==FALSE||strpos($html,'*}')!==FALSE))$ignore_is_open=false;elseif($ignore_is_open){}elseif(strpos($html,'{/noparse}')!==FALSE)$comment_is_open=false;elseif($comment_is_open)$compiled_code.=$html;elseif(strpos($html,'{ignore}')!==FALSE||strpos($html,'{*')!==FALSE)$ignore_is_open=true;elseif(strpos($html,'{noparse}')!==FALSE)$comment_is_open=true;elseif(preg_match('/\{include="([^"]*)"(?: cache="([^"]*)"){0,1}\}/',$html,$code)){$include_var=$this->var_replace($code[1],$left_delimiter=null,$right_delimiter=null,$php_left_delimiter='".',$php_right_delimiter='."',$loop_level);if(isset($code[2])){$compiled_code.='<?php $tpl = new '.get_class($this).';'.'if( $cache = $tpl->cache( $template = basename("'.$include_var.'") ) )'.'	echo $cache;'.'else{'.'	$tpl_dir_temp = self::$tpl_dir;'.'	$tpl->set( $this->var );'.(!$loop_level?null:'$tpl->set( "key", $key'.$loop_level.' ); $tpl->set( "value", $value'.$loop_level.' );').'	$tpl->publish( dirname("'.$include_var.'") . ( substr("'.$include_var.'",-1,1) != "/" ? "/" : "" ) . basename("'.$include_var.'") );'.'} ?>';}else{$compiled_code.='<?php $tpl = new '.get_class($this).';'.'$tpl_dir_temp = self::$tpl_dir;'.'$tpl->set( $this->var );'.(!$loop_level?null:'$tpl->set( "key", $key'.$loop_level.' ); $tpl->set( "value", $value'.$loop_level.' );').'$tpl->publish( dirname("'.$include_var.'") . ( substr("'.$include_var.'",-1,1) != "/" ? "/" : "" ) . basename("'.$include_var.'") );'.'?>';}}elseif(preg_match('/\{loop(?: name){0,1}="\${0,1}([^"]*)"\}/',$html,$code)){$loop_level++;$var=$this->var_replace('$'.$code[1],$tag_left_delimiter=null,$tag_right_delimiter=null,$php_left_delimiter=null,$php_right_delimiter=null,$loop_level-1);$counter="\$counter$loop_level";$key="\$key$loop_level";$value="\$value$loop_level";$compiled_code.="<?php $counter=-1; if( isset($var) && is_array($var) && sizeof($var) ) foreach( $var as $key => $value ){ $counter++; ?>";}elseif(strpos($html,'{/loop}')!==FALSE){$counter="\$counter$loop_level";$loop_level--;$compiled_code.="<?php } ?>";}elseif(preg_match('/\{if(?: condition){0,1}="([^"]*)"\}/',$html,$code)){$open_if++;$tag=$code[0];$condition=$code[1];$this->function_check($tag);$parsed_condition=$this->var_replace($condition,$tag_left_delimiter=null,$tag_right_delimiter=null,$php_left_delimiter=null,$php_right_delimiter=null,$loop_level);$compiled_code.="<?php if( $parsed_condition ){ ?>";}elseif(preg_match('/\{elseif(?: condition){0,1}="([^"]*)"\}/',$html,$code)){$tag=$code[0];$condition=$code[1];$parsed_condition=$this->var_replace($condition,$tag_left_delimiter=null,$tag_right_delimiter=null,$php_left_delimiter=null,$php_right_delimiter=null,$loop_level);$compiled_code.="<?php }elseif( $parsed_condition ){ ?>";}elseif(strpos($html,'{else}')!==FALSE){$compiled_code.='<?php }else{ ?>';}elseif(strpos($html,'{/if}')!==FALSE){$open_if--;$compiled_code.='<?php } ?>';}elseif(preg_match('/\{function="(\w*)(.*?)"\}/',$html,$code)){$tag=$code[0];$function=$code[1];$this->function_check($tag);if(empty($code[2]))$parsed_function=$function."()";else $parsed_function=$function.$this->var_replace($code[2],$tag_left_delimiter=null,$tag_right_delimiter=null,$php_left_delimiter=null,$php_right_delimiter=null,$loop_level);$compiled_code.="<?php echo $parsed_function; ?>";}elseif(strpos($html,'{$template_info}')!==FALSE){$tag='{$template_info}';$compiled_code.='<?php echo "<pre>"; print_r( $this->var ); echo "</pre>"; ?>';}else{$html=$this->var_replace($html,$left_delimiter='\{',$right_delimiter='\}',$php_left_delimiter='<?php ',$php_right_delimiter=';?>',$loop_level,$echo=true);$html=$this->const_replace($html,$left_delimiter='\{',$right_delimiter='\}',$php_left_delimiter='<?php ',$php_right_delimiter=';?>',$loop_level,$echo=true);$compiled_code.=$this->func_replace($html,$left_delimiter='\{',$right_delimiter='\}',$php_left_delimiter='<?php ',$php_right_delimiter=';?>',$loop_level,$echo=true);}}if($open_if>0){$e=new clqtpl_SyntaxException('Error! You need to close an {if} tag in '.$this->tpl['tpl_filename'].' template');throw $e->setTemplateFile($this->tpl['tpl_filename']);}return$compiled_code;}protected function reduce_path($path){$path=str_replace("://","@not_replace@",$path);$path=str_replace("//","/",$path);$path=str_replace("@not_replace@","://",$path);return preg_replace('/\w+\/\.\.\//','',$path);}protected function path_replace($html,$tpl_basedir){if(self::$path_replace){$tpl_dir=self::$base_url.self::$tpl_dir.$tpl_basedir;$path=$this->reduce_path($tpl_dir);$exp=$sub=array();if(in_array("img",self::$path_replace_list)){$exp=array('/<img(.*?)src=(?:")(http|https)\:\/\/([^"]+?)(?:")/i','/<img(.*?)src=(?:")([^"]+?)#(?:")/i','/<img(.*?)src="(.*?)"/','/<img(.*?)src=(?:\@)([^"]+?)(?:\@)/i');$sub=array('<img$1src=@$2://$3@','<img$1src=@$2@','<img$1src="'.$path.'$2"','<img$1src="$2"');}if(in_array("script",self::$path_replace_list)){$exp=array_merge($exp,array('/<script(.*?)src=(?:")(http|https)\:\/\/([^"]+?)(?:")/i','/<script(.*?)src=(?:")([^"]+?)#(?:")/i','/<script(.*?)src="(.*?)"/','/<script(.*?)src=(?:\@)([^"]+?)(?:\@)/i'));$sub=array_merge($sub,array('<script$1src=@$2://$3@','<script$1src=@$2@','<script$1src="'.$path.'$2"','<script$1src="$2"'));}if(in_array("link",self::$path_replace_list)){$exp=array_merge($exp,array('/<link(.*?)href=(?:")(http|https)\:\/\/([^"]+?)(?:")/i','/<link(.*?)href=(?:")([^"]+?)#(?:")/i','/<link(.*?)href="(.*?)"/','/<link(.*?)href=(?:\@)([^"]+?)(?:\@)/i'));$sub=array_merge($sub,array('<link$1href=@$2://$3@','<link$1href=@$2@','<link$1href="'.$path.'$2"','<link$1href="$2"'));}if(in_array("a",self::$path_replace_list)){$exp=array_merge($exp,array('/<a(.*?)href=(?:")(http\:\/\/|https\:\/\/|javascript:)([^"]+?)(?:")/i','/<a(.*?)href="(.*?)"/','/<a(.*?)href=(?:\@)([^"]+?)(?:\@)/i'));$sub=array_merge($sub,array('<a$1href=@$2$3@','<a$1href="'.self::$base_url.'$2"','<a$1href="$2"'));}if(in_array("input",self::$path_replace_list)){$exp=array_merge($exp,array('/<input(.*?)src=(?:")(http|https)\:\/\/([^"]+?)(?:")/i','/<input(.*?)src=(?:")([^"]+?)#(?:")/i','/<input(.*?)src="(.*?)"/','/<input(.*?)src=(?:\@)([^"]+?)(?:\@)/i'));$sub=array_merge($sub,array('<input$1src=@$2://$3@','<input$1src=@$2@','<input$1src="'.$path.'$2"','<input$1src="$2"'));}return preg_replace($exp,$sub,$html);}else return$html;}function const_replace($html,$tag_left_delimiter,$tag_right_delimiter,$php_left_delimiter=null,$php_right_delimiter=null,$loop_level=null,$echo=null){return preg_replace('/\{\#(\w+)\#{0,1}\}/',$php_left_delimiter.($echo?" echo ":null).'\\1'.$php_right_delimiter,$html);}function func_replace($html,$tag_left_delimiter,$tag_right_delimiter,$php_left_delimiter=null,$php_right_delimiter=null,$loop_level=null,$echo=null){preg_match_all('/'.'\{\#{0,1}(\"{0,1}.*?\"{0,1})(\|\w.*?)\#{0,1}\}'.'/',$html,$matches);for($i=0,$n=count($matches[0]);$i<$n;$i++){$tag=$matches[0][$i];$var=$matches[1][$i];$extra_var=$matches[2][$i];$this->function_check($tag);$extra_var=$this->var_replace($extra_var,null,null,null,null,$loop_level);$is_init_variable=preg_match("/^(\s*?)\=[^=](.*?)$/",$extra_var);$function_var=($extra_var and $extra_var[0]=='|')?substr($extra_var,1):null;$temp=preg_split("/\.|\[|\-\>/",$var);$var_name=$temp[0];$variable_path=substr($var,strlen($var_name));$variable_path=str_replace('[','["',$variable_path);$variable_path=str_replace(']','"]',$variable_path);$variable_path=preg_replace('/\.\$(\w+)/','["$\\1"]',$variable_path);$variable_path=preg_replace('/\.(\w+)/','["\\1"]',$variable_path);if($function_var){$function_var=str_replace("::","@double_dot@",$function_var);if($dot_position=strpos($function_var,":")){$function=substr($function_var,0,$dot_position);$params=substr($function_var,$dot_position+1);}else{$function=str_replace("@double_dot@","::",$function_var);$params=null;}$function=str_replace("@double_dot@","::",$function);$params=str_replace("@double_dot@","::",$params);}else $function=$params=null;$php_var=$var_name.$variable_path;if(isset($function)){if($php_var)$php_var=$php_left_delimiter.(!$is_init_variable&&$echo?'echo ':null).($params?"( $function( $php_var, $params ) )":"$function( $php_var )").$php_right_delimiter;else $php_var=$php_left_delimiter.(!$is_init_variable&&$echo?'echo ':null).($params?"( $function( $params ) )":"$function()").$php_right_delimiter;}else $php_var=$php_left_delimiter.(!$is_init_variable&&$echo?'echo ':null).$php_var.$extra_var.$php_right_delimiter;$html=str_replace($tag,$php_var,$html);}return$html;}function var_replace($html,$tag_left_delimiter,$tag_right_delimiter,$php_left_delimiter=null,$php_right_delimiter=null,$loop_level=null,$echo=null){if(preg_match_all('/'.$tag_left_delimiter.'\$(\w+(?:\.\${0,1}[A-Za-z0-9_]+)*(?:(?:\[\${0,1}[A-Za-z0-9_]+\])|(?:\-\>\${0,1}[A-Za-z0-9_]+))*)(.*?)'.$tag_right_delimiter.'/',$html,$matches)){for($parsed=array(),$i=0,$n=count($matches[0]);$i<$n;$i++)$parsed[$matches[0][$i]]=array('var'=>$matches[1][$i],'extra_var'=>$matches[2][$i]);foreach($parsed as$tag=>$array){$var=$array['var'];$extra_var=$array['extra_var'];$this->function_check($tag);$extra_var=$this->var_replace($extra_var,null,null,null,null,$loop_level);$is_init_variable=preg_match("/^[a-z_A-Z\.\[\](\-\>)]*=[^=]*$/",$extra_var);$function_var=($extra_var and $extra_var[0]=='|')?substr($extra_var,1):null;$temp=preg_split("/\.|\[|\-\>/",$var);$var_name=$temp[0];$variable_path=substr($var,strlen($var_name));$variable_path=str_replace('[','["',$variable_path);$variable_path=str_replace(']','"]',$variable_path);$variable_path=preg_replace('/\.(\${0,1}\w+)/','["\\1"]',$variable_path);if($is_init_variable)$extra_var="=\$this->var['{$var_name}']{$variable_path}".$extra_var;if($function_var){$function_var=str_replace("::","@double_dot@",$function_var);if($dot_position=strpos($function_var,":")){$function=substr($function_var,0,$dot_position);$params=substr($function_var,$dot_position+1);}else{$function=str_replace("@double_dot@","::",$function_var);$params=null;}$function=str_replace("@double_dot@","::",$function);$params=str_replace("@double_dot@","::",$params);}else $function=$params=null;if($loop_level){if($var_name=='key')$php_var='$key'.$loop_level;elseif($var_name=='value')$php_var='$value'.$loop_level.$variable_path;elseif($var_name=='counter')$php_var='$counter'.$loop_level;else $php_var='$'.$var_name.$variable_path;}else $php_var='$'.$var_name.$variable_path;if(isset($function))$php_var=$php_left_delimiter.(!$is_init_variable&&$echo?'echo ':null).($params?"( $function( $php_var, $params ) )":"$function( $php_var )").$php_right_delimiter;else $php_var=$php_left_delimiter.(!$is_init_variable&&$echo?'echo ':null).$php_var.$extra_var.$php_right_delimiter;$html=str_replace($tag,$php_var,$html);}}return$html;}protected function function_check($code){$preg='#(\W|\s)'.implode('(\W|\s)|(\W|\s)',self::$black_list).'(\W|\s)#';if(count(self::$black_list)&&preg_match($preg,$code,$match)){$line=0;$rows=explode("\n",$this->tpl['source']);while(!strpos($rows[$line],$code))$line++;$e=new clqtpl_SyntaxException('Unallowed syntax in '.$this->tpl['tpl_filename'].' template');throw $e->setTemplateFile($this->tpl['tpl_filename'])->setTag($code)->setTemplateLine($line);}}protected function printDebug(clqtpl_Exception $e){if(!self::$debug){throw $e;}$output=sprintf('<h2>Exception: %s</h2><h3>%s</h3><p>template: %s</p>',get_class($e),$e->getMessage(),$e->getTemplateFile());if($e instanceof clqtpl_SyntaxException){if(null!=$e->getTemplateLine()){$output.='<p>line: '.$e->getTemplateLine().'</p>';}if(null!=$e->getTag()){$output.='<p>in tag: '.htmlspecialchars($e->getTag()).'</p>';}if(null!=$e->getTemplateLine()&&null!=$e->getTag()){$rows=explode("\n",htmlspecialchars($this->tpl['source']));$rows[$e->getTemplateLine()]='<font color=red>'.$rows[$e->getTemplateLine()].'</font>';$output.='<h3>template code</h3>'.implode('<br />',$rows).'</pre>';}}$output.=sprintf('<h3>trace</h3><p>In %s on line %d</p><pre>%s</pre>',$e->getFile(),$e->getLine(),nl2br(htmlspecialchars($e->getTraceAsString())));return$output;}}class clqtpl_exception extends Exception{protected$templateFile='';public function getTemplateFile(){return$this->templateFile;}public function setTemplateFile($templateFile){$this->templateFile=(string)$templateFile;return$this;}}class clqtpl_NotFoundException extends clqtpl_Exception{}class clqtpl_SyntaxException extends clqtpl_Exception{protected$templateLine=null;protected$tag=null;public function getTemplateLine(){return$this->templateLine;}public function setTemplateLine($templateLine){$this->templateLine=(int)$templateLine;return$this;}public function getTag(){return$this->tag;}public function setTag($tag){$this->tag=(string)$tag;return$this;}}?>