<?php
	/**
	 * chat.php
	 * Sends a chat message.
	 * The client needs to send:
	 * - user name
	 * - message
	 * - channel name (optional)
	 *
	 * @author Paul
	 */

	require_once 'conn.php';

	$ret = null;

	if (isset($_POST['name'])) {
		$name = $_POST['name'];
		if (isset($_POST['message'])) {
			$message = $_POST['message'];
			// the channel defaults to the 'all' channel
			if (isset($_POST['channel'])) {
				$channel = $_POST['channel'];
			} else {
				$channel = 'all';
			}
			// the message is stored as a serialized JSON object
			$message = array(
				'name' => $name,
				'message' => $message,
				'channel' => $channel
			);
			// publish the message, refresh the expiration on the user
			$ret = array('status' => 'OK');
			$redis->publish("channel:$channel", json_encode($message));
			$redis->expire("user:$name", 60 * 3);
			$redis->expire("channels:$name", 60 * 3);
		} else {
			$ret = array('err' => 'Missing message');
		}
	} else {
		$ret = array('err' => 'You do not have a user, use the /me command');
	}

	header('Content-Type: application/json');
	echo json_encode($ret);
?>
