<?php defined('SYSPATH') or die('No direct script access.');

class Shoutbox_Controller extends Controller {

	// Do not allow to run in production
	const ALLOW_PRODUCTION = FALSE;

	public function __construct()
	{
		parent::__construct();

		$this->db   = new Database('mysql://root:r00tdb@localhost/shoutbox');
		$this->auth = new Auth;
	}

	public function index()
	{
		$messages = ORM::factory('message')->orderby('posted', 'desc')->find_all();

		View::factory('shoutbox')
			->set('messages', $messages)
			->render(TRUE);
	}

	public function signup()
	{
		$form = new Forge(NULL, 'User Login');

		$form->input('email')->label(TRUE)->rules('required|length[4,32]');
		$form->input('username')->label(TRUE)->rules('required|length[4,32]');
		$form->password('password')->label(TRUE)->rules('required|length[5,40]');
		$form->submit('Create Account');

		if ($form->validate())
		{
			$user = ORM::factory('user');

			foreach ($form->as_array() as $key => $value)
			{
				$user->$key = $value;
			}

			if ($user->save())
			{
				$user->add_role('login');
				$this->db->clear_cache();

				$user = ORM::factory('user', $user->id);

				$this->auth->login($user, $form->password->value);
			}

			url::redirect('shoutbox');
		}

		echo $form->render();
	}

	public function login()
	{
		$form = new Forge(NULL, 'User Login');

		$form->input('username')->label(TRUE)->rules('required|length[4,32]');
		$form->password('password')->label(TRUE)->rules('required|length[5,40]');
		$form->submit('Attempt Login');

		if ($form->validate())
		{
			$user = ORM::factory('user', $form->username->value);

			if ($this->auth->login($user, $form->password->value))
			{
				url::redirect('shoutbox/post');
			}
			else
			{
				$form->password->add_error('login_failed', 'Invalid username or password.');
			}
		}

		echo $form->render();
	}

	public function post()
	{
		$form = new Forge(NULL, 'Post New Message');

		$form->input('message')->size('60')->rules('required|length[4,255]');
		$form->submit('Post');

		if ($form->validate())
		{
			$message = ORM::factory('message');
			$message->user_id = $_SESSION['user_id'];
			$message->text = $form->message->value;
			$message->save();

			url::redirect('shoutbox');
		}

		echo $form->render();
	}

	public function logout()
	{
		Session::instance()->destroy();
		url::redirect('shoutbox');
	}

} // End Shoutbox Controller