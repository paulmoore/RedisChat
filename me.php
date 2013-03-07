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
			if (isset($_POST['oldname'])) {
				$oldname = $_POST['oldname'];
				$redis->del("user:$oldname");
				$redis->del("channels:$oldname");
			}
			$redis->hmset("user:$name", array(
				'name' => $name,
				'age' => $age,
				'sex' => $sex,
				'location' => $location
			));
			// if the user dosn't do anything for 3 minutes, delete him
			$redis->expire("user:$name", 60 * 3);
			$redis->sadd("channels:$name", "channel:$name", 'channel:all');
			$redis->expire("channels:$name", 60 * 3);
			// notify everyone we have joined the server
			$redis->publish('channel:all', json_encode(array(
				'name' => 'SERVER',
				'message' => "$name has joined the server"
			)));
			$ret = array('status' => 'OK');
		}
	} else {
		$ret = array('err' => 'You are missing your name, age, sex, or location - see command list');
	}

	header('Content-Type: application/json');
	echo json_encode($ret);
?>
