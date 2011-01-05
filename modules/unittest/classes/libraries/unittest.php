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
		// Merge possible default test path(s) from config with the rest
		$paths = array_merge(func_get_args(), Eight::config('unittest.paths', NO, NO));

		// Normalize all test paths
		foreach($paths as $path) {
			$path = str_replace('\\', '/', realpath((string) $path));
		}

		// Take out duplicate test paths after normalization
		$this->paths = array_unique($paths);

		// Loop over each given test path
		foreach($this->paths as $path) {
			// Validate test path
			if(!is_dir($path))
				throw new Eight_Exception('unittest.invalid_test_path', $path);

			// Recursively iterate over each file in the test path
			foreach
			(
				new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::KEY_AS_PATHNAME))
				as $path => $file
			) {
				// Normalize path
				$path = str_replace('\\', '/', $path);

				// The class name should be the same as the file name
				$class = 'Test_'.substr($path, strrpos($path, '/') + 1, -(strlen(EXT)));

				// Skip hidden files
				if(substr($class, 0, 1) === '.')
					continue;

				// Check for duplicate test class name
				if(class_exists($class, NO))
					throw new Eight_Exception('unittest.duplicate_test_class', $class, $path);

				// Include the test class
				include_once $path;

				// Check whether the test class has been found and loaded
				if(!class_exists($class, NO))
					throw new Eight_Exception('unittest.test_class_not_found', $class, $path);

				// Reverse-engineer Test class
				$reflector = new ReflectionClass($class);

				// Test classes must extend UnitTest_Case
				if(!$reflector->isSubclassOf(new ReflectionClass('UnitTest_Case')))
					throw new Eight_Exception('unittest.test_class_extends', $class);

				// Skip disabled Tests
				if($reflector->getConstant('DISABLED') === YES)
					continue;

				// Initialize setup and teardown method triggers
				$setup = $teardown = NO;

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
						if($setup === YES) {
							$object->setup();
						}

						// Run the actual test
						$object->$method_name();

						// Run teardown method
						if($teardown === YES) {
							$object->teardown();
						}

						$this->stats[$class]['total']++;

						// Test passed
						$this->results[$class][$method_name] = YES;
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


