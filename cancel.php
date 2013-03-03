<?php
	require_once 'conn.php';

	$ret = null;

	if (isset($_POST['name'])) {
		$name = $_POST['name'];
		$redis->publish("user:channel:$name", 'CANCEL');
		$ret = array('status' => 'OK');
	} else {
		$ret = array('err' => 'Missing name field');
	}

	header('Content-Type: application/json');
	echo json_encode($ret);
?>
