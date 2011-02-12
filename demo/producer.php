<?php

require('../Queue.php');

$client = new \Predis\Client('redis://127.0.0.1:6379');
$queue = new \Prequel\Queue('demo');
$queue->setClient($client);

$message = array(
	'id' => 'hello-world',
	'body' => 'Hello world'
);
$queue->publish($message);

?>