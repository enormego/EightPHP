<?php
/**
 * Adds useful information to the bottom of the current page for debugging and optimization purposes.
 *
 * Benchmarks
 * :  The times and memory usage of benchmarks run by the Benchmark library.
 *
 * Database
 * :  The raw SQL and number of affected rows of Database queries.
 *
 * Session Data
 * :  Data stored in the current session if using the Session library.
 *
 * POST Data
 * :  The name and values of any POST data submitted to the current page.
 *
 * Cookie Data
 * :  All cookies sent for the current request.
 *
 * @package		Modules
 * @subpackage	Profiler
 * @author		EightPHP Development Team
 * @copyright	(c) 2009-2010 EightPHP
 * @license		http://license.eightphp.com
 */
class Profiler_Core {

	protected $profiles = array();
	protected $show;

	public function __construct() {
		// Add all built in profiles to event
		Event::add('profiler.run', array($this, 'benchmarks'));
		Event::add('profiler.run', array($this, 'database'));
		Event::add('profiler.run', array($this, 'session'));
		Event::add('profiler.run', array($this, 'post'));
		Event::add('profiler.run', array($this, 'cookies'));
		Event::add('profiler.run', array($this, 'environment'));
		Event::add('profiler.run', array($this, 'logs'));

		// Add profiler to page output automatically
		Event::add('system.display', array($this, 'render'));

		Eight::log('debug', 'Profiler Library initialized');
	}

	/**
	 * Magic __call method. Creates a new profiler section object.
	 *
	 * @param   string   input type
	 * @param   string   input name
	 * @return  object
	 */
	public function __call($method, $args) {
		if(!$this->show OR (is_array($this->show) and!in_array($args[0], $this->show)))
			return NO;

		// Class name
		$class = 'Profiler_'.ucfirst($method);

		$class = new $class();

		$this->profiles[$args[0]] = $class;

		return $class;
	}

	/**
	 * Disables the profiler for this page only.
	 * Best used when profiler is autoloaded.
	 *
	 * @return  void
	 */
	public function disable() {
		// Removes itself from the event queue
		Event::clear('system.display', array($this, 'render'));
	}

	/**
	 * Render the profiler. Output is added to the bottom of the page by default.
	 *
	 * @param   boolean  return the output if YES
	 * @return  void|string
	 */
	public function render($return = NO) {
		$start = microtime(YES);

		$get = isset($_GET['profiler']) ? explode(',', $_GET['profiler']) : array();
		$this->show = empty($get) ? Eight::config('profiler.show') : $get;

		Event::run('profiler.run', $this);

		$styles = '';
		foreach($this->profiles as $profile) {
			$styles .= $profile->styles();
		}

		// Don't display if there's no profiles
		if(empty($this->profiles))
			return;

		// Load the profiler view
		$data = array
		(
			'profiles' => $this->profiles,
			'styles'   => $styles,
			'execution_time' => microtime(YES) - $start
		);
		$view = new View('profiler/profiler', $data);

		// Return rendered view if $return is YES
		if($return == YES)
			return $view->render();

		// Add profiler data to the output
		if(stripos(Eight::$output, '</body>') !== NO) {
			// Closing body tag was found, insert the profiler data before it
			Eight::$output = str_ireplace('</body>', $view->render().'</body>', Eight::$output);
		} else {
			// Append the profiler data to the output
			Eight::$output .= $view->render();
		}
	}

	/**
	 * Benchmark times and memory usage from the Benchmark library.
	 *
	 * @return  void
	 */
	public function benchmarks() {
		if(!$table = $this->table('benchmarks'))
			return;

		$table->add_column();
		$table->add_column('ep-column ep-data');
		$table->add_column('ep-column ep-data');
		$table->add_row(array('Benchmarks', 'Time', 'Memory'), 'ep-title', '');

		$benchmarks = Benchmark::get(YES);

		// Moves the first benchmark (total execution time) to the end of the array
		$benchmarks = array_slice($benchmarks, 1) + array_slice($benchmarks, 0, 1);

		str::alternate();
		foreach($benchmarks as $name => $benchmark) {
			// Clean unique id from system benchmark names
			$name = ucwords(str_replace(array('_', '-'), ' ', str_replace(SYSTEM_BENCHMARK.'_', '', $name)));

			$data = array($name, number_format($benchmark['time'], 3), number_format($benchmark['memory'] / 1024 / 1024, 2).'MB');
			$class = str::alternate('', 'ep-altrow');

			if($name == 'Total Execution')
				$class = 'ep-totalrow';

			$table->add_row($data, $class);
		}
	}

	/**
	 * Database query benchmarks.
	 *
	 * @return  void
	 */
	public function database() {
		if(!$table = $this->table('database'))
			return;

		$table->add_column();
		$table->add_column('ep-column ep-data');
		$table->add_column('ep-column ep-data');
		$table->add_row(array('Queries', 'Time', 'Rows'), 'ep-title', '');

		$queries = Database::$benchmarks;

		str::alternate();
		$total_time = $total_rows = 0;
		foreach($queries as $query) {
			$data = array($query['query'], number_format($query['time'], 3), $query['rows']);
			$class = str::alternate('', 'ep-altrow');
			$table->add_row($data, $class);
			$total_time += $query['time'];
			$total_rows += $query['rows'];
		}

		$data = array('Total: ' . count($queries), number_format($total_time, 3), $total_rows);
		$table->add_row($data, 'ep-totalrow');
	}

	/**
	 * Session data.
	 *
	 * @return  void
	 */
	public function session() {
		if(empty($_SESSION)) return;

		if(!$table = $this->table('session'))
			return;

		$table->add_column('ep-name');
		$table->add_column();
		$table->add_row(array('Session', 'Value'), 'ep-title', '');

		str::alternate();
		foreach($_SESSION as $name => $value) {
			if(is_object($value)) {
				$value = get_class($value).' [object]';
			}

			$data = array($name, $value);
			$class = str::alternate('', 'ep-altrow');
			$table->add_row($data, $class);
		}
	}

	/**
	 * POST data.
	 *
	 * @return  void
	 */
	public function post() {
		if(empty($_POST)) return;

		if(!$table = $this->table('post'))
			return;

		$table->add_column('ep-name');
		$table->add_column();
		$table->add_row(array('POST', 'Value'), 'ep-title', '');

		str::alternate();
		foreach($_POST as $name => $value) {
			$data = array($name, $value);
			$class = str::alternate('', 'ep-altrow');
			$table->add_row($data, $class);
		}
	}

	/**
	 * Cookie data.
	 *
	 * @return  void
	 */
	public function cookies() {
		if(empty($_COOKIE)) return;

		if(!$table = $this->table('cookies'))
			return;

		$table->add_column('ep-name');
		$table->add_column();
		$table->add_row(array('Cookies', 'Value'), 'ep-title', '');

		str::alternate();
		foreach($_COOKIE as $name => $value) {
			$data = array($name, $value);
			$class = str::alternate('', 'ep-altrow');
			$table->add_row($data, $class);
		}
	}

	/**
	 * Environment data.
	 *
	 * @return  void
	 */
	public function environment() {
		if(empty($_ENV)) return;

		if(!$table = $this->table('environment'))
			return;

		$table->add_column('ep-name');
		$table->add_column();
		$table->add_row(array('Environment', 'Value'), 'ep-title', '');

		str::alternate();
		foreach($_ENV as $name => $value) {
			$data = array($name, $value);
			$class = str::alternate('', 'ep-altrow');
			$table->add_row($data, $class);
		}
	}

	/**
	 * Logs data.
	 *
	 * @return  void
	 */
	public function logs() {
		$logs = Eight::log_get(TRUE);
		if(arr::e($logs)) return;

		if(!$table = $this->table('logs'))
			return;

		$table->add_column('ep-name');
		$table->add_column('ep-column ep-data');
		$table->add_column('ep-column ep-data');
		$table->add_row(array('Log', 'Level', 'Date'), 'ep-title', '');

		str::alternate();
		foreach($logs as $log) {
			$data = array($name, $value);
			$class = str::alternate('', 'ep-altrow');
			$table->add_row(array_reverse($log), $class);
		}
	}
	
}