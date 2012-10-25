<?php
/*
 * Created by MRR on 20120528
 * TODO: We should really be using doctrine instead of mysqli - MRR 20120528 
 */

require_once('algorithms_local.php');

class DataSource extends mysqli
{
    protected $options;
    private $dirty=false;
    private $id = null;

    function __construct($options=null) {
        $this->options = array(
            'script_url' => $this->getFullUrl().'/',
            'upload_dir' => dirname($_SERVER['SCRIPT_FILENAME']).'/files/',
            'upload_url' => $this->getFullUrl().'/files/',
            'param_name' => 'files',
	    'mysqli_connection'	 => get_mysqli_connection_options(),
            'table'      => 'datasources',
	    'autoupdate' => true, // Update automatically on dirty changes. False, caller is responsible for calling $this->save()
	    'autoescapehtml' => true, // Automatically HTML-escape strings retrieved from the database 
	    'id'         => null,
	    'debug'	 => true, //false,
            );
        if ($options) {
            $this->options = array_replace_recursive($this->options, $options);
        }

        // turn of error reporting
        //mysqli_report(MYSQLI_REPORT_OFF);
        //@parent::__construct($this->options['mysqli_connection']);
        @parent::__construct(
		$this->options['mysqli_connection']['host'],
		$this->options['mysqli_connection']['user'],
		$this->options['mysqli_connection']['pass'],
		$this->options['mysqli_connection']['dbname'],
		$this->options['mysqli_connection']['port'],
		$this->options['mysqli_connection']['sock']
	);
        if( mysqli_connect_errno() ) {
            throw new exception(mysqli_connect_error(), mysqli_connect_errno()); 
        }

	if($this->options['id']) {
		// We have an id, go ahead and retrieve the data
		$this->id($this->options['id']);
		$this->retrieve();
	}
    }

    public function id($id=null) {
	if($id) { 
		$this->id = $id;
	}
	return $this->id;
    }

    // retrieve the current id from the database
    public function retrieve() {
	if (!$this->id()) { 
            error_log("ERROR201205281444: Method retrieve must have an id"); 
            debug_print_backtrace(); 
            return null; 
        }
	$query = "SELECT * FROM ".$this->options["table"]." WHERE id='".$this->id()."'";
	$result = $this->query($query);
	$this->data = $result->fetch_assoc();
	$this->dirty = false; // Unset the dirty flag as we just retrieved/refreshed
	return $this->query($query);
    }

    public static function setOptions( array $opt ) {
        self::$options = array_merge(self::$options, $opt);
    }

    public function prepare($query) {
        $stmt = new mysqli_stmt($this, $query);
        return $stmt;
    }    


    /* Should probably move this up to a more common place like algorithms_local.php - MRR20120528 */
    protected function getFullUrl() {
        $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
      	return
    		($https ? 'https://' : 'http://').
    		(!empty($_SERVER['REMOTE_USER']) ? $_SERVER['REMOTE_USER'].'@' : '').
    		(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : ($_SERVER['SERVER_NAME'].
    		($https && $_SERVER['SERVER_PORT'] === 443 ||
    		$_SERVER['SERVER_PORT'] === 80 ? '' : ':'.$_SERVER['SERVER_PORT']))).
    		substr($_SERVER['SCRIPT_NAME'],0, strrpos($_SERVER['SCRIPT_NAME'], '/'));
    }

    public function field ($field, $val=null) {
		if(isset($val)) {
			//echo "val=".$val;
			$this->dirty=true; // The recordset is now dirty	
			$this->data[$field] = $val; // Gues $val is always an array?
		}
		if(isset($this->data[$field])) {
			//var_dump($this->data);
			$value = $this->data[$field];	
			if ($this->options["autoescapehtml"]) {
				$value = htmlentities($value);
			}
			return $value;
		} else {
            		throw new exception("There is no field ".$field." in the dataset.", "201205281747"); 
			return null;
		}
    }

    public function save () {
	// Write the data back out to the database
	if($this->dirty) {
		foreach($this->data as $field=>$value){
			if($this->options["debug"]) { error_log("DEBUG201205291406: $field value=".$value."\n"); }
			$value = $this->real_escape_string($value);
			if($value=="now()") {
				$update_args[]=$field."=".$value.""; // Leave off the quotes for now()
			} else {
				$update_args[]=$field."='".$value."'";
			}
		}
		$sql='UPDATE '.$this->options["table"].' SET '.implode(',',$update_args)." WHERE id=".$this->id();
		if($this->options["debug"]) { error_log("DEBUG201205291407: SQL=".$sql."\n"); }
		return $this->query($sql);
	} else {
		if($this->options["debug"]) { error_log("DEBUG201205291408: Nothing to save -- no changes\n"); }
	}
	return null;
    }
    // Add a new data_source to the database
    public function add ($addData = array()) {

	$default_fields = array(
		"id"=>0,
		//"id_seq"=>'',
		"user_id"=>'',
		"type"=>'CSV',
		"location"=>'algorithms',
		"filesystem_name"=>'',
		"name"=>'',
		"description"=>'',
		"version"=>'',
		"ip_address"=>'',
		"size"=>'',
		"created"=>'now()',
		"lastModified"=>'now()', 
		//"numRows"=>'',
		//"numCols"=>'',
		//"importMD5"=>'',
		//"status"=>'',
 		//"privacy"=>'',
		//"internal_notes"=>'', 
		//"customer_notes"=>''
	);	
        $addData = array_replace_recursive($default_fields, $addData);
	//$query = "INSERT INTO ".$this->options['table']." (id, ";
		$insert_cols=array();
		$insert_vals=array();
		foreach($addData as $field=>$value){
			if($this->options["debug"]) { error_log("DEBUG201205291409: $field value=".$value."\n"); }
			$insert_cols[]=$field;
			$value = $this->real_escape_string($value);
			if($value=="now()") {
				$insert_vals[]=$value;// Leave off the quotes for now()
			} else {
				$insert_vals[]="'".$value."'";
			}
		}
		$sql='INSERT INTO '.$this->options["table"].' ('.implode(',',$insert_cols).") VALUES (".implode(',',$insert_vals) .")";
		if($this->options["debug"]) { error_log("DEBUG201205291410: $sql\n"); }
		$this->query($sql);
		if($this->options["debug"]) { error_log("DEBUG201205291411: INSERT_ID=".$this->insert_id."\n"); }
		$this->id($this->insert_id);
		$this->retrieve();	
		return $this->id();

    }

    public function __call($name, $arguments) {
	if($this->options["debug"]) { error_log("DEBUG201205291412: Calling $name with ".implode(', ', $arguments)."\n"); }
	if (isset($arguments) && !empty($arguments)) {
		return $this->field($name, $arguments[0]); // Seems $arguments is always an array, so we take on the first val also not sure what to do if we every want a null...
	} else {
		return $this->field($name);
	}
    }

    public function __destruct() {
	if ($this->options['autoupdate']) {
		// Save before we are destroyed
		$this->save();
	}
    }
}



?>
