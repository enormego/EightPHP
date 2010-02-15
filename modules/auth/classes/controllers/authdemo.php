<?php
/**
 * Auth module demo controller. This controller should NOT be used in production.
 * It is for demonstration purposes only!
 *
 * @version		$Id: authdemo.php 244 2010-02-11 17:14:39Z shaun $
 *
 * @package		Modules
 * @subpackage	Authentication
 * @author		enormego
 * @copyright	(c) 2009-2010 enormego
 * @license		http://license.eightphp.com
 */
class Controller_AuthDemo extends Controller {

	// Do not allow to run in production
	const ALLOW_PRODUCTION = FALSE;

	public function __construct() {
		parent::__construct();

		// Load auth library
		$this->auth = new Auth;
	}

	public function index() {
		// Display the install page
		echo new View('auth/install');
	}

	public function create() {
		$form = new Formation(NULL, 'Create User');

		$form->input('email')->label(true)->rules('required|length[4,32]');
		$form->password('password')->label(true)->rules('required|length[5,40]');
		$form->submit('Create New User');

		if($form->validate()) {
			// Create new user
			$user = new Model_User;

			if(!$user->user_exists($this->input->post('email'))) {

				$user->email = request::$input['email'];
				$user->password = request::$input['password'];

				if($user->save()) {
					// Redirect to the login page
					url::redirect('auth_demo/login');
				}
			}
		}

		// Display the form
		echo $form->render();
	}

	public function login() {
		if ($this->auth->logged_in()) {
			$form = new Formation('auth_demo/logout', 'Log Out');

			$form->submit('Logout Now');
		} else {
			$form = new Formation(NULL, 'User Login');

			$form->input('email')->label(true)->rules('required|length[4,32]');
			$form->password('password')->label(true)->rules('required|length[5,40]');
			$form->submit('Attempt Login');

			if ($form->validate()) {
				// Load the user
				$user = new Model_User($form->email->value);
				
				// Attempt a login
				if ($this->auth->login($user, $form->password->value, true)) {
					echo '<h4>Login Success!</h4>';
					$form = new Formation('auth_demo/logout', 'Log Out');
					$form->submit('Logout Now');
				} else {
					$form->password->add_error('login_failed', 'Invalid username or password.');
				}
			}
		}

		// Display the form
		echo $form->render();
	}

	public function logout() {
		// Load auth and log out
		$this->auth->logout(true);

		// Redirect back to the login page
		url::redirect('auth_demo/login');
	}

} // End Auth Controller