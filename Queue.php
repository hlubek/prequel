<?php
namespace Prequel;

require(__DIR__ . '/lib/predis/lib/Predis.php');

class Queue {
	protected $name;

	protected $client;

	public function __construct($name) {
		$this->name = $name;
	}

	/**
	 * @param array $message Serializable array
	 * @param string $id
	 * @return void
	 */
	public function publish(array $message) {
		if (isset($message['id'])) {
			$added = $this->client->sadd("queue:{$this->name}:ids", $message['id']);
			if (!$added) return;
		}
		$encodedMessage = json_encode($message);
		$this->client->rpush("queue:{$this->name}:messages", $encodedMessage);
	}

	/**
	 * @param int $timeout
	 * @return array The message
	 */
	public function wait($timeout = 60) {
		$keyAndMessage = $this->client->blpop("queue:{$this->name}:messages", $timeout);
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

	public function setClient(\Predis\Client $client) {
		$this->client = $client;
	}
}

?>