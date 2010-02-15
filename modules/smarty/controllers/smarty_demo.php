<?php defined('SYSPATH') or die('No direct script access.');

class Smarty_Demo_Controller extends Controller
{
	// Do not allow to run in production
	const ALLOW_PRODUCTION = FALSE;

	public function index()
	{
		$welcome = new View('demo');
		$welcome->message = "Welcome to the Kohana!";

		$welcome->render(TRUE);
	}
}
