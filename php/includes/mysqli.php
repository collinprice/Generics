<?php
	
/*
	DatabaseWrapper class
*/

class DBWrapper {
	
	var $link_id;
	var $query_result;
	
	var $error_no;
	var $error_msg;
	
	function DBWrapper( $hostname, $username, $password, $dbname ) {
		$this->link_id = @mysqli_connect($hostname, $username, $password, $dbname);
		
		if ( !$this->link_id ) {
			die( json_encode( array( "error" => mysqli_connect_error() ) ) );
		}
		
		return $this->link_id;
	} // constructor
	
	function query( $sql ) {
		
		$this->query_result = @mysqli_query( $this->link_id, $sql );
		
		if ( $this->query_result ) {
			return $this->query_result;
		}
		
		$this->error_no = @mysqli_errno( $this->link_id );
		$this->error_msg = @mysqli_error( $this->link_id );
		return false;
	} // query
	
	function fetch_assoc( $id = 0 )		{ return $id ? @mysqli_fetch_assoc( $id ) : false; }
	function fetch_row( $id = 0 )		{ return $id ? @mysqli_fetch_row( $id ) : false; }
	function num_rows( $id = 0 )		{ return $id ? @mysqli_num_rows( $id ) : false; }
	function insert_id()				{ return $this->link_id ? @mysqli_insert_id( $this->link_id ) : false; }
	
	function escape( $s )
	{
		if( is_array( $s ) )
			return '';
		return mysqli_real_escape_string( $this->link_id, $s );
	} // escape
	
	function error()
	{
		if ($this->error_no) {
			$result['error_no'] = $this->error_no;
			$result['error_msg'] = $this->error_msg;
			return $result;
		}
		return false;
	} // error
	
	function close()
	{
		// Sanity
		if( !$this->link_id )
			return false;
		
		// If we have a result, free that too
		if( $this->query_result )
			@mysqli_free_result( $this->query_result );
		
		// Close up shop
		return @mysqli_close( $this->link_id );
	} // close
}

//$db = new DBWrapper( $mysql_host, $mysql_user, $mysql_password, $mysql_database );
?>