<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>

	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<title><?php echo html::specialchars($title) ?></title>

	<style type="text/css">
	html { background: #eeeeee url(<?php echo url::base(NO) ?>eight.png) 50% 0 no-repeat; }
	body { width: 52em; margin: 200px auto 2em; font-size: 76%; font-family: Arial, sans-serif; color: #111111; line-height: 1.5; text-align: center; }
	h1 { font-size: 3em; font-weight: normal; text-transform: uppercase; color: #2b1f20; }
	a { color: inherit; }
	p { padding: 0 0 1em; margin: 0; }
	code { font-size: 1.3em; }
	ul { list-style: none; padding: 2em 0; margin: 0; }
		ul li { display: inline; padding-right: 1em; text-transform: uppercase; }
			ul li a { padding: 0.5em 1em; background: white; border: 1px solid #dddddd; color: #111111; text-decoration: none; }
			ul li a:hover { background: #ffdfdf; border-color: #dfa4a5; }
	dl { text-align: left; background: #eee; padding: 0 1em 1em; margin: 0 0 1em; }
		dt { font-weight: bold; font-size: 1em; padding-top: 1em; }
	form { margin: 0 0 1em; background: #eee; text-align: left; }
		form textarea,
		form input,
		form select { width: 100%; }
		form fieldset { display: block; border: 0; padding: 1em; }
		form fieldset.submit { text-align: right; }
			form fieldset.submit a { float: left; }
		form label { display: block; margin: 0 0 0.6em; }
			form label span { display: block; font-size: 80%; padding: 0.1em 0; }
		form ul.errors { list-style: outside square; padding: 1em; }
		form ul.errors li { display: list-item; margin-left: 1em; text-transform: none; color: #800; }
	.intro { padding: 0 1em 1em; margin-bottom: 1em; border-bottom: dotted 1px #569f09; font-size: 1.2em; }
	.box { padding: 2em; background: white; border: 1px solid #dddddd; }
	.copyright { font-size: 0.9em; text-transform: uppercase; color: #2b1f20; }
	</style>

</head>
<body>

	<h1><?php echo html::specialchars($title) ?></h1>
	<?php echo $contents ?>

	<p class="copyright">
		Rendered in {execution_time} seconds, using {memory_usage} of memory<br />
		Copyright &copy;2009-<?=date("Y");?> <a href="http://eightphp.com">EightPHP</a>.
	</p>

</body>
</html>