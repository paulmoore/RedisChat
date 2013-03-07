<?php
	/**
	 * whois.php
	 * Returns all information on a user.
	 * The client must send:
	 * - user name
	 * - name of the user to get
	 *
	 * @author Paul
	 */

	require_once 'conn.php';

	$ret = null;

	if (isset($_POST['name'])) {
		$name = $_POST['name'];
		$user = $redis->hgetall("user:$name");
		if ($user && count($user) > 0) {
			$ret = $user;
		} else {
			$ret = array('err' => 'That user does not exist!');
		}
	} else {
		$ret = array('err' => 'You are missing the name field, who is who?');
	}

	header('Content-Type: application/json');
	echo json_encode($ret);
?>
