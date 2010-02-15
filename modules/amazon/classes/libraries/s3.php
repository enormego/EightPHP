<?php
/**
* Copyright (c) 2007, Donovan Schonknecht.  All rights reserved.
*
* Redistribution and use in source and binary forms, with or without
* modification, are permitted provided that the following conditions are met:
*
* - Redistributions of source code must retain the above copyright notice,
*   this list of conditions and the following disclaimer.
* - Redistributions in binary form must reproduce the above copyright
*   notice, this list of conditions and the following disclaimer in the
*   documentation and/or other materials provided with the distribution.
*
* THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
* AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
* IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
* ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
* LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
* CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
* SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
* INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
* CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
* ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
* POSSIBILITY OF SUCH DAMAGE.
*/

/**
 * Amazon S3 PHP class
 *
 * @package		Modules
 * @subpackage	Amazon
 */

class S3 {
	protected $accessKey;
	protected $secretKey;

	const ACL_PRIVATE = 'private';
	const ACL_PUBLIC_READ = 'public-read';
	const ACL_PUBLIC_READ_WRITE = 'public-read-write';

	public function __construct($accessKey = null, $secretKey = null) {
		if ($accessKey !== null) $this->accessKey = $accessKey;
		if ($secretKey !== null) $this->secretKey = $secretKey;
	}

	public function set_auth($accessKey, $secretKey) {
		$this->accessKey = $accessKey;
		$this->secretKey = $secretKey;
	}

	public function getBucket($bucket, $prefix = null, $marker = null, $maxKeys = null) {
		$get = new S3Object('GET', $bucket, '');
		if ($prefix !== null) $get->setParameter('prefix', $prefix);
		if ($marker !== null) $get->setParameter('marker', $marker);
		if ($maxKeys !== null) $get->setParameter('max-keys', $maxKeys);
		$get = $get->getResponse($this);
		$contents = array();
		if (isset($get->body->Contents))
		foreach ($get->body->Contents as $c) $contents[(string)$c->Key] = array(
			'size' => (int)$c->Size,
			'time' => strToTime((string)$c->LastModified),
			'hash' => substr((string)$c->ETag, 1, -1)
		);
		return $get->code == 200 ? $contents : false;
	}

	public function putBucket($bucket, $acl = self::ACL_PRIVATE) {
		$put = new S3Object('PUT', $bucket, '');
		$put->setAmzHeader('x-amz-acl', $acl);
		$put = $put->getResponse($this);
		return $put->code == 200 ? true : $put->body;
	}

	public function deleteBucket($bucket = '') {
		$delete = new S3Object('DELETE', $bucket);
		$delete = $delete->getResponse($this);
		return $delete->code == 204 ? true : false;
	}

	/**
	* Delete an object
	*/
	public function getObject($bucket = '', $uri = '') {
		$get = new S3Object('GET', $bucket, $uri);
		$get = $get->getResponse($this);
		return $get->code == 200 ? $get : false;
	}

	/**
	* Delete an object
	*/
	public function deleteObject($bucket = '', $uri = '') {
		$delete = new S3Object('DELETE', $bucket, $uri);
		$delete = $delete->getResponse($this);
		return $delete->code == 204 ? true : (array)$delete->body;
	}

	/**
	* Put a file
	*/
	public function putObjectFile($file, $bucket, $uri, $acl = self::ACL_PRIVATE, $metaHeaders = array(), $mime=NULL, $filename="", $extraHeaders=array()) {
		$put = new S3Object('PUT', $bucket, $uri);
		$put->file = $file;
		
		$ext = end(explode(".", $uri));

		if($mime === NULL) {
			$mime = $this->__getMimeType($file);
		}
		
		if(empty($filename)) {
			$filename = end(explode("/", $filename));
		}
		
		$filename = addslashes($filename);
		$filename = '"'.$filename.'"';

		if(stristr($mime,"image")) {
			$put->setHeader('Content-Type', $mime);
			$put->setHeader('Content-Disposition', "inline");
		} else if(stristr($mime,"video") || stristr($mime,"audio") || stristr($mime,"text")) {
			$put->setHeader('Content-Type', $mime);
			$put->setHeader('Content-Disposition', "attachment; filename=".$filename);
		} else {
			if(preg_match("#(php|pl|php3|php4|php5|phtml|cgi|py|rb)$#i", $ext)) {
				$put->setHeader('Content-Disposition', "attachment; filename=".$filename);
				$put->setHeader('Content-Type', "text/plain");
			} else if(preg_match("#(html|htm|shtml|shtm|xhtml|xhtm)$#i", $ext)) {
				$put->setHeader('Content-Disposition', "attachment; filename=".$filename);
				$put->setHeader('Content-Type', "text/plain");
			} else if(preg_match("#(flv|swf|mp3|avi|mp4|m4a|mpg|xvid|divx|mov|3gp|mpeg|m4v|wmv|wma|ogg|wav)$#i", $ext)) {
				$put->setHeader('Content-Disposition', "attachment; filename=".$filename);
				$put->setHeader('Content-Type', $mime);
			} else if(preg_match("#(exe|zip|tar|gz|sit|sitx|doc?x|rtf)$#i", $ext)) {
				$put->setHeader('Content-Disposition', "attachment; filename=".$filename);
				$put->setHeader('Content-Type', $mime);
			} else if(preg_match("#(pdf)$#i", $ext)) {
				$put->setHeader('Content-Disposition', "inline");
				$put->setHeader('Content-Type', 'application/pdf');
			} else if(preg_match("#(gif)$#i", $ext)) {
				$put->setHeader('Content-Disposition', "inline");
				$put->setHeader('Content-Type', "image/gif");
			} else if(preg_match("#(jpe?g|jfif|jpg|jff)$#i", $ext)) {
				$put->setHeader('Content-Disposition', "inline");
				$put->setHeader('Content-Type', "image/jpeg");
			} else if(preg_match("#(png)$#i", $ext)) {
				$put->setHeader('Content-Disposition', "inline");
				$put->setHeader('Content-Type', "image/png");
			} else {
				$put->setHeader('Content-Disposition', "attachment; filename=".$filename);
				$put->setHeader('Content-Type', $mime);
			}
		}
	
		if(is_array($extraHeaders) && count($extraHeaders) > 0) {
			foreach($extraHeaders as $key => $value) {
				$put->setHeader($key, $value);
			}
		}
		
		$put->setHeader('Content-MD5', base64_encode(md5_file($file, true)));
		$put->setAmzHeader('x-amz-acl', $acl);
		foreach ($metaHeaders as $metaHeader => $metaValue)
			$put->setAmzHeader('x-amz-meta-'.$metaHeader, $metaValue);
		$put = $put->getResponse($this);
		return $put->code == 200 ? true : $put;
	}

	/**
	* Put a string
	*/
	public function putObjectString($string, $bucket, $uri, $acl = self::ACL_PRIVATE, $metaHeaders = array(), $contentType = 'text/plain') {
		$put = new S3Object('PUT', $bucket, $uri);
		$put->data = $string;
		$put->setHeader('Content-Type', $contentType);
		$put->setHeader('Content-MD5', base64_encode(md5($string, true)));
		$put->setAmzHeader('x-amz-acl', $acl);
		foreach ($metaHeaders as $metaHeader => $metaValue)
			$put->setAmzHeader('x-amz-meta-'.$metaHeader, $metaValue);
		$put = $put->getResponse($this);
		return $put->code == 200 ? true : false;
	}


	/**
	* Generate the auth header: "Authorization: AWS AccessKey:Signature"
	* This uses the PECL hash extension if loaded.
	*/
	public function getAuthString($string) {
		if (extension_loaded('hash')) return 'AWS '.$this->accessKey.':'.
			base64_encode(hash_hmac('sha1', $string, $this->secretKey, true));
		else return 'AWS '.$this->accessKey.':'.base64_encode(pack('H*', sha1(
			(str_pad($this->secretKey, 64, chr(0x00)) ^
			(str_repeat(chr(0x5c), 64))) . pack('H*', sha1(
				(str_pad($this->secretKey, 64, chr(0x00)) ^
				(str_repeat(chr(0x36), 64))) . $string
			))
		)));
	}

	/**
	* MIME type for PUT Object
	*/
	private function __getMimeType(&$file) {
		if (function_exists('mime_content_type')) return mime_content_type($file);
		if (function_exists('finfo_open')) {
			$finfo = finfo_open(FILEINFO_MIME, "/usr/share/magic");
			$m = finfo_file($finfo, $file);
			finfo_close($finfo);
			return $m;
		}
		
		$exts = array(
			'jpg' => 'image/jpeg', 'gif' => 'image/gif', 'png' => 'image/png',
			'tif' => 'image/tiff', 'tiff' => 'image/tiff', 'ico' => 'image/x-icon',
			'swf' => 'application/x-shockwave-flash', 'pdf' => 'application/pdf',
			'zip' => 'application/zip', 'tar' => 'application/x-tar',
			'gz' => 'application/x-gzip', 'bz' => 'application/x-bzip',
			'bz2' => 'application/x-bzip2', 'txt' => 'text/plain', 'asc' => 'text/plain',
			'htm' => 'text/html', 'html' => 'text/html', 'xslt' => 'application/xslt+xml',
			'xsl' => 'text/xml', 'xml' => 'text/xml', 'ogg' => 'application/ogg',
			'mp3' => 'audio/mpeg', 'wav' => 'audio/x-wav', 'avi' => 'video/x-msvideo',
			'mpg' => 'video/mpeg', 'mpeg' => 'video/mpeg', 'mpe' => 'video/mpeg',
			'mov' => 'video/quicktime'
		);

		$ext = strToLower(pathInfo($file, PATHINFO_EXTENSION));
		return isset($exts[$ext]) ? $exts[$ext] : 'application/octet-stream';
	}
}

/**
 * Response container
 *
 * @package		Modules
 * @subpackage	Amazon
 */
final class S3Response { public $code, $headers, $body; }

/**
 * CURL Request
 *
 * @package		Modules
 * @subpackage	Amazon
 */
final class S3Request {
	const MODE_HTTP = 'http://';
	const MODE_HTTPS = 'https://';

	function __construct(S3 &$s3, S3Object &$obj, &$response) {
		if (!extension_loaded('curl')) dl('curl.so');

		$curlReq = curl_init();
		curl_setopt($curlReq, CURLOPT_USERAGENT, get_class($this));
		curl_setopt($curlReq, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($curlReq, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curlReq, CURLOPT_URL,
			self::MODE_HTTPS.$obj->headers['Host'].$obj->uri .
			((sizeof($obj->parameters) > 0) ? '?'.http_build_query($obj->parameters) : '')
		);

		$headers = array();
		foreach ($obj->amzHeaders as $header => $value) $headers[] = $header.': '.$value;
		foreach ($obj->headers as $header => $value) $headers[] = $header.': '.$value;
		curl_setopt($curlReq, CURLOPT_HTTPHEADER, $headers);

		switch ($obj->verb) {
			case 'PUT': {
				if ($obj->file !== false) {
					curl_setopt($curlReq, CURLOPT_PUT, true);
					curl_setopt($curlReq, CURLOPT_INFILE, fopen($obj->file, 'rb'));
					curl_setopt($curlReq, CURLOPT_INFILESIZE, filesize($obj->file));
				} elseif ($obj->data !== false) {
					curl_setopt($curlReq, CURLOPT_CUSTOMREQUEST, 'PUT');
					curl_setopt($curlReq, CURLOPT_POSTFIELDS, $obj->data);
				} else curl_setopt($curlReq, CURLOPT_CUSTOMREQUEST, 'PUT');
			}
			break;
			case 'GET': break;
			case 'DELETE': curl_setopt($curlReq, CURLOPT_CUSTOMREQUEST, 'DELETE'); break;
		}
		curl_setopt($curlReq, CURLOPT_HEADER, true);
		curl_setopt($curlReq, CURLOPT_FOLLOWLOCATION, false);
		curl_setopt($curlReq, CURLOPT_RETURNTRANSFER, true);

		$response = new S3Response;
		list($headers, $response->body) = explode("\r\n\r\n", curl_exec($curlReq));
		foreach (explode("\n", $headers) as $header) {
			list($key, $value) = explode(": ", trim($header));
			$response->headers[$key] = $value;
		}
		if (isset($response->headers['Content-Type']) &&
		$response->headers['Content-Type'] == 'application/xml')
			$response->body = @simplexml_load_string($response->body);
		$response->code = curl_getinfo($curlReq, CURLINFO_HTTP_CODE);
		curl_close($curlReq);
	}
}

/**
 * Builds the request data
 *
 * @package		Modules
 * @subpackage	Amazon
 */
final class S3Object {
	const DATE_RFC822 = 'D, d M Y H:i:s T';
	public $headers = array(
		'Host' => '', 'Date' => '', 'Content-MD5' => '', 'Content-Type' => ''
	),
	$parameters = array(), $verb, $bucket, $uri, $amzHeaders = array(),
	$resource = '', $file = false, $data = false;

	function __construct($verb, $bucket = '', $uri = '') {
		$this->verb = $verb;
		$this->bucket = $bucket;
		$this->uri = $uri !== '' ? '/'.$uri : '/';
		if ($this->bucket !== '') {
			$bucket = explode('/', $bucket);
			$this->resource = '/'.$bucket[0].$this->uri;
			$this->headers['Host'] = $bucket[0].'.s3.amazonaws.com';
			$this->bucket = implode('/', $bucket);
		} else {
			$this->headers['Host'] = 's3.amazonaws.com';
			$this->resource = '/'.$this->bucket.$this->uri;
		}
		$this->headers['Date'] = gmdate(self::DATE_RFC822);
	}

	public function getResponse(S3 &$s3) {
		$amz = array();
		foreach ($this->amzHeaders as $amzHeader => $amzHeaderValue)
			$amz[] = strToLower($amzHeader).':'.$amzHeaderValue;
		$amz = (sizeof($amz) > 0) ? "\n".implode("\n", $amz) : '';
		$this->headers['Authorization'] = $s3->getAuthString(
			$this->verb."\n".
			$this->headers['Content-MD5']."\n".
			$this->headers['Content-Type']."\n".
			$this->headers['Date'].$amz."\n".$this->resource
		);
		new S3Request($s3, $this, $response);
		return $response;
	}

	public function setParameter($key, $value) {
		$this->parameters[$key] = $value;
	}

	public function setHeader($key, $value) {
		$this->headers[$key] = $value;
	}

	public function setAmzHeader($key, $value) {
		$this->amzHeaders[$key] = $value;
	}
}
