<?php

require('../Queue.php');

$queue = new \Prequel\Queue('demo', new \Predis\Client('redis://127.0.0.1:6379'));

$N = 10;

for ($i = 0; $i < $N; $i++) {
	$message = array(
		'id' => 'hello-world-' . $i,
		'body' => 'Hello world #' . $i
	);
	$queue->publish($message);
}

?>