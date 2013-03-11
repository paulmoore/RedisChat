<?php
	/**
	 * conn.php
	 * Creates a connection to Redis and stores it in the $redis variable.
	 * Not an AJAX page.
	 *
	 * @author Paul
	 */

	require_once 'Predis/Autoloader.php';

	Predis\Autoloader::register();

	$redis = new Predis\Client(array(
		'scheme' => 'tcp',
		'host'   => 'gpu1.ddl.ok.ubc.ca',
		'port'   => 50030,
		'read_write_timeout' => 0
	));
?>
