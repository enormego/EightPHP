<table class="eight-profiler-table" style="display:<?=$hidden ? 'none' : 'block'?>" id="<?=$id?>">
<?php
foreach($rows as $row):

$class = empty($row['class']) ? '' : ' class="'.$row['class'].'"';
$style = empty($row['style']) ? '' : ' style="'.$row['style'].'"';
?>
	<tr<?php echo $class; echo $style; ?> onmouseover="this.className='kp-hover'" onmouseout="this.className=''">
		<?php
		foreach($columns as $index => $column) {
			$class = empty($column['class']) ? '' : ' class="'.$column['class'].'"';
			$style = empty($column['style']) ? '' : ' style="'.$column['style'].'"';
			$value = $row['data'][$index];
			$value = (is_array($value) OR is_object($value)) ? '<pre>'.html::specialchars(print_r($value, YES)).'</pre>' : html::specialchars($value);
			echo '<td', $style, $class, '>', nl2br(str::hard_wrap($value)), '</td>';
		}
		?>
	</tr>
<?php

endforeach;
?>
</table>