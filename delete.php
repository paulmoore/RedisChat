<?php
	/**
	 * delete.php
	 * Deletes a user.
	 * The client needs to send:
	 * - user name
	 *
	 * @author Paul
	 */

	require_once 'conn.php';

	$ret = null;

	if (isset($_POST['name'])) {
		$name = $_POST['name'];
		// need to make sure to delete both objects belonging to the user
		$redis->del("user:$name");
		$redis->del("channels:$name");
		// notify everyone the player has left the server
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
