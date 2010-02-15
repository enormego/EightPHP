<?php

$lang = substr(Kohana::config('locale.language'), 0, 2);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//<?php echo strtoupper($lang) ?>" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $lang ?>" lang="<?php echo $lang ?>">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

<title><?php echo Kohana::lang('user_guide.title') ?></title>

<?php

echo html::stylesheet(array
(
	'user_guide/css/layout',
	'user_guide/css/prettify'
))

?>

<?php

echo html::script(array
(
	'user_guide/js/jquery',
	'user_guide/js/plugins',
	'user_guide/js/prettify',
	'user_guide/js/effects'
))

?>

</head>
<body>
<div id="container">

<!-- @start Menu -->
<div id="menu">
<ul>
<?php

foreach (Kohana::lang('user_guide_menu') as $cat => $menu):

	$active = (strtolower($cat) == $category) ? ' active' : '';

?>
<li class="first<?php echo $active ?>"><span><?php echo $cat ?></span><ul>
<?php

	foreach ($menu as $sec):

		$active = (strtolower($sec) == $section) ? 'lite' : '';

?>
<li class="<?php echo $active ?>"><?php echo html::anchor(strtolower('user_guide/'.$language.'/'.$cat.'/'.$sec), $sec) ?></li>
<?php

	endforeach;

?>
</ul></li>
<?php

endforeach;

?>
</ul>
</div>
<!-- @end Menu -->

<!-- @start Body -->
<div id="body">
<?php echo $content ?>
</div>
<!-- @end Body -->

<!-- @start Footer -->
<div id="footer"><p id="copyright"><?php echo sprintf(Kohana::lang('user_guide.copyright'), date('Y')) ?></p></div>
<!-- @end Footer -->

</div>
</body>
</html>