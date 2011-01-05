<?php

/**
 * UnitTest Exception
 *
 * @package		Modules
 * @subpackage	UnitTest
 * @author		EightPHP Development Team
 * @copyright	(c) 2009-2010 EightPHP
 * @license		http://license.eightphp.com
 */

class UnitTest_Exception_Core extends Eight_Exception {

	protected $debug = nil;

	/**
	 * Sets exception message and debug info.
	 *
	 * @param   string  message
	 * @param   mixed   debug info
	 * @return  void
	 */
	public function __construct($message, $debug = nil) {
		// Failure message
		parent::__construct("UnitTest Exception", (string) $message);

		// Extra user-defined debug info
		$this->debug = $debug;

		// Overwrite failure location
		$trace = $this->getTrace();
		$this->file = $trace[0]['file'];
		$this->line = $trace[0]['line'];
	}

	/**
	 * Returns the user-defined debug info
	 *
	 * @return  mixed  debug property
	 */
	public function getDebug() {
		return $this->debug;
	}

} // End Eight_Unit_Test_Exception
