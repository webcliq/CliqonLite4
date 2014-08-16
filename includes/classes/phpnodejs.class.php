<?php class phpnodejs extends clqdb{private$NodePath;private$PHPNodeJSWrapper;private$debug=false;private$timeZone;public$rootpath="";public$lcd="";public$thisclass="clqphpnodejs extends clqdb";public$help,$iconpath,$cologopath,$imgpath,$adminpath,$form,$title,$style;function __construct($debug=false,$timeZone='Europe/Madrid'){global $rootpath;$this->rootpath=$rootpath;global $cfg;$this->cfg=$cfg;global $lcd;$this->lcd=$lcd;$this->iconpath=$this->rootpath."admin/icons/";$this->cologopath=$this->rootpath."site/cologos/";$this->imgpath=$this->rootpath."site/images/";$this->adminpath=$this->rootpath."admin/";$this->debug=$debug;$this->timeZone=$timeZone;date_default_timezone_set($this->timeZone);if($this->debug){self::DebugMsg('Searching for path of executable Node.JS ("node")...');}$this->NodePath=trim(shell_exec('which node'));if(!file_exists($this->NodePath)){self::DebugMsg('Node.JS is not installed on server. Please fix that.');die();}if($this->debug){self::DebugMsg('Path of Node.JS: '.$this->NodePath);self::DebugMsg('Current Execution Path: '.dirname(__FILE__).'/');self::DebugMsg();}}public function run($javascript_code,$function_name='',$args=array(),$jQuery=false){$tmpFile=tempnam(dirname(__FILE__),'PHPNodeJSWrapper');$this->PHPNodeJSWrapper=$tmpFile.'.js';$this->SetJSWrapper($jQuery);for($i=0;$i<count($args);$i++){$args[$i]=escapeshellarg($args[$i]);}if($this->debug){self::DebugMsg('Running Javascript with parameters:');self::DebugMsg('Javascript Code: '.$javascript_code);if($function_name!=''){self::DebugMsg('Calling JavaScript Function with Parameters: '.$function_name.'('.implode(', ',$args).')');}self::DebugMsg('Enabled special libs: jQuery('.($jQuery?'TRUE':'FALSE').')');self::DebugMsg();}$command='cd '.dirname(__FILE__).'/ && '.$this->NodePath.' '.$this->PHPNodeJSWrapper.' ';$command.=escapeshellarg($javascript_code);if($function_name!=''){$command.=' '.escapeshellarg($function_name).' '.escapeshellarg('['.implode(', ',$args).']');}if($this->debug){self::DebugMsg('Executing shell command:');self::DebugMsg($command);}$result=shell_exec($command);if($this->debug){self::DebugMsg('Result:');self::DebugMsg($result);self::DebugMsg();}$this->CleanJSWrapper();return$result;}private function SetJSWrapper($jQuery=false){if($this->debug){self::DebugMsg('Setting JS Wrapper for executing custom JavaScript function...');self::DebugMsg('JS Wrapper Path: '.$this->PHPNodeJSWrapper);}ob_start();?>
        <script type="text/javascript">
        <?php
 if($jQuery){?>
                try {
                    require.resolve("jquery");
                } catch (e) {
                    console.error("jQuery is not found. Try: npm install jquery");
                    process.exit(e.code);
                }
                var jQuery = require("jquery");
            <?php
}?>

            function PHPNodeJSWrapper(func, func_name, args) {
                eval(func);
                if (func_name && func_name !== '') {
                    args_string = "[";
                    for (var i = 0; i < args.length; i++) {
                        args_string += "'" + args[i] + "'";
                        if (i !== (args.length - 1)) {
                            args_string += ",";
                        }
                    }
                    args_string += "]";
                    var call = func_name + ".apply(this, " + args_string + ");";
                    return eval(call);
                }
            }
            var function_code = process.argv[2];
            var function_name = '';
            if (process.argv[3]) {
                function_name = process.argv[3];
            }
            var arguments = [];
            if (process.argv[4]) {
                arguments = eval(process.argv[4]);
            }
            if (function_name !== '') {
                console.log(PHPNodeJSWrapper(function_code, function_name, arguments));
            } else {
                PHPNodeJSWrapper(function_code);
            }
        </script>
        <?php
 $data=strtr(ob_get_clean(),array('<script type="text/javascript">'=>'','</script>'=>''));file_put_contents($this->PHPNodeJSWrapper,$data);if($this->debug){self::DebugMsg('JS Wrapper prepared successfully.');self::DebugMsg();}}private function CleanJSWrapper(){if($this->debug){self::DebugMsg('Cleanup JS Wrapper...');}unlink($this->PHPNodeJSWrapper);unlink(mb_substr($this->PHPNodeJSWrapper,0,-3,'UTF-8'));$this->PHPNodeJSWrapper=null;if($this->debug){self::DebugMsg('Cleanup JS Wrapper is finished.');self::DebugMsg();}}private static function DebugMsg($msg=''){if($msg==''){echo PHP_SAPI=='cli'?'':'<br />',"\n";}else{echo date('Y-m-d H:i:s'),' :: ',$msg,PHP_SAPI=='cli'?'':'<br />',"\n";}}}?>