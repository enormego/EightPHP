<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Eight event observer. Uses the SPL observer pattern.
 *
 * $Id: Calendar_Event_Observer.php 3917 2009-01-21 03:06:22Z zombor $
 *
 * @package		Modules
 * @subpackage	Calendar
 * @author		enormego
 * @copyright	(c) 2009-2010 enormego
 * @license		http://license.eightphp.com
 */
abstract class Calendar_Event_Observer implements SplObserver {

	// Calling object
	protected $caller;

	/**
	 * Initializes a new observer and attaches the subject as the caller.
	 *
	 * @param   object  Calendar_Event_Subject
	 * @return  void
	 */
	public function __construct(SplSubject $caller)
	{
		// Update the caller
		$this->update($caller);
	}

	/**
	 * Updates the observer subject with a new caller.
	 *
	 * @chainable
	 * @param   object  Calendar_Event_Subject
	 * @return  object
	 */
	public function update(SplSubject $caller)
	{
		if ( ! ($caller instanceof Calendar_Event_Subject))
			throw new Eight_Exception('event.invalid_subject', get_class($caller), get_class($this));

		// Update the caller
		$this->caller = $caller;

		return $this;
	}

	/**
	 * Detaches this observer from the subject.
	 *
	 * @chainable
	 * @return  object
	 */
	public function remove()
	{
		// Detach this observer from the caller
		$this->caller->detach($this);

		return $this;
	}

	/**
	 * Notify the observer of a new message. This function must be defined in
	 * all observers and must take exactly one parameter of any type.
	 *
	 * @param   mixed   message string, object, or array
	 * @return  void
	 */
	abstract public function notify($message);

} // End Event Observer