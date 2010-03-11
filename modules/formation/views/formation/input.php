<div class="element">
	<?foreach ($input->error_messages() as $error):?>
		<p class="error"><?=$error?></p>
	<?endforeach?>

	<div class="input">
			<?=$input->label()?>

			<?if($input instanceof Form_Textarea && $input->__get('wysiwyg') == TRUE): ?>
				<?$usewysiwyg = true?>
				<?$input->class_value('mceAdvanced')?>
			<?endif?>

			<?=$input->render()?>
	</div>

	<?if ($message = $input->message()):?>
		<p class="message"><?=$message ?></p>
	<?endif?>
</div>