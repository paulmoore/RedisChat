<?php
	/**
	 * tell.php
	 * Sends a direct message from one user to another (whisper).
	 * The client needs to send:
	 * - user name
	 * - message
	 * - recipient name
	 *
	 * @author Paul
	 */

	require_once 'conn.php';

	$ret = null;

	if (isset($_POST['name'])) {
		$name = $_POST['name'];
		if (isset($_POST['message'])) {
			$message = $_POST['message'];
			if (isset($_POST['recipient'])) {
				$recipient = $_POST['recipient'];
				// whisper the message to the recipient's private channel
				$message = json_encode(array(
					'name' => $name,
					'channel' => $recipient,
					'message' => $message
				));
				$ret = array('status' => 'OK');
				//////////////////
				// Assignment	//
				//////////////////
				// TODO: publish the message to the recipient's channel
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
