<? /* Set the default layout */ ?>
<?if(!isset($layout)) $layout = 'rows'?>

<?if($parent == 'group'):?>
	<div class="group <?=$input->name?><?=str::e($input->label()) ? ' no_header' : ' has_header'?>">
	<table class="group">
		<?if(isset($input)):?>
			<?if(!str::e($input->label())):?>
			<tr>
				<th colspan="<?=count($inputs)?>">
					<?=$input->label()?>
				</th>
			</tr>
			<?endif?>
		<?endif?>
		
		<?if($layout == 'columns'):?>
			<tr>
		<?endif?>
<?endif?>

<?foreach($inputs as $i):?>
	<? /* Skip hidden fields since we processed them already */ ?>
	<?if($i instanceof Form_Hidden):?>
		<?continue?>
	<?endif?>
	
	<?if($parent == 'group'):?>
		<?if($layout == 'columns'):?>
			<td>
		<?else:?>
			<tr><td>
		<?endif?>
	<?endif?>
	
	<?if($i instanceof Form_Group):?>
		<?=View::factory('formation/inputs', array('input' => $i, 'inputs' => $i->inputs, 'layout' => $i->layout, 'parent' => 'group'))?>
	<?endif?>
	
	<? /* Regular old input. This will only run if the other things up top didn't match */ ?>
	<?if(!($i instanceof Form_Group)):?>
		<?=View::factory('formation/input', array('input' => $i, 'layout' => $layout))?>
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
	<?if($message = $input->message()):?>
		<p class="message"><?=$message?></p>
	<?endif?>
	</div>
<?endif?>