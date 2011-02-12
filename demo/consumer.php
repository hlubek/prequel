<?php

require('../Queue.php');

$client = new \Predis\Client('redis://127.0.0.1:6379');
$queue = new \Prequel\Queue('demo');
$queue->setClient($client);

do {
	$message = $queue->wait();
	if (is_array($message)) {
		echo $message['body'] . chr(10);
	} else {
		echo "Timeout, repeating" . chr(10);
	}
} while(TRUE);

?>