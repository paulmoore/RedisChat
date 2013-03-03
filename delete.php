<?php
	require_once 'conn.php';

	$ret = null;

	if (isset($_POST['name'])) {
		$name = $_POST['name'];
		$redis->del("user:$name");
		$redis->del("channels:$name");
		$redis->publish("channel:$name", 'CANCEL');
		$redis->publish('channel:all', json_encode(array(
			'name' => 'SERVER',
			'message' => "$name has left the server"
		)));
		$ret = array('status' => 'OK');
	} else {
		$ret = array('err' => 'Missing field age');
	}

	header('Content-Type: application/json');
	echo json_encode($ret);
?>
