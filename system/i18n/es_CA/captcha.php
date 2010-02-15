<?php

$lang = array
(
	'file_not_found'	=> 'L\'arxiu especificat, %s, no ha estat trobat. Per favor, verifica que el fitxer existeix utilitzant file_exists() abans d\'intentar utilitzar-lo.',
	'requires_GD2'		=> 'La llibreria Captcha requereix GD2 amb suporti FreeType. Llegeixi el següent http://php.net/gd_info per a ampliar la informació.',
	
	// Words of varying length for the Captcha_Word_Driver to pick from
	// Note: use only alphanumeric characters
	'words' => array
	(
		'cd', 'tv', 'it', 'to', 'be', 'or',
		'sun', 'car', 'dog', 'bed', 'kid', 'egg',
		'bike', 'tree', 'bath', 'roof', 'road', 'hair',
		'hello', 'world', 'earth', 'beard', 'chess', 'water',
		'barber', 'bakery', 'banana', 'market', 'purple', 'writer',
		'america', 'release', 'playing', 'working', 'foreign', 'general',
		'aircraft', 'computer', 'laughter', 'alphabet', 'kangaroo', 'spelling',
		'architect', 'president', 'cockroach', 'encounter', 'terrorism', 'cylinders',
	),

	// Riddles for the Captcha_Riddle_Driver to pick from
	// Note: use only alphanumeric characters
	'riddles' => array
	(
		array('¿Odies el spam? (si o no)', 'si'),
		array('¿Ets un robot? (si o no)', 'no'),
		array('El foc és... (calent o fred)', 'calent'),
		array('L\'estació que ve després de la tardor és...', 'hivern'),
		array('¿Quin dia de la setmana és avui?', strftime('%A')),
		array('¿En quin mes de l\'any estem?', strftime('%B')),
	),
);
