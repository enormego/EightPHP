<?php 
/**
 * Eight event subject. Uses the SPL observer pattern.
 *
 * $Id: Calendar_Event_Subject.php 3917 2009-01-21 03:06:22Z zombor $
 *
 * @package		Modules
 * @subpackage	Calendar
 * @author		enormego
 * @copyright	(c) 2009-2010 enormego
 * @license		http://license.eightphp.com
 */
abstract class Calendar_Event_Subject implements SplSubject {

	// Attached subject listeners
	protected $listeners = array();

	/**
	 * Attach an observer to the object.
	 *
	 * @chainable
	 * @param   object  Calendar_Event_Observer
	 * @return  object
	 */
	public function attach(SplObserver $obj)
	{
		if ( ! ($obj instanceof Calendar_Event_Observer))
			throw new Eight_Exception('eventable.invalid_observer', get_class($obj), get_class($this));

		// Add a new listener
		$this->listeners[spl_object_hash($obj)] = $obj;

		return $this;
	}

	/**
	 * Detach an observer from the object.
	 *
	 * @chainable
	 * @param   object  Calendar_Event_Observer
	 * @return  object
	 */
	public function detach(SplObserver $obj)
	{
		// Remove the listener
		unset($this->listeners[spl_object_hash($obj)]);

		return $this;
	}

	/**
	 * Notify all attached observers of a new message.
	 *
	 * @chainable
	 * @param   mixed   message string, object, or array
	 * @return  object
	 */
	public function notify($message)
	{
		foreach ($this->listeners as $obj)
		{
			$obj->notify($message);
		}

		return $this;
	}

} // End Event Subject