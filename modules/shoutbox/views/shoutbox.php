<h2>Messages:</h2>
<ol>
<?php

foreach ($messages as $message): 

	$timespan = date::timespan($message->posted, time(), 'minutes');

?>
<li><?php echo $message->text ?> <small style="display:block"><?php echo $timespan ?> <?php echo inflector::plural('minute', $timespan) ?> ago by <?php echo $message->user->username ?></small></li>
<?php endforeach ?>
</ol>

<?php

if (empty($_SESSION['user_id']))
{
	$links = array
	(
		'login' => 'Login',
		'signup' => 'Signup'
	);
}
else
{
	$links = array
	(
		'post' => 'New Message',
		'logout' => 'Logout'
	);
}

?>
<ul>
<?php foreach ($links as $link => $title): ?>
<li><?php echo html::anchor('shoutbox/'.$link, $title) ?></li>
<?php endforeach ?>
</ul>
