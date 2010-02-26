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
abstract class Controller_Template extends Controller_Core {

	protected $auto_render = true;
	protected $html;
	protected $template = array();
	
	public $title;
	private $wrapper;
	private $stylesheets = array();
	private $jscripts = array();
	
	static $data = array();

	/**
	 * Template loading and setup routine.
	 */
	public function __construct() {
		parent::__construct();
		if($this->auto_render == YES) {
			// Render the template immediately after the controller method
			Event::add('system.post_controller', array($this, '_render'));
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
		
		$data = array_merge($data, self::$data, $this->template);
		
		echo View::factory($this->wrapper, $data)->render();
	}
	
	public function set_wrapper($view) {
		$this->wrapper = 'wrappers/'.$view;
	}
	
	public function add_stylesheet($name) {
		$this->stylesheets[] = $name;
	}
	
	public function remove_stylesheet($name) {
		foreach($this->stylesheets as $index => $sheet) {
			if($sheet == $name) {
				unset($this->stylesheets[$index]);
			}
		}
	}
	
	public function add_javascript($name) {
		$this->jscripts[] = $name;
	}
	
	public function remove_javascript($name) {
		foreach($this->jscripts as $index => $jscript) {
			if($jscript == $name) {
				unset($this->jscripts[$index]);
			}
		}
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