<?php
	/**
	 * leave.php
	 * Make a user leave a channel.
	 * The client must send:
	 * - user name
	 * - channel name 
	 *
	 * @author Paul
	 */

	require_once 'conn.php';

	$ret = null;

	if (isset($_POST['name'])) {
		$name = $_POST['name'];
		if (isset($_POST['channel'])) {
			$channel = $_POST['channel'];
			$count = $redis->srem("channels:$name", "channel:$channel");
			if ($count > 0) {
				// notify all users in the channel that we have left
				$redis->publish("channel:$name", 'CANCEL');
				$redis->publish("channel:$channel", json_encode(array(
					'name' => 'SERVER',
					'message' => "$name has left channel: $channel"
				)));
			}
			$ret = array('status' => 'OK');
		} else {
			$ret = array('err' => 'Missing channel field');
		}
	} else {
		$ret = array('err' => 'You do not have a user, use the /me command');
	}

	header('Content-Type: application/json');
	echo json_encode($ret);
?>
