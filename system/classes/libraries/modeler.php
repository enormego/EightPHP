<?php
/**
 * Lightweight ORM Library build on-top of the Model library
 *
 * @package		System
 * @subpackage	Libraries
 * @author		EightPHP Development Team
 * @copyright	(c) 2009-2010 EightPHP
 * @license		http://license.eightphp.com
 */

class Modeler_Core extends Model {
	// Database table name
	protected $table_name = '';
	
	// Table primary key
	protected $primary_key = 'id';
	
	// Column prefix
	protected $column_prefix = NULL;
 
	// Table fields and default values
	protected $data = array();
 	
	// The default database connection to use
	public $default_conn = 'default';
	
	// Tells you if the Model was loaded from the DB or not
	protected $loaded = FALSE;
	
	// Sets the active class, PHP workaround
	protected $active_class = NULL;
	
	// Holds the database connection
	public $db = NULL;
	
	// Tells the class we ran the "set" function
	public $ran_set = FALSE;
	
	// Run all queries on master db
	protected $use_master = FALSE;
	
	public function __construct($data = NULL, $active_class = NULL) {
		parent::__construct();
		
		// Bit of a work around to fix this annoying issue with PHP not inheriting correctly
		$this->active_class = $active_class;
		if($this->active_class == NULL OR empty($this->active_class)) {
			$obj = debug_backtrace();
			$obj = $obj[0]['object'];
			$this->active_class = get_class($obj);
		}
		
		$this->db = $this->db();
		
		if ($data != NULL) {
			if(!is_array($data)) {
				$data = array($this->primary_key => $data);
			}
			
			$this->db->use_master($this->use_master);
			$new_data = $this->db->getwhere($this->table_name, $data)->result(FALSE);
			
			if(is_object($new_data) && count($new_data) > 0) {
				if (count($new_data) == 1 AND $new_data = $new_data->current()) {
					$data = $new_data;
					$this->loaded = TRUE;
				}
			} else {
				$this->loaded = FALSE;
			}
			
			$this->set($data);
		} else {
			$this->loaded = FALSE;
		}
	}
	
	/**
	 * Handles object serialization for class objects
	 */
	public function __sleep() {
		// Return all class keys minus db, core and obj
		return array_diff_key(get_object_vars($this), array('db' => NULL, 'core' => NULL, 'obj' => NULL));
	}
	
	public function valid() {
		if(isset($this->data[$this->primary_key]) && !str::e($this->data[$this->primary_key])) {
			$this->loaded = TRUE;
			return TRUE;
		}
		
		return $this->loaded;
	}
 
	public function __destruct() {
		return TRUE;
	}
 
	public function __get($key) {
		if(array_key_exists($key, $this->data)) {
			if(method_exists($this, $key)) {
				return $this->$key();
			} else {
				return $this->data[$key];
			}
		} elseif(array_key_exists($this->column_prefix.$key, $this->data)) {
			$key = $this->column_prefix.$key;
			
			if(method_exists($this, $key)) {
				return $this->$key();
			} else {
				return $this->data[$key];
			}
		} else {
			if(method_exists($this, $key)) {
				return $this->$key();
			} else {
				return $this->$key;
			}
		}
	}
 
	public function __set($key, $value) {
		if(array_key_exists($key, $this->data)) {
			if(method_exists($this, "set_".$key)) {
				$this->{"set_".$key}($value);
			} else {
				$this->data[$key] = $value;
			}
		} elseif(array_key_exists($this->column_prefix.$key, $this->data)) {
			if(method_exists($this, "set_".$this->column_prefix.$key)) {
				$this->{"set_".$this->column_prefix.$key}($value);
			} else {
				$this->data[$this->column_prefix.$key] = $value;
			}
		} else {
			$this->$key = $value;
		}
	}

	public static function factory($model = FALSE, $id = FALSE) {
		$model = empty($model) ? __CLASS__ : 'Model_'.ucfirst($model);
		$obj = new $model($id);
		$obj->active_class = $model;
		unset($model, $id);
		return $obj;
	}
 
	public function set($key, $value="") {
		// Tell the class we're running set
		$this->ran_set = TRUE;
		
		$data = is_array($key) ? $key : array($key => $value);
		foreach($data as $key => $value) {
			if(array_key_exists($key, $this->data)) {
				$this->$key = $value;
			} elseif(array_key_exists($this->column_prefix.$key, $this->data)) {
				$this->{$this->column_prefix.$key} = $value;
			}
		}
		
		return $this;
	}
 
	public function save() {
		// Do an update or insert?
		$this->db->use_master($this->use_master);
		if($this->valid()) {
			return count($this->db->update($this->table_name, $this->data, array($this->primary_key => $this->data[$this->primary_key])));
		} else {
			$insert_id = $this->db->insert($this->table_name, $this->data)->insert_id();
			$this->data[$this->primary_key] = $insert_id;
			return $insert_id;
		}
		// If all else fails.
		return FALSE;
	}
 	
	/**
	 * Update
	 * 		This is a performance method designed to work statically without
	 * 		creating the overhead that is associated with loading a Modeler
	 * 		object.
	 * 
	 * 		primary key	id OR column => value			mixed
	 * 		column => value  							array
	 */
	public static function update($primary_key, $data) {
		// Check that the supplied data value is an array
		if(!is_array($data)) return FALSE;
		
		// Add support for specifying any column => value with an array on the where part of the update or default to primary key => id
		if(!is_array($primary_key)) {
			$primary_key = array(self::$primary_key => $primary_key);
		}
		
		// Add support for column prefixes
		foreach($data as $k=>$v) {
			if(!array_key_exists($k, self::$data) && in_array(self::$column_prefix.$k, self::$data)) {
				$data[self::$column_prefix.$k] = $v;
				unset($data[$k]);
			}
		}
		
		return self::db()->update(self::$table_name, $data, $primary_key);
	}
	
	public function delete() {
		if ($this->data[$this->primary_key]) {
			$this->db->use_master($this->use_master);
			$this->db->delete($this->table_name, array($this->primary_key => $this->data[$this->primary_key]));
			return $this->__destruct();
		}
	}
 
	public function fetch_all($orderby = NULL, $direction = 'ASC', $where=array()) {
		is_null($orderby) && $orderby = $this->primary_key;
		$class = isset($this->active_class) ? $this->active_class : __CLASS__;
		$this->db->use_master($this->use_master);
		return $this->db->orderby($orderby, $direction)->getwhere($this->table_name, $where)->result_array(TRUE, $class);
	}
 
	public function select_list($key, $display, $orderby = NULL, $direction = 'ASC', $where=array()) {
		$rows = array();
		if(!is_array($display)) {
			$display = explode(",", $display);
		}
		$this->db->use_master($this->use_master);
 		$results = $this->fetch_all($orderby, $direction, $where);
		
		// Support prefixes...
		if(!array_key_exists($key, $this->data)) {
			if(array_key_exists($this->column_prefix.$key, $this->data)) {
				$key = $this->column_prefix.$key;
			}
		}
		
		// Prefixes for the display too...
		foreach($display as $k=>$d) {
			if(!array_key_exists($d, $this->data)) {
				if(array_key_exists($this->column_prefix.$d, $this->data)) {
					$display[$k] = $this->column_prefix.$d;
				}
			}
		}
		
		foreach($results as $row) {
			foreach($display as $d) {
				$rows[$row->$key] .= $row->$d . " ";
			}
			$rows[$row->$key] = trim($rows[$row->$key]);
		}
 
		return $rows;
	}
	
	public function __to_assoc() {
		$assoc = array();
		
		foreach(arr::c($this->data) as $key => $val) {
			$key = str_replace($this->column_prefix, "", $key);
			$assoc[$key] = $val;
		}
		
		return $assoc;
	}
	
	public static function db() {
		if(!is_a(Eight::instance()->db, 'Database')) {
			return Database::instance(Eight::instance()->default_conn);
		} else {
			return Eight::instance()->db;
		}
	}

} // End Modeler Class