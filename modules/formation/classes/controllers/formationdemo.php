<?php
/**
 * Formation module demo controller. This controller should NOT be used in production.
 * It is for demonstration purposes only!
 *
 * @package		Modules
 * @subpackage	Formation
 * @author		EightPHP Development Team
 * @copyright	(c) 2009-2010 EightPHP
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
		
		$gender = $form->group('gender_group')->label('Male or Female');
		$gender->radio('gender')->label('Male')->value('m');
		$gender->radio('gender')->label('Female')->value('f');
		
		$favorites = $form->group('favorites_group')->label('Your favorites!');
		
		$favfoods = $favorites->group('favfoods')->label('Favorite Food');
		$favfoods->radio('food')->label('Pizza')->value('p');
		$favfoods->radio('food')->label('Cheeseburger')->value('c');
		
		$otherfavs = $form->group('testingGroupLayouts')->layout('columns')->label('Lots of Other Favorites!');
		
			$favpeople = $otherfavs->group('favpeople')->label('Favorite People');
			$favpeople->radio('people')->label('Tom')->value('a');
			$favpeople->radio('people')->label('Rob')->value('j');
			$favpeople->radio('people')->label('Other')->value('s');
		
			$favdrink = $otherfavs->group('favdrinks')->label('Favorite drink');
			$favdrink->radio('drink')->label('Water')->value('w');
			$favdrink->radio('drink')->label('Soda')->value('s');
			$favdrink->radio('drink')->label('Juice')->value('s');
		
			$favthings = $otherfavs->group('favthings')->label('Favorite Things');
			$favthings->radio('things')->label('YoYo')->value('w');
			$favthings->radio('things')->label('Spoon')->value('s');
			$favthings->radio('things')->label('Speaker')->value('s');
		
			$otherfavs->input('anotherfield')->label('Another Text Field');
		
		$form->checklist('dinners')->label('Favorite Dinner')->options($foods)->rules('required');
		$form->dropdown('state')->label('Home State')->options(localeUS::states())->rules('required');
		$form->dateselect('birthday')->label(YES)->minutes(15)->years(1950, date('Y'));
		
		$form->submit('Save');

		if ($form->validate()) {
			echo Eight::debug($form->as_array());
		}

		echo $form->render();
		exit;

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
