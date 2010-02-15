<?php
/**
 * Formation module demo controller. This controller should NOT be used in production.
 * It is for demonstration purposes only!
 *
 * @version		$Id: formationdemo.php 244 2010-02-11 17:14:39Z shaun $
 *
 * @package		Modules
 * @subpackage	Formation
 * @author		enormego
 * @copyright	(c) 2009-2010 enormego
 * @license		http://license.eightphp.com
 */

class Controller_Formationdemo extends Controller {

	// Do not allow to run in production
	const ALLOW_PRODUCTION = NO;

	public function index() {
		$profiler = new Profiler;

		$foods = array(
			'tacos' => array('tacos', NO),
			'burgers' => array('burgers', NO),
			'spaghetti' => array('spaghetti (checked)', YES),
			'cookies' => array('cookies (checked)', YES),
		);

		$form = new Formation(nil, 'New User');

		// Create each input, following this format:
		//
		//   type($name)->attr(..)->attr(..);
		//
		$form->hidden('hideme')->value('hiddenz!');
		$form->input('email')->label(YES)->rules('required|valid_email');
		$form->input('username')->label(YES)->rules('required|length[5,32]');
		$form->password('password')->label(YES)->rules('required|length[5,32]');
		$form->password('confirm')->label(YES)->matches($form->password);
		$form->checkbox('remember')->label('Remember Me');
		$form->checklist('foods')->label('Favorite Foods')->options($foods)->rules('required');
		$form->dropdown('state')->label('Home State')->options(localeUS::states())->rules('required');
		$form->dateselect('birthday')->label(YES)->minutes(15)->years(1950, date('Y'));
		$form->submit('Save');

		if ($form->validate()) {
			echo Eight::debug($form->as_array());
		}

		echo $form->render();

		// Using a custom template:
		// echo $form->render('custom_view', YES);
		// Inside the view access the inputs using $input_id->render(), ->label() etc
		//
		// To get the errors use $input_id_errors.
		// Set the error format with $form->error_format('<div>{message}</div>');
		// Defaults to <p class="error">{message}</p>
		//
		// Examples:
		//   echo $username->render(); echo $password_errors;
	}

	public function upload() {
		$profiler = new Profiler;

		$form = new Formation;
		$form->input('hello')->label(YES);
		$form->upload('file', YES)->label(YES)->rules('required|size[200KB]|allow[jpg,png,gif]');
		$form->submit('Upload');

		if ($form->validate()) {
			echo Eight::debug($form->as_array());
		}

		echo $form->render();
	}

} // End FormationDemo Controller
