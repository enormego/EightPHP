<?php 

/**
 * @package		Modules
 * @subpackage	SystemInfo
 */

class SysInfo_FreeBSD_Core extends SysInfo_BSD {
	var $cpu_regexp	 = "";
	var $scsi_regexp1 = "";
	var $scsi_regexp2 = "";
	var $cpu_regexp2	= "";
	
	public function __construct() {
		parent::__construct();
		$this->cpu_regexp = "CPU: (.*) \((.*)-MHz (.*)\)";
		$this->scsi_regexp1 = "^(.*): <(.*)> .*SCSI.*device";
		$this->scsi_regexp2 = "^(da[0-9]): (.*)MB ";
		$this->cpu_regexp2 = "/(.*) ([0-9]+) ([0-9]+) ([0-9]+) ([0-9]+)/";
	} 

	public function get_sys_ticks() {
		$s = explode(' ', $this->grab_key('kern.boottime'));
		$a = ereg_replace('{ ', '', $s[3]);
		$sys_ticks = time() - $a;
		return $sys_ticks;
	} 

	public function network() {
		$netstat = self::execute_program('netstat', '-nibd | grep Link');
		$lines = split("\n", $netstat);
		$results = array();
		for ($i = 0, $max = sizeof($lines); $i < $max; $i++) {
			$ar_buf = preg_split("/\s+/", $lines[$i]);
			if (!empty($ar_buf[0])) {
				$results[$ar_buf[0]] = array();

				if (strlen($ar_buf[3]) < 15) {
					$results[$ar_buf[0]]['rx_bytes'] = $ar_buf[5];
					$results[$ar_buf[0]]['rx_packets'] = $ar_buf[3];
					$results[$ar_buf[0]]['rx_errs'] = $ar_buf[4];
					$results[$ar_buf[0]]['rx_drop'] = $ar_buf[10];

					$results[$ar_buf[0]]['tx_bytes'] = $ar_buf[8];
					$results[$ar_buf[0]]['tx_packets'] = $ar_buf[6];
					$results[$ar_buf[0]]['tx_errs'] = $ar_buf[7];
					$results[$ar_buf[0]]['tx_drop'] = $ar_buf[10];

					$results[$ar_buf[0]]['errs'] = $ar_buf[4] + $ar_buf[7];
					$results[$ar_buf[0]]['drop'] = $ar_buf[10];
				} else {
					$results[$ar_buf[0]]['rx_bytes'] = $ar_buf[6];
					$results[$ar_buf[0]]['rx_packets'] = $ar_buf[4];
					$results[$ar_buf[0]]['rx_errs'] = $ar_buf[5];
					$results[$ar_buf[0]]['rx_drop'] = $ar_buf[11];

					$results[$ar_buf[0]]['tx_bytes'] = $ar_buf[9];
					$results[$ar_buf[0]]['tx_packets'] = $ar_buf[7];
					$results[$ar_buf[0]]['tx_errs'] = $ar_buf[8];
					$results[$ar_buf[0]]['tx_drop'] = $ar_buf[11];

					$results[$ar_buf[0]]['errs'] = $ar_buf[5] + $ar_buf[8];
					$results[$ar_buf[0]]['drop'] = $ar_buf[11];
				} 
			} 
		} 
		return $results;
	} 

	public function memory_additional($results) {
		$pagesize = $this->grab_key("hw.pagesize");
		$results['ram']['cached'] = $this->grab_key("vm.stats.vm.v_cache_count") * $pagesize / 1024;
		$results['ram']['cached_percent'] = round( $results['ram']['cached'] * 100 / $results['ram']['total']);
		$results['ram']['app'] = $this->grab_key("vm.stats.vm.v_active_count") * $pagesize / 1024;
		$results['ram']['app_percent'] = round( $results['ram']['app'] * 100 / $results['ram']['total']);
		$results['ram']['buffers'] = $results['ram']['used'] - $results['ram']['app'] - $results['ram']['cached'];
		$results['ram']['buffers_percent'] = round( $results['ram']['buffers'] * 100 / $results['ram']['total']);
		return $results;
	}
} 

?>
