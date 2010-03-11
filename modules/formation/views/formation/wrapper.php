<?=$open?>
	<? /* Handle the hidden fields at the top of the form */ ?>
	<?foreach($inputs as $input):?>
		<?if($input instanceof Form_Hidden):?>
			<?=View::factory('formation/hidden', array('input' => $input))?>
		<?endif?>
	<?endforeach?>
	
	<table class="main_wrapper">
		<?if ($title != ''): ?>
			<caption><?=$title?></caption>
		<?endif?>
		<tr>
			<td>
				<?=View::factory('formation/inputs', array('inputs' => $inputs))?>
			</td>
		</tr>
	</table>
<?=$close?>