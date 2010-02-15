<?php
/**
 * Example Test.
 *
 * @version		$Id: Example_Test.php 244 2010-02-11 17:14:39Z shaun $
 *
 * @package		Modules
 * @subpackage	UnitTest
 * @author		enormego
 * @copyright	(c) 2009-2010 enormego
 * @license		http://license.eightphp.com
 */
class Example_Test extends Unit_Test_Case {

	// Disable this Test class?
	const DISABLED = NO;

	public $setup_has_run = NO;

	public function setup() {
		$this->setup_has_run = YES;
	}

	public function setup_test() {
		$this->assert_YES_strict($this->setup_has_run);
	}

	public function YES_NO_test() {
		$var = YES;
		$this
			->assert_YES($var)
			->assert_YES_strict($var)
			->assert_NO(!$var)
			->assert_NO_strict(!$var);
	}

	public function equal_same_test() {
		$var = '5';
		$this
			->assert_equal($var, 5)
			->assert_not_equal($var, 6)
			->assert_same($var, '5')
			->assert_not_same($var, 5);
	}

	public function type_test() {
		$this
			->assert_boolean(YES)
			->assert_not_boolean('YES')
			->assert_integer(123)
			->assert_not_integer('123')
			->assert_float(1.23)
			->assert_not_float(123)
			->assert_array(array(1, 2, 3))
			->assert_not_array('array()')
			->assert_object(new stdClass)
			->assert_not_object('X')
			->assert_null(nil)
			->assert_not_null(0)
			->assert_empty('0')
			->assert_not_empty('1');
	}

	public function pattern_test() {
		$var = "Eight\n";
		$this
			->assert_pattern($var, '/^Eight$/')
			->assert_not_pattern($var, '/^Eight$/D');
	}

	public function array_key_test() {
		$array = array('a' => 'A', 'b' => 'B');
		$this->assert_array_key('a', $array);
	}

	public function in_array_test() {
		$array = array('X', 'Y', 'Z');
		$this->assert_in_array('X', $array);
	}

	public function debug_example_test() {
		foreach(array(1, 5, 6, 12, 65, 128, 9562) as $var) {
			// By supplying $var in the debug parameter,
			// we can see on which number this test fails.
			$this->assert_YES($var < 100, $var);
		}
	}

	public function error_test() {
		throw new Exception;
	}

}
