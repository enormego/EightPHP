<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<style type="text/css">
<?php include Eight::find_file('views', 'eight/errors', FALSE, 'css') ?>
</style>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title><?php echo $type?> &mdash; <?php echo $code?></title>
<base href="http://php.net/" />
</head>
<body>
<div id="framework_error" style="width:900px;margin:20px auto;">
<?
// Unique error identifier
$error_id = uniqid('error');
?>
<style type="text/css">
<?php include Eight::find_file('views', 'eight/errors', FALSE, 'css') ?>
</style>
<script type="text/javascript">
	document.write('<style type="text/css"> .collapsed { display: none; } </style>');
	function eight_toggle(elem)
	{
		elem = document.getElementById(elem);
		
		if (elem.style && elem.style['display']) 
			// Only works with the "style" attr
			var disp = elem.style['display'];
		else 
			if (elem.currentStyle) 
				// For MSIE, naturally
				var disp = elem.currentStyle['display'];
			else 
				if (window.getComputedStyle) 
					// For most other browsers
					var disp = document.defaultView.getComputedStyle(elem, null).getPropertyValue('display');
		
		// Toggle the state of the "display" style
		elem.style.display = disp == 'block' ? 'none' : 'block';
		return false;
	}
</script>
<div id="eight_error">
	<h1>
		<span class="type">
<?php echo $type?> &mdash; <?php echo $code?>:
		</span>
		<span class="message"><?php echo $message?></span>
	</h1>
	<div id="<?php echo $error_id ?>" class="content">
		<p>
			<span class="file key_file code">
<?php echo Eight_Exception::debug_path($file)?>:<?php echo $line?>
			</span>
		</p>

<?php if (Eight_Exception::$source_output AND $source_code = Eight_Exception::debug_source($file, $line)) : ?>
		<pre class="source" style="margin-top:5px"><code><?php foreach ($source_code as $num => $row) : ?><span class="line <?php if ($num == $line) echo 'highlight' ?>"><span class="number"><?php echo $num ?></span><?php echo htmlspecialchars($row, ENT_NOQUOTES, Eight::CHARSET) ?></span><?php endforeach ?></code></pre>
<?php endif ?>

<?php if (Eight_Exception::$trace_output) : ?>
		<ol class="trace">
			<?php foreach (Eight_Exception::trace($trace) as $i=>$step): ?>
			<li>
				<p>
					<span class="code">
						<span class="file">
							<?php if ($step['file']): $source_id = $error_id.'source'.$i; ?>
							<?php if (Eight_Exception::$source_output AND $step['source']) : ?>
							<a href="#<?php echo $source_id ?>" onclick="return eight_toggle('<?php echo $source_id ?>')"><?php echo Eight_Exception::debug_path($step['file'])?>:<?php echo $step['line']?></a>
							<?php else : ?>
							<span class="file"><?php echo Eight_Exception::debug_path($step['file'])?>:<?php echo $step['line']?></span>
							<?php endif ?>
							<?php else : ?>
							{<?php echo __('PHP internal call')?>}
							<?php endif?>
						</span>
						&ndash;
						<?php echo $step['function']?>(<?php if ($step['args']): $args_id = $error_id.'args'.$i; ?><a href="#<?php echo $args_id ?>" onclick="return eight_toggle('<?php echo $args_id ?>')"><?php echo __('args')?></a><?php endif?>)
					</span>
				</p>
				<?php if (isset($args_id)): ?>
				<div id="<?php echo $args_id ?>" class="collapsed">
					<table cellspacing="0">
						<?php foreach ($step['args'] as $name=>$arg): ?>
						<tr>
							<td>
								<code>
									<?php echo $name?>
								</code>
							</td>
							<td>
								<pre><?php echo Eight_Exception::dump($arg) ?></pre>
							</td>
						</tr>
						<?php endforeach?>
					</table>
				</div>
				<?php endif?>
				<?php if (Eight_Exception::$source_output AND $step['source'] AND isset($source_id)): ?>
				<pre id="<?php echo $source_id ?>" class="source collapsed"><code><?php foreach ($step['source'] as $num => $row) : ?><span class="line <?php if ($num == $step['line']) echo 'highlight' ?>"><span class="number"><?php echo $num ?></span><?php echo htmlspecialchars($row, ENT_NOQUOTES, Eight::CHARSET) ?></span><?php endforeach ?></code></pre>
				<?php endif?>
			</li>
			<?php unset($args_id, $source_id); ?>
			<?php endforeach?>
		</ol>
<?php endif ?>

	</div>
	<h2><a href="#<?php echo $env_id = $error_id.'environment' ?>" onclick="return eight_toggle('<?php echo $env_id ?>')"><?php echo __('Environment')?></a></h2>
	<div id="<?php echo $env_id ?>" class="content collapsed">
		<?php $included = get_included_files()?>
		<h3><a href="#<?php echo $env_id = $error_id.'environment_included' ?>" onclick="return eight_toggle('<?php echo $env_id ?>')"><?php echo __('Included files')?></a>(<?php echo count($included)?>)</h3>
		<div id="<?php echo $env_id ?>" class="collapsed">
			<table cellspacing="0">
				<?php foreach ($included as $file): ?>
				<tr>
					<td>
						<code>
<?php echo Eight_Exception::debug_path($file)?>
						</code>
					</td>
				</tr>
				<?php endforeach?>
			</table>
		</div>
		<?php $included = get_loaded_extensions()?>
		<h3><a href="#<?php echo $env_id = $error_id.'environment_loaded' ?>" onclick="return eight_toggle('<?php echo $env_id ?>')"><?php echo __('Loaded extensions')?></a>(<?php echo count($included)?>)</h3>
		<div id="<?php echo $env_id ?>" class="collapsed">
			<table cellspacing="0">
				<?php foreach ($included as $file): ?>
				<tr>
					<td>
						<code>
<?php echo Eight_Exception::debug_path($file)?>
						</code>
					</td>
				</tr>
				<?php endforeach?>
			</table>
		</div>
		<?php foreach (array('_SESSION', '_GET', '_POST', '_FILES', '_COOKIE', '_SERVER') as $var): ?>
		<?php if ( empty($GLOBALS[$var]) OR ! is_array($GLOBALS[$var])) continue ?>
		<h3><a href="#<?php echo $env_id = $error_id.'environment'.strtolower($var) ?>" onclick="return eight_toggle('<?php echo $env_id ?>')">$<?php echo $var?></a></h3>
		<div id="<?php echo $env_id ?>" class="collapsed">
			<table cellspacing="0">
				<?php foreach ($GLOBALS[$var] as $key=>$value): ?>
				<tr>
					<td>
						<code>
<?php echo $key?>
						</code>
					</td>
					<td>
						<pre><?php echo Eight_Exception::dump($value) ?></pre>
					</td>
				</tr>
				<?php endforeach?>
			</table>
		</div>
		<?php endforeach?>
	</div>
</div>
</div>
</body>
</html>