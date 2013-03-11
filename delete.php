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
		//////////////////
		// Assignment	//
		//////////////////
		// TODO: delete the user's HASH
		// TODO: delete the user's channel SET
		// notify everyone the player has left the server
		$message = json_encode(array(
			'name' => 'SERVER',
			'message' => "$name has left the server"
		));
		//////////////////
		// Assignment	//
		//////////////////
		// TODO: publish 'CANCEL' to the user's channel (will cancel the long polling request in receive.php)
		// TODO: publish to the 'channel:all' channel the left the server message.
		$ret = array('status' => 'OK');
	} else {
		$ret = array('err' => 'Missing field age');
	}

	header('Content-Type: application/json');
	echo json_encode($ret);
?>
