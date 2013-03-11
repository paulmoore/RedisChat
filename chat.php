<?php
	/**
	 * chat.php
	 * Sends a chat message.
	 * The client needs to send:
	 * - user name
	 * - message
	 * - channel name (optional)
	 *
	 * Assignment notes:
 	 * When talking to Redis, we use the following format for channels: channel:name
 	 * 		i.e. The channel "redisrox" would be specified by "channel:redisrox".
 	 * 
 	 * Users are similar to the aforementioned channels, we use user:name.
 	 * 		i.e. "user:stephen".
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
			$message = json_encode(array(
				'name' => $name,
				'message' => $message,
				'channel' => $channel
			));
			// publish the message, refresh the expiration on the user
			$ret = array('status' => 'OK');
			//////////////////
			// Assignment	//
			//////////////////
			// TODO: publish the message to the channel
		} else {
			$ret = array('err' => 'Missing message');
		}
	} else {
		$ret = array('err' => 'You do not have a user, use the /me command');
	}

	header('Content-Type: application/json');
	echo json_encode($ret);
?>
