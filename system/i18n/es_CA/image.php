<?php

$lang = array
(
	'getimagesize_missing'	  => 'a llibreria &8220;Image&8221; requereix la funció PHP getimagesize, que no sembla estar disponible en la teva instal·lació.',
	'unsupported_method'	  => 'El driver que has triat en la configuració no suporta el tipus de transformació %s.',
	'file_not_found'		  => 'La imatge especificada, %s no s\'ha trobat. Per favor, verifica que existeix utilitzant file_exists() abans de manipular-la.',
	'type_not_allowed'		  => 'El tipus d\'imatge especificat, %s, no és un tipus d\'imatge permès.', 
	'invalid_width'			  => 'L\'ample que has especificat, %s, no és valgut.',
	'invalid_height'		  => 'L\'alt que has especificat, %s, no és valgut.',
	'invalid_dimensions'	  => 'Les dimensions que has especificat per a %s no són valgudes.',
	'invalid_master'		  => 'The master dim specified is not valid.',
	'invalid_flip'			  => 'L\'adreça de rotació especificada no és valida.',
	'directory_unwritable'	  => 'El directori especificat, %s, no té permisos d\'escriptura.',

	// ImageMagick specific messages
	'imagemagick' => array
	(
		'not_found' => 'El directori de ImageMagick especificat, no conté el programa requerit, %s.', 
	),

	// GD specific messages
	'gd' => array
	(
		'requires_v2' => 'La llibreria &8220;Image&8221; requereix GD2. Per favor, llegix http://php.net/gd_info para més informació.',
	),
);
