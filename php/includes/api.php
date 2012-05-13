<?php

class API {
	
	private $db;
	private $generics;
	private $settings;
	private $id;
	
	private $errors = array(
		1 => array('error' => 1, 'error_name' => 'unknown command'),
		2 => array('error' => 2, 'error_name' => 'Missing parameters'),
		4 => array('error' => 4, 'error_name' => 'Missing user id'),
		8 => array('error' => 8, 'error_name' => 'No data'),
		16 => array('error' => 16, 'error_name' => 'Invalid user id'),
	);
	
	function __construct( $settings ) {
		
		$this->db = new DBWrapper($settings['db']['host'],$settings['db']['username'],$settings['db']['password'],$settings['db']['dbname']);
		
		$this->settings = $settings['config'];
		$this->id = false;
		$this->generics = new Generics_API($this->db, $this->settings['salt']);
	}
	
	function __destruct() {
		$this->db->close();
	}
	
	function handleCommand() {
		
		if (isset($_GET['cmd'])) {
			$cmd = $_GET['cmd'];
			unset($_GET['cmd']);
			switch ($cmd) {
				case 'create': $this->handleCreate(); return;
				case 'authenticate': $this->handleAuthenticate(); return;
				case 'app_get': $this->handleAppGet(); return;
				case 'app_set': $this->handleAppSet(); return;
				case 'user_get': $this->handleUserGet(); return;
				case 'user_set': $this->handleUserSet(); return;
				//case 'push_notification': $this->handlePushNotification(); return;
				//case '': $this->handle(); return;
			}
		}
		
		$this->handleResponse(1);
	}
	
	function handleCreate() {
		if (isset($_GET['username']) && isset($_GET['password'])) {
			if ($userID = $this->generics->createUser(trim($_GET['username']), trim($_GET['password']))) {
				$this->handleResponse(false,array('user_id' => $userID));
			} else {
				$this->handleResponse(false,$this->generics->error());
			}
		} else {
			$this->handleResponse(2);
		}
	}
	
	function handleAuthenticate() {
		if (isset($_GET['username']) && isset($_GET['password'])) {
			if ($userID = $this->generics->authenticateUser(trim($_GET['username']), trim($_GET['password']))) {
				$this->handleResponse(false,array('user_id' => $userID));
			} else {
				$this->handleResponse(false,$this->generics->error());
			}
		} else {
			$this->handleResponse(2);
		}
	}
	
	function handleAppGet() {
		
		if (isset($_GET['key'])) {
			$this->getData();
		}
		$this->handleResponse(2);
	}
	
	function handleAppSet() {
		
		if (count($_GET) > 0) {
			$this->setData();
		}
		$this->handleResponse(2);
	}
	
	function handleUserGet() {
		
		if (isset($_GET['key']) && $this->validateUserID()) {
			$this->getData();
		}
		$this->handleResponse(2);
	}
	
	function handleUserSet() {
		
		if (count($_GET) > 0 && $this->validateUserID()) {
			$this->setData();
		}
		$this->handleResponse(2);
	}
	
	function handleResponse( $code, $message = "" ) {
		header('Content-Type: application/json');
		echo $code ? json_encode($this->errors[$code]) : json_encode($message);
		exit;
	}
	
	function validateUserID() {
		
		if (isset($_GET['userID'])) {
			if ($this->generics->authenticateUserID(trim($_GET['userID']))) {
				$this->id = $_GET['userID'];
				unset($_GET['userID']);
				return $this->id;
			} else {
				$this->handleResponse(16);
			}
		} else {
			$this->handleResponse(4);
		}
	}
	
	private function getData() {
		
		$keys = explode(",",$_GET['key']);
		$results = array();
		foreach($keys as $key) {
			if (empty($key)) continue;
			$value = $this->generics->get(trim($key), $this->id);
			$results[$key] = $value;
		}
		if (count($results) == 0) {
			$this->handleResponse(8);
		} else {
			$this->handleResponse(false, $results);
		}
	}
	
	private function setData() {
		
		$results = array();
		foreach($_GET as $key => $value) {
			$results[$key] = $this->generics->set(trim($key), $value, $this->id);
		}
		if (count($results) == 0) {
			$this->handleResponse(8);
		} else {
			$this->handleResponse(false, $results);
		}
	}
	
}

?>