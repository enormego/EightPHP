<? defined('SYSPATH') OR die('No direct access allowed.');

ob_start();
$tput_cols = trim(`tput cols`);
ob_end_clean();

if(is_numeric($tput_cols)) {
	$_ENV['box_width'] = $tput_cols-8;
} else {
	$_ENV['box_width'] = 100;
}

echo "\n";
add_break();
add_line($type . ' - ' . $code);
add_line("");
add_line("File: ".Eight_Exception::debug_path($file));
add_line("Line: ".$line);
add_break();
add_line("Message:");
foreach(explode("\n", $message) as $msg_line) {
	add_line(trim($msg_line));
}
add_break();

if (Eight_Exception::$trace_output) {
	add_line("Stack trace:");
	add_line("");

	$x = 0;
	foreach (Eight_Exception::trace($trace) as $i=>$step) {
		$msg_line = "#".str_pad($x, 2, "0", STR_PAD_LEFT). "  ";
		if ($step['file']) {
			 $source_id = $error_id.'source'.$i;
			$msg_line .= Eight_Exception::debug_path($step['file']).'('.$step['line']."):  ";
		} else {
			$msg_line .= "{".__('PHP internal call')."}:  ";
		}
		
		$msg_line .= $step['function'].'(';
		$print_able_args = array();
		if ($step['args']) {
			$args_id = $error_id.'args'.$i;
			foreach($step['args'] as $arg) {
				$arg_name = "";
				
				if(is_object($arg)) {
					$arg_name = get_class($arg);
				} else if(is_array($arg)) {
					$arg_name = "Array(".count($arg).")";
				} else if(is_null($arg)) {
					$arg_name = "NULL";
				} else if(is_string($arg)) {
					$arg_name = $arg;
				} else {
					$arg_name = strval($arg);
				}
				
				$arg_name = preg_replace("#\s+#", " ", $arg_name);
				
				$print_able_args[] = str::limit_chars($arg_name,15,"");
			}
		}
		
		$msg_line .= implode(", ", $print_able_args);
		
		$msg_line .= ")";
		add_line($msg_line);
		$x++;
	}
}
add_break();
echo"\n";

function add_line($str) {
	echo "    | ".str_pad(str::limit_chars($str, ($_ENV['box_width']-4), ""), ($_ENV['box_width']-4), " ")." |\n";
}

function add_break() {
	echo "    +".str_repeat('-', ($_ENV['box_width']-2))."+\n";
}
?>