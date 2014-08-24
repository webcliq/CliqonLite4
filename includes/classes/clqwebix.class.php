<?php

//enable buffering to catch and ignore any custom output before XML generation because of this command, it strongly recommended to include connector's file before any other libs in such case it will handle any extra output from not well formed code of other libs
ini_set("output_buffering","On"); ob_start();

class OutputWriter{
	private $start;
	private $end;
	private $type;

	public function __construct($start, $end = ""){
		$this->start = $start;
		$this->end = $end;
		$this->type = "xml";
	}
	public function add($add){
		$this->start.=$add;
	}
	public function reset(){
		$this->start="";
		$this->end="";
	}
	public function set_type($add){
		$this->type=$add;
	}
	public function output($name="", $inline=true, $encoding=""){
		ob_clean();
		
		if ($this->type == "xml"){
			$header = "Content-type: text/xml";
			if ("" != $encoding)
				$header.="; charset=".$encoding;
			header($header);
		}
			
		echo $this->__toString();
	}
	public function __toString(){
		return $this->start.$this->end;
	}
}

/*! EventInterface
	Base class , for iterable collections, which are used in event
**/
class EventInterface{ 
	protected $request; ////!< DataRequestConfig instance
	public $rules=array(); //!< array of sorting rules
	
	/*! constructor
		creates a new interface based on existing request
		@param request 
			DataRequestConfig object
	*/
	public function __construct($request){
		$this->request = $request;
	}

	/*! remove all elements from collection
		*/	
	public function clear(){
		array_splice($rules,0);
	}
	/*! get index by name
		
		@param name 
			name of field
		@return 
			index of named field
	*/
	public function index($name){
		$len = sizeof($this->rules);
		for ($i=0; $i < $len; $i++) { 
			if ($this->rules[$i]["name"]==$name)
				return $i;
		}
		return false;
	}
}
/*! Wrapper for collection of sorting rules
**/
class SortInterface extends EventInterface{
	/*! constructor
		creates a new interface based on existing request
		@param request 
			DataRequestConfig object
	*/
	public function __construct($request){
		parent::__construct($request);
		$this->rules = &$request->get_sort_by_ref();
	}
	/*! add new sorting rule
		
		@param name 
			name of field
		@param dir
			direction of sorting
	*/
	public function add($name,$dir){
		$this->request->set_sort($name,$dir);
	}
	public function store(){
		$this->request->set_sort_by($this->rules);
	}
}
/*! Wrapper for collection of filtering rules
**/
class FilterInterface extends EventInterface{
	/*! constructor
		creates a new interface based on existing request
		@param request 
			DataRequestConfig object
	*/	
	public function __construct($request){
		$this->request = $request;
		$this->rules = &$request->get_filters_ref();
	}
	/*! add new filatering rule
		
		@param name 
			name of field
		@param value
			value to filter by
		@param rule
			filtering rule
	*/
	public function add($name,$value,$rule){
		$this->request->set_filter($name,$value,$rule);
	}
	public function store(){
		$this->request->set_filters($this->rules);
	}
}

/*! base class for component item representation	
**/
class DataItem{
	protected $data; //!< hash of data
	protected $config;//!< DataConfig instance
	protected $index;//!< index of element
	protected $skip;//!< flag , which set if element need to be skiped during rendering
	protected $userdata;

	/*! constructor
		
		@param data
			hash of data
		@param config
			DataConfig object
		@param index
			index of element
	*/
	function __construct($data,$config,$index){
		$this->config=$config;
		$this->data=$data;
		$this->index=$index;
		$this->skip=false;
		$this->userdata=false;
	}

	//set userdata for the item
	function set_userdata($name, $value){
		if ($this->userdata === false)
			$this->userdata = array();

		$this->userdata[$name]=$value;
	}
	/*! get named value
		
		@param name 
			name or alias of field
		@return 
			value from field with provided name or alias
	*/
	public function get_value($name){
		return $this->data[$name];
	}
	/*! set named value
		
		@param name 
			name or alias of field
		@param value
			value for field with provided name or alias
	*/
	public function set_value($name,$value){
		return $this->data[$name]=$value;
	}
	/*! get id of element
		@return 
			id of element
	*/
	public function get_id(){
		$id = $this->config->id["name"];
		if (array_key_exists($id,$this->data))
			return $this->data[$id];
		return false;
	}
	/*! change id of element
		
		@param value 
			new id value
	*/
	public function set_id($value){
		$this->data[$this->config->id["name"]]=$value;
	}
	/*! get index of element
		
		@return 
			index of element
	*/
	public function get_index(){
		return $this->index;
	}
	/*! mark element for skiping ( such element will not be rendered )
	*/
	public function skip(){
		$this->skip=true;
	}
	
	/*! return self as XML string
	*/
	public function to_xml(){
		return $this->to_xml_start().$this->to_xml_end();
	}
	
	/*! replace xml unsafe characters
		
		@param string 
			string to be escaped
		@return 
			escaped string
	*/
	public function xmlentities($string) { 
   		return str_replace( array( '&', '"', "'", '<', '>', '’' ), array( '&amp;' , '&quot;', '&apos;' , '&lt;' , '&gt;', '&apos;' ), $string);
	}
	
	/*! return starting tag for self as XML string 
	*/
	public function to_xml_start(){
		$str="<item";
		for ($i=0; $i < sizeof($this->config->data); $i++){ 
			$name=$this->config->data[$i]["name"];
			$str.=" ".$name."='".$this->xmlentities($this->data[$name])."'";
		}
		//output custom data
		if ($this->userdata !== false)
			foreach ($this->userdata as $key => $value){
				$str.=" ".$key."='".$this->xmlentities($value)."'";
			}

		return $str.">";
	}
	/*! return ending tag for XML string
	*/
	public function to_xml_end(){
		return "</item>";
	}
}





/*! Base connector class
	This class used as a base for all component specific connectors. 
	Can be used on its own to provide raw data.	
**/
class Connector {
	protected $config;//DataConfig instance
	protected $request;//DataRequestConfig instance
	protected $names;//!< hash of names for used classes
	protected $encoding="utf-8";//!< assigned encoding (UTF-8 by default) 
	protected $editing=false;//!< flag of edit mode ( response for dataprocessor )

	public $model=false;

	private $updating=false;//!< flag of update mode ( response for data-update )
	private $db; //!< db connection resource
	protected $dload;//!< flag of dyn. loading mode
	public $access;  //!< AccessMaster instance
	protected $data_separator = "\n";
	
	public $sql;	//DataWrapper instance
	public $event;	//EventMaster instance
	public $limit=false;
	
	private $id_seed=0; //!< default value, used to generate auto-IDs
	protected $live_update = false; // actions table name for autoupdating
	protected $options = array();
	
	/*! constructor
		
		Here initilization of all Masters occurs, execution timer initialized
		@param db 
			db connection resource
		@param type
			string , which hold type of database ( MySQL or Postgre ), optional, instead of short DB name, full name of DataWrapper-based class can be provided
		@param item_type
			name of class, which will be used for item rendering, optional, DataItem will be used by default
		@param data_type
			name of class which will be used for dataprocessor calls handling, optional, DataProcessor class will be used by default. 
	*/	
	public function __construct($db,$type=false, $item_type=false, $data_type=false, $render_type = false){
		$this->exec_time=microtime(true);

		if (!$type) $type="MySQL";
		if (class_exists($type."DBDataWrapper",false)) $type.="DBDataWrapper";
		if (!$item_type) $item_type="DataItem";
		if (!$data_type) $data_type="DataProcessor";
		if (!$render_type) $render_type="RenderStrategy";
		
		$this->names=array(
			"db_class"=>$type,
			"item_class"=>$item_type,
			"data_class"=>$data_type,
			"render_class"=>$render_type
		);
		$this->attributes = array();
		
		$this->config = new DataConfig();
		$this->request = new DataRequestConfig();
		$this->event = new EventMaster();
		$this->access = new AccessMaster();

		if (!class_exists($this->names["db_class"],false))
			throw new Exception("DB class not found: ".$this->names["db_class"]);
		$this->sql = new $this->names["db_class"]($db,$this->config);
		$this->render = new $this->names["render_class"]($this);
		
		$this->db=$db;//saved for options connectors, if any
		
		EventMaster::trigger_static("connectorCreate",$this);
	}

	/*! return db connection resource
		nested class may neeed to access live connection object
		@return 
			DB connection resource
	*/
	protected function get_connection(){
		return $this->db;
	}

	public function get_config(){
		return new DataConfig($this->config);
	}
	
	public function get_request(){
		return new DataRequestConfig($this->request);
	}


	protected $attributes;
	public function add_top_attribute($name, $string){
		$this->attributes[$name] = $string;
	}

	//model is a class, which will be used for all data operations
	//we expect that it has next methods get, update, insert, delete
	//if method was not defined - we will use default logic
	public function useModel($model){
		$this->model = $model;
	}


	/*! config connector based on table
		
		@param table 
			name of table in DB
		@param id 
			name of id field
		@param fields
			list of fields names
		@param extra
			list of extra fields, optional, such fields will not be included in data rendering, but will be accessible in all inner events
		@param relation_id
			name of field used to define relations for hierarchical data organization, optional
	*/
	public function render_table($table,$id="",$fields=false,$extra=false,$relation_id=false){
		$this->configure($table,$id,$fields,$extra,$relation_id);
		return $this->render();
	}
	public function configure($table,$id="",$fields=false,$extra=false,$relation_id=false){
        if ($fields === false){
            //auto-config
            $info = $this->sql->fields_list($table);
            $fields = implode(",",$info["fields"]);
            if ($info["key"])
                $id = $info["key"];
        }
		$this->config->init($id,$fields,$extra,$relation_id);
		$this->request->set_source($table);
	}
	
	public function uuid(){
		return time()."x".$this->id_seed++;
	}
	
	/*! config connector based on sql
		
		@param sql 
			sql query used as base of configuration
		@param id 
			name of id field
		@param fields
			list of fields names
		@param extra
			list of extra fields, optional, such fields will not be included in data rendering, but will be accessible in all inner events
		@param relation_id
			name of field used to define relations for hierarchical data organization, optional
	*/
	public function render_sql($sql,$id,$fields,$extra=false,$relation_id=false){
		$this->config->init($id,$fields,$extra,$relation_id);
		$this->request->parse_sql($sql);
		return $this->render();
	}

	public function render_array($data, $id, $fields, $extra=false, $relation_id=false){
		$this->configure("-",$id,$fields,$extra,$relation_id);
		$this->sql = new ArrayDBDataWrapper($data, null);
		return $this->render();
	}

	public function render_complex_sql($sql,$id,$fields,$extra=false,$relation_id=false){
		$this->config->init($id,$fields,$extra,$relation_id);
		$this->request->parse_sql($sql, true);
		return $this->render();
	}	
	
	/*! render already configured connector
		
		@param config
			configuration of data
		@param request
			configuraton of request
	*/
	public function render_connector($config,$request){
		$this->config->copy($config);
		$this->request->copy($request);
		return $this->render();
	}	
	
	/*! render self
		process commands, output requested data as XML
	*/	
	public function render(){
        $this->event->trigger("onInit", $this);
		EventMaster::trigger_static("connectorInit",$this);
		
		$this->parse_request();
		$this->set_relation();
		
		if ($this->live_update !== false && $this->updating!==false) {
			$this->live_update->get_updates();
		} else {
			if ($this->editing){
				$dp = new $this->names["data_class"]($this,$this->config,$this->request);
				$dp->process($this->config,$this->request);
			} else {
				if (!$this->access->check("read")){
					LogMaster::log("Access control: read operation blocked");
					echo "Access denied";
					die();
				}
				$wrap = new SortInterface($this->request);
				$this->event->trigger("beforeSort",$wrap);
				$wrap->store();
				
				$wrap = new FilterInterface($this->request);
				$this->event->trigger("beforeFilter",$wrap);
				$wrap->store();
				

				if ($this->model && method_exists($this->model, "get")){
					$this->sql = new ArrayDBDataWrapper();
					$result = new ArrayQueryWrapper(call_user_func(array($this->model, "get"), $this->request));
					$this->output_as_xml($result);
				} else {
					$this->output_as_xml($this->get_resource());
			}

			}
		}
		$this->end_run();
	}


	/*! empty call which used for tree-logic
	 *  to prevent code duplicating
	 */
	protected function set_relation() {}

	/*! gets resource for rendering
	 */
	protected function get_resource() {
		return $this->sql->select($this->request);
	}


	/*! prevent SQL injection through column names
		replace dangerous chars in field names
		@param str 
			incoming field name
		@return 
			safe field name
	*/
	protected function safe_field_name($str){
		return strtok($str, " \n\t;',");
	}
	
	/*! limit max count of records
		connector will ignore any records after outputing max count
		@param limit 
			max count of records
		@return 
			none
	*/
	public function set_limit($limit){
		$this->limit = $limit;
	}
	
	protected function parse_request_mode(){
		//detect edit mode
        if (isset($_GET["editing"])){
			$this->editing=true;
        } else if (isset($_POST["ids"])){
			$this->editing=true;
			LogMaster::log('While there is no edit mode mark, POST parameters similar to edit mode detected. \n Switching to edit mode ( to disable behavior remove POST[ids]');
		} else if (isset($_GET['dhx_version'])){
			$this->updating = true;
        }
	}
	
	/*! parse incoming request, detects commands and modes
	*/
	protected function parse_request(){
		//set default dyn. loading params, can be reset in child classes
		if ($this->dload)
			$this->request->set_limit(0,$this->dload);
		else if ($this->limit)
			$this->request->set_limit(0,$this->limit);
		
		$this->parse_request_mode();

        if ($this->live_update && ($this->updating || $this->editing)){
            $this->request->set_version($_GET["dhx_version"]);
            $this->request->set_user($_GET["dhx_user"]);
        }
		
		if (isset($_GET["dhx_sort"]))
			foreach($_GET["dhx_sort"] as $k => $v){
				$k = $this->safe_field_name($k);
				$this->request->set_sort($this->resolve_parameter($k),$v);
			}
				
		if (isset($_GET["dhx_filter"]))
			foreach($_GET["dhx_filter"] as $k => $v){
				$k = $this->safe_field_name($k);
				$this->request->set_filter($this->resolve_parameter($k),$v);
			}
			
		$key = ConnectorSecurity::checkCSRF($this->editing);
		if ($key !== "")
			$this->add_top_attribute("dhx_security", $key);
		
	}

	/*! convert incoming request name to the actual DB name
		@param name 
			incoming parameter name
		@return 
			name of related DB field
	*/
	protected function resolve_parameter($name){
		return $name;
	}


	/*! replace xml unsafe characters

		@param string
			string to be escaped
		@return
			escaped string
	*/
	protected function xmlentities($string) {
   		return str_replace( array( '&', '"', "'", '<', '>', '’' ), array( '&amp;' , '&quot;', '&apos;' , '&lt;' , '&gt;', '&apos;' ), $string);
	}
    
	public function getRecord($id){
		LogMaster::log("Retreiving data for record: ".$id);
		$source = new DataRequestConfig($this->request);
		$source->set_filter($this->config->id["name"],$id, "=");
		
		$res = $this->sql->select($source);
		
		$temp = $this->data_separator;
		$this->data_separator="";
		$output = $this->render_set($res);
		$this->data_separato=$temp;
		
		return $output;
	}
	
	/*! render from DB resultset
		@param res
			DB resultset 
		process commands, output requested data as XML
	*/
	protected function render_set($res){
		return $this->render->render_set($res, $this->names["item_class"], $this->dload, $this->data_separator, $this->config);
	}
	
	/*! output fetched data as XML
		@param res
			DB resultset 
	*/
	protected function output_as_xml($res){
		$start="<?xml version='1.0' encoding='".$this->encoding."' ?>".$this->xml_start();
		$end=$this->render_set($res).$this->xml_end();
		
		$out = new OutputWriter($start, $end);
		$this->event->trigger("beforeOutput", $this, $out);
		$out->output("", true, $this->encoding);
	}


	/*! end processing
		stop execution timer, kill the process
	*/
	protected function end_run(){
		$time=microtime(true)-$this->exec_time;
		LogMaster::log("Done in {$time}s");
		flush();
		die();
	}
	
	/*! set xml encoding
		
		methods sets only attribute in XML, no real encoding conversion occurs	
		@param encoding 
			value which will be used as XML encoding
	*/
	public function set_encoding($encoding){
		$this->encoding=$encoding;
	}

	/*! enable or disable dynamic loading mode
		
		@param count 
			count of rows loaded from server, actual only for grid-connector, can be skiped in other cases. 
			If value is a false or 0 - dyn. loading will be disabled
	*/
	public function dynamic_loading($count){
		$this->dload=$count;
	}	
		
	/*! enable logging
		
		@param path 
			path to the log file. If set as false or empty strig - logging will be disabled
		@param client_log
			enable output of log data to the client side
	*/
	public function enable_log($path=true,$client_log=false){
		LogMaster::enable_log($path,$client_log);
	}
	
	/*! provides infor about current processing mode
		@return 
			true if processing dataprocessor command, false otherwise
	*/
	public function is_select_mode(){
		$this->parse_request_mode();
		return !$this->editing;
	}
	
	public function is_first_call(){
		$this->parse_request_mode();
		return !($this->editing || $this->updating || $this->request->get_start() || isset($_GET['dhx_no_header']));
		
	}
	
	/*! renders self as  xml, starting part
	*/
	protected function xml_start(){
		$attributes = "";
		foreach($this->attributes as $k=>$v)
			$attributes .= " ".$k."='".$v."'";

		return "<data".$attributes.">";
	}
	/*! renders self as  xml, ending part
	*/
	protected function xml_end(){
		$this->fill_collections();
		return $this->extra_output."</data>";
	}

	protected function fill_collections(){
		foreach ($this->options as $k=>$v) { 
			$name = $k;
			$this->extra_output.="<coll_options for='{$name}'>";
			if (!is_string($this->options[$name]))
				$this->extra_output.=$this->options[$name]->render();
			else
				$this->extra_output.=$this->options[$name];
			$this->extra_output.="</coll_options>";
		}
	}

	/*! assign options collection to the column
		
		@param name 
			name of the column
		@param options
			array or connector object
	*/
	public function set_options($name,$options){
		if (is_array($options)){
			$str="";
			foreach($options as $k => $v)
				$str.="<item value='".$this->xmlentities($k)."' label='".$this->xmlentities($v)."' />";
			$options=$str;
		}
		$this->options[$name]=$options;
	}


	public function insert($data) {
		$action = new DataAction('inserted', false, $data);
		$request = new DataRequestConfig();
		$request->set_source($this->request->get_source());
		
		$this->config->limit_fields($data);
		$this->sql->insert($action,$request);
		$this->config->restore_fields($data);
		
		return $action->get_new_id();
	}
	
	public function delete($id) {
		$action = new DataAction('deleted', $id, array());
		$request = new DataRequestConfig();
		$request->set_source($this->request->get_source());
		
		$this->sql->delete($action,$request);
		return $action->get_status();
}

	public function update($data) {
		$action = new DataAction('updated', $data[$this->config->id["name"]], $data);
		$request = new DataRequestConfig();
		$request->set_source($this->request->get_source());

		$this->config->limit_fields($data);
		$this->sql->update($action,$request);
		$this->config->restore_fields($data);
		
		return $action->get_status();
	}

	/*! sets actions_table for Optimistic concurrency control mode and start it
		@param table_name
			name of database table which will used for saving actions
		@param url
			url used for update notifications
	*/	
	public function enable_live_update($table, $url=false){
		$this->live_update = new DataUpdate($this->sql, $this->config, $this->request, $table,$url);
        $this->live_update->set_event($this->event,$this->names["item_class"]);
		$this->event->attach("beforeOutput", 		Array($this->live_update, "version_output"));
		$this->event->attach("beforeFiltering", 	Array($this->live_update, "get_updates"));
		$this->event->attach("beforeProcessing", 	Array($this->live_update, "check_collision"));
		$this->event->attach("afterProcessing", 	Array($this->live_update, "log_operations"));
	}
}


/*! wrapper around options collection, used for comboboxes and filters
**/
class OptionsConnector extends Connector{
	protected $init_flag=false;//!< used to prevent rendering while initialization
	public function __construct($res,$type=false,$item_type=false,$data_type=false){
		if (!$item_type) $item_type="DataItem";
		if (!$data_type) $data_type=""; //has not sense, options not editable
		parent::__construct($res,$type,$item_type,$data_type);
	}
	/*! render self
		process commands, return data as XML, not output data to stdout, ignore parameters in incoming request
		@return
			data as XML string
	*/	
	public function render(){
		if (!$this->init_flag){
			$this->init_flag=true;
			return "";
		}
		$res = $this->sql->select($this->request);
		return $this->render_set($res);
	}
}



class DistinctOptionsConnector extends OptionsConnector{
	/*! render self
		process commands, return data as XML, not output data to stdout, ignore parameters in incoming request
		@return
			data as XML string
	*/	
	public function render(){
		if (!$this->init_flag){
			$this->init_flag=true;
			return "";
		}
		$res = $this->sql->get_variants($this->config->text[0]["db_name"],$this->request);
		return $this->render_set($res);
	}
}

/*! Connector class for DataView
**/
class ChartConnector extends DataViewConnector{
	public function __construct($res,$type=false,$item_type=false,$data_type=false){
		parent::__construct($res,$type,$item_type,$data_type);
	}
}

class ComboDataItem extends DataItem{
	private $selected;//!< flag of selected option

	function __construct($data,$config,$index){
		parent::__construct($data,$config,$index);
		
		$this->selected=false;
	}
	/*! mark option as selected
	*/
	function select(){
		$this->selected=true;
	}
	/*! return self as XML string, starting part
	*/
	function to_xml_start(){
		if ($this->skip) return "";
		
		return "<option ".($this->selected?"selected='true'":"")."value='".$this->get_id()."'><![CDATA[".$this->data[$this->config->text[0]["name"]]."]]>";
	}
	/*! return self as XML string, ending part
	*/
	function to_xml_end(){
		if ($this->skip) return "";
		return "</option>";
	}
}

/*! Connector for the dhtmlxCombo
**/
class ComboConnector extends Connector{
	private $filter; //!< filtering mask from incoming request
	private $position; //!< position from incoming request

	/*! constructor
		
		Here initilization of all Masters occurs, execution timer initialized
		@param res 
			db connection resource
		@param type
			string , which hold type of database ( MySQL or Postgre ), optional, instead of short DB name, full name of DataWrapper-based class can be provided
		@param item_type
			name of class, which will be used for item rendering, optional, DataItem will be used by default
		@param data_type
			name of class which will be used for dataprocessor calls handling, optional, DataProcessor class will be used by default. 
	*/	
	public function __construct($res,$type=false,$item_type=false,$data_type=false){
		if (!$item_type) $item_type="ComboDataItem";
		parent::__construct($res,$type,$item_type,$data_type);
	}	
	
	//parse GET scoope, all operations with incoming request must be done here
	function parse_request(){
		parent::parse_request();
		
		if (isset($_GET["pos"])){
			if (!$this->dload)	//not critical, so just write a log message
				LogMaster::log("Dyn loading request received, but server side was not configured to process dyn. loading. ");
			else
				$this->request->set_limit($_GET["pos"],$this->dload);
		}
			
		if (isset($_GET["mask"]))
			$this->request->set_filter($this->config->text[0]["db_name"],$_GET["mask"]."%","LIKE");
			
		LogMaster::log($this->request);
	}
	
	
	/*! renders self as  xml, starting part
	*/
	public function xml_start(){
		if ($this->request->get_start())
			return "<complete add='true'>";
		else
			return "<complete>";
	}
	
	/*! renders self as  xml, ending part
	*/
	public function xml_end(){
		return "</complete>";
	}		
}

class ConvertService{
	private $url;
	private $type;
	private $name;
	private $inline;
	
	public function __construct($url){
		$this->url = $url;	
		$this->pdf();
		EventMaster::attach_static("connectorInit",array($this, "handle"));
	}
	public function pdf($name = "data.pdf", $inline = false){
		$this->type = "pdf";
		$this->name = $name;
		$this->inline = $inline;
	}
	public function excel($name = "data.xls", $inline = false){
		$this->type = "excel";
		$this->name = $name;
		$this->inline = $inline;
	}
	public function handle($conn){
		$conn->event->attach("beforeOutput",array($this,"convert"));
	}
	private function as_file($size, $name, $inline){
		header('Content-Type: application/force-download');
		header('Content-Type: application/octet-stream');
		header('Content-Type: application/download');
		header('Content-Transfer-Encoding: binary'); 
		
		header('Content-Length: '.$size);
		if ($inline)
			header('Content-Disposition: inline; filename="'.$name.'";'); 
		else
			header('Content-Disposition: attachment; filename="'.basename($name).'";');
	}	
	public function convert($conn, $out){
		
		if ($this->type == "pdf")
			header("Content-type: application/pdf");
		else
			header("Content-type: application/ms-excel");

		$handle = curl_init($this->url);
		curl_setopt($handle, CURLOPT_POST, true);
		curl_setopt($handle, CURLOPT_HEADER, false);
		curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($handle, CURLOPT_POSTFIELDS, "grid_xml=".urlencode(str_replace("<rows>","<rows profile='color'>", $out)));
		
		
		$out->reset();
		$out->set_type("pdf");
		$out->add(curl_exec($handle));
		$this->as_file(strlen((string)$out), $this->name, $this->inline);
		
		curl_close($handle);
	}
}

class DelayedConnector extends Connector{
	protected $init_flag=false;//!< used to prevent rendering while initialization
	private $data_mode=false;//!< flag to separate xml and data request modes
	private $data_result=false;//<! store results of query
	
	public function dataMode($name){
		$this->data_mode = $name;
		$this->data_result=array();
	}
	public function getDataResult(){
		return $this->data_result;
	}
	
	public function render(){
		if (!$this->init_flag){
			$this->init_flag=true;
			return "";
		}
		return parent::render();
	}
	
	protected function output_as_xml($res){
		if ($this->data_mode){
			while ($data=$this->sql->get_next($res)){
				$this->data_result[]=$data[$this->data_mode];
			}
		}
		else 
			return parent::output_as_xml($res);
	}
	protected function end_run(){
		if (!$this->data_mode)
			parent::end_run();
	}
}
	
class CrossOptionsConnector extends Connector{
	public $options, $link;
	private $master_name, $link_name, $master_value;
	
	public function __construct($res,$type=false,$item_type=false,$data_type=false){
		$this->options = new OptionsConnector($res,$type,$item_type,$data_type);
		$this->link = new DelayedConnector($res,$type,$item_type,$data_type);
		
		EventMaster::attach_static("connectorInit",array($this, "handle"));
	}
	public function handle($conn){
		if ($conn instanceof DelayedConnector) return;
		if ($conn instanceof OptionsConnector) return;
		
		$this->master_name = $this->link->get_config()->id["db_name"];
		$this->link_name = $this->options->get_config()->id["db_name"];
	
		$this->link->event->attach("beforeFilter",array($this, "get_only_related"));
		
		if (isset($_GET["dhx_crosslink_".$this->link_name])){
			$this->get_links($_GET["dhx_crosslink_".$this->link_name]);
			die();
		}
		
		if (!$this->dload){
			$conn->event->attach("beforeRender", array($this, "getOptions"));
			$conn->event->attach("beforeRenderSet", array($this, "prepareConfig"));
		}
		
		
		$conn->event->attach("afterProcessing", array($this, "afterProcessing"));
	}
	public function prepareConfig($conn, $res, $config){
		$config->add_field($this->link_name);
	}
	public function getOptions($data){
		$this->link->dataMode($this->link_name);

		$this->get_links($data->get_value($this->master_name));
		
		$data->set_value($this->link_name, implode(",",$this->link->getDataResult()));
	}
	public function get_links($id){
		$this->master_value = $id;
		$this->link->render();
	}
	public function get_only_related($filters){
		$index = $filters->index($this->master_name);
		if ($index!==false){
			$filters->rules[$index]["value"]=$this->master_value;
		} else
			$filters->add($this->master_name, $this->master_value, "=");
	}
	public function afterProcessing($action){
		$status = $action->get_status();
		
		$master_key = $action->get_value($this->master_name);	
		$link_key = $action->get_value($this->link_name);
		$link_key = explode(',', $link_key);
		
		if ($status == "inserted")
			$master_key = $action->get_new_id();
			
		switch ($status){
			case "deleted":
				$this->link->delete($master_key);
				break;
			case "updated":
				$this->link->delete($master_key);
			case "inserted":
				for ($i=0; $i < sizeof($link_key); $i++)
					if ($link_key[$i]!="")
						$this->link->insert(array(
							$this->link_name => $link_key[$i],
							$this->master_name => $master_key
						));
				break;
		}
	}
}


class JSONCrossOptionsConnector extends CrossOptionsConnector{
	public $options, $link;
	private $master_name, $link_name, $master_value;
	
	public function __construct($res,$type=false,$item_type=false,$data_type=false){
		$this->options = new JSONOptionsConnector($res,$type,$item_type,$data_type);
		$this->link = new DelayedConnector($res,$type,$item_type,$data_type);
		
		EventMaster::attach_static("connectorInit",array($this, "handle"));
	}
}

class CommonDataProcessor extends DataProcessor{
	protected function get_post_values($ids){
		if (isset($_GET['action'])){
			$data = array();
			if (isset($_POST["id"])){
				$dataset = array();
				foreach($_POST as $key=>$value)
					$dataset[$key] = ConnectorSecurity::filter($value);

				$data[$_POST["id"]] = $dataset;
			}
			else
				$data["dummy_id"] = $_POST;
			return $data;
		}
		return parent::get_post_values($ids);
	}
	
	protected function get_ids(){
		if (isset($_GET['action'])){
			if (isset($_POST["id"]))
				return array($_POST['id']);
			else
				return array("dummy_id");
		}
		return parent::get_ids();
	}
	
	protected function get_operation($rid){
		if (isset($_GET['action']))
			return $_GET['action'];
		return parent::get_operation($rid);
	}
	
	public function output_as_xml($results){
		if (isset($_GET['action'])){
			LogMaster::log("Edit operation finished",$results);
			ob_clean();
			$type = $results[0]->get_status();
			if ($type == "error" || $type == "invalid"){
				echo "false";
			} else if ($type=="insert"){
				echo "true\n".$results[0]->get_new_id();
			} else 
				echo "true";
		} else
			return parent::output_as_xml($results);
	}
};

/*! DataItem class for DataView component
**/
class CommonDataItem extends DataItem{
	/*! return self as XML string
	*/
	function to_xml(){
		if ($this->skip) return "";
		return $this->to_xml_start().$this->to_xml_end();
	}

	function to_xml_start(){
		$str="<item id='".$this->get_id()."' ";
		for ($i=0; $i < sizeof($this->config->text); $i++){ 
			$name=$this->config->text[$i]["name"];
			$str.=" ".$name."='".$this->xmlentities($this->data[$name])."'";
		}

		if ($this->userdata !== false)
			foreach ($this->userdata as $key => $value)
				$str.=" ".$key."='".$this->xmlentities($value)."'";

		return $str.">";
	}
}


/*! Connector class for DataView
**/
class DataConnector extends Connector{

	/*! constructor
		
		Here initilization of all Masters occurs, execution timer initialized
		@param res 
			db connection resource
		@param type
			string , which hold type of database ( MySQL or Postgre ), optional, instead of short DB name, full name of DataWrapper-based class can be provided
		@param item_type
			name of class, which will be used for item rendering, optional, DataItem will be used by default
		@param data_type
			name of class which will be used for dataprocessor calls handling, optional, DataProcessor class will be used by default. 
	*/	
	public function __construct($res,$type=false,$item_type=false,$data_type=false,$render_type=false){
		if (!$item_type) $item_type="CommonDataItem";
		if (!$data_type) $data_type="CommonDataProcessor";

		$this->sections = array();

		if (!$render_type) $render_type="RenderStrategy";
		parent::__construct($res,$type,$item_type,$data_type,$render_type);

	}

	protected $sections;
	public function add_section($name, $string){
		$this->sections[$name] = $string;
	}

	protected function parse_request_mode(){
		//do nothing, at least for now
	}
	
	//parse GET scoope, all operations with incoming request must be done here
	protected function parse_request(){
		if (isset($_GET['action'])){
			$action = $_GET['action'];
			//simple request mode
			if ($action == "get"){
				//data request
				if (isset($_GET['id'])){
					//single entity data request
					$this->request->set_filter($this->config->id["name"],$_GET['id'],"=");
				} else {
					//loading collection of items
				}
			} else {
				//data saving
				$this->editing = true;
			}
		} else {
			if (isset($_GET['editing']) && isset($_POST['ids']))
				$this->editing = true;			
			
			parent::parse_request();
		}
	
		if (isset($_GET["start"]) && isset($_GET["count"]))
			$this->request->set_limit($_GET["start"],$_GET["count"]);

	}
	
	/*! renders self as  xml, starting part
	*/
	protected function xml_start(){
		$start = "<data";
		foreach($this->attributes as $k=>$v)
			$start .= " ".$k."='".$v."'";
		$start.= ">";

		foreach($this->sections as $k=>$v)
			$start .= "<".$k.">".$v."</".$k.">\n";
		return $start;
	}
};

class JSONDataConnector extends DataConnector{

	public function __construct($res,$type=false,$item_type=false,$data_type=false){
		if (!$item_type) $item_type="JSONCommonDataItem";
		if (!$data_type) $data_type="CommonDataProcessor";
		$this->data_separator = ",\n";
		parent::__construct($res,$type,$item_type,$data_type);
	}

	/*! assign options collection to the column
		
		@param name 
			name of the column
		@param options
			array or connector object
	*/
	public function set_options($name,$options){
		if (is_array($options)){
			$str=array();
			foreach($options as $k => $v)
				$str[]='{"id":"'.$this->xmlentities($k).'", "value":"'.$this->xmlentities($v).'"}';
			$options=implode(",",$str);
		}
		$this->options[$name]=$options;
	}

	/*! generates xml description for options collections
		
		@param list 
			comma separated list of column names, for which options need to be generated
	*/
	protected function fill_collections(){
		$options = array();
		foreach ($this->options as $k=>$v) { 
			$name = $k;
			$option="\"{$name}\":[";
			if (!is_string($this->options[$name]))
				$option.=substr($this->options[$name]->render(),0,-2);
			else
				$option.=$this->options[$name];
			$option.="]";
			$options[] = $option;
		}
		$this->extra_output .= implode($this->data_separator, $options);
	}

	protected function resolve_parameter($name){
		if (intval($name).""==$name)
			return $this->config->text[intval($name)]["db_name"];
		return $name;
	}

	protected function output_as_xml($res){
		$start = "";
		$end = "{ \"data\":[\n".substr($this->render_set($res),0,-2)."\n]";

		$collections = $this->fill_collections();
		if (!empty($this->extra_output))
			$end .= ', "collections": {'.$this->extra_output.'}';


		$is_sections = sizeof($this->sections) && $this->is_first_call();
		if ($this->dload || $is_sections || sizeof($this->attributes)){
			$start = $start.$end;
			$end="";

			$attributes = "";
			foreach($this->attributes as $k=>$v)
				$end .= ", ".$k.":\"".$v."\"";

			if ($is_sections){
				//extra sections
				foreach($this->sections as $k=>$v)
					$end.= ", ".$k.":".$v;
			}

			if ($this->dload){
				//info for dyn. loadin
				if ($pos=$this->request->get_start())
					$end .= ", \"pos\":".$pos;
				else
					$end .= ", \"pos\":0, \"total_count\":".$this->sql->get_size($this->request);
			}
		}
		$end .= " }";
		$out = new OutputWriter($start, $end);
		$out->set_type("json");
		$this->event->trigger("beforeOutput", $this, $out);
		$out->output("", true, $this->encoding);
	}
}

class JSONCommonDataItem extends DataItem{
	/*! return self as XML string
	*/
	function to_xml(){
		if ($this->skip) return "";
		
		$data = array(
			'id' => $this->get_id()
		);
		for ($i=0; $i<sizeof($this->config->text); $i++){
			$extra = $this->config->text[$i]["name"];
			$data[$extra]=$this->data[$extra];
		}

		if ($this->userdata !== false)
			foreach ($this->userdata as $key => $value)
				$data[$key]=$value;

		return json_encode($data);
	}
}


/*! wrapper around options collection, used for comboboxes and filters
**/
class JSONOptionsConnector extends JSONDataConnector{
	protected $init_flag=false;//!< used to prevent rendering while initialization
	public function __construct($res,$type=false,$item_type=false,$data_type=false){
		if (!$item_type) $item_type="JSONCommonDataItem";
		if (!$data_type) $data_type=""; //has not sense, options not editable
		parent::__construct($res,$type,$item_type,$data_type);
	}
	/*! render self
		process commands, return data as XML, not output data to stdout, ignore parameters in incoming request
		@return
			data as XML string
	*/	
	public function render(){
		if (!$this->init_flag){
			$this->init_flag=true;
			return "";
		}
		$res = $this->sql->select($this->request);
		return $this->render_set($res);
	}
}


class JSONDistinctOptionsConnector extends JSONOptionsConnector{
	/*! render self
		process commands, return data as XML, not output data to stdout, ignore parameters in incoming request
		@return
			data as XML string
	*/	
	public function render(){
		if (!$this->init_flag){
			$this->init_flag=true;
			return "";
		}
		$res = $this->sql->get_variants($this->config->text[0]["db_name"],$this->request);
		return $this->render_set($res);
	}
}



class TreeCommonDataItem extends CommonDataItem{
	protected $kids=-1;

	function to_xml_start(){
		$str="<item id='".$this->get_id()."' ";
		for ($i=0; $i < sizeof($this->config->text); $i++){ 
			$name=$this->config->text[$i]["name"];
			$str.=" ".$name."='".$this->xmlentities($this->data[$name])."'";
		}
		
		if ($this->userdata !== false)
			foreach ($this->userdata as $key => $value)
				$str.=" ".$key."='".$this->xmlentities($value)."'";

		if ($this->kids === true)
			$str .=" dhx_kids='1'";
		
		return $str.">";
	}

	function has_kids(){
		return $this->kids;
	}

	function set_kids($value){
		$this->kids=$value;
	}
}


class TreeDataConnector extends DataConnector{
	protected $parent_name = 'parent';

	/*! constructor
		
		Here initilization of all Masters occurs, execution timer initialized
		@param res 
			db connection resource
		@param type
			string , which hold type of database ( MySQL or Postgre ), optional, instead of short DB name, full name of DataWrapper-based class can be provided
		@param item_type
			name of class, which will be used for item rendering, optional, DataItem will be used by default
		@param data_type
			name of class which will be used for dataprocessor calls handling, optional, DataProcessor class will be used by default. 
	 *	@param render_type
	 *		name of class which will provides data rendering
	*/	
	public function __construct($res,$type=false,$item_type=false,$data_type=false,$render_type=false){
		if (!$item_type) $item_type="TreeCommonDataItem";
		if (!$data_type) $data_type="CommonDataProcessor";
		if (!$render_type) $render_type="TreeRenderStrategy";
		parent::__construct($res,$type,$item_type,$data_type,$render_type);
	}

	//parse GET scoope, all operations with incoming request must be done here
	protected function parse_request(){
		parent::parse_request();

		if (isset($_GET[$this->parent_name]))
			$this->request->set_relation($_GET[$this->parent_name]);
		else
			$this->request->set_relation("0");

		$this->request->set_limit(0,0); //netralize default reaction on dyn. loading mode
	}

	/*! renders self as  xml, starting part
	*/
	protected function xml_start(){
		return "<data parent='".$this->request->get_relation()."'>";
	}	
}


class JSONTreeDataConnector extends TreeDataConnector{

	public function __construct($res,$type=false,$item_type=false,$data_type=false,$render_type){
		if (!$item_type) $item_type="JSONTreeCommonDataItem";
		if (!$data_type) $data_type="CommonDataProcessor";
		if (!$render_type) $render_type="JSONTreeRenderStrategy";
		parent::__construct($res,$type,$item_type,$data_type,$render_type);
	}

	protected function output_as_xml($res){
		$data = array();
		$data["parent"] = $this->request->get_relation();
		$data["data"] = $this->render_set($res);
		$out = new OutputWriter(json_encode($data), "");
		$out->set_type("json");
		$this->event->trigger("beforeOutput", $this, $out);
		$out->output("", true, $this->encoding);
	}

}


class JSONTreeCommonDataItem extends TreeCommonDataItem{
	/*! return self as XML string
	*/
	function to_xml_start(){
		if ($this->skip) return "";
		
		$data = array( "id" => $this->get_id() );
		for ($i=0; $i<sizeof($this->config->text); $i++){
			$extra = $this->config->text[$i]["name"];
			$data[$extra]=$this->data[$extra];
		}

		if ($this->userdata !== false)
			foreach ($this->userdata as $key => $value)
				$data[$key]=$value;

		if ($this->kids === true)
			$data["dhx_kids"] = 1;

		return $data;
	}

	function to_xml_end(){
		return "";
	}
}

class DataProcessor{
	protected $connector;//!< Connector instance
	protected $config;//!< DataConfig instance
	protected $request;//!< DataRequestConfig instance
	static public $action_param ="!nativeeditor_status";

	/*! constructor
		
		@param connector 
			Connector object
		@param config
			DataConfig object
		@param request
			DataRequestConfig object
	*/
	function __construct($connector,$config,$request){
		$this->connector= $connector;
		$this->config=$config;
		$this->request=$request;
	}
	
	/*! convert incoming data name to valid db name
		redirect to Connector->name_data by default
		@param data 
			data name from incoming request
		@return 
			related db_name
	*/
	function name_data($data){
		return $data;
	}
	/*! retrieve data from incoming request and normalize it
		
		@param ids 
			array of extected IDs
		@return 
			hash of data
	*/
	protected function get_post_values($ids){
		$data=array(); 
		for ($i=0; $i < sizeof($ids); $i++)
			$data[$ids[$i]]=array();
		
		foreach ($_POST as $key => $value) {
			$details=explode("_",$key,2);
			if (sizeof($details)==1) continue;
			
			$name=$this->name_data($details[1]);
			$data[$details[0]][$name]=ConnectorSecurity::filter($value);
		}
			
		return $data;
	}
	protected function get_ids(){
		if (!isset($_POST["ids"]))
			throw new Exception("Incorrect incoming data, ID of incoming records not recognized");
		return explode(",",$_POST["ids"]);
	}
	
	protected function get_operation($rid){
		if (!isset($_POST[$rid."_".DataProcessor::$action_param]))
			throw new Exception("Status of record [{$rid}] not found in incoming request");
		return $_POST[$rid."_".DataProcessor::$action_param];
	}
	/*! process incoming request ( save|update|delete )
	*/
	function process(){
		LogMaster::log("DataProcessor object initialized",$_POST);
		
		$results=array();

		$ids=$this->get_ids();
		$rows_data=$this->get_post_values($ids);
		$failed=false;
		
		try{
			if ($this->connector->sql->is_global_transaction())
				$this->connector->sql->begin_transaction();
			
			for ($i=0; $i < sizeof($ids); $i++) { 
				$rid = $ids[$i];
				LogMaster::log("Row data [{$rid}]",$rows_data[$rid]);
				$status = $this->get_operation($rid);
				
				$action=new DataAction($status,$rid,$rows_data[$rid]);
				$results[]=$action;
				$this->inner_process($action);
			}
			
		} catch(Exception $e){
			LogMaster::log($e);
			$failed=true;
		}
		
		if ($this->connector->sql->is_global_transaction()){
			if (!$failed)
				for ($i=0; $i < sizeof($results); $i++)
					if ($results[$i]->get_status()=="error" || $results[$i]->get_status()=="invalid"){
						$failed=true; 
						break;
					}
			if ($failed){
				for ($i=0; $i < sizeof($results); $i++)
					$results[$i]->error();
				$this->connector->sql->rollback_transaction();
			}
			else
				$this->connector->sql->commit_transaction();
		}
		
		$this->output_as_xml($results);
	}	
	
	/*! converts status string to the inner mode name
		
		@param status 
			external status string
		@return 
			inner mode name
	*/
	protected function status_to_mode($status){
		switch($status){
			case "updated":
				return "update";
				break;
			case "inserted":
				return "insert";
				break;
			case "deleted":
				return "delete";
				break;
			default:
				return $status;
				break;
		}
	}
	/*! process data updated request received
		
		@param action 
			DataAction object
		@return 
			DataAction object with details of processing
	*/
	protected function inner_process($action){
		
		if ($this->connector->sql->is_record_transaction())
				$this->connector->sql->begin_transaction();		
		
		try{
				
			$mode = $this->status_to_mode($action->get_status());
			if (!$this->connector->access->check($mode)){
				LogMaster::log("Access control: {$operation} operation blocked");
				$action->error();
			} else {
				$check = $this->connector->event->trigger("beforeProcessing",$action);
				if (!$action->is_ready())
					$this->check_exts($action,$mode);
				$check = $this->connector->event->trigger("afterProcessing",$action);
			}
		
		} catch (Exception $e){
			LogMaster::log($e);
			$action->set_status("error");
			if ($action)
				$this->connector->event->trigger("onDBError", $action, $e);
		}  
		
		if ($this->connector->sql->is_record_transaction()){
			if ($action->get_status()=="error" || $action->get_status()=="invalid")
				$this->connector->sql->rollback_transaction();		
			else
				$this->connector->sql->commit_transaction();		
		}
				
		return $action;
	}
	/*! check if some event intercepts processing, send data to DataWrapper in other case

		@param action 
			DataAction object
		@param mode
			name of inner mode ( will be used to generate event names )
	*/
	function check_exts($action,$mode){
		$old_config = new DataConfig($this->config);
		
		$this->connector->event->trigger("before".$mode,$action);
		if ($action->is_ready())
			LogMaster::log("Event code for ".$mode." processed");
		else {
			//check if custom sql defined
			$sql = $this->connector->sql->get_sql($mode,$action);
			if ($sql){
				$this->connector->sql->query($sql);
			}
			else{
				$action->sync_config($this->config);
				if ($this->connector->model && method_exists($this->connector->model, $mode)){
					call_user_func(array($this->connector->model, $mode), $action);
					LogMaster::log("Model object process action: ".$mode);
				}
				if (!$action->is_ready()){
					$method=array($this->connector->sql,$mode);
					if (!is_callable($method))
						throw new Exception("Unknown dataprocessing action: ".$mode);
					call_user_func($method,$action,$this->request);
				}
			}
		}
		$this->connector->event->trigger("after".$mode,$action);
		
		$this->config = $old_config;
	}
	
	/*! output xml response for dataprocessor

		@param  results
			array of DataAction objects
	*/
	function output_as_xml($results){
		LogMaster::log("Edit operation finished",$results);
		ob_clean();
		header("Content-type:text/xml");
		echo "<?xml version='1.0' ?>";
		echo "<data>";
		for ($i=0; $i < sizeof($results); $i++)
			echo $results[$i]->to_xml();
		echo "</data>";
	}		
	
}

/*! contain all info related to action and controls customizaton
**/
class DataAction{
	private $status; //!< cuurent status of record
	private $id;//!< id of record
	private $data;//!< data hash of record
	private $userdata;//!< hash of extra data , attached to record
	private $nid;//!< new id value , after operation executed
	private $output;//!< custom output to client side code
	private $attrs;//!< hash of custtom attributes
	private $ready;//!< flag of operation's execution
	private $addf;//!< array of added fields
	private $delf;//!< array of deleted fields
	
	
	/*! constructor
		
		@param status 
			current operation status
		@param id
			record id
		@param data
			hash of data
	*/
	function __construct($status,$id,$data){
		$this->status=$status;
		$this->id=$id;
		$this->data=$data;	
		$this->nid=$id;
		
		$this->output="";
		$this->attrs=array();
		$this->ready=false;
		
		$this->addf=array();
		$this->delf=array();
	}

	
	/*! add custom field and value to DB operation
		
		@param name 
			name of field which will be added to DB operation
		@param value
			value which will be used for related field in DB operation
	*/
	function add_field($name,$value){
		LogMaster::log("adding field: ".$name.", with value: ".$value);
		$this->data[$name]=$value;
		$this->addf[]=$name;
	}
	/*! remove field from DB operation
		
		@param name 
			name of field which will be removed from DB operation
	*/
	function remove_field($name){
		LogMaster::log("removing field: ".$name);
		$this->delf[]=$name;
	}
	
	/*! sync field configuration with external object
		
		@param slave 
			SQLMaster object
		@todo 
			check , if all fields removed then cancel action
	*/
	function sync_config($slave){
		foreach ($this->addf as $k => $v)
			$slave->add_field($v);
		foreach ($this->delf as $k => $v)
			$slave->remove_field($v);
	}
	/*! get value of some record's propery
		
		@param name 
			name of record's property ( name of db field or alias )
		@return 
			value of related property
	*/
	function get_value($name){
		if (!array_key_exists($name,$this->data)){
			LogMaster::log("Incorrect field name used: ".$name);
			LogMaster::log("data",$this->data);
			return "";
		}
		return $this->data[$name];
	}
	/*! set value of some record's propery
		
		@param name 
			name of record's property ( name of db field or alias )
		@param value
			value of related property
	*/
	function set_value($name,$value){
		LogMaster::log("change value of: ".$name." as: ".$value);
		$this->data[$name]=$value;
	}
	/*! get hash of data properties
		
		@return 
			hash of data properties
	*/
	function get_data(){
		return $this->data;
	}
	/*! get some extra info attached to record
		deprecated, exists just for backward compatibility, you can use set_value instead of it
		@param name 
			name of userdata property
		@return 
			value of related userdata property
	*/
	function get_userdata_value($name){
		return $this->get_value($name);
	}
	/*! set some extra info attached to record
		deprecated, exists just for backward compatibility, you can use get_value instead of it
		@param name 
			name of userdata property
		@param value
			value of userdata property
	*/
	function set_userdata_value($name,$value){
		return $this->set_value($name,$value);
	}
	/*! get current status of record
		
		@return 
			string with status value
	*/
	function get_status(){
		return $this->status;
	}
	/*! assign new status to the record
		
		@param status 
			new status value
	*/
	function set_status($status){
		$this->status=$status;
	}
   /*! set id
    @param  id
        id value
	*/
	function set_id($id) {
	    $this->id = $id;
	    LogMaster::log("Change id: ".$id);
	}
   /*! set id
    @param  id
        id value
	*/
	function set_new_id($id) {
	    $this->nid = $id;
	    LogMaster::log("Change new id: ".$id);
	}		
	/*! get id of current record
		
		@return 
			id of record
	*/
	function get_id(){
		return $this->id;
	}
	/*! sets custom response text
		
		can be accessed through defineAction on client side. Text wrapped in CDATA, so no extra escaping necessary
		@param text 
			custom response text
	*/
	function set_response_text($text){
		$this->set_response_xml("<![CDATA[".$text."]]>");
	}
	/*! sets custom response xml
		
		can be accessed through defineAction on client side
		@param text
			string with XML data
	*/
	function set_response_xml($text){
		$this->output=$text;
	}
	/*! sets custom response attributes
		
		can be accessed through defineAction on client side
		@param name
			name of custom attribute
		@param value
			value of custom attribute
	*/
	function set_response_attribute($name,$value){
		$this->attrs[$name]=$value;
	}
	/*! check if action finished 
		
		@return 
			true if action finished, false otherwise
	*/
	function is_ready(){
		return $this->ready;
	}	
	/*! return new id value
	
		equal to original ID normally, after insert operation - value assigned for new DB record	
		@return 
			new id value
	*/
	function get_new_id(){
		return $this->nid;
	}
	
	/*! set result of operation as error
	*/
	function error(){
		$this->status="error";
		$this->ready=true;
	}
	/*! set result of operation as invalid
	*/
	function invalid(){
		$this->status="invalid";
		$this->ready=true;
	}
	/*! confirm successful opeation execution
		@param  id
			new id value, optional
	*/
	function success($id=false){
		if ($id!==false)
			$this->nid = $id;
		$this->ready=true;
	}
	/*! convert DataAction to xml format compatible with client side dataProcessor
		@return 
			DataAction operation report as XML string
	*/
	function to_xml(){
		$str="<action type='{$this->status}' sid='{$this->id}' tid='{$this->nid}' ";
		foreach ($this->attrs as $k => $v) {
			$str.=$k."='".$v."' ";
		}
		$str.=">{$this->output}</action>";	
		return $str;
	}
	/*! convert self to string ( for logs )
		
		@return 
			DataAction operation report as plain string 
	*/
	function __toString(){
		return "action:{$this->status}; sid:{$this->id}; tid:{$this->nid};";
	}
	

}

/*! DataItem class for DataView component
**/
class DataViewDataItem extends DataItem{
	/*! return self as XML string
	*/
	function to_xml(){
		if ($this->skip) return "";
		
		$str="<item id='".$this->get_id()."' >";
		for ($i=0; $i<sizeof($this->config->text); $i++){
			$extra = $this->config->text[$i]["name"];
			$str.="<".$extra."><![CDATA[".$this->data[$extra]."]]></".$extra.">";
		}
		return $str."</item>";
	}
}


/*! Connector class for DataView
**/
class DataViewConnector extends Connector{
	
	/*! constructor
		
		Here initilization of all Masters occurs, execution timer initialized
		@param res 
			db connection resource
		@param type
			string , which hold type of database ( MySQL or Postgre ), optional, instead of short DB name, full name of DataWrapper-based class can be provided
		@param item_type
			name of class, which will be used for item rendering, optional, DataItem will be used by default
		@param data_type
			name of class which will be used for dataprocessor calls handling, optional, DataProcessor class will be used by default. 
	*/	
	public function __construct($res,$type=false,$item_type=false,$data_type=false){
		if (!$item_type) $item_type="DataViewDataItem";
		if (!$data_type) $data_type="DataProcessor";
		parent::__construct($res,$type,$item_type,$data_type);
	}

	//parse GET scoope, all operations with incoming request must be done here
	function parse_request(){
		parent::parse_request();

		if (isset($_GET["posStart"]) && isset($_GET["count"]))
			$this->request->set_limit($_GET["posStart"],$_GET["count"]);
	}
	
	/*! renders self as  xml, starting part
	*/
	protected function xml_start(){
		$attributes = "";
		foreach($this->attributes as $k=>$v)
			$attributes .= " ".$k."='".$v."'";

		$start.= ">";
		if ($this->dload){
			if ($pos=$this->request->get_start())
				return "<data pos='".$pos."'".$attributes.">";
			else
				return "<data total_count='".$this->sql->get_size($this->request)."'".$attributes.">";
		}
		else
			return "<data".$attributes.">";
	}	
}

if (!defined('DHX_IGNORE_EMPTY_ROWS')) {
	define('DHX_IGNORE_EMPTY_ROWS', true);
}

class ExcelDBDataWrapper extends DBDataWrapper {
	
	public $emptyLimit = 10;
	public function excel_data($points){
		$path = $this->connection;
		$excel = PHPExcel_IOFactory::createReaderForFile($path);
		$excel = $excel->load($path);
		$result = array();
		$excelWS = $excel->getActiveSheet();
		
		for ($i=0; $i < sizeof($points); $i++) { 
			$c = array();
			preg_match("/^([a-zA-Z]+)(\d+)/", $points[$i], $c);
			if (count($c) > 0) {
				$col = PHPExcel_Cell::columnIndexFromString($c[1]) - 1;
				$cell = $excelWS->getCellByColumnAndRow($col, (int)$c[2]);
				$result[] = $cell->getValue();
			}
		}
		
		return $result;
	}
	public function select($source) {
		$path = $this->connection;
		$excel = PHPExcel_IOFactory::createReaderForFile($path);
		$excel = $excel->load($path);
		$excRes = new ExcelResult();
		$excelWS = $excel->getActiveSheet();
		$addFields = true;

		$coords = array();
		if ($source->get_source() == '*') {
			$coords['start_row'] = 0;
			$coords['end_row'] = false;
		} else {
			$c = array();
			preg_match("/^([a-zA-Z]+)(\d+)/", $source->get_source(), $c);
			if (count($c) > 0) {
				$coords['start_row'] = (int) $c[2];
			} else {
				$coords['start_row'] = 0;
			}
			$c = array();
			preg_match("/:(.+)(\d+)$/U", $source->get_source(), $c);
			if (count($c) > 0) {
				$coords['end_row'] = (int) $c[2];
			} else {
				$coords['end_row'] = false;
			}
		}

		$i = $coords['start_row'];
		$end = 0;
		while ((($coords['end_row'] == false)&&($end < $this->emptyLimit))||(($coords['end_row'] !== false)&&($i < $coords['end_row']))) {
			$r = Array();
			$emptyNum = 0;
			for ($j = 0; $j < count($this->config->text); $j++) {
				$col = PHPExcel_Cell::columnIndexFromString($this->config->text[$j]['name']) - 1;
				$cell = $excelWS->getCellByColumnAndRow($col, $i);
				if ($cell->getDataType() == 'f') {
					$r[PHPExcel_Cell::stringFromColumnIndex($col)] = $cell->getCalculatedValue();
				} else {
					$r[PHPExcel_Cell::stringFromColumnIndex($col)] = $cell->getValue();
				}
				if ($r[PHPExcel_Cell::stringFromColumnIndex($col)] == '') {
					$emptyNum++;
				}
			}
			if ($emptyNum < count($this->config->text)) {
				$r['id'] = $i;
				$excRes->addRecord($r);
				$end = 0;
			} else {
				if (DHX_IGNORE_EMPTY_ROWS == false) {
					$r['id'] = $i;
					$excRes->addRecord($r);
				}
				$end++;
			}
			$i++;
		}
		return $excRes;
	}

	protected function query($sql) {
	}

	protected function get_new_id() {
	}

	public function escape($data) {
	}	

	public function get_next($res) {
		return $res->next();
	}

}


class ExcelResult {
	private $rows;
	private $currentRecord = 0;


	// add record to output list
	public function addRecord($file) {
		$this->rows[] = $file;
	}


	// return next record
	public function next() {
		if ($this->currentRecord < count($this->rows)) {
			$row = $this->rows[$this->currentRecord];
			$this->currentRecord++;
			return $row;
		} else {
			return false;
		}
	}


	// sorts records under $sort array
	public function sort($sort, $data) {
		if (count($this->files) == 0) {
			return $this;
		}
		// defines fields list if it's need
		for ($i = 0; $i < count($sort); $i++) {
			$fieldname = $sort[$i]['name'];
			if (!isset($this->files[0][$fieldname])) {
				if (isset($data[$fieldname])) {
					$fieldname = $data[$fieldname]['db_name'];
					$sort[$i]['name'] = $fieldname;
				} else {
					$fieldname = false;
				}
			}
		}

		// for every sorting field will sort
		for ($i = 0; $i < count($sort); $i++) {
			// if field, setted in sort parameter doesn't exist, continue
			if ($sort[$i]['name'] == false) {
				continue;
			}
			// sorting by current field
			$flag = true;
			while ($flag == true) {
				$flag = false;
				// checks if previous sorting fields are equal
				for ($j = 0; $j < count($this->files) - 1; $j++) {
					$equal = true;
					for ($k = 0; $k < $i; $k++) {
						if ($this->files[$j][$sort[$k]['name']] != $this->files[$j + 1][$sort[$k]['name']]) {
							$equal = false;
						}
					}
					// compares two records in list under current sorting field and sorting direction
					if (((($this->files[$j][$sort[$i]['name']] > $this->files[$j + 1][$sort[$i]['name']])&&($sort[$i]['direction'] == 'ASC'))||(($this->files[$j][$sort[$i]['name']] < $this->files[$j + 1][$sort[$i]['name']])&&($sort[$i]['direction'] == 'DESC')))&&($equal == true)) {
						$c = $this->files[$j];
						$this->files[$j] = $this->files[$j+1];
						$this->files[$j+1] = $c;
						$flag = true;
					}
				}
			}
		}
		return $this;
	}

}

class FileSystemDBDataWrapper extends DBDataWrapper {


	// returns list of files and directories
	public function select($source) {
		$relation = $this->getFileName($source->get_relation());
		// for tree checks relation id and forms absolute path
		if ($relation == '0') {
			$relation = '';
		} else {
			$path = $source->get_source();
		}
		$path = $source->get_source();
		$path = $this->getFileName($path);
		$path = realpath($path);
		if ($path == false) {
			return new FileSystemResult();
		}
		
		if (strpos(realpath($path.'/'.$relation), $path) !== 0) {
			return new FileSystemResult();
		}
		// gets files and directories list
		$res = $this->getFilesList($path, $relation);
		// sorts list
		$res = $res->sort($source->get_sort_by(), $this->config->data);
		return $res;
	}


	// gets files and directory list
	private function getFilesList($path, $relation) {
		$fileSystemTypes = FileSystemTypes::getInstance();
		LogMaster::log("Query filesystem: ".$path);
		$dir = opendir($path.'/'.$relation);
		$result = new FileSystemResult();
		// forms fields list
		for ($i = 0; $i < count($this->config->data); $i++) {
			$fields[] = $this->config->data[$i]['db_name'];
		}
		// for every file and directory of folder
		while ($file = readdir($dir)) {
			// . and .. should not be in output list
			if (($file == '.')||($file == '..')) {
				continue;
			}
			$newFile = array();
			// parse file name as Array('name', 'ext', 'is_dir')
			$fileNameExt = $this->parseFileName($path.'/'.$relation, $file);
			// checks if file should be in output array
			if (!$fileSystemTypes->checkFile($file, $fileNameExt)) {
				continue;
			}
			// takes file stat if it's need
			if ((in_array('size', $fields))||(in_array('date', $fields))) {
				$fileInfo = stat($path.'/'.$file);
			}

			// for every field forms list of fields
			for ($i = 0; $i < count($fields); $i++) {
				$field = $fields[$i];
				switch ($field) {
					case 'filename':
						$newFile['filename'] = $file;
						break;
					case 'full_filename':
						$newFile['full_filename'] = $path."/".$file;
						break;
					case 'size':
						$newFile['size'] = $fileInfo['size'];
						break;
					case 'extention':
						$newFile['extention'] = $fileNameExt['ext'];
						break;
					case 'name':
						$newFile['name'] = $fileNameExt['name'];
						break;
					case 'date':
						$newFile['date'] = date("Y-m-d H:i:s", $fileInfo['ctime']);
						break;
				}
				$newFile['relation_id'] = $relation.'/'.$file;
				$newFile['safe_name'] = $this->setFileName($relation.'/'.$file);
				$newFile['is_folder'] = $fileNameExt['is_dir'];
			}
			// add file in output list
			$result->addFile($newFile);
		}
		return $result;
	}


	// replaces '.' and '_' in id
	private function setFileName($filename) {
		$filename = str_replace(".", "{-dot-}", $filename);
		$filename = str_replace("_", "{-nizh-}", $filename);
		return $filename;
	}

	
	// replaces '{-dot-}' and '{-nizh-}' in id
	private function getFileName($filename) {
		$filename =  str_replace("{-dot-}", ".", $filename);
		$filename = str_replace("{-nizh-}", "_", $filename);
		return $filename;
	}
	

	// parses file name and checks if is directory
	private function parseFileName($path, $file) {
		$result = Array();
		if (is_dir($path.'/'.$file)) {
			$result['name'] = $file;
			$result['ext'] = 'dir';
			$result['is_dir'] = 1;
		} else {
			$pos = strrpos($file, '.');
			$result['name'] = substr($file, 0, $pos);
			$result['ext'] = substr($file, $pos + 1);
			$result['is_dir'] = 0;
		}
		return $result;
	}

	protected function query($sql) {
	}

	protected function get_new_id() {
	}

	public function escape($data) {
	}	

	public function get_next($res) {
		return $res->next();
	}

}


class FileSystemResult {
	private $files;
	private $currentRecord = 0;


	// add record to output list
	public function addFile($file) {
		$this->files[] = $file;
	}
	
	
	// return next record
	public function next() {
		if ($this->currentRecord < count($this->files)) {
			$file = $this->files[$this->currentRecord];
			$this->currentRecord++;
			return $file;
		} else {
			return false;
		}
	}


	// sorts records under $sort array
	public function sort($sort, $data) {
		if (count($this->files) == 0) {
			return $this;
		}
		// defines fields list if it's need
		for ($i = 0; $i < count($sort); $i++) {
			$fieldname = $sort[$i]['name'];
			if (!isset($this->files[0][$fieldname])) {
				if (isset($data[$fieldname])) {
					$fieldname = $data[$fieldname]['db_name'];
					$sort[$i]['name'] = $fieldname;
				} else {
					$fieldname = false;
				}
			}
		}
		
		// for every sorting field will sort
		for ($i = 0; $i < count($sort); $i++) {
			// if field, setted in sort parameter doesn't exist, continue
			if ($sort[$i]['name'] == false) {
				continue;
			}
			// sorting by current field
			$flag = true;
			while ($flag == true) {
				$flag = false;
				// checks if previous sorting fields are equal
				for ($j = 0; $j < count($this->files) - 1; $j++) {
					$equal = true;
					for ($k = 0; $k < $i; $k++) {
						if ($this->files[$j][$sort[$k]['name']] != $this->files[$j + 1][$sort[$k]['name']]) {
							$equal = false;
						}
					}
					// compares two records in list under current sorting field and sorting direction
					if (((($this->files[$j][$sort[$i]['name']] > $this->files[$j + 1][$sort[$i]['name']])&&($sort[$i]['direction'] == 'ASC'))||(($this->files[$j][$sort[$i]['name']] < $this->files[$j + 1][$sort[$i]['name']])&&($sort[$i]['direction'] == 'DESC')))&&($equal == true)) {
						$c = $this->files[$j];
						$this->files[$j] = $this->files[$j+1];
						$this->files[$j+1] = $c;
						$flag = true;
					}
				}
			}
		}
		return $this;
	}

}


// singleton class for setting file types filter
class FileSystemTypes {

	static private $instance = NULL;
	private $extentions = Array();
	private $extentions_not = Array();
	private $all = true;
	private $patterns = Array();
	// predefined types
	private $types = Array(
		'image' => Array('jpg', 'jpeg', 'gif', 'png', 'tiff', 'bmp', 'psd', 'dir'),
		'document' => Array('txt', 'doc', 'docx', 'xls', 'xlsx', 'rtf', 'dir'),
		'web' => Array('php', 'html', 'htm', 'js', 'css', 'dir'),
		'audio' => Array('mp3', 'wav', 'ogg', 'dir'),
		'video' => Array('avi', 'mpg', 'mpeg', 'mp4', 'dir'),
		'only_dir' => Array('dir')
		);


	static function getInstance() {
		if (self::$instance == NULL) {
			self::$instance = new FileSystemTypes();
		}
		return self::$instance;
	}

	// sets array of extentions
	public function setExtentions($ext) {
		$this->all = false;
		$this->extentions = $ext;
	}

	// adds one extention in array
	public function addExtention($ext) {
		$this->all = false;
		$this->extentions[] = $ext;
	}

	
	// adds one extention which will not ouputed in array
	public function addExtentionNot($ext) {
		$this->extentions_not[] = $ext;
	}
	
	
	// returns array of extentions
	public function getExtentions() {
		return $this->extentions;
	}

	// adds regexp pattern
	public function addPattern($pattern) {
		$this->all = false;
		$this->patterns[] = $pattern;
	}

	// clear extentions array
	public function clearExtentions() {
		$this->all = true;
		$this->extentions = Array();
	}

	// clear regexp patterns array
	public function clearPatterns() {
		$this->all = true;
		$this->patterns = Array();
	}

	// clear all filters
	public function clearAll() {
		$this->clearExtentions();
		$this->clearPatterns();
	}

	// sets predefined type
	public function setType($type, $clear = false) {
		$this->all = false;
		if ($type == 'all') {
			$this->all = true;
			return true;
		}
		if (isset($this->types[$type])) {
			if ($clear) {
				$this->clearExtentions();
			}
			for ($i = 0; $i < count($this->types[$type]); $i++) {
				$this->extentions[] = $this->types[$type][$i];
			}
			return true;
		} else {
			return false;
		}
	}


	// check file under setted filter
	public function checkFile($filename, $fileNameExt) {
		if (in_array($fileNameExt['ext'], $this->extentions_not)) {
			return false;
		}
		if ($this->all) {
			return true;
		}

		if ((count($this->extentions) > 0)&&(!in_array($fileNameExt['ext'], $this->extentions))) {
			return false;
		}

		for ($i = 0; $i < count($this->patterns); $i++) {
			if (!preg_match($this->patterns[$i], $filename)) {
				return false;
			}
		}
		return true;
	}
}

class PDODBDataWrapper extends DBDataWrapper{
	private $last_result;//!< store result or last operation
	
	public function query($sql){
		LogMaster::log($sql);
		
		$res=$this->connection->query($sql);
		if ($res===false) {
			$message = $this->connection->errorInfo();
			throw new Exception("PDO - sql execution failed\n".$message[2]);
		}
		
		return new PDOResultSet($res);
	}

	protected function select_query($select,$from,$where,$sort,$start,$count){
		if (!$from)
			return $select;
			
		$sql="SELECT ".$select." FROM ".$from;
		if ($where) $sql.=" WHERE ".$where;
		if ($sort) $sql.=" ORDER BY ".$sort;
		if ($start || $count) {
			if ($this->connection->getAttribute(PDO::ATTR_DRIVER_NAME)=="pgsql")
				$sql.=" OFFSET ".$start." LIMIT ".$count;
			else
				$sql.=" LIMIT ".$start.",".$count;
		}
		return $sql;
	}
	
		
	public function get_next($res){
		$data = $res->next();
		return $data;
	}
	
	protected function get_new_id(){
		return $this->connection->lastInsertId();
	}
	
	public function escape($str){
		$res=$this->connection->quote($str);
		if ($res===false) //not supported by pdo driver
			return str_replace("'","''",$str); 
		return substr($res,1,-1);
	}
	
}

class PDOResultSet{
	private $res;
	public function __construct($res){
		$this->res = $res;
	}
	public function next(){
		$data = $this->res->fetch(PDO::FETCH_ASSOC);
		if (!$data){
			$this->res->closeCursor();
			return null;
		}
		return $data;
	}	
}

class FileTreeDataItem extends TreeDataItem {

	function has_kids(){
		if ($this->data['is_folder'] == '1') {
			return true;
		} else {
			return false;
		}
	}

}

/*! DataItem class for dhxForm component
**/
class FormDataItem extends DataItem{
	/*! return self as XML string
	*/
	function to_xml(){
		if ($this->skip) return "";
		$str="";
		for ($i = 0; $i < count($this->config->data); $i++) {
			$str .= "<".$this->config->data[$i]['name']."><![CDATA[".$this->data[$this->config->data[$i]['name']]."]]></".$this->config->data[$i]['name'].">";
		}
		return $str;
	}
}


/*! Connector class for dhtmlxForm
**/
class FormConnector extends Connector{
	
	/*! constructor
		
		Here initilization of all Masters occurs, execution timer initialized
		@param res 
			db connection resource
		@param type
			string , which hold type of database ( MySQL or Postgre ), optional, instead of short DB name, full name of DataWrapper-based class can be provided
		@param item_type
			name of class, which will be used for item rendering, optional, DataItem will be used by default
		@param data_type
			name of class which will be used for dataprocessor calls handling, optional, DataProcessor class will be used by default. 
	*/	
	public function __construct($res,$type=false,$item_type=false,$data_type=false){
		if (!$item_type) $item_type="FormDataItem";
		if (!$data_type) $data_type="FormDataProcessor";
		parent::__construct($res,$type,$item_type,$data_type);
	}

	//parse GET scoope, all operations with incoming request must be done here
	function parse_request(){
		parent::parse_request();
		if (isset($_GET["id"]))
			$this->request->set_filter($this->config->id["name"],$_GET["id"],"=");
		else if (!$_POST["ids"])
			throw new Exception("ID parameter is missed");
	}

}

/*! DataProcessor class for dhxForm component
**/
class FormDataProcessor extends DataProcessor{

}

class GridConfiguration{
	
	/*! attaching header functionality
	*/
	protected $headerDelimiter = ',';
	protected $headerNames = false;
	protected $headerAttaches = array();
	protected $footerAttaches = array();
	protected $headerWidthsUnits = 'px';
	
	protected $headerIds = false;
    protected $headerWidths = false;
    protected $headerTypes = false;
	protected $headerAlign  = false; 
	protected $headerVAlign = false;
	protected $headerSorts  = false;
	protected $headerColors = false;
	protected $headerHidden = false;
	protected $headerFormat = false;
	
	protected $convert_mode = false;
	
	function __construct($headers = false){
	 	if ($headers === false || $headers === true )
			$this->headerNames = $headers;
		else
			$this->setHeader($headers);
	}

	/*! brief convert list of parameters to an array
		@param param 
			list of values or array of values
		@return array of parameters
	*/
	private function parse_param_array($param, $check=false, $default = ""){
		if (gettype($param) == 'string')
			$param = explode($this->headerDelimiter, $param);
				
		if ($check){
			for ($i=0; $i < sizeof($param); $i++) { 
				if (!array_key_exists($param[$i],$check))
					$param[$i] = $default;
			}
		}
		return $param;
	}
	
	/*! sets delimiter for string arguments in attach header functions (default is ,)
		@param headerDelimiter
			string delimiter
	*/
	public function setHeaderDelimiter($headerDelimiter) {
		$this->headerDelimiter = $headerDelimiter;
	}

	/*! sets header
		@param names
		 array of names or string of names, delimited by headerDelimiter (default is ,)
	*/
	public function setHeader($names) {
		if ($names instanceof DataConfig){
			$out = array();
			for ($i=0; $i < sizeof($names->text); $i++)
				$out[]=$names->text[$i]["name"];
			$names = $out;
		}
				
		$this->headerNames = $this->parse_param_array($names);
	}

	/*! sets init columns width in pixels
		@param wp
			array of widths or string of widths, delimited by headerDelimiter (default is ,)
	*/
	public function setInitWidths($wp) {
		$this->headerWidths = $this->parse_param_array($wp);
		$this->headerWidthsUnits = 'px';
	}

	/*! sets init columns width in persents
		@param wp
			array of widths or string of widths, delimited by headerDelimiter (default is ,)
	*/
	public function setInitWidthsP($wp) {
		$this->setInitWidths($wp);
		$this->headerWidthsUnits = '%';
	}

	/*! sets columns align
		@param alStr
			array of aligns or string of aligns, delimited by headerDelimiter (default is ,)
	*/
	public function setColAlign($alStr) {
		$this->headerAlign = $this->parse_param_array($alStr,
			array("right"=>1, "left"=>1, "center"=>1, "justify"=>1),
			"left");
	}

	/*! sets columns vertical align
		@param alStr
			array of vertical aligns or string of vertical aligns, delimited by headerDelimiter (default is ,)
	*/
	public function setColVAlign($alStr) {
		$this->headerVAlign = $this->parse_param_array($alStr,
			array("baseline"=>1, "sub"=>1, "super"=>1, "top"=>1, "text-top"=>1, "middle"=>1, "bottom"=>1, "text-bottom"=>1),
			"top");
	}

	/*! sets column types
		@param typeStr
			array of types or string of types, delimited by headerDelimiter (default is ,)
	*/
	public function setColTypes($typeStr) {
		$this->headerTypes = $this->parse_param_array($typeStr);
	}

	/*! sets columns sorting
		@param sortStr
			array if sortings or string of sortings, delimited by headerDelimiter (default is ,)
	*/
	public function setColSorting($sortStr) {
		$this->headerSorts = $this->parse_param_array($sortStr);
	}

	/*! sets columns colors
		@param colorStr
			array of colors or string of colors, delimited by headerDelimiter (default is ,)
			if (color should not be applied it's value should be null)
	*/
	public function setColColor($colorStr) {
		$this->headerColors = $this->parse_param_array($colorStr);
	}

	/*! sets hidden columns
		@param hidStr
			array of bool values or string of bool values, delimited by headerDelimiter (default is ,)
	*/
	public function setColHidden($hidStr) {
		$this->headerHidden = $this->parse_param_array($hidStr);
	}

	/*! sets columns id
		@param idsStr
			array of ids or string of ids, delimited by headerDelimiter (default is ,)
	*/
	public function setColIds($idsStr) {
		$this->headerIds = $this->parse_param_array($idsStr);
	}

	/*! sets number/date format
		@param formatArr
			array of mask formats for number/dates , delimited by headerDelimiter (default is ,)
	*/
	public function setColFormat($formatArr) {
		$this->headerFormat = $this->parse_param_array($formatArr);
	}

	/*! attaches header
		@param values
			array of header names or string of header names, delimited by headerDelimiter (default is ,)
		@param styles
			array of header styles or string of header styles, delimited by headerDelimiter (default is ,)
	*/
	public function attachHeader($values, $styles = null, $footer = false) {
		$header = array();
		$header['values'] = $this->parse_param_array($values);
		if ($styles != null) {
			$header['styles'] = $this->parse_param_array($styles);
		} else {
			$header['styles'] = null;
		}
		if ($footer)
			$this->footerAttaches[] = $header;
		else
			$this->headerAttaches[] = $header;
	}

	/*! attaches footer
		@param values
			array of footer names or string of footer names, delimited by headerDelimiter (default is ,)
		@param styles
			array of footer styles or string of footer styles, delimited by headerDelimiter (default is ,)
	*/
	public function attachFooter($values, $styles = null) {
		$this->attachHeader($values, $styles, true);
	}
	
	private function auto_fill($mode){
		$headerWidths = array();
		$headerTypes = array();
		$headerSorts = array();
		$headerAttaches = array();
		
		for ($i=0; $i < sizeof($this->headerNames); $i++) { 
			$headerWidths[] = 100;
			$headerTypes[] = "ro";
			$headerSorts[] = "connector";
			$headerAttaches[] = "#connector_text_filter";
		}
		if ($this->headerWidths == false)
			$this->setInitWidths($headerWidths);
		if ($this->headerTypes == false)
			$this->setColTypes($headerTypes);
			
		if ($mode){
			if ($this->headerSorts == false)
				$this->setColSorting($headerSorts);
			$this->attachHeader($headerAttaches);
		}
	}

    public function defineOptions($conn){ 
        if (!$conn->is_first_call()) return; //render head only for first call

		$config = $conn->get_config();
		$full_header = ($this->headerNames === true);
		
		if (gettype($this->headerNames) == 'boolean') //auto-config
			$this->setHeader($config);
		$this->auto_fill($full_header);
		
        if (isset($_GET["dhx_colls"])) return;

        $fillList = array();
        for ($i = 0; $i < count($this->headerNames); $i++)
            if ($this->headerTypes[$i] == "co" || $this->headerTypes[$i] == "coro")
                $fillList[$i] = true;

        for ($i = 0; $i < count($this->headerAttaches); $i++) {
			for ($j = 0; $j < count($this->headerAttaches[$i]['values']); $j++) {
				if ($this->headerAttaches[$i]['values'][$j] == "#connector_select_filter"
                        || $this->headerAttaches[$i]['values'][$j] == "#select_filter") {
					$fillList[$j] =  true;;
				}
			}
        }
        
        $temp = array();
        foreach($fillList as $k => $v)
            $temp[] = $k;
        if (count($temp))
        	$_GET["dhx_colls"] = implode(",",$temp);
    }


	/*! gets header as array
	 */
	private function getHeaderArray() {
		$head = Array();
		$head[0] = $this->headerNames;
		$head = $this->getAttaches($head, $this->headerAttaches);
		return $head;
	}


	/*! get footer as array
	 */
	private function getFooterArray() {
		$foot = Array();
		$foot = $this->getAttaches($foot, $this->footerAttaches);
		return $foot;
	}


	/*! gets array of data with attaches
	 */
	private function getAttaches($to, $from) {
		for ($i = 0; $i < count($from); $i++) {
			$line = $from[$i]['values'];
			$to[] = $line;
		}
		return $to;
	}


	/*! calculates rowspan array according #cspan markers
	 */
	private function processCspan($data) {
		$rspan = Array();
		for ($i = 0; $i < count($data); $i++) {
			$last = 0;
			$rspan[$i] = Array();
			for ($j = 0; $j < count($data[$i]); $j++) {
				$rspan[$i][$j] = 0;
				if ($data[$i][$j] === '#cspan') {
					$rspan[$i][$last]++;
				} else {
					$last = $j;
				}
			}
		}
		return $rspan;
	}


	/*! calculates colspan array according #rspan markers
	 */
	private function processRspan($data) {
		$last = Array();
		$cspan = Array();
		for ($i = 0; $i < count($data); $i++) {
			$cspan[$i] = Array();
			for ($j = 0; $j < count($data[$i]); $j++) {
				$cspan[$i][$j] = 0;
				if (!isset($last[$j])) $last[$j] = 0;
				if ($data[$i][$j] === '#rspan') {
					$cspan[$last[$j]][$j]++;
				} else {
					$last[$j] = $i;
				}
			}
		}
		return $cspan;
	}
	
	
	/*! sets mode of output format: usual mode or convert mode.
	 *	@param mode
	 *		true - convert mode, false - otherwise
	 */
	public function set_convert_mode($mode) {
		$this->convert_mode = $mode;
	}


	/*! adds header configuration in output XML
	*/
	public function attachHeaderToXML($conn, $out) {
        if (!$conn->is_first_call()) return; //render head only for first call

		$head = $this->getHeaderArray();
		$foot = $this->getFooterArray();
		$rspan = $this->processRspan($head);
		$cspan = $this->processCspan($head);

		$str = '<head>';

		if ($this->convert_mode) $str .= "<columns>";

		for ($i = 0; $i < count($this->headerNames); $i++) {
			$str .= '<column';
			$str .= ' type="'. $this->headerTypes[$i].'"';
			$str .= ' width="'.$this->headerWidths[$i].'"';
			$str .= $this->headerIds  ? ' id="'.$this->headerIds[$i].'"' : '';
			$str .= $this->headerAlign[$i]  ? ' align="'.$this->headerAlign[$i].'"' : '';
			$str .= $this->headerVAlign[$i] ? ' valign="'.$this->headerVAlign[$i].'"' : '';
			$str .= $this->headerSorts[$i]  ? ' sort="'.$this->headerSorts[$i].'"' : '';
			$str .= $this->headerColors[$i] ? ' color="'.$this->headerColors[$i].'"' : '';
			$str .= $this->headerHidden[$i] ? ' hidden="'.$this->headerHidden[$i].'"' : '';
			$str .= $this->headerFormat[$i] ? ' format="'.$this->headerFormat[$i].'"' : '';
			$str .= $cspan[0][$i] ? ' colspan="'.($cspan[0][$i] + 1).'"' : '';
			$str .= $rspan[0][$i] ? ' rowspan="'.($rspan[0][$i] + 1).'"' : '';
			$str .= '>'.$this->headerNames[$i].'</column>';
		}
		
		if (!$this->convert_mode) {
			$str .= '<settings><colwidth>'.$this->headerWidthsUnits.'</colwidth></settings>';
			if ((count($this->headerAttaches) > 0)||(count($this->footerAttaches) > 0)) {
				$str .= '<afterInit>';
			}
			for ($i = 0; $i < count($this->headerAttaches); $i++) {
				$str .= '<call command="attachHeader">';
				$str .= '<param>'.implode(",",$this->headerAttaches[$i]['values']).'</param>';
				if ($this->headerAttaches[$i]['styles'] != null) {
					$str .= '<param>'.implode(",",$this->headerAttaches[$i]['styles']).'</param>';
				}
				$str .= '</call>';
			}
			for ($i = 0; $i < count($this->footerAttaches); $i++) {
				$str .= '<call command="attachFooter">';
				$str .= '<param>'.implode(",",$this->footerAttaches[$i]['values']).'</param>';
				if ($this->footerAttaches[$i]['styles'] != null) {
					$str .= '<param>'.implode(",",$this->footerAttaches[$i]['styles']).'</param>';
				}
				$str .= '</call>';
			}
			if ((count($this->headerAttaches) > 0)||(count($this->footerAttaches) > 0)) {
				$str .= '</afterInit>';
			}
		} else {
			$str .= "</columns>";
			for ($i = 1; $i < count($head); $i++) {
				$str .= "<columns>";
				for ($j = 0; $j < count($head[$i]); $j++) {
					$str .= '<column';
					$str .= $cspan[$i][$j] ? ' colspan="'.($cspan[$i][$j] + 1).'"' : '';
					$str .= $rspan[$i][$j] ? ' rowspan="'.($rspan[$i][$j] + 1).'"' : '';
					$str .= '>'.$head[$i][$j].'</column>';
				}
				$str .= "</columns>\n";
			}
		}
		$str .= '</head>';
		
		
		if ($this->convert_mode && count($foot) > 0) {
			$rspan = $this->processRspan($foot);
			$cspan = $this->processCspan($foot);
			$str .= "<foot>";
			for ($i = 0; $i < count($foot); $i++) {
				$str .= "<columns>";
				for ($j = 0; $j < count($foot[$i]); $j++) {
					$str .= '<column';
					$str .= $cspan[$i][$j] ? ' colspan="'.($cspan[$i][$j] + 1).'"' : '';
					$str .= $rspan[$i][$j] ? ' rowspan="'.($rspan[$i][$j] + 1).'"' : '';
					$str .= '>'.$foot[$i][$j].'</column>';
				}
				$str .= "</columns>\n";
			}
			$str .= "</foot>";
		}
		
		$out->add($str);
	}
}

class GridDataItem extends DataItem{
	protected $row_attrs;//!< hash of row attributes
	protected $cell_attrs;//!< hash of cell attributes
	
	function __construct($data,$name,$index=0){
		parent::__construct($data,$name,$index);
		
		$this->row_attrs=array();
		$this->cell_attrs=array();
	}
	/*! set color of row
		
		@param color 
			color of row
	*/
	function set_row_color($color){
		$this->row_attrs["bgColor"]=$color;
	}
	/*! set style of row
		
		@param color 
			color of row
	*/
	function set_row_style($color){
		$this->row_attrs["style"]=$color;
	}
	/*! assign custom style to the cell
		
		@param name
			name of column
		@param value
			css style string
	*/
	function set_cell_style($name,$value){
		$this->set_cell_attribute($name,"style",$value);
	}
	/*! assign custom class to specific cell
		
		@param name
			name of column
		@param value
			css class name
	*/
	function set_cell_class($name,$value){
		$this->set_cell_attribute($name,"class",$value);
	}
	/*! set custom cell attribute
		
		@param name
			name of column
		@param attr
			name of attribute
		@param value
			value of attribute
	*/
	function set_cell_attribute($name,$attr,$value){
		if (!array_key_exists($name, $this->cell_attrs)) $this->cell_attrs[$name]=array();
		$this->cell_attrs[$name][$attr]=$value;
	}
	
	/*! set custom row attribute
		
		@param attr
			name of attribute
		@param value
			value of attribute
	*/
	function set_row_attribute($attr,$value){
		$this->row_attrs[$attr]=$value;
	}	
	
	/*! return self as XML string, starting part
	*/
	public function to_xml_start(){
		if ($this->skip) return "";
		
		$str="<row id='".$this->get_id()."'";
		foreach ($this->row_attrs as $k=>$v)
			$str.=" ".$k."='".$v."'";
		$str.=">";
		for ($i=0; $i < sizeof($this->config->text); $i++){ 
			$str.="<cell";
			$name=$this->config->text[$i]["name"];
			if (isset($this->cell_attrs[$name])){
				$cattrs=$this->cell_attrs[$name];
				foreach ($cattrs as $k => $v)
					$str.=" ".$k."='".$this->xmlentities($v)."'";
			}
			$value = isset($this->data[$name]) ? $this->data[$name] : '';
			$str.="><![CDATA[".$value."]]></cell>";
		}
		if ($this->userdata !== false)
			foreach ($this->userdata as $key => $value)
				$str.="<userdata name='".$key."'><![CDATA[".$value."]]></userdata>";
			
		return $str;
	}
	/*! return self as XML string, ending part
	*/
	public function to_xml_end(){
		if ($this->skip) return "";
		
		return "</row>";
	}
}
/*! Connector for the dhtmlxgrid
**/
class GridConnector extends Connector{
	protected $extra_output="";//!< extra info which need to be sent to client side
	protected $options=array();//!< hash of OptionsConnector 
	
	/*! constructor
		
		Here initilization of all Masters occurs, execution timer initialized
		@param res 
			db connection resource
		@param type
			string , which hold type of database ( MySQL or Postgre ), optional, instead of short DB name, full name of DataWrapper-based class can be provided
		@param item_type
			name of class, which will be used for item rendering, optional, DataItem will be used by default
		@param data_type
			name of class which will be used for dataprocessor calls handling, optional, DataProcessor class will be used by default. 
	*/		
	public function __construct($res,$type=false,$item_type=false,$data_type=false,$render_type=false){
		if (!$item_type) $item_type="GridDataItem";
		if (!$data_type) $data_type="GridDataProcessor";
		if (!$render_type) $render_type="RenderStrategy";
		parent::__construct($res,$type,$item_type,$data_type,$render_type);
	}


	protected function parse_request(){
		parent::parse_request();
		
		if (isset($_GET["dhx_colls"]))
			$this->fill_collections($_GET["dhx_colls"]);	
		
		if (isset($_GET["posStart"]) && isset($_GET["count"]))
			$this->request->set_limit($_GET["posStart"],$_GET["count"]);
	}
	protected function resolve_parameter($name){
		if (intval($name).""==$name)
			return $this->config->text[intval($name)]["db_name"];
		return $name;
	}
	
	/*! replace xml unsafe characters
		
		@param string 
			string to be escaped
		@return 
			escaped string
	*/	
	protected function xmlentities($string) { 
   		return str_replace( array( '&', '"', "'", '<', '>', '’' ), array( '&amp;' , '&quot;', '&apos;' , '&lt;' , '&gt;', '&apos;' ), $string);
	}
		
	/*! assign options collection to the column
		
		@param name 
			name of the column
		@param options
			array or connector object
	*/
	public function set_options($name,$options){
		if (is_array($options)){
			$str="";
			foreach($options as $k => $v)
				$str.="<item value='".$this->xmlentities($k)."' label='".$this->xmlentities($v)."' />";
			$options=$str;
		}
		$this->options[$name]=$options;
	}
	/*! generates xml description for options collections
		
		@param list 
			comma separated list of column names, for which options need to be generated
	*/
	protected function fill_collections($list){
		$names=explode(",",$list);
		for ($i=0; $i < sizeof($names); $i++) { 
			$name = $this->resolve_parameter($names[$i]);
			if (!array_key_exists($name,$this->options)){
				$this->options[$name] = new DistinctOptionsConnector($this->get_connection(),$this->names["db_class"]);
				$c = new DataConfig($this->config);
				$r = new DataRequestConfig($this->request);
				$c->minimize($name);
				
				$this->options[$name]->render_connector($c,$r);
			} 
			
			$this->extra_output.="<coll_options for='{$names[$i]}'>";
			if (!is_string($this->options[$name]))
				$this->extra_output.=$this->options[$name]->render();
			else
				$this->extra_output.=$this->options[$name];
			$this->extra_output.="</coll_options>";
		}
	}
	
	/*! renders self as  xml, starting part
	*/
	protected function xml_start(){
		$attributes = "";
		foreach($this->attributes as $k=>$v)
			$attributes .= " ".$k."='".$v."'";

		if ($this->dload){
			if ($pos=$this->request->get_start())
				return "<rows pos='".$pos."'".$attributes.">";
			else
				return "<rows total_count='".$this->sql->get_size($this->request)."'".$attributes.">";
		}
		else
			return "<rows".$attributes.">";
	}
	
	
	/*! renders self as  xml, ending part
	*/
	protected function xml_end(){
		return $this->extra_output."</rows>";
	}

	public function set_config($config = false){
		if (gettype($config) == 'boolean')
			$config = new GridConfiguration($config);
			
		$this->event->attach("beforeOutput", Array($config, "attachHeaderToXML"));
                $this->event->attach("onInit", Array($config, "defineOptions"));
	}
}

/*! DataProcessor class for Grid component
**/
class GridDataProcessor extends DataProcessor{
	
	/*! convert incoming data name to valid db name
		converts c0..cN to valid field names
		@param data 
			data name from incoming request
		@return 
			related db_name
	*/
	function name_data($data){
		if ($data == "gr_id") return $this->config->id["name"];
		$parts=explode("c",$data);
		if ($parts[0]=="" && ((string)intval($parts[1]))==$parts[1])
			if (sizeof($this->config->text)>intval($parts[1]))
				return $this->config->text[intval($parts[1])]["name"];
		return $data;
	}
}

class KeyGridConnector extends GridConnector{
	public function __construct($res,$type=false,$item_type=false,$data_type=false){
		if (!$item_type) $item_type="GridDataItem";
		if (!$data_type) $data_type="KeyGridDataProcessor";
		parent::__construct($res,$type,$item_type,$data_type);
		
		$this->event->attach("beforeProcessing",array($this,"before_check_key"));	
		$this->event->attach("afterProcessing",array($this,"after_check_key"));	
	}

	public function before_check_key($action){
		if ($action->get_value($this->config->id["name"])=="")
			$action->error();
	}
	public function after_check_key($action){
		if ($action->get_status()=="inserted" || $action->get_status()=="updated"){
			$action->success($action->get_value($this->config->id["name"]));
			$action->set_status("inserted");
		}
	}
};

class KeyGridDataProcessor extends DataProcessor{
	
	/*! convert incoming data name to valid db name
		converts c0..cN to valid field names
		@param data 
			data name from incoming request
		@return 
			related db_name
	*/
	function name_data($data){
		if ($data == "gr_id") return "__dummy__id__"; //ignore ID
		$parts=explode("c",$data);
		if ($parts[0]=="" && intval($parts[1])==$parts[1])
			return $this->config->text[intval($parts[1])]["name"];
		return $data;
	}
}


/*! DataItem class for dhxForm:options
**/
class OptionsDataItem extends DataItem{
	/*! return self as XML string
	*/
	function to_xml(){
		if ($this->skip) return "";
		$str ="";
		
		$str .= "<item value=\"".$this->xmlentities($this->data[$this->config->data[0]['db_name']])."\" label=\"".$this->xmlentities($this->data[$this->config->data[1]['db_name']])."\" />";
		return $str;
	}
}

/*! Connector class for dhtmlxForm:options
**/
class SelectOptionsConnector extends Connector{

	/*! constructor
		
		Here initilization of all Masters occurs, execution timer initialized
		@param res 
			db connection resource
		@param type
			string , which hold type of database ( MySQL or Postgre ), optional, instead of short DB name, full name of DataWrapper-based class can be provided
		@param item_type
			name of class, which will be used for item rendering, optional, DataItem will be used by default
		@param data_type
			name of class which will be used for dataprocessor calls handling, optional, DataProcessor class will be used by default. 
	*/	
	public function __construct($res,$type=false,$item_type=false,$data_type=false){
		if (!$item_type) $item_type="OptionsDataItem";
		parent::__construct($res,$type,$item_type,$data_type);
	}

}

/*! DataItem class for Scheduler component
**/
class SchedulerDataItem extends DataItem{
	/*! return self as XML string
	*/
	function to_xml(){
		if ($this->skip) return "";
		
		$str="<event id='".$this->get_id()."' >";
		$str.="<start_date><![CDATA[".$this->data[$this->config->text[0]["name"]]."]]></start_date>";
		$str.="<end_date><![CDATA[".$this->data[$this->config->text[1]["name"]]."]]></end_date>";
		$str.="<text><![CDATA[".$this->data[$this->config->text[2]["name"]]."]]></text>";
		for ($i=3; $i<sizeof($this->config->text); $i++){
			$extra = $this->config->text[$i]["name"];
			$str.="<".$extra."><![CDATA[".$this->data[$extra]."]]></".$extra.">";
		}
		if ($this->userdata !== false)
			foreach ($this->userdata as $key => $value)
				$str.="<".$key."><![CDATA[".$value."]]></".$key.">";

		return $str."</event>";
	}
}


/*! Connector class for dhtmlxScheduler
**/
class SchedulerConnector extends Connector{
	
	protected $extra_output="";//!< extra info which need to be sent to client side
	protected $options=array();//!< hash of OptionsConnector 
	
			
	/*! assign options collection to the column
		
		@param name 
			name of the column
		@param options
			array or connector object
	*/
	public function set_options($name,$options){
		if (is_array($options)){
			$str="";
			foreach($options as $k => $v)
				$str.="<item value='".$this->xmlentities($k)."' label='".$this->xmlentities($v)."' />";
			$options=$str;
		}
		$this->options[$name]=$options;
	}


	/*! constructor
		
		Here initilization of all Masters occurs, execution timer initialized
		@param res 
			db connection resource
		@param type
			string , which hold type of database ( MySQL or Postgre ), optional, instead of short DB name, full name of DataWrapper-based class can be provided
		@param item_type
			name of class, which will be used for item rendering, optional, DataItem will be used by default
		@param data_type
			name of class which will be used for dataprocessor calls handling, optional, DataProcessor class will be used by default. 
	 * @param render_type
			name of class which will be used for rendering.
	*/	
	public function __construct($res,$type=false,$item_type=false,$data_type=false,$render_type=false){
		if (!$item_type) $item_type="SchedulerDataItem";
		if (!$data_type) $data_type="SchedulerDataProcessor";
		if (!$render_type) $render_type="RenderStrategy";
		parent::__construct($res,$type,$item_type,$data_type,$render_type);
	}

	//parse GET scoope, all operations with incoming request must be done here
	function parse_request(){
		parent::parse_request();
		if (count($this->config->text)){
			if (isset($_GET["to"]))
				$this->request->set_filter($this->config->text[0]["name"],$_GET["to"],"<");
			if (isset($_GET["from"]))
				$this->request->set_filter($this->config->text[1]["name"],$_GET["from"],">");
		}
	}
}

/*! DataProcessor class for Scheduler component
**/
class SchedulerDataProcessor extends DataProcessor{
	function name_data($data){
		if ($data=="start_date")
			return $this->config->text[0]["db_name"];
		if ($data=="id")
			return $this->config->id["db_name"];
		if ($data=="end_date")
			return $this->config->text[1]["db_name"];
		if ($data=="text")
			return $this->config->text[2]["db_name"];
			
		return $data;
	}
}


class JSONSchedulerDataItem extends SchedulerDataItem{
	/*! return self as XML string
	*/
	function to_xml(){
		if ($this->skip) return "";
		
		$obj = array();
		$obj['id'] = $this->get_id();
		$obj['start_date'] = $this->data[$this->config->text[0]["name"]];
		$obj['end_date'] = $this->data[$this->config->text[1]["name"]];
		$obj['text'] = $this->data[$this->config->text[2]["name"]];
		for ($i=3; $i<sizeof($this->config->text); $i++){
			$extra = $this->config->text[$i]["name"];
			$obj[$extra]=$this->data[$extra];
		}

		if ($this->userdata !== false)
			foreach ($this->userdata as $key => $value)
				$obj[$key]=$value;

		return $obj;
	}
}


class JSONSchedulerConnector extends SchedulerConnector {
	
	protected $data_separator = ",";

	/*! constructor
		
		Here initilization of all Masters occurs, execution timer initialized
		@param res 
			db connection resource
		@param type
			string , which hold type of database ( MySQL or Postgre ), optional, instead of short DB name, full name of DataWrapper-based class can be provided
		@param item_type
			name of class, which will be used for item rendering, optional, DataItem will be used by default
		@param data_type
			name of class which will be used for dataprocessor calls handling, optional, DataProcessor class will be used by default. 
	*/	
	public function __construct($res,$type=false,$item_type=false,$data_type=false,$render_type=false){
		if (!$item_type) $item_type="JSONSchedulerDataItem";
		if (!$data_type) $data_type="SchedulerDataProcessor";
		if (!$render_type) $render_type="JSONRenderStrategy";
		parent::__construct($res,$type,$item_type,$data_type,$render_type);
	}

	protected function xml_start() {
		return '{ "data":';
	}

	protected function xml_end() {
		$this->fill_collections();
		$end = (!empty($this->extra_output)) ? ', "collections": {'.$this->extra_output.'}' : '';
		foreach ($this->attributes as $k => $v)
			$end.=", ".$k.":\"".$v."\"";
		$end .= '}';
		return $end;
	}

	/*! assign options collection to the column
		
		@param name 
			name of the column
		@param options
			array or connector object
	*/
	public function set_options($name,$options){
		if (is_array($options)){
			$str=array();
			foreach($options as $k => $v)
				$str[]='{"id":"'.$this->xmlentities($k).'", "value":"'.$this->xmlentities($v).'"}';
			$options=implode(",",$str);
		}
		$this->options[$name]=$options;
	}


	/*! generates xml description for options collections
		
		@param list 
			comma separated list of column names, for which options need to be generated
	*/
	protected function fill_collections(){
		$options = array();
		foreach ($this->options as $k=>$v) { 
			$name = $k;
			$option="\"{$name}\":[";
			if (!is_string($this->options[$name]))
				$option.=substr($this->options[$name]->render(),0,-2);
			else
				$option.=$this->options[$name];
			$option.="]";
			$options[] = $option;
		}
		$this->extra_output .= implode($this->data_separator, $options);
	}


	/*! output fetched data as XML
		@param res
			DB resultset 
	*/
	protected function output_as_xml($res){
		$data=$this->xml_start();
		$data.=$this->render_set($res);
		$data.=$this->xml_end();

		$out = new OutputWriter($data, "");
		$out->set_type("json");
		$this->event->trigger("beforeOutput", $this, $out);
		$out->output("", true, $this->encoding);
	}
}


class RenderStrategy {

	protected $conn = null;

	public function __construct($conn) {
		$this->conn = $conn;
	}

	/*! render from DB resultset
		@param res
			DB resultset 
		process commands, output requested data as XML
	*/
	public function render_set($res, $name, $dload, $sep, $config){
		$output="";
		$index=0;
		$conn = $this->conn;
		$conn->event->trigger("beforeRenderSet",$conn,$res,$config);
		while ($data=$conn->sql->get_next($res)){
			$data = new $name($data,$config,$index);
			if ($data->get_id()===false)
				$data->set_id($conn->uuid());
			$conn->event->trigger("beforeRender",$data);
			$output.=$data->to_xml().$sep;
			$index++;
		}
		return $output;
	}

}

class JSONRenderStrategy extends RenderStrategy {

	/*! render from DB resultset
		@param res
			DB resultset 
		process commands, output requested data as json
	*/
	public function render_set($res, $name, $dload, $sep, $config){
		$output=array();
		$index=0;
		$conn = $this->conn;
		$conn->event->trigger("beforeRenderSet",$conn,$res,$config);
		while ($data=$conn->sql->get_next($res)){
			$data = new $name($data,$config,$index);
			if ($data->get_id()===false)
				$data->set_id($conn->uuid());
			$conn->event->trigger("beforeRender",$data);
			$output[]=$data->to_xml();
			$index++;
		}
		return json_encode($output);
	}

}

class TreeRenderStrategy extends RenderStrategy {

	protected $id_swap = array();

	public function __construct($conn) {
		parent::__construct($conn);
		$conn->event->attach("afterInsert",array($this,"parent_id_correction_a"));
		$conn->event->attach("beforeProcessing",array($this,"parent_id_correction_b"));
	}

	public function render_set($res, $name, $dload, $sep, $config){
		$output="";
		$index=0;
		$conn = $this->conn;
		while ($data=$conn->sql->get_next($res)){
			$data = new $name($data,$config,$index);
			$conn->event->trigger("beforeRender",$data);
			//there is no info about child elements,
			//if we are using dyn. loading - assume that it has,
			//in normal mode juse exec sub-render routine
			if ($data->has_kids()===-1 && $dload)
					$data->set_kids(true);
			$output.=$data->to_xml_start();
			if ($data->has_kids()===-1 || ( $data->has_kids()==true && !$dload)){
				$sub_request = new DataRequestConfig($conn->get_request());
				$sub_request->set_relation($data->get_id());
				$output.=$this->render_set($conn->sql->select($sub_request), $name, $dload, $sep, $config);
			}
			$output.=$data->to_xml_end();
			$index++;
		}
		return $output;
	}

	/*! store info about ID changes during insert operation
		@param dataAction 
			data action object during insert operation
	*/
	public function parent_id_correction_a($dataAction){
		$this->id_swap[$dataAction->get_id()]=$dataAction->get_new_id();
	}

	/*! update ID if it was affected by previous operation
		@param dataAction 
			data action object, before any processing operation
	*/
	public function parent_id_correction_b($dataAction){
		$relation = $this->conn->get_config()->relation_id["db_name"];
		$value = $dataAction->get_value($relation);

		if (array_key_exists($value,$this->id_swap))
			$dataAction->set_value($relation,$this->id_swap[$value]);
	}
}



class JSONTreeRenderStrategy extends TreeRenderStrategy {

	public function render_set($res, $name, $dload, $sep, $config){
		$output=array();
		$index=0;
		$conn = $this->conn;
		while ($data=$conn->sql->get_next($res)){
			$data = new $name($data,$config,$index);
			$conn->event->trigger("beforeRender",$data);
			//there is no info about child elements, 
			//if we are using dyn. loading - assume that it has,
			//in normal mode just exec sub-render routine			
			if ($data->has_kids()===-1 && $dload)
					$data->set_kids(true);
			$record = $data->to_xml_start();
			if ($data->has_kids()===-1 || ( $data->has_kids()==true && !$dload)){
				$sub_request = new DataRequestConfig($conn->get_request());
				$sub_request->set_relation($data->get_id());
				$temp = $this->render_set($conn->sql->select($sub_request), $name, $dload, $sep, $config);
				if (sizeof($temp))
					$record["data"] = $temp;
			}
			$output[] = $record;
			$index++;
		}
		return $output;
	}	

}


class MultitableTreeRenderStrategy extends TreeRenderStrategy {

	private $level = 0;
	private $max_level = null;
	protected $sep = "#";
	
	public function __construct($conn) {
		parent::__construct($conn);
		$conn->event->attach("beforeProcessing", Array($this, 'id_translate_before'));
		$conn->event->attach("afterProcessing", Array($this, 'id_translate_after'));
	}

	public function set_separator($sep) {
		$this->sep = $sep;
	}
	
	public function render_set($res, $name, $dload, $sep, $config){
		$output="";
		$index=0;
		$conn = $this->conn;
		while ($data=$conn->sql->get_next($res)){
			$data[$config->id['name']] = $this->level_id($data[$config->id['name']]);
			$data = new $name($data,$config,$index);
			$conn->event->trigger("beforeRender",$data);
			if (($this->max_level !== null)&&($conn->get_level() == $this->max_level)) {
				$data->set_kids(false);
			} else {
				if ($data->has_kids()===-1)
					$data->set_kids(true);
			}
			$output.=$data->to_xml_start();
			$output.=$data->to_xml_end();
			$index++;
		}
		return $output;
	}


	public function level_id($id, $level = null) {
		return ($level === null ? $this->level : $level).$this->sep.$id;
	}


	/*! remove level prefix from id, parent id and set new id before processing
		@param action
			DataAction object
	*/
	public function id_translate_before($action) {
		$id = $action->get_id();
		$id = $this->parse_id($id, false);
		$action->set_id($id);
		$action->set_value('tr_id', $id);
		$action->set_new_id($id);
		$pid = $action->get_value($this->conn->get_config()->relation_id['db_name']);
		$pid = $this->parse_id($pid, false);
		$action->set_value($this->conn->get_config()->relation_id['db_name'], $pid);
	}


	/*! add level prefix in id and new id after processing
		@param action
			DataAction object
	*/
	public function id_translate_after($action) {
		$id = $action->get_id();
		$action->set_id($this->level_id($id));
		$id = $action->get_new_id();
		$action->success($this->level_id($id));
	}


	public function get_level($parent_name) {
		if ($this->level) return $this->level;
		if (!isset($_GET[$parent_name])) {
			if (isset($_POST['ids'])) {
				$ids = explode(",",$_POST["ids"]);
				$id = $this->parse_id($ids[0]);
				$this->level--;
			}
			$this->conn->get_request()->set_relation(false);
		} else {
			$id = $this->parse_id($_GET[$parent_name]);
			$_GET[$parent_name] = $id;
		}
		return $this->level;
	}


	public function is_max_level() {
		if (($this->max_level !== null) && ($this->level >= $this->max_level))
			return true;
		return false;
	}
	public function set_max_level($max_level) {
		$this->max_level = $max_level;
	}
	public function parse_id($id, $set_level = true) {
		$parts = explode('#', urldecode($id));
		if (count($parts) === 2) {
			$level = $parts[0] + 1;
			$id = $parts[1];
		} else {
			$level = 0;
			$id = '';
		}
		if ($set_level) $this->level = $level;
		return $id;
	}

}


class JSONMultitableTreeRenderStrategy extends MultitableTreeRenderStrategy {

	public function render_set($res, $name, $dload, $sep, $config){
		$output=array();
		$index=0;
		$conn = $this->conn;
		while ($data=$conn->sql->get_next($res)){
			$data[$config->id['name']] = $this->level_id($data[$config->id['name']]);
			$data = new $name($data,$config,$index);
			$conn->event->trigger("beforeRender",$data);

			if ($this->is_max_level()) {
				$data->set_kids(false);
			} else {
				if ($data->has_kids()===-1)
					$data->set_kids(true);
			}
			$record = $data->to_xml_start($output);
			$output[] = $record;
			$index++;
		}
		return $output;
	}

}


class GroupRenderStrategy extends RenderStrategy {

	private $id_postfix = '__{group_param}';

	public function __construct($conn) {
		parent::__construct($conn);
		$conn->event->attach("beforeProcessing", Array($this, 'check_id'));
		$conn->event->attach("onInit", Array($this, 'replace_postfix'));
	}

	public function render_set($res, $name, $dload, $sep, $config){
		$output="";
		$index=0;
		$conn = $this->conn;
		while ($data=$conn->sql->get_next($res)){
			if (isset($data[$config->id['name']])) {
				$has_kids = false;
			} else {
				$data[$config->id['name']] = $data['value'].$this->id_postfix;
				$data[$config->text[0]['name']] = $data['value'];
				$has_kids = true;
			}
			$data = new $name($data,$config,$index);
			$conn->event->trigger("beforeRender",$data);
			if ($has_kids === false) {
				$data->set_kids(false);
			}

			if ($data->has_kids()===-1 && $dload)
				$data->set_kids(true);
			$output.=$data->to_xml_start();
			if (($data->has_kids()===-1 || ( $data->has_kids()==true && !$dload))&&($has_kids == true)){
				$sub_request = new DataRequestConfig($conn->get_request());
				$sub_request->set_relation(str_replace($this->id_postfix, "", $data->get_id()));
				$output.=$this->render_set($conn->sql->select($sub_request), $name, $dload, $sep, $config);
			}
			$output.=$data->to_xml_end();
			$index++;
		}
		return $output;
	}

	public function check_id($action) {
		if (isset($_GET['editing'])) {
			$config = $this->conn->get_config();
			$id = $action->get_id();
			$pid = $action->get_value($config->relation_id['name']);
			$pid = str_replace($this->id_postfix, "", $pid);
			$action->set_value($config->relation_id['name'], $pid);
			if (!empty($pid)) {
				return $action;
			} else {
				$action->error();
				$action->set_response_text("This record can't be updated!");
				return $action;
			}
		} else {
			return $action;
		}
	}

	public function replace_postfix() {
		if (isset($_GET['id'])) {
			$_GET['id'] = str_replace($this->id_postfix, "", $_GET['id']);
		}
	}

	public function get_postfix() {
		return $this->id_postfix;
	}

}


class JSONGroupRenderStrategy extends GroupRenderStrategy {

	public function render_set($res, $name, $dload, $sep, $config){
		$output=array();
		$index=0;
		$conn = $this->conn;
		while ($data=$conn->sql->get_next($res)){
			if (isset($data[$config->id['name']])) {
				$has_kids = false;
			} else {
				$data[$config->id['name']] = $data['value'].$this->id_postfix;
				$data[$config->text[0]['name']] = $data['value'];
				$has_kids = true;
			}
			$data = new $name($data,$config,$index);
			$conn->event->trigger("beforeRender",$data);
			if ($has_kids === false) {
				$data->set_kids(false);
			}

			if ($data->has_kids()===-1 && $dload)
				$data->set_kids(true);
			$record = $data->to_xml_start();
			if (($data->has_kids()===-1 || ( $data->has_kids()==true && !$dload))&&($has_kids == true)){
				$sub_request = new DataRequestConfig($conn->get_request());
				$sub_request->set_relation(str_replace($this->id_postfix, "", $data->get_id()));
				$temp = $this->render_set($conn->sql->select($sub_request), $name, $dload, $sep, $config);
				if (sizeof($temp))
					$record["data"] = $temp;
			}
			$output[] = $record;
			$index++;
		}
		return $output;
	}

}


/*! Class which allows to assign|fire events.
*/
class EventMaster{
	private $events;//!< hash of event handlers
	private $master;
	private static $eventsStatic=array();
	
	/*! constructor
	*/
	function __construct(){
		$this->events=array();
		$this->master = false;
	}
	/*! Method check if event with such name already exists.
		@param name
			name of event, case non-sensitive
		@return
			true if event with such name registered, false otherwise
	*/	
	public function exist($name){
		$name=strtolower($name);
		return (isset($this->events[$name]) && sizeof($this->events[$name]));
	}
	/*! Attach custom code to event.
	
		Only on event handler can be attached in the same time. If new event handler attached - old will be detached.
		
		@param name
			name of event, case non-sensitive
		@param method
			function which will be attached. You can use array(class, method) if you want to attach the method of the class.
	*/
	public function attach($name,$method=false){
		//use class for event handling
		if ($method === false){
			$this->master = $name;
			return;
		}
		//use separate functions
		$name=strtolower($name);
		if (!array_key_exists($name,$this->events))
			$this->events[$name]=array();
		$this->events[$name][]=$method;
	}
	
	public static function attach_static($name, $method){
		$name=strtolower($name);
		if (!array_key_exists($name,EventMaster::$eventsStatic))
			EventMaster::$eventsStatic[$name]=array();
		EventMaster::$eventsStatic[$name][]=$method;
	}
	
	public static function trigger_static($name, $method){
		$arg_list = func_get_args();
		$name=strtolower(array_shift($arg_list));
		
		if (isset(EventMaster::$eventsStatic[$name]))
			foreach(EventMaster::$eventsStatic[$name] as $method){
				if (is_array($method) && !method_exists($method[0],$method[1]))
					throw new Exception("Incorrect method assigned to event: ".$method[0].":".$method[1]);
				if (!is_array($method) && !function_exists($method))
					throw new Exception("Incorrect function assigned to event: ".$method);
				call_user_func_array($method, $arg_list);
			}
		return true;		
	}
	
	/*! Detach code from event
		@param	name
			name of event, case non-sensitive
	*/	
	public function detach($name){
		$name=strtolower($name);
		unset($this->events[$name]);
	}
	/*! Trigger event.
		@param	name
			name of event, case non-sensitive
		@param data
			value which will be provided as argument for event function,
			you can provide multiple data arguments, method accepts variable number of parameters
		@return 
			true if event handler was not assigned , result of event hangler otherwise
	*/
	public function trigger($name,$data){
		$arg_list = func_get_args();
		$name=strtolower(array_shift($arg_list));
		
		if (isset($this->events[$name]))
			foreach($this->events[$name] as $method){
				if (is_array($method) && !method_exists($method[0],$method[1]))
					throw new Exception("Incorrect method assigned to event: ".$method[0].":".$method[1]);
				if (!is_array($method) && !function_exists($method))
					throw new Exception("Incorrect function assigned to event: ".$method);
				call_user_func_array($method, $arg_list);
			}

		if ($this->master !== false)
			if (method_exists($this->master, $name))
				call_user_func_array(array($this->master, $name), $arg_list);

		return true;
	}
}

/*! Class which handles access rules.	
**/
class AccessMaster{
	private $rules,$local;
	/*! constructor
	
		Set next access right to "allowed" by default : read, insert, update, delete
		Basically - all common data operations allowed by default
	*/
	function __construct(){
		$this->rules=array("read" => true, "insert" => true, "update" => true, "delete" => true);
		$this->local=true;
	}
	/*! change access rule to "allow"
		@param name 
			name of access right
	*/
	public function allow($name){
		$this->rules[$name]=true;
	}
	/*! change access rule to "deny"
		
		@param name 
			name of access right
	*/
	public function deny($name){
		$this->rules[$name]=false;
	}
	
	/*! change all access rules to "deny"
	*/
	public function deny_all(){
		$this->rules=array();
	}	
	
	/*! check access rule
		
		@param name 
			name of access right
		@return 
			true if access rule allowed, false otherwise
	*/
	public function check($name){
		if ($this->local){
			/*!
			todo
				add referrer check, to prevent access from remote points
			*/
		}
		if (!isset($this->rules[$name]) || !$this->rules[$name]){
			return false;
		}
		return true;
	}
}

/*! Controls error and debug logging.
	Class designed to be used as static object. 
**/
class LogMaster{
	private static $_log=false;//!< logging mode flag
	private static $_output=false;//!< output error infor to client flag
	private static $session="";//!< all messages generated for current request
	
	/*! convert array to string representation ( it is a bit more readable than var_dump )
	
		@param data 
			data object
		@param pref
			prefix string, used for formating, optional
		@return 
			string with array description
	*/
	private static function log_details($data,$pref=""){
		if (is_array($data)){
			$str=array("");
			foreach($data as $k=>$v)
				array_push($str,$pref.$k." => ".LogMaster::log_details($v,$pref."\t"));
			return implode("\n",$str);
   		}
   		return $data;
	}
	/*! put record in log
		
		@param str 
			string with log info, optional
		@param data
			data object, which will be added to log, optional
	*/
	public static function log($str="",$data=""){
		if (LogMaster::$_log){
			$message = $str.LogMaster::log_details($data)."\n\n";
			LogMaster::$session.=$message;
			error_log($message,3,LogMaster::$_log);			
		}
	}
	
	/*! get logs for current request
		@return 
			string, which contains all log messages generated for current request
	*/
	public static function get_session_log(){
		return LogMaster::$session;
	}
	
	/*! error handler, put normal php errors in log file
		
		@param errn
			error number
		@param errstr
			error description
		@param file
			error file
		@param line
			error line
		@param context
			error cntext
	*/
	public static function error_log($errn,$errstr,$file,$line,$context){
		LogMaster::log($errstr." at ".$file." line ".$line);
	}
	
	/*! exception handler, used as default reaction on any error - show execution log and stop processing
		
		@param exception
			instance of Exception	
	*/
	public static function exception_log($exception){
		LogMaster::log("!!!Uncaught Exception\nCode: " . $exception->getCode() . "\nMessage: " . $exception->getMessage());
		if (LogMaster::$_output){
			echo "<pre><xmp>\n";
			echo LogMaster::get_session_log();
			echo "\n</xmp></pre>";
		}
		die();
	}
	
	/*! enable logging

		@param name 
			path to the log file, if boolean false provided as value - logging will be disabled
		@param output 
			flag of client side output, if enabled - session log will be sent to client side in case of an error.
	*/
	public static function enable_log($name,$output=false){
		LogMaster::$_log=$name;
		LogMaster::$_output=$output;
		if ($name){
			set_error_handler(array("LogMaster","error_log"),E_ALL);
			set_exception_handler(array("LogMaster","exception_log"));
			LogMaster::log("\n\n====================================\nLog started, ".date("d/m/Y h:m:s")."\n====================================");
		}
	}
}


class TreeDataItem extends DataItem{
	private $im0;//!< image of closed folder
	private $im1;//!< image of opened folder
	private $im2;//!< image of leaf item
	private $check;//!< checked state
	private $kids=-1;//!< checked state
	private $attrs;//!< collection of custom attributes
	
	function __construct($data,$config,$index){
		parent::__construct($data,$config,$index);
		
		$this->im0=false;
		$this->im1=false;
		$this->im2=false;
		$this->check=false;
		$this->attrs = array();
	}
	/*! get id of parent record
		
		@return 
			id of parent record
	*/
	function get_parent_id(){
		return $this->data[$this->config->relation_id["name"]];
	}
	/*! get state of items checkbox
		
		@return 
			state of item's checkbox as int value, false if state was not defined
	*/
	function get_check_state(){
		return $this->check;
	}
	/*! set state of item's checkbox

		@param value 
			int value, 1 - checked, 0 - unchecked, -1 - third state
	*/
	function set_check_state($value){
		$this->check=$value;
	}
	
	/*! return count of child items
		-1 if there is no info about childs
		@return 
			count of child items
	*/
	function has_kids(){
		return $this->kids;
	}
	/*! sets count of child items
		@param value
			count of child items
	*/
	function set_kids($value){
		$this->kids=$value;
	}
	
	/*! set custom attribute 
		
		@param name 
			name of the attribute
		@param value
			new value of the attribute
	*/
	function set_attribute($name, $value){
		switch($name){
			case "id": 
				$this->set_id($value);
				break;
			case "text": 
				$this->data[$this->config->text[0]["name"]]=$value;
				break;
			case "checked": 
				$this->set_check_state($value);
				break;
			case "im0": 
				$this->im0=$value;
				break;
			case "im1": 
				$this->im1=$value;
				break;
			case "im2": 
				$this->im2=$value;
				break;
			case "child": 
				$this->set_kids($value);
				break;
			default:
				$this->attrs[$name]=$value;
		}
	}
	
	
	/*! assign image for tree's item
		
		@param img_folder_closed 
			image for item, which represents folder in closed state
		@param img_folder_open 
			image for item, which represents folder in opened state, optional
		@param img_leaf 
			image for item, which represents leaf item, optional
	*/
	function set_image($img_folder_closed,$img_folder_open=false,$img_leaf=false){
		$this->im0=$img_folder_closed;
		$this->im1=$img_folder_open?$img_folder_open:$img_folder_closed;
		$this->im2=$img_leaf?$img_leaf:$img_folder_closed;
	}
	/*! return self as XML string, starting part
	*/
	function to_xml_start(){
		if ($this->skip) return "";
		
		$str1="<item id='".$this->get_id()."' text='".$this->xmlentities($this->data[$this->config->text[0]["name"]])."' ";
		if ($this->has_kids()==true) $str1.="child='".$this->has_kids()."' ";
		if ($this->im0) $str1.="im0='".$this->im0."' ";
		if ($this->im1) $str1.="im1='".$this->im1."' ";
		if ($this->im2) $str1.="im2='".$this->im2."' ";
		if ($this->check) $str1.="checked='".$this->check."' ";
		foreach ($this->attrs as $key => $value)
			$str1.=$key."='".$this->xmlentities($value)."' ";
		$str1.=">";
		if ($this->userdata !== false)
			foreach ($this->userdata as $key => $value)
				$str1.="<userdata name='".$key."'><![CDATA[".$value."]]></userdata>";
			
		return $str1;
	}
	/*! return self as XML string, ending part
	*/
	function to_xml_end(){
		if ($this->skip) return "";
		return "</item>";
	}

}

require_once("filesystem_item.php");

/*! Connector for the dhtmlxtree
**/
class TreeConnector extends Connector{
	protected $parent_name = 'id';

	/*! constructor
		
		Here initilization of all Masters occurs, execution timer initialized
		@param res 
			db connection resource
		@param type
			string , which hold type of database ( MySQL or Postgre ), optional, instead of short DB name, full name of DataWrapper-based class can be provided
		@param item_type
			name of class, which will be used for item rendering, optional, DataItem will be used by default
		@param data_type
			name of class which will be used for dataprocessor calls handling, optional, DataProcessor class will be used by default. 
	 *	@param render_type
	 *		name of class which will provides data rendering
	*/	
	public function __construct($res,$type=false,$item_type=false,$data_type=false, $render_type=false){
		if (!$item_type) $item_type="TreeDataItem";
		if (!$data_type) $data_type="TreeDataProcessor";
		if (!$render_type) $render_type="TreeRenderStrategy";
		parent::__construct($res,$type,$item_type,$data_type,$render_type);
	}

	//parse GET scoope, all operations with incoming request must be done here
	public function parse_request(){
		parent::parse_request();
		
		if (isset($_GET[$this->parent_name]))
			$this->request->set_relation($_GET[$this->parent_name]);
		else
			$this->request->set_relation("0");
			
		$this->request->set_limit(0,0); //netralize default reaction on dyn. loading mode
	}

   /*! renders self as  xml, starting part
	*/
	public function xml_start(){
		$attributes = "";
		foreach($this->attributes as $k=>$v)
			$attributes .= " ".$k."='".$v."'";

		return "<tree id='".$this->request->get_relation()."'".$attributes.">";
	}
	
	/*! renders self as  xml, ending part
	*/
	public function xml_end(){
		return "</tree>";
	}
}


class TreeDataProcessor extends DataProcessor{
	
	function __construct($connector,$config,$request){
		parent::__construct($connector,$config,$request);
		$request->set_relation(false);
	}
		
	/*! convert incoming data name to valid db name
		converts c0..cN to valid field names
		@param data 
			data name from incoming request
		@return 
			related db_name
	*/
	function name_data($data){
		if ($data=="tr_pid")
			return $this->config->relation_id["db_name"];
		if ($data=="tr_text")
			return $this->config->text[0]["db_name"];
		return $data;
	}
}

class TreeDataGroupConnector extends TreeDataConnector{

	public function __construct($res,$type=false,$item_type=false,$data_type=false,$render_type=false){
		if (!$render_type) $render_type="GroupRenderStrategy";
		parent::__construct($res,$type,$item_type,$data_type,$render_type);
	}

	/*! if not isset $_GET[id] then it's top level
	 */
	protected function set_relation() {
		if (!isset($_GET[$this->parent_name])) $this->request->set_relation(false);
	}

	/*! if it's first level then distinct level
	 *  else select by parent
	 */
	protected function get_resource() {
		$resource = null;
		if (isset($_GET[$this->parent_name]))
			$resource = $this->sql->select($this->request);
		else
			$resource = $this->sql->get_variants($this->config->relation_id['name'], $this->request);
		return $resource;
	}


	/*! renders self as xml, starting part
	*/
	public function xml_start(){
		if (isset($_GET[$this->parent_name])) {
			return "<data parent='".$_GET[$this->parent_name].$this->render->get_postfix()."'>";
		} else {
			return "<data parent='0'>";
		}
	}

}




class JSONTreeDataGroupConnector extends JSONTreeDataConnector{

	public function __construct($res,$type=false,$item_type=false,$data_type=false,$render_type=false){
		if (!$render_type) $render_type="JSONGroupRenderStrategy";
		parent::__construct($res,$type,$item_type,$data_type,$render_type);
	}

	/*! if not isset $_GET[id] then it's top level
	 */
	protected function set_relation() {
		if (!isset($_GET[$this->parent_name])) $this->request->set_relation(false);
	}

	/*! if it's first level then distinct level
	 *  else select by parent
	 */
	protected function get_resource() {
		$resource = null;
		if (isset($_GET[$this->parent_name]))
			$resource = $this->sql->select($this->request);
		else
			$resource = $this->sql->get_variants($this->config->relation_id['name'], $this->request);
		return $resource;
	}


	/*! renders self as xml, starting part
	*/
	public function xml_start(){
		if (isset($_GET[$this->parent_name])) {
			return "<data parent='".$_GET[$this->parent_name].$this->render->get_postfix()."'>";
		} else {
			return "<data parent='0'>";
		}
	}

}


class TreeDataMultitableConnector extends TreeDataConnector{

	protected $parent_name = 'parent';

	public function __construct($res,$type=false,$item_type=false,$data_type=false,$render_type=false){
		if (!$data_type) $data_type="TreeDataProcessor";
		if (!$render_type) $render_type="MultitableTreeRenderStrategy";
		parent::__construct($res,$type,$item_type,$data_type,$render_type);
	}

	public function render(){
		$this->dload = true;
		return parent::render();
	}

	/*! sets relation for rendering */
	protected function set_relation() {
		if (!isset($_GET[$this->parent_name]))
			$this->request->set_relation(false);
	}

	public function xml_start(){
		if (isset($_GET[$this->parent_name])) {
			return "<data parent='".$this->render->level_id($_GET[$this->parent_name], $this->render->get_level() - 1)."'>";
		} else {
			return "<data parent='0'>";
		}
	}

	/*! set maximum level of tree
		@param max_level
			maximum level
	*/
	public function setMaxLevel($max_level) {
		$this->render->set_max_level($max_level);
	}

	public function get_level() {
		return $this->render->get_level($this->parent_name);
	}

}






class JSONTreeDataMultitableConnector extends TreeDataMultitableConnector{

	public function __construct($res,$type=false,$item_type=false,$data_type=false,$render_type=false){
		if (!$item_type) $item_type="JSONTreeCommonDataItem";
		if (!$data_type) $data_type="CommonDataProcessor";
		if (!$render_type) $render_type="JSONMultitableTreeRenderStrategy";
		parent::__construct($res,$type,$item_type,$data_type,$render_type);
	}

	protected function output_as_xml($res){
		$data = array();
		if (isset($_GET['parent']))
			$data["parent"] = $this->render->level_id($_GET[$this->parent_name], $this->render->get_level() - 1);
		else
			$data["parent"] = "0";
		$data["data"] = $this->render_set($res);

		$out = new OutputWriter(json_encode($data), "");
		$out->set_type("json");
		$this->event->trigger("beforeOutput", $this, $out);
		$out->output("", true, $this->encoding);
	}

	public function xml_start(){
		return '';
	}
}

/*! DataItem class for TreeGrid component
**/
class TreeGridDataItem extends GridDataItem{
	private $kids=-1;//!< checked state
	
	function __construct($data,$config,$index){
		parent::__construct($data,$config,$index);
		$this->im0=false;
	}
	/*! return id of parent record

		@return 
			id of parent record
	*/
	function get_parent_id(){
		return $this->data[$this->config->relation_id["name"]];
	}
	/*! assign image to treegrid's item
		longer description
		@param img 
			relative path to the image
	*/
	function set_image($img){
		$this->set_cell_attribute($this->config->text[0]["name"],"image",$img);
	}

	/*! return count of child items
		-1 if there is no info about childs
		@return 
			count of child items
	*/	
	function has_kids(){
		return $this->kids;
	}
	/*! sets count of child items
		@param value
			count of child items
	*/	
	function set_kids($value){
		$this->kids=$value;
		if ($value) 
			$this->set_row_attribute("xmlkids",$value);
	}
}
/*! Connector for dhtmlxTreeGrid
**/
class TreeGridConnector extends GridConnector{
	protected $parent_name = 'id';

	/*! constructor
		
		Here initilization of all Masters occurs, execution timer initialized
		@param res 
			db connection resource
		@param type
			string , which hold type of database ( MySQL or Postgre ), optional, instead of short DB name, full name of DataWrapper-based class can be provided
		@param item_type
			name of class, which will be used for item rendering, optional, DataItem will be used by default
		@param data_type
			name of class which will be used for dataprocessor calls handling, optional, DataProcessor class will be used by default. 
	 *	@param render_type
	 *		name of class which will provides data rendering
	*/	
	public function __construct($res,$type=false,$item_type=false,$data_type=false,$render_type=false){
		if (!$item_type) $item_type="TreeGridDataItem";
		if (!$data_type) $data_type="TreeGridDataProcessor";
		if (!$render_type) $render_type="TreeRenderStrategy";
		parent::__construct($res,$type,$item_type,$data_type,$render_type);
	}

	/*! process treegrid specific options in incoming request */
	public function parse_request(){
		parent::parse_request();

		if (isset($_GET[$this->parent_name]))
			$this->request->set_relation($_GET[$this->parent_name]);
		else
			$this->request->set_relation("0");

		$this->request->set_limit(0,0); //netralize default reaction on dyn. loading mode
	}

	/*! renders self as  xml, starting part
	*/	
	protected function xml_start(){
		return "<rows parent='".$this->request->get_relation()."'>";
	}	
}

/*! DataProcessor class for Grid component
**/
class TreeGridDataProcessor extends GridDataProcessor{
	
	function __construct($connector,$config,$request){
		parent::__construct($connector,$config,$request);
		$request->set_relation(false);
	}
	
	/*! convert incoming data name to valid db name
		converts c0..cN to valid field names
		@param data 
			data name from incoming request
		@return 
			related db_name
	*/
	function name_data($data){
		
		if ($data=="gr_pid")
			return $this->config->relation_id["name"];
		else return parent::name_data($data);
	}
}

class TreeGridGroupConnector extends TreeGridConnector{

	public function __construct($res,$type=false,$item_type=false,$data_type=false,$render_type=false){
		if (!$render_type) $render_type="GroupRenderStrategy";
		parent::__construct($res,$type,$item_type,$data_type,$render_type);
	}

	/*! if not isset $_GET[id] then it's top level
	 */
	protected function set_relation() {
		if (!isset($_GET[$this->parent_name])) $this->request->set_relation(false);
	}

	/*! if it's first level then distinct level
	 *  else select by parent
	 */
	protected function get_resource() {
		$resource = null;
		if (isset($_GET[$this->parent_name]))
			$resource = $this->sql->select($this->request);
		else
			$resource = $this->sql->get_variants($this->config->relation_id['name'], $this->request);
		return $resource;
	}


	/*! renders self as  xml, starting part
	*/	
	protected function xml_start(){
		if (isset($_GET[$this->parent_name])) {
			return "<rows parent='".$_GET[$this->parent_name].$this->render->get_postfix()."'>";
		} else {
			return "<rows parent='0'>";
		}
	}

}

class TreeGridMultitableConnector extends TreeGridConnector{

	public function __construct($res,$type=false,$item_type=false,$data_type=false,$render_type=false){
		$data_type="TreeGridMultitableDataProcessor";
		if (!$render_type) $render_type="MultitableTreeRenderStrategy";
		parent::__construct($res,$type,$item_type,$data_type,$render_type);
		$this->render->set_separator("%23");
	}

	public function render(){
		$this->dload = true;
		return parent::render();
	}

	/*! sets relation for rendering */
	protected function set_relation() {
		if (!isset($_GET['id']))
			$this->request->set_relation(false);
	}

	public function xml_start(){
		if (isset($_GET['id'])) {
			return "<rows parent='".$this->render->level_id($_GET['id'], $this->render->get_level() - 1)."'>";
		} else {
			return "<rows parent='0'>";
		}
	}

	/*! set maximum level of tree
		@param max_level
			maximum level
	*/
	public function setMaxLevel($max_level) {
		$this->render->set_max_level($max_level);
	}

	public function get_level() {
		return $this->render->get_level($this->parent_name);
	}


}


class TreeGridMultitableDataProcessor extends DataProcessor {

	function name_data($data){
		if ($data=="gr_pid")
			return $this->config->relation_id["name"];
		if ($data=="gr_id")
			return $this->config->id["name"];
		preg_match('/^c([%\d]+)$/', $data, $data_num);
		if (!isset($data_num[1])) return $data;
		$data_num = $data_num[1];
		if (isset($this->config->data[$data_num]["db_name"])) {
			return $this->config->data[$data_num]["db_name"];
		}
		return $data;
	}

}

class TreeGroupConnector extends TreeConnector{

	public function __construct($res,$type=false,$item_type=false,$data_type=false,$render_type=false){
		if (!$render_type) $render_type="GroupRenderStrategy";
		parent::__construct($res,$type,$item_type,$data_type,$render_type);
	}

	/*! if not isset $_GET[id] then it's top level
	 */
	protected function set_relation() {
		if (!isset($_GET[$this->parent_name])) $this->request->set_relation(false);
	}

	/*! if it's first level then distinct level
	 *  else select by parent
	 */
	protected function get_resource() {
		$resource = null;
		if (isset($_GET[$this->parent_name]))
			$resource = $this->sql->select($this->request);
		else
			$resource = $this->sql->get_variants($this->config->relation_id['name'], $this->request);
		return $resource;
	}


	/*! renders self as xml, starting part
	*/
	public function xml_start(){
		if (isset($_GET[$this->parent_name])) {
			return "<tree id='".$_GET[$this->parent_name].$this->render->get_postfix()."'>";
		} else {
			return "<tree id='0'>";
		}
	}

}

class TreeMultitableConnector extends TreeConnector{

	protected $parent_name = 'id';

	public function __construct($res,$type=false,$item_type=false,$data_type=false,$render_type=false){
		if (!$data_type) $data_type="TreeDataProcessor";
		if (!$render_type) $render_type="MultitableTreeRenderStrategy";
		parent::__construct($res,$type,$item_type,$data_type,$render_type);
	}

	public function render(){
		$this->dload = true;
		return parent::render();
	}

	/*! sets relation for rendering */
	protected function set_relation() {
		if (!isset($_GET[$this->parent_name]))
			$this->request->set_relation(false);
	}

	public function xml_start(){
		if (isset($_GET[$this->parent_name])) {
			return "<tree id='".($this->render->level_id($_GET[$this->parent_name], $this->render->get_level() - 1))."'>";
		} else {
			return "<tree id='0'>";
		}
	}

	/*! set maximum level of tree
		@param max_level
			maximum level
	*/
	public function setMaxLevel($max_level) {
		$this->render->set_max_level($max_level);
	}

	public function get_level() {
		return $this->render->get_level($this->parent_name);
	}
	
}

class DataItemUpdate extends DataItem {


	/*! constructor
		@param data
			hash of data
		@param config
			DataConfig object
		@param index
			index of element
	*/
	public function __construct($data,$config,$index,$type){
		$this->config=$config;
		$this->data=$data;
		$this->index=$index;
		$this->skip=false;
		$this->child = new $type($data, $config, $index);
	}

	/*! returns parent_id (for Tree and TreeGrid components)
	*/
	public function get_parent_id(){
		if (method_exists($this->child, 'get_parent_id')) {
			return $this->child->get_parent_id();
		} else {
			return '';
		}
	}


	/*! generate XML on the data hash base
	*/
	public function to_xml(){
        $str= "<update ";
		$str .= 'status="'.$this->data['type'].'" ';
		$str .= 'id="'.$this->data['dataId'].'" ';
		$str .= 'parent="'.$this->get_parent_id().'"';
		$str .= '>';
		$str .= $this->child->to_xml();
		$str .= '</update>';
        return $str;
	}

	/*! return starting tag for XML string
	*/
	public function to_xml_start(){
		$str="<update ";
		$str .= 'status="'.$this->data['type'].'" ';
		$str .= 'id="'.$this->data['dataId'].'" ';
		$str .= 'parent="'.$this->get_parent_id().'"';
		$str .= '>';
		$str .= $this->child->to_xml_start();
		return $str;
	}

	/*! return ending tag for XML string
	*/
	public function to_xml_end(){
		$str = $this->child->to_xml_end();
		$str .= '</update>';
		return $str;
	}

	/*! returns false for outputing only current item without child items
	*/
	public function has_kids(){
		return false;
	}

	/*! sets count of child items
		@param value
			count of child items
	*/
	public function set_kids($value){
		if (method_exists($this->child, 'set_kids')) {
			$this->child->set_kids($value);
		}
	}

	/*! sets attribute for item
	*/
	public function set_attribute($name, $value){
		if (method_exists($this->child, 'set_attribute')) {
			LogMaster::log("setting attribute: \nname = {$name}\nvalue = {$value}");
			$this->child->set_attribute($name, $value);
		} else {
			LogMaster::log("set_attribute method doesn't exists");
		}
	}
}


class DataUpdate{
	
	protected $table; //!< table , where actions are stored
	protected $url; //!< url for notification service, optional
    protected $sql; //!< DB wrapper object
    protected $config; //!< DBConfig object
    protected $request; //!< DBRequestConfig object
    protected $event;
    protected $item_class;
    protected $demu;
	
	//protected $config;//!< DataConfig instance
	//protected $request;//!< DataRequestConfig instance
	
	/*! constructor
	  
	  @param connector 
	     Connector object
	  @param config
	     DataConfig object
	  @param request
	     DataRequestConfig object
	*/
	function __construct($sql, $config, $request, $table, $url){
        $this->config= $config;
        $this->request= $request;
        $this->sql = $sql;
        $this->table=$table;
        $this->url=$url;
        $this->demu = false;
	}

    public function set_demultiplexor($path){
        $this->demu = $path;
    }

    public function set_event($master, $name){
        $this->event = $master;
        $this->item_class = $name;
    }
   	
	private function select_update($actions_table, $join_table, $id_field_name, $version, $user) {
		$sql = "SELECT * FROM  {$actions_table}";
		$sql .= " LEFT OUTER JOIN {$join_table} ON ";
		$sql .= "{$actions_table}.DATAID = {$join_table}.{$id_field_name} ";
		$sql .= "WHERE {$actions_table}.ID > '{$version}' AND {$actions_table}.USER <> '{$user}'";
		return $sql;
	}

	private function get_update_max_version() {
		$sql = "SELECT MAX(id) as VERSION FROM {$this->table}";
		$res = $this->sql->query($sql);
		$data = $this->sql->get_next($res);
		
		if ($data == false || $data['VERSION'] == false) 
			return 1;
		else
			return $data['VERSION'];
	}

	private function log_update_action($actions_table, $dataId, $status, $user) {
		$sql = "INSERT INTO {$actions_table} (DATAID, TYPE, USER) VALUES ('{$dataId}', '{$status}', '{$user}')";
		$this->sql->query($sql);
        if ($this->demu)
            file_get_contents($this->demu);
	}




	/*! records operations in actions_table
		@param action
			DataAction object
	*/
	public function log_operations($action) {
		$type = 	$this->sql->escape($action->get_status());
		$dataId = 	$this->sql->escape($action->get_new_id());
		$user = 	$this->sql->escape($this->request->get_user());
		if ($type!="error" && $type!="invalid" && $type !="collision") {
			$this->log_update_action($this->table, $dataId, $type, $user);
		}
	}


	/*! return action version in XMl format
	*/
	public function get_version() {
		$version = $this->get_update_max_version();
		return "<userdata name='version'>".$version."</userdata>";
	}


	/*! adds action version in output XML as userdata
	*/
	public function version_output($conn, $out) {
		$out->add($this->get_version());
	}


	/*! create update actions in XML-format and sends it to output
	*/
	public function get_updates() {
		$sub_request = new DataRequestConfig($this->request);
		$version =	$this->request->get_version();
		$user = 	$this->request->get_user();

		$sub_request->parse_sql($this->select_update($this->table, $this->request->get_source(), $this->config->id['db_name'], $version, $user));
		$sub_request->set_relation(false);

		$output = $this->render_set($this->sql->select($sub_request), $this->item_class);
        
		ob_clean();
		header("Content-type:text/xml");
        
		echo $this->updates_start();
		echo $this->get_version();
		echo $output;
		echo $this->updates_end();
	}

    
	protected function render_set($res, $name){
		$output="";
		$index=0;
		while ($data=$this->sql->get_next($res)){
			$data = new DataItemUpdate($data,$this->config,$index, $name);
			$this->event->trigger("beforeRender",$data);
			$output.=$data->to_xml();
			$index++;
		}
		return $output;
	}

	/*! returns update start string
	*/
	protected function updates_start() {
		$start = '<updates>';
		return $start;
	}

	/*! returns update end string
	*/
	protected function updates_end() {
		$start = '</updates>';
		return $start;
	}

	/*! checks if action version given by client is deprecated
		@param action
			DataAction object
	*/
	public function check_collision($action) {
		$version =	$this->sql->escape($this->request->get_version());
		//$user = 	$this->sql->escape($this->request->get_user());
		$last_version = $this->get_update_max_version();
		if (($last_version > $version)&&($action->get_status() == 'update')) {
			$action->error();
			$action->set_status('collision');
		}
	}
}

//original name was lx_externalinput_clean
//renamed to prevent possible conflicts
class dhx_externalinput_clean {
    // this basic clean should clean html code from
    // lot of possible malicious code for Cross Site Scripting
    // use it whereever you get external input    
    
    // you can also set $filterOut to some use html cleaning, but I don't know of any code, which could
    //  exploit that. But if you want to be sure, set it to eg. array("Tidy","Dom");
    static function basic($string, $filterIn = array("Tidy","Dom","Striptags"), $filterOut = "none") {
        $string = self::tidyUp($string, $filterIn);
        $string = str_replace(array("&amp;", "&lt;", "&gt;"), array("&amp;amp;", "&amp;lt;", "&amp;gt;"), $string);
        
        // fix &entitiy\n;
        $string = preg_replace('#(&\#*\w+)[\x00-\x20]+;#u', "$1;", $string);
        $string = preg_replace('#(&\#x*)([0-9A-F]+);*#iu', "$1$2;", $string);

        $string = html_entity_decode($string, ENT_COMPAT, "UTF-8");
        
        // remove any attribute starting with "on" or xmlns
        $string = preg_replace('#(<[^>]+[\x00-\x20\"\'\/])(on|xmlns)[^>]*>#iUu', "$1>", $string);
        
        // remove javascript: and vbscript: protocol
        $string = preg_replace('#([a-z]*)[\x00-\x20\/]*=[\x00-\x20\/]*([\`\'\"]*)[\x00-\x20\/]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iUu', '$1=$2nojavascript...', $string);
        $string = preg_replace('#([a-z]*)[\x00-\x20\/]*=[\x00-\x20\/]*([\`\'\"]*)[\x00-\x20\/]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iUu', '$1=$2novbscript...', $string);
        $string = preg_replace('#([a-z]*)[\x00-\x20\/]*=[\x00-\x20\/]*([\`\'\"]*)[\x00-\x20\/]*-moz-binding[\x00-\x20]*:#Uu', '$1=$2nomozbinding...', $string);
        $string = preg_replace('#([a-z]*)[\x00-\x20\/]*=[\x00-\x20\/]*([\`\'\"]*)[\x00-\x20\/]*data[\x00-\x20]*:#Uu', '$1=$2nodata...', $string);
        
        //remove any style attributes, IE allows too much stupid things in them, eg.
        //<span style="width: expression(alert('Ping!'));"></span> 
        // and in general you really don't want style declarations in your UGC

        $string = preg_replace('#(<[^>]+[\x00-\x20\"\'\/])style[^>]*>#iUu', "$1>", $string);

        //remove namespaced elements (we do not need them...)
        $string = preg_replace('#</*\w+:\w[^>]*>#i', "", $string);
        
        //remove really unwanted tags
        do {
            $oldstring = $string;
            $string = preg_replace('#</*(applet|meta|xml|blink|link|style|script|embed|object|iframe|frame|frameset|ilayer|layer|bgsound|title|base)[^>]*>#i', "", $string);
        } while ($oldstring != $string);
        
        return self::tidyUp($string, $filterOut);
    }
    
    static function tidyUp($string, $filters) {
        if (is_array($filters)) {
            foreach ($filters as $filter) {
                $return = self::tidyUpWithFilter($string, $filter);
                if ($return !== false) {
                    return $return;
                }
            }
        } else {
            $return = self::tidyUpWithFilter($string, $filters);
        }
        // if no filter matched, use the Striptags filter to be sure.
        if ($return === false) {
            return self::tidyUpModuleStriptags($string);
        } else {
            return $return;
        }
    }
    
    static private function tidyUpWithFilter($string, $filter) {
        if (is_callable(array("self", "tidyUpModule" . $filter))) {
            return call_user_func(array("self", "tidyUpModule" . $filter), $string);
        }
        return false;
    }
    
    static private function tidyUpModuleStriptags($string) {
        
        return strip_tags($string);
    }
    
    static private function tidyUpModuleNone($string) {
        return $string;
    }
    
    static private function tidyUpModuleDom($string) {
        $dom = new domdocument();
        @$dom->loadHTML("<html><body>" . $string . "</body></html>");
        $string = '';
        foreach ($dom->documentElement->firstChild->childNodes as $child) {
            $string .= $dom->saveXML($child);
        }
        return $string;
    }
    
    static private function tidyUpModuleTidy($string) {
        if (class_exists("tidy")) {
            $tidy = new tidy();
            $tidyOptions = array("output-xhtml" => true, 
                                 "show-body-only" => true, 
                                 "clean" => true, 
                                 "wrap" => "350", 
                                 "indent" => true, 
                                 "indent-spaces" => 1,
                                 "ascii-chars" => false, 
                                 "wrap-attributes" => false, 
                                 "alt-text" => "", 
                                 "doctype" => "loose", 
                                 "numeric-entities" => true, 
                                 "drop-proprietary-attributes" => true,
                                 "enclose-text" => false,
                                 "enclose-block-text" => false
 
            );
            $tidy->parseString($string, $tidyOptions, "utf8");
            $tidy->cleanRepair();
            return (string) $tidy;
        } else {
            return false;
        }
    }
}

define("DHX_SECURITY_SAFETEXT",  1);
define("DHX_SECURITY_SAFEHTML", 2);
define("DHX_SECURITY_TRUSTED", 3);

class ConnectorSecurity{
    static public $xss = DHX_SECURITY_SAFETEXT;
    static public $security_key = false;

    static private $filterClass = null;
    static function filter($value, $mode = false){
        if ($mode === false)
            $mode = ConnectorSecurity::$xss;

        if ($mode == DHX_SECURITY_TRUSTED)
            return $value;
        if ($mode == DHX_SECURITY_SAFETEXT)
            return filter_var($value, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
        if ($mode == DHX_SECURITY_SAFEHTML){
            if (ConnectorSecurity::$filterClass == null)
                ConnectorSecurity::$filterClass = new dhx_externalinput_clean();
            return ConnectorSecurity::$filterClass->basic($value);
        }
        throw new Error("Invalid security mode:"+$mode);
    }

    static function CSRF_detected(){
        LogMaster::log("[SECURITY] Possible CSRF attack detected", array(
            "referer" => $_SERVER["HTTP_REFERER"],
            "remote" => $_SERVER["REMOTE_ADDR"]
        ));
        LogMaster::log("Request data", $_POST);
        die();
    }
    static function checkCSRF($edit){
        @session_start();

        if (ConnectorSecurity::$security_key){
            if ($edit=== true){
                if (!isset($_POST['dhx_security']))
                    return ConnectorSecurity::CSRF_detected();
                $master_key = $_SESSION['dhx_security'];
                $update_key = $_POST['dhx_security'];
                if ($master_key != $update_key)
                    return ConnectorSecurity::CSRF_detected();

                return "";
            }
            //data loading
            if (!array_key_exists("dhx_security",$_SESSION)){
                $_SESSION["dhx_security"] = md5(uniqid());
            }

            return $_SESSION["dhx_security"];
        }

        return "";
    }

}



