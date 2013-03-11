<?php
	/**
	 * me.php
	 * Called by the client to identify this user.
	 * Creates the users data structures, and optionally deletes the old user (if the user wants to reidentify).
	 * The client must send:
	 * - user name
	 * - age
	 * - sex
	 * - location
	 *
	 * @author Paul
	 */

	require_once 'conn.php';

	$ret = null;

	if (isset($_POST['name'], $_POST['age'], $_POST['sex'], $_POST['location'])) {
		$name = $_POST['name'];
		$age = $_POST['age'];
		$sex = $_POST['sex'];
		$location = $_POST['location'];
		// don't allow a user to hijack another user's name!
		if ($redis->exists("user:$name")) {
			$ret = array('err' => 'That user name already exists!');
		} else {
			// if the user is already identified, we can delete his old user
			//////////////////
			// Assignment	//
			//////////////////
			if (isset($_POST['oldname'])) {
				$oldname = $_POST['oldname'];
				// TODO: delete the user's old HASH
				// TODO: delete the user's old channel SET
			}
			// TODO: create the user's HASH object
			$message = json_encode(array(
				'name' => 'SERVER',
				'message' => "$name has joined the server"
			));
			// notify everyone we have joined the server
			// TODO: publish to the 'channel:all' channel the message
			$ret = array('status' => 'OK');
		}
	} else {
		$ret = array('err' => 'You are missing your name, age, sex, or location - see command list');
	}

	header('Content-Type: application/json');
	echo json_encode($ret);
?>
