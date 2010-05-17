<!-- Profiler Starts -->
<style type="text/css">
<?=file_get_contents(Eight::find_file('views', 'eight_profiler', NO, 'css'))?>
<?php echo $styles ?>
</style>

<?
$style='';
$x = 0 + Eight::config('profiler.offset.x');
$y = 0 + Eight::config('profiler.offset.y');

switch(Eight::config('profiler.position')) {
	case 'top_left':
		$style .= 'top: '.$y.'px; left: '.$x.'px;';
		break;
	case 'top_right':
		$style .= 'top: '.$y.'px; right: '.$x.'px;';
		break;
	case 'btm_left':
		$style .= 'bottom: '.$y.'px; left: '.$x.'px;';
		break;
	case 'btm_right':
		$style .= 'bottom: '.$y.'px; right: '.$x.'px;';
		break;
}

$style = 'style="'.$style.'"';

?>
<div id="eight-profiler-toggler" <?=$style?> onclick="if(document.getElementById('eight-profiler').style.display == 'block'){ document.getElementById('eight-profiler').style.display = 'none'; } else { document.getElementById('eight-profiler').style.display = 'block'; }">
	Toggle Debug Info
</div>

<div id="eight-profiler">
	<div class="eight-profiler-inner">
		<div id="eight-profiler-tabs">
		<?
			foreach($profiles as $profile) {
				echo "<a href='#'".(!$first_selected ? ' class="eight-profiler-tab-active"' : '')." onclick='return eight_profiler_toggle_tab(this, \"".$profile->table_id()."\")'>".$profile->name()."</a>";
				if(!$first_selected) {
					$first_selected = YES;
				}
			}
			
			$first_selected = NO;
		?>
		</div>
		<br style="clear:left" /><br />
		<div id="eight-profiler-tables">
		<?
			foreach($profiles as $profile) {
				$hidden = YES;
			
				if(!$first_selected) {
					$first_selected = YES;
					$hidden = NO;
				}
			
				echo $profile->render($hidden);
			}
		?>
		</div>
		<p class="ep-meta">Profiler executed in <?php echo number_format($execution_time, 3) ?>s</p>
	</div>
</div>

<script>
	function eight_profiler_toggle_tab(tab, table_id) {
		var tags = document.getElementById('eight-profiler-tabs').getElementsByTagName('a');
		var x;
		for(x=0;x<tags.length;x++) {
			tags[x].className = "";
		}
		
		tab.className = "eight-profiler-tab-active";
		
		var tables = document.getElementById('eight-profiler-tables').getElementsByTagName('table');
		for(x=0;x<tables.length;x++) {
			tables[x].style.display = "none";
		}

		document.getElementById(table_id).style.display = "";
		
		return false;
	}
</script>

<!-- Profiler Ends -->
