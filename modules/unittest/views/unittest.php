<style type="text/css">
#eight-unit-test
{
	font-family: Monaco, 'Courier New';
	background-color: #F8FFF8;
	margin-top: 20px;
	clear: both;
	padding: 10px 10px 0;
	border: 1px solid #E5EFF8;
	text-align: left;
}
#eight-unit-test pre
{
	margin: 0;
	font: inherit;
}
#eight-unit-test table
{
	font-size: 1.0em;
	color: #4D6171;
	width: 100%;
	border-collapse: collapse;
	border-top: 1px solid #E5EFF8;
	border-right: 1px solid #E5EFF8;
	border-left: 1px solid #E5EFF8;
	margin-bottom: 10px;
}
#eight-unit-test th
{
	text-align: left;
	border-bottom: 1px solid #E5EFF8;
	background-color: #263038;
	padding: 3px;
	color: #FFF;
}
#eight-unit-test td
{
	background-color: #FFF;
	border-bottom: 1px solid #E5EFF8;
	padding: 3px;
}
#eight-unit-test .k-stats
{
	font-weight: normal;
	color: #83919C;
	text-align: right;
}
#eight-unit-test .k-debug
{
	padding: 3px;
	background-color: #FFF0F0;
	border: 1px solid #FFD0D0;
	border-right-color: #FFFBFB;
	border-bottom-color: #FFFBFB;
	color: #83919C;
}
#eight-unit-test .k-altrow td
{
	background-color: #F7FBFF;
}
#eight-unit-test .k-name
{
	width: 25%;
	border-right: 1px solid #E5EFF8;
}
#eight-unit-test .k-passed
{
	background-color: #E0FFE0;
}
#eight-unit-test .k-altrow .k-passed
{
	background-color: #D0FFD0;
}
#eight-unit-test .k-failed
{
	background-color: #FFE0E0;
}
#eight-unit-test .k-altrow .k-failed
{
	background-color: #FFD0D0;
}
#eight-unit-test .k-error
{
	background-color: #FFFFE0;
}
#eight-unit-test .k-altrow .k-error
{
	background-color: #FFFFD1;
}
</style>

<div id="eight-unit-test">

<?php

foreach($results as $class => $methods):
str::alternate();

?>

	<table>
		<tr>
			<th><?php echo $class ?></th>
			<th class="k-stats">
				<?php printf('%s: %.2f%%', Eight::lang('unittest.score'), $stats[$class]['score']) ?> |
				<?php echo Eight::lang('unittest.total'),  ': ', $stats[$class]['total'] ?>,
				<?php echo Eight::lang('unittest.passed'), ': ', $stats[$class]['passed'] ?>,
				<?php echo Eight::lang('unittest.failed'), ': ', $stats[$class]['failed'] ?>,
				<?php echo Eight::lang('unittest.errors'), ': ', $stats[$class]['errors'] ?>
			</th>
		</tr>

		<?php if(empty($methods)): ?>

			<tr>
				<td colspan="2"><?php echo Eight::lang('unittest.no_tests_found') ?></td>
			</tr>

		<?php else:

			foreach($methods as $method => $result):

				// Hide passed tests from report
				if($result === TRUE and $hide_passed === TRUE)
					continue;

				?>

				<tr class="<?php echo str::alternate('', 'k-altrow') ?>">
					<td class="k-name"><?php echo $method ?></td>

					<?php if($result === TRUE): ?>

						<td class="k-passed"><strong><?php echo Eight::lang('unittest.passed') ?></strong></td>

					<?php elseif($result instanceof Eight_Unit_Test_Exception): ?>

						<td class="k-failed">
							<strong><?php echo Eight::lang('unittest.failed') ?></strong>
							<pre><?php echo html::specialchars($result->getMessage()) ?></pre>
							<?php echo html::specialchars($result->getFile()) ?> (<?php echo Eight::lang('unittest.line') ?>&nbsp;<?php echo $result->getLine() ?>)

							<?php if($result->getDebug() !== nil): ?>
								<pre class="k-debug" title="Debug info"><?php echo '(', gettype($result->getDebug()), ') ', html::specialchars(var_export($result->getDebug(), TRUE)) ?></pre>
							<?php endif ?>

						</td>

					<?php elseif($result instanceof Exception): ?>

						<td class="k-error">
							<strong><?php echo Eight::lang('unittest.error') ?></strong>
							<pre><?php echo html::specialchars($result->getMessage()) ?></pre>
							<?php echo html::specialchars($result->getFile()) ?> (<?php echo Eight::lang('unittest.line') ?>&nbsp;<?php echo $result->getLine() ?>)
						</td>

					<?php endif ?>

				</tr>

			<?php endforeach ?>

		<?php endif ?>

	</table>

<?php endforeach ?>

</div>
