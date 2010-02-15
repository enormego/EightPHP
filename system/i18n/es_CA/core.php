<?php

$lang = array
(
	'there_can_be_only_one' => 'Solament pot haver una instància de Eight per cada pàgina.',
	'uncaught_exception'	=> '%s no capturada: %s en l\'arxiu %s, línia %s',
	'invalid_method'		=> 'Mètode invàlid %s utilitzat en %s.',
	'invalid_property'		=> 'La propietat %s no existeix en la classe %s.',
	'log_dir_unwritable'	=> 'La teva configuració de el &8220;log.directory&8221; no apunta a un directori amb permís d\'escriptura.',
	'resource_not_found'	=> 'El fitxer de %s amb nom %s, no va poder ser trobat.',
	'invalid_filetype'		=> 'El tipus de fitxer sol·licitat, .%s, no aquesta permès en la configuració de les teves vistes.',
	'view_set_filename'		=> 'Has de definir el nom de la vista abans d\'utilitzar al mètode render',
	'no_default_route'		=> 'Per favor, especifica la ruta en config/routes.php.',
	'no_controller'			=> 'Eight no va poder determinar un controlador per a processar: %s',
	'page_not_found'		=> 'La pàgina que sol·licités, %s, no es troba.',
	'stats_footer'			=> 'Carregat en {execution_time} segons, usant {memory_usage} de memòria. Generat amb Eight v{eight_version}.',
	'error_file_line'		=> '<tt>%s <strong>[%s]:</strong></tt>',
	'stack_trace'			=> 'Stack Trace',
	'generic_error'			=> 'Impossible completar la sol·licitud',
	'errors_disabled'		=> 'Pots tornar a la <a href="%s">pàgina d\'inici</a> o <a href="%s">tornar a intentar-lo</a>.', 

	// Drivers
	'driver_implements'		=> 'El driver %s per a la llibreria %s ha d\'implementar el interface %s',
	'driver_not_found'		=> 'No s\'ha trobat el driver %s per a la llibreria %s',

	// Resource names
	'config'				=> 'fitxer de configuració',
	'controller'			=> 'controlador',
	'helper'				=> 'helper',
	'library'				=> 'llibreria ',
	'driver'				=> 'driver',
	'model'					=> 'model',
	'view'					=> 'vista',
);
