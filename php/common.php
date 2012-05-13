<?php
	
	require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/globals.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/mysqli.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/generic_api.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/api.php';
	
	$api = new API($config['development']);
	$api->handleCommand();
?>