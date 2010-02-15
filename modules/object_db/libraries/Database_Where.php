<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Provides database access in a platform agnostic way, using simple query building blocks.
 *
 * $Id: Database_Where.php 2303 2008-03-14 01:00:54Z zombor $
 *
 * @package    Core
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Database_Where_Core extends Database_Escape {

	protected $comparisons = array
	(
		'=',
		'!=',
		'<',
		'<=',
		'>',
		'>=',
		'LIKE',
		'NOT LIKE',
		'REGEX',
		'NOT REGEX',
		'IN',
		'NOT IN',
	);
	
	protected $operators = array
	(
		'AND',
		'OR',
	);

	protected $db;

	protected $columns;
	protected $comparison;
	protected $operator;

	public function __construct(array $columns, $comparison, $operator, Database_Driver $db)
	{
		$this->db = $db;

		if ( ! in_array(strtoupper($comparison), $this->comparisons, TRUE))
		{
			// Default to equals
			$comparison = '=';
		}

		if ( ! in_array(strtoupper($operator), $this->operators))
		{
			// Default to AND
			$operator = 'AND';
		}

		$this->columns    = $columns;
		$this->comparison = ' '.strtoupper($comparison).' ';
		$this->operator   = ' '.strtoupper($operator).' ';
	}

	public function op()
	{
		return $this->operator;
	}

	public function build()
	{
		$sql = array();
		foreach ($this->columns as $col => $value)
		{
			$sql[] = $this->escape('column', $col).$this->comparison.$this->escape('value', $value);
		}

		return '('.implode($this->operator, $sql).')';


		if (is_string($group)) // group name was passed
		{
			$config = Kohana::config('database.'.$group);
			$conn = Database::parse_con_string($config['connection']);
			$driver = $conn['type'];
		}
		elseif (is_array($group)) // DB Group was passed
		{
			$conn = Database::parse_con_string($group['connection']);
			$driver = $conn['type'];
		}
		elseif ($group instanceof Database_Driver)
		{
			$driver = $group;
		}

		if (!isset($this->drivers[$driver]))
		{
			// Set driver name
			$driver_class_name = 'Database_'.ucfirst($driver).'_Driver';

			// Load the driver
			if ( ! Kohana::auto_load($driver))
				throw new Kohana_Database_Exception('database.driver_not_supported', $driver_class_name);

			// Initialize the driver
			$this->drivers[$driver] = new $driver_class_name();

			// Validate the driver
			if ( ! ($this->drivers[$driver] instanceof Database_Driver))
				throw new Kohana_Database_Exception('database.driver_not_supported', 'Database drivers must use the Database_Driver interface.');
		}

		// Iterate the where array to build the query portion and return it
		$wheres_left = count($this->where);
		$where_string = '(';
		foreach ($this->where as $where)
		{
			if ($where[0] instanceof Database_Where_Core)
			{
				$where_string.=$where[0]->build();
			}
			else
			{
				$where_string.=$this->drivers[$driver]->escape_column($where[0]['key']).' '.$where[0]['op'].' '.$this->drivers[$driver]->escape($where[0]['value']).($wheres_left-- > 1 ? ' '.$where[1].' ' : '');
			}
		}
		$where_string .= ')';

		return $where_string;
	}

	public function __toString()
	{
		return $this->build();
	}

} // End Database Where