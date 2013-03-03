<?php
	require_once 'conn.php';

	$ret = null;

	if (isset($_POST['name'], $_POST['age'], $_POST['sex'], $_POST['location'])) {
		$name = $_POST['name'];
		$age = $_POST['age'];
		$sex = $_POST['sex'];
		$location = $_POST['location'];
		if ($redis->exists("user:$name")) {
			$ret = array('err' => 'That user name already exists!');
		} else {
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
			$redis->expire("user:$name", 60 * 3);
			$redis->sadd("channels:$name", "channel:$name", 'channel:all');
			$redis->expire("channels:$name", 60 * 3);
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
