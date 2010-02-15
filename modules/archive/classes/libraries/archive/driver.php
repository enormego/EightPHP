<?php
/**
 * Archive driver interface.
 *
 * @version		$Id: driver.php 244 2010-02-11 17:14:39Z shaun $
 *
 * @package		Modules
 * @subpackage	Archive
 * @author		enormego
 * @copyright	(c) 2009-2010 enormego
 * @license		http://license.eightphp.com
 */
abstract class Archive_Driver {

	/**
	 * Creates an archive and optionally, saves it to a file.
	 *
	 * @param   array    filenames to add
	 * @param   string   file to save the archive to
	 * @return  boolean
	 */
	abstract public function create($paths, $filename = NO);

	/**
	 * Add data to the archive.
	 *
	 * @param   string   filename
	 * @param   string   name of file in archive
	 * @return  void
	 */
	abstract public function add_data($file, $name, $contents = nil);

} // End Archive_Driver Interface