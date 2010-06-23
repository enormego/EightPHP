<?php

/**
*
* Copyright (c) 2009, Dan Myers.
* Parts copyright (c) 2008, Donovan Schönknecht.
* All rights reserved.
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
*
* This is a modified BSD license (the third clause has been removed).
* The BSD license may be found here:
* http://www.opensource.org/licenses/bsd-license.php
*
* Amazon SQS is a trademark of Amazon.com, Inc. or its affiliates.
*
* SQS is based on Donovan Schönknecht's Amazon S3 PHP class, found here:
* http://undesigned.org.za/2007/10/22/amazon-s3-php-class
*/

/**
 * Amazon SQS PHP class
 *
 * @link		http://sourceforge.net/projects/php-sqs/
 * @version		0.9.2
 * @package		Modules
 * @subpackage	Amazon
 */
class Amazon_SQS_Core
{
	private static $__accessKey; // AWS Access key
	private static $__secretKey; // AWS Secret key
	private static $endpoint = 'queue.amazonaws.com';

	public static $useSSL = true;
	public static $verifyHost = 1;
	public static $verifyPeer = 1;
	
	public static $connectionTimeout = 0;
	public static $requestExecutionTimeout = 0;

	/**
	* Constructor - if you're not using the class statically
	*
	* @param string $accessKey Access key
	* @param string $secretKey Secret key
	* @param boolean $useSSL Enable SSL
	* @return void
	*/
	public function __construct($accessKey = null, $secretKey = null, $useSSL = true) {
		if ($accessKey !== null && $secretKey !== null)
			self::setAuth($accessKey, $secretKey);
		self::$useSSL = $useSSL;
	}
	
	/**
	 * Update the CURL timeout values
	 * Use 0 to disable timeouts
	 * 
	 * @param	integer	value for CURLOPT_CONNECTTIMEOUT
	 * @param	integer value for CURLOPT_TIMEOUT 
	 */
	public static function setTimeouts($connectionTimeout=0, $requestExecutionTimeout=0) {
		self::$connectionTimeout = $connectionTimeout;
		self::$requestExecutionTimeout = $requestExecutionTimeout;
	}

	/**
	* Set AWS access key and secret key
	*
	* @param string $accessKey Access key
	* @param string $secretKey Secret key
	* @return void
	*/
	public static function setAuth($accessKey, $secretKey) {
		self::$__accessKey = $accessKey;
		self::$__secretKey = $secretKey;
	}

	/**
	* Enable or disable VERIFYHOST for SSL connections
	* Only has an effect if $useSSL is true
	*
	* @param boolean $enable Enable VERIFYHOST
	* @return void
	*/
	public static function enableVerifyHost($enable = true) {
		self::$verifyHost = ($enable ? 1 : 0);
	}

	/**
	* Enable or disable VERIFYPEER for SSL connections
	* Only has an effect if $useSSL is true
	*
	* @param boolean $enable Enable VERIFYPEER
	* @return void
	*/
	public static function enableVerifyPeer($enable = true) {
		self::$verifyPeer = ($enable ? 1 : 0);
	}

	/**
	* Create a queue
	*
	* @param string  $queue The queue to create
	* @param integer $visibility_timeout The visibility timeout for the new queue
	* @return boolean
	*/
	public static function createQueue($queue, $visibility_timeout = null) {
		$rest = new SQSRequest(self::$endpoint, '', 'CreateQueue', 'PUT', self::$__accessKey);

		$rest->setParameter('QueueName', $queue);

		if($visibility_timeout !== null)
		{
			$rest->setParameter('DefaultVisibilityTimeout', $visibility_timeout);
		}

		$rest = $rest->getResponse();
		if ($rest->error === false && $rest->code !== 200)
			$rest->error = array('code' => $rest->code, 'message' => 'Unexpected HTTP status');
		if ($rest->error !== false) {
			Amazon_SQS::__triggerError(__FUNCTION__, $rest->error);
			return false;
		}

		return true;
	}

	/**
	* Delete a queue
	*
	* @param string $queue The queue to delete
	* @return boolean
	*/
	public static function deleteQueue($queue) {
		$rest = new SQSRequest(self::$endpoint, $queue, 'DeleteQueue', 'DELETE', self::$__accessKey);
		$rest = $rest->getResponse();
		if ($rest->error === false && $rest->code !== 200)
			$rest->error = array('code' => $rest->code, 'message' => 'Unexpected HTTP status');
		if ($rest->error !== false) {
			Amazon_SQS::__triggerError(__FUNCTION__, $rest->error);
			return false;
		}

		return true;
	}

	/**
	* Get a list of queues
	*
	* @param string $prefix Only return queues starting with this string (optional)
	* @return array | false
	*/
	public static function listQueues($prefix = '', $queueNameOnly = FALSE) {
		$rest = new SQSRequest(self::$endpoint, '', 'ListQueues', 'GET', self::$__accessKey);

		if(strlen($prefix) > 0)
		{
			$rest->setParameter('QueueNamePrefix', $prefix);
		}

		$rest = $rest->getResponse();
		if ($rest->error === false && $rest->code !== 200)
			$rest->error = array('code' => $rest->code, 'message' => 'Unexpected HTTP status');
		if ($rest->error !== false) {
			Amazon_SQS::__triggerError(__FUNCTION__, $rest->error);
			return false;
		}

		$results = array();
		if (!isset($rest->body->ListQueuesResult))
		{
			return $results;
		}

		foreach($rest->body->ListQueuesResult->QueueUrl as $q)
		{
			$q = $queueNameOnly === TRUE ? end(explode('/', $q)) : $q;  
			$results[] = (string)$q;
		}

		return $results;
	}

	/**
	* Get a queue's attributes
	*
	* @param string $queue The queue for which to retrieve attributes
	* @param string $attribute Which attribute to retrieve (default is 'All')
	* @return array (name => value) | false
	*/
	public static function getQueueAttributes($queue, $attribute = 'All') {
		$rest = new SQSRequest(self::$endpoint, $queue, 'GetQueueAttributes', 'GET', self::$__accessKey);

		$rest->setParameter('AttributeName', $attribute);

		$rest = $rest->getResponse();
		if ($rest->error === false && $rest->code !== 200)
			$rest->error = array('code' => $rest->code, 'message' => 'Unexpected HTTP status');
		if ($rest->error !== false) {
			Amazon_SQS::__triggerError(__FUNCTION__, $rest->error);
			return false;
		}

		$results = array();
		if (!isset($rest->body->GetQueueAttributesResult))
		{
			return $results;
		}

		foreach($rest->body->GetQueueAttributesResult->Attribute as $a)
		{
			$results[(string)($a->Name)] = (string)($a->Value);
		}

		return $results;
	}

	/**
	* Set attributes on a queue
	*
	* @param string $queue The queue for which to set attributes
	* @param string $attribute The name of the attribute to set
	* @param string $value The value of the attribute
	* @return boolean
	*/
	public static function setQueueAttributes($queue, $attribute, $value) {
		$rest = new SQSRequest(self::$endpoint, $queue, 'SetQueueAttributes', 'PUT', self::$__accessKey);

		$rest->setParameter('Attribute.Name', $attribute);
		$rest->setParameter('Attribute.Value', $value);

		$rest = $rest->getResponse();
		if ($rest->error === false && $rest->code !== 200)
			$rest->error = array('code' => $rest->code, 'message' => 'Unexpected HTTP status');
		if ($rest->error !== false) {
			Amazon_SQS::__triggerError(__FUNCTION__, $rest->error);
			return false;
		}

		return true;
	}

	/**
	* Send a message to a queue
	*
	* @param string $queue The queue which will receive the message
	* @param string $message The body of the message to send
	* @return boolean
	*/
	public static function sendMessage($queue, $message) {
		$rest = new SQSRequest(self::$endpoint, $queue, 'SendMessage', 'PUT', self::$__accessKey);

		$rest->setParameter('MessageBody', $message);

		$rest = $rest->getResponse();
		if ($rest->error === false && $rest->code !== 200)
			$rest->error = array('code' => $rest->code, 'message' => 'Unexpected HTTP status');
		if ($rest->error !== false) {
			Amazon_SQS::__triggerError(__FUNCTION__, $rest->error);
			return false;
		}

		return true;
	}

	/**
	* Receive a message from a queue
	*
	* @param string  $queue The queue for which to retrieve attributes
	* @param integer $num_messages The maximum number of messages to retrieve
	* @param integer $visibility_timeout The visibility timeout of the retrieved message
	* @return array of array(key => value) | false
	*/
	public static function receiveMessage($queue, $num_messages = null, $visibility_timeout = null) {
		$rest = new SQSRequest(self::$endpoint, $queue, 'ReceiveMessage', 'GET', self::$__accessKey);

		if($num_messages !== null)
		{
			$rest->setParameter('MaxNumberOfMessages', $num_messages);
		}
		if($visibility_timeout !== null)
		{
			$rest->setParameter('VisibilityTimeout', $visibility_timeout);
		}

		$rest = $rest->getResponse();
		if ($rest->error === false && $rest->code !== 200)
			$rest->error = array('code' => $rest->code, 'message' => 'Unexpected HTTP status');
		if ($rest->error !== false) {
			Amazon_SQS::__triggerError(__FUNCTION__, $rest->error);
			return false;
		}

		$results = array();
		if (!isset($rest->body->ReceiveMessageResult))
		{
			return $results;
		}

		foreach($rest->body->ReceiveMessageResult->Message as $m)
		{
			$message = array();
			$message['MessageId'] = (string)($m->MessageId);
			$message['ReceiptHandle'] = (string)($m->ReceiptHandle);
			$message['MD5OfBody'] = (string)($m->MD5OfBody);
			$message['Body'] = (string)($m->Body);
			$results[] = $message;
		}

		return $results;
	}

	/**
	* Delete a message from a queue
	*
	* @param string $queue The queue containing the message to delete
	* @param string $receipt_handle The request id of the message to delete
	* @return boolean
	*/
	public static function deleteMessage($queue, $receipt_handle) {
		$rest = new SQSRequest(self::$endpoint, $queue, 'DeleteMessage', 'DELETE', self::$__accessKey);

		$rest->setParameter('ReceiptHandle', $receipt_handle);

		$rest = $rest->getResponse();
		if ($rest->error === false && $rest->code !== 200)
			$rest->error = array('code' => $rest->code, 'message' => 'Unexpected HTTP status');
		if ($rest->error !== false) {
			Amazon_SQS::__triggerError(__FUNCTION__, $rest->error);
			return false;
		}

		return true;
	}

	/**
	* Trigger an error message
	*
	* @internal Used by member functions to output errors
	* @param array $error Array containing error information
	* @return string
	*/
	public static function __triggerError($functionname, $error)
	{
		if($error['curl'])
		{
			trigger_error(sprintf("Amazon_SQS::%s(): %s", $functionname, $error['code']), E_USER_WARNING);
		}
		else
		{
			$message = sprintf("Amazon_SQS::%s(): Error %s caused by %s.", $functionname,
								$error['Code'], $error['Type']);
			$message .= sprintf("\nMessage: %s\n", $error['Message']);
			if(strlen($error['Detail']) > 0)
			{
				$message .= sprintf("Detail: %s\n", $error['Detail']);
			}
			trigger_error($message, E_USER_WARNING);
		}
	}

	/**
	* Generate the auth string using Hmac-SHA256
	*
	* @internal Used by SQSRequest::getResponse()
	* @param string $string String to sign
	* @return string
	*/
	public static function __getSignature($string) {
		return base64_encode(hash_hmac('sha256', $string, self::$__secretKey, true));
	}
}

/**
 * Amazon SQS Request PHP class
 *
 * @link		http://sourceforge.net/projects/php-sqs/
 * @version		0.9.2
 * @package		Modules
 * @subpackage	Amazon
 */
final class SQSRequest {
	private $url, $queue, $verb, $expires, $resource = '', $parameters = array();
	public $response;

	/**
	* Constructor
	*
	* @param string $url Queue URL, without queue name or trailing slash
	* @param string $queue Queue name, without leading slash
	* @param string $action SimpleDB action
	* @param string $verb HTTP verb
	* @param string $accesskey AWS Access Key
	* @param boolean $expires If true, uses Expires instead of Timestamp
	* @return mixed
	*/
	function __construct($url, $queue, $action, $verb, $accesskey, $expires = false) {
		$this->parameters['Action'] = $action;
		$this->parameters['Version'] = '2009-02-01';
		$this->parameters['SignatureVersion'] = '2';
		$this->parameters['SignatureMethod'] = 'HmacSHA256';
		$this->parameters['AWSAccessKeyId'] = $accesskey;

		$this->verb = $verb;
		$this->expires = $expires;
		$this->url = $url;
		$this->queue = $queue;
		$this->response = new STDClass;
		$this->response->error = false;
	}

	/**
	* Set request parameter
	*
	* @param string $key Key
	* @param string $value Value
	* @return void
	*/
	public function setParameter($key, $value) {
		$this->parameters[$key] = $value;
	}

	/**
	* Get the response
	*
	* @return object | false
	*/
	public function getResponse() {

		if($this->expires)
		{
			$this->parameters['Expires'] = gmdate('c');
		}
		else
		{
			$this->parameters['Timestamp'] = gmdate('c');
		}

		$params = array();
		foreach ($this->parameters as $var => $value)
		{
			$params[] = $var.'='.rawurlencode($value);
		}

		sort($params, SORT_STRING);

		$query = implode('&', $params);

		$strtosign = $this->verb."\n".$this->url."\n/".$this->queue."\n".$query;
		$query .= '&Signature='.rawurlencode(Amazon_SQS::__getSignature($strtosign));

		$ssl = (Amazon_SQS::$useSSL && extension_loaded('openssl'));
		$url = ($ssl ? 'https://' : 'http://').$this->url.'/'.$this->queue.'?'.$query;

		// Basic setup
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_USERAGENT, 'SQS/php');

		if (Amazon_SQS::$useSSL) {
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, Amazon_SQS::$verifyHost);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, Amazon_SQS::$verifyPeer);
		}

		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, false);
		curl_setopt($curl, CURLOPT_WRITEFUNCTION, array(&$this, '__responseWriteCallback'));
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

		// Timeouts
		curl_setopt($curl, CURLOPT_TIMEOUT, Amazon_SQS::$requestExecutionTimeout);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, Amazon_SQS::$connectionTimeout);

		// Request types
		switch ($this->verb) {
			case 'GET': break;
			case 'PUT': case 'POST':
				curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $this->verb);
				$headers[] = 'Content-Type: application/x-www-form-urlencoded';
			break;
			case 'HEAD':
				curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'HEAD');
				curl_setopt($curl, CURLOPT_NOBODY, true);
			break;
			case 'DELETE':
				curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
			break;
			default: break;
		}

		if(count($headers) > 0)
		{
			curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		}

		// Execute, grab errors
		if (curl_exec($curl))
			$this->response->code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		else
			$this->response->error = array(
				'curl' => true,
				'code' => curl_errno($curl),
				'message' => curl_error($curl),
				'resource' => $this->resource
			);

		@curl_close($curl);

		// Parse body into XML
		if ($this->response->error === false && isset($this->response->body)) {
			$this->response->body = simplexml_load_string($this->response->body);

			// Grab SQS errors
			if (!in_array($this->response->code, array(200, 204))
				&& isset($this->response->body->Error)) {
				$this->response->error = array(
					'curl' => false,
					'Type' => (string)$this->response->body->Error->Type,
					'Code' => (string)$this->response->body->Error->Code,
					'Message' => (string)$this->response->body->Error->Message,
					'Detail' => (string)$this->response->body->Error->Detail
				);
				unset($this->response->body);
			}
		}

		return $this->response;
	}

	/**
	* CURL write callback
	*
	* @param resource &$curl CURL resource
	* @param string &$data Data
	* @return integer
	*/
	private function __responseWriteCallback(&$curl, &$data) {
		$this->response->body .= $data;
		return strlen($data);
	}
}

