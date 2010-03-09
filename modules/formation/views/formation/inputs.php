<? /* Set the default layout */ ?>
<?if(!isset($layout)) $layout = 'rows'?>

<?if($parent == 'group'):?>
	<table class="group">
		<?if(isset($input)):?>
			<?if(!str::e($input->label())):?>
			<tr>
				<th colspan="<?=count($inputs)?>">
					<?=$input->label()?>
					<?if($message = $input->message()):?>
						<p class="message"><?=$message?></p>
					<?endif?>
				</th>
			</tr>
			<?endif?>
		<?endif?>
		
		<?if($layout == 'columns'):?>
			<tr>
		<?endif?>
<?endif?>

<?foreach($inputs as $input):?>
	<? /* Skip hidden fields since we processed them already */ ?>
	<?if($input instanceof Form_Hidden):?>
		<?continue?>
	<?endif?>
	
	<?if($parent == 'group'):?>
		<?if($layout == 'columns'):?>
			<td>
		<?else:?>
			<tr><td>
		<?endif?>
	<?endif?>
	
	<?if($input instanceof Form_Group):?>
		<?=View::factory('formation/inputs', array('input' => $input, 'inputs' => $input->inputs, 'layout' => $input->layout, 'parent' => 'group'))?>
	<?endif?>
	
	<? /* Regular old input. This will only run if the other things up top didn't match */ ?>
	<?if(!($input instanceof Form_Group)):?>
		<?=View::factory('formation/input', array('input' => $input, 'layout' => $layout))?>
	<?endif?>
	
	<?if($parent == 'group'):?>
		<?if($layout == 'columns'):?>
			</td>
		<?else:?>
			</td></tr>
		<?endif?>
	<?endif?>
	
<?endforeach?>

<?if($parent == 'group'):?>
		<?if($layout == 'columns'):?>
			</tr>
		<?endif?>
	</table>
<?endif?>