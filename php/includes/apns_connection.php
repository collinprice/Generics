<?php

/*
	This class is used to establish a connection to the Apple Push Notification Service
	and send a notification.
	
	Constructor ( configuration file, database object )
	
	configuration file - array containing the following properties
		certificate - location of the sign certificate from Apple.
		passphrase - 
		server - APNS server to use.
*/

class APNS_Connection {
	
	private $db;
	private $config;
	private $connection;
	private $error;
	
	function __construct($configuration, $database) {
		$this->db = $database;
		$this->config = $configuration;
	}
	
	/*
		Establish connection to the Apple Push Notification Service.
		Returns
			true - Successful connection.
			false - Could not connect. Check error() for error.
	*/
	function connect() {
		$ctx = stream_context_create();
		stream_context_set_option($ctx, 'ssl', 'local_cert', $this->config['certificate']);
		stream_context_set_option($ctx, 'ssl', 'passphrase', $this->config['passphrase']);

		$this->connection = stream_socket_client(
			'ssl://' . $this->config['server'], $err, $errstr, 60,
			STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);

		if (!$this->connection) {
			return false;
		} else {
			$this->error = false;
			return true;
		}
	}
	
	/*
		Disconnect from Apple Push Notification Service.
	*/
	function close() {
		@fclose($this->connection);
		$this->connection = NULL;
	}
	
	/*
		Send a payload to the Apple Push Notification Service.
		Returns
			true - Message sent.
			false - Could not send message. Check error() for error.
	*/
	function sendPayload($msgId, $deviceToken, $payload) {
		if (strlen($deviceToken) != 64) {
			$this->error = "Message $messageId has invalid device token.";
			return false;
		}
		
		if (!this->connection) {
			$this->error = "Not connected to APNS.";
			return false;
		}
		
		$msg = chr(0)                       // command (1 byte)
		     . pack('n', 32)                // token length (2 bytes)
		     . pack('H*', $deviceToken)     // device token (32 bytes)
		     . pack('n', strlen($payload))  // payload length (2 bytes)
		     . $payload;                    // the JSON payload
		
		$result = @fwrite($this->connection, $msg, strlen($msg));
		
		if (!$result) {
			$this->error = "Message not delivered.";
			return false;
		} else {
			$this->error = false;
			return true;
		}
	}
	
	/*
		Check is an error occurred.
		Returns
			string - Reason for error.
			false - No error.
	*/
	function error() {
		return $this->error;
	}
}

?>