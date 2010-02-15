<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Provides database access in a platform agnostic way, using simple query building blocks.
 *
 * $Id: Database.php 2303 2008-03-14 01:00:54Z zombor $
 *
 * @package    Core
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Database_Insert_Core {

	protected $db;

	protected $table   = '';
	protected $columns = array();
	protected $values  = array();
	protected $select  = NULL;

	public function __construct($table, $columns = NULL, Database_Driver $db)
	{
		$this->db = $db;

		$this->table = $table;

		if (is_array($columns))
		{
			// Set the columns to insert into
			$this->columns($columns);
		}
	}

	public function __toString()
	{
		return $this->build();
	}

	public function columns(array $columns)
	{
		$this->columns = $columns;

		return $this;
	}

	public function values($values)
	{
		if (is_object($values) AND $values instanceof Database_Select)
		{
			// Use a SELECT for values
			$this->select = $values;
		}
		else
		{
			// Add a new set of values
			$this->values[] = (array) $values;
		}

		return $this;
	}

	public function build()
	{
		// Compile
	}

} // End Database Insert