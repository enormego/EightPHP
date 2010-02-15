<?php defined('SYSPATH') or die('No direct script access.');

class Database_Expression_Core {

	protected $expression;

	public function __construct($expression)
	{
		$this->expression = $expression;
	}

	public function __toString()
	{
		return $this->build();
	}

	public function build()
	{
		return $this->expression;
	}

} // End Database Expression