<?php class clqwidget extends clq{public$rootpath="../";public$thisclass="clqwidget extends clq";public$lcd="",$js="",$schema=array(),$test=false,$yr="";public function __construct(){global $rootpath;$this->rootpath=$rootpath;global $lcd;$this->lcd=$lcd;$this->imgdir=$this->rootpath."view/images/";}public function test(){$str="Test: ".$this->thisclass;return$str;}function setVar($var,$val){$this->$var=$val;}function getVar($var){return$this->$var;}function displayTable($params){$this->schema=$params;$ftpath=$this->rootpath."js/footable/";$ft="
            <link href='".$ftpath."css/footable.css' rel='stylesheet' type='text/css' />
            <script src='".$this->rootpath."js/jquery.js' type='text/javascript'></script>
            <script src='".$ftpath."js/footable.js' type='text/javascript'></script>
            <style>
            .txt-right td {text-align: right;}
            .footable tbody tr.zebra {
              background:#fee;
            }
            .filterbox input {
                padding: 3px; font-size: 11px;
            }
            .filterlabel {
                font-size: 11px; font-family: sans-serif; 
            }
            </style>
        ";if($this->schema['filter']){$ft.="<div style='margin-bottom: 5px;'  class='filterlabel'>".str('19:Search').": <input id='filter' type='text' class='filterbox' /></div>";};$ft.="
            <table class='footable' data-filter='#filter' data-page-size=".$this->schema['page']." style='width:290px; float: left;' >
                <thead>
                    <tr>";foreach($this->schema['flds']as$f=>$ln){$x=explode("|",$ln);$ft.="<th data-type='".$x[0]."'";if($x[3]!=''){$ft.=" data-class='".$x[3]."'";}if($x[4]!=''){$ft.=" data-hide='".$x[4]."'";}if($x[5]!=''){$ft.=" data-name='".str($x[5])."'";}if($x[6]!=''){$ft.=" data-sort-initial='".$x[6]."'";}if($x[7]!=''){$ft.=" data-sort-ignore='".$x[7]."'";}$ft.=" >".str($x[1])."</th>";}$ft.="</tr>
                </thead>
        ";$ft.="<tbody>";$wherestr="WHERE ";foreach($this->schema['where']as$n=>$opts){$w=explode("|",$opts);$wherestr.=$w[0].' '.$w[1].' '.$w[2].' AND ';};$wherestr=trim($wherestr,'AND ');$wherestr=str_replace("{lcd}",$this->lcd,$wherestr);$wherestr=str_replace("´","'",$wherestr);$sql="SELECT * FROM ".$this->schema['tbl'].' '.$wherestr." ORDER BY ".$this->schema['orderby'];$rs=R::getAll($sql);for($r=0;$r<count($rs);$r++){$ft.="<tr>";foreach($this->schema['flds']as$fld=>$ln){$x=explode("|",$ln);if(stristr($fld,'clq_extra')!=false){$val=self::getXtraVal($rs[$r]['clq_extra'],$x[2]);}elseif($x[8]!=''){$dv=" data-value='".$rs[$r][$fld]."'";$val="";}else{$val=$rs[$r][$fld];}$ft.="<td valign='top' ".$dv." class='".$x[3]."' >".$val."</td>";}$ft.="</tr>";}$ft.="</tbody>";if($this->test==true){$sqlcheck="
                <tr>
                    <td colspan=".count($this->schema['flds']).">".$sql."</td>
                </tr>
                ";}else{$sqlcheck="";}if($this->schema['page']!=0){$ft.="
                    <tfoot class=1footable-pagination'>
                        <tr>
                            <td colspan=".count($this->schema['flds'])."><ul id='pagination' class='footable-nav' /></td>
                        </tr>
                        ".$sqlcheck."
                    </tfoot>
                </table> 
            ";}$ft.="       
            <script type='text/javascript'>
            <!--//
            $(function() { 
                $('.footable').footable().find('> tbody > tr:not(.footable-row-detail):nth-child(even)').addClass('zebra');
            });
            //-->
            </script>
        ";return$ft;}function displayStock($params){$this->schema=$params;$code=$_REQUEST['stockcode'];$ch="
            <script src='".$this->rootpath."js/jquery.js' type='text/javascript'></script>
            <script src='".$this->rootpath."js/chart.js' type='text/javascript'></script>
            <style>
                .titlelabel {margin-left:40px; font-size: 11px; font-family: sans-serif; color: #003366;}
            </style>
        ";$title=R::$f->begin()->select($this->schema['plot']['title'])->from($this->schema['tbl'])->where('clq_type = ? AND '.$this->schema['plot']['code'].' = ? ')->put($this->schema['tbltype'])->put($code)->get('cell');$ch.="<div class='titlelabel'>".$code.": ".$title."</div>";$wherestr="WHERE ";foreach($this->schema['where']as$n=>$opts){$w=explode("|",$opts);$wherestr.=$w[0].' '.$w[1].' '.$w[2].' AND ';};$wherestr=trim($wherestr,'AND ');$wherestr=str_replace("{lcd}",$this->lcd,$wherestr);$wherestr=str_replace("{code}",$code,$wherestr);$wherestr=str_replace("´","'",$wherestr);$sql="SELECT * FROM ".$this->schema['tbl'].' '.$wherestr." ORDER BY ".$this->schema['orderby'];$rs=R::getAll($sql);$steps=count($rs)-1;$l=explode("|",$this->schema['plot']['labels']);$lbls="";$v=explode("|",$this->schema['plot']['values']);$vals="";for($r=0;$r<$steps;$r++){if(stristr($l[0],'clq_extra')!=false){if($l[2]=='date'){$td=self::getXtraVal($rs[$r]['clq_extra'],$l[1]);$lx=self::summariseDates($td,$ld);$ld=$td;}else{$lx=self::getXtraVal($rs[$r]['clq_extra'],$l[1]);}$lbls.="'".$lx."',";}else{$lbls.="'".$rs[$r][$l[0]]."',";}if(stristr($v[0],'clq_extra')!=false){$vals.=self::getXtraVal($rs[$r]['clq_extra'],$v[1]).",";}else{$vals.=$rs[$r][$v[0]].",";}}$lbls=trim($lbls,",");$vals=trim($vals,",");$ch.="<canvas id='chart' width=320 height=280 ></canvas>";$ch.="       
            <script type='text/javascript'>
            <!--//
            $(function() { 
                
                //Get context with jQuery - using jQuery's .get() method.
                var ctx = $('#chart').get(0).getContext('2d');
                
                //This will get the first returned node in the jQuery collection.
                var myNewChart = new Chart(ctx); 

                // Data
                var data = {
                    labels : [".$lbls."],
                    datasets : [
                        {
                            fillColor : '#DBEAF9',
                            strokeColor : '#003366',
                            pointColor : '#003366',
                            pointStrokeColor : '#fff',
                            data : [".$vals."]
                        }
                    ]
                };

                // Options (all the defaults shown below)
                var options = {

                    //Boolean - Whether to show labels on the scale 
                    scaleShowLabels : true,
                    
                    //Interpolated JS string - can access value
                    scaleLabel : '<%=value%>',
                    
                    //String - Scale label font declaration for the scale label
                    scaleFontFamily : 'Arial',
                    
                    //Number - Scale label font size in pixels  
                    scaleFontSize : 8,
                    
                    //String - Scale label font weight style    
                    scaleFontStyle : 'normal',
                    
                    //String - Scale label font colour  
                    scaleFontColor : '#003366',    
                    
                    ///Boolean - Whether grid lines are shown across the chart
                    scaleShowGridLines : false,
                                        
                    //Boolean - Whether the line is curved between points
                    bezierCurve : true,
                    
                    //Boolean - Whether to show a dot for each point
                    pointDot : false,
                                        
                    //Boolean - Whether to show a stroke for datasets
                    datasetStroke : false,
                    
                    //Number - Pixel width of dataset stroke
                    datasetStrokeWidth : 2,
                    
                    //Boolean - Whether to fill the dataset with a colour
                    datasetFill : true,
                    
                    //Boolean - Whether to animate the chart
                    animation : true,

                    //Number - Number of animation steps
                    // animationSteps : 10,
                    
                    //String - Animation easing effect
                    animationEasing : 'easeOutQuart',              
                }

                new Chart(ctx).Line(data,options);    

            });
            //-->
            </script>
        ";return$ch;}function displayChart($params){$this->schema=$params;$ch="
            <script src='".$this->rootpath."js/jquery.js' type='text/javascript'></script>
            <script src='".$this->rootpath."js/chart.js' type='text/javascript'></script>
            <style>
                .titlelabel {margin-left:40px; font-size: 11px; font-family: sans-serif; color: #003366;}
            </style>
        ";if($this->schema['title']){$ch.="<div class='titlelabel'>".str($this->schema['title'])."</div>";};if($this->schema['charttype']=="Pie"){$ch.="<div class='titlelabel'>";foreach($this->schema['plot']as$segment){$ch.=$segment['label'].": <span style='width: 30px; height: 20px; border: 1px solid #003366; background: ".$segment['color']."; margin-right: 5px;'>&nbsp;&nbsp;&nbsp;&nbsp;</span>";}$ch.="</div>";};if($this->schema['plottype']=='db'){$wherestr="WHERE ";foreach($this->schema['db']['where']as$n=>$opts){$w=explode("|",$opts);$wherestr.=$w[0].' '.$w[1].' '.$w[2].' AND ';};$wherestr=trim($wherestr,'AND ');$wherestr=str_replace("{lcd}",$this->lcd,$wherestr);$wherestr=str_replace("{code}",$code,$wherestr);$wherestr=str_replace("´","'",$wherestr);$sql="SELECT * FROM ".$this->schema['db']['tbl'].' '.$wherestr." ORDER BY ".$this->schema['db']['orderby'];$rs=R::getAll($sql);$steps=count($rs)-1;$l=explode("|",$this->schema['labels']);$lbls="";$v=explode("|",$this->schema['plot']['dset0']['values']);$vals="";for($r=0;$r<$steps;$r++){if(stristr($l[0],'clq_extra')!=false){if($l[2]=='date'){$td=self::getXtraVal($rs[$r]['clq_extra'],$l[1]);$lx=self::summariseDates($td,$ld);$ld=$td;}else{$lx=self::getXtraVal($rs[$r]['clq_extra'],$l[1]);}$lbls.="'".$lx."',";}else{$lbls.="'".$rs[$r][$l[0]]."',";}if(stristr($v[0],'clq_extra')!=false){$vals.=self::getXtraVal($rs[$r]['clq_extra'],$v[1]).",";}else{$vals.=$rs[$r][$v[0]].",";}}$lbls=trim($lbls,",");$vals=trim($vals,",");$dataset="{";foreach($this->schema['plot']['dset0']as$lbl=>$val){$dataset.=$lbl." : '".$val."',";}$dataset=trim($dataset,",");$dataset.="data : [".$vals."]";$dataset.="}";}else{$lbls=$this->schema['labels'];$dataset="";foreach($this->schema['plot']as$d=>$dset){$dataset.="{".PHP_EOL;foreach($dset as$lbl=>$val){if($lbl=="data"){$dataset.=$lbl." : [".$val."],".PHP_EOL;}elseif($lbl=="value"||$lbl=='labelFontSize'){$dataset.=$lbl." : ".$val.",".PHP_EOL;}else{$dataset.=$lbl." : \"".$val."\",".PHP_EOL;}}$dataset=trim($dataset,",".PHP_EOL);$dataset.="},".PHP_EOL;}$dataset=trim($dataset,",".PHP_EOL);}$options="";foreach($this->schema['options']as$lbl=>$val){$options.=$lbl." : ".$val.",".PHP_EOL;}$options=trim($options,",".PHP_EOL);$ch.="<canvas id='chart' width=260 height=260 ></canvas>";$ch.="       
            <script type='text/javascript'>
            <!--//
            $(function() { 
                
                //Get context with jQuery - using jQuery's .get() method.
                var ctx = $('#chart').get(0).getContext('2d');
                
                //This will get the first returned node in the jQuery collection.
                var clqChart = new Chart(ctx); 
                ";if($this->schema['charttype']=='Pie'){$ch.="
                        // Datasets
                        var ddata = [".$dataset."] 
                    ";}else{$ch.="
                        // Data
                        var ddata = {
                            labels : [".$lbls."],
                            datasets : [".$dataset."]
                        };
                    ";}$ch.=" 
                // Options (all the defaults shown below)
                var opts = {
                    ".$options."
                };
                                
                new Chart(ctx).".$this->schema['charttype']."(ddata,opts);    

            });
            //-->
            </script>
        ";return$ch;}function displayEmail($params){$this->schema=$params;$msgs="";define('DISPLAY_XPM4_ERRORS',true);require_once($this->rootpath.'classes/clqxpmail.class.php');$c=POP3::Connect($this->schema['popserver'],$this->schema['username'],$this->schema['password'])or die(print_r($_RESULT));$s=POP3::pStat($c)or die(print_r($_RESULT));list($i,$b)=each($s);if($i>0){$msgs.=POP3::pRetr($c,$i)or die(print_r($_RESULT));}else{$msgs.=str('577:Mailbox is empty');};POP3::disconnect($c);return$msgs;}function displayImages($galimagedir){$gal="";$gal.="
            <script src='".$this->rootpath."js/jquery.js' type='text/javascript'></script>
            <script src='".$this->rootpath."js/flexslider.js' type='text/javascript'></script>
            <style>
                .flex-container a:active, .flexslider a:active, .flex-container a:focus, .flexslider a:focus  {outline: none;}
                .slides, .flex-control-nav, .flex-direction-nav {margin: 0; padding: 0; list-style: none;} 

                .flexslider {margin: 0; padding: 0;}
                .flexslider .slides > li {display: none; -webkit-backface-visibility: hidden;} 
                .flexslider .slides img {width: 100%; display: block;}
                .flex-pauseplay span {text-transform: capitalize;}

                .slides:after {content: "."; display: block; clear: both; visibility: hidden; line-height: 0; height: 0;} 
                html[xmlns] .slides {display: block;} 
                * html .slides {height: 1%;}
                .no-js .slides > li:first-child {display: block;}

                .slides li img {cursor: url(../view/images/mzm.png), crosshair;}

                @media screen and (max-width: 860px) {
                  .flex-direction-nav .flex-prev {opacity: 1; left: 0;}
                  .flex-direction-nav .flex-next {opacity: 1; right: 0;}
                } 

                /* popup_box DIV-Styles*/
                #popup_box {
                    display:none;
                    position:fixed;  
                    _position:absolute;                 
                    width: 600px; height: 400px;
                    z-index:100;
                   cursor: url(../view/images/cancel.png), crosshair;
                }
            
            </style>
        ";$gal.="<div id='popup_box' ></div> <ul class='slides'>";$galimages=glob($this->rootpath.$galimagedir."*.*");foreach($galimages as$i=>$img){$gal.="<li><img src='".$this->rootpath.$img."' style='width: 100%;' /></li>".PHP_EOL;}$gal.="</ul>";$gal.="       
            <script type='text/javascript'>
            <!--//
            $(function() { 
                $('.flexslider').flexslider({
                    animation: 'fade',
                    animationLoop: true,
                    itemWidth: 300,
                    itemMargin: 0,
                    slideshowSpeed: 7000,
                    animationSpeed: 600,
                    randomize: true,
                    controlNav: false,
                    directionNav: false
                });

                $('.slides li img').on('click', function() {
                   var src = $(this).attr('src');
                   var html = '<img src=\"' + src + '\" style=\"border: 2px solid #003366; height: 400px;\" id=\"popupBoxClose\"/>';
                   $('#popup_box').center();
                   loadPopupBox();
                   $('#popup_box').html(html);
                })

                $('#popup_box').click( function() {
                    unloadPopupBox();
                });

                function unloadPopupBox() {// TO Unload the Popupbox
                    $('#popup_box').fadeOut('slow');
                }   
               
                function loadPopupBox() { // To Load the Popupbox
                    $('#popup_box').fadeIn('slow');     
                }   
            });

            jQuery.fn.center = function() {
                this.css('position', 'absolute');
                this.css('top', Math.max(0, (($(window).height() - $(this).outerHeight()) / 2) + $(window).scrollTop()) + 'px');
                this.css('left', Math.max(0, (($(window).width() - $(this).outerWidth()) / 2) + $(window).scrollLeft()) + 'px');
                return this;
            }
            //-->
            </script>
        ";return$gal;}function getXtraVal($xtra,$fld){$x=json_decode($xtra,true);return$x[$fld];}function summariseDates($td,$ld){$l=explode("-",$ld);$t=explode("-",$td);if($l[0].$l[1]==$t[0].$t[1]){$this->yr=$t[0];return;}else{if($t[0]==$this->yr){return$t[1];}else{return$t[1]."-".$t[0];}}}}?>