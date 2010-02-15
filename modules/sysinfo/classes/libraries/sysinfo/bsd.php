<?php 

/**
 * @package		Modules
 * @subpackage	SystemInfo
 */

class SysInfo_BSD_Core extends SysInfo {
	protected $dmesg; 
	protected $parser;

	public function __construct() {
		$this->parser = new SysInfo_Parser();
		$this->parser->df_param = "";		
	} 
	
	// read /var/run/dmesg.boot, but only if we haven't already.
	public function read_dmesg() {
		if (! $this->dmesg) {
			if( PHP_OS == "Darwin" ) {
				$this->dmesg = array();
			} else {
				$parts = explode("rebooting", rfts( '/var/run/dmesg.boot' ) );
				$this->dmesg = explode("\n", $parts[count($parts) - 1]);
			}
		} 
		return $this->dmesg;
	} 
	
	// grabs a key from sysctl(8)
	public function grab_key($key) {
		return self::execute_program('sysctl', "-n $key");
	} 
	
	// get our apache SERVER_NAME or vhost
	public function hostname() {
		if (!($result = getenv('SERVER_NAME'))) {
			$result = "N.A.";
		} 
		return $result;
	} 
	
	// get our canonical hostname
	public function chostname() {
		return self::execute_program('hostname');
	} 
	
	// get the IP address of our canonical hostname
	public function ip_addr() {
		if (!($result = getenv('SERVER_ADDR'))) {
			$result = gethostbyname($this->chostname());
		} 
		return $result;
	} 

	public function kernel() {
		$s = $this->grab_key('kern.version');
		$a = explode(':', $s);
		return $a[0] . $a[1] . ':' . $a[2];
	} 

	public function uptime() {
		$result = $this->get_sys_ticks();

		return $result;
	} 

	public function users() {
		return self::execute_program('who', '| wc -l');
	} 

	public function loadavg ($bar = false) {
		$s = $this->grab_key('vm.loadavg');
		$s = ereg_replace('{ ', '', $s);
		$s = ereg_replace(' }', '', $s);
		$results['avg'] = explode(' ', $s);

		if ($bar) {
			if ($fd = $this->grab_key('kern.cp_time')) {
				// Find out the CPU load
				// user + sys = load
				// total = total
				preg_match($this->cpu_regexp2, $fd, $res );
				$load = $res[2] + $res[3] + $res[4];		// cpu.user + cpu.sys
				$total = $res[2] + $res[3] + $res[4] + $res[5];	// cpu.total

				// we need a second value, wait 1 second befor getting (< 1 second no good value will occour)
				sleep(1);
				$fd = $this->grab_key('kern.cp_time');
				preg_match($this->cpu_regexp2, $fd, $res );
				$load2 = $res[2] + $res[3] + $res[4];
				$total2 = $res[2] + $res[3] + $res[4] + $res[5];
				$results['cpupercent'] = (100*($load2 - $load)) / ($total2 - $total);
			}
		}
		return $results;
	} 

	public function cpu_info() {
		$results = array();
		$ar_buf = array();

		$results['model'] = $this->grab_key('hw.model');
		$results['cpus'] = $this->grab_key('hw.ncpu');

		for ($i = 0, $max = count($this->read_dmesg()); $i < $max; $i++) {
			$buf = $this->dmesg[$i];
			if (preg_match("/$this->cpu_regexp/", $buf, $ar_buf)) {
				$results['cpuspeed'] = round($ar_buf[2]);
				break;
			} 
		} 
		return $results;
	} 
	
	// get the scsi device information out of dmesg
	public function scsi() {
		$results = array();
		$ar_buf = array();

		for ($i = 0, $max = count($this->read_dmesg()); $i < $max; $i++) {
			$buf = $this->dmesg[$i];

			if (preg_match("/$this->scsi_regexp1/", $buf, $ar_buf)) {
				$s = $ar_buf[1];
				$results[$s]['model'] = $ar_buf[2];
				$results[$s]['media'] = 'Hard Disk';
			} elseif (preg_match("/$this->scsi_regexp2/", $buf, $ar_buf)) {
				$s = $ar_buf[1];
				$results[$s]['capacity'] = $ar_buf[2] * 2048 * 1.049;
			}
		} 
		// return array_values(array_unique($results));
		// 1. more useful to have device names
		// 2. php 4.1.1 array_unique() deletes non-unique values.
		asort($results);
		return $results;
	} 

	// get the pci device information out of dmesg
	public function pci() {
		$results = array();

		if( !( is_array($results = $this->parser->parse_lspci()) || is_array($results = $this->parser->parse_pciconf() ))) {
				for ($i = 0, $s = 0; $i < count($this->read_dmesg()); $i++) {
			$buf = $this->dmesg[$i];
			if(!isset($this->pci_regexp1) && !isset($this->pci_regexp2)) {
					$this->pci_regexp1 = '/(.*): <(.*)>(.*) pci[0-9]$/';
					$this->pci_regexp2 = '/(.*): <(.*)>.* at [.0-9]+ irq/';
			}
			if (preg_match($this->pci_regexp1, $buf, $ar_buf)) {
					$results[$s++] = $ar_buf[1] . ": " . $ar_buf[2];
			} elseif (preg_match($this->pci_regexp2, $buf, $ar_buf)) {
					$results[$s++] = $ar_buf[1] . ": " . $ar_buf[2];
			}
	} 
			asort($results);
		}
		return $results;
	} 

	// get the ide device information out of dmesg
	public function ide() {
		$results = array();

		$s = 0;
		for ($i = 0, $max = count($this->read_dmesg()); $i < $max; $i++) {
			$buf = $this->dmesg[$i];

			if (preg_match('/^(ad[0-9]+): (.*)MB <(.*)> (.*) (.*)/', $buf, $ar_buf)) {
				$s = $ar_buf[1];
				$results[$s]['model'] = $ar_buf[3];
				$results[$s]['media'] = 'Hard Disk';
				$results[$s]['capacity'] = $ar_buf[2] * 2048 * 1.049;
			} elseif (preg_match('/^(acd[0-9]+): (.*) <(.*)> (.*)/', $buf, $ar_buf)) {
				$s = $ar_buf[1];
				$results[$s]['model'] = $ar_buf[3];
				$results[$s]['media'] = 'CD-ROM';
			}
		} 
		// return array_values(array_unique($results));
		// 1. more useful to have device names
		// 2. php 4.1.1 array_unique() deletes non-unique values.
		asort($results);
		return $results;
	} 

	// place holder function until we add acual usb detection
	public function usb() {
		return array();
	} 

	public function sbus() {
		$results = array();
		$_results[0] = "";
		// TODO. Nothing here yet. Move along.
		$results = $_results;
		return $results;
	}

	public function memory() {
		$s = $this->grab_key('hw.physmem');

		if (PHP_OS == 'FreeBSD' || PHP_OS == 'OpenBSD') {
			// vmstat on fbsd 4.4 or greater outputs kbytes not hw.pagesize
			// I should probably add some version checking here, but for now
			// we only support fbsd 4.4
			$pagesize = 1024;
		} else {
			$pagesize = $this->grab_key('hw.pagesize');
		} 

		$results['ram'] = array();

		$pstat = self::execute_program('vmstat');
		$lines = split("\n", $pstat);
		for ($i = 0, $max = sizeof($lines); $i < $max; $i++) {
			$ar_buf = preg_split("/\s+/", $lines[$i], 19);
			if ($i == 2) {
				if(PHP_OS == 'NetBSD') {
					$results['ram']['free'] = $ar_buf[5];
				} else {
					$results['ram']['free'] = $ar_buf[5] * $pagesize / 1024;
				}
			} 
		} 

		$results['ram']['total'] = $s / 1024;
		$results['ram']['shared'] = 0;
		$results['ram']['buffers'] = 0;
		$results['ram']['used'] = $results['ram']['total'] - $results['ram']['free'];
		$results['ram']['cached'] = 0;

		$results['ram']['percent'] = round(($results['ram']['used'] * 100) / $results['ram']['total']);

		if (PHP_OS == 'OpenBSD' || PHP_OS == 'NetBSD') {
			$pstat = self::execute_program('swapctl', '-l -k');
		} else {
			$pstat = self::execute_program('swapinfo', '-k');
		} 

		$lines = split("\n", $pstat);

		$results['swap']['total'] = 0;
		$results['swap']['used'] = 0;
		$results['swap']['free'] = 0;

		for ($i = 1, $max = sizeof($lines); $i < $max; $i++) {
			$ar_buf = preg_split("/\s+/", $lines[$i], 6);

			if ($ar_buf[0] != 'Total') {
				$results['swap']['total'] = $results['swap']['total'] + $ar_buf[1];
				$results['swap']['used'] = $results['swap']['used'] + $ar_buf[2];
				$results['swap']['free'] = $results['swap']['free'] + $ar_buf[3];

				$results['devswap'][$i - 1] = array();
				$results['devswap'][$i - 1]['dev'] = $ar_buf[0];
				$results['devswap'][$i - 1]['total'] = $ar_buf[1];
				$results['devswap'][$i - 1]['used'] = $ar_buf[2];
				$results['devswap'][$i - 1]['free'] = ($results['devswap'][$i - 1]['total'] - $results['devswap'][$i - 1]['used']);
				$results['devswap'][$i - 1]['percent'] = $ar_buf[2] > 0 ? round(($ar_buf[2] * 100) / $ar_buf[1]) : 0;
			}
		} 
		$results['swap']['percent'] = round(($results['swap']['used'] * 100) / $results['swap']['total']);

		if( is_callable( array( 'sysinfo', 'memory_additional' ) ) ) {
				$results = $this->memory_additional( $results );
		}
		return $results;
	} 

	public function filesystems() {
		return $this->parser->parse_filesystems();
	}

	public function distro() { 
		$distro = self::execute_program('uname', '-s');														 
		$result = $distro;
		return($result);							 
	}
} 

?>
