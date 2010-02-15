<?php

/**
 * @package		Modules
 * @subpackage	SystemInfo
 */
 
abstract class SysInfo_Core {
	
	// Use SysInfo::factory() to initialize a new instance of this class, do not attempt to do it yourself.
	public static function factory() {
		$class_name = "SysInfo_".PHP_OS;
		return new $class_name();
	}
	
	// Public methods to get sysinfo

	abstract public function hostname();
	abstract public function chostname();
	abstract public function ip_addr();
	abstract public function kernel();
	abstract public function uptime();
	abstract public function users();
	abstract public function loadavg ($bar = false);
	abstract public function cpu_info();
	abstract public function scsi();
	abstract public function pci();
	abstract public function ide();
	abstract public function usb();
	abstract public function sbus();
	abstract public function memory();
	abstract public function filesystems();
	abstract public function distro();
	
	// Private, common methods used by the subclasses
	
	// Find a system program.  Do path checking
	public static function find_program($strProgram) {
		global $addpaths;

		$arrPath = array( '/bin', '/sbin', '/usr/bin', '/usr/sbin', '/usr/local/bin', '/usr/local/sbin' );
		if( isset( $addpaths ) && is_array( $addpaths ) ) {
			$arrPath = array_merge( $arrPath, $addpaths );
		}
		if ( function_exists( "is_executable" ) ) {
			foreach ( $arrPath as $strPath ) {
				$strProgrammpath = $strPath . "/" . $strProgram;
				if( is_executable( $strProgrammpath ) ) {
					return $strProgrammpath;
				}
			}
		} else {
			return strpos( $strProgram, '.exe' );
		}
	}

	// Execute a system program. return a trim()'d result.
	// does very crude pipe checking.  you need ' | ' for it to work
	// ie $program = execute_program('netstat', '-anp | grep LIST');
	// NOT $program = execute_program('netstat', '-anp|grep LIST');
	public static function execute_program($strProgramname, $strArgs = '', $booErrorRep = true ) {
		global $error;
		$strBuffer = '';
		$strError = '';

		$strProgram = self::find_program($strProgramname);
		if ( ! $strProgram ) {
			if( $booErrorRep ) {
				// $error->addError( '$this->find_program(' . $strProgramname . ')', 'program not found on the machine', __LINE__, __FILE__);
			}
			return "ERROR";
		}
		// see if we've gotten a |, if we have we need to do patch checking on the cmd
		if( $strArgs ) {
			$arrArgs = split( ' ', $strArgs );
			for( $i = 0; $i < count( $arrArgs ); $i++ ) {
				if ( $arrArgs[$i] == '|' ) {
					$strCmd = $arrArgs[$i + 1];
					$strNewcmd = self::find_program( $strCmd );
					$strArgs = ereg_replace( "\| " . $strCmd, "| " . $strNewcmd, $strArgs );
				}
			}
		}
		// no proc_open() below php 4.3
		if( function_exists( 'proc_open' ) ) {
			$descriptorspec = array(
				0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
				1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
				2 => array("pipe", "w")   // stderr is a pipe that the child will write to
			);
			$process = proc_open( $strProgram . " " . $strArgs, $descriptorspec, $pipes );
			if( is_resource( $process ) ) {
				while( !feof( $pipes[1] ) ) {
					$strBuffer .= fgets( $pipes[1], 1024 );
				}
				fclose( $pipes[1] );
				while( !feof( $pipes[2] ) ) {
					$strError .= fgets( $pipes[2], 1024 );
				}
				fclose( $pipes[2] );
			}
			$return_value = proc_close( $process );
		} else {
			if( $fp = popen( "(" . $strProgram . " " . $strArgs . " > /dev/null) 3>&1 1>&2 2>&3", 'r' ) ) {
				while( ! feof( $fp ) ) {
					$strError .= fgets( $fp, 4096 );
				}
				pclose( $fp );
			}
			$strError = trim( $strError );
			if( $fp = popen( $strProgram . " " . $strArgs, 'r' ) ) {
				while( ! feof( $fp ) ) {
					$strBuffer .= fgets( $fp, 4096 );
				}
				$return_value = pclose( $fp );
			}
		}

		$strError = trim( $strError );
		$strBuffer = trim( $strBuffer );

		if( ! empty( $strError ) || $return_value <> 0 ) {
			if( $booErrorRep ) {
				// $error->addError( $strProgram, $strError . "\nReturn value: " . $return_value, __LINE__, __FILE__);
			}
		}
		return $strBuffer;
	}
	
	public static function hide_mount( $strMount ) {
		global $hide_mounts;

		if( isset( $hide_mounts ) && is_array( $hide_mounts ) && in_array( $strMount, $hide_mounts ) ) {
			return true;
		} else {
			return false;
		}
	}
	
	public static function hide_fstype( $strFSType ) {
		global $hide_fstypes;

		if( isset( $hide_fstypes ) && is_array( $hide_fstypes ) && in_array( $strFSType, $hide_fstypes ) ) {
			return true;
		} else {
			return false;
		}
	}	
	
	public static function rfts( $strFileName, $intLines = 0, $intBytes = 4096, $booErrorRep = true ) {
		global $error;
		$strFile = "";
		$intCurLine = 1;

		if( file_exists( $strFileName ) ) {
			if( $fd = fopen( $strFileName, 'r' ) ) {
				while( !feof( $fd ) ) {
					$strFile .= fgets( $fd, $intBytes );
					if( $intLines <= $intCurLine && $intLines != 0 ) {
						break;
					} else {
						$intCurLine++;
					}
				}
				fclose( $fd );
			} else {
				if( $booErrorRep ) {
					$error->addError( 'fopen(' . $strFileName . ')', 'file can not read by phpsysinfo', __LINE__, __FILE__ );
				}
				return "ERROR";
			}
		} else {
			if( $booErrorRep ) {
				$error->addError( 'file_exists(' . $strFileName . ')', 'the file does not exist on your machine', __LINE__, __FILE__ );
			}
			return "ERROR";
		}

		return $strFile;
	}

	public static function gdc( $strPath, $booErrorRep = true ) {
		global $error;
		$arrDirectoryContent = array();

		if( is_dir( $strPath ) ) {
			if( $handle = opendir( $strPath ) ) {
				while( ( $strFile = readdir( $handle ) ) !== false ) {
					if( $strFile != "." && $strFile != ".." && $strFile != "CVS" ) {
						$arrDirectoryContent[] = $strFile;
					}
				}
				closedir( $handle );
			} else {
				if( $booErrorRep ) {
					$error->addError( 'opendir(' . $strPath . ')', 'directory can not be read by phpsysinfo', __LINE__, __FILE__ );
				}
			}
		} else {
			if( $booErrorRep ) {
				$error->addError( 'is_dir(' . $strPath . ')', 'directory does not exist on your machine', __LINE__, __FILE__ );
			}
		}

		return $arrDirectoryContent;
	}
}