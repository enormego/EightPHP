<?php
/**
 * Enormego pagination style
 * 
 */

$start = (($current_page - $num_links) > 0) ? $current_page - $num_links : 1;
$end   = (($current_page + $num_links) < $total_pages) ? $current_page + $num_links : $total_pages;

?>

<span class="pagination">
	<span class="pgof">Showing: <b><?=$current_first_item?> &ndash; <?=$current_last_item?></b>&nbsp;of <?=$total_items?></span> &nbsp;
	<?if($total_pages > 1):?>
		<? /*
		<?php if ($first_page): ?>
			<a href="<?=str_replace('{page}', 1, $url)?>">&laquo;&nbsp;First</a>
		<?php endif ?>
		*/ ?>
		
		<?php if ($previous_page): ?>
			<a href="<?=str_replace('{page}', $previous_page, $url)?>">Previous</a>
		<?php endif ?>
	
		<?for($loop = $start; $loop <= $end; $loop++):?>
			<?if($current_page == $loop):?>
				<span class="current"><?=$loop?></span>
			<?else:?>
				<?=$n = ($i == 0) ? '' : $i;?>
				<a href="<?=str_replace('{page}', $loop, $url)?>"><?=$loop?></a>
			<?endif;?>
		<?endfor;?>
	
		<?php if ($next_page): ?>
			<a href="<?=str_replace('{page}', $next_page, $url)?>">Next</a>
		<?php endif ?>
		
		<? /*
		<?php if ($last_page): ?>
			<a href="<?=str_replace('{page}', $last_page, $url)?>">Last&nbsp;&raquo;</a>
		<?php endif ?>
		*/ ?>
		
	<?endif?>
</span>