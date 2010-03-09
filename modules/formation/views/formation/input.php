<div>
		<?=$input->label()?>
		
		<?foreach ($input->error_messages() as $error):?>
			<p class="error"><?=$error?></p>
		<?endforeach?>
		
		<?if($input instanceof Form_Textarea && $input->__get('wysiwyg') == TRUE): ?>
			<?$usewysiwyg = true?>
			<?$input->class_value('mceAdvanced')?>
		<?endif?>
		
		<?=$input->render()?>
		
		<?if ($message = $input->message()):?>
			<p class="message"><?=$message ?></p>
		<?endif?>
</div>