<?php
/*
	
	API Class
	
	This class will act as an API for the generics system.
	
	
*/

class Generics_API {
	
	private $db;
	private $salt;
	private $error;
	
	private $errors = array(
		'1' => array(
			'code' => 1,
			'text' => 'Username aleady exists.'
		),
		'2' => array(
			'code' => 2,
			'text' => 'Username does not exist.'
		),
		'4' => array(
			'code' => 4,
			'text' => 'Incorrect password.'
		),
		'8' => array(
			'code' => 8,
			'text' => 'Blank key.'
		),
		'16' => array(
			'code' => 16,
			'text' => 'Key not found.'
		),
		'32' => array(
			'code' => 32,
			'text' => 'Database error.'
		)
	);
	
	function __construct( $database, $salt ) {
		
		if (get_class($database) != 'DBWrapper') {
			throw new Exception('DBWrapper class required for Generics_API. Input was: ' . get_class($database));
		}
		
		$this->db = $database;
		$this->salt = $salt;
		$this->error = false;
	}
	
	function get( $key, $id = false ) {
		
		if (!$key) {
			$this->error = 8;
			return false;
		}
		
		if ($id) {
			$sql = sprintf( "SELECT value FROM `app_user_data` WHERE `id` = '%s' AND `key` = '%s'", $this->db->escape($id), $this->db->escape($key) );
		} else {
			$sql = sprintf( "SELECT value FROM `app_data` WHERE `key` = '%s'", $this->db->escape($key) );
		}
		
		$result = $this->db->query($sql);
		if ($this->db->error()) {
			$this->error = 32;
			return false;
		} else if ($this->db->num_rows($result) == 0) {
			$this->error = 16;
			return false;
		} else {
			$value = $this->db->fetch_assoc($result);
			$this->error = false;
			return $value['value'];
		}
	}
	
	function set( $key, $value, $id = false ) {
		
		if (!$key) {
			$this->error = 8;
			return false;
		}
		
		if ($id) {
			$sql = sprintf("INSERT INTO `app_user_data` (`id`,`key`,`value`) VALUES ('%s','%s','%s') ON DUPLICATE KEY UPDATE `value` = '%s'", 
							$this->db->escape($id),
							$this->db->escape($key),
							$this->db->escape($value),
							$this->db->escape($value));
		} else {
			$sql = sprintf("INSERT INTO `app_data` (`key`,`value`) VALUES ('%s','%s') ON DUPLICATE KEY UPDATE `value` = '%s'", 
							$this->db->escape($key),
							$this->db->escape($value),
							$this->db->escape($value));
		}
		
		$result = $this->db->query($sql);
		
		if ($this->db->error()) {
			$this->error = 32;
			return false;
		} else {
			$this->error = false;
			return true;
		}
	}
	
	function createUser( $username, $password ) {
		
		$sql = sprintf("SELECT * FROM `app_users` WHERE `username` = '%s'", $this->db->escape($username));
		$result = $this->db->query($sql);
		
		if ($this->db->num_rows($result) > 0) {
			$this->error = 1;
			return false;
		}
		
		$hashed_password = hash('sha256', $password . $this->salt);
		
		$sql = sprintf("INSERT INTO `app_users` (`username`,`password`) VALUES ('%s','%s')", $this->db->escape($username), $this->db->escape($hashed_password));
		$result = $this->db->query($sql);
		
		if ($this->db->error()) {
			$this->error = 32;
			return false;
		}
		
		$this->error = false;
		return $this->db->insert_id();
	}
	
	function authenticateUser( $username, $password ) {
		
		$sql = sprintf("SELECT id,username,password FROM `app_users` WHERE `username` = '%s'", $this->db->escape($username));
		$result = $this->db->query($sql);
		
		if ($this->db->num_rows($result) > 0) {
			$user = $this->db->fetch_assoc($result);
			$hashed_password = hash('sha256', $password. $this->salt);
			
			if ($user['password'] == $hashed_password) {
				$this->error = false;
				return $user['id'];
			} else {
				$this->error = 4;
				return false;
			}
		} else {
			$this->error = 2;
			return false;
		}
	}
	
	function authenticateUserID( $id ) {
		$sql = sprintf("SELECT * FROM `app_users` WHERE `id` = '%s'", $this->db->escape($id));
		$result = $this->db->query($sql);
		
		if ($this->db->error()) {
			$this->error = 32;
			return false;
		}
		
		$this->error = false;
		return $this->db->num_rows($result) > 0;
	}
	
	function customSQL( $sql, $array_result = true ) {
		
		$result = $this->db->query($sql);
		
		if ($this->db->error()) {
			$this->error = 32;
			return false;
		}
		
		$this->error = false;
		if ($array_result) {
			$results = array();
			while($row = $this->db->fetch_assoc($result)) {
				$results[] = $row;
			}
			return $results;
		} else {
			return true;
		}
	}
	
	function error() {
		return $this->error ? $this->errors[$this->error] : false;
	}
}
?>