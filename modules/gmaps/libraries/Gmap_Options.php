<?php defined('SYSPATH') or die('No direct script access.');
/**
 * GMaps options container.
 *
 * $Id: Gmap_Options.php 2207 2008-03-01 20:44:52Z armen $
 *
 * @package    Gmaps
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Gmap_Options_Core {

	// Valid options
	protected $valid_options = array
	(
		'Dragging',
		'InfoWindow',
		'DoubleClickZoom',
		'ContinuousZoom',
		'GoogleBar',
		'ScrollWheelZoom'
	);

	// Settable options
	protected $options = array();

	/**
	 * Create a new GMap options 
	 *
	 * @param   array   GMap2 object options
	 * @return  void
	 */
	public function __construct(array $options)
	{
		foreach ($options as $key => $value)
		{
			if (in_array($key, $this->valid_options))
			{
				// Set all valid options
				$this->options[$key] = (bool) $value;
			}
		}
	}

	public function render($tabs = 0)
	{
		// Create the tabs
		$tabs = empty($tabs) ? '' : str_repeat("\t", $tabs);

		// Render each option
		$output = array();
		foreach ($this->options as $option => $value)
		{
			if ($value === TRUE)
			{
				// Add an enable
				$output[] = 'map.enable'.$option.'();';
			}
			else
			{
				// Add a disable
				$output[] = 'map.disable'.$options.'();';
			}
		}

		return implode("\n".$tabs, $output);
	}

} // End Gmap Options