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
			}
			$redis->hmset("user:$name", array(
				'name' => $name,
				'age' => $age,
				'sex' => $sex,
				'location' => $location
			));
			$redis->expire("user:$name", 5);
			$ret = array('status' => 'OK');
		}
	} else {
		$ret = array('err' => 'You are missing your name, age, sex, or location - see command list');
	}

	header('Content-Type: application/json');
	echo json_encode($ret);
?>
