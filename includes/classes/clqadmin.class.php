<?php
/**
* Cliqon generic admin class
*
*/
class clqadmin {
	  
	public $thisclass="clqadmin";
	public $db, $lcd, $rootpath, $icnpath, $sitepath, $scripts, $dhtmlxpath, $conn; 
	public $qrepl=array();
	public $qwith=array();
	public $cfg = array(); 
	public $imgdir = "views/gallery/";
	public $clqschema = array();
	public $admschema = array();
	public $table = "clqstring", $type = "string"; 
	public $lstr = array();

	function __construct(){

		global $rootpath; $this->rootpath = $rootpath;
		$this->icnpath = "admin/theme/icons/";
		global $sitepath; if(!$sitepath) {$this->sitepath = $_SESSION['CLQ_Sitepath'];} else {$this->sitepath = $sitepath;}; 
		global $cfg; if(!$cfg) {$this->cfg = $_SESSION['CLQ_Config'];} else {$this->cfg = $cfg;};
		global $lcd; if(!$lcd) {$this->lcd = $_SESSION['CLQ_Langcd'];} else {$this->lcd = $lcd;}; 
		global $schema; $this->clqschema = $schema; 
		global $dbcfg;

		require_once($this->rootpath."config/clqadmschema.cfg");
		$this->admschema = $admschema;

		// Cliqon Lite language handler
		require_once($this->rootpath."includes/classes/i18n/cliqon.".$this->lcd.".lcd");
		$this->lstr = $lstr;

		/*
		$this->dhtmlxpath = $this->rootpath."includes/classes/dhtmlx/";
		require($this->dhtmlxpath."db_pdo.php");
	    switch($dbcfg['dbtype']){
	        case"mysql": $this->conn = new PDO('mysql:host='.$dbcfg['server'].';dbname='.$dbcfg['db'], $dbcfg['user'], $dbcfg['password']); break;
	        case"pgsql": $this->conn = new PDO('pgsql:host='.$dbcfg['server'].';dbname='.$dbcfg['db'], $dbcfg['user'], $dbcfg['password']); break;
	        case"sqlite": $this->conn = new PDO('sqlite:'.$this->rootpath.'data/'.$dbcfg['db'], $dbcfg['user'], $dbcfg['password']); break;
	    }

	    // $this->conn->enable_log($this->rootpath."log/dhtmlx.log", true);
	    */

	}

	/*********************************************  Webix Support Classes - Menu ***************************************************************************************/

		/**
		* Generates the JS for a sidebar column in which is a menu.
		* Reads the array from the config file
		* @param = page or tabletype
		*/
		function publishMenu($page) {

			// $ln[] = array('action' => 'datatable', 'tabletype' => $key, 'label' => $lna['label'], 'icon' => $lna['icon'], 'level' => '20');
			$admmenu = array(); $usrlev = '90'; //$_SESSION['CLQ_Level'];
			foreach($this->admschema['menuarray'] as $key => $ln) {
				
				$lv = gmp_cmp($ln['lvl'], $usrlev);
				if($lv == -1) {
					$admmenu[] = $ln;
				};
			}
			$wjs = "";
			$wjs .= "
				[{
					type: 'header', template: '".$this->lstr[370]."' // 90
				},
				{ 
					view: 'list', header: '".$this->lstr[370]."', data: ".json_encode($admmenu).", id: 'admmenu',
					template: '<i class=\"fa fa-#icon# listicon\" style=\"width:25px;\"></i><span rel=\"#action#\" data-type=\"#tabletype#\" data-table=\"#table#\" data-idiom=\"".$this->lcd."\" data-table=\"".$this->table."\" alt=\"#rollover#\" title=\"#rollover#\"  class=\"itemselect\">#label#</span>',
					select:true, scroll:false, width:160, height:590, hover: true					
				}]
			";
			return $wjs;
		}


		function publishUtilMenu($type = "") {
			$umnu = "";
			$umnu .= "
				<ul id='contextmenu' class='drop hide' style='z-index: 10000; background-color: #fff; ' >
					<li class='bluebg white pad5 bold' style='width:148px;'>".$this->lstr[360]."<i style='float:right; cursor:pointer; color: #fff; margin-top: 2px; vertical-align: middle;' class='fa fa-minus-circle txtright utilclosebutton white' title='".$this->lstr[29]."' alt='".$this->lstr[29]."'></i></li>
					<!-- <li class='bluebg white pad5 bold' style='width:148px;'>".$_SESSION['CLQ_Level'].$_SESSION['CLQ_Username']."</li>	-->			
									
				    <li class='pad5'>
				    	<i class='fa fa-hand-o-right'></i>".$this->lstr[1]."
				    	<ul>";
				    		foreach($this->admschema['utilities']['common'] as $l => $li) {
				    			$umnu .= "<li class='cmenubutton lipad' rel='".$li['action']."'><i class='fa fa-".$li['icon']."''></i>".$li['label']."</li>";
				    		}					    	
			$umnu .= "
				    	</ul>
				    </li>";
			
			if($type != "") { // Not shown for dashboard
				$umnu .= "	    
					    <li class=' pad5'>
					    	<i class='fa fa-hand-o-right pad5'></i>".ucwords($type)."
					    	<ul>";
					    		foreach($this->admschema['utilities'][$type] as $l => $li) {
					    			$umnu .= "<li class='cmenubutton lipad' rel='".$li['action']."'><i class='fa fa-".$li['icon']."''></i>".$li['label']."</li>";
					    		}	
				$umnu .= "
					    	</ul>
					    </li>";
			}

			$umnu .= "
				</ul>
			";
			return $umnu;
		}

	/*********************************************  Webix Support Classes - Dashboard **********************************************************************************/

		/**
		* Generates the JS for a Dashboard
		* Reads the array from the config file
		* @param - none required
		*/
		function publishDashboard() {

			$wjs = "";
			$wjs .= "
				[{type: 'header', template: dashBoardTitle('".$this->lstr[374]."')},
				{ 
					id: 'dashboard',
					height:590,  minWidth:600, 
					rows:[
						{cols:[
							{
								width: 285, height: 'auto',
								header: 'Science Daily',
								template:'<div id=\"panel1\"></div>', scroll: 'y'
							},
							{view:'resizer'},
							{	
								width: 285, height: 'auto',
								header: 'BBC',
								template:'<div id=\"panel2\"></div>', scroll: 'y'
							}
						]
						},
						{view:'resizer'},
						{cols:[
							{
								width: 269, height: 'auto',
								header: 'Europress',
								template:'<div id=\"panel3\"></div>', scroll: 'y'
							},
							{view:'resizer'},
							{rows: [
								{	
									width: 315, height: 110,
									header: 'Fancyclock',
									template:'<div id=\"panel4\"></div>'
								},								
								{
									width: 300, height: 220,
									weekNumber:true, date:new Date(),
									view: 'calendar', cellHeight: 30, headerHeight: 40	
								}							
							]}
						]
						}
					]
				}]
			";
			return $wjs;		
		}

		function setDashboardScripts($type) {

			$this->scripts .= "
				
				// Top Buttons
					$('.topbutton').livequery('click', function(e) {
						var action = $(this).attr('rel');
						topButtons(action, 'dashboard', 'dashboard', e);
					});

				// Supports utilities
				$('.utilclosebutton').on('click', function() {
					$('#contextmenu').removeClass('show').addClass('hide');
				});

				$('.cmenubutton').livequery('click', function(e) {
					utilMenuClick(this);
				});

				$('#panel1').rssfeed('http://feeds.sciencedaily.com/sciencedaily/space_time',    {limit: 3, linktarget: '_blank'});
				$('#panel2').rssfeed('http://feeds.bbci.co.uk/news/rss.xml',    {limit: 3, linktarget: '_blank'});
				$('#panel3').rssfeed('http://www.europapress.es/rss/rss.aspx?ch=288',    {limit: 3, linktarget: '_blank'});
				$('#panel4').tzineClock();


			";
		}

	/*********************************************  Webix Support Classes - DataTable **********************************************************************************/

		function set($var, $val) {
			$this->$var = $val;
		}

		function get($var) {
			return $this->$var;
		}

		/**
		* Generates the JS for a Datatable
		* Reads the array from the config file
		* @param - table type
		*/
		function publishDatatable($table, $type) {

			if($this->clqschema[$this->table]['types'][$type]['inlinedit'] == true) {
				$iedit = " editable:true, editaction:'dblclick', ";
			} else {
				$iedit = "";
			};

			$this->table = $table; $this->type = $type;
			$wjs = "";
			$wjs .= "
				[{type: 'header', template: adminTitle('".$this->table."', '".$this->type."', 'dtable')},
				{ 
					view:'datatable', id: 'dtable', select:false, minWidth: 580, height:600, scroll: false, scrollY: false, scrollX: true,
					 ".$iedit." header: 'placeholder', fixedRowHeight: false, resizeColumn:true, columns:".self::dataTableCols($type).", 
					url: '".$this->sitepath."includes/get.php?langcd=".$this->lcd."&action=getdataset&table=".$this->table."&tabletype=".$this->type."',
					yCount: 13, footer: false,
				    on:{
				        onBeforeLoad:function(){
				            this.showOverlay('Loading...');
				        },
				        onAfterLoad:function(){
				            
				            this.hideOverlay();
				            
				            // store.set('dtable', this);

				            if (!this.count()) {
        						this.showOverlay('Sorry, there is no data');
				            }
				        },
				        onAfterRender: function() { // Anything that is needed after the Datatable is loaded
							datatableFunctions();
				        },
				        onAfterEditStop: function(state, editor, ignoreUpdate) {
						    if(state.value != state.old){
						    	updateVal('".$this->table."', editor.column, editor.row, state.value);					       
						    } 
				        }			        
				    },
					pager:{
						container: 'pagerdiv', animate:{type:'top'}, autosize:true,	size:12, group:5
					}
				}]
			";
			// template: \"{common.prev()}<div class='paging_text2'>Page {common.page()} from #limit# </div>{common.next()}\"
			return $wjs;	
		}	

		/**
		* Scripts that have to be run inside the same document ready
		* maybe move to a Require
		*/
		function setDataTableScripts($type) {

			$this->scripts .= "

				store.set('table', '".$this->table."');
				store.set('type', '".$type."');
				
				// Top Buttons
					$('.topbutton').livequery('click', function(e) {
						var action = $(this).attr('rel'); 
						topButtons(action, '".$this->table."', '".$type."', e);
					});

				// Supports utilities
				$('.utilclosebutton').on('click', function() {
					$('#contextmenu').removeClass('show').addClass('hide');
				});

				$('.cmenubutton').livequery('click', function(e) {
					utilMenuClick(this);
				});
				
			";
		}

		function dataTableCols($type) {

			$cols = array();
			
			// ref, common, idioms, value, image, notes, set
			$col = $this->clqschema[$this->table]['types'][$type]['cols'];

			if(in_array('ref', $col)) {
				// Table Reference
				$tblref = $this->clqschema[$this->table]['tblref']; // Gives a usual value of "clq_reference"
				$cols[]	= array(
					'id' => $tblref, 'header' => array($this->lstr[48], array('content' => 'textFilter')), 'editor' => 'text', 'sort' => 'string', 'fillspace' => true
				);
			}

			if(in_array('common', $col)) {
				// Table Common
				$tblcom = $this->clqschema[$this->table]['tblcom']; // Gives a usual value of "clq_common"
				$cols[]	= array(
					'id' => $tblcom, 'header'=> array($this->lstr[220], array('content' => 'textFilter')), 'fillspace' => true, 'editor' => 'popup', 'sort' => 'string'
				);
			}

			if(in_array('image', $col)) {
				$cols[]	= array(
					'id' => 'clq_image', 'header' => $this->lstr[57],  'width' => 150, 'template'	=> '<img src="'.$this->clqschema[$this->table]['types'][$type]['subdir'].'#clq_image#">',
				);	
			}

			if(in_array('value', $col)) {
				$tblval = "clq_value";
				$cols[]	= array(
					'id' => 'clq_value', 'header' => array($this->lstr[59], array('content' => 'textFilter')), 'fillspace' => true, 'editor' => 'text', 'sort' => 'string'
				);
			}		

			if(in_array('idioms', $col)) {
			// Each of the Languages as flags
				
				foreach($this->cfg['site.idiom'] as $lcdcode => $lcdname) {
					$cols[]	= array(
						'id' => 'clq_langcd_'.$lcdcode, 'header'	=> '<img src="'.$this->icnpath.$lcdcode.'.png" style="width: 20px;" title="'.$lcdname.'" />', 'width' => 40, 'template'	=> '<img src="'.$this->icnpath.$lcdcode.'.png" class="idiombuttons" style="width: 20px; margin-top: 7px;" data-id="#clq_langcd_'.$lcdcode.'#" data-table="'.$this->table.'" data-idiom="'.$lcdcode.'" data-type="'.$type.'" data-ref="#clq_reference#" alt="#clq_langcd_'.$lcdcode.'#" title="#clq_langcd_'.$lcdcode.'#" />', 
					);	
				}
			}				

			if(in_array('set', $col)) {
				$cols[]	= array(
					'id' => $tblnts, 'header'	=> array($this->lstr[21]),
					'template'	=> '<img src="'.$this->icnpath.'set.png" class="idiombuttons" style="width: 20px; margin-top: 7px;" data-table="'.$this->table.'" data-ref="#clq_reference#" data-id="#id#" data-type="'.$type.'" />'
				);
			}

			if(in_array('content', $col)) {
				$tblcnt = $this->clqschema[$this->table]['tblcnt']; // Gives a usual value of "clq_text"
				$cols[]	= array(
					'id' => $tblcnt, 'header' => array($this->lstr[162], array('content' => 'textFilter')), 'editor' => 'popup', 'sort' => 'string', 'fillspace' => true
				);
			}	

			if(in_array('notes', $col)) {
				$tblnts = $this->clqschema[$this->table]['tblnts']; // Gives a usual value of "clq_notes"
				$cols[]	= array(
					'id' => $tblnts, 'header' => array($this->lstr[162], array('content' => 'textFilter')), 'editor' => 'popup', 'sort' => 'string', 'fillspace' => true
				);
			}	

			return json_encode($cols);
		}

		function dataTableData($rq) {
			
			$this->type = $rq['tabletype'];
			$this->table = $rq['table'];

			$sql = "SELECT id, clq_reference, clq_common, clq_notes, clq_value, clq_image, clq_text FROM ".$this->table." WHERE clq_type = '".$this->type."' AND clq_langcd = '".$this->lcd."' ORDER BY clq_reference ASC, clq_order ASC";
			$rs1 = R::getAll($sql);

			$dta = array();
			if(count($rs1) < 1) {
				$dta['clq_reference'] = "No records found";
				$dta['clq_common'] = $sql;
				$dta['clq_value'] = "-";
				$dta['clq_text'] = "-";
				$dta['clq_order'] = "-";
				$dta['id'] = "0";
				$dta['clq_image'] = "blank.gif";
				foreach($this->cfg['site.idiom'] as $lcdcode => $lcdname) {
					$dta['clq_langcd_'.$lcdcode] = "0";
				}
				$dta['clq_notes'] = "Notes";
			} else {
				for($r = 0; $r < count($rs1); $r++) {
					
					$a1 = array(); $a2 = array();
					$a1 = $rs1[$r]; 
					$sql = "SELECT id, clq_langcd FROM ".$this->table." WHERE clq_type = '".$this->type."' AND clq_reference = '".$rs1[$r]['clq_reference']."' ORDER BY clq_langcd ASC";
					$rs2 = R::getAll($sql);
					foreach($rs2 as $n => $row) {
						$a2["clq_langcd_".$row['clq_langcd']] = $row['id'];
					}
					$dta[] = array_merge($a1, $a2); unset($a1); unset($a2);
				}				
			}


			return json_encode($dta);
		}

	/*********************************************  Webix Support Classes - DataTree ***********************************************************************************/

		/**
		* Generates the JS for a Datatree
		* Reads the array from the config file
		* @param - none required
		*/
		function publishDataTree($table, $type) {

			$this->table = $table; $this->type = $type;
			$wjs = "";
			$wjs .= "
				[{type: 'header', template: adminTitle('".$this->table."', '".$this->type."', 'dtree')},
				{
				    view:'tree', id: 'dtree', select:true, minWidth: 580, height:600, footer: false, css: 'clqtree',
				    url: '".$this->sitepath."includes/get.php?langcd=".$this->lcd."&action=gettreeset&table=".$this->table."&tabletype=".$this->type."',		
					template: '{common.icon()} {common.folder()} <span id=\"#id#\">#value#</span>',
				    on:{
				        onBeforeLoad:function(){
				            webix.message('Loading...');
				        },
				        onAfterLoad:function(){				        
				            if (!this.count()) {
	    						webix.message('Sorry, there is no data');
				            }
				        },
				        onItemClick: function(id) {
				        	var thisspan = 'span[id=\"' + id +'\"]';
    						var x = $(thisspan).position();
    						var ow = $(thisspan).outerWidth();
				        	clqBar(id, x, ow, '".$this->table."', '".$this->type."');
				        }
				    }			
				}]
			";
			return $wjs;			
		}

		function setDataTreeScripts($type) {
			
			$this->scripts .= "
				
				store.set('table', '".$this->table."');
				store.set('type', '".$this->type."');
				
				// Top Buttons
					$('.topbutton').livequery('click', function(e) {
						var action = $(this).attr('rel'); 
						topButtons(action, '".$this->table."', '".$this->type."', e);
					});

				// Supports utilities
				$('.utilclosebutton').on('click', function() {
					$('#contextmenu').removeClass('show').addClass('hide');
				});

				$('.cmenubutton').livequery('click', function(e) {
					utilMenuClick(this);
				});



			";
		}

		function dataTreeData($rq) {
			$rs = clq::clqUnList($rq['tabletype'], true);
		    return $rs;
		}

	/*********************************************  Webix Support Classes - Gallery  ***********************************************************************************/

		/**
		* Generates the JS for an Image Gallery
		* Reads the array from the config file
		* @param - none required
		*/
		function publishGallery() {

			// 
			$clqth = new clqthumbnail(); $dbimgarray = array();
			// Routine to create thumbnails if they do not exist
			$lgimgarray = glob($this->imgdir."{*.jpg, *.gif, *.png}", GLOB_BRACE);
			
			// Check if thumbnail exists
			foreach($lgimgarray as $i => $lgimg) {
				
				// Get filename from image
				$img = $lgimg;
				$lgimg = str_replace($this->imgdir, '', $lgimg);
				$thmbimg = $this->imgdir."thumbs/thmb_".$lgimg;
				$dbimgarray[$thmbimg] = $lgimg;
				if(!file_exists($thmbimg)) {
					// Create thumbnail
					$options = array(
					    'type'   => IMAGETYPE_JPEG,
					    'width'   => 150,'height'  => 150,
					    'method'  => THUMBNAIL_METHOD_SCALE_MAX,
					    'coord_x1'  => 0, 'coord_y1'  => 0,'coord_x2'  => 0, 'coord_y2'  => 0, 'percent' => 0,
					    'halign'  => THUMBNAIL_ALIGN_CENTER, 'valign'  => THUMBNAIL_ALIGN_CENTER,
					    'corner'  => 0, 'corcolor'  => 'FFFFFF', 'cortransparent' => 0, 'mark'  => 0,
					 );

					 $clqth->output($img, $thmbimg, $options);
				}
			}

			// Generate Image records in Database if they do not exist
			foreach($dbimgarray as $i => $img) {
				$sql = "SELECT DISTINCT clq_reference FROM clqstring WHERE clq_type = ? AND clq_image = ? AND clq_langcd = ?";
				$image = R::getCell($sql, array("image", $img, $this->lcd));
				if($image == "") {
					foreach($this->cfg['site.idiom'] as $lcdcode => $lcdname) {
						$ins = R::dispense("clqstring");
						$ins->clq_langcd = $lcdcode;
						$ins->clq_type = "image";
						$ins->clq_reference = $img;
						$ins->clq_image = $img;
						$ins->clq_order = $i;
						$ins->clq_value = "Dummy title";
						$ins->clq_text = "Dummy caption";
						$ins->clq_notes = "Generated automatically";
						$ins->clq_extra = "{}";
						$res = R::store($ins);
					}
				} 
			}

			/* Valid flags:
			 
			GLOB_MARK
			GLOB_NOSORT
			GLOB_NOCHECK
			GLOB_NOESCAPE
			GLOB_BRACE
			GLOB_ONLYDIR
			GLOB_ERR
			 
			see PHP.net manual for more info
			$images = glob("images/{*.jpg,*.gif,*.png}", GLOB_BRACE);
			*/

			$iarray = glob($this->imgdir."thumbs/{*.jpg, *.png, *.gif}", GLOB_BRACE); $ximg = array();
			
			foreach($iarray as $i => $thmbimg) {
				
				// Get filename from image
				$lgimg = str_replace($this->imgdir, '', $thmbimg);
				$img = str_replace("thumbs/thmb_", "", $lgimg);
				$ximg[$img] = $thmbimg;

			}

			// $imgarray = json_encode($ximg);
			$imgarray = json_encode($iarray);

			$wjs = "";
			$wjs .= "
				[{type: 'header',  template: dashBoardTitle('".$this->lstr[259]."')},
				{
					view: 'dataview', id: 'dgallery', css: {'margin-top':'5px;'},
					minWidth:580, height:600, scroll: false, scrollY: false, scrollX: true,
					on: {
						onAfterRender: function() { // Anything that is needed after the Datatable is loaded
							galleryFunctions();
				        },
					},
					type: {
						borderless: true,
						width: 150,
						height: 135, yCount: 4, // Controls height of Gallery
						template: '<div style=\"padding: 0; margin: 0;\" class=\"overall imgbutton\" data-table=\"clqstring\" data-type=\"image\" rel=\"#key#\" data-image=\"#value#\" ><img src=\"#value#\" style=\"width:100%; margin-top:10px;\" /></div>'
					},
					pager:{
						container: 'pagerdiv', animate:{type:'top'}, autosize:true,	size:16, group:5
					},
					data: ".$imgarray."
				}]
			";
			return $wjs;			
		}

		function setGalleryScripts() {
			$this->scripts .= "

				// Top Buttons
					$('.topbutton').livequery('click', function(e) {
						var action = $(this).attr('rel');
						topButtons(action, 'gallery', 'image', e);
					});

				// Supports utilities
				$('.utilclosebutton').on('click', function() {
					$('#contextmenu').removeClass('show').addClass('hide');
				});

				$('.cmenubutton').livequery('click', function(e) {
					utilMenuClick(this);
				});	

			";
		}

	/*********************************************  Webix Support Classes - Calendar ***********************************************************************************/

		/**
		* Generates the JS for a Calendar
		* Reads the array from the config file
		* @param - none required
		*/
		function publishCalendar($table, $type) {

			$this->table = $table; $this->type = $type;
            $cal .= '<div id="dcalendar" style="width:100%; height: 100%; padding:10px;"></div>';                  

			$wjs = "";
			$wjs .= "
				[{type: 'header', template: dashBoardTitle('".$this->lstr[227]."')},
				{type: 'calendar', height:580,  width:600, template: '".$cal."'}]
			";
			return $wjs;			
		}

	    /**
	    * Generates the record set
	    */
	    function getCalendarData($type, $rq) {

			$this->table = "clqdata";
	        // /get/lcd/getcalendardata/event/?start=2013-12-01&end=2014-01-12&_=1386054751381
	        $sql = "SELECT id, clq_title, clq_datefrom, clq_dateto, clq_url, clq_category, clq_reference, clq_summary FROM clqdata ";   

	        // Where
	        $where = " WHERE ";
	        $qrepl = array('"', '{lcd}'); $qwith = array("'", $this->lcd);
	        foreach($this->clqschema[$this->table]['where'] as $wk => $wset) {
	            $w = explode('|', $wset);
	            $w[2] = str_replace($qrepl, $qwith, $w[2]);
	            $where .= $w[0]." ".$w[1]." ".$w[2]." AND ";
	        }
	        array_key_exists('start', $rq) ? $where .= "clq_datefrom >= '".$rq['start']."' " : $where .= "";
	        array_key_exists('end', $rq) ? $where .= "AND clq_dateto <= '".$rq['end']."' " : $where .= "";
	        // Not needed but useful when debugging
	        $where = trim($where, " AND ");
	        $sql .= $where;

	        // Order by    
	        $orderby = "ORDER BY ";     
	        foreach($this->clqschema[$this->table]['orderby'] as $ok => $oset) {   
	            $orderby .= $ok." ".$oset.", ";
	        }
	        $orderby = trim($orderby, ", ");
	        $sql .= $orderby;

	        // echo $sql.PHP_EOL;

	        $rs = array(); 
	        $rs = R::getAll($sql);
	        $result = array();
	        for($r = 0; $r <= count($rs); $r++) {
	            $result[] = self::getEvent($rs[$r]);
	        }
	        
	        // return the lot as json encoded Result
	        return json_encode($result);
	    }

	    /**
	    * Internal function to format an individual event into an event array object 
	    */
	    private function getEvent($row) {

	        $rowarray = array();

	        $rowarray['id'] = $row['id'];
	        $rowarray['title'] = $row['clq_title'];

	        if($row['clq_datefrom'] == $row['clq_dateto']) {
	            $rowarray['start'] = $row['clq_datefrom'];
	            $rowarray['allDay'] = 'true';
	        } else {
	            $rowarray['start'] = $row['clq_datefrom'];
	            $rowarray['end'] = $row['clq_dateto'];
	            $rowarray['allDay'] = 'false';
	        }      
	             
	        $rowarray['url'] = $row['clq_url'];
	        $rowarray['description'] = $row['clq_summary'];
	        $rowarray['reference'] = $row['clq_reference'];
	        $rowarray['className'] = 'clqevent '.$row['clq_value'];

	        // Colors depend on Category
	        switch($row['clq_category']) {

	            /*
	            case "": 
	                $rowarray['borderColor'] = '';
	                $rowarray['textColor'] = '';
	                $rowarray['color'] = '';
	                $rowarray['backgroundColor'] = '';
	            break;
	            */

	            default:
	                $rowarray['borderColor'] = '#4F8EDC';
	                $rowarray['textColor'] = '#fff';
	                $rowarray['color'] = '#fff';
	                $rowarray['backgroundColor'] = '#4F8EDC';
	            break;
	        }
	   
	        return $rowarray;
	    }

	    /**
	    * Display an individual event for some purpose such as view
	    * 
	    */
	    function getCalendarEvent($recid) {   

	        // getCell
	    }

		function setCalendarScripts() {

			$calopts = array(
				
				'lang' => $this->lcd,
				'theme' => false,
				'height' => 540,
				'header' => array(
		        	'left' => 'prev next',
		            'center' => 'title',
		            'right' => 'month, agendaWeek, agendaDay prevYear, nextYear' 
				),
				'buttonIcons' => array(
					'prev' => 'left-single-arrow',
					'next' => 'right-single-arrow',
					'prevYear' => 'left-double-arrow',
					'nextYear' => 'right-double-arrow'
				),	
				'columnFormat' => array(
		            'month' => 'ddd',    // Mon
		            'week' => 'ddd d-M', // Mon 9/7
		            'day' => 'dddd d-M'  // Monday 9/7
				),
				'buttonText' => array(
	                'prev' 		=>  $this->lstr[5],
	                'next' 		=>  $this->lstr[6],
	                'prevYear'	=>  $this->lstr[392],
	                'nextYear'	=>  $this->lstr[393],
	                'today'		=>  $this->lstr[8],
	                'month'		=>  $this->lstr[9],
	                'week'		=>  $this->lstr[10],
	                'day'		=>  $this->lstr[11]
				),
				'events' => array(
		            'url' 	=> '/includes/get.php?langcd='.$this->lcd.'&action=getcalendardata',
		            'type' 	=> 'POST',
		            'error'	=> '<function() { alert(lstr[0]); }>'
				)
			);
			$opts = json_encode($calopts); $opts = str_replace('"<', null, $opts); $opts = str_replace('>"', null, $opts); 
			$opts = trim($opts, '}'); // Remove last bracket so we can add items

			$this->scripts .= "

				store.set('table', 'clqdata');
				store.set('type', 'event');
				
				// Top Buttons
					$('.topbutton').livequery('click', function(e) {
						var action = $(this).attr('rel');
						topButtons(action, 'clqdata', 'event', e);
					});

				// Supports utilities
				$('.utilclosebutton').on('click', function() {
					$('#contextmenu').removeClass('show').addClass('hide');
				});

				$('.cmenubutton').livequery('click', function(e) {
					utilMenuClick(this);
				});
				
				var cid;
				$('#dcalendar').fullCalendar(
					".$opts."}},
					dayClick: function(date, jsEvent, view) {
						publishForm('addrecord', 'clqdata', 'event', 0, jsEvent);
						$('input[name=\"clq_datefrom\"]').setValue(date);
						$('input[name=\"clq_dateto\"]').setValue(date);
					},
					eventClick: function(calEvent, jsEvent, view) { 
						store.set('recid', calEvent.id); 
						store.set('ref', calEvent.clq_reference); 
					}

				});

			";
		}

	/*********************************************  Webix Support Classes - Report Generator ***************************************************************************/

		/**
		* Generates the JS for a Report Generator using Reportico
		* Reads the array from the config file
		* @param - none required
		*/
		function publishRepGen($params) {

			/*
            require_once("apps/reportico/index.php"); 
            $a = new reportico();
            $a->embedded_report = true;
            // $a->forward_url_get_parameters = $params; 
            $rpt = $a->execute();
			*/

			$wjs = "";
			$wjs .= "
				[{type: 'header', template: repGenTitle('".$this->lstr[113]."')},
				{ 
					id: 'repgen',
					height:590,  minWidth:600,
					cols:[
						{
							view: 'list',
							id:'dtable',
							width: 200,
							url: '/includes/get.php?action=getlist&langcd=".$this->lcd."',
						    on:{
						        onAfterRender: function() { // Anything that is needed after the Datatable is loaded
									repgenFunctions();
						        }		        
						    },
						    autoheight: true,
						    type: {height:70,
								template: '<div class=\"repgenitem\" rel=\"#id#\" data-ref=\"#clq_reference#\" data-idiom=\"#clq_langcd#\" data-id=\"#id#\" data-table=\"clqstring\" data-type=\"report\"><div>#id#: <strong>#clq_value#</strong></div><div style=\"line-height: 120%; font-size: 12px;\">#clq_text#</div></div>',  // Format for a record
						    } // Controls individual list item - ccs[classname], height[n], width[n], template[function returns string]
						},
						{
							view:'resizer',
							id:'resizer'
						},
						{	
							id:'repgenactions',
							template:'<div id=\"reportspace\"></div>',
							width:400
						}
					]
				}]
			";
			return $wjs;		
		}

		function setRepGenScripts() {
			$this->scripts .= "
                
                moment().format();

                $.datepicker.setDefaults({
                    regional: ['".$this->lcd."'],
                    dateFormat: 'dd-M-yy'
                });
 
				$('body div #adminspace').tinyscrollbar();

				store.set('table', 'clqstring');
				store.set('type', 'report');
				
				// Top Buttons
					$('.topbutton').livequery('click', function(e) {
						var action = $(this).attr('rel');
						topButtons(action, 'clqstring', 'report', e);
					});

				// Supports utilities
					$('.utilclosebutton').on('click', function() {
						$('#contextmenu').removeClass('show').addClass('hide');
					});

					$('.cmenubutton').livequery('click', function(e) {
						utilMenuClick(this);
					});

				

			";
		}

	/*********************************************  Webix Support Classes - File Manager ***************************************************************************/

		/**
		* Generates the JS for a File Manager
		* Reads the array from the config file
		* @param - none required
		*/
		function publishFileMan() {

			$wjs = "";
			$wjs .= "
				[{type: 'header', template: '".$this->lstr[353]."'},
				{type: 'fileman', height:580,  width:600, template: '<div id=\"filemanager\" style=\"width:100%; height: 100% \" ></div>'}]
			";
			return $wjs;			
		}

	/*********************************************  Webix Support Classes - String List ********************************************************************************/

		/**
		* Display list of admin strings
		* LCD and eventually JS
		*/
		function publishDataList() {

			$strs = array(); $l = 0;
			foreach($this->lstr as $s) {
				if($s != "") {
					$strs[] = array('key' => $l, 'value' => $s); 
				}	
				$l++;
			}

			$fn = $this->rootpath."includes/js/i18n/cliqon.".$this->lcd.".js";
			$hjs = fopen($fn, "r");
			$jsdata = fread($hjs, filesize($fn));

			$wjs = "";
			$wjs .= "[{
				height:620,
				padding: 0,
				width:610,
				cols:[{
						width: 300,
						rows: [
							// Lstr PHP
							{
			                	height: 35, view:'toolbar',
				                elements:[
				                    {view:'text', id:'list1_input', label:'".$this->lstr[377]."', css:'fltr', labelWidth:170 }
				                ]
							},
							{ 
				                view:'list', id: 'list1', select: true, width: 300,
				                template: '#key#: #value#',
				                data:".json_encode($strs).",
				                scheme:{ \$sort:{by: 'value', dir: 'asc'}},
							}
						]
					},
					{width:15,},
					{
						width: 300,
						rows: [{
		                	height: 35, view:'toolbar',
			                elements:[
			                    {view:'text', id:'list2_input', label:'".$this->lstr[378]."', css:'fltr', labelWidth:170 }
			                ]
						},
						{ 
			                view:'list', id: 'list2', select: true, width: 300,
			                template: '#key#: #value#',
			                select:true,
			                data: getList(lstr),
						}]
					}]
				}]				
			";
			return $wjs;		
		}

	/*********************************************  Generic Admin and Utility Functions ********************************************************************************/

		function getAdminScripts($pagetype, $type = "") {
			
			switch($pagetype) {

				case "calendar": self::setCalendarScripts($type); break;
				case "datatree": self::setDataTreeScripts($type); break;
				case "gallery": self::setGalleryScripts(); break;
				case "repgen": self::setRepGenScripts(); break;
				case "dashboard": self::setDashboardScripts($type); break;
				case "datatable": case "datatree": default: self::setDataTableScripts($type); break;
			}

			return $this->scripts;
		}

		/**
		* Creates the Div and Toolbar that is attached to idiom buttons on Datatable and Datatree etc.
		*/
		function publishToolbar($type) {

			$tlb = $this->clqschema[$this->table]['types'][$type]['toolbar'];
			$tb = '<div id="admintoolbar" class="hide" >';
			
			// record, set, content, json, view, delete
			if(in_array('record', $tlb)) {$tb .= '<a href="#" rel="editrecord" ><i class="fa fa-wrench" title="'.$this->lstr[100].'" alt="'.$this->lstr[100].'"></i></a>';};
			if(in_array('set', $tlb)) {$tb .= '<a href="#" rel="editset" ><i class="fa fa-wrench" title="'.$this->lstr[21].'" alt="'.$this->lstr[21].'"></i></a>';};
			if(in_array('run', $tlb)) {$tb .= '<a href="#" rel="runreport" ><i class="fa fa-align-justify" title="'.$this->lstr[113].'" alt="'.$this->lstr[113].'"></i></a>';};
			if(in_array('content', $tlb)) {$tb .= '<a href="#" rel="editcontent"><i class="fa fa-pencil" title="'.$this->lstr[363].'" alt="'.$this->lstr[363].'"></i></a>';};
			if(in_array('json', $tlb)) {$tb .= '<a href="#" rel="jsonedit" ><i class="fa fa-asterisk" title="'.$this->lstr[381].'" alt="'.$this->lstr[381].'"></i></a>';};
			if(in_array('view', $tlb)) {$tb .= '<a href="#" rel="viewset"><i class="fa fa-comments"  title="'.$this->lstr[382].'" alt="'.$this->lstr[382].'"></i></a>';};
			if(in_array('viewset', $tlb)) {$tb .= '<a href="#" rel="viewset"><i class="fa fa-comments"  title="'.$this->lstr[382].'" alt="'.$this->lstr[382].'"></i></a>';};
			if(in_array('viewrecord', $tlb)) {$tb .= '<a href="#" rel="viewrecord"><i class="fa fa-comment-o"  title="'.$this->lstr[382].'" alt="'.$this->lstr[382].'"></i></a>';};
			if(in_array('viewcontent', $tlb)) {$tb .= '<a href="#" rel="viewcontent"><i class="fa fa-comment-o"  title="'.$this->lstr[104].'" alt="'.$this->lstr[104].'"></i></a>';};
			if(in_array('delete', $tlb)) {$tb .= '<a href="#" rel="deleteset"><i class="fa fa-times-circle-o"  title="'.$this->lstr[106].'" alt="'.$this->lstr[106].'"></i></a>';};
			if(in_array('viewimg', $tlb)) {$tb .= '<a href="#" rel="viewimg"><i class="fa fa-camera"  title="'.$this->lstr[104].'" alt="'.$this->lstr[104].'"></i></a>';};
			if(in_array('deleteimg', $tlb)) {$tb .= '<a href="#" rel="deleteimg"><i class="fa fa-times-circle-o"  title="'.$this->lstr[106].'" alt="'.$this->lstr[106].'"></i></a>';};
			if(in_array('imgset', $tlb)) {$tb .= '<a href="#" rel="editimage" ><i class="fa fa-wrench" title="'.$this->lstr[21].'" alt="'.$this->lstr[21].'"></i></a>';};
			// More

			$tb .= '</div>';
			return $tb;
		}

	/*********************************************  Forms and View Functions   *****************************************************************************************/

		/**
		* Get a form definition from clqschema.cfg
		* return definition as JSON suitable for clqform [json]
		* // var url = "/includes/get.php?action=getformdef&langcd=" + store.get('clq_langcd') + "&table=" + table + "&tabletype=" + type + "&recid=" + recid;
		* params = langcd, table, tabletype and recid which is 0 means that it is a new record
		*/
		function getFormDefinition($rq) {

			$this->table = $rq['table'];
			$formdef = array();

			// Hidden fields first
			if($rq['recid'] == 0) {
				$formdef[] = array('type' => 'hidden', 'name' => 'action', 'value' => 'insertrow'); $x = "add";
				$formdef[] = array('type' => 'hidden', 'name' => 'idiomreplicate', 'value' => $this->clqschema[$rq['table']]['types'][$rq['tabletype']]['replicate']);		
				$formdef[] = array('type' => 'hidden', 'name' => 'clq_type', 'value' => $rq['tabletype']);
				$formdef[] = array('type' => 'hidden', 'name' => 'clq_extra', 'value' => '{}');										
			} else {
				$formdef[] = array('type' => 'hidden', 'name' => 'action', 'value' => 'updaterow'); $x = "edit";
				$formdef[] = array('type' => 'hidden', 'name' => 'recid', 'value' => $rq['recid']);
			}
			$formdef[] = array('type' => 'hidden', 'name' => 'table', 'value' => $rq['table']);
			$formdef[] = array('type' => 'hidden', 'name' => 'tabletype', 'value' => $rq['tabletype']);
			$formdef[] = array('type' => 'hidden', 'name' => 'langcd', 'value' => $rq['langcd']);

			// Main fields
			$flds = $this->clqschema[$rq['table']]['types'][$rq['tabletype']]['formfields']; // Array

			foreach($flds as $k => $fldname) {
				$fld = explode("|", $fldname);
				$def = self::getThisField($fld[0], $rq['table'], $rq['tabletype'], $fld[0], $x); if(is_array($def)) {$formdef[] = $def;};
			}
			return $formdef;
		}

		/**
		* Same as Form but Creates a definition for a Formset
		*/
		function getFormSetDefinition($rq) {
			
			// table, type and ref

			$formdef = array(); $common = array(); $lcdspecific = array(); $specific = array();
			$common[] = array('type' => 'hidden', 'name' => 'action', 'value' => 'updateset'); 
			$common[] = array('type' => 'hidden', 'name' => 'table', 'value' => $rq['table']);
			$common[] = array('type' => 'hidden', 'name' => 'tabletype', 'value' => $rq['tabletype']);
			$common[] = array('type' => 'hidden', 'name' => 'langcd', 'value' => $rq['langcd']);


			$flds = $this->clqschema[$rq['table']]['types'][$rq['tabletype']]['formfields']; // Array

			// Common or shared
			$common[] = array('type' => 'container', 'class' => 'clqtable-row', 'css' => '{}', 'html' => array(
					array('type' => 'span', 'class' => 'clqtable-cell w2', 'html' => ""),
					array('type' => 'span', 'class' => 'clqtable-cell w9 bold blue', 'html' => $this->lstr[220])
			));	

			foreach($flds as $k => $fldname) {
				$fld = explode("|", $fldname);	
				if( (!isset($fld[1])) || ($fld[1] == "c") ) {
					// Shared or Common - everything but clq_text
					$def = self::getThisField($fld[0], $rq['table'], $rq['tabletype']); if(is_array($def)) {$common[] = $def;};
				} 
			};

			// Language specific
			foreach($this->cfg['site.idiom'] as $lcdcode => $lcdname) {
				
				// Heading for the Language		
				$lcdspecific[] = array('type' => 'container', 'class' => 'clqtable-row', 'css' => '{}', 'html' => array(
					array('type' => 'span', 'class' => 'clqtable-cell w2', 'html' => ""),
					array('type' => 'span', 'class' => 'clqtable-cell w9 bold blue', 'html' => $lcdname)
				));	

				$lcdspecific[] = array('type' => 'hidden', 'name' => 'id_'.$lcdcode, 'value' => '');

				// Each Field that is not common
				foreach($flds as $k => $fldnm) {
					$fld = explode("|", $fldnm);	
					if($fld[1] == "s") {
						$fldname = $fld[0]."_".$lcdcode;
						$def = self::getThisField($fld[0], $rq['table'], $rq['tabletype'], $fldname); 
						if(is_array($def)) {
							$lcdspecific[] = $def;
						};
					} 
				}
			}

			$formdef = array_merge($common, $lcdspecific);
			return $formdef;
		}

		/**
		* Part of Form Definition
		* creates an actual usable line
		*/
		private function getThisField($fld, $table, $type, $fldname = "", $x = "") {

    		$def = array();

    		$flddef = $this->clqschema[$table]['flds'][$fld];
    		$altfldtitle = $this->clqschema[$table]['fldtitles'][$type]; // Array 
    		$altflddefs = $this->clqschema[$table]['flddefs'][$type]; // Array 

    		// if( ($x == "add" && stristr($flddef['formtype'], "a")) || ($x == "edit" && stristr($flddef['formtype'], "e")) ) {

    			if($fldname != "") {
    				$def['id'] = $fldname;
    				$def['name'] = $fldname; 	
    			} else {
    				$def['id'] = $fld;
    				$def['name'] = $fld; 	
    			}  
 			
    			if(array_key_exists($fld, $altfldtitle)) {$def['caption'] = $altfldtitle[$fld];} else {$def['caption'] = $flddef['label'];};
	    		if(array_key_exists($fld, $altflddefs)) {

	    			foreach($altflddefs[$fld] as $key => $val) {
		    			$val = str_replace("<", "", $val); $val = str_replace(">", "", $val); 
		    			$def[$key] = $val;	    					
	    			}

	    		} else {	
		    		
		    		if(array_key_exists("placeholder", $flddef)) {$def['placeholder'] = $flddef['placeholder'];};
		    		if(array_key_exists("defval", $flddef)) {$def['value'] = $flddef['defval'];};
		    		if(array_key_exists("inputclass", $flddef)) {$def['class'] = $flddef['inputclass'];} else {$def['class'] = "std";};
		    		if(array_key_exists("required", $flddef)) {$def['required'] = "required";};
		    		if(array_key_exists("style", $flddef)) {
		    			$altdef = str_replace("'<", "", $flddef['style']); 
		    			$altdef = str_replace(">'", "", $altdef); 
		    			$def['style'] = $altdef;
		    		};

					switch($fld) {

						case "clq_image":
							if($type == "library") { // Library
								$def['type'] = "list"; $def['data-options'] = 'libraryextnicons';  
							} else {
								/* $.clqform.addType('image', function(options) { return $('<input type="text" /><i class="fa fa-picture-o" style="cursor:pointer;"></i>').clqform('attr', options); }); */
								$def['type'] = "image"; // Default
								$def['data-dir'] = $flddef['data-dir'];
								$def['data-extns'] = $flddef['data-extns'];; 
							}
						break;

						default:
							if(array_key_exists("type", $flddef)) {$def['type'] = $flddef['type'];} else {$def['type'] = "text";};
						break;  
					}  			
	    		}

				$formfield = array(
					'type' => 'container',
					'class' => 'clqcontrol-group',
					'html' => $def
				);

				return $formfield;
			/*
    		} else {
    			return false;
    		} */
		}
		
		/**
		 * Return a Json format view definition 
		 */
		function getViewDefinition($rq) {

			$viewdef = array(); $formdef = array(); $common = array(); $lcdspecific = array(); $specific = array();
			if(array_key_exists($rq['tabletype'], $this->clqschema[$rq['table']]['viewdefs'])) {
				$flds = $this->clqschema[$rq['table']]['viewdefs'][$rq['tabletype']];
			} else {
				$flds = $this->clqschema[$rq['table']]['types'][$rq['tabletype']]['formfields']; // Array
			}

			if(array_key_exists('recid', $rq)) { // Row by ID
				
				// Each Field that is not common
				foreach($flds as $k => $fldname) {
					$def = self::getViewField($fldname, $rq['table'], $rq['tabletype'], $fldname); 
					if(is_array($def)) {
						$viewdef[] = $def;
					};
				}

			} else { // Set by Ref
				
				// Common or shared
				$common[] = array('type' => 'container', 'class' => 'clqtable-row', 'css' => '{}', 'html' => array(
						array('type' => 'span', 'class' => 'clqtable-cell w2', 'html' => ""),
						array('type' => 'span', 'class' => 'clqtable-cell w9 bold blue', 'html' => $this->lstr[220])
				));	

				foreach($flds as $k => $fldname) {
					$fld = explode("|", $fldname);	
					if( (!isset($fld[1])) || ($fld[1] == "c") ) {
						// Shared or Common - everything but clq_text
						$def = self::getViewField($fld[0], $rq['table'], $rq['tabletype']); if(is_array($def)) {$common[] = $def;};
					} 
				};

				// Language specific
				foreach($this->cfg['site.idiom'] as $lcdcode => $lcdname) {
					
					// Heading for the Language		
					$lcdspecific[] = array('type' => 'container', 'class' => 'clqtable-row', 'css' => '{}', 'html' => array(
						array('type' => 'span', 'class' => 'clqtable-cell w2', 'html' => ""),
						array('type' => 'span', 'class' => 'clqtable-cell w9 bold blue', 'html' => $lcdname)
					));	

					// Each Field that is not common
					foreach($flds as $k => $fldnm) {
						$fld = explode("|", $fldnm);	
						if($fld[1] == "s") {
							$fldname = $fld[0]."_".$lcdcode;
							$def = self::getViewField($fld[0], $rq['table'], $rq['tabletype'], $fldname); 
							if(is_array($def)) {
								$lcdspecific[] = $def;
							};
						} 
					}
				}
				$viewdef = array_merge($common, $lcdspecific);
			}

			return $viewdef;
		}

		function getViewField($fld, $table, $type, $fldname = "") {

    		$def = array();

    		$flddef = $this->clqschema[$table]['flds'][$fld];
    		$altfldtitle = $this->clqschema[$table]['fldtitles'][$type]; // Array 

			if($fldname != "") {
				$def['id'] = $fldname;
				$def['name'] = $fldname; 	
			} else {
				$def['id'] = $fld;
				$def['name'] = $fld; 	
			}  
			
			if(array_key_exists($fld, $altfldtitle)) {$caption = $altfldtitle[$fld];} else {$caption = $flddef['label'];}; 		
    		if(stristr($flddef['formtype'], "v")) {

				switch($fld) {

					case "clq_image":

						if ($type == "link" || $type == "news" || $type == "image") { // Link, News and Image
							
							$def = array('type' => 'container', 'class' => 'clqtable-row', 'css' => '{}', 'html' => array(
										array('type' => 'span', 'class' => 'clqtable-cell w2 txtright blue', 'html' => $caption),
										array('type' => 'span', 'class' => 'clqtable-cell w9', 'html' => array(
											'type' => 'img', 'css' => '{}', 'src' => $this->sitepath.$flddef['subdir'].$val, 'alt' => $flddef['subdir'].$val, 'title' => $flddef['subdir'].$val, 'name' => $def['name'], 'id' => $def['id'],
									)))
								);			
				    
						} else {

							$def = array('type' => 'container', 'class' => 'clqtable-row', 'css' => '{}', 'html' => array(
									array('type' => 'span', 'class' => 'clqtable-cell w2 txtright blue', 'html' => $caption),
									array('type' => 'span', 'class' => 'clqtable-cell w9', 'name' => $def['name'],'id' => $def['id'], 'html' => $val)
							));			
						}
					break;
					
					default: 

						$def = array('type' => 'container', 'class' => 'clqtable-row', 'css' => '{}', 'html' => array(
								array('type' => 'span', 'class' => 'clqtable-cell w2 txtright blue', 'html' => $caption),
								array('type' => 'span', 'class' => 'clqtable-cell w9','name' => $def['name'], 'id' => $def['id'], 'html' => $val)
						));			

					break;	
  
				}  			
			}
			return $def;
		}
		
}
// Class Ends

class A extends clqadmin{};
class_alias('clqadmin','A');

