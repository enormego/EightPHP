<?php

/**
 * UnitTest library.
 *
 * @package		Modules
 * @subpackage	UnitTest
 * @author		EightPHP Development Team
 * @copyright	(c) 2009-2010 EightPHP
 * @license		http://license.eightphp.com
 */

class UnitTest_Core {

	// The path(s) to recursively scan for tests
	protected $paths = array();

	// The results of all tests from every test class
	protected $results = array();

	// Statistics for every test class
	protected $stats = array();

	/**
	 * Sets the test path(s), runs the tests inside and stores the results.
	 *
	 * @param   string(s)  test path(s)
	 * @return  void
	 */
	public function __construct() {
		// Register autoloader
		spl_autoload_register(array($this, '__autoload'), false, false);
		
		// Merge possible default test path(s) from config with the rest
		$paths = array_merge(func_get_args(), Eight::config('unittest.paths', NO, NO));

		// Normalize all test paths
		foreach($paths as $path) {
			$path = str_replace('\\', '/', realpath((string) $path));
		}

		// Take out duplicate test paths after normalization
		$this->paths = array_unique($paths);

		// Loop over each given test path
		foreach($this->paths as $root_path) {
			// Validate test path
			if(!is_dir($root_path))
				throw new Eight_Exception(__('unittest.invalid_test_path', array($root_path)));

			// Recursively iterate over each file in the test path
			foreach
			(
				new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root_path, RecursiveDirectoryIterator::KEY_AS_PATHNAME))
				as $path => $file
			) {
				// Normalize path
				$path = str_replace('\\', '/', $path);

				// Build class name based on the file path
				$class = ltrim(substr($path, strlen($root_path)), '/');
				$class = 'Test_'.preg_replace("#_([a-z])#e", "'_'.ucfirst('\\1')", ucfirst(str_replace("/", "_", substr($class, 0, strlen($class) - strlen(EXT)))));

				// Skip hidden files
				if(substr($class, 0, 1) === '.')
					continue;

				// Check whether the test class has been found and loaded
				if(!class_exists($class, TRUE))
					throw new Eight_Exception(__('unittest.test_class_not_found', array($class, $path)));

				// Reverse-engineer Test class
				$reflector = new ReflectionClass($class);

				// Skip any abstract classes or interfaces
				if($reflector->isAbstract() || $reflector->isInterface())
					continue;

				// Test classes must extend UnitTest_Case
				if(!$reflector->isSubclassOf(new ReflectionClass('UnitTest_Case')))
					throw new Eight_Exception(__('unittest.test_class_extends', array($class)));

				// Skip disabled Tests
				if($reflector->getConstant('DISABLED') === TRUE)
					continue;

				// Initialize setup and teardown method triggers
				$setup = $teardown = FALSE;

				// Look for valid setup and teardown methods
				foreach(array('setup', 'teardown') as $method_name) {
					if($reflector->hasMethod($method_name)) {
						$method = new ReflectionMethod($class, $method_name);
						$$method_name = ($method->isPublic() and!$method->isStatic() and $method->getNumberOfRequiredParameters() === 0);
					}
				}

				// Initialize test class results and stats
				$this->results[$class] = array();
				$this->stats[$class] = array
				(
					'passed' => 0,
					'failed' => 0,
					'errors' => 0,
					'total' => 0,
					'score'  => 0,
				);

				// Loop through all the class methods
				foreach($reflector->getMethods() as $method) {
					// Skip invalid test methods
					if(!$method->isPublic() OR $method->isStatic() OR $method->getNumberOfRequiredParameters() !== 0)
						continue;

					// Test methods should be suffixed with "_test"
					if(substr($method_name = $method->getName(), -5) !== '_test')
						continue;

					// Instantiate Test class
					$object = new $class;

					try
					{
						// Run setup method
						if($setup === TRUE) {
							$object->setup();
						}

						// Run the actual test
						$object->$method_name();

						// Run teardown method
						if($teardown === TRUE) {
							$object->teardown();
						}

						$this->stats[$class]['total']++;

						// Test passed
						$this->results[$class][$method_name] = TRUE;
						$this->stats[$class]['passed']++;

					}
					catch (UnitTest_Exception $e) {
						$this->stats[$class]['total']++;
						// Test failed
						$this->results[$class][$method_name] = $e;
						$this->stats[$class]['failed']++;
					}
					catch (Exception $e) {
						$this->stats[$class]['total']++;

						// Test error
						$this->results[$class][$method_name] = $e;
						$this->stats[$class]['errors']++;
					}

					// Calculate score
					$this->stats[$class]['score'] = $this->stats[$class]['passed'] * 100 / $this->stats[$class]['total'];

					// Cleanup
					unset($object);
				}
			}
		}
	}
	
	/**
	 * Attempts to autoload tests
	 */
	public function __autoload($class) {
		if(!str::starts_with($class, 'test_'))
			return FALSE;
		
		if(class_exists($class, FALSE))
			return TRUE;

		$filename = str_replace('test/', 'tests/', str_replace('_', '/', strtolower($class)));

		if(!($path = Eight::find_file('classes', $filename, FALSE)))
			return FALSE;
			
		require $path;
		
		return TRUE;
	}

	/**
	 * Generates nice test results.
	 *
	 * @param   boolean  hide passed tests from the report
	 * @return  string   rendered test results html
	 */
	public function report($hide_passed = nil) {
		// No tests found
		if(empty($this->results))
			return Eight::lang('unittest.no_tests_found');

		// Hide passed tests from the report?
		$hide_passed = (bool) (($hide_passed !== nil) ? $hide_passed : Eight::config('unittest.hide_passed', NO, NO));

		// Render unittest report
		return View::factory('eight/unittest', array(
			'results' => $this->results,
			'stats' => $this->stats,
			'hide_passed' => $hide_passed
		))->render();
	}

	/**
	 * Magically convert this object to a string.
	 *
	 * @return  string  test report
	 */
	public function __toString() {
		return $this->report();
	}

	/**
	 * Magically gets a UnitTest property.
	 *
	 * @param   string  property name
	 * @return  mixed   variable value if the property is found
	 * @return  void    if the property is not found
	 */
	public function __get($key) {
		if(isset($this->$key))
			return $this->$key;
	}

}


