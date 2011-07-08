<?php
/**
 * Bcrypt Library
 * 
 * @package Modules
 * @subpackage Auth2
 * @see http://stackoverflow.com/questions/6340105/how-can-we-create-a-fairly-secure-password-hash-in-php
 * @author		EightPHP Development Team
 * @copyright	(c) 2009-2010 EightPHP
 * @license		http://license.eightphp.com
 */
class Bcrypt_Core {
	
	private $rounds;
	private $randomState;
	
	public function __construct($rounds = 12) {
		if(CRYPT_BLOWFISH != 1) {
			throw new Eight_Exception("bcrypt not supported in this installation. See http://php.net/crypt");
		}

		$this->rounds = $rounds;
	}

	public function hash($input) {
		$hash = crypt($input, $this->getSalt());

		if(strlen($hash) > 13) {
			return $hash;
		}

		return FALSE;
	}

	public function verify($input, $existingHash) {
		$hash = crypt($input, $existingHash);
		return $hash === $existingHash;
	}

	private function getSalt() {
		$salt = sprintf('$2a$%02d$', $this->rounds);

		$bytes = $this->getRandomBytes(16);

		$salt .= $this->encodeBytes($bytes);

		return $salt;
	}

	private function getRandomBytes($count) {
		$bytes = '';

		if(function_exists('openssl_random_pseudo_bytes') && (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN')) { // OpenSSL slow on Win
			$bytes = openssl_random_pseudo_bytes($count);
		}

		if($bytes === '' && is_readable('/dev/urandom') && ($hRand = @fopen('/dev/urandom', 'rb')) !== FALSE) {
			$bytes = fread($hRand, $count);
			fclose($hRand);
		}

		if(strlen($bytes) < $count) {
			$bytes = '';

			if($this->randomState === null) {
				$this->randomState = microtime();
				if(function_exists('getmypid')) {
					$this->randomState .= getmypid();
				}
			}

			for($i = 0; $i < $count; $i += 16) {
				$this->randomState = md5(microtime() . $this->randomState);

				if (PHP_VERSION >= '5') {
					$bytes .= md5($this->randomState, true);
				} else {
					$bytes .= pack('H*', md5($this->randomState));
				}
			}

			$bytes = substr($bytes, 0, $count);
		}

		return $bytes;
	}

	private function encodeBytes($input) {
		// The following is code from the PHP Password Hashing Framework
		$itoa64 = './ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

		$output = '';
		$i = 0;
		while(1) {
		  $c1 = ord($input[$i++]);
		  $output .= $itoa64[$c1 >> 2];
		  $c1 = ($c1 & 0x03) << 4;
		  if ($i >= 16) {
		    $output .= $itoa64[$c1];
		    break;
		  }

		  $c2 = ord($input[$i++]);
		  $c1 |= $c2 >> 4;
		  $output .= $itoa64[$c1];
		  $c1 = ($c2 & 0x0f) << 2;

		  $c2 = ord($input[$i++]);
		  $c1 |= $c2 >> 6;
		  $output .= $itoa64[$c1];
		  $output .= $itoa64[$c2 & 0x3f];
		}

		return $output;
	}

} // End Bcrypt