<?php
namespace Prequel;

require(__DIR__ . '/lib/predis/lib/Predis.php');

/**
 * A Redis backed queue
 */
class Queue {
	protected $name;

	protected $client;

	public function __construct($name, \Predis\Client $client) {
		$this->name = $name;
		$this->client = $client;
	}

	/**
	 * Publish a message to the queue, specifiy an id array key for unique
	 * messages.
	 *
	 * @param array $message Serializable array
	 * @return void
	 */
	public function publish(array $message) {
		if (isset($message['id'])) {
			$added = $this->client->sadd("queue:{$this->name}:ids", $message['id']);
			if (!$added) return;
		}
		$encodedMessage = json_encode($message);
		$this->client->lpush("queue:{$this->name}:messages", $encodedMessage);
	}

	/**
	 * Wait for a message in the queue and return the message for processing
	 * (without safety queue)
	 *
	 * @param int $timeout
	 * @return array The JSON decoded message or NULL if a timeout occured
	 */
	public function wait($timeout = 60) {
		$keyAndMessage = $this->client->brpop("queue:{$this->name}:messages", $timeout);
		$message = $keyAndMessage[1];
		if (is_string($message)) {
			$decodedMessage = json_decode($message, TRUE);
			if (isset($decodedMessage['id'])) {
				$this->client->srem("queue:{$this->name}:ids", $decodedMessage['id']);
			}
			return $decodedMessage;
		} else {
			return NULL;
		}
	}

	/**
	 * Wait for a message in the queue and save the message to a safety processing
	 * queue.
	 *
	 * @param int $timeout
	 * @return array The JSON decoded message and original message or NULL if a timeout occured
	 */
	public function waitAndSave($timeout = 60) {
		$keyAndMessage = $this->client->brpoplpush("queue:{$this->name}:messages", "queue:{$this->name}:processing", $timeout);
		$message = $keyAndMessage[1];
		if (is_string($message)) {
			$decodedMessage = json_decode($message, TRUE);
			if (isset($decodedMessage['id'])) {
				$this->client->srem("queue:{$this->name}:ids", $decodedMessage['id']);
			}
			return array($decodedMessage, $message);
		} else {
			return NULL;
		}
	}

	/**
	 * Mark a message as finished
	 *
	 * @param string $message
	 * @return boolean TRUE if the message could be removed
	 */
	public function finish($message) {
		return $this->client->lrem("queue:{$this->name}:processing", 0, $message) > 0;
	}

	/**
	 * @return void
	 */
	public function dump() {
		echo "Messages in queue {$this->name}:\n";
		$messages = $this->client->lrange("queue:{$this->name}:messages", 0, 100);
		print_r($messages);

	}
}
?>