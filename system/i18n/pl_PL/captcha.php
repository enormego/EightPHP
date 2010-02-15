<?php

$lang = array
(
	'file_not_found' => 'Podany plik %s, nie został znaleziony. Sprawdź czy plik istnieje używając file_exists() przed wywołaniem.',
	'requires_GD2'   => 'Biblioteka Captcha wymaga biblioteki graficznej GD2 z obsługą FreeType. Więcej informacji pod: http://php.net/gd_info.',

	// Słowa o różnej długości do wybierania dla Captcha_Word_Driver
	// Uwaga: użyć można wyłącznie znaków typu alphanumeric
	'words' => array
	(
		'do', 'ja', 'my', 'na', 'on', 'ty', 'za',
		'kot', 'lub', 'osa', 'koń', 'bez', 'bęc', 'ząb',
		'miód', 'pies', 'jajo', 'woda', 'mata', 'albo', 'kruk',
		'piwko', 'norka', 'stopa', 'akord', 'bubel', 'wrota', 'miecz',
		'trąbka', 'szpada', 'drewno', 'brokuł', 'trawka', 'dźwięk', 'kurcze',
		'paproch', 'walizka', 'smoczek', 'drabina', 'lunetka', 'zeszyty', 'torebka',
		'ziemniak', 'kalarepa', 'prosiaki', 'bukowina', 'samorząd', 'czerwony', 'kapturek',
		'wiewiórka', 'bohaterka', 'nienawiść', 'niebieski', 'cytrynowy', 'irytujący', 'waltornia'
	),

	// Frazy do wybierania dla Captcha_Riddle_Driver
	// Uwaga: użyć można wyłącznie znaków typu alphanumeric
	'riddles' => array
	(
		array('Czy lubisz spam? (tak lub nie)', 'nie'),
		array('Miś Uszatek ma klapnięte...? (łóżko czy uszko)', 'uszko'),
		array('Przed latem mamy...?', 'wiosnę'),
		array('Kopernik "ruszył"...?', 'ziemię'),
		array('Dwa dodać dwa to:', 'cztery'),
		array('Który obecnie mamy rok?', strftime('%Y')),
	),
);
