# Prequel #

## About ##

Prequel is a wrapper for Redis backed queues in PHP. It provides a simple and convenient
API for safe access to queues.

## Main features ##

- Multiple queues
- Simple blocking publish / wait on a queue
- Detect duplicate messages
- Safe queue processing

## Quick examples ##

### Simple publish and process ###

Create a queue instance on the local Redis server with name "demo":

    $queue = new \Prequel\Queue('demo', new \Predis\Client('redis://127.0.0.1:6379'));

Publish a message to the queue. A message is basically a simple JSON serializable array.

    $message = array(
        'body' => 'Hello world'
    );
    $queue->publish($message);

Wait for a message (blocking with timeout) and echo the message body:

    do {
	    $message = $queue->wait();
	    if (is_array($message)) {
		    echo $message['body'] . chr(10);
        }
    } while(TRUE);

### Unique messages ##

To prevent duplicate messages in the queue (e.g. for idempotent operations), you can
specify an id key inside the message. If another message with the same identifier
is published to the queue before the other message was processed, it will be ignored.

    $message = array(
		'id' => 'hello-world',
		'body' => 'Hello world'
	);
	$queue->publish($message);

    $message = array(
		'id' => 'hello-world',
		'body' => 'Hello world 2'
	);
	$queue->publish($message);

In this example the second message will not be published to the queue.

## Dependencies ##

- PHP >= 5.3.0
- Predis (linked as submodule)

## Links ##

### Project ###
- [Source code](http://github.com/chlu/prequel/)

### Related ###
- [Redis](http://code.google.com/p/redis/)

## Author ##

[Christopher Hlubek](mailto:hlubek@networkteam.com)

## License ##

The code for Prequel is distributed under the terms of the MIT license (see LICENSE).
