<?php
	require_once 'conn.php';

	$ret = null;
	$pubsub = null;

	if (isset($_POST['name'])) {
		$name = $_POST['name'];
		$channels = $redis->smembers("channels:$name");
		//var_dump($channels);
		$pubsub = $redis->pubSub();
		$pubsub->subscribe($channels);
		foreach ($pubsub as $message) {
			switch ($message->kind) {
				case 'subscribe':
    				// Do nothing
    				break;
				case 'message':
					if ($message->payload == 'CANCEL') {
						$ret = array('status' => 'CANCEL');
					} else {
						$ret = json_decode($message->payload);
					}
					$pubsub->unsubscribe();
					break;
			}
		}
	} else {
		$ret = array('err' => 'You do not have a user, use the /me command');
	}

	unset($pubsub);

	header('Content-Type: application/json');
	echo json_encode($ret);
?>
