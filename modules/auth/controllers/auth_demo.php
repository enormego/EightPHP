<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Auth module demo controller. This controller should NOT be used in production.
 * It is for demonstration purposes only!
 *
 * $Id: auth_demo.php 3267 2008-08-06 03:44:02Z Shadowhand $
 *
 * @package    Auth
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Auth_Demo_Controller extends Template_Controller {

	// Do not allow to run in production
	const ALLOW_PRODUCTION = FALSE;

	// Use the default Kohana template
	public $template = 'kohana/template';

	public function index()
	{
		// Display the install page
		$this->template->title   = 'Auth Module Installation';
		$this->template->content = View::factory('auth/install');
	}

	public function create()
	{
		$this->template->title = 'Create User';

		$form = new Forge;
		$form->input('email')->label(TRUE)->rules('required|length[4,32]|valid_email');
		$form->input('username')->label(TRUE)->rules('required|length[4,32]');
		$form->password('password')->label(TRUE)->rules('required|length[5,40]');
		$form->submit('Create New User');

		if ($form->validate())
		{
			// Create new user
			$user = ORM::factory('user');

			if ( ! $user->username_exists($form->username->value))
			{
				foreach ($form->as_array() as $key => $val)
				{
					// Set user data
					$user->$key = $val;
				}

				if ($user->save() AND $user->add(ORM::factory('role', 'login')))
				{
					Auth::instance()->login($user, $form->password->value);

					// Redirect to the login page
					url::redirect('auth_demo/login');
				}
			}
		}

		// Display the form
		$this->template->content = $form->render();
	}

	public function login()
	{
		if (Auth::instance()->logged_in())
		{
			$this->template->title = 'User Logout';

			$form = new Forge('auth_demo/logout');
			$form->submit('Logout Now');
		}
		else
		{
			$this->template->title = 'User Login';

			$form = new Forge;
			$form->input('username')->label(TRUE)->rules('required|length[4,32]');
			$form->password('password')->label(TRUE)->rules('required|length[5,40]');
			$form->submit('Attempt Login');

			if ($form->validate())
			{
				// Load the user
				$user = ORM::factory('user', $form->username->value);

				if (Auth::instance()->login($user, $form->password->value))
				{
					// Login successful, redirect
					url::redirect('auth_demo/login');
				}
				else
				{
					$form->password->add_error('login_failed', 'Invalid username or password.');
				}
			}
		}

		// Display the form
		$this->template->content = $form->render();
	}

	public function logout()
	{
		// Force a complete logout
		Auth::instance()->logout(TRUE);

		// Redirect back to the login page
		url::redirect('auth_demo/login');
	}

} // End Auth Controller