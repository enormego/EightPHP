<?php echo $open; ?>
<table class="<?php echo $class ?>">
<?php if ($title != ''): ?>
<caption><?php echo $title ?></caption>
<?php endif ?>
<?php
foreach($inputs as $input):

$sub_inputs = array();
if ($input->type == 'group'):
	$sub_inputs = $input->inputs;

?>
<tr>
<th colspan="2"><?php echo $input->label() ?></th>
</tr>
<?php

	if ($message = $input->message()):

?>
<tr>
<td colspan="2"><p class="group_message"><?php echo $message ?></p></td>
</tr>
<?php

	endif;

else:
	$sub_inputs = array($input);	
endif;

foreach($sub_inputs as $input):

?>
<tr>
<th><?php echo $input->label() ?></th>
<td class="input<?=((get_class($input) == 'Form_Submit') ? ' submit' : '')?>">
<?foreach ($input->error_messages() as $error):?>
	<p class="error"><?php echo $error ?></p>
<?endforeach;?>
<? if(get_class($input) == 'Form_Textarea' && $input->__get('wysiwyg') == true): ?>
	<? $usewysiwyg = true ?>
	<? $input->class_value('mceAdvanced') ?>
<? endif ?>
<?=$input->render()?>
</td>
<?if ($message = $input->message()):?>
	<td class="message">
		<p><?=$message ?></p>
	</td>
<?endif?>
</tr>
<?php

endforeach;

endforeach;
?>
</table>
<?php echo $close ?>