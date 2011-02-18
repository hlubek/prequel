<?php

require('../Queue.php');

$queue = new \Prequel\Queue('demo', new \Predis\Client('redis://127.0.0.1:6379?read_write_timeout=-1'));

do {
	$message = $queue->wait();
	if (is_array($message)) {
		echo $message['body'] . chr(10);
		sleep(mt_rand(0, 5));
	} else {
		echo "Timeout, repeating" . chr(10);
	}
} while(TRUE);

?>