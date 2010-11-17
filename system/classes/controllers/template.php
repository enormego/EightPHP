<?php
/**
 * Allows a template to be automatically loaded and displayed. Display can be
 * dynamically turned off in the controller methods, and the template file
 * can be overloaded.
 *
 * To use it, declare your controller to extend this class:
 * `class Controller_YourController extends Controller_Template`
 *
 * @package		System
 * @subpackage	Controllers
 * @author		EightPHP Development Team
 * @copyright	(c) 2009-2010 EightPHP
 * @license		http://license.eightphp.com
 */
abstract class Controller_Template_Core extends Controller_Core {

	protected $auto_render = TRUE;
	protected $html;
	protected $title;
	protected $view;
	
	private $stylesheets = array();
	private $jscripts = array();
	
	public $template = array();
	
	/**
	 * Template loading and setup routine.
	 */
	public function __construct() {
		parent::__construct();
		
		if($this->auto_render == YES) {
			// Render the template along with the system display stuff
			Event::add_before('system.display', array($this, 'render'), array($this, '_render'));
		}
	}

	public function _render($data=array()) {
		if(browser::is_gecko()) $browser = "gecko";
		if(browser::is_safari()) $browser = "webkit";
		if(browser::is_iphone()) $browser = "iphone";
		if(browser::is_ie()) {
			if(browser::is_ie(9)) $browser = "ie ie9";
			elseif(browser::is_ie(8)) $browser = "ie ie8";
			elseif(browser::is_ie(7)) $browser = "ie ie7";
			elseif(browser::is_ie(6)) $browser = "ie ie6";
			else $browser = "ie";
		}
		
		$data['browser'] = $browser;
		
		$data['controller'] = Router::$controller;
		$data['method'] = Router::$method;
		
		$data['title'] = $this->title;
		$data['contents'] = $this->html;
		$data['stylesheets'] = $this->stylesheets;
		$data['jscripts'] = $this->jscripts;
		
		// Check for variable conflicts
		if(count($conflicts = array_intersect_key($data, $this->template)) > 0) {
			throw new Eight_Exception('The following variable(s) are already in use by the Controller_Template::_render() method and can NOT be used: '.implode(',', array_keys($conflicts)));
		}
		
		// Safely merge data
		$data = array_merge($data, $this->template);
		
		// Tack on our output to Eight's output buffer
		Eight::$output .= View::factory($this->wrapper, $data)->render();
	}
	
	public function set_wrapper($view) {
		// Set wrapper filename
		$this->wrapper = 'wrappers/'.$view;
	}
	
	public function add_stylesheet($name) {
		if(!in_array($name, $this->stylesheets)) {
			$this->stylesheets[] = $name;
		}
	}
	
	public function remove_stylesheet($name) {
		arr::remove_value($this->stylesheets, $name);
	}
	
	public function add_javascript($name) {
		if(!in_array($name, $this->jscripts)) {
			$this->jscripts[] = $name;
		}
	}
	
	public function remove_javascript($name) {
		arr::remove_value($this->jscripts, $name);
	}
	
	public function title() {
		return $this->title;
	}

	public function set_title($title, $base=false) {
		if($base) {
			$this->title = $title;
		} else {
			if(!empty($title)) {
				$this->title .= ' - '.$title;
			}
		}
	}

} // End Template_Controller