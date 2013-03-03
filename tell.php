<?php
	require_once 'conn.php';

	$ret = null;

	if (isset($_POST['name'])) {
		$name = $_POST['name'];
		if (isset($_POST['message'])) {
			$message = $_POST['message'];
			if (isset($_POST['recipient'])) {
				$recipient = $_POST['recipient'];
				$message = array(
					'name' => $name,
					'channel' => $recipient,
					'message' => $message
				);
				$ret = array('status' => 'OK');
				$redis->publish("channel:$recipient", json_encode($message));
				$redis->expire("user:$name", 60 * 3);
				$redis->expire("channels:$name", 60 * 3);
			} else {
				$ret = array('err' => 'Missing recipient');
			}
		} else {
			$ret = array('err' => 'Missing message');
		}
	} else {
		$ret = array('err' => 'You do not have a user, use the /me command');
	}

	header('Content-Type: application/json');
	echo json_encode($ret);
?>
